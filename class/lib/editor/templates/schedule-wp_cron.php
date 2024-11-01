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
 * @file    : schedule-wp_cron.php $
 * 
 * @id      : schedule-wp_cron.php | Tue Feb 7 08:55:11 2017 +0100 | eugenmihailescu <eugenmihailescux@gmail.com> $
*/

namespace MyBackup;
?>
<tr id="schedule_cron_row1_0">
<td></td>
<td style="width: 0px; white-space: nowrap;">
<?php
if (count ( $this->_schedules ) > 0) {
?>
<input type="radio" id="schedule_grp_wp_cron" name="schedule_grp"
value="wp_cron" onclick="jsMyBackup.toggle_wp_cron(true);"
<?php
if ('os_cron' != $this->settings ['schedule_grp'])
echo ' checked ';
?>><label for="schedule_grp_wp_cron"><?php _pesc('Schedule by WP-Cron');?></label>
</td>
<td><select id="schedule_wp_cron" name="schedule_wp_cron"
onclick="jsMyBackup.showScheduleInterval(this);">
<?php
}
foreach ( $this->_schedules as $s => $v )
echo "<option value='$s'" . ($s == $this->settings ['schedule_wp_cron'] ? ' selected' : '') . ">" . $v ['display'] . "</option>";
if (count ( $this->_schedules ) > 0) {
?>
</select>
<?php } ?>
<a id="check_wp_cron" class="help"
onclick="<?php $params=http_build_query(array('action'=>'get_wpcron_schedule','nonce'=>wp_create_nonce_wrapper('get_wpcron_schedule')));echo "jsMyBackup.asyncGetContent(jsMyBackup.ajaxurl,'$params',null,null,null,'"._esc('Enabled schedules');?>');"><?php _pesc('Check');?></a></td>
</tr>
<tr id="schedule_cron_row1_1">
<td></td>
<td style="text-align: right;"><label for="schedule_wp_cron_time"><?php _pesc('Next run');?></label></td>
<td><input type="datetime" id="schedule_wp_cron_time"
name="schedule_wp_cron_time"
value="<?php echo false!==$next_run?date(DATETIME_FORMAT,$next_run):'';?>"
size="19" maxlength="19"><a class='help'
onclick=<?php echoHelp ( $help_1 );?>> [?]</a></td>
</tr>
<?php $job_status=isJobRunning($this->settings);if($job_status[0]){?>
<tr>
<td></td>
<td style="text-align: right;"><label><?php _pesc('Status');?></label></td>
<td><?php
echo $job_status [1] . '&nbsp;';
echo "<input type='button' id='btn_monitor0' title='" . sprintf ( _esc ( 'Spy the %s log' ), _esc ( 'full' ) ) . "' onclick='jsMyBackup.spy(\"log_read\",\"full\",\"" . wp_create_nonce_wrapper ( 'log_read' ) . "\",\"" . wp_create_nonce_wrapper ( 'get_progress' ) . "\",\"" . wp_create_nonce_wrapper ( 'clean_progress' ) . "\",\"" . wp_create_nonce_wrapper ( 'log_read_abort' ) . "\");' class='button btn_monitor'>";
if (false !== $job_status [2])
printf ( "<input type='button' id='btn_stop' title='%s' onclick='jsMyBackup.abortJob(\"abort_job\",\"%s\",%s,jsMyBackup.globals.ON_JOBABORT_SUCCESS);' class='button btn_stop'>", sprintf ( _esc ( 'Abort job #%s' ), $job_status [2] [2] ), wp_create_nonce_wrapper ( 'abort_job' ), $job_status [2] [2] );
?>
</td>
</tr>
<?php
}