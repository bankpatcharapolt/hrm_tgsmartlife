<?php defined('BASEPATH') OR exit('No direct script access allowed');
class Dashboard extends Employee_Controller {
    public function __construct() { parent::__construct(); $this->load->model(['Attendance_model','Leave_model','Salary_model']); }
    public function index() {
        $uid=$this->current_user->user_id;
        $this->render('employee/dashboard/index',['title'=>'หน้าหลัก','page_title'=>'หน้าหลัก',
            'today'=>$this->Attendance_model->get_today($uid),'att_sum'=>$this->Attendance_model->get_summary($uid,date('Y'),date('n')),
            'leaves'=>$this->Leave_model->get_requests(['user_id'=>$uid,'status'=>'pending'],5),
            'salaries'=>$this->Salary_model->get_records(['user_id'=>$uid],3),'notifs'=>$this->Notification_model->get_all($uid,5)]);
    }
}
