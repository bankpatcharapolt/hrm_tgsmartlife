<?php defined('BASEPATH') OR exit('No direct script access allowed');
class Attendance extends Manager_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->model('Attendance_model');
    }

    // หา team_id ของ manager ปัจจุบัน
    private function _my_team_id() {
        $u = $this->db->where('id', $this->current_user->user_id)->get('users')->row();
        return $u ? $u->team_id : null;
    }

    // รายการการเข้างานของทีมตัวเอง
    public function index() {
        $team_id       = $this->_my_team_id();
        $y             = $this->input->get('year')          ?: date('Y');
        $m             = $this->input->get('month')         ?: date('n');
        $uid_filter    = $this->input->get('user_id')       ?: null;
        $status_filter = $this->input->get('status_filter') ?: 'present';

        // ดึงรายชื่อพนักงานในทีม (รวม manager ตัวเอง)
        $team_members = $this->_get_team_members($team_id);

        // ดึงข้อมูลการเข้างาน
        $records = $this->_get_team_attendance($team_id, $y, $m, $uid_filter);

        // ดึง leave_requests (approved+pending) สำหรับ filter=leave หรือ all
        $leave_rows = array();
        if (in_array($status_filter, array('leave', 'all'))) {
            $leave_rows = $this->_get_team_leaves($team_id, $y, $m, $uid_filter);
        }

        // คำนวณวันขาดงาน
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

    // ดึง leave_requests ของทีม รายเดือน
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
            ->join('users u',       'u.id=lr.user_id')
            ->join('leave_types lt','lt.id=lr.leave_type_id', 'left')
            ->join('roles r',       'r.id=u.role_id', 'left')
            ->where('u.status', 'active')
            ->where('r.slug !=', 'admin')
            ->where('r.slug !=', 'owner')
            // คำขอที่ทับกับเดือนนี้
            ->where('lr.start_date <=', $last)
            ->where('lr.end_date >=',   $first)
            ->where_in('lr.status', array('approved', 'pending'));

        if ($team_id) {
            $q->where('u.team_id', $team_id);
        }
        if ($uid_filter) {
            $q->where('lr.user_id', $uid_filter);
        }

        return $q->order_by('lr.start_date', 'DESC')
                 ->order_by('u.first_name', 'ASC')
                 ->get()->result();
    }

    // ดึงพนักงานในทีม
    private function _get_team_members($team_id) {
        $q = $this->db->select('u.id, u.first_name, u.last_name, u.employee_id')
            ->from('users u')
            ->join('roles r', 'r.id=u.role_id', 'left')
            ->where('u.status', 'active')
            ->where('r.slug !=', 'admin')
            ->where('r.slug !=', 'owner');
        if ($team_id) {
            $q->where('u.team_id', $team_id);
        }
        return $q->order_by('u.first_name', 'ASC')->get()->result();
    }

    // ดึงข้อมูลการเข้างานทีม รายเดือน
    private function _get_team_attendance($team_id, $y, $m, $uid_filter = null) {
        $q = $this->db->select(
                'a.*, u.first_name, u.last_name, u.employee_id,
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

        if ($team_id) {
            $q->where('u.team_id', $team_id);
        }
        if ($uid_filter) {
            $q->where('a.user_id', $uid_filter);
        }

        return $q->order_by('a.date', 'DESC')
                 ->order_by('u.first_name', 'ASC')
                 ->limit(500)
                 ->get()->result();
    }
}
