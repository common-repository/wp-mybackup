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
 * @file    : MyPclZipArchive.php $
 * 
 * @id      : MyPclZipArchive.php | Tue Feb 7 08:55:11 2017 +0100 | eugenmihailescu <eugenmihailescux@gmail.com> $
*/

namespace MyBackup;

! @constant( 'ABSPATH' ) && exit();
require_once LIB_PATH . 'MyException.php';
require_once ABSPATH . 'wp-admin/includes/class-pclzip.php';
class MyPclZip extends \PclZip {
public $beforeExtract = null;
public $afterExtract = null;
function privExtractFile( &$p_entry, $p_path, $p_remove_path, $p_remove_all_path, &$p_options ) {
if ( is_callable( $this->beforeExtract ) ) {
$v_local_header = array();
$this->privConvertHeader2FileInfo( $p_entry, $v_local_header );
$v_result = \call_user_func_array( $this->beforeExtract, array( PCLZIP_CB_PRE_EXTRACT, &$v_local_header ) );
if ( $v_result == 0 ) {
$p_entry['status'] = "skipped";
$v_result = 1;
}
if ( $v_result == 2 ) {
$p_entry['status'] = "aborted";
$v_result = PCLZIP_ERR_USER_ABORTED;
}
$p_entry['filename'] = $v_local_header['filename'];
}
if ( $p_entry['status'] == 'ok' ) {
$v_result = parent::privExtractFile( $p_entry, $p_path, $p_remove_path, $p_remove_all_path, $p_options );
} 
if ( $p_entry['status'] == "aborted" ) {
$p_entry['status'] = "skipped";
} elseif ( is_callable( $this->afterExtract ) ) {
$v_local_header = array();
$this->privConvertHeader2FileInfo( $p_entry, $v_local_header );
$v_result = call_user_func_array( $this->afterExtract, array( PCLZIP_CB_POST_EXTRACT, &$v_local_header ) );
if ( $v_result == 2 ) {
$v_result = PCLZIP_ERR_USER_ABORTED;
}
}
return $v_result;
}
}
class MyPclZipArchive extends GenericArchive {
private $_zip;
private $_errcode;
private $_opened;
private $_file_count;
private $_filelist_buffer;
private $_filelist_buffer_length = 1000;
private $_filelist_buffer_maxsize = 33554432; 
private $_filelist_buffer_size;
private function reset_filelist_buffer() {
$this->_filelist_buffer = array();
$this->_filelist_buffer_size = 0;
}
private function _get_removed_path( $filename, $alias ) {
$removed_path = '';
if ( $filename != $alias && preg_match( '/(.+)' . preg_quote( $alias, '/' ) . '$/', $filename, $matches ) )
$removed_path = $matches[1];
if ( empty( $removed_path ) && preg_match( '/^(\w:)?(\\' . DIRECTORY_SEPARATOR . ')(.*)/', $filename, $matches ) ) {
$removed_path = $matches[1] . $matches[2];
}
return $removed_path;
}
function __construct( $filename, $provider = null, $auto_ext = true ) {
if ( $auto_ext )
$filename = $filename . '.zip';
parent::__construct( $filename, $provider );
$this->_opened = false;
$this->_zip = null;
$this->_errcode = 0;
$this->_file_count = 0;
$this->reset_filelist_buffer();
if ( ! empty( $filename ) ) {
$this->_opened = true === $this->open( $filename );
}
$memory_usage = memory_get_usage( true );
$memory_limit = getMemoryLimit();
if ( $memory_limit - $memory_usage > 32 * MB )
$this->_filelist_buffer_maxsize = $memory_limit - $memory_usage;
}
function __destruct() {
is_resource( $this->_zip ) && $this->close();
}
public function open( $filename = null ) {
if ( $this->_opened && $filename == $this->getFileName() ) {
$this->_errcode = 0;
return true;
}
$this->close();
$this->reset_filelist_buffer();
$filename = empty( $filename ) ? $this->getFileName() : $filename;
if ( empty( $filename ) ) {
$this->_errcode = PCLZIP_ERR_MISSING_FILE;
return false;
}
$this->_zip = new MyPclZip( $filename );
if ( ! _is_file( $filename ) ) {
$this->_errcode = $this->_zip->create( '' );
$this->setDefaultComment();
return 0 !== $this->_errcode;
}
$this->_file_count = $this->getProperties( 'nb' );
return true;
}
public function close() {
if ( $this->_opened ) {
if ( ! empty( $this->_filelist_buffer ) ) {
$this->_opened = false;
return $this->addFiles( $this->_filelist_buffer );
}
}
return true;
}
public function addFile( $filename, $name = null, $compress = true ) {
if ( ! $can_add = parent::addFile( $filename, $name, $compress ) )
return false;
if ( _is_callable( $this->onAbortCallback ) &&
false !== _call_user_func( $this->onAbortCallback) )
return false;
$this->_file_count++;
$this->_filelist_buffer[$filename] = array( $name, $compress );
$this->_filelist_buffer_size += filesize( $filename );
if ( $this->_filelist_buffer_size >= $this->_filelist_buffer_maxsize ||
count( $this->_filelist_buffer ) >= $this->_filelist_buffer_length ) {
return $this->addFiles( $this->_filelist_buffer );
}
return $can_add;
}
public function addFiles( &$array ) {
$all_files = array( false => array(), true => array() );
foreach ( $array as $filename => $fileinfo ) {
$all_files[$fileinfo[1]][$filename] = $fileinfo[0];
}
$this->reset_filelist_buffer(); 
$arc_filename = $this->getFileName();
$abort_signal_received = false;
foreach ( array( false, true ) as $compress ) {
$max = count( $all_files[$compress] );
$i = 1;
foreach ( $all_files[$compress] as $filename => $falias ) {
if ( _is_callable( $this->onAbortCallback ) && ( $abort_signal_received = $abort_signal_received ||
false !== _call_user_func( $this->onAbortCallback) ) )
break;
$v_options = array( $filename );
! $compress && $v_options[] = PCLZIP_OPT_NO_COMPRESSION;
if ( $to_remove = $this->_get_removed_path( $filename, $falias ) ) {
$v_options[] = PCLZIP_OPT_REMOVE_PATH;
$v_options[] = $to_remove;
}
if ( 0 == ( $this->_errcode = call_user_func_array( array( &$this->_zip, 'add' ), $v_options ) ) ) {
break;
}
$fsize = filesize( $filename );
$this->onProgress( $arc_filename, $i++, $max, $this, PT_ADDFILE );
}
$this->onProgress( $arc_filename, $max, $max, $this, PT_ADDFILE );
if ( 0 == $this->_errcode )
break;
}
return 0 != $this->_errcode;
}
public function compress( $method, $level ) {
parent::compress( $method, $level );
return $this->getFileName();
}
public function decompress( $method = null, $uncompress_size = 0 ) {
parent::decompress( $method, $uncompress_size );
return $this->getFileName();
}
public function getArchiveFiles( $filename = null ) {
if ( ! $this->open( $filename ) )
return false;
$result = array();
foreach ( $this->_zip->listContent() as $file_info ) {
$result[$file_info['filename']] = array( 
'index' => $file_info['index'], 
'name' => $file_info['stored_filename'], 
'time' => $file_info['mtime'], 
'size' => $file_info['size'], 
'crc' => $file_info['crc'], 
'compressed' => $file_info['compressed_size'] );
}
return $result;
}
public function extract( $filename = null, $dst_path = null, $force_extrct = true ) {
$filename = empty( $filename ) ? $this->getFileName() : $filename;
$dst_path = addTrailingSlash( $dst_path );
if ( $result = false !== ( $zip_files = $this->getArchiveFiles( $filename ) ) )
! ( empty( $dst_path ) || _file_exists( $dst_path ) ) && $result = mkdir( $dst_path, 0770, true );
if ( ! $result )
return false;
$result = array();
$abort_signal_received = false;
$max = count( $zip_files );
$i = 1;
$is_win = preg_match( '/^win/i', PHP_OS );
$abort_signal_received = false;
$_this_ = &$this; 
$myPreExtractCallBack = function ( $p_event, &$p_header ) use(
&$abort_signal_received, 
&$dst_path, 
&$is_win, 
&$filename, 
&$i, 
&$max, 
&$result, 
&$_this_ ) {
if ( _is_callable( $_this_->onAbortCallback ) && ( $abort_signal_received = $abort_signal_received ||
false !== _call_user_func( $_this_->onAbortCallback) ) ) {
$result = false;
return 2;
}
if ( ! empty( $dst_path ) && $is_win ) {
$p_header['filename'] = preg_replace( '@\w*:[\\\/]@', '', $p_header['filename'] );
$p_header['stored_filename'] = preg_replace( '@\w*:[\\\/]@', '', $p_header['stored_filename'] );
}
$_this_->onProgress( $filename, $i++, $max, $_this_, PT_EXTRACTFILE );
return 1;
};
$myPostExtractCallBack = function ( $p_event, &$p_header ) use(
&$abort_signal_received, 
&$dst_path, 
&$force_extrct, 
&$result, 
&$_this_ ) {
if ( _is_callable( $_this_->onAbortCallback ) && ( $abort_signal_received = $abort_signal_received ||
false !== _call_user_func( $_this_->onAbortCallback) ) ) {
$result = false;
return 2;
}
$error = false;
if ( ! $p_header['folder'] ) {
$output_crc = @crc32( file_get_contents( $p_header['filename'] ) );
if ( $output_crc != $p_header['crc'] ) {
$error = true;
$_this_->_stdOutput( 
sprintf( 
_esc( 'Extracted file CRC (%s) != archived file CRC (%s)' ), 
$output_crc, 
$p_header['crc'] ) );
}
}
if ( ! $error || $force_extrct ) {
$result[$p_header['filename']] = $p_header['stored_filename'];
return 1;
}
$result = false;
return 2;
};
$this->_zip->beforeExtract = $myPreExtractCallBack;
$this->_zip->afterExtract = $myPostExtractCallBack;
$this->_zip->extract( PCLZIP_OPT_PATH, $dst_path );
return $result;
}
public function setArchiveComment( $comment ) {
if ( $tmpname = tempnam( dirname( $this->getFileName() ), 'pclzip' ) ) {
$v_options = array( 
$tmpname, 
PCLZIP_OPT_NO_COMPRESSION, 
PCLZIP_OPT_REMOVE_ALL_PATH, 
PCLZIP_OPT_COMMENT, 
$comment );
if ( 0 != $this->_errcode = ( $stat = call_user_func_array( array( &$this->_zip, 'add' ), $v_options ) ) ) {
$stat = array_pop( $stat );
$this->_zip->delete( PCLZIP_OPT_BY_NAME, basename( $tmpname ) );
}
unlink( $tmpname );
return 0 != $this->_errcode;
}
return false;
}
public function setArchivePassword( $password ) {
return false;
}
public function unlink() {
}
public function setFileName( $filename = null ) {
if ( $this->fileName != $filename ) {
$this->fileName = $filename;
$this->open( $this->fileName );
}
}
public function getProperties( $prop_name = '' ) {
$array = $this->_zip->properties();
if ( 0 === $array || ! ( empty( $prop_name ) || isset( $array[$prop_name] ) ) )
return false;
return empty( $prop_name ) ? $array : $array[$prop_name];
}
}
?>