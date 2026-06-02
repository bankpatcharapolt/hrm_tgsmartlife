<?php defined('BASEPATH') OR exit('No direct script access allowed');
class Attendance extends Admin_Controller {
    public function __construct() {
        parent::__construct();
        $this->require_permission('can_monitor_attendance');
        $this->load->model(array('Attendance_model','Shift_model','Leave_model'));
    }
    public function index() {
        $y    = $this->input->get('year')  ?: date('Y');
        $m    = $this->input->get('month') ?: date('n');
        $dept = $this->input->get('dept');
        $sid  = $this->input->get('shift_id');
        $records = $this->Attendance_model->get_all_monthly($y,$m,$dept,$sid);
        $this->render('admin/attendance/index',array(
            'title'       => 'รายงานการเข้างาน',
            'page_title'  => 'รายงานการเข้างาน',
            'records'     => $records,
            'departments' => $this->User_model->get_all_departments(),
            'shifts'      => $this->Shift_model->get_all(),
            'leave_types' => $this->Leave_model->get_types(),
            'year'=>$y, 'month'=>$m, 'dept'=>$dept, 'shift_id'=>$sid,
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
}
