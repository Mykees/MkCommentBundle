jQuery( function ($) {

    var $remove_reply    = $('#remove-reply').parent();
    var $form    = $('#comments_area');

    $('.reply').click( function (e){
        e.preventDefault();
        var $this    = $(this);
        var $comment = $this.parents('.comment__list');
        var id       = $this.data('id');
        $('#mykees_commentbundle_comment_parentId').val(id);
        $remove_reply.css('display','inline-block');
        $form.hide();
        $comment.after($form);
        $form.slideDown();
    });
    $('body').on('click','#remove-reply', function(e){
        e.preventDefault();
        var $this    = $(this);
        var $wrapper = $('#form-wrapper');
        $remove_reply.css('display','none');
        $form.hide();
        $wrapper.append($form);
        $form.slideDown();
    });

});