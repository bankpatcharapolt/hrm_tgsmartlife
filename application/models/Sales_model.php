<?php defined('BASEPATH') OR exit('No direct script access allowed');
class Sales_model extends CI_Model {
    public function get_records($filters=array(),$limit=30,$offset=0) {
        $this->db->select('sr.*,u.first_name,u.last_name,u.employee_id,d.name AS dept_name,t.team_name')
            ->from('sales_records sr')->join('users u','u.id=sr.user_id','left')->join('departments d','d.id=sr.department_id','left')->join('teams t','t.id=sr.team_id','left');
        if (!empty($filters['year']))      $this->db->where('sr.record_year',$filters['year']);
        if (!empty($filters['month']))     $this->db->where('sr.record_month',$filters['month']);
        if (!empty($filters['dept_id']))   $this->db->where('sr.department_id',$filters['dept_id']);
        if (!empty($filters['sales_type'])) $this->db->where('sr.sales_type',$filters['sales_type']);
        $this->db->order_by('sr.actual_amount','DESC');
        if ($limit>0) $this->db->limit($limit,$offset);
        return $this->db->get()->result();
    }
    public function create($data) {
        if ($data['target_amount']>0) $data['achievement_pct']=round($data['actual_amount']/$data['target_amount']*100,2);
        $data['created_at']=$data['updated_at']=date('Y-m-d H:i:s');
        $this->db->insert('sales_records',$data); return $this->db->insert_id();
    }
    public function delete($id) { $this->db->where('id',$id)->delete('sales_records'); return $this->db->affected_rows()>0; }
    public function get_yearly($y, $sales_type='individual') {
        $this->db->select('record_month,SUM(actual_amount) AS actual,SUM(target_amount) AS target,SUM(customer_count) AS customers')
            ->where('record_year',$y);
        if (!empty($sales_type)) $this->db->where('sales_type',$sales_type);
        return $this->db->group_by('record_month')->order_by('record_month','ASC')->get('sales_records')->result();
    }

    public function get_yearly_by_type($y, $sales_type) {
        return $this->get_yearly($y, $sales_type);
    }
    public function get_top($y,$m,$n=5) {
        return $this->db->select('sr.*,u.first_name,u.last_name,u.employee_id')
            ->from('sales_records sr')->join('users u','u.id=sr.user_id')
            ->where('sr.record_year',$y)->where('sr.record_month',$m)->where('sr.sales_type','individual')
            ->order_by('sr.actual_amount','DESC')->limit($n)->get()->result();
    }
}
