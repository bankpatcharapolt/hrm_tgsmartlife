<?php defined('BASEPATH') OR exit('No direct script access allowed');
class Dashboard extends Admin_Controller {
    public function __construct() {
        parent::__construct();
        $this->load->model(array('Attendance_model','Leave_model','Salary_model'));
    }
    public function index() {
        $y = date('Y');
        $m = date('n');
        $stats = array(
            'total_emp'     => $this->User_model->count_all(array('status'=>'active')),
            'present_today' => $this->db->where('date',date('Y-m-d'))->where('status','present')->count_all_results('attendance'),
            'late_today'    => $this->db->where('date',date('Y-m-d'))->where('is_late',1)->count_all_results('attendance'),
            'pending_leave' => $this->Leave_model->count_pending(),
            'total_dept'    => count($this->User_model->get_all_departments())
        );
        $this->render('admin/dashboard/index', array(
            'title'         => 'แดชบอร์ด | ระบบ HRM',
            'page_title'    => 'แดชบอร์ด',
            'stats'         => $stats,
            'salary_sum'    => $this->Salary_model->get_monthly_summary($y, $m),
            'pending_leaves'=> $this->Leave_model->get_requests(array('status'=>'pending'), 5),
            'activities'    => $this->db->order_by('created_at','DESC')->limit(8)->get('activity_logs')->result(),
            'year'          => $y,
            'month'         => $m
        ));
    }
}
