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
 * @file    : post-config.php $
 * 
 * @id      : post-config.php | Tue Feb 7 08:55:11 2017 +0100 | eugenmihailescu <eugenmihailescux@gmail.com> $
*/

namespace MyBackup;

$PROGRESS_PROVIDER = $BACKUP_TARGETS + array_intersect_key ( $TARGET_NAMES, array_flip ( $NOT_BACKUP_TARGETS ) );
$BACKUP_TARGETS = array_diff_key ( $PROGRESS_PROVIDER, array_flip ( $NOT_BACKUP_TARGETS ) );
! isset ( $REGISTERED_BACKUP_TABS ) && $REGISTERED_BACKUP_TABS = array (); 
$REGISTERED_BACKUP_TABS = $REGISTERED_BACKUP_TABS + $BACKUP_TARGETS;
arrayKeySort ( $REGISTERED_BACKUP_TABS, array (
DISK_TARGET,
FTP_TARGET,
SSH_TARGET,
DROPBOX_TARGET,
WEBDAV_TARGET 
) );
?>