jQuery(function($){

    let modal = $('#wrhr-modal');
    let pageWrapper = $('#wrhr-page-wrapper');
    let currentPage = 1;
    let totalPages = 1;

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
    $('.wrhr-read-btn').on('click', function(){
        let htmlUrl = $(this).data('html');
        let readerId = $(this).data('reader');

        // Phase 6 will fetch HTML here. Now dummy.
        pageWrapper.html(`<div class="wrhr-page">Loading HTML: ${htmlUrl}</div>`);

        modal.addClass('active');
        $('body').addClass('wrhr-modal-open');
    });

    // Close modal
    $('#wrhr-close, #wrhr-modal-overlay').on('click', function(){
        modal.removeClass('active');
        $('body').removeClass('wrhr-modal-open');
    });

    // Fullscreen toggle
    $('#wrhr-fs-btn').on('click', function(){
        $('#wrhr-modal-content').toggleClass('fullscreen');
        setTimeout(calcA5Height, 250);
    });

    // Navigation (will be real in Phase 6)
    $('#wrhr-next').on('click', function(){
        console.log('Next page…');
    });
    $('#wrhr-prev').on('click', function(){
        console.log('Prev page…');
    });

});
