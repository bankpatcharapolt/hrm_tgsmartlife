<?php defined('BASEPATH') OR exit('No direct script access allowed');
class Attendance extends Admin_Controller {
    public function __construct() {
        parent::__construct();
        $this->require_permission('can_monitor_attendance');
        $this->load->model(array('Attendance_model','Shift_model','Leave_model'));
    }
    public function index() {
        $y          = (int)($this->input->get('year')  ?: date('Y'));
        $m          = (int)($this->input->get('month') ?: date('n'));
        $dept       = $this->input->get('dept');
        $sid        = $this->input->get('shift_id');
        $sel_status = $this->input->get('status') ?: '';
        $per_page   = 50;
        $page       = max(1, (int)($this->input->get('page') ?: 1));
        $offset     = ($page - 1) * $per_page;

        // ── กรณีพิเศษ: ขาดงาน ──────────────────────────────────────
        if ($sel_status === 'absent') {
            $user_filters = array('status' => 'active');
            if ($dept) $user_filters['department_id'] = $dept;
            $all_users = $this->User_model->get_all($user_filters, 500);

            $all_absent = array();
            foreach ($all_users as $u) {
                $absent_days = $this->Attendance_model->get_absent_days($u->id, $y, $m);
                foreach ($absent_days as $date) {
                    $row = new stdClass();
                    $row->date            = $date;
                    $row->user_id         = $u->id;
                    $row->first_name      = $u->first_name;
                    $row->last_name       = $u->last_name;
                    $row->employee_id     = $u->employee_id;
                    $row->dept_name       = isset($u->dept_name) ? $u->dept_name : '–';
                    $row->shift_name      = null;
                    $row->shift_color     = null;
                    $row->check_in_time   = null;
                    $row->check_out_time  = null;
                    $row->status          = 'absent';
                    $row->is_late         = 0;
                    $row->late_minutes    = 0;
                    $row->leave_hours     = 0;
                    $row->ot_hours        = 0;
                    $row->leave_type_name = null;
                    $row->note            = '';
                    $row->id              = null;
                    $all_absent[] = $row;
                }
            }
            usort($all_absent, function($a, $b){ return strcmp($b->date, $a->date); });
            $total   = count($all_absent);
            $records = array_slice($all_absent, $offset, $per_page);
        } else {
            $total   = $this->Attendance_model->count_all_monthly_filtered($y,$m,$dept,$sid,$sel_status);
            $records = $this->Attendance_model->get_all_monthly($y,$m,$dept,$sid,$sel_status,$per_page,$offset);
        }

        $total_pages = $per_page > 0 ? (int)ceil($total / $per_page) : 1;

        $this->render('admin/attendance/index', array(
            'title'       => 'รายงานการเข้างาน',
            'page_title'  => 'รายงานการเข้างาน',
            'records'     => $records,
            'departments' => $this->User_model->get_all_departments(),
            'shifts'      => $this->Shift_model->get_all(),
            'leave_types' => $this->Leave_model->get_types(),
            'year'        => $y,
            'month'       => $m,
            'dept'        => $dept,
            'shift_id'    => $sid,
            'sel_status'  => $sel_status,
            'total'       => $total,
            'page'        => $page,
            'per_page'    => $per_page,
            'total_pages' => $total_pages,
        ));
    }
    // บันทึกด้วยตนเอง (รองรับลาชั่วโมง)
    public function manual() {
        if ($this->input->method()==='post') {
            $status = $this->input->post('status') ?: 'present';
            $data = array(
                'user_id'        => $this->input->post('user_id'),
                'shift_id'       => $this->input->post('shift_id') ?: null,
                'date'           => $this->input->post('date'),
                'check_in_time'  => $this->input->post('check_in')  ?: null,
                'check_out_time' => $this->input->post('check_out') ?: null,
                'status'         => $status,
                'note'           => $this->input->post('note',TRUE),
                'is_late'        => 0,
                'late_minutes'   => 0,
                'ot_hours'       => (float)$this->input->post('ot_hours'),
            );
            // ลาชั่วโมง
            if ($status === 'leave') {
                $leave_unit = $this->input->post('leave_unit') ?: 'day';
                $data['leave_type_id'] = $this->input->post('leave_type_id') ?: null;
                if ($leave_unit === 'hour') {
                    $sh = $this->input->post('leave_start_hour');
                    $eh = $this->input->post('leave_end_hour');
                    if ($sh && $eh) {
                        $data['leave_hours'] = round((strtotime($eh) - strtotime($sh)) / 3600, 2);
                        $data['check_in_time']  = $data['date'].' '.$sh.':00';
                        $data['check_out_time'] = $data['date'].' '.$eh.':00';
                    }
                }
            }
            $id = $this->Attendance_model->manual_add($data);
            if ($id) {
                $this->User_model->log($this->current_user->user_id,'manual_attendance','attendance','เพิ่มการเข้างาน ID:'.$id);
                $this->session->set_flashdata('success','บันทึกสำเร็จ');
            } else {
                $this->session->set_flashdata('error','เกิดข้อผิดพลาด หรือมีข้อมูลวันนี้แล้ว');
            }
            redirect('admin/attendance');
        }
        $this->render('admin/attendance/manual',array(
            'title'       => 'บันทึกการเข้างาน',
            'page_title'  => 'บันทึกการเข้างานด้วยตนเอง',
            'employees'   => $this->User_model->get_all(array('status'=>'active'),300),
            'shifts'      => $this->Shift_model->get_all(),
            'leave_types' => $this->Leave_model->get_types(),
        ));
    }
    // แก้ไขรายการ
    public function edit($id) {
        $rec = $this->Attendance_model->get_by_id($id);
        if (!$rec) { $this->session->set_flashdata('error','ไม่พบข้อมูล'); redirect('admin/attendance'); }
        if ($this->input->method()==='post') {
            $status = $this->input->post('status') ?: 'present';
            $data = array(
                'shift_id'       => $this->input->post('shift_id') ?: null,
                'check_in_time'  => $this->input->post('check_in')  ?: null,
                'check_out_time' => $this->input->post('check_out') ?: null,
                'status'         => $status,
                'note'           => $this->input->post('note',TRUE),
                'ot_hours'       => (float)$this->input->post('ot_hours'),
            );
            if ($status === 'leave') {
                $leave_unit = $this->input->post('leave_unit') ?: 'day';
                $data['leave_type_id'] = $this->input->post('leave_type_id') ?: null;
                if ($leave_unit === 'hour') {
                    $sh = $this->input->post('leave_start_hour');
                    $eh = $this->input->post('leave_end_hour');
                    if ($sh && $eh) {
                        $data['leave_hours'] = round((strtotime($eh) - strtotime($sh)) / 3600, 2);
                    }
                } else {
                    $data['leave_hours'] = 0;
                }
            }
            if ($this->Attendance_model->update_record($id, $data, $this->current_user->user_id)) {
                $this->User_model->log($this->current_user->user_id,'edit_attendance','attendance','แก้ไข ID:'.$id);
                $this->session->set_flashdata('success','แก้ไขสำเร็จ');
                redirect('admin/attendance');
            }
            $this->session->set_flashdata('error','เกิดข้อผิดพลาด');
        }
        $this->render('admin/attendance/edit',array(
            'title'       => 'แก้ไขการเข้างาน',
            'page_title'  => 'แก้ไขการเข้างาน',
            'rec'         => $rec,
            'shifts'      => $this->Shift_model->get_all(),
            'leave_types' => $this->Leave_model->get_types(),
        ));
    }
    // ลบรายการ
    public function delete($id) {
        $rec = $this->Attendance_model->get_by_id($id);
        if ($rec) {
            $this->Attendance_model->delete_record($id);
            $this->User_model->log($this->current_user->user_id,'delete_attendance','attendance','ลบ ID:'.$id.' ของ '.$rec->first_name.' '.$rec->last_name.' วันที่ '.$rec->date);
            $this->session->set_flashdata('success','ลบรายการสำเร็จ');
        }
        redirect('admin/attendance');
    }
    // ตั้งค่ากะ
    public function shifts() {
        $this->render('admin/attendance/shifts',array(
            'title'      => 'ตั้งค่ากะการทำงาน',
            'page_title' => 'จัดการกะการทำงาน',
            'shifts'     => $this->Shift_model->get_all(false),
        ));
    }
    public function store_shift() {
        if ($this->input->method()!=='post') redirect('admin/attendance/shifts');
        $data = array(
            'name'                    => $this->input->post('name',TRUE),
            'start_time'              => $this->input->post('start_time'),
            'end_time'                => $this->input->post('end_time'),
            'break_minutes'           => (int)$this->input->post('break_minutes'),
            'late_threshold_minutes'  => (int)$this->input->post('late_threshold_minutes'),
            'ot_starts_after_minutes' => (int)$this->input->post('ot_starts_after_minutes'),
            'is_night_shift'          => $this->input->post('is_night_shift') ? 1 : 0,
            'color'                   => $this->input->post('color') ?: '#1a56db',
            'status'                  => 'active',
        );
        $id = $this->input->post('shift_id');
        if ($id) {
            $this->Shift_model->update($id,$data);
            $this->session->set_flashdata('success','อัปเดตกะสำเร็จ');
        } else {
            $this->Shift_model->create($data);
            $this->session->set_flashdata('success','เพิ่มกะสำเร็จ');
        }
        redirect('admin/attendance/shifts');
    }
    public function delete_shift($id) {
        $this->Shift_model->delete($id);
        $this->session->set_flashdata('success','ลบกะสำเร็จ');
        redirect('admin/attendance/shifts');
    }
    // กำหนดกะให้พนักงาน
    public function assign_shift() {
        if ($this->input->method()==='post') {
            $uid = $this->input->post('user_id');
            $sid = $this->input->post('shift_id') ?: null;
            $this->User_model->update($uid, array('shift_id'=>$sid));
            $this->session->set_flashdata('success','กำหนดกะสำเร็จ');
            redirect('admin/attendance/shifts');
        }
        redirect('admin/attendance/shifts');
    }

