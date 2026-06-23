<?php defined('BASEPATH') OR exit('No direct script access allowed');
class Notification_model extends CI_Model {
    public function create($data) { $data['created_at']=date('Y-m-d H:i:s'); $this->db->insert('notifications',$data); return $this->db->insert_id(); }
    public function send_to_all($sid,$type,$title,$msg,$link='') {
        foreach($this->db->where('status','active')->get('users')->result() as $u)
            $this->create(array('user_id'=>$u->id,'sender_id'=>$sid,'type'=>$type,'title'=>$title,'message'=>$msg,'link'=>$link));
    }
    public function send_to_role($sid,$slug,$type,$title,$msg,$link='') {
        $users = $this->db->select('u.id')->from('users u')->join('roles r','r.id=u.role_id')
            ->where('r.slug',$slug)->where('u.status','active')->get()->result();
        foreach($users as $u) $this->create(array('user_id'=>$u->id,'sender_id'=>$sid,'type'=>$type,'title'=>$title,'message'=>$msg,'link'=>$link));
    }

    /**
     * แจ้งเตือนเฉพาะหัวหน้าทีมเดียวกับ sender + admin + owner
     * manager ทีมอื่นจะไม่ได้รับแจ้งเตือน
     */
    public function send_to_team_manager($sid,$type,$title,$msg,$link='') {
        // หา team_id ของ sender
        $sender = $this->db->select('team_id')->where('id',$sid)->get('users')->row();
        $team_id = $sender ? $sender->team_id : null;
        $notified = array();

        // หัวหน้าทีมเดียวกัน
        if ($team_id) {
            $managers = $this->db->select('u.id')->from('users u')
                ->join('roles r','r.id=u.role_id')
                ->where('r.slug','manager')
                ->where('u.team_id',$team_id)
                ->where('u.status','active')
                ->where('u.id !=',$sid)
                ->get()->result();
            foreach ($managers as $u) {
                if (!in_array($u->id,$notified)) {
                    $this->create(array('user_id'=>$u->id,'sender_id'=>$sid,'type'=>$type,'title'=>$title,'message'=>$msg,'link'=>$link));
                    $notified[] = $u->id;
                }
            }
        }
        // ถ้าไม่มีหัวหน้าในทีม ส่งให้ manager ทุกคน
        if (empty($notified)) {
            $all_mgr = $this->db->select('u.id')->from('users u')
                ->join('roles r','r.id=u.role_id')
                ->where('r.slug','manager')
                ->where('u.status','active')
                ->where('u.id !=',$sid)
                ->get()->result();
            foreach ($all_mgr as $u) {
                if (!in_array($u->id,$notified)) {
                    $this->create(array('user_id'=>$u->id,'sender_id'=>$sid,'type'=>$type,'title'=>$title,'message'=>$msg,'link'=>$link));
                    $notified[] = $u->id;
                }
            }
        }
        // admin + owner เสมอ
        $ao = $this->db->select('u.id')->from('users u')
            ->join('roles r','r.id=u.role_id')
            ->where_in('r.slug',array('admin','owner'))
            ->where('u.status','active')
            ->where('u.id !=',$sid)
            ->get()->result();
        foreach ($ao as $u) {
            if (!in_array($u->id,$notified)) {
                $this->create(array('user_id'=>$u->id,'sender_id'=>$sid,'type'=>$type,'title'=>$title,'message'=>$msg,'link'=>$link));
                $notified[] = $u->id;
            }
        }
    }
    public function count_unread($uid) { return $this->db->where('user_id',$uid)->where('is_read',0)->count_all_results('notifications'); }
    public function get_recent($uid,$n=5) { return $this->db->where('user_id',$uid)->order_by('created_at','DESC')->limit($n)->get('notifications')->result(); }
    public function get_all($uid,$limit=30,$offset=0) { return $this->db->where('user_id',$uid)->order_by('created_at','DESC')->limit($limit,$offset)->get('notifications')->result(); }
    public function mark_read($id,$uid) { $this->db->where('id',$id)->where('user_id',$uid)->update('notifications',array('is_read'=>1)); }
    public function mark_all_read($uid) { $this->db->where('user_id',$uid)->where('is_read',0)->update('notifications',array('is_read'=>1)); }
}
