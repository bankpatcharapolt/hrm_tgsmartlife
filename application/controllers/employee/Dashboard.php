<?php defined('BASEPATH') OR exit('No direct script access allowed');
class Dashboard extends Employee_Controller {
   public function __construct() {
    parent::__construct();
    $this->load->model(array('Attendance_model', 'Leave_model', 'Salary_model', 'Notification_model'));
}
    public function index() {
        $uid = $this->current_user->user_id;

        // ดึง team lat/lng/radius ของพนักงานคนนี้
        // ป้องกัน error กรณียังไม่ได้รัน ALTER TABLE เพิ่ม column
        $user_team = null;
        $has_geo   = $this->db->field_exists('lat', 'teams');
        if ($has_geo) {
            $user_team = $this->db
                ->select('t.lat, t.lng, t.checkin_radius_km')
                ->from('users u')
                ->join('teams t', 't.id = u.team_id', 'left')
                ->where('u.id', $uid)
                ->get()->row();
        }

        $today = $this->Attendance_model->get_today($uid);

        // [เพิ่ม] เวลาสิ้นสุดกะของวันนี้ — ใช้ฝั่ง JS เพื่อเตือน/บังคับกรอกเหตุผลถ้าจะออกก่อนเวลา
        $shift_end_time = null;
        if ($today && $today->shift_id) {
            $this->load->model('Shift_model');
            $shift = $this->Shift_model->get_by_id($today->shift_id);
            if ($shift && !empty($shift->end_time)) $shift_end_time = $shift->end_time;
        }

        $this->render('employee/dashboard/index', array(
            'title'      => 'หน้าหลัก',
            'page_title' => 'หน้าหลัก',
            'today'      => $today,
            'att_sum'    => $this->Attendance_model->get_summary($uid, date('Y'), date('n')),
            'leaves'     => $this->Leave_model->get_requests(array('user_id'=>$uid,'status'=>'pending'), 5),
            'salaries'   => $this->Salary_model->get_records(array('user_id'=>$uid), 3),
            'notifs'     => $this->Notification_model->get_all($uid, 5),
            'team_lat'         => ($user_team && $user_team->lat)  ? (float)$user_team->lat  : null,
            'team_lng'         => ($user_team && $user_team->lng)  ? (float)$user_team->lng  : null,
            'team_radius_km'   => ($user_team && $user_team->checkin_radius_km) ? (float)$user_team->checkin_radius_km : null,
            'shift_end_time'   => $shift_end_time,
        ));
    }
}
