<?php defined('BASEPATH') OR exit('No direct script access allowed');
class Salary_model extends CI_Model {
    public function get_records($filters=array(),$limit=20,$offset=0) {
        $this->db->select('sr.*,u.first_name,u.last_name,u.employee_id,d.name AS dept_name')
            ->from('salary_records sr')->join('users u','u.id=sr.user_id')->join('departments d','d.id=u.department_id','left');
        if (!empty($filters['year'])) $this->db->where('sr.salary_year',$filters['year']);
        if (!empty($filters['month'])) $this->db->where('sr.salary_month',$filters['month']);
        if (!empty($filters['user_id'])) $this->db->where('sr.user_id',$filters['user_id']);
        $this->db->order_by('u.employee_id','ASC');
        if ($limit>0) $this->db->limit($limit,$offset);
        return $this->db->get()->result();
    }
    public function get_by_id($id) {
        return $this->db->select('sr.*,u.first_name,u.last_name,u.employee_id,u.social_security_id,u.tax_id,d.name AS dept_name')
            ->from('salary_records sr')->join('users u','u.id=sr.user_id')->join('departments d','d.id=u.department_id','left')->where('sr.id',$id)->get()->row();
    }
    public function calc_and_save($data) {
        $data['gross_salary'] = $data['base_salary']+$data['commission']+$data['ot_pay']+$data['monthly_bonus']+$data['special_bonus']+$data['other_income'];
        $data['net_salary']   = $data['gross_salary']-$data['social_security_deduct']-$data['tax_deduct']-$data['other_deduct']-$data['absent_deduct']-$data['late_deduct'];
        $data['created_at']=$data['updated_at']=date('Y-m-d H:i:s');
        $this->db->insert('salary_records',$data); return $this->db->insert_id();
    }
    public function update($id,$data) {
        $data['gross_salary'] = $data['base_salary']+$data['commission']+$data['ot_pay']+$data['monthly_bonus']+$data['special_bonus']+$data['other_income'];
        $data['net_salary']   = $data['gross_salary']-$data['social_security_deduct']-$data['tax_deduct']-$data['other_deduct']-$data['absent_deduct']-$data['late_deduct'];
        $data['updated_at']=date('Y-m-d H:i:s'); $this->db->where('id',$id)->update('salary_records',$data); return $this->db->affected_rows()>0;
    }
    public function mark_paid($id) { $this->db->where('id',$id)->update('salary_records',['payment_status'=>'paid','payment_date'=>date('Y-m-d'),'updated_at'=>date('Y-m-d H:i:s')]); return $this->db->affected_rows()>0; }
    public function get_slips($uid) { return $this->db->where('user_id',$uid)->order_by('slip_year DESC,slip_month DESC')->get('salary_slips')->result(); }
    public function save_slip($data) { $data['created_at']=date('Y-m-d H:i:s'); $this->db->insert('salary_slips',$data); return $this->db->insert_id(); }
    public function get_tax_docs($uid) { return $this->db->where('user_id',$uid)->order_by('tax_year','DESC')->get('tax_documents')->result(); }
    public function save_tax_doc($data) { $data['created_at']=date('Y-m-d H:i:s'); $this->db->insert('tax_documents',$data); return $this->db->insert_id(); }
    public function get_bonuses($filters=[]) {
        $this->db->select('ab.*,u.first_name,u.last_name,u.employee_id,d.name AS dept_name')
            ->from('annual_bonuses ab')->join('users u','u.id=ab.user_id')->join('departments d','d.id=u.department_id','left');
        if (!empty($filters['year'])) $this->db->where('ab.bonus_year',$filters['year']);
        if (!empty($filters['user_id'])) $this->db->where('ab.user_id',$filters['user_id']);
        return $this->db->order_by('u.employee_id','ASC')->get()->result();
    }
    public function save_bonus($data) { $data['created_at']=date('Y-m-d H:i:s'); $this->db->insert('annual_bonuses',$data); return $this->db->insert_id(); }
    public function get_monthly_summary($y,$m) {
        return $this->db->select('COUNT(*) AS total_emp,SUM(net_salary) AS total_net,SUM(gross_salary) AS total_gross,SUM(social_security_deduct) AS total_ss,SUM(tax_deduct) AS total_tax')
            ->where('salary_year',$y)->where('salary_month',$m)->get('salary_records')->row();
    }
    public function get_yearly_chart($y) {
        return $this->db->select('salary_month,SUM(gross_salary) AS gross,SUM(net_salary) AS net')
            ->where('salary_year',$y)->group_by('salary_month')->order_by('salary_month','ASC')->get('salary_records')->result();
    }
}
