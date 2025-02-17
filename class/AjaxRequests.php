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
 * @file    : AjaxRequests.php $
 * 
 * @id      : AjaxRequests.php | Tue Feb 7 08:55:11 2017 +0100 | eugenmihailescu <eugenmihailescux@gmail.com> $
*/

namespace MyBackup;

include_once FUNCTIONS_PATH . 'utils.php';
include_once CLASS_PATH . 'ActionHandler.php';
class AjaxRequests
{
private $_action_handler;
private $_settings;
function __construct($settings)
{
$this->_settings = $settings;
$this->_action_handler = new ActionHandler($settings);
! (empty($_POST) || empty($_POST['action'])) && $this->verify_nonce($_POST['action']);
}
function verify_nonce($action = null)
{
if (isset($_POST['nonce']))
$nonce = $_POST['nonce'];
else {
$nonce = null;
}
$err_msg = sprintf('Security check error #%d (' . $action . '):' . $nonce, 'test_dwl' == $action ? 1 : 2);
if (! empty($action))
if (! empty($nonce)) {
if (! wp_verify_nonce_wrapper($nonce, $action)) {
$time = substr($nonce, - 10);
if (time() - intval($time) > (@constant('WPMYBAK_NONCE_LIFESPAN') ? @constant('WPMYBAK_NONCE_LIFESPAN') : 3600))
$err_msg .= '<br>Please try again by <a href="#" onclick="window.location.assign(window.location.href);">re-visiting again the page</a>.';
die($err_msg);
}
} else {
die($err_msg . "<br>This should not happen. Please send a bug report (ref code : 1).");
}
else {
throw new MyException('Action should not be empty');
}
if (defined(__NAMESPACE__.'\\ADDONFUNC_PATH') && 'reset_defaults' != $action) {
$callback = 'after_nonce';
_file_exists(ADDONFUNC_PATH . $callback . '.php') && $this->_action_handler->anonymousExec($callback);
}
}
function wpmybackup_ajax()
{
isset($_REQUEST) && isset($_REQUEST['action']) && ! empty($_REQUEST['action']) && ($action = $_REQUEST['action']) || die(_esc('I expect a valid action parameter'));
$function = array(
$this->_action_handler,
$action
);
if (_is_callable($function)) {
_call_user_func($function);
} else {
$this->_action_handler->anonymousExec($action);
}
is_wp() && die();
}
function wpmybackup_do_action()
{
$action_found = false;
if (isset($_POST['action'])) {
$action_found = true;
switch ($_POST['action']) {
case 'submit_options':
$this->_action_handler->submit_options();
break;
case 'dwl_sql_script':
$this->_action_handler->dwl_sql_script();
break;
case 'clear_log':
$this->_action_handler->clear_log($_POST['log_type'], isset($_POST['log']) ? $_POST['log'] : null);
break;
case 'dwl_file':
$this->_action_handler->dwl_file();
break;
case 'del_lic':
$this->_action_handler->anonymousExec('del_lic');
break;
case 'reset_defaults':
$this->_action_handler->reset_defaults();
break;
case 'edit_step':
$this->_action_handler->edit_step();
break;
case 'del_target':
$this->_action_handler->del_target();
break;
case 'set_branched_log':
$this->_action_handler->set_branched_log();
break;
case 'del_branched_log':
$this->_action_handler->del_branched_log();
break;
default:
$action_found = false;
break;
}
}
return $action_found;
}
}
?>