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
     * manager ได้รับ link ของ manager, admin/owner ได้รับ link ของ admin
     *
     * @param int    $sid         sender user_id
     * @param string $type        notification type
     * @param string $title       หัวข้อ
     * @param string $msg         ข้อความ
     * @param string $manager_link  URL สำหรับ manager (เช่น manager/attendance)
     * @param string $admin_link    URL สำหรับ admin/owner (เช่น admin/attendance) ถ้าว่างใช้ manager_link
     */
    public function send_to_team_manager($sid, $type, $title, $msg, $manager_link = '', $admin_link = '') {
        if (empty($admin_link)) $admin_link = $manager_link;

        // หา team_id ของ sender
        $sender  = $this->db->select('team_id')->where('id', $sid)->get('users')->row();
        $team_id = $sender ? $sender->team_id : null;

        // ── ขั้นที่ 1: รวบรวม id ของ admin + owner ก่อน ────────────────────
        // เพื่อ exclude ออกจาก manager query อย่างเด็ดขาด
        $ao_ids   = array();
        $ao_rows  = $this->db->select('u.id')->from('users u')
            ->join('roles r', 'r.id=u.role_id')
            ->where_in('r.slug', array('admin', 'owner'))
            ->where('u.status', 'active')
            ->get()->result();
        foreach ($ao_rows as $u) $ao_ids[] = (int)$u->id;

        // ── ขั้นที่ 2: notify admin + owner ด้วย admin_link เสมอ ───────────
        $notified = array();
        foreach ($ao_rows as $u) {
            if ((int)$u->id === (int)$sid) continue; // ไม่ส่งหา sender ตัวเอง
            $this->create(array(
                'user_id'   => $u->id,
                'sender_id' => $sid,
                'type'      => $type,
                'title'     => $title,
                'message'   => $msg,
                'link'      => $admin_link, // admin/owner ได้ admin_link เสมอ
            ));
            $notified[] = (int)$u->id;
        }

        // ── ขั้นที่ 3: หัวหน้าทีมเดียวกัน (role=manager เท่านั้น) ────────────
        // exclude admin/owner id ออกเพื่อป้องกัน override
        $manager_found = false;
        if ($team_id) {
            $q = $this->db->select('u.id')->from('users u')
                ->join('roles r', 'r.id=u.role_id')
                ->where('r.slug', 'manager')
                ->where('u.team_id', $team_id)
                ->where('u.status', 'active')
                ->where('u.id !=', $sid);
            if (!empty($ao_ids)) $q->where_not_in('u.id', $ao_ids);
            $managers = $q->get()->result();
            foreach ($managers as $u) {
                if (!in_array((int)$u->id, $notified)) {
                    $this->create(array(
                        'user_id'   => $u->id,
                        'sender_id' => $sid,
                        'type'      => $type,
                        'title'     => $title,
                        'message'   => $msg,
                        'link'      => $manager_link,
                    ));
                    $notified[]    = (int)$u->id;
                    $manager_found = true;
                }
            }
        }

        // ── ขั้นที่ 4: fallback — เฉพาะเมื่อพนักงานไม่มี team_id เลย ─────────
        // ถ้ามีทีมแต่ทีมนั้นไม่มีหัวหน้า → ไม่ส่งข้ามทีม (admin/owner ได้รับไปแล้ว)
        if (!$manager_found && !$team_id) {
            $q = $this->db->select('u.id')->from('users u')
                ->join('roles r', 'r.id=u.role_id')
                ->where('r.slug', 'manager')
                ->where('u.status', 'active')
                ->where('u.id !=', $sid);
            if (!empty($ao_ids)) $q->where_not_in('u.id', $ao_ids);
            $all_mgr = $q->get()->result();
            foreach ($all_mgr as $u) {
                if (!in_array((int)$u->id, $notified)) {
                    $this->create(array(
                        'user_id'   => $u->id,
                        'sender_id' => $sid,
                        'type'      => $type,
                        'title'     => $title,
                        'message'   => $msg,
                        'link'      => $manager_link,
                    ));
                    $notified[] = (int)$u->id;
                }
            }
        }
    }
    public function count_unread($uid) { return $this->db->where('user_id',$uid)->where('is_read',0)->count_all_results('notifications'); }
    public function get_recent($uid,$n=5) { return $this->db->where('user_id',$uid)->order_by('created_at','DESC')->limit($n)->get('notifications')->result(); }
    public function get_all($uid,$limit=30,$offset=0) { return $this->db->where('user_id',$uid)->order_by('created_at','DESC')->limit($limit,$offset)->get('notifications')->result(); }
    public function mark_read($id,$uid) { $this->db->where('id',$id)->where('user_id',$uid)->update('notifications',array('is_read'=>1)); }
    public function mark_all_read($uid) { $this->db->where('user_id',$uid)->where('is_read',0)->update('notifications',array('is_read'=>1)); }
}
