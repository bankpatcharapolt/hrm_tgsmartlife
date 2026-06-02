<?php defined('BASEPATH') OR exit('No direct script access allowed');
class Profile extends Employee_Controller {
    public function __construct() {
        parent::__construct();
        // [แก้ไข ข้อ 5] ไม่ใช้ CI Upload library เพราะ MY_Upload มี conflict กับ Sales property
        // จัดการ upload โดยตรงแทน
    }

    public function index() {
        $this->render('employee/profile/index', array(
            'title'      => 'โปรไฟล์ของฉัน',
            'page_title' => 'โปรไฟล์ของฉัน',
            'emp'        => $this->User_model->get_by_id($this->current_user->user_id),
        ));
    }

    public function update() {
        if ($this->input->method() !== 'post') redirect('employee/profile');

        $uid = $this->current_user->user_id;
        $d = array(
            'phone'    => $this->input->post('phone',    TRUE),
            'email'    => $this->input->post('email',    TRUE),
            'address'  => $this->input->post('address',  TRUE),
            'nickname' => $this->input->post('nickname', TRUE),
        );

        // เปลี่ยนรหัสผ่าน
        $pw  = $this->input->post('new_password');
        $cpw = $this->input->post('confirm_password');
        if ($pw) {
            if ($pw !== $cpw) {
                $this->session->set_flashdata('error', 'รหัสผ่านไม่ตรงกัน');
                redirect('employee/profile');
            }
            $d['password'] = $pw;
        }

        // [แก้ไข ข้อ 5] อัปโหลดรูปภาพด้วย native PHP ไม่ใช้ CI Upload library
        if (!empty($_FILES['photo']['name']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
            $allowed_types = array('image/jpeg', 'image/png', 'image/webp', 'image/gif');
            $max_size      = 2 * 1024 * 1024; // 2MB

            $file_tmp  = $_FILES['photo']['tmp_name'];
            $file_size = $_FILES['photo']['size'];
            $file_type = mime_content_type($file_tmp); // ใช้ mime_content_type แทน $_FILES type

            if (!in_array($file_type, $allowed_types)) {
                $this->session->set_flashdata('error', 'รองรับเฉพาะไฟล์ jpg, png, webp, gif');
                redirect('employee/profile');
            }
            if ($file_size > $max_size) {
                $this->session->set_flashdata('error', 'ไฟล์รูปภาพขนาดใหญ่เกิน 2MB');
                redirect('employee/profile');
            }

            $upload_path = FCPATH . 'uploads/photos/';
            if (!is_dir($upload_path)) mkdir($upload_path, 0755, true);

            // กำหนดนามสกุลไฟล์จาก mime type
            $ext_map = array('image/jpeg'=>'jpg','image/png'=>'png','image/webp'=>'webp','image/gif'=>'gif');
            $ext = $ext_map[$file_type] ?? 'jpg';

            $new_name = uniqid('photo_') . '.' . $ext;
            $dest     = $upload_path . $new_name;

            if (move_uploaded_file($file_tmp, $dest)) {
                // ลบรูปเก่าถ้ามี
                $old = $this->User_model->get_by_id($uid);
                if (!empty($old->photo) && file_exists(FCPATH . $old->photo)) {
                    @unlink(FCPATH . $old->photo);
                }
                $d['photo'] = 'uploads/photos/' . $new_name;
            } else {
                $this->session->set_flashdata('error', 'อัปโหลดรูปล้มเหลว กรุณาลองใหม่');
                redirect('employee/profile');
            }
        }

        $this->User_model->update($uid, $d);
        $this->session->set_flashdata('success', 'อัปเดตสำเร็จ');
        redirect('employee/profile');
    }
}
