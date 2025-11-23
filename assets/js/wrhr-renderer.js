jQuery(function($){

    $('.wrhr-read-btn').on('click', function(){

        const htmlUrl = $(this).data('html');
        const readerId = $(this).data('reader');

        console.log('WRHR Read Button Clicked:', htmlUrl, readerId);

        alert('HTML Reader modal will open in Phase 5.');

    });

});
