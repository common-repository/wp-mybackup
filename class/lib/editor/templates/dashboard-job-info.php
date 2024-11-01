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
 * @file    : dashboard-job-info.php $
 * 
 * @id      : dashboard-job-info.php | Tue Feb 7 08:55:11 2017 +0100 | eugenmihailescu <eugenmihailescux@gmail.com> $
*/

namespace MyBackup;

$action = 'last_job_info';
$next_schedule = wp_next_scheduled( WPCRON_SCHEDULE_HOOK_NAME );
$next_schedule = empty( $next_schedule ) ? _esc( 'undefined' ) : date( DATETIME_FORMAT, $next_schedule );
$hook_js = '';
if ( isset( $hook_page ) ) {
ob_start();
?>
var re=/(.*href=)(['"])(.*\/)([^\/]+?)(\?.*?)(\2.*)/, subst="$1$2$3<?php echo $hook_page;?>$5<?php echo empty($hook_args)?'':$hook_args;?>$6", i, keys=['operation','source_type'];
for(var job_type in json){
if(json.hasOwnProperty(job_type)){
for(i=0;keys.length>i;i+=1)
if(json[job_type].hasOwnProperty(keys[i])){
for(var k in json[job_type][keys[i]]){
if(json[job_type][keys[i]].hasOwnProperty(k)){
json[job_type][keys[i]][k]=unescape(json[job_type][keys[i]][k]).replace(re,subst);
}
}
}
}
}
<?php
$hook_js = ob_get_clean() . PHP_EOL;
}
ob_start();
?>
<tr>
<td colspan="3" style="vertical-align: top;">
<table class="<?echo $container_shape;?>">
<tr>
<td><label><?php _pesc('Date');?></label></td>
<td id="job_info_start"></td>
</tr>
<tr>
<td><label><?php _pesc('Status');?></label></td>
<td><span id="job_info_status" style="padding: 2px; padding-left:5px;padding-right:5px;"></span></td>
</tr>
<tr>
<td><label><?php _pesc('State');?></label></td>
<td><span id="job_info_state" style="padding: 2px; padding-left:5px;padding-right:5px;"></span></td>
</tr>
<tr>
<td><label><?php _pesc('Backup mode');?></label></td>
<td id="job_info_mode"></td>
</tr>
<tr>
<td><label><?php _pesc('Source size');?></label></td>
<td id="job_info_size"></td>
</tr>
<tr>
<td><label><?php _pesc('Includes');?></label></td>
<td id="job_info_source"></td>
</tr>
<tr>
<td><label><?php _pesc('Copied to');?></label></td>
<td id="job_info_location"></td>
</tr>
<tr>
<td><label><?php _pesc('Next schedule');?></label></td>
<td><?php echo $next_schedule;?></td>
</tr>
</table>
</td>
</tr>
<?php
$last_job_html = ob_get_clean();
ob_start();
?>
parent.job_info={};
parent.last_job={};
parent.update_element=function(sufix,param,index,fgindex,bgindex,is_array,prefix,key){
var array = function(obj) {
var i,result=[];
for(i in obj)
if(obj.hasOwnProperty(i))result.push(obj[i]);
return result;
};
index=null!==parent.isNull(index,null)?index:-1;
fgindex=null!==parent.isNull(fgindex,null)?fgindex:-1;
bgindex=null!==parent.isNull(bgindex,null)?bgindex:-1;
is_array=null!==parent.isNull(is_array,null)?is_array:false;
key=null!==parent.isNull(key,null)?key:null;
var e=document.getElementById(prefix+'_'+sufix),data='?',fgcolor='',bgcolor='';
if(e){
var obj=null===key?parent[prefix]:parent[prefix][key];
if(obj.hasOwnProperty(param)){
data=obj[param];
if(fgindex>-1){
fgcolor=data.hasOwnProperty(fgindex)?data[fgindex]:'';
}
if(bgindex>-1){
bgcolor=data.hasOwnProperty(bgindex)?data[bgindex]:'';
}
if(index>-1){
data=data.hasOwnProperty(index)?data[index]:data;
}
}
if(''!=fgcolor){
e.style.color=fgcolor;
}
if(''!=bgcolor){
e.style.backgroundColor=bgcolor;
}
e.innerHTML=is_array?array(data).join(', '):data;
}
};	
parent.update_job_element=function(sufix,param,index,fgindex,bgindex,is_array){
parent.update_element(sufix,param,index,fgindex,bgindex,is_array,'job_info',<?php echo JOB_BACKUP;?>);
};
parent.update_job_info=function(xmlhttp){
try{
var json=JSON.parse(xmlhttp.responseText);
<?php echo $hook_js;?>
parent.job_info=json;
}catch(e){
parent.job_info={<?php echo JOB_BACKUP;?>:{title:e.message}};
console.log(xmlhttp);
}
parent.update_job_element('title','title');
parent.update_job_element('start','started_time');
parent.update_job_element('status','job_status',0,1,3);
parent.update_job_element('state','job_state',0,1,3);
parent.update_job_element('mode','mode');
parent.update_job_element('size','jobsize');
parent.update_job_element('source','source_type',null,null,null,true);
parent.update_job_element('location','operation',null,null,null,true);
parent.last_bak_job_id=parent.job_info[<?php echo JOB_BACKUP?>].hasOwnProperty('id')?parent.job_info[<?php echo JOB_BACKUP?>].id:0;
parent.last_rst_job_id=parent.job_info[-4].hasOwnProperty('id')?parent.job_info[-4].id:0;
parent.last_job=parent.job_info;
if(e=document.getElementById('btn_view_log')){
if(parent.last_bak_job_id)
e.value=e.value.replace(/(\s#\d+)/,'')+' #'+parent.last_bak_job_id;
e.disabled=!parent.last_bak_job_id;
}
if(e=document.getElementById('btn_restore_backup'))
{
if(parent.last_bak_job_id)
e.value=e.value.replace(/(\s#\d+)/,'')+' #'+parent.last_bak_job_id;
e.disabled=!parent.last_bak_job_id;			
}
if(2!=parent.last_job[<?php echo JOB_BACKUP;?>].job_status[2]||2==parent.last_job[<?php echo JOB_BACKUP;?>].job_state[2]){
var btn_restore_backup=document.getElementById('btn_restore_backup');
btn_restore_backup.setAttribute('class',btn_restore_backup.getAttribute('class')+' button-red');
}
};
parent.get_last_jobinfo=function(nocache){
nocache=parent.isNull(nocache,false);
parent.asyncGetContent(parent.ajaxurl,'action=<?php echo $action;?>&nonce=<?php echo wp_create_nonce_wrapper( $action );?>&url='+encodeURIComponent(window.location),parent.dummy,parent.update_job_info);
nocache && parent.get_wp_jobs_stats(nocache);
};
parent.get_last_jobinfo();
<?php
$last_job_js = ob_get_clean();
?>