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
 * @file    : WPMyBackupClass.php $
 * 
 * @id      : WPMyBackupClass.php | Tue Feb 7 08:55:11 2017 +0100 | eugenmihailescu <eugenmihailescux@gmail.com> $
*/

namespace MyBackup;

class WPMyBackupClass
{
private $_logfile;
private $cron_schedule;
private $settings;
private function _validate_install()
{
$wp_version = get_bloginfo('version');
$php_ok = version_compare(PHP_VERSION, SUPPORT_MIN_PHP, '>=');
$wp_ok = version_compare($wp_version, SUPPORT_MIN_WP, '>=');
if (! ($php_ok && $wp_ok)) {
deactivate_plugins(basename(__FILE__));
if (isset($_GET['action']) && ($_GET['action'] == 'activate' || $_GET['action'] == 'error_scrape')) {
wp_die(sprintf(_esc('%s requires at least PHP %s%s and WordPress %s or newer to work%s.'), WPMYBACKUP, SUPPORT_MIN_PHP, $php_ok ? '' : sprintf(_esc(' (yours = %s)'), PHP_VERSION), SUPPORT_MIN_WP, $wp_ok ? '' : sprintf(_esc(' (yours = %s)'), $wp_version)));
}
}
}
function __construct($opts = array())
{
date_default_timezone_set(wp_get_timezone_string());
$wrkdir = plugin_dir_path(__FILE__);
$this->_validate_install();
$this->settings = get_option(WPMYBACKUP_OPTION_NAME);
afterSettingsLoad($this->settings);
if (is_array($opts))
foreach ($opts as $option_key => $option_value)
$this->settings[$option_key] = $option_value;
$this->_logfile = new LogFile(JOBS_LOGFILE, $this->settings);
$this->cron_schedule = isset($this->settings['schedule_enabled']) && strToBool($this->settings['schedule_enabled']) && isset($this->settings['schedule_grp']) && 'os_cron' != $this->settings['schedule_grp'] && isset($this->settings['schedule_wp_cron']) ? $this->settings['schedule_wp_cron'] : false;
}
function run_backup($sender = '')
{
add_filter('https_ssl_verify', '__return_false');
add_filter('https_local_ssl_verify', '__return_false');
if ('os_cron' == $this->settings['schedule_grp'])
return false;
$result = false;
try {
$wpb = new WPBackupHandler(getArgFromOptions($this->settings), $sender);
$result = $wpb->run();
$job_id = $wpb->getCurrentJobId();
$wpb->addMessage(MESSAGE_TYPE_NORMAL, sprintf(_esc('New backup job run by %s'), $sender), empty($job_id) ? 0 : $job_id);
} catch ( \Exception $e) {
$this->_logfile->writelnLog(sprintf("[%s] %s", date(DATETIME_FORMAT), $e->getMessage()));
}
$this->_logfile->writelnLog(sprintf(_esc("[%s] Backup task ended"), date(DATETIME_FORMAT)));
$wpb->un_lockSession();
unset($wpb); 
return $result;
}
function load_dashboard()
{
global $java_scripts;
require_once CLASS_PATH . 'regactions.php'; 
$dashboard_class = 'ProDashboard';
$dashboard_file = CLASS_PATH . "$dashboard_class.php";
_file_exists($dashboard_file) || $dashboard_class = 'Dashboard';
require_once CLASS_PATH . "$dashboard_class.php";
$dashboard_class = __NAMESPACE__ . '\\' . $dashboard_class;
$dashboard = new $dashboard_class();
$java_scripts = array_merge($java_scripts, $dashboard->getJavaScripts());
$dashboard->show();
$footer_banner = $dashboard->getBanner('footer_banner');
include_once INC_PATH . 'footer.php';
}
}
?>