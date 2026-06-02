<?php defined('BASEPATH') OR exit('No direct script access allowed');
class User_model extends CI_Model {
    public function authenticate($username, $password) {
        $u = $this->db->select('u.*,r.name AS role_name,r.slug AS role_slug,
            r.can_checkin,r.can_view_own_salary,r.can_approve_leave,r.can_manage_employees,
            r.can_view_sales,r.can_send_notifications,r.can_manage_salary,r.can_upload_documents,
            r.can_view_reports,r.can_monitor_attendance,r.is_full_access,
            d.name AS department_name')
            ->from('users u')->join('roles r','r.id=u.role_id','left')
            ->join('departments d','d.id=u.department_id','left')
            ->where('u.username',$username)->where('u.status','active')->get()->row();
        if (!$u || !password_verify($password, $u->password)) return false;
        $this->db->where('id',$u->id)->update('users',array('last_login'=>date('Y-m-d H:i:s')));
        unset($u->password);
        return $u;
    }
    public function get_by_id($id) {
        return $this->db->select('u.*,r.name AS role_name,r.slug AS role_slug,
            r.can_checkin,r.can_view_own_salary,r.can_approve_leave,r.can_manage_employees,
            r.can_view_sales,r.can_send_notifications,r.can_manage_salary,r.can_upload_documents,
            r.can_view_reports,r.can_monitor_attendance,r.is_full_access,d.name AS department_name')
            ->from('users u')->join('roles r','r.id=u.role_id','left')
            ->join('departments d','d.id=u.department_id','left')
            ->where('u.id',$id)->get()->row();
    }
    public function get_all($filters=array(),$limit=20,$offset=0) {
        $this->db->select('u.id,u.employee_id,u.first_name,u.last_name,u.nickname,u.phone,u.email,
            u.start_date,u.base_salary,u.status,u.photo,r.name AS role_name,r.slug AS role_slug,
            d.name AS department_name')
            ->from('users u')->join('roles r','r.id=u.role_id','left')
            ->join('departments d','d.id=u.department_id','left');
        if (!empty($filters['department_id'])) $this->db->where('u.department_id',$filters['department_id']);
        if (!empty($filters['role_id'])) $this->db->where('u.role_id',$filters['role_id']);
        if (!empty($filters['status'])) $this->db->where('u.status',$filters['status']);
        if (!empty($filters['search'])) {
            $s = $filters['search'];
            $this->db->group_start()->like('u.first_name',$s)->or_like('u.last_name',$s)
                ->or_like('u.employee_id',$s)->or_like('u.phone',$s)->group_end();
        }
        $this->db->order_by('u.employee_id','ASC');
        if ($limit>0) $this->db->limit($limit,$offset);
        return $this->db->get()->result();
    }
    public function count_all($filters=array()) {
        $this->db->from('users u')->join('roles r','r.id=u.role_id','left');
        if (!empty($filters['department_id'])) $this->db->where('u.department_id',$filters['department_id']);
        if (!empty($filters['role_id'])) $this->db->where('u.role_id',$filters['role_id']);
        if (!empty($filters['status'])) $this->db->where('u.status',$filters['status']);
        if (!empty($filters['search'])) {
            $s = $filters['search'];
            $this->db->group_start()->like('u.first_name',$s)->or_like('u.last_name',$s)->or_like('u.employee_id',$s)->group_end();
        }
        return $this->db->count_all_results();
    }
    public function create($data) {
        if (!empty($data['password'])) $data['password'] = password_hash($data['password'],PASSWORD_BCRYPT,array('cost'=>12));
        $data['created_at'] = $data['updated_at'] = date('Y-m-d H:i:s');
        $this->db->insert('users',$data);
        return $this->db->affected_rows()>0 ? $this->db->insert_id() : false;
    }
    public function update($id,$data) {
        if (!empty($data['password'])) $data['password'] = password_hash($data['password'],PASSWORD_BCRYPT,array('cost'=>12));
        else unset($data['password']);
        $data['updated_at'] = date('Y-m-d H:i:s');
        $this->db->where('id',$id)->update('users',$data);
        return $this->db->affected_rows()>0;
    }
    public function username_exists($u,$ex=0) { if($ex>0)$this->db->where('id !=',$ex); return $this->db->where('username',$u)->count_all_results('users')>0; }
    public function employee_id_exists($e,$ex=0) { if($ex>0)$this->db->where('id !=',$ex); return $this->db->where('employee_id',$e)->count_all_results('users')>0; }
    public function get_all_roles() { return $this->db->order_by('id','ASC')->get('roles')->result(); }
    public function get_role($id) { return $this->db->where('id',$id)->get('roles')->row(); }
    public function update_role($id,$data) { $data['updated_at']=date('Y-m-d H:i:s'); $this->db->where('id',$id)->update('roles',$data); return $this->db->affected_rows()>0; }
    public function get_all_departments() { return $this->db->order_by('name','ASC')->get('departments')->result(); }
    public function log($uid,$action,$module,$desc='') {
        $this->db->insert('activity_logs',array('user_id'=>$uid,'action'=>$action,'module'=>$module,'description'=>$desc,'ip_address'=>$this->input->ip_address(),'created_at'=>date('Y-m-d H:i:s')));
    }
}
