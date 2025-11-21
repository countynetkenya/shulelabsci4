$(document).on('click', '.item__playlist_item', function() {
    "use strict";
    var lessonid   = $(this).data('lessonid');
    $('.item__playlist_item').removeClass('active');
    $(this).addClass('active');

    if(parseInt(lessonid)) {
        $.ajax({
            type: 'POST',
            url: LESSONURL,
            data: {'lessonid': lessonid},
            dataType: "html",
            success: function(data) {
               $('#mainItem').html(data);
            }
        });
    }
});