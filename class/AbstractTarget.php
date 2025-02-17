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
 * @file    : AbstractTarget.php $
 * 
 * @id      : AbstractTarget.php | Tue Feb 7 08:55:11 2017 +0100 | eugenmihailescu <eugenmihailescux@gmail.com> $
*/

namespace MyBackup;

include_once FUNCTIONS_PATH . 'utils.php';
class AbstractTarget {
private $options, $enabled, $path, $age, $size_limit, $params;
function __construct( $target_name, $options, $params = null ) {
$this->options = $options;
$this->setPath( getParam( $options, $target_name ) );
$this->age = getParam( $options, $target_name . "_age" );
$this->setSizeLimit( getParam( $options, $target_name . "_limit" ) );
$key = $target_name . '_enabled';
$this->enabled =  isset( $options[$key] )  && strToBool( $options[$key] ); 
$this->params = array();
if ( null != $params )
$this->setParams( $params );
}
function isEnabled() {
return $this->enabled;
}
function setEnabled( $enabled ) {
$this->enabled = $enabled;
}
function getPath() {
return $this->path;
}
function setPath( $path ) {
if ( '\\' == DIRECTORY_SEPARATOR ) {
$path = str_replace( DIRECTORY_SEPARATOR . DIRECTORY_SEPARATOR, DIRECTORY_SEPARATOR, $path );
}
$this->path = $path;
}
function getAge() {
return intval( $this->age );
}
function getSizeLimit() {
return $this->size_limit;
}
function setSizeLimit( $size_limit ) {
$this->size_limit = $size_limit;
}
function getParams() {
return $this->params;
}
function setParams( $params ) {
foreach ( $params as $param )
$this->params[$param] = getParam( $this->options, $param );
}
function getOption( $name ) {
return isset( $this->options[$name] ) ? $this->options[$name] : null;
}
function getOptions() {
return $this->options;
}
}
?>