<?php defined('BASEPATH') OR exit('No direct script access allowed');
$route['default_controller'] = 'auth/login';
$route['404_override'] = 'errors/page_404';
$route['translate_uri_dashes'] = FALSE;
$route['auth/(:any)'] = 'auth/$1';
$route['login'] = 'auth/login';
$route['logout'] = 'auth/logout';
$route['admin'] = 'admin/dashboard/index';
$route['admin/dashboard'] = 'admin/dashboard/index';

$route['admin/employees_import/import'] = 'admin/employees_import/import';
$route['admin/employees_import/do_import'] = 'admin/employees_import/do_import';
$route['admin/employees_import/import_result'] = 'admin/employees_import/import_result';
$route['admin/employees_import/export'] = 'admin/employees_import/export';

$route['admin/employees'] = 'admin/employees/index';
$route['admin/employees/(:any)'] = 'admin/employees/$1';
$route['admin/salary/bonus'] = 'admin/salary/bonus';
$route['admin/salary/tax_docs'] = 'admin/salary/tax_docs';
$route['admin/salary/upload_tax'] = 'admin/salary/upload_tax';
$route['admin/salary/upload_slip'] = 'admin/salary/upload_slip';
$route['admin/salary/store_bonus'] = 'admin/salary/store_bonus';

$route['admin/salary/slips']             = 'admin/salary/slips';
$route['admin/salary/delete_slip/(:num)']= 'admin/salary/delete_slip/$1';
$route['admin/salary/delete_tax/(:num)'] = 'admin/salary/delete_tax/$1';

$route['admin/salary'] = 'admin/salary/index';
$route['admin/salary/(:any)'] = 'admin/salary/$1';

$route['admin/attendance/shifts'] = 'admin/attendance/shifts';
$route['admin/attendance/store_shift'] = 'admin/attendance/store_shift';
$route['admin/attendance/delete_shift/(:num)'] = 'admin/attendance/delete_shift/$1';
$route['admin/attendance/assign_shift'] = 'admin/attendance/assign_shift';
$route['admin/attendance/edit/(:num)'] = 'admin/attendance/edit/$1';
$route['admin/attendance/delete/(:num)'] = 'admin/attendance/delete/$1';
$route['admin/attendance/manual'] = 'admin/attendance/manual';

$route['admin/attendance'] = 'admin/attendance/index';
$route['admin/attendance/(:any)'] = 'admin/attendance/$1';

$route['admin/leave/create'] = 'admin/leave/create';
$route['admin/leave/store']  = 'admin/leave/store';
$route['admin/leave/edit/(:num)']   = 'admin/leave/edit/$1';
$route['admin/leave/delete/(:num)'] = 'admin/leave/delete/$1';
$route['admin/leave/approve/(:num)']= 'admin/leave/approve/$1';
$route['admin/leave/reject/(:num)'] = 'admin/leave/reject/$1';

$route['admin/leave'] = 'admin/leave/index';
$route['admin/leave/(:any)'] = 'admin/leave/$1';
$route['admin/sales'] = 'admin/sales/index';
$route['admin/sales/(:any)'] = 'admin/sales/$1';
$route['admin/notifications'] = 'admin/notifications/index';
$route['admin/notifications/(:any)'] = 'admin/notifications/$1';

$route['admin/teams'] = 'admin/teams/index';
$route['admin/teams/create'] = 'admin/teams/create';
$route['admin/teams/update/(:num)'] = 'admin/teams/update/$1';
$route['admin/teams/delete/(:num)'] = 'admin/teams/delete/$1';

$route['admin/roles'] = 'admin/roles/index';
$route['admin/roles/(:any)'] = 'admin/roles/$1';
$route['dashboard'] = 'employee/dashboard/index';
$route['employee/dashboard'] = 'employee/dashboard/index';

$route['employee/attendance/add']          = 'employee/attendance/add';
$route['employee/attendance/edit/(:num)']  = 'employee/attendance/edit/$1';
$route['employee/attendance/delete/(:num)']= 'employee/attendance/delete/$1';

$route['employee/attendance'] = 'employee/attendance/index';

$route['employee/leave/edit/(:num)']   = 'employee/leave/edit/$1';
$route['employee/leave/cancel/(:num)'] = 'employee/leave/cancel/$1';

$route['employee/leave/store'] = 'employee/leave/store';
$route['employee/leave/cancel/(:num)'] = 'employee/leave/cancel/$1';
$route['employee/leave/request'] = 'employee/leave/request';
$route['employee/leave'] = 'employee/leave/index';
$route['employee/salary'] = 'employee/salary/index';
$route['employee/profile/update'] = 'employee/profile/update';
$route['employee/profile'] = 'employee/profile/index';
$route['employee/notifications'] = 'employee/notifications/index';
$route['manager/leave'] = 'manager/leave/index';
$route['manager/leave/(:any)'] = 'manager/leave/$1';
$route['manager/attendance'] = 'manager/attendance/index';
$route['manager/attendance/approve_attendance/(:num)'] = 'manager/attendance/approve_attendance/$1';
$route['manager/attendance/reject_attendance/(:num)']  = 'manager/attendance/reject_attendance/$1';
$route['manager/attendance/approve_leave/(:num)']      = 'manager/attendance/approve_leave/$1';
$route['manager/attendance/reject_leave/(:num)']       = 'manager/attendance/reject_leave/$1';
$route['manager/attendance/(:any)'] = 'manager/attendance/$1';
$route['api/notifications/unread'] = 'api/notifications/unread';
$route['api/notifications/mark_read/(:num)'] = 'api/notifications/mark_read/$1';
$route['api/attendance/checkin'] = 'api/attendance/checkin';
$route['api/attendance/checkout'] = 'api/attendance/checkout';

// SSE notification stream
$route['api/notifications/stream'] = 'api/notifications/stream';
$route['api/notifications/mark_all_read'] = 'api/notifications/mark_all_read';

// โบนัส 3 ประเภท
$route['admin/salary/delete_bonus/(:num)'] = 'admin/salary/delete_bonus/$1';

// Employee Sales Dashboard
$route['employee/sales'] = 'employee/sales/index';

// Employee Team Sales
$route['employee/sales/team'] = 'employee/sales/team';

// จัดการประเภทการลา (admin + owner only)
$route['admin/leave_types']              = 'admin/leave_types/index';
$route['admin/leave_types/store']        = 'admin/leave_types/store';
$route['admin/leave_types/edit/(:num)']  = 'admin/leave_types/edit/$1';
$route['admin/leave_types/update/(:num)']= 'admin/leave_types/update/$1';
$route['admin/leave_types/delete/(:num)']= 'admin/leave_types/delete/$1';

// Attendance manual approval
$route['admin/attendance/approve_manual/(:num)'] = 'admin/attendance/approve_manual/$1';
$route['admin/attendance/reject_manual/(:num)']  = 'admin/attendance/reject_manual/$1';
