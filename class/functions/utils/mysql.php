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
 * @file    : mysql.php $
 * 
 * @id      : mysql.php | Tue Feb 7 08:55:11 2017 +0100 | eugenmihailescu <eugenmihailescux@gmail.com> $
*/

namespace MyBackup;

function getMySQLTableNamesWhereByPattern( $pattern = '.+' ) {
if ( false !== strpos( $pattern, '|' ) && false === strpos( $pattern, ',' ) )
$where = explode( '|', $pattern );
elseif ( false === strpos( $pattern, '|' ) && false !== strpos( $pattern, ',' ) )
$where = explode( ',', $pattern );
else {
$where = array();
foreach ( explode( ',', $pattern ) as $item )
foreach ( explode( '|', $item ) as $tbl )
$where[] = $tbl;
}
array_walk( $where, function ( &$item ) {
$item = sprintf( "table_name REGEXP '^%s$'", $item );
} );
$where = empty( $where ) ? '' : ( '(' . implode( ' OR ', $where ) . ')' );
$wp_db_prefix = is_wp() ? wp_get_db_prefix() : '';
empty( $wp_db_prefix ) ||
$where = sprintf( "table_name like '%s%%' AND %s", wp_get_db_prefix(), empty( $where ) ? 'true' : $where );
return $where;
}
function getMySQLTableNamesFromPattern( $pattern = '.+', $mysql_obj = null, $settings = null, $extended = false, $order_by_name = false ) {
if ( ! $mysql_obj ) {
return false;
}
$db_name = null;
if ( $rst = $mysql_obj->query( 'select DATABASE()' ) ) {
if ( $row = $mysql_obj->fetch_row( $rst ) )
$db_name = $row[0];
else
$db_name = $mysql_obj->get_param( 'mysql_db' );
$mysql_obj->free_result( $rst );
}
$sql = sprintf( 
"SELECT * FROM (SELECT table_name,table_rows,(data_length+index_length) as table_size FROM information_schema.tables WHERE table_schema='%s' AND %s) A ORDER BY A.", 
$db_name, 
getMySQLTableNamesWhereByPattern( $pattern ) );
$sql .= $order_by_name ? 'table_name' : 'table_size DESC';
if ( $rst = $mysql_obj->query( $sql ) ) {
$tables = array();
while ( $row = $mysql_obj->fetch_row( $rst ) ) {
( $extended && $tables[$row[0]] = array( $row[1], $row[2] ) ) || $tables[] = $row[0];
}
$mysql_obj->free_result( $rst );
} else
$tables = false;
return $tables;
}
?>