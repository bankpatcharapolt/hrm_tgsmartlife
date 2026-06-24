<?php defined('BASEPATH') OR exit('No direct script access allowed');
// ─────────────────────────────────────────────────────────────
// Import/Export พนักงาน ด้วย PHP ZipArchive เท่านั้น
// ไม่พึ่ง Python, PhpSpreadsheet หรือ library ภายนอก
// ─────────────────────────────────────────────────────────────
class Employees_import extends Admin_Controller {

    const HQ_TEAM_NAME = 'สำนักงานใหญ่';

    public function __construct() {
        parent::__construct();
        $this->require_permission('can_manage_employees');
        $this->_ensure_hq_team();
    }

    // สร้าง/หา team "สำนักงานใหญ่" ไว้เสมอ
    private function _ensure_hq_team() {
        $t = $this->db->where('team_name', self::HQ_TEAM_NAME)->get('teams')->row();
        if (!$t) {
            $this->db->insert('teams', array(
                'team_code'   => 'HQ',
                'team_name'   => self::HQ_TEAM_NAME,
                'location'    => 'สำนักงานใหญ่',
                'is_active'   => 1,
                'created_at'  => date('Y-m-d H:i:s'),
                'updated_at'  => date('Y-m-d H:i:s'),
            ));
        }
    }

    private function _hq_team_id() {
        $t = $this->db->where('team_name', self::HQ_TEAM_NAME)->get('teams')->row();
        return $t ? $t->id : null;
    }

    // ════ หน้า Import ════════════════════════════════════════
    public function import() {
        $this->render('admin/employees/import', array(
            'title'      => 'นำเข้าข้อมูลพนักงาน',
            'page_title' => 'นำเข้าข้อมูลพนักงาน (Import Excel)',
        ));
    }

    // ════ รับไฟล์ ════════════════════════════════════════════
    public function do_import() {
        if ($this->input->method() !== 'post') redirect('admin/employees');

        $err_code = isset($_FILES['excel_file']['error']) ? $_FILES['excel_file']['error'] : -1;
        if (empty($_FILES['excel_file']['tmp_name']) || $err_code !== UPLOAD_ERR_OK) {
            $this->session->set_flashdata('error', 'ไม่พบไฟล์ที่อัปโหลด (error: '.$err_code.')');
            redirect('admin/employees_import/import');
        }

        $orig = $_FILES['excel_file']['name'];
        $ext  = strtolower(pathinfo($orig, PATHINFO_EXTENSION));
        $size = (int)$_FILES['excel_file']['size'];

        if (!in_array($ext, array('xlsx', 'xls'))) {
            $this->session->set_flashdata('error', 'รองรับเฉพาะ .xlsx เท่านั้น');
            redirect('admin/employees_import/import');
        }
        if ($size > 20 * 1024 * 1024) {
            $this->session->set_flashdata('error', 'ไฟล์ใหญ่เกิน 20MB');
            redirect('admin/employees_import/import');
        }

        $tmp_dir  = FCPATH.'uploads/tmp/';
        if (!is_dir($tmp_dir)) mkdir($tmp_dir, 0755, true);
        $tmp_file = $tmp_dir.uniqid('import_').'.'.$ext;

        if (!move_uploaded_file($_FILES['excel_file']['tmp_name'], $tmp_file)) {
            $this->session->set_flashdata('error', 'บันทึกไฟล์ชั่วคราวไม่ได้ ตรวจสอบสิทธิ์โฟลเดอร์');
            redirect('admin/employees_import/import');
        }

        $rows = $this->_read_xlsx($tmp_file);
        @unlink($tmp_file);

        if (isset($rows['error'])) {
            $this->session->set_flashdata('error', $rows['error']);
            redirect('admin/employees_import/import');
        }
        if (empty($rows)) {
            $this->session->set_flashdata('error', 'ไฟล์ Excel ไม่มีข้อมูล');
            redirect('admin/employees_import/import');
        }

        $success = 0; $updated = 0; $skipped = 0; $errors = array();
        foreach ($rows as $i => $row) {
            $rn = $i + 2;
            if (empty($row[0])) { $skipped++; continue; }
            $res = $this->_upsert($row, $rn);
            if ($res === 'created')     $success++;
            elseif ($res === 'updated') $updated++;
            elseif (is_string($res))    $errors[] = 'แถว '.$rn.': '.$res;
        }

        $this->session->set_flashdata('import_result', array(
            'success' => $success, 'updated' => $updated,
            'errors'  => $errors,  'skipped' => $skipped,
        ));
        redirect('admin/employees_import/import_result');
    }

