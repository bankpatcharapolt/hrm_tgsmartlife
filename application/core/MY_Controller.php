<?php defined('BASEPATH') OR exit('No direct script access allowed');
class MY_Controller extends CI_Controller {
    protected $current_user = null;
    protected $view_data = array();
    protected $layout = 'layouts/main';
    public function __construct() {
        parent::__construct();
        $this->load->model(array('User_model','Notification_model'));
        $this->load->helper(array('url','form'));
        if ($this->session->userdata('logged_in')) {
            $this->current_user = (object)$this->session->userdata();
        }
        $unread = 0; $notifs = array();
        if ($this->current_user && !empty($this->current_user->user_id)) {
            $unread = $this->Notification_model->count_unread($this->current_user->user_id);
            $notifs = $this->Notification_model->get_recent($this->current_user->user_id, 5);
        }
        $this->view_data = array(
            'current_user'          => $this->current_user,
            'unread_notifications'  => $unread,
            'recent_notifications'  => $notifs,
            'flash_success'         => $this->session->flashdata('success'),
            'flash_error'           => $this->session->flashdata('error'),
            'flash_warning'         => $this->session->flashdata('warning'),
            'flash_info'            => $this->session->flashdata('info'),
        );
    }
    protected function require_login() {
        if (!$this->session->userdata('logged_in')) {
            $this->session->set_flashdata('error', 'กรุณาเข้าสู่ระบบก่อนใช้งาน');
            redirect('auth/login');
        }
        if (time() - (int)$this->session->userdata('login_time') > 28800) {
            $this->session->sess_destroy();
            $this->session->set_flashdata('error', 'เซสชันหมดอายุ กรุณาเข้าสู่ระบบใหม่');
            redirect('auth/login');
        }
    }
    protected function require_permission($perm) {
        $this->require_login();
        if ($this->session->userdata('is_full_access') == true || $this->session->userdata('is_full_access') === '1' || $this->session->userdata('is_full_access') === 1) return;
        if (!$this->session->userdata($perm)) $this->show_403();
    }
    protected function require_role($roles) {
        $this->require_login();
        if ($this->session->userdata('is_full_access') == true || $this->session->userdata('is_full_access') === '1' || $this->session->userdata('is_full_access') === 1) return;
        if (!in_array($this->session->userdata('role_slug'), (array)$roles)) $this->show_403();
    }
    protected function render($view, $data = array()) {
        $d = array_merge($this->view_data, $data);
        // ป้องกัน CI3 inject $mimes และ CI vars เข้าสู่ view
        $ci_vars = array('mimes','config','BM','CFG','URI','RTR','OUT','SEC',
                         'upload','load','db','session','input','output','security',
                         'autoload','router','benchmark');
        foreach ($ci_vars as $k) unset($d[$k]);

        // CI3 inject $mimes ผ่าน _ci_cached_vars — ต้อง clear ก่อน load view
        $CI =& get_instance();
        if (isset($CI->load) && isset($CI->load->_ci_cached_vars['mimes'])) {
            // unset ใน cached_vars โดยตรงผ่าน reference
            $cached = &$CI->load->_ci_cached_vars;
            if (is_array($cached)) unset($cached['mimes']);
        }

        if ($this->layout) {
            $d['content_view'] = $this->load->view($view, $d, true);
            $this->load->view($this->layout, $d);
        } else {
            $this->load->view($view, $d);
        }
    }
    protected function json_ok($data = null, $msg = '') {
        $this->output->set_content_type('application/json', 'utf-8')
            ->set_output(json_encode(array('success'=>true,'message'=>$msg,'data'=>$data), JSON_UNESCAPED_UNICODE));
    }
    protected function json_err($msg, $code = 400) {
        $this->output->set_status_header($code)->set_content_type('application/json', 'utf-8')
            ->set_output(json_encode(array('success'=>false,'message'=>$msg), JSON_UNESCAPED_UNICODE));
    }
    protected function show_403() {
        $this->output->set_status_header(403);
        $this->load->view('errors/403', array_merge($this->view_data, array('title'=>'ไม่มีสิทธิ์เข้าถึง')));
        exit;
    }
}
class Admin_Controller extends MY_Controller {
    public function __construct() { parent::__construct(); $this->require_role(array('admin','owner')); $this->layout = 'layouts/admin'; }
}
class Manager_Controller extends MY_Controller {
    public function __construct() { parent::__construct(); $this->require_role(array('manager','admin','owner')); $this->layout = 'layouts/main'; }
}
class Employee_Controller extends MY_Controller {
    public function __construct() { parent::__construct(); $this->require_login(); $this->layout = 'layouts/main'; }
}
