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
 * @file    : MyChunkUploader.php $
 * 
 * @id      : MyChunkUploader.php | Tue Feb 7 08:55:11 2017 +0100 | eugenmihailescu <eugenmihailescux@gmail.com> $
*/

namespace MyBackup;

if ( ! function_exists( __NAMESPACE__ . '\\_esc' ) ) {
function _esc( $text ) {
return function_exists( '_' ) ? _( $text ) : ( function_exists( '__' ) ? __( $text ) : $text );
}
}
class MyUploadException extends \Exception {
public function __construct( $error_code ) {
parent::__construct( $this->_getMessage( $error_code ), $error_code );
}
private function _getMessage( $error_code ) {
switch ( $error_code ) {
case UPLOAD_ERR_INI_SIZE :
$message = _esc( 'The uploaded file exceeds the upload_max_filesize directive in php.ini' );
break;
case UPLOAD_ERR_FORM_SIZE :
$message = _esc( 
'The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form' );
break;
case UPLOAD_ERR_PARTIAL :
$message = _esc( 'The uploaded file was only partially uploaded' );
break;
case UPLOAD_ERR_NO_FILE :
$message = _esc( 'No file was uploaded' );
break;
case UPLOAD_ERR_NO_TMP_DIR :
$message = _esc( 'Missing a temporary folder. Check the upload_tmp_dir directive in php.ini' );
break;
case UPLOAD_ERR_CANT_WRITE :
$message = _esc( 'Failed to write file to disk' );
break;
case UPLOAD_ERR_EXTENSION :
$message = _esc( 
'A PHP extension stopped the file upload. Examining the list of loaded PHP extensions may help.' );
break;
default :
$message = _esc( 'Unknown upload error' );
break;
}
return $message;
}
}
class MyChunkUploader {
private $_raw_post;
private $_range;
private $_filename;
private $_headers;
private $_require_nonce = false;
private $_error;
private $_tmp_dir;
private $_abort;
private $_waiting;
private $_may_run;
private $_content_type;
public $on_chk_nonce = false;
public $on_new_nonce = false;
public $on_done;
public $on_get_type;
function __construct( $working_dir = null ) {
$class_header = 'X-' . str_replace( __NAMESPACE__ . '\\', '', __CLASS__ );
defined( __NAMESPACE__.'\\UPLOADER_CHUNK_SIGNATURE' ) || define( __NAMESPACE__.'\\UPLOADER_CHUNK_SIGNATURE', $class_header ); 
$prefix = UPLOADER_CHUNK_SIGNATURE;
defined( __NAMESPACE__.'\\UPLOADER_WAIT_HEADER' ) || define( __NAMESPACE__.'\\UPLOADER_WAIT_HEADER', $prefix . '-Wait' ); 
defined( __NAMESPACE__.'\\UPLOADER_TYPE_HEADER' ) || define( __NAMESPACE__.'\\UPLOADER_TYPE_HEADER', $prefix . '-Type' ); 
defined( __NAMESPACE__.'\\UPLOADER_NONCE_HEADER' ) || define( __NAMESPACE__.'\\UPLOADER_NONCE_HEADER', $prefix . '-Security-Nonce' ); 
defined( __NAMESPACE__.'\\UPLOADER_RAW_POST_HEADER' ) || define( __NAMESPACE__.'\\UPLOADER_RAW_POST_HEADER', $prefix . '-Raw-Post' ); 
defined( __NAMESPACE__.'\\UPLOADER_ABORT_HEADER' ) || define( __NAMESPACE__.'\\UPLOADER_ABORT_HEADER', $prefix . '-Abort' ); 
defined( __NAMESPACE__.'\\UPLOADER_TIMEOUT_HEADER' ) || define( __NAMESPACE__.'\\UPLOADER_TIMEOUT_HEADER', $prefix . '-Timeout' ); 
defined( __NAMESPACE__.'\\UPLOADER_RANGE_HEADER' ) || define( __NAMESPACE__.'\\UPLOADER_RANGE_HEADER', 'Content-Range' ); 
defined( __NAMESPACE__.'\\UPLOADER_FILENAME_HEADER' ) || define( __NAMESPACE__.'\\UPLOADER_FILENAME_HEADER', 'Content-Disposition' ); 
defined( __NAMESPACE__.'\\UPLOADER_TIMEOUT' ) || define( __NAMESPACE__.'\\UPLOADER_TIMEOUT', 3600 ); 
$uploader_headers = array( 
UPLOADER_CHUNK_SIGNATURE, 
UPLOADER_TYPE_HEADER, 
UPLOADER_NONCE_HEADER, 
UPLOADER_RAW_POST_HEADER, 
UPLOADER_ABORT_HEADER, 
UPLOADER_TIMEOUT_HEADER, 
UPLOADER_RANGE_HEADER, 
UPLOADER_FILENAME_HEADER, 
UPLOADER_WAIT_HEADER );
$this->on_done = null;
$this->on_get_type = null;
$this->_range = array();
$this->_filename = null;
$this->on_chk_nonce = false;
$this->_new_nonce_callback = false;
$this->_require_nonce = false;
$this->_content_type = false; 
if ( defined( __NAMESPACE__.'\\UPLOADER_VERIFY_NONCE_CALLBACK' ) && is_callable( UPLOADER_VERIFY_NONCE_CALLBACK ) ) {
$this->on_chk_nonce = UPLOADER_VERIFY_NONCE_CALLBACK;
$this->_require_nonce = defined( __NAMESPACE__.'\\UPLOADER_REQUIRES_NONCE' ) && UPLOADER_REQUIRES_NONCE;
}
$this->_tmp_dir = ! empty( $working_dir ) ? $working_dir : dirname(LOG_DIR);
if ( empty( $this->_tmp_dir ) || substr( $this->_tmp_dir, - 1 ) != DIRECTORY_SEPARATOR )
$this->_tmp_dir .= DIRECTORY_SEPARATOR;
_is_dir( $this->_tmp_dir ) || mk_dir( $this->_tmp_dir );
$this->_headers = $this->array_intersect_ikey( getallheaders(), array_flip( $uploader_headers ) );
$this->_may_run = $this->_strToBool( $this->_get_header_value( UPLOADER_CHUNK_SIGNATURE ) );
$this->_waiting = $this->_get_header_value( UPLOADER_WAIT_HEADER );
$this->_abort = $this->_strToBool( $this->_get_header_value( UPLOADER_ABORT_HEADER ) );
$this->_filename = $this->get_filename();
}
private function array_intersect_ikey( $array1, $array2 ) {
$result = array();
foreach ( $array1 as $k1 => $v1 )
foreach ( $array2 as $k2 => $v2 )
if ( strtolower( $k1 ) == strtolower( $k2 ) )
$result[$k2] = $v1;
return $result;
}
private function _get_header_value( $header_name ) {
return ! empty( $this->_headers ) && isset( $this->_headers[$header_name] ) ? $this->_headers[$header_name] : false;
}
private function _strToBool( $value ) {
return true === $value || 1 === preg_match( '/(true|on|1|yes)/i', $value );
}
private function _sanitize_file_name( $filename ) {
$special_chars = array( 
"?", 
"[", 
"]", 
"/", 
"\\", 
"=", 
"<", 
">", 
":", 
";", 
",", 
"'", 
"\"", 
"&", 
"$", 
"#", 
"*", 
"(", 
")", 
"|", 
"~", 
"`", 
"!", 
"{", 
"}", 
"%", 
"+", 
chr( 0 ) );
$filename = preg_replace( "#\x{00a0}#siu", ' ', $filename );
$filename = str_replace( $special_chars, '', $filename );
$filename = str_replace( array( '%20', '+' ), '-', $filename );
$filename = preg_replace( '/[\r\n\t -]+/', '-', $filename );
$filename = trim( $filename, '.-_' );
$parts = explode( '.', $filename );
if ( count( $parts ) <= 2 ) {
return $filename;
}
$filename = array_shift( $parts );
$extension = array_pop( $parts );
foreach ( (array) $parts as $part ) {
$filename .= '.' . $part;
}
$filename .= '.' . $extension;
return $filename;
}
public function _cleanup_parts( $filename = false ) {
$filename || $filename = $this->_filename;
if ( _is_dir( $this->_tmp_dir ) && ! empty( $filename ) )
foreach ( $this->_get_parts( false, false, $filename ) as $chunk_filename ) {
if ( ! empty( $chunk_filename ) && 0 === strpos( $chunk_filename, $this->_tmp_dir ) &&
_is_file( $chunk_filename ) ) {
@unlink( $chunk_filename );
}
}
}
public function _die( $array ) {
die( json_encode( $array, JSON_FORCE_OBJECT ) );
}
public function _set_error( $message, $code = -1, $sys_error = true ) {
if ( $sys_error ) {
if ( $e = error_get_last() ) {
$message = $e['message'];
$code = $e['type'] . ( - 1 !== $code ? '-' . $code : '' );
}
}
empty( $message ) && $message = _esc( 'unknown' );
$error = array( 
'success' => false, 
'message' => $message, 
'code' => $code, 
'json' => array( 'name' => $this->_filename ) );
$this->_cleanup_parts();
$this->_die( $error );
}
private function _copy_file( $input_file, $output_file, $sufix = '', $write_mode = 'wb' ) {
$fr = @fopen( $input_file, 'rb' );
( false !== $fr ) || $this->_set_error( null, "3.4$sufix" );
$fw = @fopen( $output_file, $write_mode );
( false !== $fw ) || $this->_set_error( false, "3.5$sufix" );
$written = 0;
while ( ! feof( $fr ) && ( $buffer = @fread( $fr, 4096 ) ) && ( false !== $written ) ) {
$written = @fwrite( $fw, $buffer );
}
( false !== $buffer ) || $this->_set_error( null, "3.6$sufix" );
( false !== $written ) || $this->_set_error( null, "3.7$sufix" );
@fclose( $fr ) || $this->_set_error( null, "3.8$sufix" );
@fclose( $fw ) || $this->_set_error( null, "3.9$sufix" );
}
private function _validate_headers() {
if ( is_callable( $this->on_chk_nonce ) ) {
$nonce = $this->_get_header_value( UPLOADER_NONCE_HEADER );
if ( $nonce ) {
if ( ! call_user_func( $this->on_chk_nonce, $nonce ) ) {
$this->_set_error( _esc( 'Security nonce error' ), "3.0.a", false );
}
} else {
$this->_set_error( _esc( 'Security nonce is required' ), "3.0.b", false );
}
}
$this->_content_type = $this->_get_header_value( UPLOADER_TYPE_HEADER );
$header_error = _esc( '%s header expected' );
if ( $this->_filename ) {
_is_file( $this->_filename ) && unlink( $this->_filename );
$this->_raw_post = $this->is_raw_post();
} else {
$this->_set_error( sprintf( $header_error, UPLOADER_FILENAME_HEADER ), 3.1, false );
}
if ( ! $this->_abort ) {
( $this->_range = $this->get_range() ) ||
$this->_set_error( sprintf( $header_error, UPLOADER_RANGE_HEADER ), 3.2, false );
}
}
private function _merge_files() {
$files = $this->_get_parts();
if ( $this->has_not_received_parts() ) {
! ( $timeout = $this->_get_header_value( UPLOADER_TIMEOUT_HEADER ) ) && $timeout = 3600;
{
$response = array( 
'name' => basename( $this->_filename ), 
'error' => false, 
'done' => false, 
'wait' => 1, 
'headers' => array_merge( array( UPLOADER_WAIT_HEADER => true ), $this->_headers ) );
if ( is_callable( $this->on_new_nonce ) )
$response['new_nonce'] = call_user_func( $this->on_new_nonce );
$this->_die( array( 'success' => true, 'json' => $response ) );
}
}
foreach ( $files as $chunk_filename ) {
$this->_copy_file( $chunk_filename, $this->_filename, 'final', 'ab' );
unlink( $chunk_filename );
}
if ( is_callable( $this->on_done ) && count( $files ) ) {
try {
$this->_filename = call_user_func( $this->on_done, $this->_filename, $this->_tmp_dir );
} catch ( \Exception $e ) {
$this->_set_error( $e->getMessage(), $e->getCode(), false );
}
}
return count( $files );
}
private function _get_parts( $sort = true, $desc = false, $filename = false ) {
$filename || $filename = $this->_filename;
$pattern = sprintf( '%s%s-*-*', $this->_tmp_dir, $filename );
$parts = glob( $pattern );
$sort && usort( 
$parts, 
function ( $a, $b ) {
$range_pattern = '/-(\d+)-\d+$/';
if ( preg_match( $range_pattern, $a, $range_1 ) && preg_match( $range_pattern, $b, $range_2 ) )
return intval( $range_1[1] ) - intval( $range_2[1] );
else
return ( $a < $b ? - 1 : 1 ) * ( $desc ? - 1 : 1 );
} );
return ! $parts ? array() : $parts;
}
private function has_not_received_parts() {
$parts = $this->_get_parts( true, true );
$get_part_by_offset = function ( $to ) use(&$parts ) {
foreach ( $parts as $filename )
if ( preg_match( '/-(\d+)-' . $to . '$/', $filename, $matches ) )
return $matches[1];
return false;
};
$range_pattern = '/-(\d+)-(\d+)$/';
$tail = array_pop( $parts );
if ( preg_match( $range_pattern, $tail, $range ) ) {
$from = $range[1];
$to = $range[2];
if ( '0' != $from )
while ( false != ( $from = ( $get_part_by_offset( $from - 1 ) ) ) )
;
return '0' === $from ? 0 : 1;
}
return false;
}
private function _get_content_type() {
if ( is_callable( $this->on_get_type ) ) {
try {
return call_user_func( $this->on_get_type, $this->_filename, $this->_content_type );
} catch ( \Exception $e ) {
$this->_set_error( $e->getMessage(), $e->getCode(), false );
}
}
return $this->_content_type;
}
public function run() {
if ( ! $this->may_run() ) {
return false;
}
$file_crc32 = function ( $filename ) {
return hexdec( @hash_file( 'crc32b', $filename ) );
};
$this->_validate_headers();
if ( $this->_abort ) {
$this->_set_error( _esc( 'Aborted by user' ), 'UI', false );
}
$tmp_filename = sprintf( '%s%s-%d-%d', $this->_tmp_dir, $this->_filename, $this->_range[1], $this->_range[2] );
if ( $this->_raw_post ) {
$chunk_filename = 'php://input';
} else {
if ( ! $this->_waiting ) {
empty( $_FILES ) && $this->_set_error( _esc( 'No file sent' ), 3.11, false );
$file = end( $_FILES );
$sys_error = error_get_last();
UPLOAD_ERR_OK == $file['error'] || $this->_set_error( 
null === $sys_error ? _esc( 'File upload error. Try again' ) : $sys_error['message'], 
null === $sys_error ? 3.12 : $sys_error['type'], 
false );
$chunk_filename = $file['tmp_name'];
}
}
$this->_waiting || $this->_copy_file( $chunk_filename, $tmp_filename, 'chunk' );
if ( ( $this->_range[2] + 1 == $this->_range[3] ) ) {
if ( $chunks = $this->_merge_files() ) {
$response = array( 
'tmp_name' => realpath( $this->_filename ), 
'name' => basename( $this->_filename ), 
'size' => @filesize( $this->_filename ), 
'type' => $this->_get_content_type(), 
'error' => false, 
'chunks' => $chunks, 
'crc' => $file_crc32( $this->_filename ), 
'done' => true );
} else
$this->_set_error( _esc( 'Could not merge the chunks' ), 3.13, false );
} else
$response = array( 
'index' => count( $this->_get_parts( false ) ), 
'tmp_name' => realpath( $tmp_filename ), 
'name' => basename( $tmp_filename ), 
'size' => @filesize( $tmp_filename ), 
'error' => false, 
'crc' => $file_crc32( $tmp_filename ), 
'done' => false );
$this->_die( array( 'success' => true, 'json' => $response ) );
}
public function may_run() {
return $this->_may_run;
}
public function is_waiting() {
return $this->_waiting;
}
public function is_raw_post() {
return $this->_strToBool( $this->_get_header_value( UPLOADER_RAW_POST_HEADER ) );
}
public function is_aborting() {
return $this->_abort;
}
public function get_range( $key = null ) {
$range_pattern = '/.*\s([^-]+)-([^\/]+)\/(\d+)$/'; 
$header = $this->_get_header_value( UPLOADER_RANGE_HEADER );
$result = $header && preg_match( $range_pattern, $header, $result ) ? $result : false;
return ! ( $result && isset( $key ) ) ? $result : ( isset( $key ) ? $result[$key] : $result );
}
public function get_filename() {
$filename_pattern = '/filename=(\\\\?)(["\'])(.+?)\1\2/'; 
$header = $this->_get_header_value( UPLOADER_FILENAME_HEADER );
if ( $header && preg_match( $filename_pattern, $header, $matches ) ) {
return $this->_sanitize_file_name( $matches[3] );
}
return false;
}
}
?>