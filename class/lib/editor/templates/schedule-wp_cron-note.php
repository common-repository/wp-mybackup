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
 * @file    : schedule-wp_cron-note.php $
 * 
 * @id      : schedule-wp_cron-note.php | Tue Feb 7 08:55:11 2017 +0100 | eugenmihailescu <eugenmihailescux@gmail.com> $
*/

namespace MyBackup;
?>
<div id="wp_cron_note"
class="hintbox <?php echo $this->container_shape;?>"
style="display: none">
<table>
<tr>
<td colspan="2"><b><?php _pesc('Note');?></b></td>
</tr>
<tr>
<td>&nbsp;</td>
<td><?php printf(_esc('The action will trigger <b>ONLY</b> when someone visits your WordPress site, if the scheduled time has passed. This is not a bug! This is %s.'),getAnchor(_esc('the way the WordPress Cron works'), 'https://codex.wordpress.org/Function_Reference/wp_schedule_event#Description'));?></td>
</tr>
<tr>
<td>&nbsp;</td>
<td><?php printf(_esc('I recommend you to read also %s.'),getAnchor(_esc('this'), 'https://www.lucasrolff.com/wordpress/why-wp-cron-sucks'));?></td>
</tr>
<?php if($w3ctc_active||$this->_is_wpcron_disabled){?>
<tr>
<td colspan="2"><?php printf('<b>'._esc('Warning').'</b>');?></td>
</tr>
<?php if($w3ctc_active){?>
<tr>
<td>&nbsp;</td>
<td><?php printf(_esc('The %s WP plugin seems to be installed. WP Cron is known to be buggy when W3TC Page Cache option is enabled.'),getAnchor('W3 Total Cache', 'https://wordpress.org/plugins/w3-total-cache'));?></td>
</tr>
<tr>
<td>&nbsp;</td>
<td><?php echo readMoreHere('https://wordpress.org/support/topic/w3-total-cache-and-backup-buddy');?></td>
</tr>
<?php
}
if ($this->_is_wpcron_disabled) {
?>
<tr>
<td>&nbsp;</td>
<td><?php printf(_esc('The WP Cron seems to be %s and thus the backup schedule won`t work.'),getAnchor(_esc('disabled'), 'https://codex.wordpress.org/Editing_wp-config.php#Disable_Cron_and_Cron_Timeout'));?></td>
</tr>
<tr>
<td>&nbsp;</td>
<td><?php _pesc('See also the <b>Set DISABLE_WP_CRON=false</b> expert option below.');?></td>
</tr>
<?php }}?>
</table>
</div>