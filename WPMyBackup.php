<?php
/**
 * ################################################################################
 * WP MyBackup
 * 
 * Copyright 2017 Eugen Mihailescu <eugenmihailescux@gmail.com>
 * 
 * This program is free software: you can redistribute it and/or modify it under
 * the terms of the GNU General Public License as published by the Free Software
 * Foundation, either version 3 of the License, or (at your option) any later
 * version.
 * 
 * This program is distributed in the hope that it will be useful, but WITHOUT ANY
 * WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A
 * PARTICULAR PURPOSE.  See the GNU General Public License for more details.
 * 
 * You should have received a copy of the GNU General Public License along with
 * this program.  If not, see <http://www.gnu.org/licenses/>.
 * 
 * ################################################################################
 * 
 * Short description:
 * URL: http://wpmybackup.mynixworld.info
 * 
 * Git revision information:
 * 
 * @version : 1.0-3 $
 * @commit  : 1b3291b4703ba7104acb73f0a2dc19e3a99f1ac1 $
 * @author  : eugenmihailescu <eugenmihailescux@gmail.com> $
 * @date    : Tue Feb 7 08:55:11 2017 +0100 $
 * @file    : WPMyBackup.php $
 * 
 * @id      : WPMyBackup.php | Tue Feb 7 08:55:11 2017 +0100 | eugenmihailescu <eugenmihailescux@gmail.com> $
*/

namespace MyBackup;

/**
 * Plugin Name: WP MyBackup
 * Plugin URI: https://wordpress.org/plugins/wp-mybackup
 * Description: Creates, restores, encrypts and schedules your backups (full, incremental, differential) to disk, Dropbox, Google Drive, FTP(s), SCP, SFTP, WebDAV, e-mail
 * Author: Eugen Mihailescu, MyNixWorld
 * Author URI: https://profiles.wordpress.org/eugenmihailescu/#content-plugins
 * Text Domain: wpmybackup
 * Domain Path: /locale/lang/
 * Version: 1.0-3
 */
