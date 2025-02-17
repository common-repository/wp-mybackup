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
 * @file    : backup2mail.php $
 * 
 * @id      : backup2mail.php | Tue Feb 7 08:55:11 2017 +0100 | eugenmihailescu <eugenmihailescux@gmail.com> $
*/

namespace MyBackup;
?>
<tr>
<td><label for="backup2mail"><?php _pesc('Backup to e-mail');?></label></td>
<td><input type="checkbox" name="backup2mail" id="backup2mail" value="1"
<?php
if ( strToBool( $this->settings['backup2mail'] ) )
echo ' checked';
?>><a class='help' onclick=<?php
echoHelp( $help_1 );
?>>[?]</a><input type="hidden" name="backup2mail" value="0"></td>
</tr>
<tr>
<td><label for="backup2mail_address"><?php _pesc('Alternative e-mail');?></label></td>
<td><input type="email" name="backup2mail_address" id="backup2mail_address"
size=30 value="<?php echo $this->settings ['backup2mail_address'];?>"><a
class='help' onclick=<?php
echoHelp( $help_2 );
?>>[?]</a></td>
</tr>
<tr>
<td><label for="backup2mail_maxsize"><?php _pesc('Attachment size');?></label></td>
<td><input type="number" name="backup2mail_maxsize" id="backup2mail_maxsize"
value="<?php echo $this->settings ['backup2mail_maxsize'];?>"
min="<?php echo MB;?>"> <?php echo _esc('bytes');?><a class='help'
onclick=<?php
echoHelp( $help_3 );
?>> [?]</a></td>
</tr>