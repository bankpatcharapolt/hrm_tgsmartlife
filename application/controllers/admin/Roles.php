<?php defined('BASEPATH') OR exit('No direct script access allowed');
class Roles extends Admin_Controller {
    public function __construct() {
        parent::__construct();
        $this->require_role(array('owner'));
    }
    public function index() {
        $this->render('admin/roles/index', array(
            'title'      => 'ตั้งค่าบทบาท',
            'page_title' => 'ตั้งค่าบทบาทและสิทธิ์',
            'roles'      => $this->User_model->get_all_roles()
        ));
    }
    public function edit($id) {
        $r = $this->User_model->get_role($id);
        if (!$r) redirect('admin/roles');
        $this->render('admin/roles/edit', array(
            'title'      => 'แก้ไขบทบาท',
            'page_title' => 'แก้ไขบทบาท',
            'role'       => $r
        ));
    }
    public function update($id) {
        if ($this->input->method() !== 'post') redirect('admin/roles');
        $fs = array('can_checkin','can_view_own_salary','can_approve_leave',
                    'can_manage_employees','can_view_sales','can_send_notifications',
                    'can_manage_salary','can_upload_documents','can_view_reports','can_monitor_attendance');
        $d = array(
            'name'                  => $this->input->post('name', TRUE),
            'work_start_time'       => $this->input->post('work_start_time'),
            'work_end_time'         => $this->input->post('work_end_time'),
            'leave_quota_sick'      => (int)$this->input->post('leave_quota_sick'),
            'leave_quota_personal'  => (int)$this->input->post('leave_quota_personal'),
            'leave_quota_vacation'  => (int)$this->input->post('leave_quota_vacation')
        );
        foreach ($fs as $f) $d[$f] = $this->input->post($f) ? 1 : 0;
        $this->User_model->update_role($id, $d);
        $this->session->set_flashdata('success', 'อัปเดตสำเร็จ');
        redirect('admin/roles');
    }
}
