<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Leave extends Employee_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->model('Leave_model');
    }

    // ── ฟังก์ชันตัวช่วยดึงข้อมูลกะและเวลาพัก ──────────────────
    private function _get_user_shift($uid) {
        $this->db->select('shifts.*');
        $this->db->from('users');
        $this->db->join('shifts', 'shifts.id = users.shift_id', 'left');
        $this->db->where('users.id', $uid);
        $shift = $this->db->get()->row();

        // ถ้าไม่มีกะ หรือใน DB ไม่มีคอลัมน์เบรก ให้ตั้ง Default เป็น 08.30-17.30 (พัก 12.30-13.30)
        if (!$shift || empty($shift->start_time)) {
            $shift = (object)[
                'start_time'       => '08:30:00',
                'end_time'         => '17:30:00',
                'break_start_time' => '12:30:00',
                'break_end_time'   => '13:30:00'
            ];
        } else {
            if (empty($shift->break_start_time)) $shift->break_start_time = '12:30:00';
            if (empty($shift->break_end_time)) $shift->break_end_time = '13:30:00';
        }
        return $shift;
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
        $uid = $this->current_user->user_id;
        $shift_data = $this->_get_user_shift($uid); // ดึงค่าจาก DB

        $this->render('employee/leave/request', array(
            'title'       => 'ยื่นคำขอลา',
            'page_title'  => 'ยื่นคำขอลา',
            'leave_types' => $this->Leave_model->get_types(),
            'shift'       => $shift_data, // ส่งไปให้ JS
        ));
    }

    public function store() {
        if ($this->input->method() !== 'post') redirect('employee/leave');
        $uid  = $this->current_user->user_id;
        $sd   = $this->input->post('start_date');
        $ed   = $this->input->post('end_date');
        $unit = $this->input->post('leave_unit') ?: 'day';

        $shift = $this->_get_user_shift($uid); // ดึงค่าจาก DB มาคำนวณ

        $days  = 0;
        $hours = 0;
        $sh    = null;
        $eh    = null;

        if ($unit === 'day') {
            $days = max(1, round((strtotime($ed)-strtotime($sd))/86400)+1);
        } else if ($unit === 'hour') {
            $sh = $this->input->post('leave_start_time');
            $eh = $this->input->post('leave_end_time');
            
            if ($sh && $eh) {
                $timeToDec = function($timeStr) {
                    if (empty($timeStr)) return 0;
                    list($h, $m) = explode(':', $timeStr);
                    return (int)$h + ((int)$m / 60);
                };

                $s_start = $timeToDec($shift->start_time);
                $s_end   = $timeToDec($shift->end_time);
                $b_start = $timeToDec($shift->break_start_time);
                $b_end   = $timeToDec($shift->break_end_time);
                
                $req_start = $timeToDec($sh);
                $req_end   = $timeToDec($eh);
                
                $days_diff = round((strtotime($ed) - strtotime($sd)) / 86400);
                
                $calcHoursForDay = function($reqS, $reqE) use ($s_start, $s_end, $b_start, $b_end) {
                    $workS = max($reqS, $s_start);
                    $workE = min($reqE, $s_end);
                    if ($workS >= $workE) return 0;
                    
                    $total = $workE - $workS;
                    $overlapBStart = max($workS, $b_start);
                    $overlapBEnd   = min($workE, $b_end);
                    
                    if ($overlapBStart < $overlapBEnd) {
                        $total -= ($overlapBEnd - $overlapBStart);
                    }
                    return $total;
                };

                $full_day_hours = $calcHoursForDay($s_start, $s_end);
                if ($full_day_hours <= 0) $full_day_hours = 8;

                if ($days_diff == 0) {
                    $hours = $calcHoursForDay($req_start, $req_end);
                } else {
                    $first_day   = $calcHoursForDay($req_start, $s_end);
                    $last_day    = $calcHoursForDay($s_start, $req_end);
                    $middle_days = max(0, $days_diff - 1) * $full_day_hours;
                    $hours       = $first_day + $last_day + $middle_days;
                }

                $hours = round($hours, 2);
                $days  = round($hours / $full_day_hours, 2);
            }
        }

        $data = array(
            'user_id'       => $uid,
            'leave_type_id' => $this->input->post('leave_type_id'),
            'start_date'    => $sd,
            'end_date'      => $ed,
            'total_days'    => $days,
            'leave_unit'    => $unit,
            'reason'        => $this->input->post('reason', TRUE),
            'status'        => 'pending',
            'total_hours'   => $hours,
            'start_time'    => $sh,
            'end_time'      => $eh,
        );

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

        // [ข้อ 2] ใบรับรองแพทย์ (เฉพาะลาป่วย)
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
        if ($req->status !== 'pending') {
            $this->session->set_flashdata('error', 'แก้ไขได้เฉพาะคำขอที่รอการอนุมัติเท่านั้น');
            redirect('employee/leave');
        }

        if ($this->input->method() === 'post') {
            $sd   = $this->input->post('start_date');
            $ed   = $this->input->post('end_date');
            $unit = $this->input->post('leave_unit') ?: 'day';

            $shift = $this->_get_user_shift($uid); // ดึงค่าจาก DB

            $days  = 0;
            $hours = 0;
            $sh    = null;
            $eh    = null;

            if ($unit === 'day') {
                $days = max(1, round((strtotime($ed)-strtotime($sd))/86400)+1);
            } else if ($unit === 'hour') {
                $sh = $this->input->post('leave_start_time');
                $eh = $this->input->post('leave_end_time');
                
                if ($sh && $eh) {
                    $timeToDec = function($timeStr) {
                        if (empty($timeStr)) return 0;
                        list($h, $m) = explode(':', $timeStr);
                        return (int)$h + ((int)$m / 60);
                    };

                    $s_start = $timeToDec($shift->start_time);
                    $s_end   = $timeToDec($shift->end_time);
                    $b_start = $timeToDec($shift->break_start_time);
                    $b_end   = $timeToDec($shift->break_end_time);
                    
                    $req_start = $timeToDec($sh);
                    $req_end   = $timeToDec($eh);
                    
                    $days_diff = round((strtotime($ed) - strtotime($sd)) / 86400);
                    
                    $calcHoursForDay = function($reqS, $reqE) use ($s_start, $s_end, $b_start, $b_end) {
                        $workS = max($reqS, $s_start);
                        $workE = min($reqE, $s_end);
                        if ($workS >= $workE) return 0;
                        
                        $total = $workE - $workS;
                        $overlapBStart = max($workS, $b_start);
                        $overlapBEnd   = min($workE, $b_end);
                        
                        if ($overlapBStart < $overlapBEnd) {
                            $total -= ($overlapBEnd - $overlapBStart);
                        }
                        return $total;
                    };

                    $full_day_hours = $calcHoursForDay($s_start, $s_end);
                    if ($full_day_hours <= 0) $full_day_hours = 8;

                    if ($days_diff == 0) {
                        $hours = $calcHoursForDay($req_start, $req_end);
                    } else {
                        $first_day   = $calcHoursForDay($req_start, $s_end);
                        $last_day    = $calcHoursForDay($s_start, $req_end);
                        $middle_days = max(0, $days_diff - 1) * $full_day_hours;
                        $hours       = $first_day + $last_day + $middle_days;
                    }

                    $hours = round($hours, 2);
                    $days  = round($hours / $full_day_hours, 2);
                }
            }

            $data = array(
                'leave_type_id' => $this->input->post('leave_type_id'),
                'start_date'    => $sd,
                'end_date'      => $ed,
                'total_days'    => $days,
                'leave_unit'    => $unit,
                'reason'        => $this->input->post('reason', TRUE),
                'total_hours'   => $hours,
                'start_time'    => $sh,
                'end_time'      => $eh,
                'updated_at'    => date('Y-m-d H:i:s'),
            );

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

            // [ข้อ 2] ใบรับรองแพทย์
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