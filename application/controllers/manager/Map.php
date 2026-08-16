<?php defined('BASEPATH') OR exit('No direct script access allowed');

// แผนที่พนักงาน (มุมมองหัวหน้างาน) — เห็นเฉพาะทีมตัวเองเท่านั้น
// อ้างอิง logic เดียวกับ admin/Map.php แต่บังคับ team_id = ทีมของหัวหน้างานที่ login อยู่เสมอ
// (ไม่รับ team_id/dept_id จาก request เหมือน admin — ตาม requirement ที่ UI มีแค่ filter วันที่ + ค้นชื่อ)
class Map extends Manager_Controller {

    const SALES_DEPT_ID = 6; // department_id ของแผนกการขาย (ตรงกับ admin/Map.php)

    // หา team_id ของหัวหน้างานคนปัจจุบัน — pattern เดียวกับ manager/Attendance.php และ manager/Leave.php
    private function _my_team_id() {
        $u = $this->db->where('id', $this->current_user->user_id)->get('users')->row();
        return $u ? $u->team_id : null;
    }

    public function index() {
        $team_id = $this->_my_team_id();

        // [ตามที่ยืนยัน] หัวหน้างานที่ยังไม่ถูกผูก team_id → ไม่ fallback เห็นทุกคนแบบ attendance/leave
        // ตัดที่หน้า index เลยเพื่อไม่ต้อง init Google Maps โดยไม่จำเป็น ส่วน data() ก็มี guard ซ้ำไว้อีกชั้น
        $team = null;
        if ($team_id) {
            $team = $this->db->select('id, team_code, team_name, lat, lng')
                ->from('teams')
                ->where('id', $team_id)
                ->get()->row();
        }

        $this->render('manager/map/index', array(
            'title'      => 'แผนที่ทีม',
            'page_title' => 'แผนที่ทีม',
            'my_team_id' => $team_id,
            'team'       => $team,
        ));
    }

