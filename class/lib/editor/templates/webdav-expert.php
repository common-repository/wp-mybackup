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
 * @file    : webdav-expert.php $
 * 
 * @id      : webdav-expert.php | Tue Feb 7 08:55:11 2017 +0100 | eugenmihailescu <eugenmihailescux@gmail.com> $
*/

namespace MyBackup;
?>
<tr>
<td><label for="webdav_authtype">HTTP auth type</label></td>
<td><select name="webdav_authtype" id="webdav_authtype"
<?php echo $this->enabled_tag;?>>
<?php echo $auth_options;?>
</select><a class='help'
onclick=<?php
echoHelp ( $help_4 );
?>> [?]</a></td>
</tr>
<tr>
<td><label for="webdav_cainfo">CA PEM path/file</label></td>
<td><input type="text" name="webdav_cainfo" id="webdav_cainfo"
style="width: 300px"
value="<?php echo $this->settings['webdav_cainfo'];?>"
<?php echo $this->enabled_tag;?>></td>
<td><a class='help' onclick=<?php
echoHelp ( $help_3 );
?>> [?]</a></td>
</tr>
<?php if(defined(__NAMESPACE__.'\\BANDWIDTH_THROTTLING')){?>
<tr>
<td><label for="webdav_throttle">Upload throttling</label></td>
<td><input type="number" name="webdav_throttle" id="webdav_throttle"
value="<?php echo $this->settings['webdav_throttle'];?>"
<?php echo $this->enabled_tag;?>> KiBps<a class='help'
onclick=<?php echo echoHelp($help_1);?>> [?]</a></td>
</tr>
<?php
}
if (defined ( __NAMESPACE__.'\\FILE_EXPLORER' )) {
?>
<tr>
<td><label for="webdav_direct_dwl">Direct download</label></td>
<td><input type="checkbox" name="webdav_direct_dwl"
id="webdav_direct_dwl" value='1'
<?php echo (isNull($this->settings,'webdav_direct_dwl',false)?'checked':'').' '.$this->enabled_tag;?>>
<input type='hidden' name='webdav_direct_dwl' value='0'><a
class='help' onclick=<?php echo echoHelp($help_2);?>> [?]</a><?php echo getSSLIcon();?></td>
</tr>
<?php }?>