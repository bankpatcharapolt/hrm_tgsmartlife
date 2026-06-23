<?php defined('BASEPATH') OR exit('No direct script access allowed');
class Shift_model extends CI_Model {
    public function get_all($active_only=true) {
        if ($active_only) $this->db->where('status','active');
        return $this->db->order_by('start_time','ASC')->get('shifts')->result();
    }
    public function get_by_id($id) {
        return $this->db->where('id',$id)->get('shifts')->row();
    }
    public function create($data) {
        $data['created_at']=$data['updated_at']=date('Y-m-d H:i:s');
        $this->db->insert('shifts',$data);
        return $this->db->insert_id();
    }
    public function update($id,$data) {
        $data['updated_at']=date('Y-m-d H:i:s');
        $this->db->where('id',$id)->update('shifts',$data);
        return $this->db->affected_rows()>0;
    }
    public function delete($id) {
        $this->db->where('id',$id)->update('shifts',array('status'=>'inactive'));
        return $this->db->affected_rows()>0;
    }
    public function get_for_user($user_id) {
        // ดึงกะของ user จาก users.shift_id
        $u = $this->db->select('s.*')->from('users u')
            ->join('shifts s','s.id=u.shift_id','left')
            ->where('u.id',$user_id)->get()->row();
        if ($u && !empty($u->id)) return $u;

        // fallback: ดู attendance ล่าสุดที่มี shift_id
        $last_att = $this->db->where('user_id', $user_id)
                             ->where('shift_id IS NOT NULL')
                             ->order_by('date', 'DESC')
                             ->get('attendance')->row();
        if ($last_att) {
            $shift = $this->get_by_id($last_att->shift_id);
            if ($shift) return $shift;
        }

        // fallback สุดท้าย: กะปกติ 08:30-17:30 (ไม่ใช้ id=1 อีกต่อไป — หา by name หรือ default object)
        $default = $this->db->where('status','active')
                            ->like('name','ปกติ')
                            ->order_by('id','ASC')
                            ->get('shifts')->row();
        if ($default) return $default;

        // กะ default object — ไม่ผูกกับ id ใดใน DB เพื่อความปลอดภัย
        return (object)array(
            'id'                     => null,
            'name'                   => 'กะปกติ',
            'start_time'             => '08:30:00',
            'end_time'               => '17:30:00',
            'break_minutes'          => 60,
            'late_threshold_minutes' => 15,
            'ot_starts_after_minutes'=> 0,
            'is_night_shift'         => 0,
            'color'                  => '#16a34a',
        );
    }

    // ตรวจสอบว่า check-in สายไหม
    public function is_late($shift, $checkin_time) {
        $shift_start = strtotime($shift->start_time);
        $checkin     = strtotime($checkin_time);
        $diff_min    = ($checkin - $shift_start) / 60;
        return $diff_min > ($shift->late_threshold_minutes ?? 15);
    }

    /**
     * คำนวณ OT (ชั่วโมง)
     *
     * @param object $shift          ข้อมูลกะ
     * @param string $checkout_time  DATETIME ของเวลาออกงาน
     * @param string $checkin_time   DATETIME ของเวลาเข้างาน (optional — ใช้ตรวจ sanity)
     * @return float  OT hours (>= 0)
     */
    public function calc_ot($shift, $checkout_time, $checkin_time = null) {
        if (!$checkout_time) return 0;

        $date = date('Y-m-d', strtotime($checkout_time));
        $cout = strtotime($checkout_time);

        // ── Sanity check: checkout ต้องไม่น้อยกว่า checkin ──────────────
        if ($checkin_time) {
            $cin = strtotime($checkin_time);
            if ($cout <= $cin) return 0;
            // ถ้าทำงานไม่ถึง ot_starts_after_minutes หลัง end_time → 0
        }

        // ── คำนวณ shift_end timestamp ───────────────────────────────────
        $end = strtotime($date . ' ' . $shift->end_time);

        // Night shift ข้ามวัน: end_time อยู่วันถัดไป
        if (!empty($shift->is_night_shift)) {
            $start = strtotime($date . ' ' . $shift->start_time);
            if ($end <= $start) $end += 86400; // end ข้ามวัน
        }

        // ── ตรวจ workday: ถ้าวันเสาร์หรืออาทิตย์ ────────────────────────
        // กะที่ไม่ใช่ night shift และ start/end อยู่ช่วงกลางวัน (08:00-20:00)
        // → ถือเป็นกะจ-ศ → วันหยุดสุดสัปดาห์ไม่ควรมี OT จาก shift end
        $day_of_week = (int)date('N', strtotime($date)); // 1=Mon ... 7=Sun
        $is_weekend  = ($day_of_week >= 6);

        if ($is_weekend && empty($shift->is_night_shift)) {
            // วันเสาร์/อาทิตย์ + กะกลางวัน:
            // ถ้ามาทำงาน → OT = ชั่วโมงที่ทำจริง (check_in ถึง check_out) หัก ot_starts_after_minutes
            // แต่ถ้าไม่มี checkin_time → ไม่สามารถคำนวณได้ → 0
            if (!$checkin_time) return 0;

            $cin = strtotime($checkin_time);
            if ($cout <= $cin) return 0;

            $work_sec = $cout - $cin;
            $ot_delay_sec = ($shift->ot_starts_after_minutes ?? 0) * 60;

            // OT เริ่มหลังจากทำงานครบ ot_starts_after_minutes
            $ot_sec = $work_sec - $ot_delay_sec;
            return max(0, round($ot_sec / 3600, 2));
        }

        // ── กะปกติ (วันทำงาน) ───────────────────────────────────────────
        // OT = เวลาที่เกิน (shift_end + ot_starts_after_minutes)
        $ot_delay_sec = ($shift->ot_starts_after_minutes ?? 0) * 60;
        $diff = ($cout - $end - $ot_delay_sec);
        return max(0, round($diff / 3600, 2));
    }

    // คำนวณชั่วโมงทำงาน
    public function calc_work_hours($checkin, $checkout, $break_min=60) {
        if (!$checkin || !$checkout) return 0;
        $diff = (strtotime($checkout) - strtotime($checkin)) / 3600;
        return max(0, round($diff - ($break_min/60), 2));
    }
}
