<?php defined('BASEPATH') OR exit('No direct script access allowed');
class Salary extends Admin_Controller {

    public function __construct() {
        parent::__construct();
        $this->require_permission('can_manage_salary');
        $this->load->model('Salary_model');
    }

    // ── รายการเงินเดือน ──────────────────────────────────────
    public function index() {
        $y = (int)($this->input->get('year')  ? $this->input->get('year')  : date('Y'));
        $m = (int)($this->input->get('month') ? $this->input->get('month') : date('n'));
        $this->render('admin/salary/index', array(
            'title'      => 'เงินเดือน',
            'page_title' => 'จัดการเงินเดือน',
            'records'    => $this->Salary_model->get_records(array('year'=>$y,'month'=>$m), 200),
            'summary'    => $this->Salary_model->get_monthly_summary($y, $m),
            'year'       => $y,
            'month'      => $m,
            'employees'  => $this->User_model->get_all(array('status'=>'active'), 300),
        ));
    }

    // ── บันทึกเงินเดือน ──────────────────────────────────────
    public function create() {
        $this->render('admin/salary/form', array(
            'title'      => 'บันทึกเงินเดือน',
            'page_title' => 'บันทึกเงินเดือน',
            'employees'  => $this->User_model->get_all(array('status'=>'active'), 300),
            'rec'        => null,
            'year'       => date('Y'),
            'month'      => date('n'),
        ));
    }

    public function store() {
        if ($this->input->method() !== 'post') redirect('admin/salary');
        $d  = $this->_fd();
        $id = $this->Salary_model->calc_and_save($d);
        if ($id) {
            $this->Notification_model->create(array(
                'user_id'   => $d['user_id'],
                'sender_id' => $this->current_user->user_id,
                'type'      => 'salary_paid',
                'title'     => 'บันทึกเงินเดือนแล้ว',
                'message'   => 'เงินเดือน '.$d['salary_month'].'/'.$d['salary_year'].' ประมวลผลแล้ว',
                'link'      => base_url('employee/salary'),
            ));
            $this->session->set_flashdata('success', 'บันทึกสำเร็จ');
        } else {
            $this->session->set_flashdata('error', 'เกิดข้อผิดพลาด หรือมีข้อมูลซ้ำ');
        }
        redirect('admin/salary');
    }

    // ── แก้ไขเงินเดือน ───────────────────────────────────────
    public function edit($id) {
        $r = $this->Salary_model->get_by_id($id);
        if (!$r) redirect('admin/salary');
        $this->render('admin/salary/form', array(
            'title'      => 'แก้ไขเงินเดือน',
            'page_title' => 'แก้ไขเงินเดือน',
            'employees'  => $this->User_model->get_all(array('status'=>'active'), 300),
            'rec'        => $r,
            'year'       => $r->salary_year,
            'month'      => $r->salary_month,
        ));
    }

    public function update($id) {
        if ($this->input->method() !== 'post') redirect('admin/salary');
        $this->Salary_model->update($id, $this->_fd());
        $this->session->set_flashdata('success', 'แก้ไขสำเร็จ');
        redirect('admin/salary');
    }

    // ── เปลี่ยนสถานะเป็นจ่ายแล้ว ─────────────────────────────
    public function mark_paid($id) {
        $r = $this->Salary_model->get_by_id($id);
        $this->Salary_model->mark_paid($id);
        if ($r) {
            $this->Notification_model->create(array(
                'user_id'   => $r->user_id,
                'sender_id' => $this->current_user->user_id,
                'type'      => 'salary_paid',
                'title'     => 'เงินเดือนโอนแล้ว',
                'message'   => 'เงินเดือน '.$r->salary_month.'/'.$r->salary_year.' โอนเข้าบัญชีแล้ว',
                'link'      => base_url('employee/salary'),
            ));
        }
        $this->session->set_flashdata('success', 'อัปเดตสำเร็จ');
        redirect('admin/salary');
    }

