jQuery( function($){

    if($('#cont_tab').length){
        //Enable Tabs
        $('#cont_tab a:first').tab('show');
            $('#cont_tab a').click(function (e) {
                e.preventDefault();
                $(this).tab('show');
                })

        //Click sub 		
       $(".radio_sub").each(function(){
          var id = $(this).attr("id");
       
            if($(this).is(':checked')){
                $("#"+id+"_sub").fadeIn('fast');
            }else{
                $("#"+id+"_sub").hide();
            }
       });
       
       $( ".radio" ).on( "click", function() {
          var id = $(this).attr("id");
       
            if($(this).is(':checked')){
                $('.options_sub').hide();
                $("#"+id+"_sub").fadeIn('fast');
            }
       });
       
       //shortcode class
       $( ".uji_class").html( $( "#pop_class" ).val());
        $( "#pop_class" ).on( "input", function() {
           var the_class = $(this).val();
           $( ".uji_class").html(the_class);
       });
       

        //Data clear
        $('.dclear').click(function(e){
            e.preventDefault();	
            $(this).parent('div').find('input').val('');	

        });
        
    }    
	
	
	//Color picker
    if (typeof colorpicker == 'function') { 
    
        $('.colorpicker').colorpicker();

        $('.colorpicker').each(function(index) {
            var initc = $(this).find("input").val();
            $(this).find("i").css("background", initc);
        });
        
    }else{
                
        $('.wpcolorpicker').wpColorPicker();
    }
    
    
	
	//Color picker
	$('#show_timer').change(function() {
		
	 	init_settings();
	});
	
	init_settings();
    
    //---------------- / Admin Terget Pages / --------------------
    
    $('#nav-menus-frame').find('h3').removeClass( "hndle" );
    
    //Fix max_input_vars limit to 1000 server congfig issue
    $('.menu-item-parent-id, .menu-item-db-id, .menu-item-url, .menu-item-target, .menu-item-attr_title, .menu-item-classes, .menu-item-xfn').remove();

    //Add target
    $('.uji-add-target').on("click", function( event ) {
        event.preventDefault();
        
        var ptype = $(this).data("type"),
            pformat = $(this).data("format"),
            pcont = ( pformat == 'pop_ids_post') ? 'posttypediv' : 'taxonomydiv',
            
            pchecks = $(this).closest( "."+pcont ),
            pchecked = [];
            
            pchecks.find("input:checkbox[class=menu-item-checkbox]:checked").each(function(){             
                var itemId = $(this).val();
                var ckItemId = false;
                $('#'+pformat+'_hold').find("input:hidden[name='"+pformat+"["+ptype+"][]']").each(function() {
                    if ($(this).val() == itemId){
                        ckItemId = true;
                        return false;
                    }
                });
               if( !ckItemId ){
                    var itemName = $(this).closest( '.menu-item-title' ).text();
                    $('#'+pformat+'_hold').append('<input type="hidden" class="pop-item-'+ptype+'-'+itemId+'" name="'+pformat+'['+ptype+'][]" value="'+itemId+'">');
                    $('#'+pformat+'_hold').append('<span><a class="tardelbutton" data-id="'+itemId+'" data-cat="'+ptype+'" data-type="'+pformat+'">X</a> '+itemName+' ('+ucfirstJs(ptype)+') </span>');
                }
                
            });
            
     });
    
    //Delete target
    $('#pop_ids_post_hold, #pop_ids_tax_hold').on("click", '.tardelbutton', function( event ) {
        event.preventDefault();
        
        var ptype  = $(this).data("type"),
            pcat  = $(this).data("cat"),
            itemId = $(this).data("id");
        
        $(this).parent('span').fadeOut( "slow", function() {
            $(this).parent().find('.pop-item-'+pcat+'-'+itemId+'').remove();
            $(this).remove();
        }); 
        
        
    });
    
    //Fix toggle
    $('h2.hndle, .handlediv').click( function(e) {
	   e.stopPropagation();
        $(this).parent().toggleClass( 'closed' );
        var ariaExpandedValue = $(this).parent().hasClass( 'closed' );
    });
    
    //---------------- / End Admin Terget Pages / --------------------
	
});

function ucfirstJs(string) {
    return string.charAt(0).toUpperCase() + string.slice(1);
}
	
//Select options:
	function init_settings(){
		var is_show = jQuery('#show_timer').val();
		var opt1 = jQuery('#countdown_time');
		var opt2 = jQuery('#wait_time');
	
		if(is_show == "yes"){
			opt1.parents('tr').show();
			opt2.parents('tr').show();
			
		}
		else if(is_show == "no"){
			opt1.parents('tr').hide();
			opt2.parents('tr').hide();
			
		}
	}

	