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
 * @file    : wp_restore.php $
 * 
 * @id      : wp_restore.php | Tue Feb 7 08:55:11 2017 +0100 | eugenmihailescu <eugenmihailescux@gmail.com> $
*/

namespace MyBackup;

require_once LIB_PATH . 'MyException.php';
require_once CLASS_PATH . 'AbstractJob.php';
require_once FUNCTIONS_PATH . 'download.php';
require_once ABSPATH . 'wp-admin/includes/class-wp-upgrader.php';
require_once EDITOR_PATH . 'file-functions.php';
class WP_MyBackup_Upgrader extends \Core_Upgrader {
public function __construct( $skin = null ) {
parent::__construct( $skin );
$this->init();
}
private function _copy( $from, $to_dir ) {
if ( ! _is_file( $from ) )
return false;
_is_dir( $to_dir ) || mkdir( $to_dir, 0755, true );
return copy( $from, trailingslashit( $to_dir ) . basename( $from ) );
}
private function _fix_insane_distro( $tmpdir ) {
$wp_root = ABSPATH;
$readme = 'readme.html';
$version = '/version.php';
$wp_settings = 'wp-settings.php';
$wp_admin = 'wp-admin';
$admin = $wp_admin . DIRECTORY_SEPARATOR . 'admin.php';
$functions = '/functions.php';
_is_file( $tmpdir . $readme ) || $this->_copy( $wp_root . $readme, $tmpdir );
_is_file( $tmpdir . $wp_settings ) || $this->_copy( $wp_root . $wp_settings, $tmpdir );
_is_file( $tmpdir . $admin ) || $this->_copy( $wp_root . $admin, $tmpdir . $wp_admin );
_is_file( $tmpdir . WPINC . $version ) || $this->_copy( $wp_root . WPINC . $version, $tmpdir . WPINC );
_is_file( $tmpdir . WPINC . $functions ) || $this->_copy( $wp_root . WPINC . $functions, $tmpdir . WPINC );
}
public function upgrade( $current, $args = array() ) {
global $wp, $wp_filesystem;
$url = home_url( add_query_arg( array(), $wp->request ) );
if ( false === ( $credentials = request_filesystem_credentials( $url ) ) ) {
return new \WP_Error( 'request_filesystem_credentials', _esc( 'Cannot connect local filesystem.' ) );
}
if ( !\WP_Filesystem( $credentials ) ) {
request_filesystem_credentials( $url, '', true ); 
return new \WP_Error( 'request_filesystem_credentials', _esc( 'Cannot connect local filesystem.' ) );
}
if ( ! is_object( $wp_filesystem ) )
return new \WP_Error( 'fs_unavailable', _esc( 'Could not access filesystem.' ) );
if ( is_WP_Error( $wp_filesystem->errors ) && $wp_filesystem->errors->get_error_code() )
return new \WP_Error( 'fs_error', _esc( 'Filesystem error.' ), $wp_filesystem->errors );
$wp_dir = ABSPATH;
$working_dir = trailingslashit( $current->download );
$update_core = 'wp-admin/includes/update-core.php';
$update_core_dir = dirname( $working_dir . $update_core );
if ( ! ( _is_dir( $update_core_dir ) || mkdir( $update_core_dir, 0755, true ) ) ) {
return new \WP_Error( 
'mkdir_failed_for_update_core_file', 
_esc( 'Could not create the temporary upgrade directory' ), 
$update_core_dir );
}
if ( ! $this->_copy( $wp_dir . $update_core, $update_core_dir ) ) {
return new \WP_Error( 
'prepare_failed_for_update_core_file', 
_esc( 
'The update cannot be installed because we will be unable to copy some files. This is usually due to inconsistent file permissions.' ), 
array( $wp_dir, $working_dir ) );
}
return parent::upgrade( $current, $args );
}
public function unpack_package( $package, $delete_package = true ) {
$tmpdir = is_a( $package, 'stdClass' ) ? $package->full : $package;
$this->_fix_insane_distro( $tmpdir );
return dirname( $tmpdir ) ;
}
public function download_package( $package ) {
return $package;
}
}
class WP_MyBackup_Restore extends AbstractJob {
private $_method; 
private $_components_files; 
private $_components_mysql; 
private $_components_wp; 
private $_point; 
private $_date; 
private $_date_from;
private $_date_to;
private $_target; 
private $_path; 
private $_disk; 
private $_reconcile; 
private $_point_selection;
private $_uncompress_start;
private $_dropin;
private $_backup_job_id;
private $_files_id;
private $_filter_operation;
private $_args;
function __construct( $opts = null, $sender = '' ) {
parent::__construct( $opts, $sender );
date_default_timezone_set( wp_get_timezone_string() );
if ( ! is_session_started( false ) && ! headers_sent() )
session_start();
session_write_close();
$this->_backup_job_id = 0;
$this->_files_id = array();
$this->_filter_operation = '';
$this->_args = array();
}
private function _check_mysql() {
$throw_db_error = function ( $obj ) {
$error = $obj->get_last_error();
throw new MyException( $error['message'], $error['code'] );
};
try {
$this->_statistics_manager->isSQLite() ||
$this->_statistics_manager->_sqlExec( 'SET SESSION query_cache_type = OFF', true );
} catch ( \Exception $e ) {
}
$obj = new MySQLWrapper( $this->getOptions() );
$link = $obj->connect();
$error = true;
if ( $link ) {
$tbl_name = uniqid( '_dummy_' );
$fld_name = 'dummyField';
$test_value = 100;
if ( $obj->query( sprintf( 'CREATE TABLE %s (%s INTEGER)', $tbl_name, $fld_name ) ) ) {
if ( false !== ( $rst_1 = $obj->query( sprintf( "SHOW TABLES LIKE '" . $tbl_name . "'" ) ) ) &&
$obj->get_rows_count( $rst_1 ) ) {
if ( $obj->query( sprintf( 'INSERT INTO %s (%s) VALUES (%s)', $tbl_name, $fld_name, $test_value ) ) ) {
if ( false !== ( $rst_2 = $obj->query( 
sprintf( 'SELECT %s from %s WHERE %s=%d', $fld_name, $tbl_name, $fld_name, $test_value ) ) ) ) {
$error = ! $obj->get_rows_count( $rst_2 );
$obj->free_result( $rst_2 );
}
}
$obj->query( sprintf( 'DROP TABLE %s', $tbl_name ) );
$obj->free_result( $rst_1 );
}
}
}
$error && $throw_db_error( $obj );
$obj->disconnect();
$obj = null;
return ! $error;
}
private function _restoreFiles( $dest_path = null ) {
if ( ! _is_dir( $dest_path ) ) {
return new \WP_Error( 'restore_files_destpath', _esc( 'The upgrade path does not exists' ), $dest_path );
}
include ( ABSPATH . WPINC . '/version.php' );
global $wpdb, $wp_version;
if ( method_exists( $wpdb, 'db_version' ) )
$mysql_version = preg_replace( '/[^0-9.].*/', '', $wpdb->db_version() );
else
$mysql_version = 'N/A';
$update = (object) array( 
'response' => 'reinstall', 
'download' => $dest_path, 
'locale' => apply_filters( 'core_version_check_locale', get_locale() ), 
'packages' => (object) array_fill_keys( 
array( 'full', 'no_content', 'new_bundled', 'partial', 'rollback' ), 
'' ), 
'current' => $wp_version, 
'version' => $wp_version, 
'php_version' => phpversion(), 
'mysql_version' => $mysql_version, 
'new_bundled' => $wp_version, 
'partial_version' => '' );
$upgrader = new WP_MyBackup_Upgrader();
$update->packages->full = $dest_path;
$update->package = $update->packages;
$_this_ = &$this;
add_action( 
'_core_updated_successfully', 
function () use(&$_this_ ) {
$_this_->logOutputTimestamp( _esc( 'WordPress files restored successfully' ), BULLET );
} );
$this->onProgress( TMPFILE_SOURCE, $dest_path, 0, 1, 7, - 1 );
$result = $upgrader->upgrade( $update );
$this->onProgress( TMPFILE_SOURCE, $dest_path, 1, 1, 7, - 1 );
return $result;
}
private function _restoreMySQL( $files, $source_type, $timestamp ) {
$error = false;
$ok_sql = 0;
include_once ADDONFUNC_PATH . 'restore_mysql.php';
return ! $error || $ok_sql;
}
private function findDropinFiles() {
global $COMPRESSION_NAMES;
$result = array();
$allowed_pattern = '\.(' . implode( 
'|', 
array_map( function ( $item ) {
return preg_quote( $item );
}, $COMPRESSION_NAMES ) ) . ')';
$is_valid_backup_file = function ( $name, $filename ) {
global $COMPRESSION_NAMES;
if ( ! _is_file( $filename ) )
return sprintf( _esc( 'File %s does not exist' ), '<strong>' . $filename . '</strong>' );
$ext = preg_replace( '/(.*\.)([^.]+)$/', '$2', $name );
if ( false !== ( $method = array_search( $ext, $COMPRESSION_NAMES ) ) ) {
$obj = new TarArchive( $filename, null, false );
try {
return $obj->isValidArchive( $filename, $method );
} catch ( \Exception $e ) {
return $e->getMessage();
}
}
return sprintf( _esc( 'Unknown archive type (%s)' ), $ext );
};
$get_archive_type = function ( $filename ) use(&$allowed_pattern ) {
$types = array();
$wp_components = array_keys( getWPSourceDirList( WPMYBACKUP_ROOT ) );
array_walk( $wp_components, function ( &$item ) {
$item = preg_quote( basename( $item ) );
} );
$name = preg_replace( '/' . $allowed_pattern . '/i', '', $filename );
if ( preg_match( '/[\-.](' . implode( '|', $wp_components ) . ')$/i', $name ) )
return WP_SOURCE;
if ( preg_match( '/[\-.]db|sql$/i', $name ) )
return MYSQL_SOURCE;
return SRCFILE_SOURCE;
};
$wrk_dir = $this->getWorkDir();
$wrk_dir = ! empty( $wrk_dir ) ? $this->getWorkDir() : dirname(LOG_DIR);
$wrk_dir = addTrailingSlash( addTrailingSlash( $wrk_dir ) . DROPIN_RESTORE );
$files = getFileListByPattern( $wrk_dir, '/' . $allowed_pattern . '$/i', false, false, false, 2 );
if ( is_array( $files ) )
foreach ( $files as $filename ) {
$name = basename( $filename );
if ( $is_valid_backup_file( $name, $filename ) )
$result[] = array( 
'target' => DISK_TARGET, 
'mode' => BACKUP_MODE_FULL, 
'name' => $name, 
'size' => filesize( $filename ), 
'date' => filemtime( $filename ), 
'source_path' => $wrk_dir, 
'source_type' => $get_archive_type( $name ), 
'dest_path' => $wrk_dir, 
'volumes' => 0, 
'compression_type' => preg_replace( '/(.*\.)([^.]+)$/', '$2', $name ), 
'uncompressed' => 0, 
'checksum' => file_checksum( $filename ) );
}
return $result;
}
public function findBackups() {
global $COMPRESSION_NAMES;
if ( ! $this->_history_enabled )
throw new MyException( _esc( 'Job history not enabled' ) );
$sql = 'SELECT ' . TBL_PREFIX . TBL_PATHS . '.id, ' . TBL_PREFIX . TBL_JOBS . '.volumes_count, ' . TBL_PREFIX .
TBL_JOBS . '.mode, ' . TBL_PREFIX . TBL_JOBS . '.compression_type, ' . TBL_PREFIX . TBL_JOBS .
'.started_time, ' . TBL_PREFIX . TBL_PATHS . '.path AS dest_path, ' .
sqlFloor( TBL_PREFIX . TBL_PATHS . '.operation/2', $this->_statistics_manager->isSQLite() ) .
' AS dest_operation, ' . TBL_PREFIX . TBL_SOURCES . '.source_type, ' . TBL_PREFIX . TBL_SOURCES .
'.path AS source_path, ' . TBL_PREFIX . TBL_FILES . '.filename AS tmp_filename, ' . TBL_PREFIX . TBL_FILES .
'.uncompressed, ' . TBL_PREFIX . TBL_FILES . '.filesize, ' . TBL_PREFIX . TBL_FILES . '.checksum FROM ' .
TBL_PREFIX . TBL_JOBS . ' INNER JOIN ' . TBL_PREFIX . TBL_PATHS . ' ON ' . TBL_PREFIX . TBL_JOBS . '.id = ' .
TBL_PREFIX . TBL_PATHS . '.jobs_id INNER JOIN ' . TBL_PREFIX . TBL_FILES . ' ON ' . TBL_PREFIX . TBL_JOBS .
'.id = ' . TBL_PREFIX . TBL_FILES . '.jobs_id INNER JOIN ' . TBL_PREFIX . TBL_SOURCES . ' ON ' . TBL_PREFIX .
TBL_FILES . '.jobs_id = ' . TBL_PREFIX . TBL_SOURCES . '.jobs_id AND ' . TBL_PREFIX . TBL_FILES .
'.sources_id = ' . TBL_PREFIX . TBL_SOURCES . '.id';
$sql .= PHP_EOL;
$filter = array( 
TBL_PREFIX . TBL_JOBS . '.id=' . $this->_backup_job_id, 
TBL_PREFIX . TBL_PATHS . '.operation>=0' );
if ( ! strToBool( $this->options['downloadforcebly'] ) ) {
$filter[] = TBL_PREFIX . TBL_FILES . '.checksum IS NOT NULL';
}
empty( $this->_files_id ) ||
$filter[] = TBL_PREFIX . TBL_FILES . '.id IN (' . implode( ',', $this->_files_id ) . ')';
empty( $this->_filter_operation ) || $filter[] = sqlFloor( 
TBL_PREFIX . TBL_PATHS . '.operation/2', 
$this->_statistics_manager->isSQLite() ) . '=' . ( intval( $this->_filter_operation ) - 1 );
count( $filter ) > 0 && $sql .= 'WHERE ' . implode( ' AND ', $filter ) . PHP_EOL;
$result = array();
if ( $rst = $this->_statistics_manager->queryData( $sql ) ) {
while ( $row = $this->_statistics_manager->fetchArray( $rst ) ) {
$tmp_file = addTrailingSlash( $row['dest_path'], '/' ) . basename( $row['tmp_filename'] );
if ( 0 == $row['dest_operation'] && ! _file_exists( $tmp_file ) ) {
$this->outputError( 
'<yellow>' . sprintf( 
_esc( 'File %s skipped due to it does not exist' ), 
shorten_path( $tmp_file ) ) . '</yellow>' );
continue;
}
$result[] = array( 
'target' => $row['dest_operation'] + 1, 
'mode' => $row['mode'], 
'name' => basename( $row['tmp_filename'] ), 
'size' => $row['filesize'], 
'date' => $row['started_time'], 
'source_path' => $row['source_path'], 
'source_type' => $row['source_type'], 
'dest_path' => addTrailingSlash( $row['dest_path'], '/' ), 
'volumes' => $row['volumes_count'], 
'compression_type' => empty( $row['compression_type'] ) ? null : $COMPRESSION_NAMES[$row['compression_type']], 
'uncompressed' => $row['uncompressed'], 
'checksum' => $row['checksum'] );
}
$this->_statistics_manager->freeResult( $rst );
}
return $result;
}
private function _getRestoreFiles() {
$this->logOutputTimestamp( _esc( 'Searching for restore points on local job history' ) );
$result = $this->_dropin ? $this->findDropinFiles() : $this->findBackups();
$this->logOutputTimestamp( sprintf( _esc( 'found %d restore points' ), count( $result ) ), BULLET, 2 );
return $result;
}
public function _copyFromDisk( $target, $fileinfo, $operation ) {
return addTrailingSlash( $fileinfo['dest_path'] ) . $fileinfo['name'];
}
public function _downloadFromStorage( $target, $fileinfo, $operation ) {
$filename = $fileinfo['dest_path'] . $fileinfo['name'];
try {
$api_fct = in_array( 
$target, 
array( $this->getTargetConstant( 'SSH_TARGET' ), $this->getTargetConstant( 'FTP_TARGET' ) ) ) ? 'initRemoteStorage' : 'initCloudStorage';
$api = $this->$api_fct( $target, $filename, $fileinfo['size'], true );
if ( ! is_object( $api ) ) {
$this->outputError( 
sprintf( _esc( 'Function %s returned a non-object. This should never happen.' ), $api_fct ) );
return false;
}
$tmp_free_space = _disk_free_space( $this->getWorkDir() );
if ( $fileinfo['uncompressed'] > $tmp_free_space )
$this->outputError( 
sprintf( 
_esc( 
"<red>[!] file %s cannot be downloaded from %s due to insufficient disk space on %s (free: %s, needs: %s)</red>" ), 
$fileinfo['name'], 
$target_name, 
$this->getWorkDir(), 
getHumanReadableSize( $tmp_free_space ), 
getHumanReadableSize( $filesize ) ), 
false, 
$err_params );
else {
$out_filename = addTrailingSlash( $this->getWorkDir() ) . $fileinfo['name'];
if ( $this->getTargetConstant( 'GOOGLE_TARGET' ) == $target && false != ( $metadata = $api->searchFileNames( 
$fileinfo['dest_path'], 
$this->getSearchFilter( $target, $fileinfo['name'], true ) ) ) )
isset( $metadata['items'] ) && $filename = $metadata['items'][count( $metadata['items'] ) - 1]['id'];
$result = $api->downloadFile( $filename, $out_filename ) ? $out_filename : false;
if ( ! $api->curlAborted() )
$this->parseResponse( $result );
if ( filesize( $out_filename ) < 1024 ) {
$array = json_decode( file_get_contents( $out_filename ), true );
if ( isset( $array['error'] ) )
throw new MyException( $array['error'] );
}
}
} catch ( \Exception $e ) {
$err_params = $this->getOperErrParams( $filename, $operation, $fileinfo['size'], true );
$this->outputError( formatErrMsg( $e ), false, $err_params );
$result = false;
}
return $result;
}
private function _dwlFiles( $array ) {
$downloaded = function ( $checksum ) use(&$array ) {
foreach ( $array as $key => $file )
if ( isset( $file['checksum'] ) && ( $file['checksum'] == $checksum ) && isset( $file['tmp'] ) &&
$file['tmp'] )
return $key;
return false;
};
uasort( 
$array, 
function ( $file1, $file2 ) {
return $file1['target'] == DISK_TARGET ? - 1 : ( $file2['target'] == DISK_TARGET ? 1 : 0 );
} );
foreach ( $array as $key => $file ) {
if ( $this->chkProcessSignal() )
break;
if ( isset( $file['checksum'] ) && ( $tmp_key = $downloaded( $file['checksum'] ) ) ) {
if ( false !== $tmp_key ) {
$array[$key]['tmp'] = false;
$array[$key]['del_tmp'] = false;
}
continue;
}
$src_file = addTrailingSlash( $file['dest_path'] ) . $file['name'];
$delete_after_func = true;
$is_secure = false;
switch ( $file['target'] ) {
case $this->getTargetConstant( 'DISK_TARGET' ) :
$func_name = '_copyFromDisk';
$delete_after_func = $this->_dropin;
break;
case $this->getTargetConstant( 'FTP_TARGET' ) :
case $this->getTargetConstant( 'SSH_TARGET' ) :
case $this->getTargetConstant( 'DROPBOX_TARGET' ) :
case $this->getTargetConstant( 'GOOGLE_TARGET' ) :
case $this->getTargetConstant( 'WEBDAV_TARGET' ) :
$func_name = '_downloadFromStorage';
$is_secure = true;
break;
}
list( $target_name, $oper_send, $oper_sent ) = $this->getTargetOperConsts( $file['target'], true );
$this->startTransfer( $oper_send, $src_file, dirname( $src_file ), $target_name, $is_secure, $file['size'] );
if ( ! ( $array[$key]['tmp'] = _call_user_func( 
array( $this, $func_name ), 
$file['target'], 
$file, 
$oper_send ) ) )
continue;
$array[$key]['del_tmp'] = $delete_after_func;
$this->stopTransfer( $oper_sent, $src_file, $file['size'], $file['uncompressed'] );
}
return $array;
}
private function _extractFiles( $array, $dst_path ) {
global $COMPRESSION_ARCHIVE;
$result = array();
foreach ( $array as $key => $file ) {
if ( $this->chkProcessSignal() )
break;
if ( ! ( isset( $file['tmp'] ) && $file['tmp'] ) )
continue;
$tmp_file = $file['tmp'];
if ( ! isset( $tmp_file ) || false == $tmp_file )
continue;
if ( ! _file_exists( $tmp_file ) )
throw new MyException( 
sprintf( _esc( 'Cannot extract the archive %s. File does not exist' ), $tmp_file ) );
$checksum = file_checksum( $tmp_file );
$delete_after_extract = $file['del_tmp'];
if ( $checksum != $file['checksum'] ) {
$this->outputError( 
'<yellow>' . sprintf( _esc( '[!] Warning : invalid MD5 checksum on %s' ), $tmp_file ) . '</yellow>', 
false, 
null, 
null, 
0 );
$this->outputError( 
'<yellow>' . sprintf( _esc( 'expected %s, found %s)' ), $file['checksum'], $checksum ) . '</yellow>', 
false, 
null, 
BULLET, 
1 );
if ( ! $this->getExtractForcebly() )
throw new MyException( _esc( 'Cannot continue due to invalid MD5 checksum' ) );
}
$extension = preg_match( '/.*\.([^.]+)/', $tmp_file, $matches ) ? $matches[1] : null;
$tmp_size = $file['size'];
if ( 'enc' == $extension ) {
if ( false === ( $tmp_file = $this->decrypt( $tmp_file ) ) )
throw new MyException( sprintf( _esc( 'Could not decrypt the file %s' ), $tmp_file ) );
$tmp_size = filesize( $tmp_file );
}
$compression_method = NONE;
if ( preg_match( '/.*\.([^.]+)/', $tmp_file, $matches ) ) {
$ext = strtoupper( $matches[1] );
if ( 'ZIP' == $ext && ! @constant( __NAMESPACE__ . '\\' . $ext ) )
$ext = 'PCLZIP';
$compression_method = @constant( __NAMESPACE__ . '\\' . $ext );
}
$this->setCompressionMethod( $compression_method );
$archive_classname = __NAMESPACE__ . '\\' .
( null !== $compression_method ? $COMPRESSION_ARCHIVE[$compression_method] : 'TarArchive' );
$archive = new $archive_classname( $tmp_file, null, false );
$archive->setCPUSleep( $this->getCPUSleep() );
$archive->onAbortCallback = array( $this, 'chkProcessSignal' );
$archive->onProgressCallback = array( $this, 'onProgress' );
$archive->onStdOutput = array( $this, 'logOutputTimestamp' );
$this->startCompress( $tmp_file, null, true );
try {
$archive_filename = $archive->decompress( null, $file['uncompressed'] );
if ( false !== $archive_filename ) {
$this->logOutputTimestamp( 
sprintf( 
_esc( 'extracting files from %s to %s' ), 
getSpan( shorten_path( $archive_filename ), 'cyan' ), 
shorten_path( $dst_path ) ), 
BULLET, 
2 );
$file['files'] = $archive->extract( $archive_filename, $dst_path, $this->getExtractForcebly() );
if ( $this->_dropin || DISK_TARGET != $file['target'] ||
in_array( $compression_method, array( BZ2, GZ ) ) )
unlink( $archive_filename );
$archive_filename = null;
! $file['uncompressed'] && $file['files'] && $file['uncompressed'] = getFilesSize( $file['files'] );
$result[] = $file;
$ratio = $file['uncompressed'] / $tmp_size;
$vol_no = $file['volumes'] < 2 ? 1 : ( preg_match( 
'/(([^\-]*)-)*(\d)*\.(tar|zip|rar).*?(\.enc$)/', 
$file['volumes'], 
$matches ) ? $matches[3] : 0 );
$this->stopCompress( $tmp_file, $file['uncompressed'], $ratio, $vol_no, true );
} else
$this->outputError( sprintf( _esc( 'Could not decompress the file %s' ), $tmp_file ) );
} catch ( \Exception $e ) {
$this->outputError( formatErrMsg( $e ) );
}
isset( $archive_filename ) && _file_exists( $archive_filename ) && @unlink( $archive_filename );
if ( $delete_after_extract && ( $this->_dropin || DISK_TARGET != $file['target'] || 'enc' == $extension ) )
@unlink( $tmp_file );
}
return $result;
}
public function run( $job_type = JOB_BACKUP ) {
$job_type = - 4;
parent::run( $job_type );
$start = time();
$cpusleep = $this->getCPUSleep();
$timestamp = time();
$dirs = array();
$ok_status = _esc( 'successfully' );
$status = _esc( 'unexpectedly' );
$aborted = false;
$exit_unexpectedly = false;
$file_ok = true;
$sql_ok = true;
$at_least_one_file = 0;
$at_least_one_sql = 0;
$arc_count = 0;
try {
$this->logSeparator();
$files = $this->_getRestoreFiles();
if ( ! ( $aborted = $this->_is_job_aborted( $aborted ) ) )
$tmp_files = $this->_dwlFiles( $files ); 
$tmp_dir = ABSPATH . implode( DIRECTORY_SEPARATOR, array( 'wp-content', 'upgrade', 'wordpress', '' ) );
$wp_arcs = array_filter( 
$tmp_files, 
function ( $item ) {
return in_array( $item['source_type'], array( SRCFILE_SOURCE, WP_SOURCE ) );
} );
$mysql_arcs = array_filter( 
$tmp_files, 
function ( $item ) {
return MYSQL_SOURCE == $item['source_type'];
} );
$extracted_files = array();
$extracted_mysql = array();
if ( ! ( $aborted = $this->_is_job_aborted( $aborted ) ) )
! empty( $wp_arcs ) && $extracted_files = $this->_extractFiles( $wp_arcs, $tmp_dir );
if ( ! ( $aborted = $this->_is_job_aborted( $aborted ) ) )
! empty( $mysql_arcs ) && $extracted_mysql = $this->_extractFiles( $mysql_arcs, $this->getWorkDir() );
$f_restored = 0;
! $this->getVerbosity( VERBOSE_COMPACT ) && $this->logSeparator();
$this->getProgressManager()->cleanUp();
$mysql_rolledback = 0;
if ( ! ( $aborted = $this->_is_job_aborted( $aborted ) ) )
if ( $sql_ok && ! empty( $extracted_mysql ) ) {
if ( $sql_ok = $this->_check_mysql() ) {
foreach ( $extracted_mysql as $file_header ) {
$sql_ok = true;
$this->getVerbosity( VERBOSE_COMPACT ) && $this->logSeparator();
$this->logOutputTimestamp( 
sprintf( 
_esc( 'Restoring the %s from %s' ), 
_esc( 'MySQL database' ), 
getSpan( basename( $file_header['name'] ), 'cyan' ) ) );
if ( isset( $file_header['files'] ) && ! empty( $file_header['files'] ) ) {
foreach ( $file_header['files'] as $src_file ) {
$path = DIRECTORY_SEPARATOR == substr( $src_file, - 1 ) ? $src_file : addTrailingSlash( 
dirname( $src_file ) );
! in_array( $path, $dirs ) && $path != $this->getWorkDir() && $dirs[] = $path;
}
if ( $sql_ok ) {
if ( $sql_ok = $this->_restoreMySQL( 
$file_header['files'], 
$file_header['source_type'], 
$timestamp ) ) {
$this->logOutput( 
'<white>' . sprintf( 
_esc( '<b>SUBTOTAL</b> : MySQL restored from %s' ), 
$file_header['name'] ) . '</white>' );
$this->logSeparator();
} else
$mysql_rolledback++;
}
} else
$this->logOutputTimestamp( 
'<yellow>[!] ' . _esc( 
'Skipping : the TAR archive either has no file or could not be extracted properly (see errors above)' ) .
'</yellow>', 
BULLET ) && $sql_ok = false;
$status = $sql_ok ? $ok_status : _esc( 'with errors' );
$this->setError( null );
}
} else
$this->logOutputTimestamp( 
'<yellow>[!] ' . _esc( 'Skipping : cannot create tables or insert MySQL data' ) . '</yellow>', 
BULLET ) && $sql_ok = false;
}
$skipped_files = 0;
if ( ! ( $aborted = $this->_is_job_aborted( $aborted ) ) )
if ( $sql_ok || ! $mysql_rolledback ) {
if ( ! empty( $extracted_files ) ) {
$this->logOutputTimestamp( 
sprintf( 
_esc( 'Restoring WordPress file from %s' ), 
getSpan( shorten_path( $tmp_dir ), 'cyan' ) ) );
$result = $this->_restoreFiles( $tmp_dir );
if ( is_WP_Error( $result ) ) {
$file_ok = 'up_to_date' == $result->get_error_code();
! $file_ok && $this->outputError( 
sprintf( '<red>[!] : %s</red>', $result->get_error_message() ), 
true, 
null, 
null, 
0 );
} else {
$f_restored += array_reduce( 
$extracted_files, 
function ( $carry, $item ) {
return $carry + count( $item['files'] );
}, 
0 );
$this->logOutput( 
'<white>' . sprintf( 
_esc( "<b>SUBTOTAL</b> : %s files restored from %s" ), 
getSpan( $f_restored, '#fff', 'bold' ), 
shorten_path( $tmp_dir ) ) . '</white>' );
$this->logSeparator();
$this->setError( null );
}
$status = $file_ok ? $ok_status : _esc( 'with errors' );
}
} else
$skipped_files++;
$at_least_one_file = count( $extracted_files ) ? count( $extracted_files ) - $skipped_files : 0;
$at_least_one_sql = count( $extracted_mysql ) ? count( $extracted_mysql ) - $mysql_rolledback : 0;
$arc_count = $at_least_one_file + $at_least_one_sql;
if ( ! ( $aborted = $this->_is_job_aborted( $aborted ) ) && ( $at_least_one_file || $at_least_one_sql ) ) {
$restore_details = array();
empty( $extracted_files ) || $restore_details[] = sprintf( _esc( '%d files' ), $at_least_one_file );
empty( $extracted_mysql ) || $restore_details[] = sprintf( _esc( '%d MySQL' ), $at_least_one_sql );
$this->logOutput( 
sprintf( 
"<white><b>" . _esc( 'GRAND TOTAL' ) . "</b></white> : " .
_esc( '%d archives (%s) restored to %s' ), 
$arc_count, 
implode( ', ', $restore_details ), 
'custom' == $this->_path ? $this->_disk : _esc( 'their default location' ) ) );
}
} catch ( \Exception $e ) {
$status = _esc( 'with errors' ) . ':<br> - ' . $e->getMessage();
$this->setError( $e );
$arclist = false;
$exit_unexpectedly = true;
}
$elapsed_time = time() - $start;
$this->logSeparator();
$job_status = 'JOB_STATUS_FINISHED';
if ( $sql_ok && $file_ok ) {
$aborted && $job_status = 'JOB_STATUS_ABORTED';
$job_state = ! ( $aborted || $mysql_rolledback || $exit_unexpectedly ) && $arc_count ? 'JOB_STATE_COMPLETED' : 'JOB_STATE_PARTIAL';
} else {
$job_state = ( $at_least_one_file || $at_least_one_sql ) && ! $exit_unexpectedly ? 'JOB_STATE_PARTIAL' : 'JOB_STATE_FAILED';
}
$this->_job_state = @constant( __NAMESPACE__ . '\\' . $job_state );
$this->_job_status = @constant( __NAMESPACE__ . '\\' . $job_status );
$this->onJobEnds( 
array( 
'duration' => $elapsed_time, 
'avg_cpu' => get_system_load( $elapsed_time ), 
'job_status' => $job_status, 
'job_state' => $job_state, 
'files_count' => $arc_count ) );
if ( $ok_status != $status ) {
$prefix = '<red>';
$sufix = '</red>';
} else {
$prefix = '<white>';
$sufix = '</white>';
}
if ( $aborted = $this->_is_job_aborted( $aborted ) ) {
$status = _esc( 'with abort signal.' );
$prefix = '<yellow>';
$sufix = '</yellow>';
$this->ackProcessSignal();
}
$this->logOutput( 
$prefix . sprintf( 
_esc( '<b>Job finished %s.<br>Total elapsed time %s</b>' ), 
$status, 
timeFormat( $elapsed_time ) ) . $sufix );
$this->logSeparator();
$this->sendEmailReport();
$this->logSeparator();
$this->printJobSection( false, true ); 
$this->addMessage( 
$ok_status == $status ? MESSAGE_TYPE_NORMAL : MESSAGE_TYPE_WARNING, 
sprintf( _esc( 'New restore job run by %s (%s)' ), $this->getSender(), $status ), 
empty( $job_id ) ? 0 : $job_id );
$this->getProgressManager()->cleanUp();
}
protected function _beforeRun() {
$this->_dropin = isset( $this->_args['dropin'] ) ? $this->_args['dropin'] : false;
$this->_backup_job_id = isset( $this->_args['job_id'] ) ? $this->_args['job_id'] : 0;
$this->_files_id = isset( $this->_args['wp_components'] ) ? $this->_args['wp_components'] : array();
$this->_filter_operation = isset( $this->_args['filter'] ) ? $this->_args['filter'] : '';
}
public function init( $args ) {
$this->_args = $args;
}
}
if ( $dropin = isset( $_this_->method['dropin'] ) ? $_this_->method['dropin'] : 0 ) {
$args = array( 'dropin' => true );
} else {
$job_id = isset( $_this_->method['job_id'] ) ? $_this_->method['job_id'] : 0;
$files_ids = isset( $_this_->method['wp_components'] ) ? explode( '|', $_this_->method['wp_components'] ) : array();
$filter = isset( $_this_->method['filter'] ) ? $_this_->method['filter'] : '';
if ( ! ( $job_id > 0 && ! empty( $_this_->method['wp_components'] ) ) ) {
( $job_id > 0 ) || printf( _esc( 'Invalid job id: %s' ) . '<br>', $job_id );
empty( $_this_->method['wp_components'] ) && print ( _esc( 'No WP component selected to be restored' ) ) ;
return;
}
$args = array( 'job_id' => $job_id, 'wp_components' => $files_ids, 'filter' => $filter );
}
try {
$wprst = new WP_MyBackup_Restore( $_this_->params, ADMIN_ASYNC_IFNAME );
$wprst->init( $args );
$wprst->run( - 4 );
} catch ( \Exception $e ) {
echo $e->getMessage();
}
?>