    // ── รายการสลิปเงินเดือน (admin เห็นทุกคน) ───────────────
    public function slips() {
        $y    = (int)($this->input->get('year')  ? $this->input->get('year')  : date('Y'));
        $m    = (int)($this->input->get('month') ? $this->input->get('month') : 0);
        $uid  = (int)$this->input->get('user_id');
        
        $this->db->select('ss.*,u.first_name,u.last_name,u.employee_id,d.name AS dept_name')
            ->from('salary_slips ss')
            ->join('users u','u.id=ss.user_id')
            ->join('departments d','d.id=u.department_id','left')
            ->where('ss.slip_year',$y);
        if ($m > 0)   $this->db->where('ss.slip_month',$m);
        if ($uid > 0) $this->db->where('ss.user_id',$uid);
        $slips = $this->db->order_by('ss.slip_year DESC,ss.slip_month DESC,u.employee_id ASC')->get()->result();

        $this->render('admin/salary/slips', array(
            'title'      => 'รายการสลิปเงินเดือน',
            'page_title' => 'รายการสลิปเงินเดือน',
            'slips'      => $slips,
            'employees'  => $this->User_model->get_all(array('status'=>'active'), 300),
            'year'       => $y,
            'month'      => $m,
            'sel_uid'    => $uid,
        ));
    }

    // ── อัปโหลดสลิป (หลายไฟล์) ──────────────────────────────
    public function upload_slip() {
        if ($this->input->method() !== 'post') redirect('admin/salary');

        $upload_path = FCPATH.'uploads/slips/';
        if (!is_dir($upload_path)) mkdir($upload_path, 0755, true);

        $files   = isset($_FILES['slip_files']) ? $_FILES['slip_files'] : array();
        $success = 0;
        $errors  = array();

        if (empty($files['name'][0])) {
            $this->session->set_flashdata('error','กรุณาเลือกไฟล์');
            redirect('admin/salary');
        }

        $month_map = array(1=>'JAN',2=>'FEB',3=>'MAR',4=>'APR',5=>'MAY',6=>'JUN',
                           7=>'JUL',8=>'AUG',9=>'SEP',10=>'OCT',11=>'NOV',12=>'DEC');

        foreach ($files['name'] as $i => $orig_name) {
            if (empty($orig_name)) continue;
            $pattern = '/^(\d{13})_([A-Z]{3})(\d{4})\.pdf$/i';
            if (!preg_match($pattern, $orig_name, $matches)) {
                $errors[] = $orig_name.': ชื่อไฟล์ไม่ถูกต้อง ต้องเป็น {เลขบัตร}_{MON}{ปี}.pdf';
                continue;
            }
            $id_card   = $matches[1];
            $month_str = strtoupper($matches[2]);
            $file_year = (int)$matches[3];
            $month_num = array_search($month_str, $month_map);
            if (!$month_num) { $errors[] = $orig_name.': เดือนไม่ถูกต้อง'; continue; }
            $user = $this->db->where('id_card_number',$id_card)->where('status','active')->get('users')->row();
            if (!$user) { $errors[] = $orig_name.': ไม่พบพนักงานเลขบัตร '.$id_card; continue; }
            $ext = strtolower(pathinfo($orig_name, PATHINFO_EXTENSION));
            if ($ext !== 'pdf') { $errors[] = $orig_name.': ต้องเป็น PDF'; continue; }
            if ($files['size'][$i] > 10485760) { $errors[] = $orig_name.': ไฟล์ใหญ่เกิน 10MB'; continue; }
            $save = uniqid().'_'.$orig_name;
            if (move_uploaded_file($files['tmp_name'][$i], $upload_path.$save)) {
                $this->Salary_model->save_slip(array(
                    'user_id'=>$user->id,'slip_year'=>$file_year,'slip_month'=>$month_num,
                    'file_name'=>$orig_name,'file_path'=>'uploads/slips/'.$save,
                    'file_size'=>$files['size'][$i],'uploaded_by'=>$this->current_user->user_id,
                ));
                $this->Notification_model->create(array(
                    'user_id'=>$user->id,'sender_id'=>$this->current_user->user_id,
                    'type'=>'document_uploaded','title'=>'สลิปเงินเดือนพร้อมแล้ว',
                    'message'=>'สลิป '.$month_str.' '.$file_year.' พร้อมดาวน์โหลด',
                    'link'=>base_url('employee/salary'),
                ));
                $success++;
            } else {
                $errors[] = $orig_name.': อัปโหลดล้มเหลว';
            }
        }

        if ($success > 0) {
            $msg = 'อัปโหลดสำเร็จ '.$success.' ไฟล์';
            if (!empty($errors)) $msg .= ' (มีข้อผิดพลาด '.count($errors).' ไฟล์)';
            $this->session->set_flashdata('success', $msg);
        }
        if (!empty($errors) && $success === 0) {
            $this->session->set_flashdata('error', implode('<br>', $errors));
        } elseif (!empty($errors)) {
            $this->session->set_flashdata('warning', implode('<br>', $errors));
        }
        redirect('admin/salary');
    }

