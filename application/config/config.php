<?php defined('BASEPATH') OR exit('No direct script access allowed');

$config['base_url'] = 'http://localhost/hrm_tgsmartlife/';
$config['index_page'] = '';           // ว่างเปล่า - ต้องใช้กับ mod_rewrite
$config['uri_protocol'] = 'REQUEST_URI';
$config['url_suffix'] = '';
$config['language'] = 'english';
$config['charset'] = 'UTF-8';
$config['enable_hooks'] = FALSE;
$config['subclass_prefix'] = 'MY_';
$config['composer_autoload'] = FALSE;
$config['permitted_uri_chars'] = 'a-z 0-9~%.:_\-';
$config['allow_get_array'] = TRUE;
$config['enable_query_strings'] = FALSE;
$config['log_threshold'] = 0;
$config['log_path'] = '';
$config['log_file_extension'] = '';
$config['log_file_permissions'] = 0644;
$config['log_date_format'] = 'Y-m-d H:i:s';
$config['error_views_path'] = '';
$config['cache_path'] = '';
$config['encryption_key'] = 'HRM2025ChangeThisKey!RandomStr32';

$config['sess_driver']          = 'files';
$config['sess_cookie_name']     = 'hrm_sess';
$config['sess_expiration']      = 28800;
$config['sess_save_path']       = NULL;
$config['sess_match_ip']        = FALSE;
$config['sess_time_to_update']  = 300;
$config['sess_regenerate_destroy'] = FALSE;

$config['cookie_prefix']    = 'hrm_';
$config['cookie_domain']    = '';
$config['cookie_path']      = '/';
$config['cookie_secure']    = FALSE;
$config['cookie_httponly']  = FALSE;

// จากเดิมที่เป็น TRUE ให้แก้เป็น FALSE ครับ
$config['csrf_protection'] = FALSE; 

$config['csrf_token_name']  = 'csrf_token';
$config['csrf_cookie_name'] = 'csrf_cookie';
$config['csrf_expire']      = 7200;
$config['csrf_regenerate']  = TRUE;
$config['csrf_exclude_uris'] = array('api/.*');

$config['compress_output']   = FALSE;
$config['time_reference']    = 'local';
$config['rewrite_short_tags'] = FALSE;
$config['reverse_proxy_ips'] = '';

// ── วันเริ่มต้นนับการขาดงาน ──────────────────────────────────────────────────
// ระบบจะไม่นับวันก่อนหน้าวันนี้เป็น "ขาดงาน" เพื่อป้องกันปัญหาตอน deploy ใหม่
// รูปแบบ: 'YYYY-MM-DD' หรือ '' (ว่าง = นับทุกวันตั้งแต่ต้นเดือน)
// ตัวอย่าง: deploy วันที่ 1 ส.ค. 2026 → ตั้งเป็น '2026-08-01'
$config['attendance_track_start'] = '2026-06-01';
