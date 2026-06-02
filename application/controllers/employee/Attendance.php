<?php defined('BASEPATH') OR exit('No direct script access allowed');
class Attendance extends Employee_Controller {

    public function __construct() {
        parent::__construct();
        $this->require_permission('can_checkin');
        $this->load->model(array('Attendance_model','Shift_model','Leave_model'));
    }

    // ── รายการการเข้างานของตัวเอง ──────────────────────────
    public function index() {
        $uid = $this->current_user->user_id;
        $y   = $this->input->get('year')  ?: date('Y');
        $m   = $this->input->get('month') ?: date('n');

        $this->render('employee/attendance/index', array(
            'title'      => 'การเข้างานของฉัน',
            'page_title' => 'ตารางการเข้างานของฉัน',
            'records'    => $this->Attendance_model->get_monthly($uid, $y, $m),
            'summary'    => $this->Attendance_model->get_summary($uid, $y, $m),
            'today'      => $this->Attendance_model->get_today($uid),
            'shifts'     => $this->Shift_model->get_all(),
            'leave_types'=> $this->Leave_model->get_types(),
            'year'       => $y,
            'month'      => $m,
        ));
    }

    // ── เพิ่มรายการด้วยตนเอง (เฉพาะของตัวเอง) ────────────
    public function add() {
        if ($this->input->method() !== 'post') redirect('employee/attendance');

        $uid    = $this->current_user->user_id;
        $status = $this->input->post('status') ?: 'present';
        $date   = $this->input->post('date');

        // ตรวจซ้ำ
        $exists = $this->db->where('user_id',$uid)->where('date',$date)->count_all_results('attendance');
        if ($exists > 0) {
            $this->session->set_flashdata('error','มีข้อมูลวันที่ '.$date.' อยู่แล้ว');
            redirect('employee/attendance');
        }

        $data = array(
            'user_id'        => $uid,
            'shift_id'       => $this->input->post('shift_id') ?: null,
            'date'           => $date,
            'check_in_time'  => $this->input->post('check_in')  ?: null,
            'check_out_time' => $this->input->post('check_out') ?: null,
            'status'         => $status,
            'note'           => $this->input->post('note', TRUE),
            'is_late'        => 0,
            'late_minutes'   => 0,
            'ot_hours'       => 0,
        );

        // ลาชั่วโมง
        if ($status === 'leave') {
            $unit = $this->input->post('leave_unit') ?: 'day';
            $data['leave_type_id'] = $this->input->post('leave_type_id') ?: null;
            if ($unit === 'hour') {
                $sh = $this->input->post('leave_start_hour');
                $eh = $this->input->post('leave_end_hour');
                if ($sh && $eh) {
                    $data['leave_hours'] = round((strtotime($eh)-strtotime($sh))/3600, 2);
                }
            }
        }

        $this->Attendance_model->manual_add($data);
        $this->session->set_flashdata('success', 'บันทึกสำเร็จ');
        redirect('employee/attendance');
    }

    // ── แก้ไข (เฉพาะของตัวเอง) ────────────────────────────
    public function edit($id) {
        $uid = $this->current_user->user_id;
        $rec = $this->db->where('id',$id)->where('user_id',$uid)->get('attendance')->row();
        if (!$rec) {
            $this->session->set_flashdata('error', 'ไม่พบข้อมูล หรือไม่ใช่รายการของคุณ');
            redirect('employee/attendance');
        }

        if ($this->input->method() === 'post') {
            $status = $this->input->post('status') ?: $rec->status;
            $data = array(
                'check_in_time'  => $this->input->post('check_in')  ?: null,
                'check_out_time' => $this->input->post('check_out') ?: null,
                'status'         => $status,
                'note'           => $this->input->post('note', TRUE),
                'updated_at'     => date('Y-m-d H:i:s'),
            );
            if ($status === 'leave') {
                $unit = $this->input->post('leave_unit') ?: 'day';
                $data['leave_type_id'] = $this->input->post('leave_type_id') ?: null;
                if ($unit === 'hour') {
                    $sh = $this->input->post('leave_start_hour');
                    $eh = $this->input->post('leave_end_hour');
                    if ($sh && $eh) {
                        $data['leave_hours'] = round((strtotime($eh)-strtotime($sh))/3600, 2);
                    }
                } else {
                    $data['leave_hours'] = 0;
                }
            }
            $this->db->where('id',$id)->where('user_id',$uid)->update('attendance', $data);
            $this->session->set_flashdata('success', 'แก้ไขสำเร็จ');
            redirect('employee/attendance');
        }

        $this->render('employee/attendance/edit', array(
            'title'       => 'แก้ไขการเข้างาน',
            'page_title'  => 'แก้ไขการเข้างาน',
            'rec'         => $rec,
            'shifts'      => $this->Shift_model->get_all(),
            'leave_types' => $this->Leave_model->get_types(),
        ));
    }

    // ── ลบ (เฉพาะของตัวเอง) ──────────────────────────────
    public function delete($id) {
        $uid = $this->current_user->user_id;
        $rec = $this->db->where('id',$id)->where('user_id',$uid)->get('attendance')->row();
        if ($rec) {
            $this->db->where('id',$id)->where('user_id',$uid)->delete('attendance');
            $this->session->set_flashdata('success', 'ลบรายการสำเร็จ');
        } else {
            $this->session->set_flashdata('error', 'ไม่พบข้อมูล หรือไม่ใช่รายการของคุณ');
        }
        redirect('employee/attendance');
    }
}