    // ── โบนัสประจำปี ─────────────────────────────────────────
    public function bonus() {
        $y = (int)($this->input->get('year') ? $this->input->get('year') : date('Y'));
        $this->render('admin/salary/bonus', array(
            'title'      => 'โบนัสประจำปี',
            'page_title' => 'โบนัสประจำปี',
            'bonuses'    => $this->Salary_model->get_bonuses(array('year'=>$y)),
            'employees'  => $this->User_model->get_all(array('status'=>'active'), 300),
            'year'       => $y,
        ));
    }

    public function store_bonus() {
        if ($this->input->method() !== 'post') redirect('admin/salary/bonus');
        $d = array(
            'user_id'      => $this->input->post('user_id'),
            'bonus_year'   => $this->input->post('bonus_year'),
            'amount'       => (float)$this->input->post('amount'),
            'remarks'      => $this->input->post('remarks', TRUE),
            'payment_date' => $this->input->post('payment_date') ? $this->input->post('payment_date') : null,
            'created_by'   => $this->current_user->user_id,
        );
        $id = $this->Salary_model->save_bonus($d);
        if ($id) {
            $this->Notification_model->create(array(
                'user_id'   => $d['user_id'],
                'sender_id' => $this->current_user->user_id,
                'type'      => 'bonus_paid',
                'title'     => 'ได้รับโบนัสประจำปี',
                'message'   => 'โบนัสปี '.$d['bonus_year'].' จำนวน '.number_format($d['amount'],0).' บาท',
                'link'      => base_url('employee/salary'),
            ));
        }
        $this->session->set_flashdata('success', 'บันทึกโบนัสสำเร็จ');
        redirect('admin/salary/bonus');
    }

    // ── ทวิ 50 — รายการ + อัปโหลด ───────────────────────────
    public function tax_docs() {
        $y   = (int)($this->input->get('year') ? $this->input->get('year') : date('Y'));
        $uid = (int)$this->input->get('user_id');

        $this->db->select('td.*,u.first_name,u.last_name,u.employee_id,d.name AS dept_name')
            ->from('tax_documents td')
            ->join('users u','u.id=td.user_id')
            ->join('departments d','d.id=u.department_id','left')
            ->where('td.tax_year',$y);
        if ($uid > 0) $this->db->where('td.user_id',$uid);
        $tax_list = $this->db->order_by('u.employee_id ASC')->get()->result();

        $this->render('admin/salary/tax_docs', array(
            'title'      => 'ทวิ 50',
            'page_title' => 'เอกสารทวิ 50 (ภ.ง.ด.1ก)',
            'employees'  => $this->User_model->get_all(array('status'=>'active'), 300),
            'tax_list'   => $tax_list,
            'year'       => $y,
            'sel_uid'    => $uid,
        ));
    }