    // ════ อ่าน XLSX ด้วย PHP ZipArchive + DOM ════════════════
    private function _read_xlsx($file) {
        if (!class_exists('ZipArchive')) {
            return array('error' => 'PHP extension "zip" ไม่ได้เปิด กรุณาเปิด extension=zip ใน php.ini แล้ว restart Apache');
        }

        $zip = new ZipArchive();
        $res = $zip->open($file);
        if ($res !== true) {
            return array('error' => 'เปิดไฟล์ xlsx ไม่ได้ (code: '.$res.') ไฟล์อาจเสียหาย');
        }

        // ── Shared Strings ──────────────────────────────────
        $strings = array();
        $ss_raw  = $zip->getFromName('xl/sharedStrings.xml');
        if ($ss_raw) {
            $dom = new DOMDocument();
            @$dom->loadXML($ss_raw);
            $ns = 'http://schemas.openxmlformats.org/spreadsheetml/2006/main';
            foreach ($dom->getElementsByTagNameNS($ns, 'si') as $si) {
                $t_nodes = $si->getElementsByTagNameNS($ns, 't');
                $val = '';
                foreach ($t_nodes as $t) $val .= $t->nodeValue;
                $strings[] = $val;
            }
        }

        // ── หา sheet index ของ "รายงานพนักงาน" ─────────────
        $sheet_idx = 1;
        $wb_raw = $zip->getFromName('xl/workbook.xml');
        if ($wb_raw) {
            $dom = new DOMDocument();
            @$dom->loadXML($wb_raw);
            $idx = 1;
            foreach ($dom->getElementsByTagName('sheet') as $s) {
                $name = $s->getAttribute('name');
                if (mb_strpos($name, 'รายงานพนักงาน') !== false) {
                    $sheet_idx = $idx; break;
                }
                $idx++;
            }
        }

        // ── อ่าน Sheet ──────────────────────────────────────
        $sheet_raw = $zip->getFromName('xl/worksheets/sheet'.$sheet_idx.'.xml');
        $zip->close();

        if (!$sheet_raw) {
            return array('error' => 'ไม่พบ sheet "รายงานพนักงาน" ในไฟล์ Excel กรุณาตรวจสอบชื่อ sheet');
        }

        $dom = new DOMDocument();
        @$dom->loadXML($sheet_raw);
        $ns = 'http://schemas.openxmlformats.org/spreadsheetml/2006/main';

        $all_rows = array();
        $is_first = true;
        foreach ($dom->getElementsByTagNameNS($ns, 'row') as $row_el) {
            if ($is_first) { $is_first = false; continue; } // skip header

            $cells   = array();
            $c_nodes = $row_el->getElementsByTagNameNS($ns, 'c');
            foreach ($c_nodes as $c) {
                $addr    = $c->getAttribute('r');
                $col_str = preg_replace('/[0-9]/', '', $addr);
                $col_num = 0;
                for ($ci = 0; $ci < strlen($col_str); $ci++) {
                    $col_num = $col_num * 26 + (ord($col_str[$ci]) - 64);
                }
                $t    = $c->getAttribute('t');
                $v_el = $c->getElementsByTagNameNS($ns, 'v');
                $v    = ($v_el->length > 0) ? $v_el->item(0)->nodeValue : null;

                if ($t === 's' && $v !== null) {
                    $cells[$col_num] = isset($strings[(int)$v]) ? $strings[(int)$v] : null;
                } elseif ($v !== null && $v !== '') {
                    $cells[$col_num] = (is_numeric($v))
                        ? (strpos($v, '.') !== false ? (float)$v : (int)$v)
                        : $v;
                } else {
                    $cells[$col_num] = null;
                }
            }

            if (empty($cells)) continue;
            $max_c = max(array_keys($cells));
            $row_arr = array();
            for ($ci = 1; $ci <= max($max_c, 30); $ci++) {
                $row_arr[] = isset($cells[$ci]) ? $cells[$ci] : null;
            }
            if (trim((string)$row_arr[0]) === '') continue;
            $all_rows[] = $row_arr;
        }

        return $all_rows;
    }

