<?php defined('BASEPATH') OR exit('No direct script access allowed');
class Notifications extends Employee_Controller {
    public function index() { $uid=$this->current_user->user_id; $this->Notification_model->mark_all_read($uid); $this->render('employee/notifications/index',['title'=>'การแจ้งเตือน','page_title'=>'การแจ้งเตือนทั้งหมด','notifs'=>$this->Notification_model->get_all($uid,50)]); }
}
