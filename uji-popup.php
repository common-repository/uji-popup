<?php
/*
Plugin Name: Uji Popup
Plugin URI: http://wpmanage.com/popups-ads
Description: Allown to convert visitors on your site/blog into taking an action whether its advertise, subscribing into your newsletter, offering a discount or cupon
Version: 1.4.3
Author: WPmanage
Author URI: http://wpmanage.com/
*/

    if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

	 if ( ! class_exists( 'Uji_Popups' ) ) {
                define( 'UJI_POPUP_BASE', __FILE__ );
                        
		//Functions
                require_once( 'classes/class-popups-functions.php' );
                //Uji Popup Front
                require_once( 'classes/class-popups.php' );
	 }

	
	 global $ujipopup;
	 $ujipopup = new Uji_Popups( __FILE__ );
	 $ujipopup->version = '1.4.3';
	 
?>