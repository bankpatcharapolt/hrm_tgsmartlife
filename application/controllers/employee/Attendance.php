<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Attendance extends Employee_Controller {

    public function __construct() {
        parent::__construct();
        $this->require_permission('can_checkin');
        $this->load->model(array('Attendance_model','Shift_model','Leave_model'));
    }

    // ── 1. ฟังก์ชันอัจฉริยะ: ค้นหากะการทำงานที่ถูกต้อง ────────────────
    private function _resolve_shift($uid, $posted_shift_id = null) {
        if (!empty($posted_shift_id)) {
            $shift = $this->db->where('id', $posted_shift_id)->get('shifts')->row();
            if ($shift) return $shift;
        }
        if ($this->db->field_exists('shift_id', 'users')) {
            $this->db->select('shifts.*');
            $this->db->from('users');
            $this->db->join('shifts', 'shifts.id = users.shift_id', 'left');
            $this->db->where('users.id', $uid);
            $shift = $this->db->get()->row();
            if ($shift && !empty($shift->id)) return $shift;
        }
        $last_att = $this->db->where('user_id', $uid)
                             ->where('shift_id IS NOT NULL')
                             ->order_by('date', 'DESC')
                             ->get('attendance')->row();
        if ($last_att) {
            $shift = $this->db->where('id', $last_att->shift_id)->get('shifts')->row();
            if ($shift) return $shift;
        }
        $shift = $this->db->order_by('id', 'ASC')->get('shifts')->row();
        if ($shift) return $shift;
        return (object)[
            'id'               => null,
            'start_time'       => '08:30:00',
            'end_time'         => '17:30:00',
            'break_start_time' => '12:00:00',
            'break_end_time'   => '13:00:00'
        ];
    }

    // ── 2. คำนวณชั่วโมงลา ─────────────────────────────────────────────
    private function _calculate_leave_hours_by_shift($shift, $sh, $eh) {
        if (!$sh || !$eh) return 0;
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
        if ($req_end <= $req_start) return 0;
        $workS = max($req_start, $s_start);
        $workE = min($req_end, $s_end);
        if ($workS >= $workE) return 0;
        $total = $workE - $workS;
        $overlapBStart = max($workS, $b_start);
        $overlapBEnd   = min($workE, $b_end);
        if ($overlapBStart < $overlapBEnd) {
            $total -= ($overlapBEnd - $overlapBStart);
        }
        return round($total, 2);
    }

    // ── [ข้อ 7] หากะของ user เพื่อ pre-select ─────────────────────────
    private function _get_user_default_shift($uid) {
        if ($this->db->field_exists('shift_id', 'users')) {
            $user = $this->db->where('id', $uid)->get('users')->row();
            if ($user && !empty($user->shift_id)) return $user->shift_id;
        }
        $last = $this->db->where('user_id', $uid)
            ->where('shift_id IS NOT NULL')
            ->order_by('date','DESC')
            ->get('attendance')->row();
        return $last ? $last->shift_id : null;
    }

    // ── รายการการเข้างานของตัวเอง ──────────────────────────────────────
    public function index() {
        $uid = $this->current_user->user_id;
        $y   = $this->input->get('year')  ?: date('Y');
        $m   = $this->input->get('month') ?: date('n');

        // [ข้อ 3] คำนวณวันขาดงาน
        $absent_days = $this->Attendance_model->get_absent_days($uid, $y, $m);

        $this->render('employee/attendance/index', array(
            'title'           => 'การเข้างานของฉัน',
            'page_title'      => 'ตารางการเข้างานของฉัน',
            'records'         => $this->Attendance_model->get_monthly($uid, $y, $m),
            'summary'         => $this->Attendance_model->get_summary($uid, $y, $m),
            'today'           => $this->Attendance_model->get_today($uid),
            'shifts'          => $this->Shift_model->get_all(),
            'leave_types'     => $this->Leave_model->get_types(),
            'year'            => $y,
            'month'           => $m,
            'absent_days'     => $absent_days,           // [ข้อ 3] ส่งวันขาดงาน
            'default_shift_id'=> $this->_get_user_default_shift($uid), // [ข้อ 7]
        ));
    }

    // ── เพิ่มรายการด้วยตนเอง (ลงย้อนหลัง) ────────────────────────────
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

        // [ข้อ 7] ใช้ shift_id จากฟอร์ม (locked = shift ของ user)
        $posted_shift_id = $this->input->post('shift_id');
        $shift = $this->_resolve_shift($uid, $posted_shift_id);

        $data = array(
            'user_id'        => $uid,
            'shift_id'       => $shift->id,
            'date'           => $date,
            'check_in_time'  => $this->_normalize_datetime($this->input->post('check_in')),
            'check_out_time' => $this->_normalize_datetime($this->input->post('check_out')),
            'status'         => $status,
            'note'           => $this->input->post('note', TRUE),
            'is_late'        => 0,
            'late_minutes'   => 0,
            'ot_hours'       => 0,
        );

        // [ข้อ 7] ถ้าสถานะ = half_day บันทึก work_hours
        if ($status === 'half_day') {
            $data['work_hours'] = (float)($this->input->post('half_day_hours') ?: 4);
        }

        // [ข้อ 7] ถ้าสถานะ = hourly บันทึก work_hours จากเวลาเข้า-ออก
        if ($status === 'hourly') {
            $ci = $this->input->post('check_in');
            $co = $this->input->post('check_out');
            if ($ci && $co) {
                $diff_min = (strtotime($co) - strtotime($ci)) / 60;
                $data['work_hours'] = round($diff_min / 60, 2);
            }
            $data['status'] = 'present'; // บันทึกเป็น present แต่มี work_hours
        }

        if ($status === 'leave') {
            $unit = $this->input->post('leave_unit') ?: 'day';
            $data['leave_type_id'] = $this->input->post('leave_type_id') ?: null;
            if ($unit === 'hour') {
                $sh = $this->input->post('leave_start_hour');
                $eh = $this->input->post('leave_end_hour');
                $data['leave_hours'] = $this->_calculate_leave_hours_by_shift($shift, $sh, $eh);
            }
        }

        // [อนุมัติ] ลงย้อนหลัง = รอการอนุมัติ
        $data['is_manual']       = 1;
        $data['approval_status'] = 'pending';

        $att_id = $this->Attendance_model->manual_add($data);

        // แจ้งเตือนเฉพาะหัวหน้าทีมเดียวกัน + admin + owner
        $this->load->model('Notification_model');
        $uid      = $this->current_user->user_id;
        $emp      = $this->db->where('id', $uid)->get('users')->row();
        $emp_name = $emp ? ($emp->first_name . ' ' . $emp->last_name) : 'พนักงาน';
        $msg      = $emp_name . ' ขอบันทึกการเข้างานย้อนหลัง วันที่ ' . $date . ' รอการอนุมัติ';
        $this->Notification_model->send_to_team_manager(
            $uid,
            'manual_attendance',
            'ขอบันทึกย้อนหลัง',
            $msg,
            base_url('manager/attendance'),   // manager → หน้าการเข้างานทีม
            base_url('admin/attendance')       // admin/owner → หน้า admin
        );

        $this->session->set_flashdata('success', 'ส่งคำขอบันทึกย้อนหลังสำเร็จ รอการอนุมัติจากหัวหน้างาน');
        redirect('employee/attendance');
    }

    // ── แก้ไข ─────────────────────────────────────────────────────────
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

    // ── ลบ ─────────────────────────────────────────────────────────────
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

    /**
     * แปลงวันที่ทุกรูปแบบ → Y-m-d H:i:s สำหรับ MySQL
     * รองรับ: dd/mm/yyyy HH:MM, dd/mm/yyyy, Y-m-d H:i:s, Y-m-d, Y-m-dTH:i
     */
    private function _normalize_datetime($val) {
        if (empty($val)) return null;
        $val = trim($val);
        // dd/mm/yyyy HH:MM
        if (preg_match('/^(\d{2})\/(\d{2})\/(\d{4})\s+(\d{2}):(\d{2})$/', $val, $m)) {
            return "{$m[3]}-{$m[2]}-{$m[1]} {$m[4]}:{$m[5]}:00";
        }
        // dd/mm/yyyy
        if (preg_match('/^(\d{2})\/(\d{2})\/(\d{4})$/', $val, $m)) {
            return "{$m[3]}-{$m[2]}-{$m[1]}";
        }
        // Y-m-dTH:i หรือ Y-m-d H:i หรือ Y-m-d
        $ts = strtotime($val);
        if ($ts) return date('Y-m-d H:i:s', $ts);
        return null;
    }

}
