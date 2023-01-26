//
jQuery(document).ready(function($) {

    // Hide troubleshooting for everyone except queenbee
    var username = theUser.username;
    //console.log('username: '+username); // tft
    if ( username == 'queenbee' ) {
        //$('.troubleshooting').show();
    }
    
    // Background images
    if ($(".custom-page-background")[0]){
        
        console.log('found custom-page-background');
        
        var background_image = $("#content.custom-page-background").css("background-image");
        if ( ! background_image ) {
            return;
        } else {
            console.log('background_image: '+background_image);
        }
        
        var background_image_url_arr = $("#content.custom-page-background").css("background-image").match(/^url\("?(.+?)"?\)$/);//match(/^url\("?(.+?)"?\)$/);//match(/^url\(["']?(.+?)["']?\)$/)
        var background_image_url = background_image_url_arr[1];
        console.log('background_image_url: '+background_image_url);

        // Load image in order to get dimensions
        var img = new Image ;
        img.src = background_image_url;
        
        $(img).on("load", function() {

            var image_file_width = img.width;
            var image_file_height = img.height;
            console.log("img loaded: "+image_file_width + " x " + image_file_height) ;
            
            var background_height;
            var background_width;
            
            var bodywidth = $(document.body).width();
            var bodyheight = $(document.body).height();
            
            /*if ( image_file_width > bodywidth ) {
            } else {                
            }*/
            
            var x = image_file_width/bodywidth;
            //console.log("x = " + x);
            background_height = image_file_height/x;
            console.log("background_height = " + background_height);
            
            /*if (image_file_width > 1500) {
                var x = image_file_width/1500;
                console.log("x" + x);
                background_height = image_file_height/x;
                console.log("height" + height);
            } else {
                background_height = image_file_width;
            }*/
            
            var step1 = background_height * 0.3  +"px";
            var step2 = background_height * 0.5  +"px";
            var step3 = background_height * 0.7  +"px";
            var step4 = background_height * 0.75 +"px";
            var step5 = background_height * 0.8  +"px";
            var step6 = background_height * 0.85 +"px";

            var color1 = "rgba(255,255,255,0.0)";
            var color2 = "rgba(255,255,255,0.4)";
            var color3 = "rgba(255,255,255,0.7)";
            var color4 = "rgba(255,255,255,1.0)";
            var color5 = "rgba(255,255,255,1.0)";
            var color6 = "rgba(255,255,255,1.0)";

            //background_image_url = 'https://dev.saintthomaschurch.org/wp-content/uploads/2019/10/1559-Book-of-Common-Prayer.jpg'; // tft

            var new_background = "linear-gradient(";
            new_background += color1+" "+step1+", ";
            new_background += color2+" "+step2+", ";
            new_background += color3+" "+step3+", ";
            new_background += color4+" "+step4+", ";
            new_background += color5+" "+step5+", ";
            new_background += color6+" "+step6+"), ";
            new_background += "url('"+background_image_url+"')";

            //new_background = "url('"+background_image_url+"')"; // tft

            console.log('new_background: '+new_background);

            //$("#content.custom-page-background").css({'background-image':background_image_url});
            $("#content.custom-page-background").css("background-image", new_background ); 
            //$("#content.custom-page-background").css("background-image", "url(" + background_image_url + ")"); 
            //$('#image_element').css('background-image', 'url(http://example.com/img.jpg)');
            //$("#content.custom-page-background").css("background-image", new_background);

        });
        
    }
    
    // Webcasts
    
    if ( $("article.type-event.event-categories-webcasts")[0] 
        || $("article.type-sermon")[0] 
        || $("article.type-post.category-webcasts")[0]   
        || $("article.type-page.page_tag-webcasts")[0]        
       ) {
        
        //console.log('found webcast event'); // tft
        
        // Check cookie
        var cname = 'email_capture_attempted';
        var cvalue = getCookie(cname);
        //let cname = 'email_capture_attempted';
        //let cvalue = getCookie(cname);
        var dialog_id = "#nf_dialog";
        
        if (cvalue != "") {
        
            //console.log('cvalue of "'+cvalue+'" found for '+cname); // tft
            //alert("cookie '" + cname+"' = '" + cvalue + "'");
            
            // Hide the div
            if($(dialog_id)[0] ) {
                //console.log('hide the div: "'+dialog_id+'"'); // tft
                $(dialog_id).hide();
            }
            
        } else {
            
            //console.log('NO cvalue found for '+cname);
            
            if($(dialog_id)[0] ) {
                
                //console.log( 'Dialog content found.' ); // tft
                
                var theDialog = prepDialog( dialog_id );
                theDialog.dialog("open");
            
                if ($(dialog_id).dialog('isOpen') === true) {
                    cvalue = 'true';
                    //alert("about to set cookie '" + cname+"' = '" + cvalue + "'");
                    //console.log("about to set cookie '" + cname+"' = '" + cvalue + "'"); // tft
                    setCookie(cname, cvalue, 365);
                } else {
                    console.log( 'found webcast event' ); // tft
                    console.log( 'Uh oh! Failed to open dialog.' );
                }
                
            } else {
                //console.log( 'No dialog content found.' ); // tft
            }
            
            
            /*if ( theDialog.dialog("open") ) {
                //alert("about to set cookie '" + cname+"' = '" + cvalue + "'");
  			   setCookie(cname, 'true', 365);
            } else {
                console.log( 'Failed to open dialog' );
            }*/
         
        }
        
    }
    
    // Security Cookie
    if ( username == 'queenbee' || username == 'avery' ) {
        
        //console.log('found event');
        
        // Check cookie
        var cname = 'human_verified';
        var cvalue = getCookie(cname);
        var dialog_id = "#nf_dialog";
        
        if (cvalue != "") {
        
            console.log('cvalue of "'+cvalue+'" found for '+cname);
            //alert("cookie '" + cname+"' = '" + cvalue + "'");
            
            // Hide the div
            console.log('hide the div: "'+dialog_id);
            $(dialog_id).hide();
            
        } else {
            
            console.log('NO cvalue found for '+cname);
            
            var theDialog = prepDialog( dialog_id );
            
            theDialog.dialog("open");
            
            if ($(dialog_id).dialog('isOpen') === true) {
                //alert("about to set cookie '" + cname+"' = '" + cvalue + "'");
                setCookie(cname, 'true', 365);
            } else {
                console.log( 'Failed to open dialog' );
            }
         
        }
        
    }
    
    // Flex Grid images -- Responsive dimensions -- preserve 1:1 aspect ratio while maximizing width.
    //if ($(".flex-container")[0]){
    /*if ($(".flex-container.squares")[0]){
        
        console.log('found flex-container (squares)'); // tft
        
		var container_width = $("#primary").width();
		console.log('container_width: '+container_width);
        
		//if ( container_width < 578 ) {
		
			// Find all flex-boxes; reset width of box and contained image
			//$(".flex-box").each(function(){
            $(".flex-container.squares > .flex-box").each(function(){
				
                var current_box_width = $(this).width();
                
                console.log('current flex-box dimensions: '+$(this).width()+"x"+$(this).height());
                
                if ( container_width < 600 ) { // 578
                    
                    // On smaller screens, make flex box full width
                    var new_box_width = container_width; //* 0.9 * 0.99;
                    //console.log('new_box_width: '+new_box_width);
				    $(this).width( new_box_width );
				    $(this).height( new_box_width );
                    
                } else {
                    
                    // On larger screens, set height to match width
                    console.log('Set height to match width.'); // tft
                    $(this).height( current_box_width );
                    
                }
                
				var img_container = $(this).children('.flex-img');
				if ( img_container ) {
					img_container.width( new_box_width );
					img_container.height( new_box_width );
				}
				
				var iframes = $(this).children('iframe');
                if ( iframes.length > 0) {
                    $(this).width( new_box_width );
                    $(this).height( 'auto' );
                    
                    iframes.each( function( index, el ) {
                        console.log('iframe! (src: '+$( el ).attr('src')+")");
                        //$( el ).addClass( "newClass" );
                        $( el ).attr('height','auto');
                        $( el ).css('margin-bottom','0');
                    });
                    
                }
				
				var imgs = img_container.children('img');
				
				if ( imgs == null || imgs == "" || imgs == 0 ){
					
					console.log('no images in this flex box');
					$(this).height( '500' );
					
				} else {
					
					imgs.each(function(){
						//console.log('img: '+$(this).attr('src'));
						$(this).width( new_box_width );
						$(this).height( new_box_width );
					});
				
				}
				
			});
			
		//} // if ( container_width < 578 ) {
        
    }*/
	
    // Toggling
    $( ".toggle_handle" ).click(function() {
        var id = $(this).attr('id');
        var item_id = id.substr(14); // e.g. toggle_handle_35381
        var target_id = "#toggle_target_"+item_id;
        console.log('item_id: '+item_id+"; target_id: "+target_id);
        $( target_id ).toggle( "fast", function() {
            // Animation complete.
        });
    });
    
    $( ".toggle_handle_secondary" ).click(function() {
        var id = $(this).attr('id');
        var item_id = id.substr(15); // e.g. toggle_handle2_35381
        var target_id = "#toggle_target_"+item_id;
        console.log('item_id: '+item_id+"; target_id: "+target_id);
        $( target_id ).toggle( "fast", function() {
            // Animation complete.
        });
    });
    
	
	
    // EM Datepicker
    
    if($('.widget_em_calendar')) {
        
        // When the Month/Year name classed as "monthyear_picker" is clicked, show the month/year select form.
        $('body').on('click','.monthyear_picker',function(){
            this.blur();
            console.log('month/year picker clicked');
			$('#em-calendar-datepicker').show();
        });
        
        $('body').on('click','#monthyear_picker_cancel',function(){
            //console.log('month/year picker cancelled');
			$('#em-calendar-datepicker').hide();
		});
        
        $('body').on('click','#monthyear_picker_submit',function(){
            //console.log('month/year picker submitted');
            $('#calendar-month-list').hide();
            document.getElementById('datepicker-form').submit();
		});
        
	}
    
    // EM Booking form
    
    /*$(document).on('submit', '.em-booking-form', function(){
        //$(this).scrollTop(0); // no dice
        //var dialog_id = $(this).parentNode.id; // no
        console.log('dialog_id: '+dialog_id);
        //$('div.em-booking-message').focus(); // nope
    });*/
    
    $(document).on('submit', '.em-booking-form', function(){
      if($('div.em-booking-message-error')) {
          console.log('found em-booking-message-error');
          var dialog_id = "#"+$(this).closest('div.dialog_content').attr('id');
          console.log('dialog_id: '+dialog_id);
          $(dialog_id).scrollTop("0");
          /*setTimeout(function(){
              $(dialog_id).scrollTop("0");
          }, 500);*/
      }
    });

    // NINJA FORMS?
                                   
	//jQuery(document).on( 'nfFormReady', function( e ) {
	jQuery(document).on( 'nfFormReady', function( e, layoutView ) {
	
		// Get the container element
		var form_container = document.getElementById('nf-form-17_1-cont');
		if (form_container) { 

			console.log('form_container is a: '+form_container.tagName);

			//var the_divs = form_container.getElementsByTagName("div");
			//console.log('form_container contains: '+the_divs.length+" divs");

			var the_forms = form_container.getElementsByTagName("form");
			console.log('form_container contains: '+the_forms.length+" forms");
			
			var the_form = form_container.getElementsByTagName("form")[0];
			//console.log('the_form: '+the_form);
			
			
			/*$(document).on("wheel", "input[type=text]", function (e) {
				$(this).blur();
			});*/
			
			// https://stackoverflow.com/questions/9712295/disable-scrolling-on-input-type-number
			
			// This version has no apparent effect
			/*$(the_form).on('focus', 'input[type=text]', function (e) {
			  $(this).on('wheel.disableScroll', function (e) {
				e.preventDefault();
			  });
			});
			
			$('form').on('blur', 'input[type=text]', function (e) {
			  $(this).off('wheel.disableScroll');
			});
			*/
            
			var the_inputs = the_form.getElementsByTagName("input");
			//var the_inputs = the_form.getElementsByTagName('input[type="text"]');
			console.log('form_container contains: '+the_inputs.length+" inputs");

			if (the_inputs.length > 0) {
				fix_inputs(the_inputs);
			}

		}

		function fix_inputs(inputs) {

			console.log('about to try to fix_inputs');

			for (var i = 0; i < inputs.length; i++) {
				var input = inputs[i];
				//var input_name = input.name;
				var input_id = input.id;
				var input_type = input.type;

				//console.log('input_name: '+input_name);
				if (input_type === "text") { 
					console.log('[text] input_id: '+input_id);
					//input.addEventListener('wheel', stopWheel, {passive: false});
					input.addEventListener('wheel', stopWheel, false);
					//input.addEventListener('wheel', do_not_wheel);
				}
				//console.log('input_type: '+input_type);
				
			}

		}

		function stopWheel(e){
			if(!e){ e = window.event; } /* IE7, IE8, Chrome, Safari */
			if(e.preventDefault) { e.preventDefault(); } /* Chrome, Safari, Firefox */
			e.returnValue = false; /* IE7, IE8 */
			
			console.log(e);
			console.log("tried to stop the wheel");
		}

	});
	

	/*** V1 ***/
	
	function handleinputs(items) {
		
		console.log('testing function: '+handleinputs);
		
		for (var i = 0; i < items.length; i++) {
			
			var item = items[i];
			var item_name = item.name;
			var item_id = item.id;
			//var item_name = item.getAttribute('name');
			
			console.log('input_name: '+input_name);
			console.log('input_id: '+input_id);
			
			console.log('item: '+item.getAttribute('name'));

			item.addEventListener('wheel', do_not_wheel);
		}
	}
	
	/*** END V1 ***/

    /*** FORM FUNCTIONS ***/
    
    $("#form_reset").click(function() {
        
		//alert("click"); // tft
        $('form input:not([type="submit"]').each(function(){
			$(this).val( "" );
        });
        $("form select").each(function(){
			$(this).val( $(this).find("option:first").val() );
        });
        
    });
    
	$("#reset").click(function() {
		//alert("click"); // tft
        $("#filter-form select").each(function(){
			$(this).val( $(this).find("option:first").val() );
        });
    });
    
	
    /**** Events Calendar ***/

    function getModalDimensions() {

        var winwidth = $(window).width();
        var winheight = $(window).height();
        var bodywidth = $(document.body).width();
        var bodyheight = $(document.body).height();

        var modalwidth;
        var modalheight;
        //var modalposition;
        var modal_at;
        //console.log('winwidth: '+winwidth+'; winheight: '+winheight+'; bodywidth: '+bodywidth+'; bodyheight: '+bodyheight);

        //alert ("window dimensions: "+winwidth+" x "+winheight);
        if ( winwidth > 1300) {
            modalwidth = winwidth * 0.6;
        } else if ( winwidth > 800) {
            modalwidth = winwidth * 0.8;
        } else if ( winwidth > 400) {
            modalwidth = winwidth * 0.75;
        } else {
            modalwidth = winwidth * 0.99;
        }

        if ( winheight > 1200) {
            modalheight = winheight * 0.7;
            modal_at = "center top+25%";
        } else if ( winheight > 800) {
            modalheight = winheight * 0.75;
            modal_at = "center top+25%";
        } else if ( winheight > 400) {
            modalheight = winheight * 0.75;
            modal_at = "center top+25%";
        } else {
            modalheight = winheight * 0.8;
            modal_at = "center top+10%";
        }

        modalwidth = Math.round(modalwidth);
        modalheight = Math.round(modalheight);

        if ( modalheight > 500 ) { modalheight = 500; }
        //alert ("modal_at: "+modal_at+" ("+modalwidth+" x "+modalheight+")"); // tft
        //console.log('modalwidth: '+modalwidth+'; modalheight: '+modalheight);

        var dimensions = { height:modalheight, width:modalwidth, modal_at:modal_at };

        return dimensions;

    }

    function prepDialog( dialog_id ) {

		//console.log('about to prepDialog for dialog_id: '+dialog_id); // tft
		
        var modalDimensions = getModalDimensions();
        var modalwidth = modalDimensions["width"];
        var modalheight = modalDimensions["height"];
        var modal_at = modalDimensions["modal_at"];

        var theDialog = $(dialog_id).dialog({            
            autoOpen: false,
            modal: true,
            height: modalheight,
            width: modalwidth,
            closeOnEscape: true,
            position: { my: "center top", at: modal_at, of: window }
            //position: { my: "center top", at: "center top+25%", of: window }
        });

        return theDialog;

    }
    
    // Pop-up dialog for Day Titles and other modal content
    $(function() {
        
        var handle_id;
        var dialog_id;
        
        $("a.dialog_handle").on("click", function() {
        
        	console.log('click registered on a dialog_handle link');
            
            //var isOpen = false, dialogOpen = false;
            
            // Get the handle_id so as to open the correct dialog -- there may be multiple instances per page (e.g. day title links in event/sermon lists)
            var handle_id = $(this).attr('id');
            var item_id = handle_id.substr(14); // e.g. dialog_handle_35381
            var dialog_id = "#dialog_content_"+item_id;
            //dialog_id = "#day_title_"+handle_id; // old
            console.log('handle_id: '+handle_id);
            console.log('dialog_id: '+dialog_id);
            
            var theDialog = prepDialog( dialog_id );
            
            theDialog.dialog("open");
            
        });
        
        $(document.body).on("click", ".ui-widget-overlay", function() {
        
        	console.log('click registered on a widget overlay element');
        	
            $.each($(".ui-dialog"), function() {
                var $dialog;
                $dialog = $(this).children(".ui-dialog-content");
                if($dialog.dialog("option", "modal")) {
                    $dialog.dialog("close");
                }
            });
        });
        
        $(window).resize(function() {
            
            var modalDimensions = getModalDimensions();
            var modalwidth = modalDimensions["width"];
            var modalheight = modalDimensions["height"];
            //var modal_at = modalDimensions["modal_at"];
            
            $.each($(".ui-dialog"), function() {
                var $dialog;
                $dialog = $(this).children(".ui-dialog-content");
                if ($dialog.dialog("option", "modal")) {
                    $dialog.dialog( "option", "width", modalwidth );
                    $dialog.dialog( "option", "height", modalheight );
                    //$dialog.dialog("close");
                }
            });
            
        });
        
    });
	
});