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
    
    // Msg bar
    // TBD: how and when to re-open it once the user has closed it?
    // >> check to see if post_id cookie is set and current post_id matches. If not, show msg_bar and set cookie with current post_id
    
    if ( $('#msg_bar') ) {
    	
    	console.log('>> msg_bar <<<');
    	
    	var show_msg_bar = false;
    	$("#msg_bar").hide();
    	
		// Get the msg_bar post_id
		var post_id = $('#msg_bar div.featured-post').attr('id');	
    	if ( post_id ) { console.log('post_id: '+post_id); } else { post_id = null; console.log('no post_id defined'; }
		
		// Check cookie
		var sdg_featured_post = getCookie('sdg_featured_post');
		
		if ( !empty(sdg_featured_post) ) {
	
			console.log('sdg_featured_post: '+sdg_featured_post);
			
			var sdg_user_closed_msg_bar = getCookie('sdg_user_closed_msg_bar');
			//console.log('sdg_user_closed_msg_bar: '+sdg_user_closed_msg_bar);
			
			if ( sdg_user_closed_msg_bar ) {
				console.log('sdg_user_closed_msg_bar');
			} else {
				show_msg_bar = true;
			}
			
			// Compare cvalue with post_id
			if ( sdg_featured_post == post_id ) {				
				console.log('sdg_featured_post == post_id');				
			} else {
				console.log('sdg_featured_post ('+sdg_featured_post+') NE post_id ('+post_id+')');
				console.log('setCookie sdg_featured_post');
				setCookie('sdg_featured_post', post_id, 365);
				show_msg_bar = true;
			}
			
			if ( show_msg_bar == true ) {
				console.log('show_msg_bar');
				$("#msg_bar").show();
				deleteCookie('sdg_user_closed_msg_bar');
			}
					
		} else {
		
			console.log('NO sdg_featured_post found ');
			console.log('setCookie sdg_featured_post');
			setCookie('sdg_featured_post', post_id, 365);
	 
		}
    	
	}
        
    $( ".msg_bar_close" ).click(function() {
        $("#msg_bar").hide();
        //$("#msg_bar").addClass('hidden');
        setCookie('sdg_user_closed_msg_bar', 'true', 365);
        console.log('setCookie sdg_user_closed_msg_bar');
    });
	
	
    // EM Datepicker -- Customizations
    
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
		// Reset form fields
        $(".filter-form select").each(function(){
			$(this).val( $(this).find("option:first").val() );
        });
        /*
        // Redirect to remove query vars
        let current_url = window.location.href;
		let current_path = window.location.pathname;
		let query_str = window.location.search;
		console.log( "current_url: "+current_url );
		console.log( "current_path: "+current_path );
		console.log( "query_str: "+query_str );
		//window.location.href = current_path; // redirect
		*/
    });
    
    $("#swap-ids").click(function() {
		//alert("swap!"); // tft
		// Swap field values
        var new_p2 = $("#p1_id").val();
        var new_p1 = $("#p2_id").val();
        console.log( "new_p1: "+new_p1 );
        console.log( "new_p2: "+new_p2 );
        $("#p1_id").val(new_p1);
        $("#p2_id").val(new_p2);
        
    });
    
	
    /**** Modal PopUp Windows ***/

    function prepDialog( handle_id, dialog_id ) {

		console.log('about to prepDialog for dialog_id: '+dialog_id+' with handle_id: '+handle_id);
		
		// Get modal dimensions
        var modalDimensions = getModalDimensions();
        var modal_height = modalDimensions["height"];
        var modal_width = modalDimensions["width"];
        //var modal_at = modalDimensions["modal_at"];
        
		// Determine positioning for modal window
		// --------------------------------------
		
		// Set positioning defaults
		var target_element = window; //"#content"; //var target_element = "#site-navigation"; // Which element to position against		
        var modal_anchor = "center"; //var modal_anchor = "center top"; // Defines which position on the element being positioned to align with the target element
		var modal_at = "center"; //var modal_at = "center bottom+50px"; // Defines which position on the target element to align the positioned element against

		//
		var winwidth = window.innerWidth; //$(window).width();
        var winheight = window.innerHeight; //$(window).height();
        var emwidth = winwidth/parseFloat($("body").css("font-size"));
        var scroll = $(window).scrollTop(); //var scroll = window.scrollTop();
        var offset = $(handle_id).offset(); //var offset = handle_id.offset();
        //if ( winheight < 400) { modal_at = "center bottom+10%"; }
        
        // Adjust defaults as needed based on screen size, scroll position, etc.
        /*if ( emwidth < 56  ) { // For mobile devices, effectively, where sticky header isn't sticky
        	if ( handle_id == dialog_id ) { // Is handle_id same as dialog_id? (as w/ nf_dialog)
        		// tbd
        	} else {
        		target_element = handle_id;
        	}        	
        	//modal_anchor = "center center";
        	
        } else {
        
        	if ( scroll > 100 ) {
				//modal_at = "center top+"+offset.top;
				//target_element = handle_id;
				//modal_anchor = "center center";
			}
        
        }*/
        
        //
        //console.log('emwidth: '+emwidth);
        //console.log('scroll: '+scroll);
        //console.log("handle offset top: " + offset.top + "; handle offset left: " + offset.left);
       	//console.log('-------');
        //console.log('modal_anchor: '+modal_anchor);
        //console.log('modal_at: '+modal_at);
        //console.log('target_element: '+target_element);

        var theDialog = $(dialog_id).dialog({      
        	//title: dialog_id,
        	//appendTo: "#someElem" // Which element the dialog (and overlay, if modal) should be appended to. // Default: "body"      
            autoOpen: false,
            modal: true,
            height: modal_height,
            width: modal_width,
            closeOnEscape: true,
            closeText: "x",
            // my: Defines which position on the element being positioned to align with the target element
            // at: Defines which position on the target element to align the positioned element against
            // of: Which element to position against
            position: { my: modal_anchor, at: modal_at, of: target_element, collision: "fit" }
            //position: { my: "center top", at: "center top+25%", of: window }
        });

        return theDialog;

    }
	
	//$("a.dialog_handle").on("click", function() {
	$('body').on('click','.dialog_handle',function(){
	
		console.log('click registered on a dialog_handle link');
		
		//var isOpen = false, dialogOpen = false;
		
		// Get the handle_id so as to open the correct dialog -- there may be multiple instances per page (e.g. day title links in event/sermon lists)
		var handle_id = "#"+$(this).attr('id');
		var dialog_id = handle_id.replace(/handle/g, "content");
		//var item_id = handle_id.substr(14); // e.g. dialog_handle_35381
		//var dialog_id = "#dialog_content_"+item_id;
		//dialog_id = "#day_title_"+handle_id; // old
		console.log('handle_id: '+handle_id);
		console.log('dialog_id: '+dialog_id);
		
		var theDialog = prepDialog( handle_id, dialog_id );
		
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

/**** Modal PopUp Windows ***/

// Determine modal dimensions (width, height0 based on width and height of dinwo)
function getModalDimensions() {

	console.log('about to getModalDimensions'); // tft
	
	// TODO: build in option to set dimensions based on content?
	
	var winwidth = window.innerWidth; //$(window).width();
	var winheight = window.innerHeight; //$(window).height();
	var bodywidth = document.body.clientWidth; // $(document.body).width();
	var bodyheight = document.body.clientHeight; // $(document.body).height();
	//
	var modalwidth;
	var modalheight;
	//var modal_at;
	//console.log('winwidth: '+winwidth+'; winheight: '+winheight+'; bodywidth: '+bodywidth+'; bodyheight: '+bodyheight);
	
	// Width
	if ( winwidth > 1300) {
		modalwidth = winwidth * 0.6;
		//modal_at = "center top+25%";
	} else if ( winwidth > 800) {
		modalwidth = winwidth * 0.8;
		//modal_at = "center top+25%";
	} else if ( winwidth > 400) {
		modalwidth = winwidth * 0.75;
		//modal_at = "center top+25%";
	} else {
		modalwidth = winwidth * 0.99;
		//modal_at = "center top+10%";
	}
	
	// Height
	if ( winheight > 1200) {
		modalheight = winheight * 0.7;
	} else if ( winheight > 800) {
		modalheight = winheight * 0.75;
	} else if ( winheight > 400) {
		modalheight = winheight * 0.75;
	} else {
		modalheight = winheight * 0.8;
	}
	
	//console.log('winheight: '+winheight);
	//console.log('winwidth: '+winwidth);
	//console.log('modalheight: '+modalheight);
	//console.log('modalwidth: '+modalwidth);
	
	// Round the numbers
	modalwidth = Math.round(modalwidth);
	modalheight = Math.round(modalheight);

	if ( modalheight > 500 ) { modalheight = 500; }
	//console.log("window dimensions: "+winwidth+" x "+winheight);
	//console.log("modal dimensions: "+modalwidth+" x "+modalheight);
	//alert ("modal_at: "+modal_at+" ("+modalwidth+" x "+modalheight+")"); 

	var dimensions = { height:modalheight, width:modalwidth };
	//var dimensions = { height:modalheight, width:modalwidth, modal_at:modal_at };
	
	return dimensions;

}