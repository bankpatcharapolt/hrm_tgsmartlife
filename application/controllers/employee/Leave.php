<?php defined('BASEPATH') OR exit('No direct script access allowed');
class Leave extends Employee_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->model('Leave_model');
    }

    // ── รายการลาของตัวเอง ─────────────────────────────────
    public function index() {
        $uid = $this->current_user->user_id;
        $this->render('employee/leave/index', array(
            'title'       => 'การลาของฉัน',
            'page_title'  => 'การลาของฉัน',
            'requests'    => $this->Leave_model->get_requests(array('user_id'=>$uid), 50),
            'leave_types' => $this->Leave_model->get_types(),
        ));
    }

    // ── ยื่นคำขอลา ────────────────────────────────────────
    public function request() {
        $this->render('employee/leave/request', array(
            'title'       => 'ยื่นคำขอลา',
            'page_title'  => 'ยื่นคำขอลา',
            'leave_types' => $this->Leave_model->get_types(),
        ));
    }

    public function store() {
        if ($this->input->method() !== 'post') redirect('employee/leave');
        $uid  = $this->current_user->user_id;
        $sd   = $this->input->post('start_date');
        $ed   = $this->input->post('end_date');
        $unit = $this->input->post('leave_unit') ?: 'day';
        $days = $unit === 'hour' ? 0 : max(1, round((strtotime($ed)-strtotime($sd))/86400)+1);

        $data = array(
            'user_id'       => $uid,
            'leave_type_id' => $this->input->post('leave_type_id'),
            'start_date'    => $sd,
            'end_date'      => $ed,
            'total_days'    => $days,
            'leave_unit'    => $unit,
            'reason'        => $this->input->post('reason', TRUE),
            'status'        => 'pending',
            'total_hours'   => 0,
        );

        // ลาชั่วโมง
        if ($unit === 'hour') {
            $sh = $this->input->post('leave_start_time');
            $eh = $this->input->post('leave_end_time');
            if ($sh && $eh) {
                $data['total_hours'] = round((strtotime($eh)-strtotime($sh))/3600, 2);
                $data['start_time']  = $sh;
                $data['end_time']    = $eh;
            }
        }

        // อัปโหลดเอกสาร
        if (!empty($_FILES['document']['size'])) {
            $p = FCPATH.'uploads/leave_docs/';
            if (!is_dir($p)) mkdir($p, 0755, true);
            $ext = strtolower(pathinfo($_FILES['document']['name'], PATHINFO_EXTENSION));
            if (in_array($ext, array('pdf','jpg','jpeg','png'))) {
                $fn = uniqid().'.'.$ext;
                if (move_uploaded_file($_FILES['document']['tmp_name'], $p.$fn)) {
                    $data['document_path'] = 'uploads/leave_docs/'.$fn;
                }
            }
        }

        $id = $this->Leave_model->create($data);
        if ($id) {
            $this->Notification_model->send_to_role($uid, 'admin', 'leave_request', 'มีคำขอลาใหม่',
                $this->current_user->full_name.' ขอลา '.$days.' วัน ('.($sd===$ed?$sd:$sd.' ถึง '.$ed).')',
                base_url('admin/leave'));
            $this->Notification_model->send_to_role($uid, 'manager', 'leave_request', 'มีคำขอลาใหม่',
                $this->current_user->full_name.' ขอลา '.$days.' วัน',
                base_url('manager/leave'));
            $this->session->set_flashdata('success', 'ส่งคำขอลาสำเร็จ รอการอนุมัติ');
        } else {
            $this->session->set_flashdata('error', 'เกิดข้อผิดพลาด');
        }
        redirect('employee/leave');
    }

    // ── แก้ไข (เฉพาะของตัวเอง, เฉพาะ pending) ───────────
    public function edit($id) {
        $uid = $this->current_user->user_id;
        $req = $this->db->where('id',$id)->where('user_id',$uid)->get('leave_requests')->row();
        if (!$req) {
            $this->session->set_flashdata('error', 'ไม่พบข้อมูล หรือไม่ใช่คำขอของคุณ');
            redirect('employee/leave');
        }
        // เฉพาะ pending แก้ได้
        if ($req->status !== 'pending') {
            $this->session->set_flashdata('error', 'แก้ไขได้เฉพาะคำขอที่รอการอนุมัติเท่านั้น');
            redirect('employee/leave');
        }

        if ($this->input->method() === 'post') {
            $sd   = $this->input->post('start_date');
            $ed   = $this->input->post('end_date');
            $unit = $this->input->post('leave_unit') ?: 'day';
            $days = $unit === 'hour' ? 0 : max(1, round((strtotime($ed)-strtotime($sd))/86400)+1);

            $data = array(
                'leave_type_id' => $this->input->post('leave_type_id'),
                'start_date'    => $sd,
                'end_date'      => $ed,
                'total_days'    => $days,
                'leave_unit'    => $unit,
                'reason'        => $this->input->post('reason', TRUE),
                'total_hours'   => 0,
                'updated_at'    => date('Y-m-d H:i:s'),
            );

            if ($unit === 'hour') {
                $sh = $this->input->post('leave_start_time');
                $eh = $this->input->post('leave_end_time');
                if ($sh && $eh) {
                    $data['total_hours'] = round((strtotime($eh)-strtotime($sh))/3600, 2);
                    $data['start_time']  = $sh;
                    $data['end_time']    = $eh;
                }
            }

            if (!empty($_FILES['document']['size'])) {
                $p = FCPATH.'uploads/leave_docs/';
                if (!is_dir($p)) mkdir($p, 0755, true);
                $ext = strtolower(pathinfo($_FILES['document']['name'], PATHINFO_EXTENSION));
                if (in_array($ext, array('pdf','jpg','jpeg','png'))) {
                    $fn = uniqid().'.'.$ext;
                    if (move_uploaded_file($_FILES['document']['tmp_name'], $p.$fn)) {
                        $data['document_path'] = 'uploads/leave_docs/'.$fn;
                    }
                }
            }

            $this->db->where('id',$id)->where('user_id',$uid)->update('leave_requests', $data);
            $this->session->set_flashdata('success', 'แก้ไขคำขอลาสำเร็จ');
            redirect('employee/leave');
        }

        $this->render('employee/leave/edit', array(
            'title'       => 'แก้ไขคำขอลา',
            'page_title'  => 'แก้ไขคำขอลา',
            'req'         => $req,
            'leave_types' => $this->Leave_model->get_types(),
        ));
    }

    // ── ลบ (เฉพาะของตัวเอง, เฉพาะ pending) ──────────────
    public function cancel($id) {
        $uid = $this->current_user->user_id;
        $req = $this->db->where('id',$id)->where('user_id',$uid)->get('leave_requests')->row();
        if (!$req) {
            $this->session->set_flashdata('error', 'ไม่พบข้อมูล หรือไม่ใช่คำขอของคุณ');
            redirect('employee/leave');
        }
        if (!in_array($req->status, array('pending','cancelled'))) {
            $this->session->set_flashdata('error', 'ไม่สามารถลบคำขอที่อนุมัติ/ปฏิเสธแล้วได้');
            redirect('employee/leave');
        }
        if (!empty($req->document_path) && file_exists(FCPATH.$req->document_path)) {
            @unlink(FCPATH.$req->document_path);
        }
        $this->db->where('id',$id)->where('user_id',$uid)->delete('leave_requests');
        $this->session->set_flashdata('success', 'ลบคำขอลาสำเร็จ');
        redirect('employee/leave');
    }
}
