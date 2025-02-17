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
 * @file    : autoloader.php $
 * 
 * @id      : autoloader.php | Tue Feb 7 08:55:11 2017 +0100 | eugenmihailescu <eugenmihailescux@gmail.com> $
*/


namespace MyBackup;
global $classes_path_668264596;
$classes_path_668264596 = array (
'AbstractOAuthClient' => OAUTH_PATH . 'AbstractOAuthClient.php',
'AbstractTarget' => CLASS_PATH . 'AbstractTarget.php',
'AbstractTargetEditor' => EDITOR_PATH . 'AbstractTargetEditor.php',
'Array2XML' => MISC_PATH . 'Array2XML.php',
'BackupFilesFilter' => CLASS_PATH . 'BackupFilesFilter.php',
'BackupSettingsEditor' => EDITOR_PATH . 'BackupSettingsEditor.php',
'ChangeLogEditor' => EDITOR_PATH . 'ChangeLogEditor.php',
'CurlFtpWrapper' => CURL_PATH . 'CurlFtpWrapper.php',
'CurlOptsCodes' => CURL_PATH . 'CurlOptsCodes.php',
'CurlOptsParamsCodes' => CURL_PATH . 'CurlOptsParamsCodes.php',
'CurlSSHWrapper' => CURL_PATH . 'CurlSSHWrapper.php',
'DashboardEditor' => EDITOR_PATH . 'DashboardEditor.php',
'DiskSourceEditor' => EDITOR_PATH . 'DiskSourceEditor.php',
'DiskTargetEditor' => EDITOR_PATH . 'DiskTargetEditor.php',
'DropboxCloudStorage' => STORAGE_PATH . 'DropboxCloudStorage.php',
'DropboxOAuth2Client' => OAUTH_PATH . 'DropboxOAuth2Client.php',
'DropboxTargetEditor' => EDITOR_PATH . 'DropboxTargetEditor.php',
'FacebookOAuth2Client' => OAUTH_PATH . 'FacebookOAuth2Client.php',
'FtpStatusCodes' => CURL_PATH . 'FtpStatusCodes.php',
'FtpTargetEditor' => EDITOR_PATH . 'FtpTargetEditor.php',
'GenericArchive' => CLASS_PATH . 'GenericArchive.php',
'GenericCloudStorage' => STORAGE_PATH . 'GenericCloudStorage.php',
'GenericDataManager' => MISC_PATH . 'GenericDataManager.php',
'GenericOAuth2Client' => OAUTH_PATH . 'GenericOAuth2Client.php',
'GoogleCloudStorage' => STORAGE_PATH . 'GoogleCloudStorage.php',
'GoogleOAuth2Client' => OAUTH_PATH . 'GoogleOAuth2Client.php',
'GoogleTargetEditor' => EDITOR_PATH . 'GoogleTargetEditor.php',
'HtmlTableConverter' => MISC_PATH . 'HtmlTableConverter.php',
'HttpStatusCodes' => CURL_PATH . 'HttpStatusCodes.php',
'LocalFilesMD5' => CLASS_PATH . 'LocalFilesMD5.php',
'MailTargetEditor' => EDITOR_PATH . 'MailTargetEditor.php',
'MessageHandler' => MISC_PATH . 'MessageHandler.php',
'MessageItem' => MISC_PATH . 'MessageItem.php',
'MyChunkUploader' => MISC_PATH . 'MyChunkUploader.php',
'MyFtpWrapper' => CURL_PATH . 'MyFtpWrapper.php',
'MyPclZip' => CLASS_PATH . 'MyPclZipArchive.php',
'MyPclZipArchive' => CLASS_PATH . 'MyPclZipArchive.php',
'MySQLBackupHandler' => CLASS_PATH . 'MySQLBackupHandler.php',
'MySQLErrorException' => MISC_PATH . 'MySQLWrapper.php',
'MySQLException' => MISC_PATH . 'MySQLWrapper.php',
'MySQLSourceEditor' => EDITOR_PATH . 'MySQLSourceEditor.php',
'MySQLWrapper' => MISC_PATH . 'MySQLWrapper.php',
'MyUploadException' => MISC_PATH . 'MyChunkUploader.php',
'NonceLib' => MISC_PATH . 'NonceLib.php',
'NotificationEditor' => EDITOR_PATH . 'NotificationEditor.php',
'OAuthTargetEditor' => EDITOR_PATH . 'OAuthTargetEditor.php',
'ProgressManager' => MISC_PATH . 'ProgressManager.php',
'RegExBuilder' => MISC_PATH . 'RegExBuilder.php',
'SSHTargetEditor' => EDITOR_PATH . 'SSHTargetEditor.php',
'ScheduleEditor' => EDITOR_PATH . 'ScheduleEditor.php',
'SupportCategories' => STORAGE_PATH . 'SupportCategories.php',
'TargetCollection' => EDITOR_PATH . 'TargetCollection.php',
'TargetCollectionItem' => EDITOR_PATH . 'TargetCollectionItem.php',
'WPScheduleEditor' => EDITOR_PATH . 'WPScheduleEditor.php',
'WPSourceEditor' => EDITOR_PATH . 'WPSourceEditor.php',
'WP_MyBackup_Restore' => ADDONFUNC_PATH . 'wp_restore.php',
'WP_MyBackup_Upgrader' => ADDONFUNC_PATH . 'wp_restore.php',
'WebDAVParser' => STORAGE_PATH . 'WebDAVParser.php',
'WebDAVResource' => STORAGE_PATH . 'WebDAVResource.php',
'WebDAVResponse' => STORAGE_PATH . 'WebDAVResponse.php',
'WebDAVTargetEditor' => EDITOR_PATH . 'WebDAVTargetEditor.php',
'WebDAVWebStorage' => STORAGE_PATH . 'WebDAVWebStorage.php',
'WebDavLock' => STORAGE_PATH . 'WebDavLock.php',
'WelcomeEditor' => EDITOR_PATH . 'WelcomeEditor.php',
'Xml2Array' => MISC_PATH . 'Xml2Array.php',
'YayuiCompressor' => MISC_PATH . 'YayuiCompressor.php'
);
spl_autoload_register ( function ($class_name) {
global $classes_path_668264596;
$class_name = preg_replace ( "/" . __NAMESPACE__ . "\\\\/", "", $class_name );
isset ( $classes_path_668264596 [$class_name] ) && include_once $classes_path_668264596 [$class_name];});
?>