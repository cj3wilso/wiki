(function($){
	//Create New Project
    $('form.create-project')
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
			var saveButton = $form.find('input[type="submit"]');
            var formSerialized = $form.serialize();

            saveButton.prop("disabled", true);
            $form.find('.status').removeClass('error-text');
            $form.find('.status').show().html(ajax_login_object.loadingmessage);
            $.ajax({
                type: 'POST',
                dataType: 'html',
                url: ajax_login_object.ajaxurl,
                cache: false,
                data: {
                    'action': 'create_project', //calls wp_ajax_nopriv_ajaxlogin
                    'form': formSerialized,
                    'security': $('#security').val() },
                success: function(data){
					saveButton.prop("disabled", false);
					
					console.log("Create Project in success function.. show data:");
                    console.log(data);
					
					//Evaluate JSON - if formatted correctly display usual messages
					try {
						data = JSON.parse(data);
					} catch (e) {
						//Data is not JSON
						var origdata = data;
						//Does data contain JSON?
						var substr = data.match("{(.*)}");
						//If contains JSON
						if(substr){
							//Parse JSON to get values
							jsontext = "{"+substr[1]+"}";
							data = JSON.parse(jsontext);
							//Get the extra text and set as message so we know what PHP is writing
							var extratext = origdata.replace(jsontext, "");
							data.message = extratext;
						}else{
							//If doesn't contain JSON
							//Set the server response as the message
							var message = data;
							var data = new Object();
							data.message = message;
						 }
					 }
                    
                    if (data.success != true){
                        $form.find('.status').addClass('error-text');
                    }
					$form.find('.status').html(data.message);
                },
                error: function(xhr, ajaxOptions, thrownError){
					saveButton.prop("disabled", false);
					$form.find('.status').addClass('error-text');
                    $form.find('.status').html(xhr.status+' '+thrownError);
                    console.log("Create Project in error function.. show error:");
                    console.log(xhr.status);
                    console.log(thrownError);
                    console.log(ajaxOptions);
                }
            });
        });
		
	//Delete Project
    $('form.delete-project')
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
			//Get user to confirm before deleting anything
			var confirm_delete = confirm("Are you sure? You can't undo this");
			if (confirm_delete == true) {
				// Get the form instance
				var $form = $(e.target);
				var saveButton = $form.find('input[type="submit"]');
				var formSerialized = $form.serialize();
				
				var project_values = [];
				$form.find('[name="projectname"]').each( function() {
					if( $(this).is(':checked') ) {
						project_values.push( $(this).val() );
					}
				});
				var projects = project_values.join(',');

				var formSerialized = $form.serialize();
				formSerialized = formSerialized + "&projectname=" + projects;

				console.log('formSerialized');
				console.log(formSerialized);

				saveButton.prop("disabled", true);
				$form.find('.status').removeClass('error-text');
				$form.find('.status').show().html(ajax_login_object.loadingmessage);
				$.ajax({
					type: 'POST',
					dataType: 'html',
					url: ajax_login_object.ajaxurl,
					cache: false,
					data: {
						'action': 'delete_project', //calls wp_ajax_nopriv_ajaxlogin
						'form': formSerialized,
						'security': $('#security').val() },
					success: function(data){
						saveButton.prop("disabled", false);
						
						console.log("Create Project in success function.. show data:");
						console.log(data);
						
						//Evaluate JSON - if formatted correctly display usual messages
						try {
							data = JSON.parse(data);
						} catch (e) {
							//Data is not JSON
							var origdata = data;
							//Does data contain JSON?
							var substr = data.match("{(.*)}");
							//If contains JSON
							if(substr){
								//Parse JSON to get values
								jsontext = "{"+substr[1]+"}";
								data = JSON.parse(jsontext);
								//Get the extra text and set as message so we know what PHP is writing
								var extratext = origdata.replace(jsontext, "");
								data.message = extratext;
							}else{
								//If doesn't contain JSON
								//Set the server response as the message
								var message = data;
								var data = new Object();
								data.message = message;
							 }
						 }
						
						if (data.success != true){
							$form.find('.status').addClass('error-text');
						}
						$form.find('.status').html(data.message);
					},
					error: function(xhr, ajaxOptions, thrownError){
						saveButton.prop("disabled", false);
						$form.find('.status').addClass('error-text');
						$form.find('.status').html(xhr.status+' '+thrownError);
						console.log("Create Project in error function.. show error:");
						console.log(xhr.status);
						console.log(thrownError);
						console.log(ajaxOptions);
					}
				});
			}
        });
})(jQuery);