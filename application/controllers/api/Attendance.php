<?php defined('BASEPATH') OR exit('No direct script access allowed');
class Attendance extends MY_Controller {
    public function __construct() { parent::__construct(); $this->require_login(); $this->load->model('Attendance_model'); }
    public function checkin() {
        if($this->input->method()!=='post'){$this->json_err('Method not allowed');return;}
        $uid=$this->current_user->user_id; $today=$this->Attendance_model->get_today($uid);
        if($today&&$today->check_in_time){$this->json_err('ลงเวลาเข้างานแล้ว');return;}
        $id=$this->Attendance_model->checkin($uid,$this->current_user->role_id);
        $today=$this->Attendance_model->get_today($uid);
        if($today->is_late) $this->Notification_model->send_to_role($uid,'admin','late_checkin','พนักงานมาสาย',$this->current_user->full_name.' มาสาย '.$today->late_minutes.' นาที',base_url('admin/attendance'));
        $this->json_ok(['time'=>date('H:i:s'),'is_late'=>(bool)$today->is_late,'late_minutes'=>$today->late_minutes],'ลงเวลาเข้างานสำเร็จ');
    }
    public function checkout() {
        if($this->input->method()!=='post'){$this->json_err('Method not allowed');return;}
        $uid=$this->current_user->user_id; $today=$this->Attendance_model->get_today($uid);
        if(!$today||!$today->check_in_time){$this->json_err('ยังไม่ได้ลงเวลาเข้างาน');return;}
        if($today->check_out_time){$this->json_err('ลงเวลาออกงานแล้ว');return;}
        $this->Attendance_model->checkout($today->id,$uid);
        $this->json_ok(['time'=>date('H:i:s')],'ลงเวลาออกงานสำเร็จ');
    }
}
