jQuery(function($){

    let modal = $('#wrhr-modal');
    let pageWrapper = $('#wrhr-page-wrapper');
    let WRHR_PAGES = [];
    let WRHR_INDEX = 0;

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
                const text = node.textContent.trim();
                if (text.length) {
                    blocks.push(node.outerHTML);
                }
                continue;
            }

            if (tag === 'DIV') {
                const hasInnerBlocks = node.querySelector('p, h1, h2, h3, h4, h5, h6, li, td, th');
                const text = node.textContent.trim();

                if (!hasInnerBlocks && text) {
                    blocks.push(`<p>${text}</p>`);
                }
            }
        }

        if (!blocks.length) {
            const fallback = container.textContent.trim();
            if (fallback) {
                fallback.split(/\n{2,}/).forEach(part => {
                    const trimmed = part.trim();
                    if (trimmed.length) {
                        blocks.push(`<p>${trimmed}</p>`);
                    }
                });
            }
        }

        if (!blocks.length) {
            blocks.push('<p>No readable content found.</p>');
        }

        return blocks;
    }

    function paginateBlocks(blocks){
        const pages = [];
        const limit = parseFloat(getComputedStyle(document.documentElement).getPropertyValue('--wrhr-a5-height'));

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

                    const fragments = splitOversizedBlock(html, limit);

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
        return wrapper.firstElementChild || document.createElement('div');
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

    function calcA5Height(){
        let h = Math.min(
            window.innerHeight * 0.90,
            (window.innerWidth * 1.414)
        );
        document.documentElement.style.setProperty('--wrhr-a5-height', h + 'px');
    }

    calcA5Height();
    $(window).on('resize', calcA5Height);

    let WRHR_BLOCKS = [];

    // Open modal
    $('.wrhr-read-btn').on('click', async function(){

        const url = $(this).data('html');

        modal.addClass('active');

        $('#wrhr-page-wrapper').html('<div class="wrhr-page">Loading…</div>');

        let cleanHTML = await loadHTML(url);
        WRHR_BLOCKS = htmlToBlocks(cleanHTML);
        WRHR_PAGES = paginateBlocks(WRHR_BLOCKS);
        renderPage(0);

    });

    // Close modal
    $('#wrhr-close, #wrhr-modal-overlay').on('click', function(){
        modal.removeClass('active');
        $('body').removeClass('wrhr-modal-open');
    });

    // Fullscreen toggle
    $('#wrhr-fs-btn').on('click', function(){
        $('#wrhr-modal-content').toggleClass('fullscreen');

        setTimeout(() => {
            calcA5Height();

            if (WRHR_PAGES.length > 0){
                WRHR_PAGES = paginateBlocks(WRHR_BLOCKS);
                renderPage(Math.min(WRHR_INDEX, WRHR_PAGES.length - 1));
            }
        }, 350);
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

});