    // ════ Upsert พนักงาน ═════════════════════════════════════
    private function _upsert($row, $rn) {
        // helper อ่าน cell
        $g = function($r, $i) {
            $v = isset($r[$i]) ? $r[$i] : null;
            return ($v !== null) ? trim((string)$v) : '';
        };

        $emp_id      = $g($row, 0);
        $title       = $g($row, 1);
        $fname       = $g($row, 2);
        $lname       = $g($row, 3);
        $fname_en    = $g($row, 4);
        $lname_en    = $g($row, 5);
        $id_card     = $g($row, 6);
        $dept        = $g($row, 7);
        $position    = $g($row, 8);
        $emp_type    = $g($row, 9) ?: 'รายเดือน';
        $email       = $g($row, 10);
        $phone       = $g($row, 11);
        $emg_contact = $g($row, 12);
        $emg_phone   = $g($row, 13);
        $address     = $g($row, 14);
        $sub_dist    = $g($row, 15);
        $district    = $g($row, 16);
        $province    = $g($row, 17);
        $postal      = $g($row, 18);
        $start_raw   = $g($row, 19);
        $salary      = isset($row[20]) ? $row[20] : 0;
        $salary_acc  = $g($row, 21);
        $ss_status   = $g($row, 22);
        $tax         = isset($row[23]) ? $row[23] : 0;
        $pay_channel = $g($row, 24);
        $bank_acc    = $g($row, 25);
        $status_raw  = $g($row, 26);
        $team_raw    = $g($row, 27);
        $username_in = $g($row, 28);
        $password_in = $g($row, 29);

        if (empty($emp_id) || empty($fname) || empty($lname))
            return 'ข้อมูลไม่ครบ (ต้องมีรหัสพนักงาน/ชื่อ/นามสกุล)';

        // แปลงวันที่
        $start_dt = date('Y-m-d');
        if (!empty($start_raw)) {
            if (preg_match('/^(\d{1,2})\/(\d{1,2})\/(\d{4})$/', $start_raw, $m)) {
                $start_dt = sprintf('%04d-%02d-%02d', $m[3], $m[2], $m[1]);
            } elseif (preg_match('/^(\d{4})(\d{2})(\d{2})$/', $start_raw, $m)) {
                $start_dt = $m[1].'-'.$m[2].'-'.$m[3];
            } elseif (preg_match('/^\d{4}-\d{2}-\d{2}$/', $start_raw)) {
                $start_dt = $start_raw;
            } elseif (is_numeric($start_raw) && $start_raw > 1000) {
                // Excel serial date
                $start_dt = date('Y-m-d', mktime(0,0,0,1,1,1900) + ((int)$start_raw - 2) * 86400);
            }
        }

        // ── แปลงสถานะ ────────────────────────────────────────
        // active  : พนักงาน | ใช้งาน | active | ทำงาน | ปกติ | (ว่าง)
        // inactive: ลาออก | ไม่ใช้งาน | ไม่ใช้ | พ้นสภาพ | inactive | ออก
        // suspended: ระงับ | พักงาน | suspended
        $status = 'active'; // default
        $sl = mb_strtolower(trim($status_raw));
        $inactive_keywords  = array('ลาออก','ไม่ใช้งาน','ไม่ใช้','พ้นสภาพ','inactive','ออกงาน','ออกแล้ว','ไม่ active');
        $suspended_keywords = array('ระงับ','พักงาน','suspended');
        $active_keywords    = array('พนักงาน','ใช้งาน','active','ทำงาน','ปกติ','ยังทำงาน');

        $matched = false;
        foreach ($inactive_keywords as $kw) {
            if (mb_strpos($sl, $kw) !== false) { $status = 'inactive'; $matched = true; break; }
        }
        if (!$matched) {
            foreach ($suspended_keywords as $kw) {
                if (mb_strpos($sl, $kw) !== false) { $status = 'suspended'; $matched = true; break; }
            }
        }
        if (!$matched && !empty($sl)) {
            // ถ้ามีค่าแต่ไม่ match กลุ่มไหนเลย → ถือเป็น active (เช่น "พนักงาน", "ใช้งาน")
            $status = 'active';
        }

        // หา/สร้าง department
        $dept_id = null;
        if (!empty($dept)) {
            $d = $this->db->where('name', $dept)->get('departments')->row();
            if ($d) {
                $dept_id = $d->id;
            } else {
                $this->db->insert('departments', array('name'=>$dept,'created_at'=>date('Y-m-d H:i:s')));
                $dept_id = $this->db->insert_id();
            }
        }

        // หา/สร้าง team — ถ้าว่างใช้ "สำนักงานใหญ่"
        $team_id = $this->_hq_team_id(); // default = HQ
        if (!empty($team_raw)) {
            $t = $this->db->where('team_name', $team_raw)->get('teams')->row();
            if ($t) {
                $team_id = $t->id;
            } else {
                $this->db->insert('teams', array(
                    'team_code'  => 'T'.substr(time(),-5),
                    'team_name'  => $team_raw,
                    'is_active'  => 1,
                    'created_at' => date('Y-m-d H:i:s'),
                    'updated_at' => date('Y-m-d H:i:s'),
                ));
                $team_id = $this->db->insert_id();
            }
        }

        $role    = $this->db->where('slug','employee')->get('roles')->row();
        $role_id = $role ? $role->id : 1;

        $data = array(
            'employee_id'            => $emp_id,
            'title'                  => $title,
            'first_name'             => $fname,
            'last_name'              => $lname,
            'first_name_en'          => $fname_en,
            'last_name_en'           => $lname_en,
            'id_card_number'         => $id_card,
            'department_id'          => $dept_id,
            'team_id'                => $team_id,
            'position'               => $position,
            'employee_type'          => in_array($emp_type,array('รายเดือน','รายวัน','พาร์ทไทม์','สัญญาจ้าง'))?$emp_type:'รายเดือน',
            'email'                  => $email,
            'phone'                  => $phone,
            'emergency_contact'      => $emg_contact,
            'emergency_phone'        => $emg_phone,
            'address'                => $address,
            'sub_district'           => $sub_dist,
            'district'               => $district,
            'province'               => $province,
            'postal_code'            => $postal,
            'start_date'             => $start_dt,
            'base_salary'            => is_numeric($salary)?(float)$salary:0,
            'salary_account'         => $salary_acc,
            'social_security_status' => (mb_strpos($ss_status,'ไม่ขึ้น')!==false)
                                        ? 'ไม่ขึ้นทะเบียนประกันสังคม'
                                        : 'ขึ้นทะเบียนประกันสังคม',
            'withholding_tax'        => is_numeric($tax)?(float)$tax:0,
            'payment_channel'        => $pay_channel,
            'bank_account'           => $bank_acc,
            'status'                 => $status,
            'updated_at'             => date('Y-m-d H:i:s'),
        );

        $exists = $this->db->where('employee_id', $emp_id)->get('users')->row();
        if ($exists) {
            $this->db->where('id', $exists->id)->update('users', $data);
            return 'updated';
        }

        // INSERT
        $data['role_id']    = $role_id;
        $data['created_at'] = date('Y-m-d H:i:s');

        // username: Excel → email prefix → employee_id
        if (!empty($username_in) && !in_array(strtolower($username_in), array('none','null',''))) {
            $uname = $username_in;
        } elseif (!empty($email)) {
            $at_pos = strpos($email, '@');
            $prefix = ($at_pos !== false) ? substr($email, 0, $at_pos) : $email;
            $uname  = preg_replace('/[^a-zA-Z0-9._\-]/', '', $prefix);
        } else {
            $uname = strtolower(preg_replace('/[^a-zA-Z0-9]/', '_', $emp_id));
        }
        if (empty($uname)) $uname = 'user_'.$emp_id;

        // ป้องกัน username ซ้ำ
        $orig = $uname; $n = 2;
        while ($this->db->where('username', $uname)->count_all_results('users') > 0) {
            $uname = $orig.$n; $n++;
        }
        $data['username'] = $uname;

        // password: Excel → "1234"
        $pw = (!empty($password_in) && !in_array(strtolower((string)$password_in), array('none','null','')))
            ? (string)$password_in : '1234';
        $data['password'] = password_hash($pw, PASSWORD_BCRYPT, array('cost'=>10));

        $this->db->insert('users', $data);
        return 'created';
    }

