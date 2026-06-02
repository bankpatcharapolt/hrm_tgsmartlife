<?php defined('BASEPATH') OR exit('No direct script access allowed');
class Notifications extends MY_Controller {
    public function __construct() { parent::__construct(); $this->require_login(); }
    public function unread() { $uid=$this->current_user->user_id; $this->json_ok(array('count'=>$this->Notification_model->count_unread($uid),'items'=>$this->Notification_model->get_recent($uid,5))); }
    public function mark_read($id) { $this->Notification_model->mark_read($id,$this->current_user->user_id); $this->json_ok(); }
}
