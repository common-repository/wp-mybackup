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
 * @file    : config-custom.php $
 * 
 * @id      : config-custom.php | Tue Feb 7 08:55:11 2017 +0100 | eugenmihailescu <eugenmihailescux@gmail.com> $
*/

namespace MyBackup;

if ( ! @constant( 'ABSPATH' ) )
define( __NAMESPACE__.'\\ALT_ABSPATH', ROOT_PATH );
else
define( __NAMESPACE__.'\\ALT_ABSPATH', str_replace( '/', DIRECTORY_SEPARATOR, ABSPATH ) );
include_once 'config-fixes.php';
require_once CLASS_PATH . 'constants.php';
require_once CLASS_PATH . 'autoloader.php';
define( __NAMESPACE__.'\\BENCHMARK_RANDWORDS_FILE', '/usr/share/dict/words' );
define( __NAMESPACE__.'\\CYGWIN_PATH', "C:\cygwin\bin\bash.exe" ); 
define( __NAMESPACE__.'\\PBZIP2', false ); 
define( __NAMESPACE__.'\\BENCHMARK_FILE_SIZE', 100 ); 
define( __NAMESPACE__.'\\TIME_FORMAT', 'H:i:s' );
define( __NAMESPACE__.'\\DATETIME_FORMAT', 'Y-m-d ' . TIME_FORMAT );
define( __NAMESPACE__.'\\MORE_ENTROPY', false ); 
define( __NAMESPACE__.'\\SYST_USAGE_THRESOLD', 75 ); 
define( __NAMESPACE__.'\\PROGRESS_LAZYWRITE', true ); 
define( __NAMESPACE__.'\\LOG_CHECK_TIMEOUT', 10000 ); 
define( __NAMESPACE__.'\\LONG_RUNNING_JOB_TIMEOUT', 1000 ); 
if ( ! function_exists( '\\add_management_page' ) ) {
$set_db = function ( $name, $value ) {
(null !== @constant( $name )) || define( $name, $value );
$_dummy = ''; 
};
$set_db( 'DB_NAME', '' );
$set_db( 'DB_USER', '' );
$set_db( 'DB_PASSWORD', '' );
$set_db( 'DB_HOST', 'localhost' );
$set_db( 'DB_CHARSET', 'utf8' );
$set_db( 'DB_COLLATE', '' );
}
define( __NAMESPACE__.'\\SSL_ENFORCE', false ); 
define( __NAMESPACE__.'\\DEFAULT_BACKUP_LIFESPAN', 5 ); 
define( __NAMESPACE__.'\\RESTORE_MIN_EXECUTION_TIME', 100 ); 
define( __NAMESPACE__.'\\GIT_BRANCH_TYPE', '' ); 
define( __NAMESPACE__.'\\APP_VERSION_NO', '1.0-3' ); 
define( __NAMESPACE__.'\\APP_VERSION_TYPE', 'stable build' ); 
define( __NAMESPACE__.'\\APP_VERSION_ID', '1.0-3 - stable build' ); 
define( __NAMESPACE__.'\\APP_VERSION_DATE', '2017-02-07' ); 
?>