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
 * @file    : ChangeLogEditor.php $
 * 
 * @id      : ChangeLogEditor.php | Tue Feb 7 08:55:11 2017 +0100 | eugenmihailescu <eugenmihailescux@gmail.com> $
*/

namespace MyBackup;
class ChangeLogEditor extends AbstractTargetEditor {
protected function initTarget() {
parent::initTarget ();
$this->hasCustomFrame = true;
}
protected function getEditorTemplate() {
$yayui = new YayuiCompressor ();
$options = array (
PRESERVE_STRINGS => false, 
SINGLE_COMMENT => true,
BLOCK_COMMENT => true,
LINE_SEPARATOR => true,
WHITESPACE => true,
MINIFY => true 
);
echo '<style>';
echo $yayui->streamCompress ( file_get_contents ( $this->getTemplatePath ( 'changelog.css' ) ), $options, 'CSS', false );
echo '</style>';
echo $yayui->htmlCompress ( file_get_contents ( $this->getTemplatePath ( 'changelog.html', ROOT_PATH ) ), $options );
}
}
?>