    public function upload_tax() {
        if ($this->input->method() !== 'post') redirect('admin/salary/tax_docs');
        $uid = $this->input->post('user_id');
        $y   = $this->input->post('tax_year');
        $p   = FCPATH.'uploads/tax_docs/';
        if (!is_dir($p)) mkdir($p, 0755, true);
        if (empty($_FILES['tax_file']['tmp_name']) || $_FILES['tax_file']['error'] !== UPLOAD_ERR_OK) {
            $this->session->set_flashdata('error','กรุณาเลือกไฟล์');
            redirect('admin/salary/tax_docs');
        }
        $orig = $_FILES['tax_file']['name'];
        $ext  = strtolower(pathinfo($orig, PATHINFO_EXTENSION));
        if ($ext !== 'pdf') {
            $this->session->set_flashdata('error','รองรับเฉพาะ PDF');
            redirect('admin/salary/tax_docs');
        }
        if ($_FILES['tax_file']['size'] > 10485760) {
            $this->session->set_flashdata('error','ไฟล์ใหญ่เกิน 10MB');
            redirect('admin/salary/tax_docs');
        }
        $fn = uniqid().'_'.$orig;
        if (move_uploaded_file($_FILES['tax_file']['tmp_name'], $p.$fn)) {
            $this->Salary_model->save_tax_doc(array(
                'user_id'     => $uid,
                'tax_year'    => $y,
                'file_name'   => $orig,
                'file_path'   => 'uploads/tax_docs/'.$fn,
                'file_size'   => $_FILES['tax_file']['size'],
                'uploaded_by' => $this->current_user->user_id,
            ));
            $this->Notification_model->create(array(
                'user_id'   => $uid,
                'sender_id' => $this->current_user->user_id,
                'type'      => 'document_uploaded',
                'title'     => 'ทวิ 50 พร้อมแล้ว',
                'message'   => 'เอกสารทวิ 50 ปี '.$y.' พร้อมดาวน์โหลด',
                'link'      => base_url('employee/salary'),
            ));
            $this->session->set_flashdata('success','อัปโหลดสำเร็จ');
        } else {
            $this->session->set_flashdata('error','อัปโหลดล้มเหลว');
        }
        redirect('admin/salary/tax_docs');
    }

    public function delete_slip($id) {
        $s = $this->db->where('id',$id)->get('salary_slips')->row();
        if ($s) {
            if (!empty($s->file_path) && file_exists(FCPATH.$s->file_path)) @unlink(FCPATH.$s->file_path);
            $this->db->where('id',$id)->delete('salary_slips');
            $this->session->set_flashdata('success','ลบสลิปสำเร็จ');
        }
        redirect('admin/salary/slips');
    }

    public function delete_tax($id) {
        $t = $this->db->where('id',$id)->get('tax_documents')->row();
        if ($t) {
            if (!empty($t->file_path) && file_exists(FCPATH.$t->file_path)) @unlink(FCPATH.$t->file_path);
            $this->db->where('id',$id)->delete('tax_documents');
            $this->session->set_flashdata('success','ลบเอกสารสำเร็จ');
        }
        redirect('admin/salary/tax_docs');
    }

    private function _fd() {
        return array(
            'user_id'               => $this->input->post('user_id'),
            'salary_year'           => $this->input->post('salary_year'),
            'salary_month'          => $this->input->post('salary_month'),
            'base_salary'           => (float)$this->input->post('base_salary'),
            'commission'            => (float)$this->input->post('commission'),
            'ot_pay'                => (float)$this->input->post('ot_pay'),
            'monthly_bonus'         => (float)$this->input->post('monthly_bonus'),
            'special_bonus'         => (float)$this->input->post('special_bonus'),
            'other_income'          => (float)$this->input->post('other_income'),
            'social_security_deduct'=> (float)$this->input->post('social_security_deduct'),
            'tax_deduct'            => (float)$this->input->post('tax_deduct'),
            'other_deduct'          => (float)$this->input->post('other_deduct'),
            'absent_deduct'         => (float)$this->input->post('absent_deduct'),
            'late_deduct'           => (float)$this->input->post('late_deduct'),
            'note'                  => $this->input->post('note', TRUE),
            'payment_status'        => $this->input->post('payment_status') ? $this->input->post('payment_status') : 'draft',
            'created_by'            => $this->current_user->user_id,
        );
    }
}
