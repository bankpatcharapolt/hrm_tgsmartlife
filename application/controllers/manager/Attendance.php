<?php defined('BASEPATH') OR exit('No direct script access allowed');
class Attendance extends Manager_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->model('Attendance_model');
    }

    private function _my_team_id() {
        $u = $this->db->where('id', $this->current_user->user_id)->get('users')->row();
        return $u ? $u->team_id : null;
    }

    private function _is_my_team_member($user_id) {
        $team_id = $this->_my_team_id();
        if (!$team_id) return true;
        $u = $this->db->where('id', $user_id)->where('team_id', $team_id)->get('users')->row();
        return !empty($u);
    }

    // ── หน้าหลัก ──────────────────────────────────────────────────────────────
    public function index() {
        $team_id       = $this->_my_team_id();
        $y             = $this->input->get('year')          ?: date('Y');
        $m             = $this->input->get('month')         ?: date('n');
        $uid_filter    = $this->input->get('user_id')       ?: null;
        $status_filter = $this->input->get('status_filter') ?: 'present';

        $team_members = $this->_get_team_members($team_id);
        $records      = $this->_get_team_attendance($team_id, $y, $m, $uid_filter);

        $leave_rows = array();
        if (in_array($status_filter, array('leave', 'all'))) {
            $leave_rows = $this->_get_team_leaves($team_id, $y, $m, $uid_filter);
        }

        $absent_map = array();
        $targets = $uid_filter
            ? array_filter($team_members, function($mem) use ($uid_filter) { return $mem->id == $uid_filter; })
            : $team_members;

        foreach ($targets as $member) {
            $days = $this->Attendance_model->get_absent_days($member->id, $y, $m);
            foreach ($days as $d) {
                $absent_map[$d][] = array(
                    'user_id'     => $member->id,
                    'first_name'  => $member->first_name,
                    'last_name'   => $member->last_name,
                    'employee_id' => $member->employee_id,
                );
            }
        }
        ksort($absent_map);

        $this->render('manager/attendance/index', array(
            'title'         => 'การเข้างานทีม',
            'page_title'    => 'การเข้างานของทีม',
            'records'       => $records,
            'leave_rows'    => $leave_rows,
            'absent_map'    => $absent_map,
            'team_members'  => $team_members,
            'year'          => $y,
            'month'         => $m,
            'uid_filter'    => $uid_filter,
            'status_filter' => $status_filter,
            'my_team_id'    => $team_id,
        ));
    }

    // ── อนุมัติ/ปฏิเสธการบันทึกย้อนหลัง ─────────────────────────────────────
    public function approve_attendance($id) {
        $a = $this->db->where('id', $id)->get('attendance')->row();
        if ($a && $this->_is_my_team_member($a->user_id)) {

            // ── คำนวณ OT + is_late + late_minutes ใหม่หลัง approve ──────
            $this->load->model('Shift_model');
            $update = array(
                'approval_status' => 'approved',
                'approved_by'     => $this->current_user->user_id,
                'updated_at'      => date('Y-m-d H:i:s'),
            );

            $shift = $a->shift_id ? $this->Shift_model->get_by_id($a->shift_id) : null;
            if ($shift && $a->check_in_time && $a->check_out_time) {
                // is_late + late_minutes
                $ci_time  = date('H:i:s', strtotime($a->check_in_time));
                $diff_min = (strtotime($ci_time) - strtotime($shift->start_time)) / 60;
                // กะดึก: ถ้า diff เป็น negative มาก (เข้าก่อนข้ามวัน) ให้ปรับ
                if (!empty($shift->is_night_shift) && $diff_min < -360) {
                    $diff_min += 1440;
                }
                $threshold = $shift->late_threshold_minutes ?? 15;
                $update['is_late']      = ($diff_min > $threshold) ? 1 : 0;
                $update['late_minutes'] = $update['is_late'] ? (int)round($diff_min) : 0;

                // OT
                $update['ot_hours'] = $this->Shift_model->calc_ot(
                    $shift,
                    $a->check_out_time,
                    $a->check_in_time
                );
            }

            $this->db->where('id', $id)->update('attendance', $update);

            $ot_msg = (isset($update['ot_hours']) && $update['ot_hours'] > 0)
                    ? ' (OT ' . $update['ot_hours'] . ' ชม.)' : '';

            $this->Notification_model->create(array(
                'user_id'   => $a->user_id,
                'sender_id' => $this->current_user->user_id,
                'type'      => 'attendance_approved',
                'title'     => 'การบันทึกย้อนหลังได้รับการอนุมัติ',
                'message'   => 'การเข้างานวันที่ ' . $a->date . ' อนุมัติแล้ว' . $ot_msg,
                'link'      => base_url('employee/attendance'),
            ));
            $this->session->set_flashdata('success', 'อนุมัติสำเร็จ' . $ot_msg);
        } else {
            $this->session->set_flashdata('error', 'ไม่มีสิทธิ์อนุมัติ');
        }
        redirect('manager/attendance?status_filter=all');
    }

    public function reject_attendance($id) {
        $a    = $this->db->where('id', $id)->get('attendance')->row();
        $note = $this->input->post('note', TRUE);
        if ($a && $this->_is_my_team_member($a->user_id)) {
            $this->db->where('id', $id)->update('attendance', array(
                'approval_status' => 'rejected',
                'approved_by'     => $this->current_user->user_id,
                'note'            => ($a->note ? $a->note . ' | ' : '') . ($note ?: 'ถูกปฏิเสธ'),
                'updated_at'      => date('Y-m-d H:i:s'),
            ));
            $this->Notification_model->create(array(
                'user_id'   => $a->user_id,
                'sender_id' => $this->current_user->user_id,
                'type'      => 'attendance_rejected',
                'title'     => 'การบันทึกย้อนหลังถูกปฏิเสธ',
                'message'   => 'การเข้างานวันที่ ' . $a->date . ' ถูกปฏิเสธ' . ($note ? ': ' . $note : ''),
                'link'      => base_url('employee/attendance'),
            ));
            $this->session->set_flashdata('warning', 'ปฏิเสธสำเร็จ');
        }
        redirect('manager/attendance?status_filter=all');
    }

    // ── อนุมัติ/ปฏิเสธการลา (shortcut จาก attendance view) ───────────────────
    public function approve_leave($id) {
        $this->load->model('Leave_model');
        $r = $this->Leave_model->get_by_id($id);
        if ($r && $this->_is_my_team_member($r->user_id)) {
            $this->Leave_model->approve($id, $this->current_user->user_id, '');
            $this->Notification_model->create(array(
                'user_id'   => $r->user_id,
                'sender_id' => $this->current_user->user_id,
                'type'      => 'leave_approved',
                'title'     => 'คำขอลาได้รับการอนุมัติ',
                'message'   => 'การลาวันที่ ' . $r->start_date . ' อนุมัติแล้ว',
                'link'      => base_url('employee/leave'),
            ));
            $this->session->set_flashdata('success', 'อนุมัติการลาสำเร็จ');
        }
        redirect('manager/attendance?status_filter=leave');
    }

    public function reject_leave($id) {
        $this->load->model('Leave_model');
        $r    = $this->Leave_model->get_by_id($id);
        $note = $this->input->post('note', TRUE) ?: 'ถูกปฏิเสธ';
        if ($r && $this->_is_my_team_member($r->user_id)) {
            $this->Leave_model->reject($id, $this->current_user->user_id, $note);
            $this->Notification_model->create(array(
                'user_id'   => $r->user_id,
                'sender_id' => $this->current_user->user_id,
                'type'      => 'leave_rejected',
                'title'     => 'คำขอลาถูกปฏิเสธ',
                'message'   => 'การลาวันที่ ' . $r->start_date . ' ถูกปฏิเสธ: ' . $note,
                'link'      => base_url('employee/leave'),
            ));
            $this->session->set_flashdata('warning', 'ปฏิเสธการลาสำเร็จ');
        }
        redirect('manager/attendance?status_filter=leave');
    }

    // ── Private query methods ──────────────────────────────────────────────────
    private function _get_team_members($team_id) {
        $q = $this->db->select('u.id, u.first_name, u.last_name, u.employee_id')
            ->from('users u')
            ->join('roles r', 'r.id=u.role_id', 'left')
            ->where('u.status', 'active')
            ->where('r.slug !=', 'admin')
            ->where('r.slug !=', 'owner');
        if ($team_id) $q->where('u.team_id', $team_id);
        return $q->order_by('u.first_name', 'ASC')->get()->result();
    }

    private function _get_team_attendance($team_id, $y, $m, $uid_filter = null) {
        $q = $this->db->select(
                'a.id, a.user_id, a.date, a.check_in_time, a.check_out_time,
                 a.status, a.is_late, a.late_minutes, a.ot_hours,
                 a.note, a.is_manual, a.approval_status,
                 a.shift_id, a.leave_hours,
                 u.first_name, u.last_name, u.employee_id,
                 s.name AS shift_name, s.color AS shift_color'
            )
            ->from('attendance a')
            ->join('users u', 'u.id=a.user_id')
            ->join('roles r', 'r.id=u.role_id', 'left')
            ->join('shifts s', 's.id=a.shift_id', 'left')
            ->where('u.status', 'active')
            ->where('r.slug !=', 'admin')
            ->where('r.slug !=', 'owner')
            ->where('YEAR(a.date)', $y)
            ->where('MONTH(a.date)', $m);
        if ($team_id)    $q->where('u.team_id', $team_id);
        if ($uid_filter) $q->where('a.user_id', $uid_filter);
        return $q->order_by('a.date', 'DESC')->order_by('u.first_name', 'ASC')->limit(2000)->get()->result();
    }

    private function _get_team_leaves($team_id, $y, $m, $uid_filter = null) {
        $first = sprintf('%04d-%02d-01', $y, $m);
        $last  = date('Y-m-t', strtotime($first));
        $q = $this->db->select(
                'lr.id, lr.user_id, lr.start_date, lr.end_date,
                 lr.total_days, lr.leave_unit, lr.reason, lr.status AS leave_status,
                 lr.total_hours, lr.start_time, lr.end_time,
                 lt.name AS leave_type_name,
                 u.first_name, u.last_name, u.employee_id'
            )
            ->from('leave_requests lr')
            ->join('users u',        'u.id=lr.user_id')
            ->join('leave_types lt', 'lt.id=lr.leave_type_id', 'left')
            ->join('roles r',        'r.id=u.role_id', 'left')
            ->where('u.status', 'active')
            ->where('r.slug !=', 'admin')
            ->where('r.slug !=', 'owner')
            ->where('lr.start_date <=', $last)
            ->where('lr.end_date >=',   $first)
            ->where_in('lr.status', array('approved', 'pending'));
        if ($team_id)    $q->where('u.team_id', $team_id);
        if ($uid_filter) $q->where('lr.user_id', $uid_filter);
        return $q->order_by('lr.start_date', 'DESC')->order_by('u.first_name', 'ASC')->get()->result();
    }
}
