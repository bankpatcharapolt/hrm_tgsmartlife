<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Attendance extends Employee_Controller {

    public function __construct() {
        parent::__construct();
        $this->require_permission('can_checkin');
        $this->load->model(array('Attendance_model','Shift_model','Leave_model'));
    }

    // ── 1. ฟังก์ชันอัจฉริยะ: ค้นหากะการทำงานที่ถูกต้อง ────────────────
    private function _resolve_shift($uid, $posted_shift_id = null) {
        // 1.1 ถ้าเลือกในฟอร์ม เอาตามที่เลือกก่อนเลย
        if (!empty($posted_shift_id)) {
            $shift = $this->db->where('id', $posted_shift_id)->get('shifts')->row();
            if ($shift) return $shift;
        }

        // 1.2 ถ้าไม่ได้เลือก ลองหากะประจำตัวจากตาราง users (เช็คก่อนว่ามีคอลัมน์นี้ไหม ป้องกันพัง)
        if ($this->db->field_exists('shift_id', 'users')) {
            $this->db->select('shifts.*');
            $this->db->from('users');
            $this->db->join('shifts', 'shifts.id = users.shift_id', 'left');
            $this->db->where('users.id', $uid);
            $shift = $this->db->get()->row();
            if ($shift && !empty($shift->id)) return $shift;
        }

        // 1.3 ถ้ายังหาไม่ได้ ให้หากะล่าสุดที่พนักงานคนนี้เคยลงเวลาไว้ (ประวัติเก่า)
        $last_att = $this->db->where('user_id', $uid)
                             ->where('shift_id IS NOT NULL')
                             ->order_by('date', 'DESC')
                             ->get('attendance')->row();
        if ($last_att) {
            $shift = $this->db->where('id', $last_att->shift_id)->get('shifts')->row();
            if ($shift) return $shift;
        }

        // 1.4 ถ้าหาไม่ได้จริงๆ ดึงกะแรกสุดในระบบมาใช้ (ดีกว่า Hardcode)
        $shift = $this->db->order_by('id', 'ASC')->get('shifts')->row();
        if ($shift) return $shift;

        // 1.5 Fallback สุดท้าย (กรณีไม่มีข้อมูลใน DB เลย)
        return (object)[
            'id'               => null,
            'start_time'       => '08:30:00',
            'end_time'         => '17:30:00',
            'break_start_time' => '12:00:00',
            'break_end_time'   => '13:00:00'
        ];
    }

    // ── 2. ฟังก์ชันคำนวณชั่วโมงลา (อิงตามกะที่หามาได้) ────────────────
    private function _calculate_leave_hours_by_shift($shift, $sh, $eh) {
        if (!$sh || !$eh) return 0;

        // ดักจับกรณีเวลาพักเบรกใน DB ว่างเปล่า
        $b_start_str = !empty($shift->break_start_time) ? $shift->break_start_time : '12:00:00';
        $b_end_str   = !empty($shift->break_end_time)   ? $shift->break_end_time   : '13:00:00';

        $timeToDec = function($timeStr) {
            if (empty($timeStr)) return 0;
            list($h, $m) = explode(':', $timeStr);
            return (int)$h + ((int)$m / 60);
        };

        $s_start = $timeToDec($shift->start_time);
        $s_end   = $timeToDec($shift->end_time);
        $b_start = $timeToDec($b_start_str);
        $b_end   = $timeToDec($b_end_str);
        $req_start = $timeToDec($sh);
        $req_end   = $timeToDec($eh);

        // ดัก AM/PM ผิด หรือกรอกเวลาถอยหลัง
        if ($req_end <= $req_start) return 0;

        // คร็อปเวลาลา ให้อยู่ในกรอบของกะทำงานเท่านั้น
        $workS = max($req_start, $s_start);
        $workE = min($req_end, $s_end);
        
        if ($workS >= $workE) return 0;
        
        $total = $workE - $workS;
        
        // หักเวลาพักเบรก (เฉพาะถ้าช่วงเวลาที่ลา ไปคร่อมเวลาพักของกะนั้นๆ พอดี)
        $overlapBStart = max($workS, $b_start);
        $overlapBEnd   = min($workE, $b_end);
        if ($overlapBStart < $overlapBEnd) {
            $total -= ($overlapBEnd - $overlapBStart);
        }

        return round($total, 2);
    }

    // ── รายการการเข้างานของตัวเอง ──────────────────────────
    public function index() {
        $uid = $this->current_user->user_id;
        $y   = $this->input->get('year')  ?: date('Y');
        $m   = $this->input->get('month') ?: date('n');
        // echo "<PRE>";
        // print_r(array(
        //     'title'      => 'การเข้างานของฉัน',
        //     'page_title' => 'ตารางการเข้างานของฉัน',
        //     'records'    => $this->Attendance_model->get_monthly($uid, $y, $m),
        //     'summary'    => $this->Attendance_model->get_summary($uid, $y, $m),
        //     'today'      => $this->Attendance_model->get_today($uid),
        //     'shifts'     => $this->Shift_model->get_all(),
        //     'leave_types'=> $this->Leave_model->get_types(),
        //     'year'       => $y,
        //     'month'      => $m,
        // ));exit();
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

        $exists = $this->db->where('user_id',$uid)->where('date',$date)->count_all_results('attendance');
        if ($exists > 0) {
            $this->session->set_flashdata('error','มีข้อมูลวันที่ '.$date.' อยู่แล้ว');
            redirect('employee/attendance');
        }

        // 🔥 เรียกใช้ฟังก์ชันอัจฉริยะ ค้นหากะที่ถูกต้องที่สุด
        $posted_shift_id = $this->input->post('shift_id');
        $shift = $this->_resolve_shift($uid, $posted_shift_id);

        $data = array(
            'user_id'        => $uid,
            'shift_id'       => $shift->id, // บังคับบันทึก ID ที่หามาได้ลง DB เสมอ ป้องกันค่า Null
            'date'           => $date,
            'check_in_time'  => $this->input->post('check_in')  ?: null,
            'check_out_time' => $this->input->post('check_out') ?: null,
            'status'         => $status,
            'note'           => $this->input->post('note', TRUE),
            'is_late'        => 0,
            'late_minutes'   => 0,
            'ot_hours'       => 0,
        );

        if ($status === 'leave') {
            $unit = $this->input->post('leave_unit') ?: 'day';
            $data['leave_type_id'] = $this->input->post('leave_type_id') ?: null;
            if ($unit === 'hour') {
                $sh = $this->input->post('leave_start_hour');
                $eh = $this->input->post('leave_end_hour');
                // ส่ง Object $shift ที่แม่นยำไปคำนวณ
                $data['leave_hours'] = $this->_calculate_leave_hours_by_shift($shift, $sh, $eh);
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
                    
                    // หากะตอนแก้ไข โดยอิงจากกะเดิมที่ถูกบันทึกไว้
                    $shift = $this->_resolve_shift($uid, $rec->shift_id);
                    $data['leave_hours'] = $this->_calculate_leave_hours_by_shift($shift, $sh, $eh);
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