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
 * @file    : WPScheduleEditor.php $
 * 
 * @id      : WPScheduleEditor.php | Tue Feb 7 08:55:11 2017 +0100 | eugenmihailescu <eugenmihailescux@gmail.com> $
*/

namespace MyBackup;
class WPScheduleEditor extends ScheduleEditor {
private $_is_wpcron_disabled;
private function _getJavaScripts() {
$i = array();
foreach ( $this->_schedules as $s => $v )
$i[] = "['$s'," . $v['interval'] . "]";
$this->java_scripts[] = 'parent.globals.schedule=[' . implode( ',', $i ) . '];';
$del_wpcron_action = 'del_wpcron_schedule';
ob_start();
?>
parent.fix_datetime_input=function(id){
if(parent.supportsDateInput())
return;
var el=document.getElementById(id);
if(id){
el.type='text';
el.placeholder='YYYY-MM-DD hh:mm:ss';
}
};
parent.fix_datetime_input('schedule_wp_cron_time');
parent.del_wpcron_schedule=function(cron_hook){
var on_ready=function(){
parent.removePopupAll();
var el=document.getElementById('check_wp_cron');
if(el)
el.click();
};
parent.asyncGetContent(parent.ajaxurl,'action=<?php echo $del_wpcron_action;?>&id='+cron_hook+'&nonce=<?php echo wp_create_nonce_wrapper($del_wpcron_action);?>', parent.dummy, on_ready);
};
parent.remove_schedule=function(cron_hook){
parent.popupConfirm("<?php _pesc( 'Confirm' );?>","<?php _pesc( 'Are you sure you want to remove this WP Cron schedule?' );?>",null,{"<?php _pesc( 'Yes, I`m damn sure' );?>":"jsMyBackup.del_wpcron_schedule('"+cron_hook+"')","<?php _pesc( 'No' );?>":null});
};
<?php
$this->java_scripts[] = ob_get_clean();
}
protected function initTarget() {
parent::initTarget();
$this->hasCustomFrame = false;
$this->_is_wpcron_disabled = is_wpcron_disabled();
$this->_schedules = wp_get_schedules_wrapper();
$this->_getJavaScripts();
}
protected function getEditorTemplate() {
$w3ctc_active = is_plugin_active( 'w3-total-cache' . DIRECTORY_SEPARATOR . 'w3-total-cache.php' );
$schedule_time = wp_next_scheduled( WPCRON_SCHEDULE_HOOK_NAME );
$next_run = wp_next_scheduled( WPCRON_SCHEDULE_HOOK_NAME );
$help_1 = "'" .
_esc( 
'Specify the schedule date/time for the next immediate run. The next run after that will be calculated (based on the selected schedule) starting from this timestamp.' ) .
"'";
require_once $this->getTemplatePath( 'schedule.php' );
require_once $this->getTemplatePath( 'schedule-wp_cron.php' );
echo '<tr id="schedule_cron_row4"><td colspan="3">';
include_once $this->getTemplatePath( 'schedule-wp_cron-note.php' );
echo '</td></tr>';
echo '<tr><td colspan="3"><input type="hidden" name="excludedirs" id="excludedirs" value="' .
$this->settings['excludedirs'] . '"></td></tr>';
}
protected function getExpertEditorTemplate() {
$help_1 = "'" .
sprintf( 
_esc( 
'The WP Cron functionality can be disabled by setting <b>DISABLE_WP_CRON = true</b> in your wp-config.php file. There are many reason some WordPress sites has this option off (%s).<br>By checking this option I will set the DISABLE_WP_CRON=false, thus enabling the internal WP Cron functionality.<br>If for some reason the backup doesn`t seem to run at all when it`s scheduled as WP-Cron then check this option and try again (see also the next option).' ), 
getAnchorE( 
_esc( 'read more' ), 
'http://code.tutsplus.com/articles/insights-into-wp-cron-an-introduction-to-scheduling-tasks-in-wordpress--wp-23119' ) ) .
"'";
$help_2 = "'" . sprintf( 
_esc( 
'If the WP Cron does not seem to work depite the fact that <b>DISABLE_WP_CRON = false</b> then you may try to set this option ON. This is a WordPress workaround.<br>Please read %s before.' ), 
getAnchorE( _esc( 'this' ), 'https://codex.wordpress.org/Editing_wp-config.php#Alternative_Cron' ) ) . "'";
require_once $this->getTemplatePath( 'schedule-expert.php' );
}
}
?>