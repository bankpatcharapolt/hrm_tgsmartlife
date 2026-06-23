<?php defined('BASEPATH') OR exit('No direct script access allowed');
class Leave extends Admin_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->model('Leave_model');
        $this->load->library('upload');
    }

    // ── รายการคำขอลา ──────────────────────────────────────
    public function index() {
        $f = array(
            'status'  => $this->input->get('status'),
            'dept_id' => $this->input->get('dept'),
            'year'    => $this->input->get('year') ?: date('Y'),
        );
        $this->render('admin/leave/index', array(
            'title'       => 'จัดการการลา',
            'page_title'  => 'จัดการการลา',
            'requests'    => $this->Leave_model->get_requests($f, 100),
            'departments' => $this->User_model->get_all_departments(),
            'leave_types' => $this->Leave_model->get_types(),
            'filters'     => $f,
        ));
    }

    // ── สร้างคำขอลาแทนพนักงาน (Admin only) ──────────────
    public function create() {
        $this->render('admin/leave/form', array(
            'title'       => 'สร้างคำขอลา',
            'page_title'  => 'สร้างคำขอลาให้พนักงาน',
            'leave_types' => $this->Leave_model->get_types(),
            'employees'   => $this->User_model->get_all(array('status'=>'active'), 300),
            'req'         => null,
        ));
    }

    public function store() {
        if ($this->input->method() !== 'post') redirect('admin/leave');

        $sd   = $this->_normalize_date($this->input->post('start_date'));
        $ed   = $this->_normalize_date($this->input->post('end_date'));
        $unit = $this->input->post('leave_unit') ?: 'day';
        $days = max(1, round((strtotime($ed) - strtotime($sd)) / 86400) + 1);

        $data = array(
            'user_id'       => $this->input->post('user_id'),
            'leave_type_id' => $this->input->post('leave_type_id'),
            'start_date'    => $sd,
            'end_date'      => $ed,
            'total_days'    => $unit === 'hour' ? 0 : $days,
            'leave_unit'    => $unit,
            'reason'        => $this->input->post('reason', TRUE),
            'status'        => $this->input->post('status') ?: 'pending',
        );

        // ลาชั่วโมง
        if ($unit === 'hour') {
            $sh = $this->input->post('leave_start_time');
            $eh = $this->input->post('leave_end_time');
            if ($sh && $eh) {
                $data['total_hours'] = round((strtotime($eh) - strtotime($sh)) / 3600, 2);
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

        // [ข้อ 2] อัปโหลดใบรับรองแพทย์ (เฉพาะลาป่วย)
        if (!empty($_FILES['medical_cert']['size'])) {
            $p = FCPATH.'uploads/leave_docs/medical/';
            if (!is_dir($p)) mkdir($p, 0755, true);
            $ext = strtolower(pathinfo($_FILES['medical_cert']['name'], PATHINFO_EXTENSION));
            if (in_array($ext, array('pdf','jpg','jpeg','png'))) {
                $fn = 'mc_'.uniqid().'.'.$ext;
                if (move_uploaded_file($_FILES['medical_cert']['tmp_name'], $p.$fn)) {
                    $data['medical_cert_path'] = 'uploads/leave_docs/medical/'.$fn;
                }
            }
        }

        // ถ้า admin สร้างและ status=approved → set approved_by
        if ($data['status'] === 'approved') {
            $data['approved_by'] = $this->current_user->user_id;
            $data['approved_at'] = date('Y-m-d H:i:s');
        }

        $id = $this->Leave_model->create($data);
        if ($id) {
            // แจ้งเตือนพนักงาน
            $status_th = array('pending'=>'รอการอนุมัติ','approved'=>'อนุมัติแล้ว','rejected'=>'ปฏิเสธ');
            $this->Notification_model->create(array(
                'user_id'   => $data['user_id'],
                'sender_id' => $this->current_user->user_id,
                'type'      => $data['status'] === 'approved' ? 'leave_approved' : 'leave_request',
                'title'     => 'มีการบันทึกการลาให้คุณ',
                'message'   => 'Admin บันทึกการลา '.$sd.' ถึง '.$ed.' สถานะ: '.($status_th[$data['status']]??$data['status']),
                'link'      => base_url('employee/leave'),
            ));
            $this->User_model->log($this->current_user->user_id, 'create_leave', 'leave', 'สร้างการลา user_id:'.$data['user_id']);
            $this->session->set_flashdata('success', 'สร้างคำขอลาสำเร็จ');
        } else {
            $this->session->set_flashdata('error', 'เกิดข้อผิดพลาด');
        }
        redirect('admin/leave');
    }

    // ── แก้ไขคำขอลา ──────────────────────────────────────
    public function edit($id) {
        $req = $this->Leave_model->get_by_id($id);
        if (!$req) { $this->session->set_flashdata('error', 'ไม่พบข้อมูล'); redirect('admin/leave'); }

        if ($this->input->method() === 'post') {
            $sd   = $this->input->post('start_date');
            $ed   = $this->input->post('end_date');
            $unit = $this->input->post('leave_unit') ?: 'day';
            $days = max(1, round((strtotime($ed) - strtotime($sd)) / 86400) + 1);

            $data = array(
                'user_id'       => $this->input->post('user_id'),
                'leave_type_id' => $this->input->post('leave_type_id'),
                'start_date'    => $sd,
                'end_date'      => $ed,
                'total_days'    => $unit === 'hour' ? 0 : $days,
                'leave_unit'    => $unit,
                'reason'        => $this->input->post('reason', TRUE),
                'status'        => $this->input->post('status') ?: $req->status,
                'approver_note' => $this->input->post('approver_note', TRUE),
                'updated_at'    => date('Y-m-d H:i:s'),
            );

            // ลาชั่วโมง
            if ($unit === 'hour') {
                $sh = $this->input->post('leave_start_time');
                $eh = $this->input->post('leave_end_time');
                if ($sh && $eh) {
                    $data['total_hours'] = round((strtotime($eh) - strtotime($sh)) / 3600, 2);
                    $data['start_time']  = $sh;
                    $data['end_time']    = $eh;
                }
            }

            // update approved_by ถ้าเปลี่ยนสถานะ
            if (in_array($data['status'], array('approved','rejected')) && $data['status'] !== $req->status) {
                $data['approved_by'] = $this->current_user->user_id;
                $data['approved_at'] = date('Y-m-d H:i:s');
            }

            // อัปโหลดเอกสารใหม่
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

            // [ข้อ 2] อัปโหลดใบรับรองแพทย์ใหม่
            if (!empty($_FILES['medical_cert']['size'])) {
                $p = FCPATH.'uploads/leave_docs/medical/';
                if (!is_dir($p)) mkdir($p, 0755, true);
                $ext = strtolower(pathinfo($_FILES['medical_cert']['name'], PATHINFO_EXTENSION));
                if (in_array($ext, array('pdf','jpg','jpeg','png'))) {
                    $fn = 'mc_'.uniqid().'.'.$ext;
                    if (move_uploaded_file($_FILES['medical_cert']['tmp_name'], $p.$fn)) {
                        if (!empty($req->medical_cert_path) && file_exists(FCPATH.$req->medical_cert_path))
                            @unlink(FCPATH.$req->medical_cert_path);
                        $data['medical_cert_path'] = 'uploads/leave_docs/medical/'.$fn;
                    }
                }
            }

            $this->db->where('id', $id)->update('leave_requests', $data);
            $this->User_model->log($this->current_user->user_id, 'edit_leave', 'leave', 'แก้ไข ID:'.$id);

            // แจ้งเตือนพนักงานถ้าเปลี่ยนสถานะ
            if ($data['status'] !== $req->status) {
                $type_map = array('approved'=>'leave_approved','rejected'=>'leave_rejected');
                $title_map = array('approved'=>'คำขอลาได้รับการอนุมัติ','rejected'=>'คำขอลาถูกปฏิเสธ');
                $ntype = $type_map[$data['status']] ?? 'general';
                $ntitle = $title_map[$data['status']] ?? 'สถานะการลาเปลี่ยนแปลง';
                $note = !empty($data['approver_note']) ? ' เหตุผล: '.$data['approver_note'] : '';
                $this->Notification_model->create(array(
                    'user_id'   => $req->user_id,
                    'sender_id' => $this->current_user->user_id,
                    'type'      => $ntype,
                    'title'     => $ntitle,
                    'message'   => 'การลาวันที่ '.$sd.' ถึง '.$ed.$note,
                    'link'      => base_url('employee/leave'),
                ));
            }

            $this->session->set_flashdata('success', 'แก้ไขสำเร็จ');
            redirect('admin/leave');
        }

        $this->render('admin/leave/form', array(
            'title'       => 'แก้ไขคำขอลา',
            'page_title'  => 'แก้ไขคำขอลา',
            'leave_types' => $this->Leave_model->get_types(),
            'employees'   => $this->User_model->get_all(array('status'=>'active'), 300),
            'req'         => $req,
        ));
    }

    // ── ลบคำขอลา ──────────────────────────────────────────
    public function delete($id) {
        $req = $this->Leave_model->get_by_id($id);
        if ($req) {
            $this->db->where('id', $id)->delete('leave_requests');
            $this->User_model->log($this->current_user->user_id, 'delete_leave', 'leave', 'ลบ ID:'.$id.' ของ '.$req->first_name.' '.$req->last_name);
            // ลบไฟล์เอกสาร
            if (!empty($req->document_path) && file_exists(FCPATH.$req->document_path)) {
                @unlink(FCPATH.$req->document_path);
            }
            $this->session->set_flashdata('success', 'ลบคำขอลาสำเร็จ');
        }
        redirect('admin/leave');
    }

    // ── อนุมัติ/ปฏิเสธ (quick action) ────────────────────
    public function approve($id) {
        $note = $this->input->post('note', TRUE);
        $req  = $this->Leave_model->get_by_id($id);
        if ($this->Leave_model->approve($id, $this->current_user->user_id, $note) && $req) {
            $this->Notification_model->create(array(
                'user_id'   => $req->user_id,
                'sender_id' => $this->current_user->user_id,
                'type'      => 'leave_approved',
                'title'     => 'คำขอลาได้รับการอนุมัติ',
                'message'   => 'การลาวันที่ '.$req->start_date.' ถึง '.$req->end_date.' อนุมัติแล้ว',
                'link'      => base_url('employee/leave'),
            ));
        }
        $this->session->set_flashdata('success', 'อนุมัติสำเร็จ');
        redirect('admin/leave');
    }

    public function reject($id) {
        $note = $this->input->post('note', TRUE);
        $req  = $this->Leave_model->get_by_id($id);
        if ($this->Leave_model->reject($id, $this->current_user->user_id, $note) && $req) {
            $this->Notification_model->create(array(
                'user_id'   => $req->user_id,
                'sender_id' => $this->current_user->user_id,
                'type'      => 'leave_rejected',
                'title'     => 'คำขอลาถูกปฏิเสธ',
                'message'   => 'การลาวันที่ '.$req->start_date.' ถูกปฏิเสธ'.($note ? ': '.$note : ''),
                'link'      => base_url('employee/leave'),
            ));
        }
        $this->session->set_flashdata('warning', 'ปฏิเสธสำเร็จ');
        redirect('admin/leave');
    }

    private function _normalize_date($val) {
        if (empty($val)) return null;
        $val = trim($val);
        if (preg_match('/^(\d{2})\/(\d{2})\/(\d{4})$/', $val, $m)) {
            return "{$m[3]}-{$m[2]}-{$m[1]}";
        }
        $ts = strtotime($val);
        return $ts ? date('Y-m-d', $ts) : null;
    }

}
