!function(a){a("form.create-project").bootstrapValidator({excluded:[":disabled"],feedbackIcons:{valid:"glyphicon glyphicon-ok",invalid:"glyphicon glyphicon-remove",validating:"glyphicon glyphicon-refresh"}}).on("error.field.bv",function(e,o){}).on("success.field.bv",function(e,o){}).on("success.form.bv",function(e){e.preventDefault();var n=a(e.target),c=n.find('input[type="submit"]'),o=n.serialize();c.prop("disabled",!0),n.find(".status").removeClass("error-text"),n.find(".status").show().html(ajax_login_object.loadingmessage),a.ajax({type:"POST",dataType:"html",url:ajax_login_object.ajaxurl,cache:!1,data:{action:"create_project",form:o,security:a("#security").val()},success:function(o){c.prop("disabled",!1),console.log("Create Project in success function.. show data:"),console.log(o);try{o=JSON.parse(o)}catch(e){var s=o,t=o.match("{(.*)}");if(t){jsontext="{"+t[1]+"}",o=JSON.parse(jsontext);var a=s.replace(jsontext,"");o.message=a}else{var r=o;(o=new Object).message=r}}1!=o.success&&n.find(".status").addClass("error-text"),n.find(".status").html(o.message)},error:function(e,o,s){c.prop("disabled",!1),n.find(".status").addClass("error-text"),n.find(".status").html(e.status+" "+s),console.log("Create Project in error function.. show error:"),console.log(e.status),console.log(s),console.log(o)}})}),a("form.delete-project").bootstrapValidator({excluded:[":disabled"],feedbackIcons:{valid:"glyphicon glyphicon-ok",invalid:"glyphicon glyphicon-remove",validating:"glyphicon glyphicon-refresh"}}).on("error.field.bv",function(e,o){}).on("success.field.bv",function(e,o){}).on("success.form.bv",function(e){e.preventDefault();var n=a(e.target),c=n.find('input[type="submit"]'),o=n.serialize(),s=[];n.find('[name="projectname"]').each(function(){a(this).is(":checked")&&s.push(a(this).val())});var t=s.join(",");o=(o=n.serialize())+"&projectname="+t,console.log("formSerialized"),console.log(o),c.prop("disabled",!0),n.find(".status").removeClass("error-text"),n.find(".status").show().html(ajax_login_object.loadingmessage),a.ajax({type:"POST",dataType:"html",url:ajax_login_object.ajaxurl,cache:!1,data:{action:"delete_project",form:o,security:a("#security").val()},success:function(o){c.prop("disabled",!1),console.log("Create Project in success function.. show data:"),console.log(o);try{o=JSON.parse(o)}catch(e){var s=o,t=o.match("{(.*)}");if(t){jsontext="{"+t[1]+"}",o=JSON.parse(jsontext);var a=s.replace(jsontext,"");o.message=a}else{var r=o;(o=new Object).message=r}}1!=o.success&&n.find(".status").addClass("error-text"),n.find(".status").html(o.message)},error:function(e,o,s){c.prop("disabled",!1),n.find(".status").addClass("error-text"),n.find(".status").html(e.status+" "+s),console.log("Create Project in error function.. show error:"),console.log(e.status),console.log(s),console.log(o)}})})}(jQuery);