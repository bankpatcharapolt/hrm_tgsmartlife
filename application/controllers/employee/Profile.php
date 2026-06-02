<?php defined('BASEPATH') OR exit('No direct script access allowed');
class Profile extends Employee_Controller {
    public function __construct() { parent::__construct(); $this->load->library('upload'); }
    public function index() { $this->render('employee/profile/index',['title'=>'โปรไฟล์ของฉัน','page_title'=>'โปรไฟล์ของฉัน','emp'=>$this->User_model->get_by_id($this->current_user->user_id)]); }
    public function update() {
        if($this->input->method()!=='post') redirect('employee/profile');
        $uid=$this->current_user->user_id;
        $d=['phone'=>$this->input->post('phone',TRUE),'email'=>$this->input->post('email',TRUE),'address'=>$this->input->post('address',TRUE),'nickname'=>$this->input->post('nickname',TRUE)];
        $pw=$this->input->post('new_password'); $cpw=$this->input->post('confirm_password');
        if($pw) { if($pw!==$cpw){$this->session->set_flashdata('error','รหัสผ่านไม่ตรงกัน');redirect('employee/profile');} $d['password']=$pw; }
        if(!empty($_FILES['photo']['size'])) { $p=FCPATH.'uploads/photos/'; if(!is_dir($p))mkdir($p,0755,true); $this->upload->initialize(['upload_path'=>$p,'allowed_types'=>'jpg|jpeg|png|webp','max_size'=>2048,'encrypt_name'=>TRUE]); if($this->upload->do_upload('photo'))$d['photo']='uploads/photos/'.$this->upload->data('file_name'); }
        $this->User_model->update($uid,$d); $this->session->set_flashdata('success','อัปเดตสำเร็จ'); redirect('employee/profile');
    }
}