    // ── อนุมัติการบันทึกย้อนหลัง ──────────────────────────────────
    public function approve_manual($id) {
        $att = $this->db->where('id',$id)->where('is_manual',1)->get('attendance')->row();
        if (!$att) { $this->session->set_flashdata('error','ไม่พบรายการ'); redirect('admin/attendance'); }

        $this->db->where('id',$id)->update('attendance', array(
            'approval_status' => 'approved',
            'approved_by'     => $this->current_user->user_id,
            'approved_at'     => date('Y-m-d H:i:s'),
        ));

        // แจ้งเตือนพนักงาน
        $this->load->model('Notification_model');
        $this->Notification_model->create(array(
            'user_id'   => $att->user_id,
            'sender_id' => $this->current_user->user_id,
            'type'      => 'attendance_approved',
            'title'     => 'คำขอบันทึกย้อนหลังได้รับการอนุมัติ',
            'message'   => 'การบันทึกย้อนหลังวันที่ ' . date('d/m/Y', strtotime($att->date)) . ' ได้รับการอนุมัติแล้ว',
            'link'      => base_url('employee/attendance'),
        ));
        $this->session->set_flashdata('success', 'อนุมัติสำเร็จ');
        redirect('admin/attendance');
    }

    // ── ปฏิเสธการบันทึกย้อนหลัง ───────────────────────────────────
    public function reject_manual($id) {
        if ($this->input->method() !== 'post') redirect('admin/attendance');
        $att = $this->db->where('id',$id)->where('is_manual',1)->get('attendance')->row();
        if (!$att) { $this->session->set_flashdata('error','ไม่พบรายการ'); redirect('admin/attendance'); }

        $reason = $this->input->post('reason', TRUE) ?: 'ไม่ระบุ';
        $this->db->where('id',$id)->update('attendance', array(
            'approval_status' => 'rejected',
            'approved_by'     => $this->current_user->user_id,
            'approved_at'     => date('Y-m-d H:i:s'),
            'reject_reason'   => $reason,
        ));

        // แจ้งเตือนพนักงาน
        $this->load->model('Notification_model');
        $this->Notification_model->create(array(
            'user_id'   => $att->user_id,
            'sender_id' => $this->current_user->user_id,
            'type'      => 'attendance_rejected',
            'title'     => 'คำขอบันทึกย้อนหลังถูกปฏิเสธ',
            'message'   => 'การบันทึกย้อนหลังวันที่ ' . date('d/m/Y', strtotime($att->date)) . ' ถูกปฏิเสธ: ' . $reason,
            'link'      => base_url('employee/attendance'),
        ));
        $this->session->set_flashdata('info', 'ปฏิเสธคำขอแล้ว');
        redirect('admin/attendance');
    }
}
