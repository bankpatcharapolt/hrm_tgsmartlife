<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Sales extends Employee_Controller {

    public function __construct() {
        parent::__construct();
        $this->require_permission('can_view_sales');
        $this->load->model(array('Sales_model', 'Salary_model'));
    }

    public function index() {
        $uid = $this->current_user->user_id;
        $y   = (int)($this->input->get('year')  ?: date('Y'));
        $m   = (int)($this->input->get('month') ?: date('n'));

        // ── ยอดขายรายเดือนของปีนี้ (สำหรับ chart + ตาราง) ──────────────
        $monthly = $this->db
            ->select('sr.record_month, sr.actual_amount, sr.target_amount, sr.achievement_pct, sr.note')
            ->from('sales_records sr')
            ->where('sr.user_id', $uid)
            ->where('sr.record_year', $y)
            ->where('sr.sales_type', 'individual')
            ->order_by('sr.record_month', 'ASC')
            ->get()->result();

        // ── ยอดขายเดือนที่เลือก (สำหรับ summary card) ──────────────────
        $current_month = $this->db
            ->where('user_id', $uid)
            ->where('record_year', $y)
            ->where('record_month', $m)
            ->where('sales_type', 'individual')
            ->get('sales_records')->row();

        // ── ยอดรวมปีนี้ ──────────────────────────────────────────────────
        $yearly_total = $this->db
            ->select('SUM(actual_amount) AS total_actual, SUM(target_amount) AS total_target')
            ->where('user_id', $uid)
            ->where('record_year', $y)
            ->where('sales_type', 'individual')
            ->get('sales_records')->row();

        // ── commission + bonus จาก salary_records ──────────────────────
        $salary_data = $this->db
            ->select('salary_month, commission, monthly_bonus, special_bonus, gross_salary, net_salary')
            ->where('user_id', $uid)
            ->where('salary_year', $y)
            ->order_by('salary_month', 'ASC')
            ->get('salary_records')->result();

        // map salary ตาม month
        $salary_map = array();
        foreach ($salary_data as $s) {
            $salary_map[(int)$s->salary_month] = $s;
        }

        // ── โบนัสจากยอดขาย (annual_bonuses type=sales) ─────────────────
        $sales_bonus = $this->db
            ->select('SUM(amount) AS total')
            ->where('user_id', $uid)
            ->where('bonus_type', 'sales')
            ->where('bonus_year', $y)
            ->get('annual_bonuses')->row();

        // ── ยอดขายย้อนหลัง 3 ปี (สำหรับ comparison chart) ─────────────
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

        $this->render('employee/sales/index', array(
            'title'        => 'ยอดขายของฉัน',
            'page_title'   => 'ยอดขายของฉัน',
            'year'         => $y,
            'month'        => $m,
            'monthly'      => $monthly,
            'current_month'=> $current_month,
            'yearly_total' => $yearly_total,
            'salary_map'   => $salary_map,
            'sales_bonus'  => $sales_bonus,
            'history'      => $history,
        ));
    }
}
