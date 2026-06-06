<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Leave_types extends Admin_Controller
{
    public function __construct()
    {
        parent::__construct();
        // เฉพาะ owner และ admin เท่านั้น
        $this->require_role(array('admin', 'owner'));
    }

    // ── รายการทั้งหมด ─────────────────────────────────────────────
    public function index()
    {
        $types = $this->db->order_by('id', 'ASC')->get('leave_types')->result();

        $this->render('admin/leave_types/index', array(
            'title'      => 'จัดการประเภทการลา',
            'page_title' => 'จัดการประเภทการลา',
            'types'      => $types,
            'rec'        => null,
        ));
    }

    // ── บันทึกใหม่ ────────────────────────────────────────────────
    public function store()
    {
        if ($this->input->method() !== 'post') redirect('admin/leave_types');

        $data = array(
            'leave_code'        => strtoupper(trim($this->input->post('leave_code', TRUE))) ?: null,
            'name'              => trim($this->input->post('name', TRUE)),
            'quota_per_year'    => (int)$this->input->post('quota_per_year'),
            'is_paid'           => $this->input->post('is_paid') ? 1 : 0,
            'is_deduct_salary'  => $this->input->post('is_deduct_salary') ? 1 : 0,
            'requires_doc'      => $this->input->post('requires_doc') ? 1 : 0,
            'require_doc_days'  => (int)$this->input->post('require_doc_days'),
            'can_leave_by_hour' => $this->input->post('can_leave_by_hour') ? 1 : 0,
            'is_carry_forward'  => $this->input->post('is_carry_forward') ? 1 : 0,
            'description'       => $this->input->post('description', TRUE) ?: null,
        );

        if (empty($data['name'])) {
            $this->session->set_flashdata('error', 'กรุณาระบุชื่อประเภทการลา');
            redirect('admin/leave_types');
        }

        $this->db->insert('leave_types', $data);
        $this->session->set_flashdata('success', 'เพิ่มประเภทการลา "' . $data['name'] . '" สำเร็จ');
        redirect('admin/leave_types');
    }

    // ── แก้ไข ─────────────────────────────────────────────────────
    public function edit($id)
    {
        $rec = $this->db->where('id', $id)->get('leave_types')->row();
        if (!$rec) {
            $this->session->set_flashdata('error', 'ไม่พบข้อมูล');
            redirect('admin/leave_types');
        }

        $types = $this->db->order_by('id', 'ASC')->get('leave_types')->result();

        $this->render('admin/leave_types/index', array(
            'title'      => 'แก้ไขประเภทการลา',
            'page_title' => 'จัดการประเภทการลา',
            'types'      => $types,
            'rec'        => $rec,
        ));
    }

    // ── อัปเดต ────────────────────────────────────────────────────
    public function update($id)
    {
        if ($this->input->method() !== 'post') redirect('admin/leave_types');

        $data = array(
            'leave_code'        => strtoupper(trim($this->input->post('leave_code', TRUE))) ?: null,
            'name'              => trim($this->input->post('name', TRUE)),
            'quota_per_year'    => (int)$this->input->post('quota_per_year'),
            'is_paid'           => $this->input->post('is_paid') ? 1 : 0,
            'is_deduct_salary'  => $this->input->post('is_deduct_salary') ? 1 : 0,
            'requires_doc'      => $this->input->post('requires_doc') ? 1 : 0,
            'require_doc_days'  => (int)$this->input->post('require_doc_days'),
            'can_leave_by_hour' => $this->input->post('can_leave_by_hour') ? 1 : 0,
            'is_carry_forward'  => $this->input->post('is_carry_forward') ? 1 : 0,
            'description'       => $this->input->post('description', TRUE) ?: null,
        );

        if (empty($data['name'])) {
            $this->session->set_flashdata('error', 'กรุณาระบุชื่อประเภทการลา');
            redirect('admin/leave_types/edit/' . $id);
        }

        $this->db->where('id', $id)->update('leave_types', $data);
        $this->session->set_flashdata('success', 'แก้ไขสำเร็จ');
        redirect('admin/leave_types');
    }

    // ── ลบ ───────────────────────────────────────────────────────
    public function delete($id)
    {
        // ตรวจว่ามีการลาที่ใช้ประเภทนี้อยู่ไหม
        $used = $this->db->where('leave_type_id', $id)->count_all_results('leave_requests');
        if ($used > 0) {
            $this->session->set_flashdata('error',
                'ไม่สามารถลบได้ เนื่องจากมีคำขอลาที่ใช้ประเภทนี้อยู่ ' . $used . ' รายการ');
            redirect('admin/leave_types');
        }

        $rec = $this->db->where('id', $id)->get('leave_types')->row();
        if ($rec) {
            $this->db->where('id', $id)->delete('leave_types');
            $this->session->set_flashdata('success', 'ลบประเภท "' . $rec->name . '" สำเร็จ');
        }
        redirect('admin/leave_types');
    }
}
