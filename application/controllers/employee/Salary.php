<?php defined('BASEPATH') OR exit('No direct script access allowed');
class Salary extends Employee_Controller {
    public function __construct() {
        parent::__construct();
        $this->require_permission('can_view_own_salary');
        $this->load->model('Salary_model');
    }
    public function index() {
        $uid = $this->current_user->user_id;
        $y   = $this->input->get('year') ? $this->input->get('year') : date('Y');
        $this->render('employee/salary/index', array(
            'title'     => 'เงินเดือนของฉัน',
            'page_title'=> 'เงินเดือนและเอกสาร',
            'records'   => $this->Salary_model->get_records(array('user_id'=>$uid,'year'=>$y), 12),
            'slips'     => $this->Salary_model->get_slips($uid ,$y),
            'tax_docs'  => $this->Salary_model->get_tax_docs($uid ,$y),
            'bonuses'   => $this->Salary_model->get_bonuses(array('user_id'=>$uid)),
            'year'      => $y
        ));
    }
}
