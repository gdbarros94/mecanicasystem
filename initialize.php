<?php
$dev_data = array('id'=>'-1','firstname'=>'Developer','lastname'=>'','username'=>'asms_user','password'=>'SENHA-USER','last_login'=>'','date_updated'=>'','date_added'=>'');
if(!defined('base_url')) define('base_url','http://localhost/asms/');
if(!defined('base_app')) define('base_app', str_replace('\\','/',__DIR__).'/' );
// if(!defined('dev_data')) define('dev_data',$dev_data);
if(!defined('DB_SERVER')) define('DB_SERVER',"localhost");
if(!defined('DB_USERNAME')) define('DB_USERNAME',"asms_user");
if(!defined('DB_PASSWORD')) define('DB_PASSWORD',"SENHA-USER");
if(!defined('DB_NAME')) define('DB_NAME',"asms_db");
?>