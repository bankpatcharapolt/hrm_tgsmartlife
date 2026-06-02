<?php defined('BASEPATH') OR exit('No direct script access allowed');
class Notifications extends Admin_Controller {
    public function __construct() { parent::__construct(); $this->require_permission('can_send_notifications'); }
    public function index() { $this->render('admin/notifications/index',['title'=>'ส่งการแจ้งเตือน','page_title'=>'ส่งการแจ้งเตือน','roles'=>$this->User_model->get_all_roles(),'employees'=>$this->User_model->get_all(['status'=>'active'],300)]); }
    public function send() {
        if($this->input->method()!=='post') redirect('admin/notifications');
        $type=$this->input->post('target_type'); $title=$this->input->post('title',TRUE); $msg=$this->input->post('message',TRUE); $link=$this->input->post('link',TRUE); $nt=$this->input->post('notif_type')?:'general';
        switch($type) {
            case 'all': $this->Notification_model->send_to_all($this->current_user->user_id,$nt,$title,$msg,$link); break;
            case 'role': $this->Notification_model->send_to_role($this->current_user->user_id,$this->input->post('role_slug',TRUE),$nt,$title,$msg,$link); break;
            case 'individual': $this->Notification_model->create(['user_id'=>$this->input->post('user_id'),'sender_id'=>$this->current_user->user_id,'type'=>$nt,'title'=>$title,'message'=>$msg,'link'=>$link]); break;
        }
        $this->User_model->log($this->current_user->user_id,'send_notification','notifications',$title);
        $this->session->set_flashdata('success','ส่งการแจ้งเตือนสำเร็จ'); redirect('admin/notifications');
    }
}
