(function($){
	//Trouble Signing In Form
    $('form.trouble-signing-in')
        .bootstrapValidator({
            // Only disabled elements are excluded
            // The invisible elements belonging to inactive tabs must be validated
            excluded: [':disabled'],
            feedbackIcons: {
                valid: 'glyphicon glyphicon-ok',
                invalid: 'glyphicon glyphicon-remove',
                validating: 'glyphicon glyphicon-refresh'
            }
        })
        // Called when a field is invalid
        .on('error.field.bv', function(e, data) {
            // data.element --> The field element
        })
        // Called when a field is valid
        .on('success.field.bv', function(e, data) {
            // data.bv      --> The BootstrapValidator instance
            // data.element --> The field element
        })
        .on('success.form.bv', function(e) {
            // Prevent form submission
            e.preventDefault();

            // Get the form instance
            var $form = $(e.target);

            var interest_values = [];
            $('form.trouble-signing-in [name="interests"]').each( function() {
                if( $(this).is(':checked') ) {
                    interest_values.push( $(this).val() );
                }
            });
            var interests = interest_values.join(',');

            var formSerialized = $form.serialize();
            formSerialized = formSerialized + "&interests=" + interests;

            // Get the BootstrapValidator instance
            var bv = $form.data('bootstrapValidator');
            $form.find('.status').removeClass('error-text');
            $form.find('.status').show().html(ajax_login_object.loadingmessage);
            $.ajax({
                type: 'POST',
                dataType: 'json',
                url: ajax_login_object.ajaxurl,
                cache: false,
                data: {
                    'action': 'trouble_signing_in', //calls wp_ajax_nopriv_ajaxlogin
                    'form': formSerialized,
                    'security': $('#security').val() },
                success: function(data){
                    console.log("Trouble signing in success function.. show data:");
                    console.log(data);
                    saveButton.prop("disabled", true);
                    if (data.success == true){
                        $(".row.contact-boxes .card.block.form:not(#main-message)").closest(".contact-boxes").remove();
                        $form.find(".row.contact-boxes .card.block.form").html(data.message);
                        $form.find('.status').html("");
                    }else{
                        $form.find('.status').addClass('error-text');
                        $form.find('.status').html(data.message);
                    }
                    $('html').animate({scrollTop: 0, scrollLeft: 0},300);
                },
                error: function(xhr, ajaxOptions, thrownError){
                    $form.find('.status').addClass('error-text');
                    $form.find('.status').html(xhr.status+' '+thrownError);
                    console.log("Trouble signing in error function.. show error:");
                    console.log(xhr.status);
                    console.log(thrownError);
                    console.log(ajaxOptions);
                }
            });
        });
})(jQuery);