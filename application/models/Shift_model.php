<?php defined('BASEPATH') OR exit('No direct script access allowed');
class Shift_model extends CI_Model {
    public function get_all($active_only=true) {
        if ($active_only) $this->db->where('status','active');
        return $this->db->order_by('start_time','ASC')->get('shifts')->result();
    }
    public function get_by_id($id) {
        return $this->db->where('id',$id)->get('shifts')->row();
    }
    public function create($data) {
        $data['created_at']=$data['updated_at']=date('Y-m-d H:i:s');
        $this->db->insert('shifts',$data);
        return $this->db->insert_id();
    }
    public function update($id,$data) {
        $data['updated_at']=date('Y-m-d H:i:s');
        $this->db->where('id',$id)->update('shifts',$data);
        return $this->db->affected_rows()>0;
    }
    public function delete($id) {
        $this->db->where('id',$id)->update('shifts',array('status'=>'inactive'));
        return $this->db->affected_rows()>0;
    }
    public function get_for_user($user_id) {
        // ดึงกะของ user จาก users.shift_id
        $u = $this->db->select('s.*')->from('users u')
            ->join('shifts s','s.id=u.shift_id','left')
            ->where('u.id',$user_id)->get()->row();
        // ถ้าไม่มีกะประจำ ใช้กะปกติ (08:30-17:30)
        if (!$u) {
            return (object)array('start_time'=>'08:30:00','end_time'=>'17:30:00','late_threshold_minutes'=>15,'break_minutes'=>60,'is_night_shift'=>0,'name'=>'กะปกติ');
        }
        return $u;
    }
    // ตรวจสอบว่า check-in สายไหม
    public function is_late($shift, $checkin_time) {
        $shift_start = strtotime($shift->start_time);
        $checkin     = strtotime($checkin_time);
        $diff_min    = ($checkin - $shift_start) / 60;
        return $diff_min > ($shift->late_threshold_minutes ?? 15);
    }
    // คำนวณ OT
    public function calc_ot($shift, $checkout_time) {
        if (!$checkout_time) return 0;
        $end  = strtotime($shift->end_time);
        $cout = strtotime($checkout_time);
        // Night shift ข้ามวัน
        if ($shift->is_night_shift && $cout < $end) $cout += 86400;
        $diff = ($cout - $end) / 3600;
        return max(0, round($diff, 2));
    }
    // คำนวณชั่วโมงทำงาน
    public function calc_work_hours($checkin, $checkout, $break_min=60) {
        if (!$checkin || !$checkout) return 0;
        $diff = (strtotime($checkout) - strtotime($checkin)) / 3600;
        return max(0, round($diff - ($break_min/60), 2));
    }
}
