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

        $cout = strtotime($checkout_time);
        $cin  = $checkin_time ? strtotime($checkin_time) : null;

        // Sanity: checkout ต้องหลัง checkin
        // กะดึกข้ามวัน: checkout อาจถูกบันทึกเป็นวันเดียวกับ checkin
        // (เช่น checkin=27 21:30, checkout=27 09:30 แทนที่จะเป็น 28 09:30)
        // → แก้โดย +86400 ให้ checkout ถ้าเป็นกะดึกและ cout < cin
        if ($cin !== null && $cout <= $cin) {
            if (!empty($shift->is_night_shift)) {
                $cout += 86400; // เพิ่ม 1 วัน
            } else {
                return 0; // กะปกติ checkout ก่อน checkin = ข้อมูลผิด
            }
        }

        // ── เลือก base_date ──────────────────────────────────────────────────
        // กะดึก: ใช้ checkin_date เป็นฐาน (เพราะกะเริ่มวัน X สิ้นสุดวัน X+1)
        if (!empty($shift->is_night_shift) && $checkin_time) {
            $base_date = date('Y-m-d', strtotime($checkin_time));
        } else {
            $base_date = date('Y-m-d', strtotime($checkout_time));
        }

        // ── คำนวณ shift_end timestamp ────────────────────────────────────────
        $shift_start = strtotime($base_date . ' ' . $shift->start_time);
        $shift_end   = strtotime($base_date . ' ' . $shift->end_time);

        // กะดึกข้ามวัน: end_time <= start_time → end วันถัดไป
        if (!empty($shift->is_night_shift) && $shift_end <= $shift_start) {
            $shift_end += 86400;
        }

        // ── Weekend check ────────────────────────────────────────────────────
        $day_of_week = (int)date('N', strtotime($base_date));
        $is_weekend  = ($day_of_week >= 6);

        if ($is_weekend && empty($shift->is_night_shift)) {
            // กะกลางวัน วันหยุด: OT = ชั่วโมงทำจริง - ot_delay
            if (!$cin) return 0;
            $work_sec     = $cout - $cin;
            if ($work_sec <= 0) return 0;
            $ot_delay_sec = ($shift->ot_starts_after_minutes ?? 0) * 60;
            return max(0, round(($work_sec - $ot_delay_sec) / 3600, 2));
        }

        // ── กะปกติ / กะดึก: OT = เวลาที่เกิน shift_end + ot_delay ────────
        $ot_delay_sec = ($shift->ot_starts_after_minutes ?? 0) * 60;
        $diff         = $cout - $shift_end - $ot_delay_sec;
        return max(0, round($diff / 3600, 2));
    }

    // คำนวณชั่วโมงทำงาน
    public function calc_work_hours($checkin, $checkout, $break_min=60) {
        if (!$checkin || !$checkout) return 0;
        $diff = (strtotime($checkout) - strtotime($checkin)) / 3600;
        return max(0, round($diff - ($break_min/60), 2));
    }
}
