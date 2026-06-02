<?php defined('BASEPATH') OR exit('No direct script access allowed');
class Teams extends Admin_Controller {
    public function __construct() {
        parent::__construct();
        // เฉพาะ admin/owner เท่านั้น
        if (!$this->current_user->is_full_access && !$this->current_user->can_manage_employees) {
            $this->session->set_flashdata('error','ไม่มีสิทธิ์เข้าถึง');
            redirect('admin/dashboard');
        }
    }
    public function index() {
        $this->render('admin/teams/index', array(
            'title'      => 'จัดการทีม/สาขา',
            'page_title' => 'จัดการทีม/สาขา',
            'teams'      => $this->db->select('t.*,COUNT(u.id) AS member_count')
                ->from('teams t')
                ->join('users u','u.team_id=t.id AND u.status="active"','left')
                ->group_by('t.id')
                ->order_by('t.team_code','ASC')
                ->get()->result(),
        ));
    }
    public function store() {
        if ($this->input->method()!=='post') redirect('admin/teams');
        $id = $this->input->post('team_id');
        $data = array(
            'team_code'      => strtoupper(trim($this->input->post('team_code',TRUE))),
            'team_name'      => trim($this->input->post('team_name',TRUE)),
            'location'       => trim($this->input->post('location',TRUE)),
            'monthly_target' => (float)$this->input->post('monthly_target'),
            'manager_emp_id' => trim($this->input->post('manager_emp_id',TRUE)) ?: null,
            'is_active'      => $this->input->post('is_active') ? 1 : 0,
            'updated_at'     => date('Y-m-d H:i:s'),
        );
        if ($id) {
            $this->db->where('id',$id)->update('teams',$data);
            $msg = 'อัปเดตทีมสำเร็จ';
        } else {
            // ตรวจ code ซ้ำ
            if ($this->db->where('team_code',$data['team_code'])->count_all_results('teams')>0) {
                $this->session->set_flashdata('error','รหัสทีม "'.$data['team_code'].'" ซ้ำ');
                redirect('admin/teams');
            }
            $data['created_at'] = date('Y-m-d H:i:s');
            $this->db->insert('teams',$data);
            $msg = 'สร้างทีมใหม่สำเร็จ';
        }
        $this->session->set_flashdata('success',$msg);
        redirect('admin/teams');
    }
    public function delete($id) {
        $t = $this->db->where('id',$id)->get('teams')->row();
        if (!$t) { redirect('admin/teams'); }
        // ตรวจว่ามีพนักงาน
        $cnt = $this->db->where('team_id',$id)->where('status','active')->count_all_results('users');
        if ($cnt > 0) {
            $this->session->set_flashdata('error','ไม่สามารถลบทีมที่ยังมีพนักงาน '.$cnt.' คนอยู่ได้');
            redirect('admin/teams');
        }
        $this->db->where('id',$id)->delete('teams');
        $this->session->set_flashdata('success','ลบทีม "'.$t->team_name.'" สำเร็จ');
        redirect('admin/teams');
    }
    // API: get team members
    public function members($id) {
        $members = $this->db->select('u.id,u.employee_id,u.first_name,u.last_name,u.position')
            ->from('users u')
            ->where('u.team_id',$id)
            ->where('u.status','active')
            ->get()->result();
        $this->json_ok($members);
    }
}
