<?php defined('BASEPATH') OR exit(); $r=$rec; ?>
<div class="card" style="max-width:720px">
  <div class="card-header"><i class="bi bi-pencil me-2"></i>แก้ไขการเข้างาน – <?=$r->first_name.' '.$r->last_name?> (<?=date('d/m/Y',strtotime($r->date))?>)</div>
  <div class="card-body">
    <?=form_open('admin/attendance/edit/'.$r->id)?>
    <input type="hidden" name="<?=$this->security->get_csrf_token_name()?>" value="<?=$this->security->get_csrf_hash()?>">
    <div class="row g-3">
      <div class="col-md-4">
        <label class="form-label">กะการทำงาน</label>
        <select name="shift_id" class="form-select">
          <option value="">– ไม่ระบุกะ –</option>
          <?php foreach($shifts as $s):?>
          <option value="<?=$s->id?>" <?=$r->shift_id==$s->id?'selected':''?>><?=$s->name?> (<?=substr($s->start_time,0,5)?>–<?=substr($s->end_time,0,5)?>)</option>
          <?php endforeach;?>
        </select>
      </div>
      <div class="col-md-4">
        <label class="form-label">เวลาเข้างาน</label>
        <input type="datetime-local" name="check_in" class="form-control" value="<?=$r->check_in_time?date('Y-m-d\TH:i',strtotime($r->check_in_time)):''?>">
      </div>
      <div class="col-md-4">
        <label class="form-label">เวลาออกงาน</label>
        <input type="datetime-local" name="check_out" class="form-control" value="<?=$r->check_out_time?date('Y-m-d\TH:i',strtotime($r->check_out_time)):''?>">
      </div>
      <div class="col-md-3">
        <label class="form-label">OT (ชั่วโมง)</label>
        <input type="number" name="ot_hours" class="form-control" value="<?=$r->ot_hours?>" min="0" step="0.5">
      </div>
      <div class="col-md-5">
        <label class="form-label">สถานะ</label>
        <select name="status" class="form-select" id="statusSel" onchange="toggleLeave(this.value)">
          <option value="present" <?=$r->status==='present'?'selected':''?>>มาทำงาน</option>
          <option value="absent" <?=$r->status==='absent'?'selected':''?>>ขาดงาน</option>
          <option value="leave" <?=$r->status==='leave'?'selected':''?>>ลา</option>
          <option value="holiday" <?=$r->status==='holiday'?'selected':''?>>วันหยุด</option>
          <option value="half_day" <?=$r->status==='half_day'?'selected':''?>>ครึ่งวัน</option>
        </select>
      </div>
      <div class="col-md-4">
        <label class="form-label">หมายเหตุ</label>
        <input type="text" name="note" class="form-control" value="<?=htmlspecialchars($r->note??'')?>">
      </div>
      <!-- Leave Section -->
      <div class="col-12" id="leaveSection" <?=$r->status==='leave'?'':'style="display:none"'?>>
        <div class="p-3 rounded" style="background:#eff6ff;border:1px solid #bae6fd">
          <div class="fw-semibold mb-2 small"><i class="bi bi-calendar-check me-1"></i>รายละเอียดการลา</div>
          <div class="row g-2">
            <div class="col-md-5">
              <label class="form-label small">ประเภทการลา</label>
              <select name="leave_type_id" class="form-select form-select-sm ts-select">
                <option value="">– เลือก –</option>
                <?php foreach($leave_types as $lt):?>
                <option value="<?=$lt->id?>" <?=$r->leave_type_id==$lt->id?'selected':''?>><?=$lt->name?></option>
                <?php endforeach;?>
              </select>
            </div>
            <div class="col-md-4">
              <label class="form-label small">หน่วยการลา</label>
              <select name="leave_unit" class="form-select form-select-sm" id="leaveUnit" onchange="toggleHourLeave(this.value)">
                <option value="day" <?=$r->leave_hours==0?'selected':''?>>ลาเต็มวัน</option>
                <option value="hour" <?=$r->leave_hours>0?'selected':''?>>ลาเป็นชั่วโมง</option>
              </select>
            </div>
            <div class="col-md-3" id="hourLeaveSection" <?=$r->leave_hours>0?'':'style="display:none"'?>>
              <label class="form-label small">จำนวนชั่วโมง</label>
              <div class="input-group input-group-sm">
                <input type="number" name="leave_hours_direct" class="form-control" value="<?=$r->leave_hours?>" min="0.5" max="8" step="0.5" placeholder="ชม.">
                <span class="input-group-text">ชม.</span>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
    <div class="mt-4 d-flex gap-2">
      <button type="submit" class="btn btn-primary"><i class="bi bi-save me-1"></i>บันทึกการแก้ไข</button>
      <a href="<?=base_url('admin/attendance')?>" class="btn btn-outline-secondary">ยกเลิก</a>
      <a href="<?=base_url('admin/attendance/delete/'.$r->id)?>" onclick="return confirm('ลบรายการนี้?')" class="btn btn-outline-danger ms-auto"><i class="bi bi-trash me-1"></i>ลบ</a>
    </div>
    <?=form_close()?>
  </div>
</div>
<script>
function toggleLeave(v){document.getElementById("leaveSection").style.display=v==="leave"?"":"none";}
function toggleHourLeave(v){document.getElementById("hourLeaveSection").style.display=v==="hour"?"":"none";}
</script>
