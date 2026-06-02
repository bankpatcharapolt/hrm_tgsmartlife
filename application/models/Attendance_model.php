<?php defined('BASEPATH') OR exit('No direct script access allowed');
class Attendance_model extends CI_Model {
    public function get_today($uid) {
        return $this->db->where('user_id',$uid)->where('date',date('Y-m-d'))->get('attendance')->row();
    }
    public function get_by_id($id) {
        return $this->db->select('a.*,u.first_name,u.last_name,u.employee_id,s.name AS shift_name')
            ->from('attendance a')->join('users u','u.id=a.user_id')
            ->join('shifts s','s.id=a.shift_id','left')
            ->where('a.id',$id)->get()->row();
    }
    public function checkin($uid, $shift_id=null) {
        $this->load->model('Shift_model');
        $shift = $shift_id
            ? $this->Shift_model->get_by_id($shift_id)
            : $this->Shift_model->get_for_user($uid);
        $now  = date('H:i:s');
        $is_late = 0; $lm = 0;
        if ($shift) {
            $diff = (strtotime($now) - strtotime($shift->start_time)) / 60;
            $threshold = $shift->late_threshold_minutes ?? 15;
            if ($diff > $threshold) { $is_late=1; $lm=round($diff); }
        }
        $this->db->insert('attendance',array(
            'user_id'=>$uid, 'shift_id'=>$shift_id??($shift?$shift->id??null:null),
            'date'=>date('Y-m-d'), 'check_in_time'=>date('Y-m-d H:i:s'),
            'is_late'=>$is_late, 'late_minutes'=>$lm, 'status'=>'present',
            'created_at'=>date('Y-m-d H:i:s'), 'updated_at'=>date('Y-m-d H:i:s')
        ));
        return $this->db->insert_id();
    }
    public function checkout($att_id, $uid) {
        $att = $this->db->where('id',$att_id)->where('user_id',$uid)->get('attendance')->row();
        if (!$att) return false;
        $this->load->model('Shift_model');
        $shift = $att->shift_id
            ? $this->Shift_model->get_by_id($att->shift_id)
            : $this->Shift_model->get_for_user($uid);
        $ot = $shift ? $this->Shift_model->calc_ot($shift, date('Y-m-d H:i:s')) : 0;
        $this->db->where('id',$att_id)->update('attendance',array(
            'check_out_time'=>date('Y-m-d H:i:s'), 'ot_hours'=>$ot,
            'updated_at'=>date('Y-m-d H:i:s')
        ));
        return true;
    }
    public function get_monthly($uid,$y,$m) {
        return $this->db->select('a.*,s.name AS shift_name,s.start_time AS shift_start,s.end_time AS shift_end,s.color AS shift_color')
            ->from('attendance a')->join('shifts s','s.id=a.shift_id','left')
            ->where('a.user_id',$uid)->where('YEAR(a.date)',$y)->where('MONTH(a.date)',$m)
            ->order_by('a.date','ASC')->get()->result();
    }
    public function get_all_monthly($y,$m,$dept=null,$shift_id=null) {
        $this->db->select('a.*,u.first_name,u.last_name,u.employee_id,d.name AS dept_name,s.name AS shift_name,s.color AS shift_color,lt.name AS leave_type_name')
            ->from('attendance a')
            ->join('users u','u.id=a.user_id')
            ->join('departments d','d.id=u.department_id','left')
            ->join('shifts s','s.id=a.shift_id','left')
            ->join('leave_types lt','lt.id=a.leave_type_id','left')
            ->where('YEAR(a.date)',$y)->where('MONTH(a.date)',$m);
        if ($dept)     $this->db->where('u.department_id',$dept);
        if ($shift_id) $this->db->where('a.shift_id',$shift_id);
        return $this->db->order_by('a.date DESC,u.employee_id ASC')->get()->result();
    }
    public function get_summary($uid,$y,$m) {
        $rows=$this->get_monthly($uid,$y,$m);
        $s=array('present'=>0,'absent'=>0,'late'=>0,'leave'=>0,'leave_hours'=>0,'total_late_min'=>0,'total_ot'=>0,'total_work_hrs'=>0);
        foreach($rows as $r) {
            if ($r->status==='present') $s['present']++;
            if ($r->status==='absent')  $s['absent']++;
            if ($r->status==='leave')   { $s['leave']++; $s['leave_hours']+=$r->leave_hours; }
            if ($r->is_late)            { $s['late']++; $s['total_late_min']+=$r->late_minutes; }
            $s['total_ot']+=$r->ot_hours;
        }
        return $s;
    }
    public function manual_add($data) {
        $data['created_at']=$data['updated_at']=date('Y-m-d H:i:s');
        // คำนวณ is_late ถ้ามีกะ
        if (!empty($data['shift_id']) && !empty($data['check_in_time'])) {
            $this->load->model('Shift_model');
            $shift = $this->Shift_model->get_by_id($data['shift_id']);
            if ($shift) {
                $ct = date('H:i:s', strtotime($data['check_in_time']));
                $diff = (strtotime($ct) - strtotime($shift->start_time)) / 60;
                $data['is_late']       = $diff > ($shift->late_threshold_minutes??15) ? 1 : 0;
                $data['late_minutes']  = $data['is_late'] ? round($diff) : 0;
            }
        }
        $this->db->insert('attendance',$data);
        return $this->db->insert_id();
    }
    public function update_record($id, $data, $admin_id=null) {
        $data['updated_at'] = date('Y-m-d H:i:s');
        if ($admin_id) {
            $data['modified_by'] = $admin_id;
            $data['modified_at'] = date('Y-m-d H:i:s');
        }
        // คำนวณ is_late ใหม่ถ้ามี shift + check_in
        if (!empty($data['shift_id']) && !empty($data['check_in_time'])) {
            $this->load->model('Shift_model');
            $shift = $this->Shift_model->get_by_id($data['shift_id']);
            if ($shift) {
                $ct = date('H:i:s', strtotime($data['check_in_time']));
                $diff = (strtotime($ct) - strtotime($shift->start_time)) / 60;
                $data['is_late']      = $diff > ($shift->late_threshold_minutes??15) ? 1 : 0;
                $data['late_minutes'] = $data['is_late'] ? round($diff) : 0;
            }
        }
        $this->db->where('id',$id)->update('attendance',$data);
        return $this->db->affected_rows()>0;
    }
    public function delete_record($id) {
        $this->db->where('id',$id)->delete('attendance');
        return $this->db->affected_rows()>0;
    }
    public function count_all_monthly($y,$m,$dept=null) {
        $this->db->from('attendance a')->join('users u','u.id=a.user_id','left')
            ->where('YEAR(a.date)',$y)->where('MONTH(a.date)',$m);
        if ($dept) $this->db->where('u.department_id',$dept);
        return $this->db->count_all_results();
    }
}
