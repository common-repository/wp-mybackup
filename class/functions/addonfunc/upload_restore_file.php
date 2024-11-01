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
 * @file    : upload_restore_file.php $
 * 
 * @id      : upload_restore_file.php | Tue Feb 7 08:55:11 2017 +0100 | eugenmihailescu <eugenmihailescux@gmail.com> $
*/

namespace MyBackup;

include_once EDITOR_PATH . 'file-functions.php';
$response = array();
$tmp_dir = isset( $_this_->settings['wrkdir'] ) && ! empty( $_this_->settings['wrkdir'] ) ? $_this_->settings['wrkdir'] : dirname(LOG_DIR);
$tmp_dir = addTrailingSlash( $tmp_dir );
$dropin_dir = $tmp_dir . addTrailingSlash( DROPIN_RESTORE );
_is_dir( $dropin_dir ) || @mkdir( $dropin_dir );
function _getAllowedExtensions() {
global $COMPRESSION_NAMES;
return '\.(' . implode( 
'|', 
array_map( function ( $item ) {
return preg_quote( $item );
}, $COMPRESSION_NAMES ) ) . ')';
}
$is_valid_backup_file = function ( $name, $tmp_filename ) {
global $COMPRESSION_NAMES;
if ( ! _is_file( $tmp_filename ) )
return sprintf( _esc( 'File %s does not exist' ), '<strong>' . $tmp_filename . '</strong>' );
empty( $name ) && $name = $tmp_filename;
$ext = preg_replace( '/(.*\.)([^.]+)$/', '$2', $name );
if ( false !== ( $method = array_search( $ext, $COMPRESSION_NAMES ) ) ) {
$obj = new TarArchive( $tmp_filename, null, false );
try {
return $obj->isValidArchive( $tmp_filename, $method );
} catch ( \Exception $e ) {
return $e->getMessage();
}
}
return sprintf( _esc( '%s - unknown archive type (%s)' ), basename( $name ), $ext );
};
function _get_archive_type( $filename ) {
$types = array();
$allowed_pattern = _getAllowedExtensions();
$wp_components = array_keys( getWPSourceDirList( WPMYBACKUP_ROOT ) );
array_walk( $wp_components, function ( &$item ) {
$item = preg_quote( basename( $item ) );
} );
$name = preg_replace( '/' . $allowed_pattern . '/i', '', $filename );
if ( preg_match( '/[\-.](' . implode( '|', $wp_components ) . ')$/i', $name ) )
$types[] = getTabTitleById( WP_SOURCE );
if ( preg_match( '/[\-.]db|sql$/i', $name ) )
$types[] = getTabTitleById( MYSQL_SOURCE );
empty( $types ) && $types[] = _esc( 'Unknown' );
return $types;
}
$get_response = function ( $error, $code, $params = null, $is_sys_error = false ) {
$message = false != $error ? $error : '';
$response = array( 'success' => false == $error );
false == $error || $response['code'] = $code;
false != $error && $response['message'] = $error;
if ( false !== $error && $is_sys_error ) {
$e = error_get_last();
$response['message'] = $e['message'];
}
if ( ! empty( $params ) ) {
if ( false == $error )
$response['json'] = $params;
elseif ( isset( $params['name'] ) )
$response['json'] = array( 'name' => $params['name'] );
}
return $response;
};
$validate_file = function ( $file, $chunk_upload = false, $filename = false ) use(
&$is_valid_backup_file, 
&$get_response, 
&$dropin_dir ) {
$allowed_pattern = _getAllowedExtensions();
$filename = $filename ? $filename : $file['name'];
if ( empty( $filename ) ) {
$hdr = getallheaders();
if ( isset( $hdr['Content-Disposition'] ) ) {
$filename_pattern = '/filename=(\\\\?)(["\'])(.+?)\1\2/'; 
$header = $hdr['Content-Disposition'];
if ( $header && preg_match( $filename_pattern, $header, $matches ) ) {
$filename = $matches[3];
}
}
}
$result = array( 'name' => $filename );
if ( UPLOAD_ERR_OK == $file['error'] ) {
if ( true === ( $error = $is_valid_backup_file( $filename, $file['tmp_name'] ) ) ) {
if ( ! ( $chunk_upload || rename( $file['tmp_name'], $dropin_dir . basename( $filename ) ) ) ) {
return $get_response( true, 1.1, $result, true );
} else {
return $get_response( 
$file['error'], 
0, 
array_merge( 
$result, 
array( 
'error' => $file['error'], 
'tmp_name' => $file['tmp_name'], 
'crc' => hexdec( @hash_file( 'crc32b', $file['tmp_name'] ) ), 
'size' => $file['size'], 
'type' => implode( 
',', 
_get_archive_type( $dropin_dir . basename( $filename ), $allowed_pattern ) ) ) ) );
}
} else {
@unlink( $file['tmp_name'] );
return $get_response( 
! $error ? sprintf( _esc( 'File %s is not a valid archive' ), $filename ) : $error, 
1.2, 
$result );
}
}
try {
throw new MyUploadException( $file['error'] );
} catch ( \Exception $e ) {
return $get_response( $e->getMessage(), 1.3, $result );
}
};
if ( isset( $_this_->method['delete'] ) ) {
$filename = basename( $_this_->method['delete'] );
if ( _is_file( $dropin_dir . $filename ) ) {
if ( @unlink( $dropin_dir . $filename ) )
$response = $get_response( false, 2.1, array( 'name' => $filename ) );
else {
$response = $get_response( true, 2.2, null, true );
}
} else {
$response = $get_response( 
sprintf( _esc( 'File %s does not exist' ), '<strong>' . $filename . '</strong>' ), 
2.3 );
}
$uploader = new MyChunkUploader( $tmp_dir );
$uploader->_cleanup_parts( $filename );
} 
elseif ( isset( $_this_->method['refresh'] ) ) {
$allowed_pattern = _getAllowedExtensions();
$files = getFileListByPattern( $dropin_dir, '/' . $allowed_pattern . '$/i', false, false, false, 2 );
if ( is_array( $files ) )
foreach ( $files as $filename ) {
$name = basename( $filename );
if ( $is_valid_backup_file( $name, $filename ) )
$response[] = $get_response( 
false, 
0, 
array( 
'name' => $name, 
'size' => filesize( $filename ), 
'type' => implode( ',', _get_archive_type( $filename, $allowed_pattern ) ) ) );
}
} 
else {
$uploader = new MyChunkUploader( $tmp_dir );
$ok_files = false;
$filename = ( $chunk_upload = $uploader->may_run() ) ? $uploader->get_filename() : false;
if ( $file = end( $_FILES ) ) {
$file['name'] = $filename; 
if ( ! ( $chunk_upload && $uploader->get_range( 1 ) ) ) {
$response = $validate_file( $file, $chunk_upload, $filename );
$ok_files = $response['success'];
$file['error'] = ! $response['success'];
} else
$ok_files = true;
}
if ( $chunk_upload && ( $ok_files || $uploader->is_waiting() ) ) {
function on_chunk_upload_done( $filename, $tmp_dir ) {
$dropin_dir = addTrailingSlash( $tmp_dir ) . addTrailingSlash( DROPIN_RESTORE );
$dest_filename = $dropin_dir . basename( $filename );
if ( rename( $filename, $dest_filename ) )
return $dest_filename;
return false;
}
function on_chunk_on_get_type( $filename, $content_type ) {
return implode( ',', _get_archive_type( $filename, _getAllowedExtensions() ) );
}
function on_chunk_upload_new_nonce() {
return wp_create_nonce_wrapper( preg_replace( '/(.+?)\.?[^.]*$/', '$1', basename( __FILE__ ) ) );
}
$uploader->on_new_nonce = __NAMESPACE__ . '\\on_chunk_upload_new_nonce';
$uploader->on_done = __NAMESPACE__ . '\\on_chunk_upload_done';
$uploader->on_get_type = __NAMESPACE__ . '\\on_chunk_on_get_type';
}
( $ok_files || $uploader->is_waiting() ) && $uploader->run();
! empty( $response ) && ( $response['success'] ||
$uploader->_set_error( $response['message'], $response['code'], false ) );
}
die( json_encode( $response, JSON_FORCE_OBJECT ) );
?>