<?php defined('BASEPATH') OR exit('No direct script access allowed');
class Sales extends Admin_Controller {
    public function __construct() {
        parent::__construct();
        $this->require_permission('can_view_sales');
        $this->load->model('Sales_model');
    }
    public function index() {
        $y=$this->input->get('year')?:date('Y');
        $m=$this->input->get('month')?:date('n');

        // ดึง records ทั้งปี (สำหรับ tab แยกพนักงาน/ทีม)
        $records_year = $this->db
            ->select('sr.*,u.first_name,u.last_name,u.employee_id,d.name AS dept_name,t.team_name')
            ->from('sales_records sr')
            ->join('users u','u.id=sr.user_id','left')
            ->join('departments d','d.id=sr.department_id','left')
            ->join('teams t','t.id=sr.team_id','left')
            ->where('sr.record_year',$y)
            ->order_by('sr.actual_amount','DESC')
            ->get()->result();

        $this->render('admin/sales/index', array(
            'title'        => 'รายงานยอดขาย',
            'page_title'   => 'รายงานยอดขาย',
            'records'      => $this->Sales_model->get_records($y,$m),
            'records_year' => $records_year,
            'top'          => $this->Sales_model->get_top($y,$m,5),
            'yearly'       => $this->Sales_model->get_yearly($y),
            'employees'    => $this->User_model->get_all(array('status'=>'active'),300),
            'teams'        => $this->db->where('is_active',1)->get('teams')->result(),
            'year'         => $y,
            'month'        => $m,
        ));
    }
    public function store() {
        if ($this->input->method()!=='post') redirect('admin/sales');
        $type = $this->input->post('sales_type');
        $data = array(
            'sales_type'    => $type,
            'record_year'   => $this->input->post('record_year'),
            'record_month'  => $this->input->post('record_month'),
            'target_amount' => (float)$this->input->post('target_amount'),
            'actual_amount' => (float)$this->input->post('actual_amount'),
            'note'          => $this->input->post('note',TRUE),
            'created_by'    => $this->current_user->user_id,
            'achievement_pct'=> 0,
        );
        // individual
        if ($type === 'individual') {
            $data['user_id']       = $this->input->post('user_id') ?: null;
            $data['department_id'] = null;
            $data['team_id']       = null;
        } else {
            // ทีม/แผนก
            $data['user_id']       = null;
            $data['team_id']       = $this->input->post('team_id') ?: null;
            $data['department_id'] = $this->input->post('department_id') ?: null;
        }
        // คำนวณ %
        if ($data['target_amount'] > 0) {
            $data['achievement_pct'] = round(($data['actual_amount'] / $data['target_amount']) * 100, 2);
        }
        $data['customer_count'] = (int)($this->input->post('customer_count') ?: 0);
        $data['created_at'] = $data['updated_at'] = date('Y-m-d H:i:s');
        $this->db->insert('sales_records', $data);
        $this->session->set_flashdata('success','บันทึกยอดขายสำเร็จ');
        redirect('admin/sales');
    }
    public function delete($id) {
        $this->db->where('id',$id)->delete('sales_records');
        $this->session->set_flashdata('success','ลบสำเร็จ');
        redirect('admin/sales');
    }
}