@constant('ABSPATH') || die('Use WordPress Admin interface!');
if (defined(__NAMESPACE__.'\\STARTPAGE_LOAD_TIME')) {
add_action('admin_notices', function () {
deactivate_plugins(plugin_basename(__FILE__));
printf('<div class="error"><p>%s</p></div>', _esc('It seems that there is alreay a WP MyBackup plug-in installed. Deactivate the other before activating this one.'));
});
return;
}
define(__NAMESPACE__."\\STARTPAGE_LOAD_TIME", microtime(true));
$bn = basename($_SERVER['SCRIPT_NAME']);
$dn = dirname($_SERVER['SCRIPT_NAME']);
$is_admin = is_admin();
if (! ($is_admin || (@constant('DOING_CRON') && DOING_CRON)  ||
isset($_SERVER['SHELL']) || isset($_SERVER['USER']))) {
return;
}
$is_multisite = function_exists('\\is_multisite') && \is_multisite();
$multisite_page_prefix = $is_multisite ? 'network/' : '';
$admin_page = '/wp-admin';
$is_dashboard_page = function () use (&$multisite_page_prefix, &$admin_page) {
$valid_dashboard_entries = array(
$admin_page . '/' . $multisite_page_prefix . 'index.php',
$admin_page . '/index.php'
);
return in_array($_SERVER['SCRIPT_NAME'], $valid_dashboard_entries);
};
define(__NAMESPACE__.'\\WP_OPTIONS_PAGE', 'tools.php');
define(__NAMESPACE__.'\\WP_ADMINAJAX_PAGE', 'admin-ajax.php');
define(__NAMESPACE__.'\\WP_DASHBOARD_PAGE', $admin_page . '/' . $multisite_page_prefix . 'index.php');
define(__NAMESPACE__.'\\WP_PLUGINS_PAGE', 'plugins.php');
define(__NAMESPACE__.'\\WPMU_SETTINGS_PAGE', $is_multisite ? 'settings.php' : false);
require_once __DIR__ . '/config.php'; 
! defined(__NAMESPACE__.'\\PLUGIN_EDITION') && define(__NAMESPACE__."\\PLUGIN_EDITION", WPMYBACKUP  );
define(__NAMESPACE__."\\PLUGIN_SLUG", 'wpmybackup');
define(__NAMESPACE__.'\\WPMYBACKUP_DASHBOARD_PAGE', WPMYBACKUP_ID);
defined(__NAMESPACE__.'\\WPMYBACKUP_OPTION_NAME') || define(__NAMESPACE__."\\WPMYBACKUP_OPTION_NAME", strtolower(str_replace(' ', '_', PLUGIN_EDITION)) . '_options');
require_once CLASS_PATH . 'wpmybackup-hooks.php';
add_action('admin_init', __NAMESPACE__ . '\\do_register_setting');
add_action('admin_menu', __NAMESPACE__ . '\\admin_page_menu');
$allowed_admin_pages = array(
WP_OPTIONS_PAGE,
WP_ADMINAJAX_PAGE,
WPMU_SETTINGS_PAGE,
WP_PLUGINS_PAGE
);
if ($is_admin && (! (in_array($bn, $allowed_admin_pages) || $is_dashboard_page()))) {
return;
}
if ((defined('\DOING_AJAX') && DOING_AJAX) || (WP_ADMINAJAX_PAGE == $bn)) {
if (! (isset($_POST['nonce']) && isset($_POST['action']))) {
return;
}
include_once __DIR__ . '/config/ajax-actions.php';
if (! in_array($_POST['action'], get_valid_ajax_actions())) {
return;
}
}
if (isset($_REQUEST['page']) && WP_OPTIONS_PAGE != $bn) {
return;
}
wp_cookie_constants();
require_once \ABSPATH . 'wp-admin/includes/plugin.php';
require_once \ABSPATH . \WPINC . '/pluggable.php';
if (! defined(str_replace(__NAMESPACE__.'-', '_', strtoupper(APP_SLUG)) . '_CONFIG_PATH_NOT_FOUND')) {
require_once CLASS_PATH . 'constants.php';
require_once FUNCTIONS_PATH . 'utils.php';
require_once FUNCTIONS_PATH . 'settings.php';
$widget_file = CLASS_PATH . 'wpmybackup-widget.php';
is_file($widget_file) && include_once CLASS_PATH . 'wpmybackup-widget.php';
global $wpmybackup_plugin_data;
$wpmybackup_plugin_data = @get_plugin_data(__FILE__, false, false);
function mybackup_plugin_get_version()
{
global $wpmybackup_plugin_data;
return $wpmybackup_plugin_data['Version'];
}
function on_admin_get_footer_text($text)
{
global $wpmybackup_plugin_data;
return sprintf(_esc('If you like %s please leave us a %s rating. A huge thank you from %s in advance!'), getAnchor($wpmybackup_plugin_data['Name'], $wpmybackup_plugin_data['PluginURI']), sprintf('<a href="%s" class="%s" target="_blank" %s>%s</a>', 'https://wordpress.org/support/view/plugin-reviews/wp-mybackup?filter=5#postform', 'wc-rating-link', 'data-rated="Thanks :)"', '★★★★★'), $wpmybackup_plugin_data['Author']);
}
add_action('plugins_loaded', function () {
do_action('mybackup_init_updater', __FILE__);
});
add_filter('wpmu_options', __NAMESPACE__ . '\\do_register_wpmu_settings');
add_action('update_wpmu_options', __NAMESPACE__ . '\\save_wpmu_settings');
add_filter('cron_schedules', __NAMESPACE__ . '\\customize_schedule', 10, 1);
add_filter('plugin_action_links_' . plugin_basename(__FILE__), __NAMESPACE__ . '\\plugin_settings_link');
add_filter('plugin_row_meta', __NAMESPACE__ . '\\plugin_row_meta', 10, 4);
add_filter('upgrader_source_selection', __NAMESPACE__ . '\\upgrader_pre_install', 10, 4);
add_filter('upgrader_post_install', __NAMESPACE__ . '\\upgrader_post_install', 10, 3);
add_filter('itsec_has_external_backup', '__return_true', 1000);
add_filter('itsec_external_backup_link', __NAMESPACE__ . '\\external_backup_link', 1000);
add_filter('itsec_scheduled_external_backup', __NAMESPACE__ . '\\scheduled_external_backup', 1000);
if (! (defined('\DOING_AJAX') && DOING_AJAX) && is_dashboard()) {
add_action('admin_enqueue_scripts', __NAMESPACE__ . '\\enqueue_admin_scripts_styles');
add_filter('admin_footer_text', __NAMESPACE__ . '\\on_admin_get_footer_text');
}
add_action('core_upgrade_preamble', __NAMESPACE__ . '\\core_upgrade_preamble');
$is_dashboard_page() && add_action('all_admin_notices', __NAMESPACE__ . '\\site_admin_notice');
add_action('admin_head', __NAMESPACE__ . '\\do_admin_head');
add_action('wp_loaded', __NAMESPACE__ . '\\on_wordpress_load', 0);
} else {
add_action('admin_notices', function () {
deactivate_plugins(plugin_basename(__FILE__));
printf('<div class="error"><p>%s : %s</p></div>', basename(__FILE__), @constant(__NAMESPACE__ . '\\' . str_replace('-', '_', strtoupper(APP_SLUG)) . '_CONFIG_PATH_NOT_FOUND'));
});
}
?>