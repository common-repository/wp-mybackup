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
 * @file    : restore-expert.php $
 * 
 * @id      : restore-expert.php | Tue Feb 7 08:55:11 2017 +0100 | eugenmihailescu <eugenmihailescux@gmail.com> $
*/

namespace MyBackup;

$restore_alert = '';
$max_execution_time = ini_get( "max_execution_time" );
$mysql_ext = array( 'mysqli', 'pdo_mysql', '' );
$loaded_ext = get_loaded_extensions();
$has_ext = array_intersect( $mysql_ext, $loaded_ext );
if ( $max_execution_time < RESTORE_MIN_EXECUTION_TIME && ! ( strToBool( $this->settings['restore_acid'] ) &&
count( $has_ext ) && in_array( $this->settings['mysql_ext'], $mysql_ext ) ) ) {
$alert_msg = getSpanE( _esc( 'Warning' ), 'red', 'bold' ) . ' : ';
$alert_msg .= sprintf( 
_esc( 
'You chose to restore MySQL data while %s=%s < %s. If the max_execution_time is too short the script may be forcebly innterupted (by PHP) while restoring your data. This may lead to partial data restoration.<br>Make sure you either increase that limit or enable the `MySQL ACID restore` expert option.<br>Caution is advised!' ), 
getAnchorE( 'max_execution_time', PHP_MANUAL_URL . 'info.configuration.php#ini.max-execution-time' ), 
$max_execution_time, 
RESTORE_MIN_EXECUTION_TIME, 
_esc( 'best available' ) );
$restore_alert = sprintf( '<div class=\'hintbox %s\'>%s</div>', $this->container_shape, $alert_msg );
}
$this->java_scripts[] = sprintf( 'parent.restore_alert="%s";', $restore_alert );
$extract_forcebly = strToBool( $this->settings['extractforcebly'] );
$download_forcebly = strToBool( $this->settings['downloadforcebly'] );
$restore_mybackup = strToBool( $this->settings['restore_mybackup'] );
$restore_acid = strToBool( $this->settings['restore_acid'] );
$restore_backup_mysql_dir = strToBool( $this->settings['restore_backup_mysql_dir'] );
$is_dashboard = isset( $is_dashboard ) && $is_dashboard;
$help_1 = "'" .
_esc( 
'Force extracting (and restoring) all files from an archive even if their content will be partially truncated. This situation may occur on a corrupted/invalid archive.<br>I do not recommend this unless you are utterly desperated and when one byte recovered is better than none.' ) .
"'";
$help_2 = "'" .
_esc( 
'This option allows the usage of the remote archives regardless of their checksum consistence. Use this only when you have no other choice (ie. valid backup source).' ) .
"'";
$help_3 = "'" . sprintf( 
_esc( 
'This option allows the restoration of %s tables/files created by itself. However, the %s options will be restored because they are stored in the WordPress global options table. If unsure then let it unchecked.' ), 
WPMYBACKUP, 
WPMYBACKUP ) . "'";
$help_4 = sprintf( 
_esc( 
'When restoring a MySQL database encloses all the SQL statements within s single SQL transaction with respect to the MySQL %s. If one statement fails the whole batch is rolled back so no change is done to your MySQL database.' ), 
getAnchorE( 'ACID model', 'https://dev.mysql.com/doc/refman/5.6/en/mysql-acid.html' ) );
$help_4 = "'" . $help_4 . '<br>' .
_esc( 
'Moreover, if the MySQL restoration fails and is rolled back then the file restoration (if choosen) will not be executed either.' ) .
"'";
$help_9 = "'" .
_esc( 
'Backup the MySQL data directory (if possible) before restoring the MySQL database. This is the so called `paranoid backup`. However, usually the web server cannot access the MySQL data directory so do not rely on this.' ) .
"'";
if ( $is_dashboard ) {
$restore_upl_chunked = strToBool( $this->settings['restore_upl_chunked'] );
if ( ! _dir_in_allowed_path( $this->settings['wrkdir'] ) )
$disk_free = PHP_INT_MAX;
else
$disk_free = _disk_free_space( $this->settings['wrkdir'] ? $this->settings['wrkdir'] : _sys_get_temp_dir() );
$help_5 = "'" . sprintf( 
_esc( 
'Set this option to overcome the php.ini %s restriction (which is %s) while uploading an external/custom backup archive.' ), 
implode( 
'|', 
array_map( function ( $item ) {
return escape_quotes( $item );
}, $this->_upload_constraint_link ) ), 
getHumanReadableSize( getUploadLimit() ) ) . '<br>' . sprintf( 
_esc( 
'The file(s) will be uploaded in small chunks which guaranties a successful upload even for large files (eg. %s).' ), 
getHumanReadableSize( $disk_free ) ) . "'";
$chunk_options_disabled = ! $restore_upl_chunked ? ' disabled ' : '';
$chunk_note = _esc( 'The file will be splitted in equal chunks/slices.' ) . ' ';
$concurrency_note = ' ' .
_esc( 'Please note that the browser and/or your web host may restrict the number of concurrent connections.' ) .
' ' . readMoreHereE( 'http://www.browserscope.org/?category=network' ) . '.';
$help_6 = "'" . $chunk_note .
_esc( 
'Specify the maximum size (KiB) of a slice of the file that is going to be uploaded within a single HTTP request. The larger, the better.' ) .
"'";
$help_7 = "'" . $chunk_note . _esc( 'Specify how many chunks will be uploaded at a time.' ) . $concurrency_note . "'";
$help_8 = "'" . $chunk_note .
_esc( 
'Specify how many milliseconds to wait before sending a new concurrent chunk. If you specify zero (or 20ms) then they will be send almost simoultaneously.' ) .
$concurrency_note . "'";
}
?>
<tr>
<td>
<table class="restore-expert">
<tr>
<td><label for="extractforcebly"><?php _pesc('Extract forcebly');?></label></td>
<td><input type="checkbox" name="extractforcebly" id="extractforcebly"
value="1"
<?php
if ( $extract_forcebly )
echo ' checked';
?>><input type="hidden" name="extractforcebly" value="0"><a class='help'
onclick=<?php echoHelp ( $help_1 ); ?>>[?]</a></td>
</tr>
<tr>
<td><label for="downloadforcebly"><?php _pesc('Download forcebly');?></label></td>
<td><input type="checkbox" name="downloadforcebly" id="downloadforcebly"
value="1"
<?php
if ( $download_forcebly )
echo ' checked';
?>><input type="hidden" name="downloadforcebly" value="0"><a class='help'
onclick=<?php echoHelp ( $help_2 ); ?>>[?]</a></td>
</tr>
<tr>
<td><label for="restore_mybackup"><?php echo sprintf(_esc('Restore %s files'),WPMYBACKUP);?></label></td>
<td><input type="checkbox" name="restore_mybackup" id="restore_mybackup"
value="1"
<?php
if ( $restore_mybackup )
echo ' checked';
?>><input type="hidden" name="restore_mybackup" value="0"><a class='help'
onclick=<?php echoHelp ( $help_3 ); ?>>[?]</a></td>
</tr>
<tr>
<td><label for="restore_acid"><?php echo sprintf(_esc('MySQL ACID restore'),WPMYBACKUP);?></label></td>
<td><input type="checkbox" name="restore_acid" id="restore_acid" value="1"
<?php
if ( $restore_acid )
echo ' checked';
?>><input type="hidden" name="restore_acid" value="0"><a class='help'
onclick=<?php echoHelp ( $help_4 ); ?>>[?]</a></td>
</tr>
<tr>
<td><label for="restore_backup_mysql_dir"><?php echo sprintf(_esc('Backup MySQL dir'),WPMYBACKUP);?></label></td>
<td><input type="checkbox" name="restore_backup_mysql_dir"
id="restore_backup_mysql_dir" value="1"
<?php
if ( $restore_backup_mysql_dir )
echo ' checked';
?>><input type="hidden" name="restore_backup_mysql_dir" value="0"><a
class='help' onclick=<?php echoHelp ( $help_9 ); ?>>[?]</a></td>
</tr>
</table>
</td>
<?php
if ( $is_dashboard ) {
?>
<td>
<table class="chunked-upload-expert" style="padding-left: 2em">
<tr>
<td><label for="restore_upl_chunked"><?php _pesc('Upload files in chunks');?></label></td>
<td><input type="checkbox" name="restore_upl_chunked"
id="restore_upl_chunked" value="1"
<?php
if ( $restore_upl_chunked )
echo ' checked';
?>><input type="hidden" name="restore_upl_chunked" value="0"><a class='help'
onclick=<?php echoHelp ( $help_5 ); ?>>[?]</a></td>
</tr>
<tr>
<td><label for="upload_max_chunk_size"><?php echo _esc('Upload max chunk size (KiB)');?></label></td>
<td><input id="upload_max_chunk_size" name="upload_max_chunk_size" min="4"
<?php echo $chunk_options_disabled;?>
value="<?php echo $this->settings['upload_max_chunk_size'];?>"
type="number"><a class='help' onclick=<?php echoHelp ( $help_6 ); ?>>[?]</a></td>
</tr>
<tr>
<td><label for="upload_max_parallel_chunks"><?php echo _esc('Upload max parallel chunks');?></label></td>
<td><input id="upload_max_parallel_chunks" name="upload_max_parallel_chunks"
value="<?php echo $this->settings['upload_max_parallel_chunks'];?>" min="1"
<?php echo $chunk_options_disabled;?> type="number"><a class='help'
onclick=<?php echoHelp ( $help_7 ); ?>>[?]</a></td>
</tr>
<tr>
<td><label for="upload_send_interval"><?php echo _esc('Upload send interval (ms)');?></label></td>
<td><input id="upload_send_interval" name="upload_send_interval" min="0"
<?php echo $chunk_options_disabled;?>
value="<?php echo $this->settings['upload_send_interval'];?>" type="number"><a
class='help' onclick=<?php echoHelp ( $help_8 ); ?>>[?]</a></td>
</tr>
</table>
</td>
<?php }?>	
</tr>