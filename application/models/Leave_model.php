<?php defined('BASEPATH') OR exit('No direct script access allowed');
class Leave_model extends CI_Model {
    public function get_types() { return $this->db->get('leave_types')->result(); }
    public function get_by_id($id) {
        return $this->db->select('lr.*,lt.name AS leave_type_name,lt.is_paid,lt.can_leave_by_hour,u.first_name,u.last_name,u.employee_id,u.department_id,ap.first_name AS ap_fn,ap.last_name AS ap_ln')
            ->from('leave_requests lr')->join('leave_types lt','lt.id=lr.leave_type_id')
            ->join('users u','u.id=lr.user_id')->join('users ap','ap.id=lr.approved_by','left')
            ->where('lr.id',$id)->get()->row();
    }
    public function get_requests($filters=array(),$limit=30,$offset=0) {
        $this->db->select('lr.*,lt.name AS leave_type_name,u.first_name,u.last_name,u.employee_id,ap.first_name AS ap_fn,ap.last_name AS ap_ln')
            ->from('leave_requests lr')->join('leave_types lt','lt.id=lr.leave_type_id')
            ->join('users u','u.id=lr.user_id')->join('users ap','ap.id=lr.approved_by','left');
        if (!empty($filters['user_id'])) $this->db->where('lr.user_id',$filters['user_id']);
        if (!empty($filters['status'])) $this->db->where('lr.status',$filters['status']);
        if (!empty($filters['dept_id'])) $this->db->where('u.department_id',$filters['dept_id']);
        if (!empty($filters['year'])) $this->db->where('YEAR(lr.start_date)',$filters['year']);
        $this->db->order_by('lr.created_at','DESC');
        if ($limit>0) $this->db->limit($limit,$offset);
        return $this->db->get()->result();
    }
    public function create($data) {
        // set defaults
        if (!isset($data['leave_unit']))  $data['leave_unit']  = 'day';
        if (!isset($data['total_hours'])) $data['total_hours'] = 0;
        if (!isset($data['start_time']))  $data['start_time']  = null;
        if (!isset($data['end_time']))    $data['end_time']    = null;
        $data['created_at']=$data['updated_at']=date('Y-m-d H:i:s');
        $this->db->insert('leave_requests',$data);
        return $this->db->insert_id();
    }
    public function approve($id,$uid,$note='') { $this->db->where('id',$id)->update('leave_requests',array('status'=>'approved','approved_by'=>$uid,'approved_at'=>date('Y-m-d H:i:s'),'approver_note'=>$note,'updated_at'=>date('Y-m-d H:i:s'))); return $this->db->affected_rows()>0; }
    public function reject($id,$uid,$note='') { $this->db->where('id',$id)->update('leave_requests',array('status'=>'rejected','approved_by'=>$uid,'approved_at'=>date('Y-m-d H:i:s'),'approver_note'=>$note,'updated_at'=>date('Y-m-d H:i:s'))); return $this->db->affected_rows()>0; }
    public function cancel($id,$uid) { $this->db->where('id',$id)->where('user_id',$uid)->where('status','pending')->update('leave_requests',array('status'=>'cancelled','updated_at'=>date('Y-m-d H:i:s'))); return $this->db->affected_rows()>0; }
    public function count_pending() { return $this->db->where('status','pending')->count_all_results('leave_requests'); }
}
