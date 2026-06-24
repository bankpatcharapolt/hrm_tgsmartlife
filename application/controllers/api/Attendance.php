<?php defined('BASEPATH') OR exit('No direct script access allowed');
class Attendance extends MY_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->require_login();
        $this->load->model('Attendance_model');
    }

    // ── Check-in ─────────────────────────────────────────────────────
    public function checkin()
    {
        if ($this->input->method() !== 'post') {
            $this->json_err('Method not allowed'); return;
        }
        $uid   = $this->current_user->user_id;
        $today = $this->Attendance_model->get_today($uid);
        if ($today && $today->check_in_time) {
            $this->json_err('ลงเวลาเข้างานแล้ว'); return;
        }

        // [ข้อ 2] รับ GPS จาก POST body (JSON หรือ form)
        $raw   = file_get_contents('php://input');
        $json  = json_decode($raw, true);
        $lat   = isset($json['lat'])  ? (float)$json['lat']  : (float)($this->input->post('lat')  ?: 0);
        $lng   = isset($json['lng'])  ? (float)$json['lng']  : (float)($this->input->post('lng')  ?: 0);

        // [ข้อ 2] รูปถ่าย (base64 → บันทึกไฟล์)
        $photo_path = null;
        $photo_b64  = isset($json['photo']) ? $json['photo'] : $this->input->post('photo');
        if (!empty($photo_b64)) {
            $photo_path = $this->_save_photo($photo_b64, $uid, 'in');
        }

        // check-in ปกติ
        $id = $this->Attendance_model->checkin($uid, $this->current_user->role_id);

        // อัปเดต GPS + photo
        if ($id && ($lat || $photo_path)) {
            $upd = array();
            if ($lat) { $upd['checkin_lat'] = $lat; $upd['checkin_lng'] = $lng; }
            if ($photo_path) $upd['checkin_photo'] = $photo_path;
            if (!empty($upd)) $this->db->where('id', $id)->update('attendance', $upd);
        }

        $today = $this->Attendance_model->get_today($uid);
        if ($today && $today->is_late) {
            // ── ข้อมูลพนักงานที่มาสาย ────────────────────────────────
            $emp = $this->db->where('id', $uid)->get('users')->row();
            $emp_name = $emp
                ? ($emp->first_name . ' ' . $emp->last_name . ' (' . ($emp->employee_id ?? '') . ')')
                : 'พนักงาน';
            $late_min = $today->late_minutes;
            $msg   = $emp_name . ' มาสาย ' . $late_min . ' นาที';
            $title = 'แจ้งเตือน: พนักงานมาสาย';
            $link  = base_url('admin/attendance');

            // ── รายชื่อ user ที่ต้องแจ้งเตือน (ไม่ซ้ำ, ไม่รวมตัวเอง) ──
            $notified = array();

            // 1. หัวหน้างานในทีมเดียวกัน (role = manager + team_id เดียวกัน)
            if (!empty($emp) && !empty($emp->team_id)) {
                $team_managers = $this->db
                    ->select('u.id')
                    ->from('users u')
                    ->join('roles r', 'r.id = u.role_id')
                    ->where('r.slug', 'manager')
                    ->where('u.team_id', $emp->team_id)
                    ->where('u.status', 'active')
                    ->where('u.id !=', $uid)
                    ->get()->result();
                foreach ($team_managers as $m) {
                    if (!in_array($m->id, $notified)) {
                        $this->Notification_model->create(array(
                            'user_id'   => $m->id,
                            'sender_id' => $uid,
                            'type'      => 'late_checkin',
                            'title'     => $title,
                            'message'   => $msg,
                            'link'      => $link,
                        ));
                        $notified[] = $m->id;
                    }
                }
            }

            // 2. ถ้าไม่มีหัวหน้าในทีม ให้แจ้ง manager ทุกคน
            if (empty($notified)) {
                $all_managers = $this->db
                    ->select('u.id')
                    ->from('users u')
                    ->join('roles r', 'r.id = u.role_id')
                    ->where('r.slug', 'manager')
                    ->where('u.status', 'active')
                    ->where('u.id !=', $uid)
                    ->get()->result();
                foreach ($all_managers as $m) {
                    if (!in_array($m->id, $notified)) {
                        $this->Notification_model->create(array(
                            'user_id'   => $m->id,
                            'sender_id' => $uid,
                            'type'      => 'late_checkin',
                            'title'     => $title,
                            'message'   => $msg,
                            'link'      => $link,
                        ));
                        $notified[] = $m->id;
                    }
                }
            }

            // 3. แจ้ง admin และ owner เสมอ (ไม่ว่าจะมีหัวหน้าหรือไม่)
            $admins_owners = $this->db
                ->select('u.id')
                ->from('users u')
                ->join('roles r', 'r.id = u.role_id')
                ->where_in('r.slug', array('admin', 'owner'))
                ->where('u.status', 'active')
                ->where('u.id !=', $uid)
                ->get()->result();
            foreach ($admins_owners as $a) {
                if (!in_array($a->id, $notified)) {
                    $this->Notification_model->create(array(
                        'user_id'   => $a->id,
                        'sender_id' => $uid,
                        'type'      => 'late_checkin',
                        'title'     => $title,
                        'message'   => $msg,
                        'link'      => $link,
                    ));
                    $notified[] = $a->id;
                }
            }
        }

        $this->json_ok(array(
            'time'         => date('H:i:s'),
            'is_late'      => (bool)($today->is_late ?? false),
            'late_minutes' => (int)($today->late_minutes ?? 0),
        ), 'ลงเวลาเข้างานสำเร็จ');
    }

    // ── Check-out ────────────────────────────────────────────────────
    public function checkout()
    {
        if ($this->input->method() !== 'post') {
            $this->json_err('Method not allowed'); return;
        }
        $uid   = $this->current_user->user_id;
        $today = $this->Attendance_model->get_today($uid);
        if (!$today || !$today->check_in_time) {
            $this->json_err('ยังไม่ได้ลงเวลาเข้างาน'); return;
        }
        if ($today->check_out_time) {
            $this->json_err('ลงเวลาออกงานแล้ว'); return;
        }

        // [ข้อ 2] GPS checkout
        $raw  = file_get_contents('php://input');
        $json = json_decode($raw, true);
        $lat  = isset($json['lat']) ? (float)$json['lat'] : (float)($this->input->post('lat') ?: 0);
        $lng  = isset($json['lng']) ? (float)$json['lng'] : (float)($this->input->post('lng') ?: 0);

        $this->Attendance_model->checkout($today->id, $uid);

        // [ข้อ 2] ตรวจออกก่อนเวลา
        $early_out = 0; $early_min = 0;
        $this->load->model('Shift_model');
        $shift = $this->db->where('id', $today->shift_id)->get('shifts')->row();
        if ($shift && !empty($shift->end_time)) {
            $end_sec  = strtotime(date('Y-m-d') . ' ' . $shift->end_time);
            $now_sec  = time();
            if ($now_sec < $end_sec) {
                $early_out = 1;
                $early_min = (int)round(($end_sec - $now_sec) / 60);
            }
        }

        // อัปเดต GPS + early_out
        $upd = array();
        if ($lat) { $upd['checkout_lat'] = $lat; $upd['checkout_lng'] = $lng; }
        if ($early_out) { $upd['is_early_out'] = 1; $upd['early_out_minutes'] = $early_min; }
        if (!empty($upd)) $this->db->where('id', $today->id)->update('attendance', $upd);

        $msg = 'ลงเวลาออกงานสำเร็จ';
        if ($early_out) $msg .= ' (ออกก่อนเวลา ' . $early_min . ' นาที)';

        // ── แจ้งเตือนออกก่อนหมดกะ ────────────────────────────────────
        if ($early_out) {
            $emp = $this->db->where('id', $uid)->get('users')->row();
            $emp_name = $emp
                ? ($emp->first_name . ' ' . $emp->last_name . ' (' . ($emp->employee_id ?? '') . ')')
                : 'พนักงาน';
            $notif_title = 'แจ้งเตือน: ออกก่อนหมดกะ';
            $notif_msg   = $emp_name . ' ออกก่อนหมดกะ ' . $early_min . ' นาที'
                         . ' (กะ ' . ($shift->name ?? '') . ' สิ้นสุด ' . substr($shift->end_time, 0, 5) . ')';
            $notif_link  = base_url('admin/attendance');
            $notified    = array();

            // 1. หัวหน้างานในทีมเดียวกันเท่านั้น (role = manager + team_id เดียวกัน)
            if (!empty($emp) && !empty($emp->team_id)) {
                $team_managers = $this->db
                    ->select('u.id')
                    ->from('users u')
                    ->join('roles r', 'r.id = u.role_id')
                    ->where('r.slug', 'manager')
                    ->where('u.team_id', $emp->team_id)
                    ->where('u.status', 'active')
                    ->where('u.id !=', $uid)
                    ->get()->result();
                foreach ($team_managers as $m) {
                    if (!in_array($m->id, $notified)) {
                        $this->Notification_model->create(array(
                            'user_id'   => $m->id,
                            'sender_id' => $uid,
                            'type'      => 'early_checkout',
                            'title'     => $notif_title,
                            'message'   => $notif_msg,
                            'link'      => $notif_link,
                        ));
                        $notified[] = $m->id;
                    }
                }
            }

            // 2. admin และ owner เสมอ
            $admins_owners = $this->db
                ->select('u.id')
                ->from('users u')
                ->join('roles r', 'r.id = u.role_id')
                ->where_in('r.slug', array('admin', 'owner'))
                ->where('u.status', 'active')
                ->where('u.id !=', $uid)
                ->get()->result();
            foreach ($admins_owners as $a) {
                if (!in_array($a->id, $notified)) {
                    $this->Notification_model->create(array(
                        'user_id'   => $a->id,
                        'sender_id' => $uid,
                        'type'      => 'early_checkout',
                        'title'     => $notif_title,
                        'message'   => $notif_msg,
                        'link'      => $notif_link,
                    ));
                    $notified[] = $a->id;
                }
            }
        }

        $this->json_ok(array(
            'time'          => date('H:i:s'),
            'is_early_out'  => (bool)$early_out,
            'early_minutes' => $early_min,
        ), $msg);
    }

    // ── บันทึกรูปถ่าย base64 ────────────────────────────────────────
    private function _save_photo($base64, $uid, $type)
    {
        // รับ data:image/jpeg;base64,....
        if (strpos($base64, ',') !== false) {
            list(, $base64) = explode(',', $base64, 2);
        }
        $data = base64_decode($base64);
        if (!$data || strlen($data) < 100) return null;

        $dir = FCPATH . 'uploads/checkin_photos/';
        if (!is_dir($dir)) mkdir($dir, 0755, true);

        $fname = $uid . '_' . $type . '_' . date('Ymd_His') . '.jpg';
        file_put_contents($dir . $fname, $data);
        return 'uploads/checkin_photos/' . $fname;
    }
}
