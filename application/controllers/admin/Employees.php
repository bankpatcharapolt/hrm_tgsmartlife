<?php defined('BASEPATH') OR exit('No direct script access allowed');
class Employees extends Admin_Controller {
    public function __construct() { parent::__construct(); $this->require_permission('can_manage_employees'); $this->load->library('upload'); }
    public function index() {
        $f=array('search'=>$this->input->get('search',TRUE),'department_id'=>$this->input->get('dept'),'role_id'=>$this->input->get('role'),'status'=>$this->input->get('status')?:'active');
        $pp=20; $pg=max(1,(int)($this->input->get('page')?:1)); $off=($pg-1)*$pp; $total=$this->User_model->count_all($f);
        $this->render('admin/employees/index',array('title'=>'จัดการพนักงาน','page_title'=>'จัดการพนักงาน',
            'employees'=>$this->User_model->get_all($f,$pp,$off),'departments'=>$this->User_model->get_all_departments(),
            'roles'=>$this->User_model->get_all_roles(),'filters'=>$f,'total'=>$total,'pp'=>$pp,'page'=>$pg,'total_pages'=>ceil($total/$pp)));
    }
    public function create() { $this->render('admin/employees/form',array('title'=>'เพิ่มพนักงาน','page_title'=>'เพิ่มพนักงานใหม่','departments'=>$this->User_model->get_all_departments(),'roles'=>$this->User_model->get_all_roles(),'teams'=>$this->db->where('is_active',1)->get('teams')->result(),'emp'=>null)); }
    public function store() {
        if ($this->input->method()!=='post') redirect('admin/employees');
        $d=$this->_fd(); if(!$d) redirect('admin/employees/create');
        if (!empty($_FILES['photo']['size'])) { $ph=$this->_upload('photo','uploads/photos'); if($ph) $d['photo']=$ph; }
        $id=$this->User_model->create($d);
        if ($id) { $this->User_model->log($this->current_user->user_id,'create_employee','employees',$d['employee_id']); $this->session->set_flashdata('success','เพิ่มพนักงานสำเร็จ'); redirect('admin/employees'); }
        $this->session->set_flashdata('error','เกิดข้อผิดพลาด'); redirect('admin/employees/create');
    }
    public function edit($id) { $e=$this->User_model->get_by_id($id); if(!$e){$this->session->set_flashdata('error','ไม่พบข้อมูล');redirect('admin/employees');} $this->render('admin/employees/form',array('title'=>'แก้ไขพนักงาน','page_title'=>'แก้ไขพนักงาน','departments'=>$this->User_model->get_all_departments(),'roles'=>$this->User_model->get_all_roles(),'teams'=>$this->db->where('is_active',1)->get('teams')->result(),'emp'=>$e)); }
    public function update($id) {
        if ($this->input->method()!=='post') redirect('admin/employees');
        $d=$this->_fd($id); if(!$d) redirect('admin/employees/edit/'.$id);
        if (!empty($_FILES['photo']['size'])) { $ph=$this->_upload('photo','uploads/photos'); if($ph) $d['photo']=$ph; }
        $this->User_model->update($id,$d); $this->User_model->log($this->current_user->user_id,'update_employee','employees','ID:'.$id);
        $this->session->set_flashdata('success','แก้ไขสำเร็จ'); redirect('admin/employees');
    }
    public function view($id) { $this->load->model('Attendance_model'); $e=$this->User_model->get_by_id($id); if(!$e){redirect('admin/employees');} $this->render('admin/employees/view',array('title'=>'ข้อมูลพนักงาน','page_title'=>'ข้อมูลพนักงาน','emp'=>$e,'att_sum'=>$this->Attendance_model->get_summary($id,date('Y'),date('n')))); }
    public function deactivate($id) { $this->User_model->update($id,array('status'=>'inactive','end_date'=>date('Y-m-d'))); $this->session->set_flashdata('success','ปิดการใช้งานสำเร็จ'); redirect('admin/employees'); }
    private function _fd($ex=0) {
        $eid=trim($this->input->post('employee_id',TRUE)); $un=trim($this->input->post('username',TRUE));
        if ($this->User_model->employee_id_exists($eid,$ex)) { $this->session->set_flashdata('error','รหัสพนักงานซ้ำ'); return false; }
        if ($this->User_model->username_exists($un,$ex)) { $this->session->set_flashdata('error','ชื่อผู้ใช้ซ้ำ'); return false; }
        $d=array('employee_id'=>$eid,'username'=>$un,'role_id'=>$this->input->post('role_id'),
            'department_id'=>$this->input->post('department_id')?:null,
            'team_id'=>$this->input->post('team_id')?:null,
            'title'=>$this->input->post('title',TRUE),
            'first_name'=>$this->input->post('first_name',TRUE),'last_name'=>$this->input->post('last_name',TRUE),
            'first_name_en'=>$this->input->post('first_name_en',TRUE),
            'last_name_en'=>$this->input->post('last_name_en',TRUE),
            'nickname'=>$this->input->post('nickname',TRUE),'gender'=>$this->input->post('gender'),
            'date_of_birth'=>$this->input->post('dob')?:null,'id_card_number'=>$this->input->post('id_card',TRUE),
            'phone'=>$this->input->post('phone',TRUE),'email'=>$this->input->post('email',TRUE),
            'emergency_contact'=>$this->input->post('emergency_contact',TRUE),
            'emergency_phone'=>$this->input->post('emergency_phone',TRUE),
            'address'=>$this->input->post('address',TRUE),
            'sub_district'=>$this->input->post('sub_district',TRUE),
            'district'=>$this->input->post('district',TRUE),
            'province'=>$this->input->post('province',TRUE),
            'postal_code'=>$this->input->post('postal_code',TRUE),
            'position'=>$this->input->post('position',TRUE),
            'employee_type'=>$this->input->post('employee_type')?:'รายเดือน',
            'start_date'=>$this->input->post('start_date'),
            'base_salary'=>(float)$this->input->post('base_salary'),
            'salary_account'=>$this->input->post('salary_account',TRUE),
            'social_security_id'=>$this->input->post('ssid',TRUE),
            'social_security_status'=>$this->input->post('social_security_status')?:'ขึ้นทะเบียนประกันสังคม',
            'withholding_tax'=>(float)$this->input->post('withholding_tax'),
            'tax_id'=>$this->input->post('tax_id',TRUE),
            'payment_channel'=>$this->input->post('payment_channel',TRUE),
            'bank_account'=>$this->input->post('bank_account',TRUE),
            'status'=>$this->input->post('status')?:'active');
        $pw=$this->input->post('password'); if($pw) $d['password']=$pw;
        return $d;
    }
    private function _upload($field,$subdir) {
        $p=FCPATH.$subdir.'/'; if(!is_dir($p)) mkdir($p,0755,true);
        $this->upload->initialize(array('upload_path'=>$p,'allowed_types'=>'jpg|jpeg|png|webp','max_size'=>2048,'encrypt_name'=>TRUE));
        if ($this->upload->do_upload($field)) return $subdir.'/'.$this->upload->data('file_name');
        return false;
    }
}