    // API: ดึงข้อมูลพนักงานบนแผนที่ — เฉพาะทีมของหัวหน้างานที่ login อยู่เท่านั้น
    public function data() {
        $date     = $this->input->get('date') ?: date('Y-m-d');
        $team_id  = $this->_my_team_id();
        $today    = date('Y-m-d');
        $is_today = ($date === $today);

        // [ตามที่ยืนยัน] ไม่มีทีม → ไม่แสดงใครเลย (ต่างจาก manager/Attendance.php และ manager/Leave.php
        // ที่ fallback เห็นทุกคนถ้าไม่มีทีม — จุดนี้ตั้งใจให้เข้มกว่าเพราะเป็นข้อมูลตำแหน่ง GPS สด)
        if (!$team_id) {
            $this->output
                ->set_content_type('application/json')
                ->set_output(json_encode(array(
                    'success'  => true,
                    'date'     => $date,
                    'is_today' => $is_today,
                    'no_team'  => true,
                    'markers'  => array(),
                    'summary'  => array(
                        'checked_in'=>0,'late'=>0,'forgot_checkout'=>0,'checked_out'=>0,
                        'checked_in_late'=>0,'on_leave'=>0,'not_in'=>0,
                    ),
                )));
            return;
        }

        // ── ดึงพนักงานในทีมตัวเอง (ที่ active ไม่นับ admin/owner) ──────────
        $employees = $this->db->select(
                'u.id, u.employee_id, u.first_name, u.last_name,
                 u.position, u.photo, u.department_id,
                 t.id AS team_id, t.team_name, t.lat AS team_lat, t.lng AS team_lng'
            )
            ->from('users u')
            ->join('roles r',  'r.id=u.role_id',  'left')
            ->join('teams t',  't.id=u.team_id',  'left')
            ->where('u.status', 'active')
            ->where('u.team_id', $team_id)
            ->where_not_in('r.slug', array('admin','owner'))
            ->get()->result();

        // ── ดึง attendance ของวันนั้น (เฉพาะทีมตัวเอง) ──────────────────
        $att_rows = $this->db->select(
                'a.user_id, a.status, a.check_in_time, a.check_out_time,
                 a.is_late, a.late_minutes,
                 a.is_early_out, a.early_out_minutes, a.early_out_reason,
                 a.checkout_out_of_radius_reason,
                 a.checkin_lat, a.checkin_lng, a.checkout_lat, a.checkout_lng'
            )
            ->from('attendance a')
            ->join('users u2', 'u2.id=a.user_id', 'inner')
            ->where('a.date', $date)
            ->where('u2.team_id', $team_id)
            ->get()->result();
        $att_map = array();
        foreach ($att_rows as $a) { $att_map[$a->user_id] = $a; }

        // ── ดึง leave ของวันนั้น (เฉพาะทีมตัวเอง) ───────────────────────
        $leave_rows = $this->db->select('lr.user_id')
            ->from('leave_requests lr')
            ->join('users u3', 'u3.id=lr.user_id', 'inner')
            ->where('lr.start_date <=', $date)
            ->where('lr.end_date >=',   $date)
            ->where('lr.status', 'approved')
            ->where('u3.team_id', $team_id)
            ->get()->result();
        $on_leave = array();
        foreach ($leave_rows as $l) { $on_leave[$l->user_id] = true; }

        // ── ดึงยอดขาย (เฉพาะแผนกการขาย) — [ตามที่ยืนยัน] แสดงเหมือน admin ────
        $sale_month = (int)date('m', strtotime($date));
        $sale_year  = (int)date('Y', strtotime($date));
        $sales_data      = array(); // ยอดขายรายเดือน
        $sales_year_data = array(); // ยอดขายรวมทั้งปี

        $emp_dept6_ids = array_map(function($e){ return $e->id; },
            array_filter($employees, function($e){ return (int)$e->department_id === self::SALES_DEPT_ID; })
        );

        if (!empty($emp_dept6_ids)) {
            $sales_rows = $this->db->select('user_id, actual_amount, target_amount')
                ->from('sales_records')
                ->where('record_year',  $sale_year)
                ->where('record_month', $sale_month)
                ->where('sales_type',   'individual')
                ->where_in('user_id',   $emp_dept6_ids)
                ->get()->result();
            foreach ($sales_rows as $s) {
                $sales_data[$s->user_id] = array(
                    'actual' => (float)$s->actual_amount,
                    'target' => (float)$s->target_amount,
                );
            }

            $sales_year_rows = $this->db->select('user_id, SUM(actual_amount) AS year_actual')
                ->from('sales_records')
                ->where('record_year',  $sale_year)
                ->where('sales_type',   'individual')
                ->where_in('user_id',   $emp_dept6_ids)
                ->group_by('user_id')
                ->get()->result();
            foreach ($sales_year_rows as $s) {
                $sales_year_data[$s->user_id] = (float)$s->year_actual;
            }
        }

        // ── Build markers (logic เดียวกับ admin/Map.php) ─────────────────
        $markers = array();

        foreach ($employees as $emp) {
            $att   = $att_map[$emp->id] ?? null;
            $leave = isset($on_leave[$emp->id]);
            $is_sales = ((int)$emp->department_id === self::SALES_DEPT_ID);

            // กำหนดสถานะ
            if ($is_today) {
                if ($leave || ($att && $att->status === 'leave')) {
                    $status = 'on_leave';
                } elseif ($att && $att->check_in_time && $att->check_out_time) {
                    $status = 'checked_out';
                } elseif ($att && $att->check_in_time && !$att->check_out_time) {
                    $status = !empty($att->is_late) ? 'late' : 'checked_in';
                } else {
                    $status = 'not_in';
                }
            } else {
                if ($leave || ($att && $att->status === 'leave')) {
                    $status = 'on_leave';
                } elseif ($att && $att->check_in_time && $att->check_out_time) {
                    $status = !empty($att->is_late) ? 'checked_in_late' : 'checked_in';
                } elseif ($att && $att->check_in_time && !$att->check_out_time) {
                    $status = 'forgot_checkout';
                } else {
                    $status = 'not_in';
                }
            }

            // กำหนดพิกัด
            $lat = null; $lng = null;
            if ($status === 'checked_out') {
                if ($att && $att->checkout_lat) {
                    $lat = (float)$att->checkout_lat;
                    $lng = (float)$att->checkout_lng;
                } elseif ($att && $att->checkin_lat) {
                    $lat = (float)$att->checkin_lat;
                    $lng = (float)$att->checkin_lng;
                } else {
                    $lat = $emp->team_lat ? (float)$emp->team_lat : null;
                    $lng = $emp->team_lng ? (float)$emp->team_lng : null;
                }
            } elseif (in_array($status, array('checked_in', 'late', 'checked_in_late', 'forgot_checkout'))) {
                if ($att && $att->checkin_lat) {
                    $lat = (float)$att->checkin_lat;
                    $lng = (float)$att->checkin_lng;
                } else {
                    $lat = $emp->team_lat ? (float)$emp->team_lat : null;
                    $lng = $emp->team_lng ? (float)$emp->team_lng : null;
                }
            } elseif (in_array($status, array('not_in', 'on_leave'))) {
                $lat = $emp->team_lat ? (float)$emp->team_lat : null;
                $lng = $emp->team_lng ? (float)$emp->team_lng : null;
            }

            if ($lat === null || $lng === null) continue; // ไม่มีพิกัด ข้ามไป

            $marker = array(
                'user_id'      => $emp->id,
                'employee_id'  => $emp->employee_id,
                'name'         => $emp->first_name . ' ' . $emp->last_name,
                'position'     => $emp->position ?? '',
                'team_name'    => $emp->team_name ?? '',
                'photo'        => $emp->photo ? base_url($emp->photo) : null,
                'status'       => $status,
                'lat'          => $lat,
                'lng'          => $lng,
                'is_sales'     => $is_sales,
                'sale_month'   => $sale_month,
                'sale_year'    => $sale_year,
                'is_late'      => !empty($att->is_late) ? true : false,
                'late_minutes' => $att ? (int)($att->late_minutes ?? 0) : 0,
                'is_early_out'      => !empty($att->is_early_out) ? true : false,
                'early_out_minutes' => $att ? (int)($att->early_out_minutes ?? 0) : 0,
                'early_out_reason'  => $att && !empty($att->early_out_reason) ? $att->early_out_reason : null,
                'offsite_reason'    => $att && !empty($att->checkout_out_of_radius_reason) ? $att->checkout_out_of_radius_reason : null,
                'check_in_time'  => $att && $att->check_in_time
                    ? date('H:i', strtotime($att->check_in_time)) : null,
                'check_out_time' => $att && $att->check_out_time
                    ? date('H:i', strtotime($att->check_out_time)) : null,
            );

            if ($is_sales) {
                $sd = $sales_data[$emp->id] ?? array('actual'=>0,'target'=>0);
                $marker['sales_actual']      = $sd['actual'];
                $marker['sales_target']      = $sd['target'];
                $marker['sales_pct']         = $sd['target'] > 0
                    ? round($sd['actual'] / $sd['target'] * 100, 2) : 0;
                $marker['sales_year_actual'] = $sales_year_data[$emp->id] ?? 0;
            }

            $markers[] = $marker;
        }

        // สรุปจำนวน
        $summary = array(
            'checked_in'      => 0,
            'late'            => 0,
            'forgot_checkout' => 0,
            'checked_out'     => 0,
            'checked_in_late' => 0,
            'on_leave'        => 0,
            'not_in'          => 0,
        );
        foreach ($markers as $m) {
            if (isset($summary[$m['status']])) $summary[$m['status']]++;
        }

        $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode(array(
                'success'  => true,
                'date'     => $date,
                'is_today' => $is_today,
                'no_team'  => false,
                'markers'  => $markers,
                'summary'  => $summary,
            )));
    }
}
