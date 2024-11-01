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
 * @file    : restore_mysql.php $
 * 
 * @id      : restore_mysql.php | Tue Feb 7 08:55:11 2017 +0100 | eugenmihailescu <eugenmihailescux@gmail.com> $
*/

namespace MyBackup;

if (is_array($files)) {
$opts = $this->getOptions();
$cpusleep = $this->getCPUSleep();
$restore_acid = strToBool($opts['restore_acid']);
$restore_mybackup = strToBool($opts['restore_mybackup']);
$restore_debug = strToBool($opts['restore_debug_on']);
$aborted = false;
$restore_log = new LogFile(RESTORE_DEBUG_LOG, $opts);
$obj = new MySQLWrapper($opts);
$link = $obj->connect();
$i = 0;
$mysql_progress_id = 'multiple-mysql-files';
$this->onProgress($source_type, $mysql_progress_id, $i, count($files), 7, - 1);
if ($link) {
foreach ($files as $mysql_filename) {
if ($aborted = $this->_is_job_aborted($aborted, $source_type)) {
$error = true;
break;
}
if (! _file_exists($mysql_filename)) {
$this->outputError(sprintf(_esc('Cannot execute the SQL statements from %s. File does not exist'), $mysql_filename));
continue;
}
$start_time = time();
$eol = ";" . PHP_EOL;
$max_prg = getFileLinesCount($mysql_filename, $eol);
if ($restore_debug) {
$restore_log->writeSeparator();
$restore_log->writelnLog(' ' . sprintf(_esc('Restoring %s @ %s:%s/%s [jobId: %s]'), basename($mysql_filename), $opts['mysql_host'], $opts['mysql_port'], $opts['mysql_db'], $this->getCurrentJobId()));
$restore_log->writelnLog(' ' . sprintf(_esc('Lines count: %d'), getFileLinesCount($mysql_filename)));
$restore_log->writelnLog(' ' . sprintf(_esc('SQL stmts: %d'), $max_prg));
$restore_log->writelnLog(' ' . sprintf(_esc('Start time: %s'), date('Y-M-d H:i:s', $start_time)));
$restore_log->writelnLog(' ' . sprintf(_esc('ACID: %s'), boolToStr($restore_acid, true)));
$restore_log->writelnLog(' ' . sprintf(_esc('Line syntax: %s'), 'status > SQL @lineno <:error>'));
$restore_log->writelnLog(' ' . sprintf(_esc('             status = { OK : %s, !!! : %s, *** : %s }'), _esc('success'), _esc('warning'), _esc('error')));
$restore_log->writeSeparator();
}
$sql_verbose = $this->getVerbosity(VERBOSE_FULL) && _file_exists($mysql_filename) && (filesize($mysql_filename) < 5 * MB);
$pos_prg = 0;
$this->onProgress($source_type, $mysql_filename, 0, $max_prg, 7);
$this->logOutputTimestamp(sprintf(_esc('running %d SQL statements from %s @ %s:%s/%s'), $max_prg, $mysql_filename, $opts['mysql_host'], $opts['mysql_port'], $opts['mysql_db']), BULLET);
$fr = fopen($mysql_filename, 'r');
$sql = '';
if (false !== $fr) {
$ok_sql = 0;
$ko_sql = 0;
$skip_sql = 0;
$sql_transaction = false;
if ($restore_acid) {
$str_prefix = _esc('MySQL ACID mode in effect') . '; ';
if ($sql_transaction = $obj->begin_transaction())
$this->logOutputTimestamp($str_prefix . _esc('started a new SQL transaction'), BULLET);
else {
$last_error = $obj->get_last_error();
$this->outputError($str_prefix . sprintf(_esc('could not start a SQL transaction (%s)'), $last_error['message']));
}
}
$lineno = 1; 
$stmt_lines = 0; 
while (($buff = fgets($fr)) !== false && ! ($error && $sql_transaction)) {
if ($aborted = $this->_is_job_aborted($aborted, $source_type)) {
$error = true;
break;
}
$lineno ++; 
if (strlen($buff) == substr_count($buff, PHP_EOL) * strlen(PHP_EOL))
continue;
$stmt_lines ++; 
$sql .= $buff;
if (';' == substr(str_replace(PHP_EOL, '', $buff), - 1)) {
if ($restore_mybackup || false === strpos($sql, TBL_PREFIX)) {
$sql_verbose && $this->logOutputTimestamp($sql, BULLET, 2);
$debug_sql_cmd = preg_replace('/^([A-Z]+ [A-Z]+) .*/', '$1', str_replace(PHP_EOL, ' ', $sql));
if (false === ($res = $obj->query($sql))) {
$ko_sql ++;
$error = true;
$mysql_error = $obj->get_last_error();
$this->outputError(sprintf('<red>[!] @ sql[%d]: %s (%d)</red>', $ok_sql + $ko_sql + $skip_sql, $mysql_error['message'], $mysql_error['code']));
if ($restore_debug)
$restore_log->writelnLog(sprintf('*** > %s @%d : %s (%d)', $debug_sql_cmd, $lineno - $stmt_lines, $mysql_error['message'], $mysql_error['code']));
if (in_array($mysql_error['code'], array(
1153,
2006,
2013
))) {
fseek($fr, 0, SEEK_END); 
break;
}
} else {
$ok_sql ++;
if ($restore_debug)
$restore_log->writelnLog(sprintf(' OK > %s @%d', $debug_sql_cmd, $lineno - $stmt_lines));
is_bool($res) || $obj->free_result($res);
}
$cpusleep > 0 && _usleep(1000 * $cpusleep);
} else {
$skip_sql ++;
if ($restore_debug)
$restore_log->writelnLog(sprintf('!!! > %s @%d : %s', $debug_sql_cmd, $lineno - $stmt_lines, sprintf(_esc('skipping %s table'), TBL_PREFIX)));
}
$sql = '';
$stmt_lines = 0;
}
$this->onProgress($source_type, $mysql_filename, $ok_sql + $ko_sql + $skip_sql, $max_prg, 7);
}
$this->onProgress($source_type, $mysql_filename, $max_prg, $max_prg, 7);
fclose($fr);
$sql_action = _esc('executed');
if ($sql_transaction) {
$str_suffix = ' ' . _esc('(that is strange!)');
if (! ($ko_sql || $error)) {
if ($obj->commit_transaction()) {
$this->logOutputTimestamp(_esc('transaction commited successfully'), BULLET);
$sql_action = _esc('commited');
} else {
$this->outputError(sprintf('<yellow>[!] %s</yellow>', _esc('transaction commit error') . $str_suffix));
$sql_action = _esc('not commited');
}
} else {
$error = true;
if ($obj->rollback_transaction()) {
$this->outputError(sprintf('<yellow>[!] %s; %s</yellow>', sprintf(_esc('%d error(s) found'), $ko_sql), _esc('transaction rolled back')));
$sql_action = _esc('rolled back');
} else {
$this->outputError(sprintf('<yellow>[!] %s</yellow>', _esc('transaction rollback error' . $str_suffix)));
$sql_action = _esc('not rolled back');
}
}
}
}
unlink($mysql_filename);
$file_restore_status = sprintf(_esc('%d SQL statements %s successfuly%s'), $ok_sql, $sql_action, $ko_sql + $skip_sql > 0 ? ' (' . implode(', ', array(
sprintf('%d failed', $ko_sql),
sprintf('%d skipped', $skip_sql)
)) . ')' : '');
$this->logOutputTimestamp('<yellow><b>' . $file_restore_status . '</b></yellow>', BULLET, 2);
$skip_sql && ! $restore_mybackup && $this->logOutput('<yellow>' . sprintf('<b>' . _esc('NOTE') . '</b> : ' . _esc('The restoration of %s tables was skipped.'), WPMYBACKUP) . '</yellow>');
$i ++;
$this->onProgress($source_type, $mysql_progress_id, $i, count($files), 7, - 1);
if ($restore_debug) {
$restore_log->writeSeparator();
$restore_log->writelnLog(' ' . sprintf(_esc('Total executed SQL statements: %d'), $ok_sql + $skip_sql + $ko_sql));
$restore_log->writelnLog(' ' . $file_restore_status);
$restore_log->writelnLog(' ' . sprintf(_esc('Elapsed time: %s'), getHumanReadableTime(time() - $start_time)));
$restore_log->writeSeparator();
}
}
}
$obj->disconnect();
$obj = null;
} else
$this->logOutputTimestamp(_esc('unexpected error'), BULLET, 2);
?>