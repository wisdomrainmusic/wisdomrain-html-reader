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

        container.childNodes.forEach(node => {
            if (node.nodeType === 1) { // element
                if (['P','H2','H3','H4','UL','OL'].includes(node.tagName)) {
                    blocks.push(node.outerHTML);
                }
            }
        });

        return blocks;
    }

    function paginateBlocks(blocks){
        let pages = [];
        let currentPage = document.createElement('div');
        currentPage.className = 'wrhr-page';

        let wrapper = $('#wrhr-page-wrapper');
        wrapper.html(currentPage);

        let limit = parseFloat(getComputedStyle(document.documentElement)
            .getPropertyValue('--wrhr-a5-height'));

        blocks.forEach(html => {
            let temp = document.createElement('div');
            temp.innerHTML = html;

            currentPage.appendChild(temp);

            if (currentPage.scrollHeight > limit){
                currentPage.removeChild(temp);

                pages.push(currentPage.outerHTML);

                currentPage = document.createElement('div');
                currentPage.className = 'wrhr-page';
                currentPage.appendChild(temp);
            }
        });

        pages.push(currentPage.outerHTML);

        return pages;
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

    // Open modal
    $('.wrhr-read-btn').on('click', async function(){

        const url = $(this).data('html');

        modal.addClass('active');

        $('#wrhr-page-wrapper').html('<div class="wrhr-page">Loading…</div>');

        let cleanHTML = await loadHTML(url);
        let blocks = htmlToBlocks(cleanHTML);
        WRHR_PAGES = paginateBlocks(blocks);
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
                let cleanHTML = WRHR_PAGES.join('');
                let blocks = htmlToBlocks(cleanHTML);
                WRHR_PAGES = paginateBlocks(blocks);
                renderPage(0);
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
