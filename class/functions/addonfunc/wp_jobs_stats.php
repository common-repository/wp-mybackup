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
 * @file    : wp_jobs_stats.php $
 * 
 * @id      : wp_jobs_stats.php | Tue Feb 7 08:55:11 2017 +0100 | eugenmihailescu <eugenmihailescux@gmail.com> $
*/

namespace MyBackup;

$cache_file = LOG_PREFIX . '-wp_jobs_stats.cache';
$json_stats = ! ( isset( $_this_->method['nocache'] ) && strToBool( $_this_->method['nocache'] ) ) &&
is_file( $cache_file ) ? file_get_contents( $cache_file ) : false;
if ( ! ( $json_stats && ( $stat_info = json_decode( $json_stats, true ) ) && isset( $stat_info['timestamp'] ) &&
time() - $stat_info['timestamp'] < 3600 ) ) {
$stat_mngr = getJobsStatManager( $_this_->settings );
$stat_info = getJobsStatistics( $stat_mngr );
$stat_info['title'] = _esc( 'Backup & Restore Statistics' );
$stat_info['file_size'] = getHumanReadableSize( $stat_info[JOB_BACKUP]['file_size'] );
$stat_info['data_size'] = getHumanReadableSize( $stat_info[JOB_BACKUP]['data_size'] );
$stat_info['files_count'] = number_format( $stat_info[JOB_BACKUP]['files_count'] );
$stat_info['ratio'] = sprintf( 'x%.2f', $stat_info[JOB_BACKUP]['ratio'] );
$backup_count = implode( 
' | ', 
array( 
getSpan( number_format( $stat_info[JOB_BACKUP]['completed'] ), 'green' ), 
getSpan( number_format( $stat_info[JOB_BACKUP]['partial'] ), '#FF8000' ), 
getSpan( number_format( $stat_info[JOB_BACKUP]['failed'] ), 'red' ) ) );
$restoration_count = implode( 
' | ', 
array( 
getSpan( number_format( $stat_info[- 4]['completed'] ), 'green' ), 
getSpan( number_format( $stat_info[- 4]['partial'] ), '#FF8000' ), 
getSpan( number_format( $stat_info[- 4]['failed'] ), 'red' ) ) );
$stat_info['backup_count'] = str_replace( '"', "'", $backup_count );
$stat_info['restoration_count'] = str_replace( '"', "'", $restoration_count );
$stat_info['timestamp'] = time();
$json_stats = json_encode( $stat_info, JSON_FORCE_OBJECT );
file_put_contents( $cache_file, $json_stats );
}
echo $json_stats;
?>