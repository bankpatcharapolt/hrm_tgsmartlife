<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Sales extends Employee_Controller {

    public function __construct() {
        parent::__construct();
        // อนุญาต: มี can_view_sales หรือ เป็น manager/admin/owner
        $role_slug = (string)($this->session->userdata('role_slug') ?? '');
        $is_full   = (bool)$this->session->userdata('is_full_access');
        $is_manager_above = in_array($role_slug, array('manager','admin','owner'));
        if (!$is_full && !$this->session->userdata('can_view_sales') && !$is_manager_above) {
            $this->show_403();
        }
        $this->load->model(array('Sales_model', 'Salary_model'));
    }

    // ── ยอดขายของฉัน (เดิม) ─────────────────────────────────────────
    public function index() {
        $uid = $this->current_user->user_id;
        $y   = (int)($this->input->get('year')  ?: date('Y'));
        $m   = (int)($this->input->get('month') ?: date('n'));

        $monthly = $this->db
            ->select('sr.record_month, sr.actual_amount, sr.target_amount, sr.achievement_pct, sr.note')
            ->from('sales_records sr')
            ->where('sr.user_id', $uid)
            ->where('sr.record_year', $y)
            ->where('sr.sales_type', 'individual')
            ->order_by('sr.record_month', 'ASC')
            ->get()->result();

        $current_month = $this->db
            ->where('user_id', $uid)
            ->where('record_year', $y)
            ->where('record_month', $m)
            ->where('sales_type', 'individual')
            ->get('sales_records')->row();

        $yearly_total = $this->db
            ->select('SUM(actual_amount) AS total_actual, SUM(target_amount) AS total_target')
            ->where('user_id', $uid)
            ->where('record_year', $y)
            ->where('sales_type', 'individual')
            ->get('sales_records')->row();

        $salary_data = $this->db
            ->select('salary_month, commission, monthly_bonus, special_bonus, gross_salary, net_salary')
            ->where('user_id', $uid)
            ->where('salary_year', $y)
            ->order_by('salary_month', 'ASC')
            ->get('salary_records')->result();

        $salary_map = array();
        foreach ($salary_data as $s) {
            $salary_map[(int)$s->salary_month] = $s;
        }

        $sales_bonus = $this->db
            ->select('SUM(amount) AS total')
            ->where('user_id', $uid)
            ->where('bonus_type', 'sales')
            ->where('bonus_year', $y)
            ->get('annual_bonuses')->row();

        $history = array();
        for ($hy = $y - 2; $hy <= $y; $hy++) {
            $row = $this->db
                ->select('SUM(actual_amount) AS total')
                ->where('user_id', $uid)
                ->where('record_year', $hy)
                ->where('sales_type', 'individual')
                ->get('sales_records')->row();
            $history[$hy] = $row ? (float)$row->total : 0;
        }

        // โบนัส sales แยกตามเดือน
        $sales_bonus_monthly = array();
        $bonus_rows = $this->db
            ->select('bonus_month, amount')
            ->where('user_id', $uid)
            ->where('bonus_type', 'sales')
            ->where('bonus_year', $y)
            ->where('bonus_month IS NOT NULL')
            ->get('annual_bonuses')->result();
        foreach ($bonus_rows as $br) {
            $sales_bonus_monthly[(int)$br->bonus_month] = (float)$br->amount;
        }

        $this->render('employee/sales/index', array(
            'title'               => 'ยอดขายของฉัน',
            'page_title'          => 'ยอดขายของฉัน',
            'year'                => $y,
            'month'               => $m,
            'monthly'             => $monthly,
            'current_month'       => $current_month,
            'yearly_total'        => $yearly_total,
            'salary_map'          => $salary_map,
            'sales_bonus'         => $sales_bonus,
            'sales_bonus_monthly' => $sales_bonus_monthly,
            'history'             => $history,
        ));
    }

    // ── ยอดขายของทีม ────────────────────────────────────────────────
    public function team() {
        $uid = $this->current_user->user_id;
        $y   = (int)($this->input->get('year')  ?: date('Y'));
        $m   = (int)($this->input->get('month') ?: date('n'));

        // ดึงข้อมูล team ของ user
        $user = $this->db->where('id', $uid)->get('users')->row();
        $team_id = $user ? $user->team_id : null;

        $team = null;
        if ($team_id) {
            $team = $this->db->where('id', $team_id)->get('teams')->row();
        }

        // ── ยอดขายทีมรายเดือน (sales_type='team') ──────────────────
        $monthly = array();
        if ($team_id) {
            $monthly = $this->db
                ->select('sr.record_month, sr.actual_amount, sr.target_amount, sr.achievement_pct, sr.note')
                ->from('sales_records sr')
                ->where('sr.team_id', $team_id)
                ->where('sr.record_year', $y)
                ->where('sr.sales_type', 'team')
                ->order_by('sr.record_month', 'ASC')
                ->get()->result();
        }

        // ── ยอดขายทีมเดือนที่เลือก ──────────────────────────────────
        $current_month = null;
        if ($team_id) {
            $current_month = $this->db
                ->where('team_id', $team_id)
                ->where('record_year', $y)
                ->where('record_month', $m)
                ->where('sales_type', 'team')
                ->get('sales_records')->row();
        }

        // ── ยอดรวมทีมปีนี้ ───────────────────────────────────────────
        $yearly_total = null;
        if ($team_id) {
            $yearly_total = $this->db
                ->select('SUM(actual_amount) AS total_actual, SUM(target_amount) AS total_target')
                ->where('team_id', $team_id)
                ->where('record_year', $y)
                ->where('sales_type', 'team')
                ->get('sales_records')->row();
        }

        // ── สมาชิกในทีม + ยอดขายรายคนเดือนนี้ ──────────────────────
        $members = array();
        if ($team_id) {
            $members = $this->db
                ->select('u.id, u.employee_id, u.first_name, u.last_name, u.photo,
                          COALESCE(sr.actual_amount, 0) AS actual_amount,
                          COALESCE(sr.target_amount, 0) AS target_amount,
                          COALESCE(sr.achievement_pct, 0) AS achievement_pct')
                ->from('users u')
                ->join('sales_records sr',
                    "sr.user_id = u.id
                     AND sr.record_year = {$y}
                     AND sr.record_month = {$m}
                     AND sr.sales_type = 'individual'", 'left')
                ->where('u.team_id', $team_id)
                ->where('u.status', 'active')
                ->order_by('sr.actual_amount', 'DESC')
                ->get()->result();
        }

        // ── ยอดขายทีมย้อนหลัง 3 ปี ──────────────────────────────────
        $history = array();
        for ($hy = $y - 2; $hy <= $y; $hy++) {
            $row = null;
            if ($team_id) {
                $row = $this->db
                    ->select('SUM(actual_amount) AS total')
                    ->where('team_id', $team_id)
                    ->where('record_year', $hy)
                    ->where('sales_type', 'team')
                    ->get('sales_records')->row();
            }
            $history[$hy] = $row ? (float)$row->total : 0;
        }

        $this->render('employee/sales/team', array(
            'title'         => 'ยอดขายของทีม',
            'page_title'    => 'ยอดขายของทีม',
            'uid'           => $uid,
            'year'          => $y,
            'month'         => $m,
            'team'          => $team,
            'monthly'       => $monthly,
            'current_month' => $current_month,
            'yearly_total'  => $yearly_total,
            'members'       => $members,
            'history'       => $history,
        ));
    }
}
