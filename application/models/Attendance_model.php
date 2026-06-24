<?php defined('BASEPATH') OR exit('No direct script access allowed');
class Attendance_model extends CI_Model {

    public function get_today($uid) {
        return $this->db->where('user_id',$uid)->where('date',date('Y-m-d'))->get('attendance')->row();
    }
    public function get_by_id($id) {
        return $this->db->select('a.*,u.first_name,u.last_name,u.employee_id,s.name AS shift_name')
            ->from('attendance a')->join('users u','u.id=a.user_id')
            ->join('shifts s','s.id=a.shift_id','left')
            ->where('a.id',$id)->get()->row();
    }
    public function checkin($uid,$role = null, $shift_id=null) {
        $this->load->model('Shift_model');
        $shift = null;
        if ($shift_id) {
            $shift = $this->Shift_model->get_by_id($shift_id);
        } else {
            $shift = $this->Shift_model->get_for_user($uid);
            if (!$shift) {
                $last_att = $this->db->where('user_id', $uid)
                                     ->where('shift_id IS NOT NULL')
                                     ->order_by('date', 'DESC')
                                     ->get('attendance')->row();
                if ($last_att) {
                    $shift = $this->Shift_model->get_by_id($last_att->shift_id);
                }
            }
            if (!$shift) {
                $shift = $this->db->order_by('id', 'ASC')->get('shifts')->row();
            }
        }
        $now  = date('H:i:s');
        $is_late = 0; $lm = 0;
        if ($shift) {
            $diff = (strtotime($now) - strtotime($shift->start_time)) / 60;
            $threshold = $shift->late_threshold_minutes ?? 15;
            if ($diff > $threshold) { $is_late=1; $lm=round($diff); }
        }
        $this->db->insert('attendance',array(
            'user_id'       => $uid,
            'shift_id'      => $shift ? $shift->id : null,
            'date'          => date('Y-m-d'),
            'check_in_time' => date('Y-m-d H:i:s'),
            'is_late'       => $is_late,
            'late_minutes'  => $lm,
            'status'        => 'present',
            'created_at'    => date('Y-m-d H:i:s'),
            'updated_at'    => date('Y-m-d H:i:s')
        ));
        return $this->db->insert_id();
    }

    public function checkout($att_id, $uid) {
        $att = $this->db->where('id',$att_id)->where('user_id',$uid)->get('attendance')->row();
        if (!$att) return false;
        $this->load->model('Shift_model');
        $shift = null;
        if ($att->shift_id) {
            $shift = $this->Shift_model->get_by_id($att->shift_id);
        } else {
            $shift = $this->Shift_model->get_for_user($uid);
        }
        $now = date('Y-m-d H:i:s');
        // ส่ง check_in_time ให้ calc_ot เพื่อตรวจ sanity + คำนวณ OT วันหยุดถูกต้อง
        $ot = $shift ? $this->Shift_model->calc_ot($shift, $now, $att->check_in_time) : 0;
        $this->db->where('id',$att_id)->update('attendance',array(
            'check_out_time' => $now,
            'ot_hours'       => $ot,
            'updated_at'     => $now
        ));
        return true;
    }

    public function get_monthly($uid,$y,$m) {
        return $this->db->select('a.*,s.name AS shift_name,s.start_time AS shift_start,s.end_time AS shift_end,s.color AS shift_color')
            ->from('attendance a')->join('shifts s','s.id=a.shift_id','left')
            ->where('a.user_id',$uid)->where('YEAR(a.date)',$y)->where('MONTH(a.date)',$m)
            ->order_by('a.date','ASC')->get()->result();
    }

    public function get_all_monthly($y,$m,$dept=null,$shift_id=null,$status=null,$limit=50,$offset=0) {
        $this->db->select('a.*,u.first_name,u.last_name,u.employee_id,d.name AS dept_name,s.name AS shift_name,s.color AS shift_color,lt.name AS leave_type_name')
            ->from('attendance a')
            ->join('users u','u.id=a.user_id')
            ->join('departments d','d.id=u.department_id','left')
            ->join('shifts s','s.id=a.shift_id','left')
            ->join('leave_types lt','lt.id=a.leave_type_id','left')
            ->where('YEAR(a.date)',$y)->where('MONTH(a.date)',$m);
        if ($dept)     $this->db->where('u.department_id',$dept);
        if ($shift_id) $this->db->where('a.shift_id',$shift_id);
        if (!empty($status)) {
            if ($status === 'late') {
                $this->db->where('a.is_late', 1);
            } else {
                $this->db->where('a.status', $status);
            }
        }
        if ($limit > 0) $this->db->limit($limit, $offset);
        return $this->db->order_by('a.date DESC,u.employee_id ASC')->get()->result();
    }

    public function count_all_monthly_filtered($y,$m,$dept=null,$shift_id=null,$status=null) {
        $this->db->from('attendance a')
            ->join('users u','u.id=a.user_id')
            ->where('YEAR(a.date)',$y)->where('MONTH(a.date)',$m);
        if ($dept)     $this->db->where('u.department_id',$dept);
        if ($shift_id) $this->db->where('a.shift_id',$shift_id);
        if (!empty($status)) {
            if ($status === 'late') {
                $this->db->where('a.is_late', 1);
            } else {
                $this->db->where('a.status', $status);
            }
        }
        return $this->db->count_all_results();
    }

