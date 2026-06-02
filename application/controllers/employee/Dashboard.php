<?php defined('BASEPATH') OR exit('No direct script access allowed');
class Dashboard extends Employee_Controller {
   public function __construct() {
    parent::__construct();
    // เพิ่ม 'Notification_model' เข้าไปใน array
    $this->load->model(array('Attendance_model', 'Leave_model', 'Salary_model', 'Notification_model'));
}
    public function index() {
        $uid = $this->current_user->user_id;
      
        $this->render('employee/dashboard/index', array(
            'title'    => 'หน้าหลัก',
            'page_title'=> 'หน้าหลัก',
            'today'    => $this->Attendance_model->get_today($uid),
            'att_sum'  => $this->Attendance_model->get_summary($uid, date('Y'), date('n')),
            'leaves'   => $this->Leave_model->get_requests(array('user_id'=>$uid,'status'=>'pending'), 5),
            'salaries' => $this->Salary_model->get_records(array('user_id'=>$uid), 3),
            'notifs'   => $this->Notification_model->get_all($uid, 5)
        ));
    }
}
