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
 * @file    : wpmybackup-hooks.php $
 * 
 * @id      : wpmybackup-hooks.php | Tue Feb 7 08:55:11 2017 +0100 | eugenmihailescu <eugenmihailescux@gmail.com> $
*/

namespace MyBackup;

require_once __DIR__ . '/WPMyBackupClass.php';
function admin_page_menu()
{
add_management_page(PLUGIN_EDITION, PLUGIN_EDITION, 'manage_options', WPMYBACKUP_DASHBOARD_PAGE, function () {
$mybackup_object = new WPMyBackupClass();
is_session_started();
$mybackup_object->load_dashboard();
});
}
function do_register_setting()
{
register_setting(WPMYBACKUP_LOGS . '_options_group', WPMYBACKUP_OPTION_NAME);
}
function customize_schedule($schedules)
{
$schedules['weekly'] = array(
'interval' => 604800,
'display' => WPMYBACKUP . ' ' . _esc('weekly')
);
$schedules['monthly'] = array(
'interval' => 2592000,
'display' => WPMYBACKUP . ' ' . _esc('monthly')
);
return $schedules;
}
function plugin_settings_link($links)
{
$plugin_links = array(
'<a href="' . external_backup_link() . '">' . _esc('Settings') . '</a>'
);
return array_merge($plugin_links, $links);
}
function plugin_row_meta($plugin_meta, $plugin_file = null, $plugin_data = null, $status = null)
{
if (basename(dirname(__DIR__)) . DIRECTORY_SEPARATOR . 'WPMyBackup.php' == $plugin_file) {
$plugin_meta['faq'] = sprintf('<a href="%s" target="_blank">%s</a>', APP_PLUGIN_FAQ_URI, _esc('FAQ'));
$plugin_meta['docs'] = sprintf('<a href="%s" target="_blank">%s</a>', APP_ADDONS_SHOP_URI . 'tutorials', _esc('Docs'));
! defined(__NAMESPACE__.'\\APP_LICENSE') && $plugin_meta['pro'] = sprintf('<a href="%scomparison" target="_blank" style="color:#FF5950;font-weight:bold">%s</a>', APP_ADDONS_SHOP_URI, _esc('Upgrade to PRO'));
}
return $plugin_meta;
}
function external_backup_link()
{
return menu_page_url(WPMYBACKUP_DASHBOARD_PAGE, false);
}
function scheduled_external_backup()
{
return false !== wp_next_scheduled(WPCRON_SCHEDULE_HOOK_NAME);
}
function echo_backup_status($status)
{
echo '<ul style="list-style: inside none square;"><li>', $status, '</li></ul>';
}
function plugin_copy_backup($destination = '')
{
if ($destination) {
echo_backup_status(sprintf(_esc('backup the folder: %s'), LOG_DIR));
if (! copy_folder(LOG_DIR, $destination)) {
return new \WP_Error('copy_folder', WPMYBACKUP . ' - ' . sprintf(_esc('%s could not be copied at %s.'), LOG_DIR, $destination));
}
} else {
return new \WP_Error('invalid_argument', 'plugin_copy_backup argument should not be empty');
}
return true;
}
function is_our_plugin($plugin_main_file)
{
if (_file_exists($plugin_main_file)) {
$file_data = '';
if ($fp = fopen($plugin_main_file, 'r')) {
$file_data = fread($fp, 8192); 
fclose($fp);
}
return preg_match('/' . sprintf('(namespace\s+%s\s*;|class\s+%s\s*{)', __NAMESPACE__, preg_replace('/' . __NAMESPACE__ . '\\\/', '', __CLASS__)) . '/', $file_data);
}
return false;
}
function upgrader_pre_install($source, $remote_source, $upgrader, $hook_extra)
{
if (is_wp_error($source) || ! (isset($hook_extra['plugin']) || isset($hook_extra['theme']))) {
return $source;
}
$is_plugin = isset($hook_extra['plugin']);
do_backup_before_upgrade($source, $hook_extra[$is_plugin ? 'plugin' : 'theme'], $is_plugin);
$plugin_main_file = addTrailingSlash($source) . 'WPMyBackup.php';
if (is_our_plugin($plugin_main_file) && true !== ($success = plugin_copy_backup(trailingslashit($source) . 'tmp' . DIRECTORY_SEPARATOR . 'logs')))
return $success;
return $source;
}
function upgrader_post_install($response, $hook_extra, $result)
{
if (is_wp_error($response)) {
return $response;
}
$plugin_main_file = 'WPMyBackup.php';
if (isset($result) && isset($result['source_files']) && in_array($plugin_main_file, $result['source_files']) && is_our_plugin(addTrailingSlash($result['destination']) . $plugin_main_file)) {
echo_backup_status(sprintf(_esc('%s restored successfully at %s'), 'tmp/logs', LOGS_PATH));
}
do_backup_after_upgrade($result);
return $result;
}
function get_cron_schedule()
{
is_session_started();
$session_key = 'wp_cron_check';
if (isset($_SESSION[$session_key]) && (time() - $_SESSION[$session_key] < 60))
return;
add_session_var($session_key, time());
$settings = get_option(WPMYBACKUP_OPTION_NAME);
return isset($settings['schedule_enabled']) && strToBool($settings['schedule_enabled']) && isset($settings['schedule_grp']) && 'os_cron' != $settings['schedule_grp'] && isset($settings['schedule_wp_cron']) ? $settings['schedule_wp_cron'] : false;
}
function on_wordpress_load()
{
include INC_PATH . 'globals.php';
add_session_var(SIMPLELOGIN_SESSION_LOGGED, is_user_logged_in() && is_admin());
$schedule = get_cron_schedule();
if (false !== $schedule) {
add_filter(WPCRON_SCHEDULE_HOOK_NAME . '_filter', function () use (&$schedule) {
return $schedule;
});
add_action(WPCRON_SCHEDULE_HOOK_NAME, function () use (&$schedule) {
add_filter(WPCRON_SCHEDULE_HOOK_NAME . '_last_filter', function () use (&$schedule) {
return $schedule;
});
$mybackup_object = new WPMyBackupClass();
is_session_started();
$mybackup_object->run_backup(_esc('WP-Cron schedule'));
});
}
(is_dashboard() || is_admin_ajax() || defined(__NAMESPACE__.'\\DOING_CRON') || isset($_GET['doing_wp_cron'])) && (include_once CLASS_PATH . 'regactions.php');
}
function enqueue_admin_scripts_styles()
{
enqueue_admin_scripts();
enqueue_admin_styles();
}
function enqueue_admin_styles($styles = null)
{
$styles || $styles = array(
'admin',
'admin1'
);
for ($i = 0; $i < count($styles); $i ++) {
$css_file = CSS_PATH . WPMYBACKUP_LOGS . '-' . $styles[$i] . '.css';
if (_file_exists($css_file)) {
$css_handle = WPMYBACKUP_LOGS . "_options_stylesheet$i";
wp_register_style($css_handle, plugins_url(str_replace(ROOT_PATH, '', $css_file), __DIR__), false, mybackup_plugin_get_version());
wp_enqueue_style($css_handle);
}
}
}
function enqueue_admin_scripts($scripts = null)
{
$i = 0;
$scripts || $scripts = array(
'globals' => null,
'admin' => array(
'globals'
),
'regex-utils' => null,
'chunk-uploader' => array(
'globals'
),
'dashboard' => array(
'globals',
'chunk-uploader'
)
);
$keys = array_keys($scripts);
$kdepends = $scripts;
array_walk($kdepends, function (&$item, $key) use (&$keys) {
is_array($item) && array_walk($item, function (&$value, $key) use (&$keys) {
$i = array_search($value, $keys);
$value = WPMYBACKUP_LOGS . "_options_script$i";
});
});
foreach ($scripts as $sufix => $depends) {
$js_file = JS_PATH . WPMYBACKUP_LOGS . '-' . $sufix . '.js';
if (_file_exists($js_file)) {
$js_handle = WPMYBACKUP_LOGS . "_options_script$i";
wp_register_script($js_handle, plugins_url(str_replace(ROOT_PATH, '', $js_file), __DIR__), empty($kdepends[$sufix]) ? false : $kdepends[$sufix], mybackup_plugin_get_version(), false);
wp_enqueue_script($js_handle);
$i ++;
}
}
}
function is_dashboard()
{
if (! \is_admin())
return false;
$bn = basename($_SERVER['SCRIPT_NAME']);
if (in_array($bn, array(
'admin.php',
WP_OPTIONS_PAGE
))) {
if (WP_OPTIONS_PAGE == $bn) {
parse_str($_SERVER['QUERY_STRING'], $query);
return isset($query['page']) && WPMYBACKUP_DASHBOARD_PAGE == $query['page'];
}
if (isset($_SERVER['HTTP_REFERER']) && (false !== ($query = parse_url($_SERVER['HTTP_REFERER'], PHP_URL_QUERY)))) {
parse_str($query, $query);
return isset($query['page']) && WPMYBACKUP_DASHBOARD_PAGE == $query['page'];
}
}
if (WP_ADMINAJAX_PAGE == $bn) {
return is_dashboard_ajax();
}
return false;
}
function is_admin_ajax()
{
if ((defined('\DOING_AJAX') && DOING_AJAX) || (WP_ADMINAJAX_PAGE == basename($_SERVER['SCRIPT_NAME'])))
return isset($_POST['action']);
return false;
}
function is_dashboard_ajax()
{
if (is_admin_ajax() && isset($_POST['nonce'])) {
if (! ($action_exists = method_exists(__NAMESPACE__ . '\\ActionHandler', $_POST['action']))) {
include_once UTILS_PATH . 'session.php';
is_session_started();
$session_key = 'mynix_addonfunc_cache';
$session_key_timeout = 'mynix_addonfunc_timeout';
if (! (isset($_SESSION[$session_key_timeout]) && isset($_SESSION[$session_key]) && is_array($_SESSION[$session_key])) || (time() - $_SESSION[$session_key_timeout] > SECDAY)) {
add_session_var($session_key_timeout, time());
add_session_var($session_key, glob(ADDONFUNC_PATH . '*.php'));
}
foreach ($_SESSION[$session_key] as $source_file) {
if ($action_exists = $_POST['action'] == preg_replace('/(.*)\.php/', '$1', basename($source_file)))
break;
}
}
return $action_exists;
}
return false;
}
function do_register_wpmu_settings()
{
if (! is_wpmu_admin())
return;
$wpmu_wrkdir = get_site_option('wpmu_wrkdir', _sys_get_temp_dir() . WPMYBACKUP_LOGS);
if ($wpmu_wrkdir_status = ($tmpname = tempnam($wpmu_wrkdir, WPMYBACKUP_LOGS)) && unlink($tmpname)) {
$status_color = 'green';
$status_str = 'working';
} else {
$status_color = 'red';
$status_str = error_get_last();
$status_str = $status_str['message'];
}
?>
<h3 id="<?php echo WPMYBACKUP_LOGS;?>"><?php
echo WPMYBACKUP;
?></h3>
<table id="menu" class="form-table">
<tr>
<th scope="row"><?php echo _esc( 'Global working directory' ); ?></th>
<td><input name="wpmu_wrkdir" id="wpmu_wrkdir" type="text" aria-describedby="wpmu_wrkdir-desc"
value="<?php echo esc_attr( $wpmu_wrkdir); ?>" size="40" />
<?php printf('<span style="color:%s">%s</span>',$status_color,$status_str);?>
<p class="description" id="wpmu_wrkdir-desc">
<?php _esc( 'The backup working directory for all blogs (each blog will have its own subdirectory).' )?>
</p></td>
</tr>
</table>
<?php
}
function save_wpmu_settings()
{
if (! is_wpmu_admin())
return;
$wpmu_wrkdir = 'wpmu_wrkdir';
isset($_POST[$wpmu_wrkdir]) && update_site_option($wpmu_wrkdir, sanitize_text_field($_POST[$wpmu_wrkdir]));
}
function do_backup_before_upgrade($source, $key, $is_plugin)
{
$opts = get_option(WPMYBACKUP_OPTION_NAME);
$wp_core_backup = isNull($opts, 'wp_core_backup', false);
if (! strToBool($wp_core_backup))
return;
$success = false;
$success_msg = array(
false => array(
_esc('failed'),
'red'
),
true => array(
_esc('done'),
'green'
)
);
if ($is_plugin) {
$plugins = \get_plugins(DIRECTORY_SEPARATOR . dirname($key));
$plugin_data = $plugins[basename($key)];
$name = $plugin_data['Name'];
$version = $plugin_data['Version'];
$root = WP_PLUGIN_DIR . DIRECTORY_SEPARATOR . dirname($key);
} else {
$func_name = version_compare(get_bloginfo('version', 'display'), '3.4-dev', '<') ? 'get_theme' : 'wp_get_theme';
if (_function_exists($func_name)) {
$theme_obj = call_user_func($func_name, $key);
$theme_obj->get_theme_root();
$name = $theme_obj->get('Name');
$version = $theme_obj->get('Version');
$root = $theme_obj->theme_root . DIRECTORY_SEPARATOR . $theme_obj->stylesheet;
}
}
printf(_esc('%s will backup the %s %s %s before WP updates it'), '<strong>' . WPMYBACKUP . '</strong>', '<strong>' . $name . '</strong>', $version, $is_plugin ? _esc('plugin') : _esc('theme'));
$opts = array(
'dir' => $root,
'excludedirs' => '',
'excludefiles' => '',
'excludeext' => '',
'plugin_backup' => true
);
$mybackup_object = new WPMyBackupClass($opts);
is_session_started();
$success = false !== $mybackup_object->run_backup(_esc('WP Upgrader Hook'));
echo ' : ', getSpan($success_msg[$success][0], $success_msg[$success][1]), '<br>';
}
function do_backup_after_upgrade($result)
{
$plugins = \get_plugins(DIRECTORY_SEPARATOR . $result['destination_name']);
$plugin_data = current($plugins);
unset_session_vars();
}
function core_upgrade_preamble()
{
if (! current_user_can('update_core'))
return;
$opts = get_option(WPMYBACKUP_OPTION_NAME);
$wp_core_backup = isNull($opts, 'wp_core_backup', false);
$sql_db = isNull($opts, 'mysql_enabled', false);
$container = 'mybackup_core_backup';
$action = $container;
?>
<table id="<?php echo $container;?>" class="widefat" style="border-left: 4px solid #00ADEE;">
<thead>
<tr>
<td colspan="2">
<h3><?php _pesc('Automatically backup plug-ins/themes before WordPress Updates');?></h3>
</td>
</tr>
</thead>
<tr>
<th scope="row" class="check-column"><input id="wp_core_backup" name="wp_core_backup" type="checkbox"
<?php echo ' ',strToBool($wp_core_backup)?'checked':'';?>></th>
<td>
<p><?php
printf(_esc('Create a %s of the below plug-ins/themes %sbefore WordPress update them.'), '<strong>' . _esc('safe copy') . '</strong>', $sql_db ? '+ MySQL ' . _esc('db') . ' ' : '');
echo ' ' . readMoreHere(APP_ADDONS_SHOP_URI) . '.';
?></p>
</td>
</tr>
</table>
<script type='text/javascript'>
function wp_core_backup_change(sender){
jQuery.post(ajaxurl,{action:'<?php echo $action;?>',wp_core_backup:sender.target.checked,nonce:'<?php echo wp_create_nonce_wrapper($action);?>'});
}
jQuery('#<?php echo $container;?>').appendTo('.wrap p:first');
jQuery('#wp_core_backup').change(wp_core_backup_change);
</script>
<?php
}
function do_admin_head()
{
include_once INC_PATH . 'head.php';
}
function site_admin_notice()
{
$is_multisite = is_multisite_wrapper();
if (! (($is_multisite && is_wpmu_admin()) || is_administrator())) {
return false;
}
$dashnotice_file = LOG_PREFIX . '-dashnotice.tmp';
$visible = ! is_file($dashnotice_file) || ((filesize($dashnotice_file) < 11) && intval(file_get_contents($dashnotice_file)) < time());
if ($visible && ! get_site_option('wpmu_wrkdir')) {
$remind_caption = _esc('Remind me later');
$network_settings_link = getAnchor(_esc('Network Settings'), network_admin_url('settings.php') . '#' . WPMYBACKUP_LOGS);
$mybackup_dismiss_dashboard_action = 'mybackup_dismiss_dashboard_notice';
$mybackup_dismiss_dashboard_nonce = wp_create_nonce_wrapper($mybackup_dismiss_dashboard_action);
$mybackup_hide_dashboard_notice = sprintf("jQuery('.updated.mybackup').slideUp();jQuery.post(ajaxurl,{action:'%s',nonce:'%s',days:%%d});", $mybackup_dismiss_dashboard_action, $mybackup_dismiss_dashboard_nonce);
?>
<div class='updated mybackup' style="display: block; border-color: #00adee;">
<table style="margin-bottom: 15px">
<tr>
<td style="padding-top: 20px; vertical-align: top;"><a href="<?php echo APP_PLUGIN_URI;?>" target="_blank"><img
style="box-shadow: 0 0 5px #888;" src="https://ps.w.org/wp-mybackup/assets/icon-128x128.png"></a></td>
<td style="padding-left: 10px;"><h3><?php printf(_esc('Thank you for choosing %s'),WPMYBACKUP);?></h3>
<p><?php
if ($is_multisite && is_wpmu_admin()) {
printf(_esc('Before you start using %s please configure the %s option in %s page. It is the root where all sites will have their own working directory.'), WPMYBACKUP, '<span style="font-weight:bold">' . _esc('Global working directory') . '</span>', $network_settings_link);
} else {
global $TARGET_NAMES;
printf(_esc('Before you start using %s please take a look at %s page.'), WPMYBACKUP, getAnchor(_esc('How it works'), external_backup_link() . '&tab=' . $TARGET_NAMES[APP_WELCOME] . '&nocheck', '_self'));
}
?></p>
<p><?php _pesc('Here are few things you might be interested in:');?></p>
<ol>
<li><?php echo getAnchor(sprintf(_esc('Getting started with %s'),WPMYBACKUP), APP_ADDONS_SHOP_URI.'getting-started-with-mybackup');?></li>
<li><?php echo getAnchor(_esc('Frequently Asked Questions (FAQ)'), APP_PLUGIN_FAQ_URI);?></li>
<?php
if (! defined(__NAMESPACE__.'\\APP_LICENSE')) {
?>
<li><?php echo getAnchor(_esc('Get the <span style="color:#FF5950;font-weight:bold">PRO version</span>'), APP_ADDONS_SHOP_URI.'shop/wpmybackup-pro').'. '._esc('Right now it is only $3,99 (time limited offer)');?></li>
<?php
}
?>
</ol></td>
</tr>
<tr>
<td></td>
<td><input type="button" class="button" value="<?php _pesc('Got it');?>"
onclick="<?php printf($mybackup_hide_dashboard_notice,365);?>" title="<?php _pesc('Dismiss it for 1 year');?>"> <input
type="button" class="button" value="<?php echo $remind_caption;?>"
title="<?php printf(_esc('%s on %d days'),$remind_caption,DASHBOARD_REMINDER);?>"
onclick="<?php printf($mybackup_hide_dashboard_notice,DASHBOARD_REMINDER);?>"></td>
</tr>
</table>
</div>
<?php
}
}
?>