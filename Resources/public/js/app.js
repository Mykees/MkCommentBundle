jQuery( function ($) {

    var $remove_reply   = $('#remove-reply');
    var $form           = $('#comments_area');
    var $body           = $('body');
    var $comments_list  = $("#comments-list-area");
    var $inputs         = $('.comment_form_input');
    var $inputs_opacity = $('.input_opacity');
    var $comment_id     = 0;
    var $comment_depth  = 0;
    var $ajax_loader    = $("#ajax-loader");
    var $comment_parent_id  = $('#mykees_comment_parentId');
    var $textarea           = $("#mykees_comment_content");
    var $comment_text   = $("#alert-comment-flash");
    var $model_value    = $("#mykees_comment_model").val();
    var $model_id_value    = $("#mykees_comment_modelId").val();

    // REPLY TO COMMENT
    $body.on('click','.reply', function (e){
        e.preventDefault();
        var $this     = $(this);
        var $comment  = $this.parents('.comment-list');
        $comment_id       = $this.data('id');
        $comment_depth    = $this.data('depth');
        $comment_parent_id.val($comment_id);
        $remove_reply.css('display','inline-block');
        $form.hide();
        $comment.after($form);
        $form.slideDown();
        $form.addClass('ajax-reponse');
        $textarea.val("@" + $this.data('username') + " ");
    });
    // CANCEL REPLY
    $body.on('click','#remove-reply', function(e){
        e.preventDefault();
        var $wrapper = $('#form-wrapper');
        $remove_reply.css('display','none');
        $comment_parent_id.val(0);
        $form.hide();
        $wrapper.append($form);
        $form.slideDown();
        $form.removeClass('ajax-reponse');
        $textarea.val('');
    });

    //SUBMIT COMMENT
    $body.on('submit',".ajax_comment_form",function(e){
        e.preventDefault();
        var $this = $(this);
        var $url = $this.attr('action');
        var values = {};
        $.each( $this.serializeArray(), function(i, field) {
            var result = field.name.match(/\[(.*)\]/);
            values[result[1]] = field.value;
        });

        $inputs.val('');
        $inputs_opacity.css('opacity','0.4');
        $inputs.attr('disabled','disabled');
        $ajax_loader.css('display','block');

        var $response_type = $form.next().hasClass('comment-replies') && $form.hasClass('ajax-reponse') ? true : false;

        if(
            $("#mykees_comment_model").val() == $model_value &&
            $("#mykees_comment_modelId").val() == $model_id_value
        ){
            ajaxPost($url,values,$comment_depth,$response_type)
        }else{
            $inputs_opacity.css('opacity','1');
            $ajax_loader.css('display','none');
            $inputs.removeAttr('disabled');
        }

        $comment_parent_id.val(0);
        $comment_depth = 0;
    });

    function ajaxPost($url,values,$comment_depth,$response_type)
    {
        $.post($url,{'mykees_comment':values,'depth':$comment_depth,'response_type':$response_type},function(response){

            var data = $.parseJSON(response);

            if(!data.error)
            {
                if(data.parent_id > 0 )
                {
                    if($response_type){ // If the comment is reply to a comment

                        $form.next('.comment-replies').append(data.template);

                    }else if($form.parent().hasClass('comment-replies') && data.max_depth == true){ // If the comment is a reply to a reply

                        $form.parent().append(data.template);

                    }else{
                        //add a new reply
                        $form.after(data.template);
                    }

                    $remove_reply.trigger('click');
                }else{
                    //Add a new comment
                    $comments_list.prepend(data.template);
                }

                if($comment_text.length > 0)
                {
                    $comment_text.html(data.success_message).text();
                    $comment_text.trigger('change');
                }
            }
            $inputs_opacity.css('opacity','1');
            $ajax_loader.css('display','none');
            $inputs.removeAttr('disabled');
        });
    }
});