    public function get_summary($uid,$y,$m) {
        $rows=$this->get_monthly($uid,$y,$m);
        $s=array('present'=>0,'absent'=>0,'late'=>0,'leave'=>0,'leave_hours'=>0,'total_late_min'=>0,'total_ot'=>0,'total_work_hrs'=>0);
        foreach($rows as $r) {
            if ($r->status==='present') $s['present']++;
            if ($r->status==='absent')  $s['absent']++;
            if ($r->status==='leave')   { $s['leave']++; $s['leave_hours']+=$r->leave_hours; }
            if ($r->is_late)            { $s['late']++; $s['total_late_min']+=$r->late_minutes; }
            $s['total_ot']+=$r->ot_hours;
        }
        return $s;
    }

    // ─────────────────────────────────────────────────────────────────────
    // [ข้อ 3] หาวันขาดงาน: วันทำงาน (จ-ศ) ที่ไม่ใช่วันหยุดนักขัตฤกษ์ไทย
    //          และพนักงานไม่ได้ลงเวลา/ไม่ได้ลา (approved) ในวันนั้น
    // ─────────────────────────────────────────────────────────────────────
    public function get_absent_days($uid, $y, $m) {
        $today     = date('Y-m-d');
        $first_day = sprintf('%04d-%02d-01', $y, $m);
        $last_day  = date('Y-m-t', strtotime($first_day));

        // ไม่นับเดือนในอนาคต (นับถึงวันนี้เท่านั้น)
        if ($last_day > $today) $last_day = $today;
        if ($first_day > $today) return array();

        // อ่านวันเริ่มนับขาดงานจาก config (ป้องกันปัญหาตอน deploy ใหม่)
        $CI =& get_instance();
        $track_start = $CI->config->item('attendance_track_start');
        if (!empty($track_start)) {
            // ถ้าทั้งเดือนอยู่ก่อน track_start → ไม่มีขาดเลย
            if ($last_day < $track_start) return array();
            // ถ้า first_day อยู่ก่อน track_start → เริ่มนับจาก track_start แทน
            if ($first_day < $track_start) $first_day = $track_start;
        }

        // ดึงวันหยุดนักขัตฤกษ์ไทยของเดือนนั้น
        $holidays = $this->_get_thai_holidays($y);

        // ดึง attendance ของพนักงานในเดือนนั้น
        $att_rows = $this->db->where('user_id', $uid)
            ->where('date >=', $first_day)
            ->where('date <=', $last_day)
            ->get('attendance')->result();
        $att_dates = array();
        foreach ($att_rows as $row) {
            $att_dates[$row->date] = $row->status;
        }

        // ดึงวันลาที่ approved (status=approved) ของพนักงานในเดือนนั้น
        $leave_rows = $this->db
            ->where('user_id', $uid)
            ->where('status', 'approved')
            ->where('start_date <=', $last_day)
            ->where('end_date >=', $first_day)
            ->get('leave_requests')->result();

        // สร้าง set ของวันลาที่อนุมัติแล้ว
        $leave_dates = array();
        foreach ($leave_rows as $lr) {
            $cur = strtotime($lr->start_date);
            $end = strtotime($lr->end_date);
            while ($cur <= $end) {
                $leave_dates[date('Y-m-d', $cur)] = true;
                $cur = strtotime('+1 day', $cur);
            }
        }

        // วนหาวันขาด
        $absent_days = array();
        $cur = strtotime($first_day);
        $end = strtotime($last_day);

        while ($cur <= $end) {
            $date_str  = date('Y-m-d', $cur);
            $day_of_week = (int)date('N', $cur); // 1=Mon ... 7=Sun

            // ข้ามเสาร์-อาทิตย์
            if ($day_of_week >= 6) {
                $cur = strtotime('+1 day', $cur);
                continue;
            }
            // ข้ามวันหยุดนักขัตฤกษ์
            if (isset($holidays[$date_str])) {
                $cur = strtotime('+1 day', $cur);
                continue;
            }
            // ข้ามวันที่ลงเวลาแล้ว (present/leave/holiday/half_day)
            if (isset($att_dates[$date_str])) {
                $cur = strtotime('+1 day', $cur);
                continue;
            }
            // ข้ามวันที่มีการลาที่อนุมัติแล้ว
            if (isset($leave_dates[$date_str])) {
                $cur = strtotime('+1 day', $cur);
                continue;
            }
            // ถือว่าขาด
            $absent_days[] = $date_str;
            $cur = strtotime('+1 day', $cur);
        }

        return $absent_days;
    }

