<?php defined('BASEPATH') OR exit('No direct script access allowed');
class Leave extends Manager_Controller {
    public function __construct() { parent::__construct(); $this->load->model('Leave_model'); }

    // หา team_id ของ manager ปัจจุบัน
    private function _my_team_id() {
        $u = $this->db->where('id', $this->current_user->user_id)->get('users')->row();
        return $u ? $u->team_id : null;
    }

    public function index() {
        $team_id = $this->_my_team_id();
        $f = array(
            'status'  => $this->input->get('status') ?: 'pending',
            'year'    => $this->input->get('year')   ?: date('Y'),
            'team_id' => $team_id, // กรองเฉพาะทีมตัวเอง
        );
        $this->render('manager/leave/index', array(
            'title'       => 'อนุมัติการลา',
            'page_title'  => 'อนุมัติการลา',
            'requests'    => $this->Leave_model->get_requests($f, 100),
            'leave_types' => $this->Leave_model->get_types(),
            'filters'     => $f,
            'my_team_id'  => $team_id,
        ));
    }

    public function approve($id) {
        // ตรวจสอบว่าคำขอนี้เป็นของทีมตัวเองก่อนอนุมัติ
        $r = $this->Leave_model->get_by_id($id);
        if ($r && $this->_is_my_team_member($r->user_id)) {
            if ($this->Leave_model->approve($id, $this->current_user->user_id, $this->input->post('note', TRUE))) {
                $this->Notification_model->create(array(
                    'user_id'   => $r->user_id,
                    'sender_id' => $this->current_user->user_id,
                    'type'      => 'leave_approved',
                    'title'     => 'คำขอลาได้รับการอนุมัติ',
                    'message'   => 'การลาวันที่ ' . $r->start_date . ' อนุมัติแล้ว',
                    'link'      => base_url('employee/leave'),
                ));
            }
        }
        $this->session->set_flashdata('success', 'อนุมัติสำเร็จ');
        redirect('manager/leave');
    }

    public function reject($id) {
        $r    = $this->Leave_model->get_by_id($id);
        $note = $this->input->post('note', TRUE);
        if ($r && $this->_is_my_team_member($r->user_id)) {
            if ($this->Leave_model->reject($id, $this->current_user->user_id, $note)) {
                $this->Notification_model->create(array(
                    'user_id'   => $r->user_id,
                    'sender_id' => $this->current_user->user_id,
                    'type'      => 'leave_rejected',
                    'title'     => 'คำขอลาถูกปฏิเสธ',
                    'message'   => 'การลาวันที่ ' . $r->start_date . ' ถูกปฏิเสธ' . ($note ? ': ' . $note : ''),
                    'link'      => base_url('employee/leave'),
                ));
            }
        }
        $this->session->set_flashdata('warning', 'ปฏิเสธสำเร็จ');
        redirect('manager/leave');
    }

    // ตรวจสอบว่า user_id อยู่ในทีมเดียวกับ manager
    private function _is_my_team_member($user_id) {
        $team_id = $this->_my_team_id();
        if (!$team_id) return true; // manager ไม่มีทีม → อนุมัติได้ทุกคน
        $u = $this->db->where('id', $user_id)->where('team_id', $team_id)->get('users')->row();
        return !empty($u);
    }
}
