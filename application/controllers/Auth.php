<?php defined('BASEPATH') OR exit('No direct script access allowed');
class Auth extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('User_model');
        $this->load->library(array('session', 'form_validation'));
        $this->load->helper(array('url', 'form'));
    }
    public function index()
    {
        redirect('auth/login');
    }
    public function login()
    {
      
        if ($this->session->userdata('logged_in'))
        
            redirect($this->_dash());
        $this->load->view('auth/login', array('title' => 'เข้าสู่ระบบ | ระบบ HRM', 'error' => $this->session->flashdata('error'), 'success' => $this->session->flashdata('success')));
    }
    public function process_login()
    {     
        if ($this->input->method() !== 'post')
            show_404();
        $u = trim($this->input->post('username', TRUE));
        $p = $this->input->post('password');
        if (empty($u) || empty($p)) {
            $this->session->set_flashdata('error', 'กรุณากรอกชื่อผู้ใช้และรหัสผ่าน');
            redirect('auth/login');
        }
        $user = $this->User_model->authenticate($u, $p);
        if (!$user) {
            $this->session->set_flashdata('error', 'ชื่อผู้ใช้หรือรหัสผ่านไม่ถูกต้อง');
            redirect('auth/login');
        }

        $this->session->set_userdata(array(
            'user_id' => $user->id,
            'employee_id' => $user->employee_id,
            'username' => $user->username,
            'full_name' => $user->first_name . ' ' . $user->last_name,
            'first_name' => $user->first_name,
            'last_name' => $user->last_name,
            'photo' => $user->photo,
            'role_id' => $user->role_id,
            'role_name' => $user->role_name,
            'role_slug' => $user->role_slug,
            'department_name' => $user->department_name,
            'can_checkin' => (bool) $user->can_checkin,
            'can_view_own_salary' => (bool) $user->can_view_own_salary,
            'can_approve_leave' => (bool) $user->can_approve_leave,
            'can_manage_employees' => (bool) $user->can_manage_employees,
            'can_view_sales' => (bool) $user->can_view_sales,
            'can_send_notifications' => (bool) $user->can_send_notifications,
            'can_manage_salary' => (bool) $user->can_manage_salary,
            'can_upload_documents' => (bool) $user->can_upload_documents,
            'can_view_reports' => (bool) $user->can_view_reports,
            'can_monitor_attendance' => (bool) $user->can_monitor_attendance,
            'is_full_access' => (bool) $user->is_full_access,
            'logged_in' => TRUE,
            'login_time' => time()
        ));
        $this->User_model->log($user->id, 'login', 'auth', 'เข้าสู่ระบบ: ' . $u);
        redirect($this->_dash($user->role_slug));
    }
    public function logout()
    {
        $uid = $this->session->userdata('user_id');
        if ($uid)
            $this->User_model->log($uid, 'logout', 'auth', 'ออกจากระบบ');
        $this->session->sess_destroy();
        redirect('auth/login');
    }
    private function _dash($slug = null)
    {
        if (!$slug) $slug = $this->session->userdata('role_slug');
        switch ($slug) {
            case 'owner':
            case 'admin':
                return site_url('admin/dashboard');
            case 'manager':
                return site_url('employee/dashboard');
            default:
                return site_url('employee/dashboard');
        }
    }
}