    // ════ ผลลัพธ์ ════════════════════════════════════════════
    public function import_result() {
        $result = $this->session->flashdata('import_result');
        if (!$result) redirect('admin/employees_import/import');
        $this->render('admin/employees/import_result', array(
            'title'      => 'ผลการนำเข้าข้อมูล',
            'page_title' => 'ผลการนำเข้าข้อมูลพนักงาน',
            'result'     => $result,
        ));
    }

    // ════ EXPORT ด้วย PHP เท่านั้น ═══════════════════════════
    public function export() {
        $employees = $this->db
            ->select('u.*,d.name AS dept_name,t.team_name,r.name AS role_name')
            ->from('users u')
            ->join('departments d','d.id=u.department_id','left')
            ->join('teams t','t.id=u.team_id','left')
            ->join('roles r','r.id=u.role_id','left')
            ->order_by('u.employee_id','ASC')
            ->get()->result();

        $this->_export_xlsx($employees);
    }

    // สร้าง XLSX ด้วย PHP เท่านั้น (ไม่มี library ภายนอก)
    private function _export_xlsx($employees) {
        $month_name = array(1=>'JAN',2=>'FEB',3=>'MAR',4=>'APR',5=>'MAY',6=>'JUN',
                            7=>'JUL',8=>'AUG',9=>'SEP',10=>'OCT',11=>'NOV',12=>'DEC');
        $status_map  = array('active'=>'พนักงาน','inactive'=>'ลาออก','suspended'=>'ระงับ');

        $headers = array(
            'รหัสพนักงาน','คำนำหน้า','ชื่อจริง','นามสกุล','ชื่อจริง (EN)','นามสกุล (EN)',
            'เลขบัตรประชาชน','แผนก','ตำแหน่ง','ประเภทพนักงาน','อีเมล','เบอร์โทร',
            'ผู้ติดต่อฉุกเฉิน','เบอร์ติดต่อฉุกเฉิน','ที่อยู่','แขวง/ตำบล','เขต/อำเภอ',
            'จังหวัด','รหัสไปรษณีย์','วันที่เริ่มทำงาน YYYYMMDD (ค.ศ.)','เงินเดือน',
            'บัญชีเงินเดือนที่บันทึก','ประกันสังคม','ยอดหัก ณ ที่จ่าย (ภ.ง.ด.1)',
            'ช่องทางรับเงิน','เลขที่บัญชี','สถานะ','ทีม','username','password',
        );

        // สร้าง XML ของ sheet
        $rows_xml = '';
        $row_num  = 1;
        $shared   = array();

        $add_str = function($val) use (&$shared) {
            $key = array_search($val, $shared);
            if ($key === false) { $shared[] = $val; $key = count($shared)-1; }
            return $key;
        };

        // Header row
        $cells_xml = '';
        foreach ($headers as $ci => $h) {
            $col = $this->_col_letter($ci+1);
            $idx = $add_str($h);
            $cells_xml .= '<c r="'.$col.$row_num.'" t="s"><v>'.$idx.'</v></c>';
        }
        $rows_xml .= '<row r="'.$row_num.'">'.$cells_xml.'</row>';
        $row_num++;

        // Data rows
        foreach ($employees as $e) {
            $start = '';
            if (!empty($e->start_date)) {
                $parts = explode('-', $e->start_date);
                if (count($parts)===3) $start = $parts[2].'/'.$parts[1].'/'.$parts[0];
            }
            $vals = array(
                $e->employee_id, $e->title??'', $e->first_name, $e->last_name,
                $e->first_name_en??'', $e->last_name_en??'',
                $e->id_card_number??'', $e->dept_name??'', $e->position??'',
                $e->employee_type??'รายเดือน', $e->email??'', $e->phone??'',
                $e->emergency_contact??'', $e->emergency_phone??'',
                $e->address??'', $e->sub_district??'', $e->district??'',
                $e->province??'', $e->postal_code??'', $start,
                (float)($e->base_salary??0), $e->salary_account??'',
                $e->social_security_status??'ขึ้นทะเบียนประกันสังคม',
                (float)($e->withholding_tax??0), $e->payment_channel??'',
                $e->bank_account??'',
                isset($status_map[$e->status]) ? $status_map[$e->status] : 'พนักงาน',
                $e->team_name??'', $e->username, '',
            );
            $cells_xml = '';
            foreach ($vals as $ci => $v) {
                $col = $this->_col_letter($ci+1);
                if (is_numeric($v) && !is_string($v)) {
                    $cells_xml .= '<c r="'.$col.$row_num.'"><v>'.htmlspecialchars((string)$v).'</v></c>';
                } else {
                    $idx = $add_str((string)$v);
                    $cells_xml .= '<c r="'.$col.$row_num.'" t="s"><v>'.$idx.'</v></c>';
                }
            }
            $rows_xml .= '<row r="'.$row_num.'">'.$cells_xml.'</row>';
            $row_num++;
        }

        // Build XML files
        $ns_main = 'http://schemas.openxmlformats.org/spreadsheetml/2006/main';
        $ns_rel  = 'http://schemas.openxmlformats.org/package/2006/relationships';

        $sheet_xml = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'.
            '<worksheet xmlns="'.$ns_main.'">'.
            '<sheetData>'.$rows_xml.'</sheetData>'.
            '<freezePane ySplit="1" topLeftCell="A2" activePane="bottomLeft" state="frozen"/>'.
            '</worksheet>';

        // Shared strings
        $ss_xml = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'.
            '<sst xmlns="'.$ns_main.'" count="'.count($shared).'" uniqueCount="'.count($shared).'">';
        foreach ($shared as $s) {
            $ss_xml .= '<si><t xml:space="preserve">'.htmlspecialchars($s, ENT_XML1, 'UTF-8').'</t></si>';
        }
        $ss_xml .= '</sst>';

        $wb_xml = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'.
            '<workbook xmlns="'.$ns_main.'" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships">'.
            '<sheets><sheet name="รายงานพนักงาน" sheetId="1" r:id="rId1"/></sheets>'.
            '</workbook>';

        $wb_rels = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'.
            '<Relationships xmlns="'.$ns_rel.'">'.
            '<Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/worksheet" Target="worksheets/sheet1.xml"/>'.
            '<Relationship Id="rId2" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/sharedStrings" Target="sharedStrings.xml"/>'.
            '<Relationship Id="rId3" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/styles" Target="styles.xml"/>'.
            '</Relationships>';

        $ct_xml = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'.
            '<Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types">'.
            '<Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/>'.
            '<Default Extension="xml" ContentType="application/xml"/>'.
            '<Override PartName="/xl/workbook.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet.main+xml"/>'.
            '<Override PartName="/xl/worksheets/sheet1.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.worksheet+xml"/>'.
            '<Override PartName="/xl/sharedStrings.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sharedStrings+xml"/>'.
            '<Override PartName="/xl/styles.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.styles+xml"/>'.
            '</Types>';

        $styles_xml = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'.
            '<styleSheet xmlns="'.$ns_main.'">'.
            '<fonts><font><sz val="10"/><name val="Sarabun"/></font><font><b/><sz val="10"/><name val="Sarabun"/></font></fonts>'.
            '<fills><fill><patternFill patternType="none"/></fill><fill><patternFill patternType="gray125"/></fill><fill><patternFill patternType="solid"><fgColor rgb="FF1A56DB"/></patternFill></fill></fills>'.
            '<borders><border><left/><right/><top/><bottom/><diagonal/></border></borders>'.
            '<cellStyleXfs count="1"><xf numFmtId="0" fontId="0" fillId="0" borderId="0"/></cellStyleXfs>'.
            '<cellXfs><xf numFmtId="0" fontId="0" fillId="0" borderId="0" xfId="0"/>'.
            '<xf numFmtId="0" fontId="1" fillId="2" borderId="0" xfId="0"><alignment horizontal="center"/></xf></cellXfs>'.
            '</styleSheet>';

        $pkg_rels = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'.
            '<Relationships xmlns="'.$ns_rel.'">'.
            '<Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="xl/workbook.xml"/>'.
            '</Relationships>';

        // บันทึกเป็น ZIP
        $tmp = tempnam(sys_get_temp_dir(), 'hrm_').'.xlsx';
        $zip = new ZipArchive();
        $zip->open($tmp, ZipArchive::CREATE | ZipArchive::OVERWRITE);
        $zip->addFromString('[Content_Types].xml',          $ct_xml);
        $zip->addFromString('_rels/.rels',                  $pkg_rels);
        $zip->addFromString('xl/workbook.xml',              $wb_xml);
        $zip->addFromString('xl/_rels/workbook.xml.rels',   $wb_rels);
        $zip->addFromString('xl/worksheets/sheet1.xml',     $sheet_xml);
        $zip->addFromString('xl/sharedStrings.xml',         $ss_xml);
        $zip->addFromString('xl/styles.xml',                $styles_xml);
        $zip->close();

        $fn = 'Employee_export_'.date('YmdHi').'.xlsx';
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="'.$fn.'"');
        header('Content-Length: '.filesize($tmp));
        header('Cache-Control: no-cache, no-store');
        header('Pragma: no-cache');
        readfile($tmp);
        @unlink($tmp);
        exit;
    }

    // แปลง column index เป็น letter (1→A, 27→AA)
    private function _col_letter($n) {
        $s = '';
        while ($n > 0) {
            $n--;
            $s = chr(65 + ($n % 26)) . $s;
            $n = (int)($n / 26);
        }
        return $s;
    }
}
