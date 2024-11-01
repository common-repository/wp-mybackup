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
 * @file    : 10-wp_lite.php $
 * 
 * @id      : 10-wp_lite.php | Tue Feb 7 08:55:11 2017 +0100 | eugenmihailescu <eugenmihailescux@gmail.com> $
*/

namespace MyBackup;

define(__NAMESPACE__."\\TBL_PREFIX", wp_get_db_prefix() . "_wpmybk_");
defined(__NAMESPACE__.'\\WP_PLUGINS_PAGE') || define(__NAMESPACE__.'\\WP_PLUGINS_PAGE', 'plugins.php');
define(__NAMESPACE__.'\\WP_CRON_PAGE', 'wp-cron.php');
define(__NAMESPACE__.'\\WPCRON_SCHEDULE_HOOK_NAME', WPMYBACKUP_LOGS . '_schedule');
define(__NAMESPACE__.'\\WP_SOURCE', - 4);
define(__NAMESPACE__.'\\APP_WP_SCHEDULE', 23);
define(__NAMESPACE__.'\\APP_DASHBOARD', 26);
define(__NAMESPACE__.'\\DROPIN_RESTORE', 'dropin-restore');
$REGISTERED_SCHEDULE_TABS['wp_schedule'] = 'WP';
$TARGET_NAMES = $TARGET_NAMES + array(
WP_SOURCE => 'wpsource',
APP_WP_SCHEDULE => 'wp_schedule',
APP_DASHBOARD => 'dashboard'
);
$NOT_BACKUP_TARGETS = $NOT_BACKUP_TARGETS + array(
WP_SOURCE,
APP_WP_SCHEDULE
);
registerDefaultTab(APP_DASHBOARD, 'DashboardEditor', _esc('Dashboard'));
registerTab(APP_WP_SCHEDULE, 'WPScheduleEditor', 'WP-Cron');
registerTab(WP_SOURCE, 'WPSourceEditor', IS_MULTISITE && ! SANDBOX ? _esc('Site files') : _esc('WP files'), 'getWPDirList');
insertArrayBefore($dashboard_tabs, APP_BACKUP_JOB, APP_DASHBOARD);
insertArrayBefore($dashboard_tabs, MYSQL_SOURCE, WP_SOURCE);
?>