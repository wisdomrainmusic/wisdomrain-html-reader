jQuery(function($){

    let modal = $('#wrhr-modal');
    let pageWrapper = $('#wrhr-page-wrapper');
    let WRHR_PAGES = [];
    let WRHR_INDEX = 0;
    let WRHR_ACTIVE_READER = '';
    let WRHR_ACTIVE_BOOK_INDEX = null;

    const wrhrLangConfig = window.wrhrLangConfig || {};
    const WRHR_STORAGE_KEYS = wrhrLangConfig.storage_keys || {};
    const WRHR_LAST_LANG_KEY     = WRHR_STORAGE_KEYS.last_lang || 'wrhr_last_lang';
    const WRHR_FALLBACK_LANG_KEY = 'wrhr_lang';
    const WRHR_LAST_PAGE_PREFIX = WRHR_STORAGE_KEYS.last_page_prefix || 'wrhr_last_page_';

    // ---------------- LANGUAGE STORAGE HELPERS ----------------
    function wrhrPersistLanguage(value) {
        try {
            localStorage.setItem(WRHR_LAST_LANG_KEY, value);
            localStorage.setItem(WRHR_FALLBACK_LANG_KEY, value);
        } catch (e) {}
    }

    function wrhrGetSavedLanguage() {
        try {
            return localStorage.getItem(WRHR_LAST_LANG_KEY) || localStorage.getItem(WRHR_FALLBACK_LANG_KEY);
        } catch (e) {
            return null;
        }
    }

    // mevcut wrhrTranslate yapısını sadece UI highlight için bırakıyoruz
    const wrhrTranslate = {
        languages: wrhrLangConfig.languages || [],
        selectors: wrhrLangConfig.google_selectors || {},
        currentLanguage: null,
        combo: null,

        init() {
            this.restoreLastLanguage();
        },

        highlightActive(code) {
            if (!this.container || !this.container.length) {
                return;
            }

            this.container.find('.wrhr-lang-btn').removeClass('is-active');
            if (!code) {
                return;
            }

            this.container.find(`.wrhr-lang-btn[data-lang="${code}"]`).addClass('is-active');
        },

        restoreLastLanguage() {
            const stored = wrhrGetSavedLanguage();
            if (!stored) {
                return;
            }
            this.setLanguage(stored, { persist: false, silent: true });
        },

        setLanguage(code, options = {}) {
            const language = this.languages.find((item) => item.code === code);
            if (!language) {
                return;
            }

            this.currentLanguage = language;
            this.highlightActive(language.code);

            if (options.persist !== false) {
                wrhrPersistLanguage(language.code);
            }
            // Google çeviriye uygulamayı tek motor üzerinden yap:
            if (!options.silent) {
                wrhrSetLanguage(language.google_code);
            }

            $(document).trigger('wrhr_translate_language_changed', { language });
        },

        getGoogleCombo() {
            if (this.combo && document.body.contains(this.combo)) {
                return this.combo;
            }

            if (!this.selectors.combo) {
                return null;
            }

            const el = document.querySelector(this.selectors.combo);
            if (el) {
                this.combo = el;
            }

            return el || null;
        },

        // applyToGoogle / refresh mantığını kaldırıyoruz; tek motor wrhrSetLanguage
        applyToGoogle() {},
        refresh() {},
    };

    window.wrhrTranslate = wrhrTranslate;
    wrhrTranslate.init();

    function getLastPageKey() {
        if (!WRHR_ACTIVE_READER) {
            return null;
        }
        return `${WRHR_LAST_PAGE_PREFIX}${WRHR_ACTIVE_READER}`;
    }

    function restoreLastPage(total) {
        const key = getLastPageKey();
        if (!key) {
            return 0;
        }

        const stored = parseInt(localStorage.getItem(key), 10);
        if (Number.isFinite(stored) && stored >= 0 && stored < total) {
            return stored;
        }

        return 0;
    }

    function persistPageIndex(index) {
        const key = getLastPageKey();
        if (!key) {
            return;
        }

        localStorage.setItem(key, String(index));
    }

    async function loadHTML(url){
        try {
            let raw = await fetch(url).then(r => r.text());

            // Clean via REST
            let cleaned = await fetch(wpApiSettings.root + 'wrhr/v1/clean', {
                method: 'POST',
                headers: {'Content-Type': 'application/json'},
                body: JSON.stringify({ html: raw })
            }).then(r => r.json());

            return cleaned.clean;

        } catch(e){
            console.error(e);
            return "<p>Error loading HTML.</p>";
        }
    }

    function htmlToBlocks(html){
        let container = document.createElement('div');
        container.innerHTML = html;

        let blocks = [];
        const blockTags = new Set(['P','H1','H2','H3','H4','H5','H6','LI','TD','TH']);

        const walker = document.createTreeWalker(container, NodeFilter.SHOW_ELEMENT, null);

        while (walker.nextNode()) {
            const node = walker.currentNode;
            const tag  = node.tagName;

            if (blockTags.has(tag)) {
                const text = normalizeText(node.textContent);
                if (text.length) {
                    blocks.push(node.outerHTML);
                }
                continue;
            }

            if (tag === 'DIV') {
                const hasInnerBlocks = node.querySelector('p, h1, h2, h3, h4, h5, h6, li, td, th');
                const text = normalizeText(node.textContent);

                if (!hasInnerBlocks && text) {
                    blocks.push(`<p>${text}</p>`);
                }
            }
        }

        if (!blocks.length) {
            const fallback = normalizeText(container.textContent);
            if (fallback) {
                fallback.split(/\n{2,}/).forEach(part => {
                    const trimmed = normalizeText(part);
                    if (trimmed.length) {
                        blocks.push(`<p>${trimmed}</p>`);
                    }
                });
            }
        }

        if (!blocks.length) {
            blocks.push('<p>No readable content found.</p>');
        }

        const filteredBlocks = blocks
            .map(b => b.trim())
            .filter(b => normalizeText(stripTags(b)).length);

        return splitLargeBlocks(filteredBlocks);
    }

    function normalizeText(text){
        return (text || '').replace(/\s+/g, ' ').trim();
    }

    function stripTags(html){
        const temp = document.createElement('div');
        temp.innerHTML = html;
        return temp.textContent || '';
    }

    function splitLargeBlocks(blocks, wordLimit = 180){
        const normalized = [];

        blocks.forEach(html => {
            const temp = document.createElement('div');
            temp.innerHTML = html;
            const base = temp.firstElementChild || temp.firstChild;
            const tagName = base && base.tagName ? base.tagName.toLowerCase() : 'p';
            const text = normalizeText(temp.textContent || '');

            if (!text.length) {
                return;
            }

            const words = text.split(/\s+/);

            if (words.length <= wordLimit) {
                normalized.push(html);
                return;
            }

            for (let i = 0; i < words.length; i += wordLimit) {
                const part = words.slice(i, i + wordLimit).join(' ');
                normalized.push(`<${tagName}>${part}</${tagName}>`);
            }
        });

        return normalized.length ? normalized : ['<p>No readable content found.</p>'];
    }

    function paginateBlocks(blocks){
        const pages = [];
        const cssLimit = parseFloat(getComputedStyle(document.documentElement).getPropertyValue('--wrhr-a5-height'));
        const limit = Number.isFinite(cssLimit) && cssLimit > 0 ? cssLimit : 744;

        const measuringWrapper = document.createElement('div');
        measuringWrapper.style.position = 'absolute';
        measuringWrapper.style.visibility = 'hidden';
        measuringWrapper.style.width = 'calc(100% - 40px)';
        measuringWrapper.style.left = '-9999px';
        measuringWrapper.style.top = '-9999px';
        document.body.appendChild(measuringWrapper);

        let currentPage = createPageElement();
        measuringWrapper.appendChild(currentPage);

        function resetPage(){
            currentPage = createPageElement();
            measuringWrapper.innerHTML = '';
            measuringWrapper.appendChild(currentPage);
        }

        function appendBlockWithSplit(html){
            let blockEl = createBlockElement(html);
            currentPage.appendChild(blockEl);

            if (currentPage.scrollHeight > limit){
                currentPage.removeChild(blockEl);

                if (currentPage.childNodes.length){
                    pages.push(currentPage.outerHTML);
                    resetPage();
                }

                blockEl = createBlockElement(html);
                currentPage.appendChild(blockEl);

                if (currentPage.scrollHeight > limit){
                    currentPage.removeChild(blockEl);

                    const fragments = splitOversizedBlock(html, limit).filter(fragment => normalizeText(stripTags(fragment)).length);

                    if (!fragments.length || (fragments.length === 1 && fragments[0] === html)){
                        const forceBlock = createBlockElement(html);
                        currentPage.appendChild(forceBlock);
                        pages.push(currentPage.outerHTML);
                        resetPage();
                        return;
                    }

                    fragments.forEach(fragment => appendBlockWithSplit(fragment));
                    return;
                }
            }
        }

        blocks.forEach(appendBlockWithSplit);

        if (!currentPage.childNodes.length) {
            currentPage.appendChild(createBlockElement('<p>No readable content found.</p>'));
        }

        pages.push(currentPage.outerHTML);

        if (!pages.length) {
            pages.push('<div class="wrhr-page"><p>No readable content found.</p></div>');
        }

        measuringWrapper.remove();

        return pages;
    }

    function createBlockElement(html){
        const wrapper = document.createElement('div');
        wrapper.innerHTML = html.trim();
        const element = wrapper.firstElementChild || document.createElement('div');
        element.classList.add('wrhr-block');
        return element;
    }

    function createPageElement(){
        const page = document.createElement('div');
        page.className = 'wrhr-page';
        return page;
    }

    function splitOversizedBlock(html, limit){
        const temp = document.createElement('div');
        temp.innerHTML = html;
        const base = temp.firstElementChild || temp.firstChild;

        const tagName = base && base.tagName ? base.tagName.toLowerCase() : 'p';
        const text = (base && base.textContent ? base.textContent : temp.textContent || '').trim();

        if (!text.length) {
            return [];
        }

        const words = text.split(/\s+/);
        const fragments = [];

        const probePage = createPageElement();
        probePage.style.position = 'absolute';
        probePage.style.visibility = 'hidden';
        probePage.style.left = '-9999px';
        probePage.style.top = '-9999px';
        probePage.style.height = 'auto';
        document.body.appendChild(probePage);

        const fits = (candidateWords) => {
            probePage.innerHTML = '';
            const el = document.createElement(tagName);
            el.textContent = candidateWords.join(' ');
            probePage.appendChild(el);
            return probePage.scrollHeight <= limit;
        };

        let buffer = [];
        words.forEach(word => {
            buffer.push(word);
            if (!fits(buffer)) {
                buffer.pop();
                if (buffer.length) {
                    fragments.push(`<${tagName}>${buffer.join(' ')}</${tagName}>`);
                    buffer = [word];
                } else {
                    fragments.push(`<${tagName}>${word}</${tagName}>`);
                    buffer = [];
                }
            }
        });

        if (buffer.length) {
            fragments.push(`<${tagName}>${buffer.join(' ')}</${tagName}>`);
        }

        probePage.remove();

        return fragments;
    }

    /* Phase 9 – Responsive A5/A6 Height Logic Fix */
    function calcA5Height() {

        const width = window.innerWidth;
        const height = window.innerHeight;

        let h;

        // Mobile: keep A6 ratio
        if (width < 768) {
            h = width * 1.414; 
        }
        // Desktop + Tablet: use viewport height
        else {
            h = height * 0.88;
        }

        // Fallback
        if (!isFinite(h) || h <= 0) {
            h = 744;
        }

        document.documentElement.style.setProperty('--wrhr-a5-height', h + 'px');
    }

    calcA5Height();
    $(window).on('resize', calcA5Height);

    let WRHR_BLOCKS = [];

    function WRHR_recalculateLayout() {
        calcA5Height();

        if (WRHR_PAGES.length > 0) {
            WRHR_PAGES = paginateBlocks(WRHR_BLOCKS);
            renderPage(Math.min(WRHR_INDEX, WRHR_PAGES.length - 1));
        }
    }

    window.WRHR_recalculateLayout = WRHR_recalculateLayout;

    function wrhrEnterFullscreen() {
        const modalContent = document.getElementById('wrhr-modal-content');
        if (!modalContent) return;

        if (!modalContent.classList.contains('fullscreen')) {
            modalContent.classList.add('fullscreen');
        }

        if (typeof window.WRHR_recalculateLayout === 'function') {
            setTimeout(() => window.WRHR_recalculateLayout(), 350);
        }
    }

    // Open modal
    function wrhrRestoreLanguageOnOpen() {
        const saved = wrhrGetSavedLanguage();
        if (!saved) return;

        wrhrWaitForTranslateCombo(function(combo) {
            combo.value = saved;
            combo.dispatchEvent(new Event('change'));
        });
    }

    $('.wrhr-read-btn').on('click', async function(){

        const url = $(this).data('html');
        const wrapper = this.closest('.wrhr-reader-wrapper');
        const title = wrapper && wrapper.dataset ? wrapper.dataset.title : '';
        WRHR_ACTIVE_READER = $(this).data('reader') || '';
        const parsedIndex = parseInt($(this).data('index'), 10);
        WRHR_ACTIVE_BOOK_INDEX = Number.isFinite(parsedIndex) ? parsedIndex : null;

        $(document).trigger('wrhr_modal_opened', {
            readerId: WRHR_ACTIVE_READER,
            bookIndex: WRHR_ACTIVE_BOOK_INDEX,
        });

        wrhrRestoreLanguageOnOpen();
        wrhrTranslate.restoreLastLanguage();

        modal.addClass('active');
        $('body').addClass('wrhr-modal-open');
        if (title && title.length > 0) {
            $('#wrhr-modal-title').text(title);
        } else {
            $('#wrhr-modal-title').text('Untitled');
        }

        wrhrEnterFullscreen();

        $('#wrhr-page-wrapper').html('<div class="wrhr-page">Loading…</div>');

        let cleanHTML = await loadHTML(url);
        WRHR_BLOCKS = htmlToBlocks(cleanHTML || '');
        WRHR_PAGES = paginateBlocks(WRHR_BLOCKS);

        if (!WRHR_PAGES.length) {
            WRHR_PAGES = ['<div class="wrhr-page"><p>No readable content found.</p></div>'];
        }

        const initialPage = restoreLastPage(WRHR_PAGES.length);
        renderPage(initialPage);
        wrhrRestoreLanguageAfterPagination();

    });

    // Close modal
    $('#wrhr-close, #wrhr-modal-overlay').on('click', function(){
        modal.removeClass('active');
        $('body').removeClass('wrhr-modal-open');
    });

    // Fullscreen toggle
    $('#wrhr-fs-btn').on('click', function(){
        $('#wrhr-modal-content').toggleClass('fullscreen');

        if (typeof window.WRHR_recalculateLayout === 'function') {
            setTimeout(() => window.WRHR_recalculateLayout(), 350);
        }
    });

    function renderPage(i){
        if (!WRHR_PAGES.length){
            pageWrapper.html('<div class="wrhr-page">No content available.</div>');
            $('#wrhr-page-info').text('Page 0 / 0');
            return;
        }

        if (i < 0) i = 0;
        if (i >= WRHR_PAGES.length) i = WRHR_PAGES.length - 1;

        WRHR_INDEX = i;
        pageWrapper.html( WRHR_PAGES[i] );
        $('#wrhr-page-info').text(`Page ${i+1} / ${WRHR_PAGES.length}`);

        persistPageIndex(WRHR_INDEX);

        $(document).trigger('wrhr_page_changed', {
            page: WRHR_INDEX + 1,
            total: WRHR_PAGES.length,
            readerId: WRHR_ACTIVE_READER,
            bookIndex: WRHR_ACTIVE_BOOK_INDEX,
        });

        wrhrRestoreLanguageAfterPagination();
    }

    function wrhrRestoreLanguageAfterPagination() {
        const saved = wrhrGetSavedLanguage();
        if (!saved) return;

        setTimeout(() => {
            wrhrWaitForTranslateCombo(function(combo) {
                combo.value = saved;
                combo.dispatchEvent(new Event('change'));
            });
        }, 200);
    }

    $('#wrhr-next').on('click', function(){
        if (WRHR_INDEX < WRHR_PAGES.length - 1){
            renderPage(WRHR_INDEX + 1);
        }
    });

    $('#wrhr-prev').on('click', function(){
        if (WRHR_INDEX > 0){
            renderPage(WRHR_INDEX - 1);
        }
    });

    // --------------------------------------------------------------
    //  GOOGLE TRANSLATE INTEGRATION (PHASE 3)
    // --------------------------------------------------------------

    /**
     * Safe way to get Google Translate combo element.
     */
    function wrhrGetTranslateCombo() {
        // Only use the combo created inside our hidden container
        return document.querySelector('#google_translate_element select.goog-te-combo') || document.querySelector('select.goog-te-combo');
    }

    function wrhrWaitForTranslateCombo(callback) {
        var combo = wrhrGetTranslateCombo();
        if (combo) {
            callback(combo);
            return;
        }

        var tries = 0;
        var timer = setInterval(function() {
            combo = wrhrGetTranslateCombo();
            if (combo || tries > 50) {
                clearInterval(timer);
                if (combo) {
                    callback(combo);
                } else {
                    console.warn('WRHR: Google Translate combo never appeared.');
                }
            }
            tries++;
        }, 200);
    }

    /**
     * Combo yeniden yaratıldığında bile dil seçimini kaydet.
     * Her yeni oluşturulan goog-te-combo için sadece 1 kez listener ekleriz.
     */
    function wrhrBindGoogleComboPersistence() {
        wrhrWaitForTranslateCombo(function(combo) {
            if (combo.dataset.wrhrBound === '1') {
                return;
            }

            combo.addEventListener('change', function () {
                try {
                    wrhrPersistLanguage(combo.value);
                } catch (e) {}
            });

            combo.dataset.wrhrBound = '1';
        });
    }

    /**
     * Set language on Google Translate widget.
     */
    function wrhrSetLanguage(langCode) {
        wrhrWaitForTranslateCombo(function(combo) {
            combo.value = langCode;
            combo.dispatchEvent(new Event("change"));

            try {
                wrhrPersistLanguage(langCode);
            } catch (e) {}

            // Her setLanguage çağrısında persistence listener'ı da garanti altına al
            wrhrBindGoogleComboPersistence();
        });
    }

    /**
     * Force Google Translate to reapply translation after page content changes.
     */
    function wrhrTranslateRefresh() {
        try {
            var saved = wrhrGetSavedLanguage();
            if (saved) {
                wrhrSetLanguage(saved);
            }
        } catch (e) {}
    }

    // Sayfa yüklendiğinde ilk combo için persistence bağla
    wrhrBindGoogleComboPersistence();

    /**
     * Restore last language on modal open.
     */
    function wrhrRestoreLastLanguage() {
        try {
            const lang = wrhrGetSavedLanguage();
            if (!lang) return;

            // Modal açılışında tek sefer set et
            setTimeout(() => {
                wrhrSetLanguage(lang);
            }, 300);
        } catch (e) {}
    }

    // --------------------------------------------------------------
    //  PAGE & LANGUAGE RESTORE ENGINE
    // --------------------------------------------------------------

    /**
     * Restore last page based on reader id.
     */
    function wrhrRestoreLastPage(readerId) {
        try {
            const saved = localStorage.getItem('wrhr_last_page_' + readerId);
            if (!saved) return;

            const page = parseInt(saved);
            if (!isNaN(page)) {
                wrhrGoToPage(page);
            }
        } catch (e) {}
    }

    /**
     * Save page number on navigation.
     */
    document.addEventListener('wrhr_page_changed', function (e) {
        try {
            const readerId = window.wrhrCurrentReaderId || null;
            const page = e.detail && e.detail.page ? e.detail.page : null;

            // Save page
            if (readerId && page !== null) {
                localStorage.setItem('wrhr_last_page_' + readerId, page);
            }
        } catch (e) {}
    });

    // --------------------------------------------------------------
    //  READ BUTTON OVERRIDE – CAPTURE READER ID
    // --------------------------------------------------------------

    document.addEventListener('click', function (e) {
        const btn = e.target.closest('.wrhr-read-btn');
        if (!btn) return;

        // Find reader wrapper for ID
        const wrapper = btn.closest('.wrhr-reader-wrapper');
        const readerId = wrapper ? wrapper.getAttribute('data-reader-id') : null;

        if (readerId) {
            window.wrhrCurrentReaderId = readerId;

            // Restore saved page after modal opens
            setTimeout(() => {
                wrhrRestoreLastPage(readerId);
            }, 400);
        }
    });

    // --------------------------------------------------------------
    //  INITIALIZE TRANSLATE SYSTEM ON PAGE LOAD + MODAL OPEN
    // --------------------------------------------------------------

    // Whenever modal opens, restore language
    document.addEventListener('wrhr_modal_opened', function () {
        wrhrRestoreLastLanguage();
    });

    // Final safety refresh after resize events
    window.addEventListener('resize', function () {
        setTimeout(() => {
            wrhrTranslateRefresh();
        }, 200);
    });

});
