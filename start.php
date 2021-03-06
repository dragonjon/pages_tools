<?php
/**
 * The main plugin file
 */

if (!defined('DOMPDF_ENABLE_AUTOLOAD')) {
	define('DOMPDF_ENABLE_AUTOLOAD', false);
}

@include_once(dirname(__FILE__) . '/vendor/autoload.php');

require_once(dirname(__FILE__) . '/lib/functions.php');
require_once(dirname(__FILE__) . '/lib/hooks.php');
require_once(dirname(__FILE__) . '/lib/events.php');

// register default Elgg events
elgg_register_event_handler('init', 'system', 'pages_tools_init');

/**
 * Called during system init
 *
 * @return void
 */
function pages_tools_init() {
	// register DOM PDF as a library
	elgg_register_library("dompdf", dirname(__FILE__) . "/vendor/dompdf/dompdf/dompdf_config.inc.php");
	
	// extend site css
	elgg_extend_view("css/elgg", "css/pages_tools/site");
	
	// extend site js
	elgg_extend_view("js/elgg", "js/pages_tools/site");
	
	// register JS library
	elgg_register_js("jquery.tree", elgg_get_site_url() . "mod/pages_tools/vendors/jstree/jquery.tree.min.js");
	elgg_register_css("jquery.tree", elgg_get_site_url() . "mod/pages_tools/vendors/jstree/themes/classic/style.css");
	
	elgg_register_ajax_view('pages_tools/export');
	
	// add widgets (overrule default pages widget, to add group support)
	elgg_register_widget_type("pages", elgg_echo("pages"), elgg_echo("pages:widget:description"), array("profile", "dashboard", "groups"));
	elgg_register_widget_type("index_pages", elgg_echo("pages"), elgg_echo("pages_tools:widgets:index_pages:description"), array("index"), true);
	
	// register plugin hooks
	elgg_register_plugin_hook_handler("register", "menu:entity", "pages_tools_entity_menu_hook");
	elgg_register_plugin_hook_handler("permissions_check:comment", "object", "pages_tools_permissions_comment_hook");
	elgg_register_plugin_hook_handler("widget_url", "widget_manager", "pages_tools_widget_url_hook");
	elgg_register_plugin_hook_handler("cron", "daily", "pages_tools_daily_cron_hook");
	
	// events
	elgg_register_event_handler('create', 'object', 'pages_tools_cache_handler');
	elgg_register_event_handler('update', 'object', 'pages_tools_cache_handler');
	elgg_register_event_handler('delete', 'object', 'pages_tools_cache_handler');
	
	// register actions
	elgg_register_action("pages/export", dirname(__FILE__) . "/actions/export.php", "public");
	elgg_register_action("pages/reorder", dirname(__FILE__) . "/actions/reorder.php");
	
	elgg_register_action("pages_tools/update_edit_notice", dirname(__FILE__) . "/actions/update_edit_notice.php");
	
	// overrule action
	elgg_register_action("pages/edit", dirname(__FILE__) . "/actions/pages/edit.php");
	elgg_register_action("pages/delete", dirname(__FILE__) . "/actions/pages/delete.php");
}
