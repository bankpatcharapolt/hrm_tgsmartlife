<?php defined('BASEPATH') OR exit('No direct script access allowed');
$active_group = 'default';
$query_builder = TRUE;
$db['default'] = array(
    'dsn'=>'', 'hostname'=>'localhost', 'username'=>'root', 'password'=>'',
    'database'=>'hrm_db', 'dbdriver'=>'mysqli', 'dbprefix'=>'', 'pconnect'=>FALSE,
    'db_debug'=>(ENVIRONMENT!=='production'), 'cache_on'=>FALSE, 'cachedir'=>'',
    'char_set'=>'utf8mb4', 'dbcollat'=>'utf8mb4_unicode_ci',
    'swap_pre'=>'', 'encrypt'=>FALSE, 'compress'=>FALSE, 'stricton'=>FALSE,
    'failover'=> array(), // แก้ตรงนี้จาก [) เป็น array() หรือ [] ก็ได้ครับ
    'save_queries'=>TRUE
);