<?php
/*
Plugin Name: GradeBook
Plugin URI: http://wpEduSuite.cole20.com/gradebook/
Description: Manage and present students grade results. A derived work from Leaguemanager plugin by Kolja Schleich.
Version: 1.0
Author: Carlos Ruiz
Author URI: http://www.cole20.com/

Copyright 2009 Carlos Ruiz  (http://www.cole20.com)

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/
if ( !defined( 'WP_CONTENT_URL' ) )
	define( 'WP_CONTENT_URL', get_option( 'siteurl' ) . '/wp-content' );
if ( !defined( 'WP_PLUGIN_URL' ) )
	define( 'WP_PLUGIN_URL', WP_CONTENT_URL. '/plugins' );
if ( !defined( 'WP_CONTENT_DIR' ) )
	define( 'WP_CONTENT_DIR', ABSPATH . 'wp-content' );
if ( !defined( 'WP_PLUGIN_DIR' ) )
	define( 'WP_PLUGIN_DIR', WP_CONTENT_DIR . '/plugins' );
	
define( 'GRADEBOOK_VERSION', '2.4.1' );
define( 'GRADEBOOK_URL', WP_PLUGIN_URL.'/gradebook' );
define( 'GRADEBOOK_PATH', WP_PLUGIN_DIR.'/gradebook' );

// Load GradeBook Class
include_once( 'gradebook.php' );

$gradebook = new WP_GradeBook();

include_once( 'functions.php' );

register_activation_hook(__FILE__, array(&$gradebook, 'activate') );
// Actions
add_action( 'admin_head', array(&$gradebook, 'addHeaderCode') );
add_action( 'wp_head', array(&$gradebook, 'addHeaderCode') );
add_action( 'admin_menu', array(&$gradebook, 'addAdminMenu') );

// Ajax Actions
add_action( 'wp_ajax_leaguemanager_show_match_date_selection', 'leaguemanager_show_match_date_selection' );

// Filters
add_filter( 'the_content', array(&$gradebook, 'insert') );


if ( function_exists('register_uninstall_hook') )
	register_uninstall_hook(__FILE__, array(&$gradebook, 'uninstall'));

// Uninstall Plugin
if ( !function_exists('register_uninstall_hook') )
	if (isset($_GET['gradebook']) AND 'uninstall' == $_GET['gradebook'] AND ( isset($_GET['delete_plugin']) AND 1 == $_GET['delete_plugin'] ) )
		$gradebook->uninstall();




?>