    // ── วันหยุดนักขัตฤกษ์ไทย (บริษัทเอกชน) ─────────────────────────────
    private function _get_thai_holidays($year) {
        // รายการวันหยุดฐาน (ไม่ขึ้นกับปี)
        $fixed = array(
            '01-01' => 'วันขึ้นปีใหม่',
            '04-06' => 'วันจักรี',
            '04-13' => 'วันสงกรานต์',
            '04-14' => 'วันสงกรานต์',
            '04-15' => 'วันสงกรานต์',
            '05-01' => 'วันแรงงาน',
            '05-05' => 'วันฉัตรมงคล',
            '06-03' => 'วันเฉลิมพระชนมพรรษาสมเด็จพระราชินี',
            '07-28' => 'วันเฉลิมพระชนมพรรษา ร.10',
            '08-12' => 'วันแม่แห่งชาติ',
            '10-13' => 'วันคล้ายวันสวรรคต ร.9',
            '10-23' => 'วันปิยมหาราช',
            '12-05' => 'วันพ่อแห่งชาติ / วันชาติ',
            '12-10' => 'วันรัฐธรรมนูญ',
            '12-31' => 'วันสิ้นปี',
        );

        // วันหยุดที่คำนวณตามปีพุทธศักราช (ประมาณการ)
        $variable = $this->_calc_variable_holidays($year);

        $result = array();
        foreach ($fixed as $md => $name) {
            $result[sprintf('%04d-%s', $year, $md)] = $name;
        }
        foreach ($variable as $date => $name) {
            $result[$date] = $name;
        }
        return $result;
    }

    // คำนวณวันหยุดตามปฏิทินจันทรคติ (วันวิสาขบูชา/มาฆบูชา/อาสาฬหบูชา/เข้าพรรษา)
    private function _calc_variable_holidays($year) {
        $holidays = array();
        // วันสำคัญทางพุทธศาสนา — ใช้ค่าประมาณการ (ปฏิทินจันทรคติแม่นยำต้องคำนวณ)
        // ข้อมูลปี 2024-2027 (สามารถเพิ่มได้)
        $data = array(
            2024 => array('02-24'=>'มาฆบูชา','05-22'=>'วิสาขบูชา','07-21'=>'อาสาฬหบูชา','07-22'=>'เข้าพรรษา'),
            2025 => array('02-12'=>'มาฆบูชา','05-11'=>'วิสาขบูชา','07-10'=>'อาสาฬหบูชา','07-11'=>'เข้าพรรษา'),
            2026 => array('03-03'=>'มาฆบูชา','05-31'=>'วิสาขบูชา','07-29'=>'อาสาฬหบูชา','07-30'=>'เข้าพรรษา'),
            2027 => array('02-20'=>'มาฆบูชา','05-20'=>'วิสาขบูชา','07-18'=>'อาสาฬหบูชา','07-19'=>'เข้าพรรษา'),
        );
        if (isset($data[$year])) {
            foreach ($data[$year] as $md => $name) {
                $holidays[sprintf('%04d-%s', $year, $md)] = $name;
            }
        }
        return $holidays;
    }

    public function manual_add($data) {
        $data['created_at']=$data['updated_at']=date('Y-m-d H:i:s');
        if (!empty($data['shift_id']) && !empty($data['check_in_time'])) {
            $this->load->model('Shift_model');
            $shift = $this->Shift_model->get_by_id($data['shift_id']);
            if ($shift) {
                $ct = date('H:i:s', strtotime($data['check_in_time']));
                $diff = (strtotime($ct) - strtotime($shift->start_time)) / 60;
                $data['is_late']       = $diff > ($shift->late_threshold_minutes??15) ? 1 : 0;
                $data['late_minutes']  = $data['is_late'] ? round($diff) : 0;
            }
        }
        $this->db->insert('attendance',$data);
        return $this->db->insert_id();
    }

    public function update_record($id, $data, $admin_id=null) {
        $data['updated_at'] = date('Y-m-d H:i:s');
        if ($admin_id) {
            $data['modified_by'] = $admin_id;
            $data['modified_at'] = date('Y-m-d H:i:s');
        }
        if (!empty($data['shift_id']) && !empty($data['check_in_time'])) {
            $this->load->model('Shift_model');
            $shift = $this->Shift_model->get_by_id($data['shift_id']);
            if ($shift) {
                $ct = date('H:i:s', strtotime($data['check_in_time']));
                $diff = (strtotime($ct) - strtotime($shift->start_time)) / 60;
                $data['is_late']      = $diff > ($shift->late_threshold_minutes??15) ? 1 : 0;
                $data['late_minutes'] = $data['is_late'] ? round($diff) : 0;
            }
        }
        $this->db->where('id',$id)->update('attendance',$data);
        return $this->db->affected_rows()>0;
    }

    public function delete_record($id) {
        $this->db->where('id',$id)->delete('attendance');
        return $this->db->affected_rows()>0;
    }

    public function count_all_monthly($y,$m,$dept=null) {
        $this->db->from('attendance a')->join('users u','u.id=a.user_id','left')
            ->where('YEAR(a.date)',$y)->where('MONTH(a.date)',$m);
        if ($dept) $this->db->where('u.department_id',$dept);
        return $this->db->count_all_results();
    }
}
