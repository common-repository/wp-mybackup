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
 * @file    : WebDAVParser.php $
 * 
 * @id      : WebDAVParser.php | Tue Feb 7 08:55:11 2017 +0100 | eugenmihailescu <eugenmihailescux@gmail.com> $
*/

namespace MyBackup;
class WebDAVParser extends Xml2Array {
private function _getPropKey($prop_array) {
if (count ( $prop_array ) > 0 && false !== reset ( $prop_array ) && preg_match ( '/([^:]+):/', key ( $prop_array ), $matches ))
return $matches [1];
return false;
}
private function _setValue($key, &$array, &$value) {
if (! is_array ( $array ))
throw new MyException ( __FUNCTION__ . ' expects 2nd parameter to be array, string given: "' . $array . '"' );
if (isset ( $array [$key] ) && is_array ( $array [$key] ) && isset ( $array [$key] [WEBDAV_TEXT_KEY] ))
$value = $array [$key] [WEBDAV_TEXT_KEY];
}
public function parse() {
$webdav_responses = array ();
$responses = $this->getValueByPath ( 'multistatus response' );
if (! ($single_response = ! isset ( $responses [0] )))
$response = current ( $responses );
else
$response = $responses;
if (false !== $response)
do {
$dav_response = new WebDAVResponse ();
$href = $this->getValueByPath ( 'href', $response );
$name = $href [WEBDAV_TEXT_KEY];
$dav_response->href = $name;
$dav_resource = new WebDAVResource ();
$propstats = $this->getValueByPath ( 'propstat', $response );
$namespace = $this->getNamespace ();
foreach ( $propstats as $d_prop_key => $d_prop )
if (false !== $d_prop) {
$pk = $this->_getPropKey ( $d_prop );
$this->_setValue ( "$pk:creationdate", $d_prop, $dav_resource->creation_date );
$this->_setValue ( "$pk:getlastmodified", $d_prop, $dav_resource->modified_date );
$this->_setValue ( "$pk:getetag", $d_prop, $dav_resource->tag );
$this->_setValue ( "$pk:getcontentlength", $d_prop, $dav_resource->content_length );
$this->_setValue ( "$pk:executable", $d_prop, $dav_resource->executable );
if (isset ( $d_prop ["$namespace:supportedlock"] ) && is_array ( $d_prop ["$namespace:supportedlock"] ) && isset ( $d_prop ["$namespace:supportedlock"] ["$namespace:lockentry"] )) {
foreach ( $d_prop ["$namespace:supportedlock"] ["$namespace:lockentry"] as $d_lock_entry ) {
$dav_lock = new WebDavLock ();
if (isset ( $d_lock_entry ["$namespace:lockscope"] ))
$dav_lock->scope = current ( array_keys ( $d_lock_entry ["$namespace:lockscope"] ) );
if (isset ( $d_lock_entry ["$namespace:locktype"] ))
$dav_lock->type = current ( array_keys ( $d_lock_entry ["$namespace:locktype"] ) );
$dav_resource->supported_locks [] = $dav_lock;
}
}
$this->_setValue ( "$namespace:getcontenttype", $d_prop, $dav_resource->content_type );
if ("$namespace:status" == $d_prop_key && isset ( $d_prop [WEBDAV_TEXT_KEY] ))
$dav_resource->status = $d_prop [WEBDAV_TEXT_KEY];
elseif ("$namespace:prop" == $d_prop_key)
$dav_response->props = $dav_resource;
}
$webdav_responses [] = $dav_response;
if (! $single_response)
$response = next ( $responses );
} while ( false === $single_response && false !== $response );
return $webdav_responses;
}
}
?>