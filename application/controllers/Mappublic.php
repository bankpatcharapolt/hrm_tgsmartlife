<?php defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * MapPublic — แผนที่พนักงาน สาธารณะ (ไม่ต้อง login)
 * URL: /map/live       → หน้าแผนที่
 * URL: /map/live/data  → API JSON
 */
class Mappublic extends MY_Controller {

    const SALES_DEPT_ID = 6;

    public function __construct() {
        parent::__construct();
        // ไม่เรียก require_login() หรือ require_role()
        $this->layout = 'layouts/public_map'; // layout เปล่าไม่มี sidebar
    }

    public function index() {
        $teams = $this->db->select('id, team_code, team_name, lat, lng')
            ->from('teams')
            ->where('is_active', 1)
            ->order_by('team_code', 'ASC')
            ->get()->result();

        $departments = $this->db->select('id, name')
            ->from('departments')
            ->order_by('name', 'ASC')
            ->get()->result();

        $this->render('public/map/index', array(
            'title'       => 'แผนที่พนักงาน',
            'page_title'  => 'แผนที่พนักงาน (Realtime)',
            'teams'       => $teams,
            'departments' => $departments,
        ));
    }

    public function data() {
        // Logic เหมือน admin/Map::data() ทุกประการ
        $date     = $this->input->get('date')    ?: date('Y-m-d');
        $team_id  = $this->input->get('team_id') ?: null;
        $dept_id  = $this->input->get('dept_id') ?: null;
        $today    = date('Y-m-d');
        $is_today = ($date === $today);

        $q = $this->db->select(
                'u.id, u.employee_id, u.first_name, u.last_name,
                 u.position, u.photo, u.department_id,
                 t.id AS team_id, t.team_name, t.lat AS team_lat, t.lng AS team_lng'
            )
            ->from('users u')
            ->join('roles r', 'r.id=u.role_id', 'left')
            ->join('teams t', 't.id=u.team_id', 'left')
            ->where('u.status', 'active')
            ->where_not_in('r.slug', array('admin','owner'));

        if ($team_id) $q->where('u.team_id', $team_id);
        if ($dept_id) $q->where('u.department_id', $dept_id);
        $employees = $q->get()->result();

        $att_q = $this->db->select(
                'a.user_id, a.status, a.check_in_time, a.check_out_time,
                 a.is_late, a.late_minutes,
                 a.checkin_lat, a.checkin_lng, a.checkout_lat, a.checkout_lng'
            )
            ->from('attendance a')
            ->where('a.date', $date);
        if ($team_id) {
            $att_q->join('users u2','u2.id=a.user_id','inner')
                  ->where('u2.team_id', $team_id);
        }
        $att_rows = $att_q->get()->result();
        $att_map  = array();
        foreach ($att_rows as $a) { $att_map[$a->user_id] = $a; }

        $leave_q = $this->db->select('lr.user_id')
            ->from('leave_requests lr')
            ->where('lr.start_date <=', $date)
            ->where('lr.end_date >=',   $date)
            ->where('lr.status', 'approved');
        if ($team_id) {
            $leave_q->join('users u3','u3.id=lr.user_id','inner')
                    ->where('u3.team_id', $team_id);
        }
        $leave_rows = $leave_q->get()->result();
        $on_leave   = array();
        foreach ($leave_rows as $l) { $on_leave[$l->user_id] = true; }

        $sale_month      = (int)date('m', strtotime($date));
        $sale_year       = (int)date('Y', strtotime($date));
        $sales_data      = array();
        $sales_year_data = array();

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

        $markers = array();
        foreach ($employees as $emp) {
            $att      = $att_map[$emp->id] ?? null;
            $leave    = isset($on_leave[$emp->id]);
            $is_sales = ((int)$emp->department_id === self::SALES_DEPT_ID);

            if ($is_today) {
                if ($leave || ($att && $att->status === 'leave')) {
                    $status = 'on_leave';
                } elseif ($att && $att->check_in_time && $att->check_out_time) {
                    // [FIX] เดิมบังคับต้องมี checkout_lat ด้วยถึงจะนับว่าออกงานแล้ว
                    // ถ้า GPS ตอน checkout ส่งมาไม่ได้ (ปิด location, timeout ฯลฯ)
                    // check_out_time มีค่าแล้วแต่ checkout_lat เป็น NULL จะหลุดไป else -> 'not_in' (ขาดงาน) ทั้งที่ออกงานจริง
                    // ให้ตัดสินจาก check_in_time/check_out_time เท่านั้น เหมือน branch ของวันก่อนหน้า (บรรทัดล่าง)
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

            $lat = null; $lng = null;
            if ($status === 'checked_out') {
                if ($att && $att->checkout_lat) {
                    // มีพิกัด checkout → ใช้พิกัดจริงตอนออกงาน
                    $lat = (float)$att->checkout_lat;
                    $lng = (float)$att->checkout_lng;
                } elseif ($att && $att->checkin_lat) {
                    // [FIX] ไม่มีพิกัด checkout (GPS ส่งไม่มาตอนออกงาน) → fallback ใช้พิกัด checkin แทน
                    // เดิมถ้าไม่มี checkout_lat จะไม่เข้าเงื่อนไขนี้เลย ทำให้ lat/lng เป็น null แล้วโดน continue ข้ามไปทั้ง marker
                    $lat = (float)$att->checkin_lat;
                    $lng = (float)$att->checkin_lng;
                } else {
                    // ไม่มีพิกัดทั้ง checkout และ checkin → fallback ใช้พิกัดสาขา
                    $lat = $emp->team_lat ? (float)$emp->team_lat : null;
                    $lng = $emp->team_lng ? (float)$emp->team_lng : null;
                }
            } elseif (in_array($status, array('checked_in','late','checked_in_late','forgot_checkout'))) {
                if ($att && $att->checkin_lat) {
                    $lat = (float)$att->checkin_lat;
                    $lng = (float)$att->checkin_lng;
                } else {
                    // fallback ใช้พิกัดสาขา
                    $lat = $emp->team_lat ? (float)$emp->team_lat : null;
                    $lng = $emp->team_lng ? (float)$emp->team_lng : null;
                }
            } elseif (in_array($status, array('not_in','on_leave'))) {
                $lat = $emp->team_lat ? (float)$emp->team_lat : null;
                $lng = $emp->team_lng ? (float)$emp->team_lng : null;
            }

            if ($lat === null || $lng === null) continue;

            $marker = array(
                'user_id'     => $emp->id,
                'employee_id' => $emp->employee_id,
                'name'        => $emp->first_name . ' ' . $emp->last_name,
                'position'    => $emp->position ?? '',
                'team_name'   => $emp->team_name ?? '',
                'photo'       => $emp->photo
                    ? base_url('thumb.php?src=' . rawurlencode($emp->photo) . '&s=80')
                    : null,
                'status'      => $status,
                'lat'         => $lat,
                'lng'         => $lng,
                'is_sales'    => $is_sales,
                'sale_month'  => $sale_month,
                'sale_year'    => $sale_year,
                'is_late'      => !empty($att->is_late) ? true : false,
                'late_minutes' => $att ? (int)($att->late_minutes ?? 0) : 0,
                'check_in_time'  => $att && $att->check_in_time ? date('H:i', strtotime($att->check_in_time)) : null,
                'check_out_time' => $att && $att->check_out_time ? date('H:i', strtotime($att->check_out_time)) : null,
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

        $summary = array(
            'checked_in'      => 0,
            'late'            => 0,
            'checked_in_late' => 0,
            'forgot_checkout' => 0,
            'checked_out'     => 0,
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
                'markers'  => $markers,
                'summary'  => $summary,
            )));
    }
}
