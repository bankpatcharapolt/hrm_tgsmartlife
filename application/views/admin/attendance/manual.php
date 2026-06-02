<?php defined('BASEPATH') OR exit(); ?>
<div class="card" style="max-width:720px">
  <div class="card-header"><i class="bi bi-plus-circle me-2"></i><?=$page_title?></div>
  <div class="card-body">
    <?=form_open('admin/attendance/manual')?>
    <input type="hidden" name="<?=$this->security->get_csrf_token_name()?>" value="<?=$this->security->get_csrf_hash()?>">
    <div class="row g-3">
      <div class="col-md-6">
        <label class="form-label">พนักงาน *</label>
        <select name="user_id" class="form-select ts-select" required>
          <option value="">-- เลือกพนักงาน --</option>
          <?php foreach($employees as $e):?>
          <option value="<?=$e->id?>"><?=$e->employee_id?> – <?=$e->first_name.' '.$e->last_name?></option>
          <?php endforeach;?>
        </select>
      </div>
      <div class="col-md-3">
        <label class="form-label">กะการทำงาน</label>
        <select name="shift_id" class="form-select">
          <option value="">– ไม่ระบุกะ –</option>
          <?php foreach($shifts as $s):?>
          <option value="<?=$s->id?>"><?=$s->name?> (<?=substr($s->start_time,0,5)?>–<?=substr($s->end_time,0,5)?>)</option>
          <?php endforeach;?>
        </select>
      </div>
      <div class="col-md-3">
        <label class="form-label">วันที่ *</label>
        <input type="date" name="date" class="form-control" value="<?=date('Y-m-d')?>" required>
      </div>
      <div class="col-md-4">
        <label class="form-label">เวลาเข้างาน</label>
        <input type="datetime-local" name="check_in" class="form-control">
      </div>
      <div class="col-md-4">
        <label class="form-label">เวลาออกงาน</label>
        <input type="datetime-local" name="check_out" class="form-control">
      </div>
      <div class="col-md-4">
        <label class="form-label">OT (ชั่วโมง)</label>
        <input type="number" name="ot_hours" class="form-control" value="0" min="0" step="0.5">
      </div>
      <div class="col-md-6">
        <label class="form-label">สถานะ</label>
        <select name="status" class="form-select" id="statusSel" onchange="toggleLeave(this.value)">
          <option value="present">มาทำงาน</option>
          <option value="absent">ขาดงาน</option>
          <option value="leave">ลา</option>
          <option value="holiday">วันหยุด</option>
          <option value="half_day">ครึ่งวัน</option>
        </select>
      </div>
      <div class="col-md-6">
        <label class="form-label">หมายเหตุ</label>
        <input type="text" name="note" class="form-control" placeholder="หมายเหตุเพิ่มเติม">
      </div>

      <!-- Leave Section (แสดงเมื่อสถานะ = ลา) -->
      <div class="col-12" id="leaveSection" style="display:none">
        <div class="p-3 rounded" style="background:#eff6ff;border:1px solid #bae6fd">
          <div class="fw-semibold mb-2" style="font-size:.875rem"><i class="bi bi-calendar-check me-1"></i>รายละเอียดการลา</div>
          <div class="row g-2">
            <div class="col-md-4">
              <label class="form-label small">ประเภทการลา</label>
              <select name="leave_type_id" class="form-select form-select-sm ts-select">
                <option value="">– เลือก –</option>
                <?php foreach($leave_types as $lt):?>
                <option value="<?=$lt->id?>"><?=$lt->name?></option>
                <?php endforeach;?>
              </select>
            </div>
            <div class="col-md-4">
              <label class="form-label small">หน่วยการลา</label>
              <select name="leave_unit" class="form-select form-select-sm" id="leaveUnit" onchange="toggleHourLeave(this.value)">
                <option value="day">ลาเต็มวัน</option>
                <option value="hour">ลาเป็นชั่วโมง</option>
              </select>
            </div>
            <div class="col-md-4" id="hourLeaveSection" style="display:none">
              <label class="form-label small">ช่วงเวลาที่ลา</label>
              <div class="input-group input-group-sm">
                <input type="time" name="leave_start_hour" class="form-control" placeholder="เริ่ม">
                <span class="input-group-text">–</span>
                <input type="time" name="leave_end_hour" class="form-control" placeholder="สิ้นสุด">
              </div>
              <div class="form-text text-primary" id="leaveHoursCalc"></div>
            </div>
          </div>
        </div>
      </div>
    </div>
    <div class="mt-4 d-flex gap-2">
      <button type="submit" class="btn btn-primary"><i class="bi bi-save me-1"></i>บันทึก</button>
      <a href="<?=base_url('admin/attendance')?>" class="btn btn-outline-secondary">ยกเลิก</a>
    </div>
    <?=form_close()?>
  </div>
</div>
<?php $extra_js='<script>
function toggleLeave(v){document.getElementById("leaveSection").style.display=v==="leave"?"":"none";}
function toggleHourLeave(v){document.getElementById("hourLeaveSection").style.display=v==="hour"?"":"none";}
// คำนวณชั่วโมงลา
document.querySelectorAll("[name=leave_start_hour],[name=leave_end_hour]").forEach(function(el){
  el.addEventListener("change",function(){
    var s=document.querySelector("[name=leave_start_hour]").value,e=document.querySelector("[name=leave_end_hour]").value;
    if(s&&e){var h=((new Date("2000-01-01 "+e))-(new Date("2000-01-01 "+s)))/3600000;document.getElementById("leaveHoursCalc").textContent=h>0?"ลา "+h.toFixed(1)+" ชั่วโมง":"";}
  });
});
</script>';?>
