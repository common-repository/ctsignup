<?php
/*
Plugin Name: CTSignup
Plugin URI: https://www.calculatietool.com
Description: Calculatietool Signup and contact form client for CalculatieTool.com
Version: 2.0
Author: CalculatieTool.com
Author URI: https://www.calculatietool.com
License: BSD
*/

// Make sure we don't expose any info if called directly
if ( !function_exists( 'add_action' ) ) {
	exit;
}

define( 'CTSINGUP_VERSION', '2.0' );
define( 'CTSINGUP__MINIMUM_WP_VERSION', '3.2' );
define( 'CTSINGUP__PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'CTSINGUP__INCLUDE_DIR', CTSINGUP__PLUGIN_DIR . 'inc/' );

require_once( ABSPATH . WPINC . '/pluggable.php' );
require_once( CTSINGUP__PLUGIN_DIR . 'class.ct.php' );

add_action( 'init', array( 'CalculatieTool', 'init') );

// Direct requests to observers
CalculatieTool::helper();

