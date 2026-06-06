<?php defined('BASEPATH') OR exit(); $r=$req; ?>
<div class="card" style="max-width:680px">
  <div class="card-header"><i class="bi bi-pencil me-2"></i>แก้ไขคำขอลา</div>
  <div class="card-body">
    <div class="alert alert-info py-2 px-3 mb-3 small">
      <i class="bi bi-info-circle me-1"></i>แก้ไขได้เฉพาะคำขอที่ <strong>รอการอนุมัติ</strong> เท่านั้น
    </div>
    <?=form_open_multipart('employee/leave/edit/'.$r->id)?>
    <input type="hidden" name="<?=$this->security->get_csrf_token_name()?>" value="<?=$this->security->get_csrf_hash()?>">
    <div class="row g-3">
      <div class="col-12">
        <label class="form-label">ประเภทการลา *</label>
        <select name="leave_type_id" id="leaveTypeEdit" class="form-select" onchange="checkMedCert(this)" required>
          <option value="">-- เลือก --</option>
          <?php foreach($leave_types as $lt):?>
          <option value="<?=$lt->id?>" <?=$r->leave_type_id==$lt->id?'selected':''?>>
            <?=$lt->name?><?=$lt->quota_per_year>0?' (โควต้า '.$lt->quota_per_year.' วัน/ปี)':' (ไม่จำกัด)'?>
          </option>
          <?php endforeach;?>
        </select>
      </div>
      <div class="col-md-4">
        <label class="form-label">หน่วยการลา</label>
        <select name="leave_unit" class="form-select" id="leaveUnit" onchange="toggleHour(this.value)">
          <option value="day"  <?=(!$r->leave_unit||$r->leave_unit==='day' )?'selected':''?>>ลาเต็มวัน</option>
          <option value="hour" <?=($r->leave_unit==='hour')?'selected':''?>>ลาชั่วโมง</option>
        </select>
      </div>
      <div class="col-md-4">
        <label class="form-label">วันที่เริ่มลา *</label>
        <input type="date" name="start_date" class="form-control" value="<?=$r->start_date?>" onchange="calcDays()" required>
      </div>
      <div class="col-md-4">
        <label class="form-label">วันที่สิ้นสุด *</label>
        <input type="date" name="end_date" class="form-control" value="<?=$r->end_date?>" onchange="calcDays()" required>
      </div>
      <!-- ชั่วโมง -->
      <div class="col-12" id="hourSection" <?=($r->leave_unit==='hour')?'':'style="display:none"'?>>
        <div class="p-2 rounded" style="background:#eff6ff;border:1px solid #bae6fd">
          <div class="row g-2">
            <div class="col-5">
              <label class="form-label small">เวลาเริ่มลา</label>
              <input type="time" name="leave_start_time" class="form-control form-control-sm" value="<?=$r->start_time?substr($r->start_time,0,5):''?>">
            </div>
            <div class="col-2 d-flex align-items-end pb-1 justify-content-center">–</div>
            <div class="col-5">
              <label class="form-label small">เวลาสิ้นสุด</label>
              <input type="time" name="leave_end_time" class="form-control form-control-sm" value="<?=$r->end_time?substr($r->end_time,0,5):''?>">
            </div>
          </div>
        </div>
      </div>
      <div class="col-12" id="daysInfo" style="font-size:.83rem;color:#1a56db"></div>
      <div class="col-12">
        <label class="form-label">เหตุผล *</label>
        <textarea name="reason" class="form-control" rows="3" required><?=htmlspecialchars($r->reason)?></textarea>
      </div>
      <div class="col-12">
        <label class="form-label">เอกสารประกอบ (ถ้ามี)</label>
        <input type="file" name="document" class="form-control" accept=".pdf,.jpg,.jpeg,.png">
        <?php if(!empty($r->document_path)):?>
        <div class="mt-1">
          <a href="<?=base_url($r->document_path)?>" target="_blank"
             class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-file-earmark me-1"></i>ดูเอกสารปัจจุบัน
          </a>
          <small class="text-muted ms-1">(อัปโหลดใหม่เพื่อแทนที่)</small>
        </div>
        <?php endif;?>
      </div>

      <!-- [ข้อ 2] ใบรับรองแพทย์ — แสดงเฉพาะลาป่วย -->
      <div class="col-12" id="medCertEditWrap" style="display:none">
        <div class="p-3 rounded" style="background:#fff7ed;border:1px solid #fed7aa">
          <label class="form-label fw-semibold" style="color:#c2410c">
            <i class="bi bi-file-medical me-1"></i>ใบรับรองแพทย์
            <span class="text-muted fw-normal" style="font-size:.75rem">(ถ้ามี)</span>
          </label>
          <!-- แสดงไฟล์เดิมถ้ามี -->
          <?php if(!empty($r->medical_cert_path)):?>
          <div class="mb-2 d-flex align-items-center gap-2">
            <a href="<?=base_url($r->medical_cert_path)?>" target="_blank"
               class="btn btn-outline-warning btn-sm">
              <i class="bi bi-file-medical me-1"></i>ดูใบรับรองแพทย์ปัจจุบัน
            </a>
            <small class="text-muted">(อัปโหลดใหม่เพื่อแทนที่)</small>
          </div>
          <?php endif;?>
          <input type="file" name="medical_cert" id="medCertFileEdit"
                 class="form-control" accept=".pdf,.jpg,.jpeg,.png">
          <div class="form-text">รองรับ PDF, JPG, PNG ขนาดไม่เกิน 5MB</div>
        </div>
      </div>
    </div>
    <div class="mt-4 d-flex gap-2">
      <button type="submit" class="btn btn-primary"><i class="bi bi-save me-1"></i>บันทึกการแก้ไข</button>
      <a href="<?=base_url('employee/leave')?>" class="btn btn-outline-secondary">ยกเลิก</a>
    </div>
    <?=form_close()?>
  </div>
</div>
<script>
function toggleHour(v){
  document.getElementById('hourSection').style.display = v==='hour' ? '' : 'none';
}
function calcDays(){
  var s = document.querySelector('[name=start_date]').value;
  var e = document.querySelector('[name=end_date]').value;
  var u = document.getElementById('leaveUnit').value;
  var di = document.getElementById('daysInfo');
  if(s && e && u==='day'){
    var d = Math.round((new Date(e)-new Date(s))/86400000)+1;
    if(d>0){ di.textContent='รวม '+d+' วัน'; return; }
  }
  di.textContent='';
}

// [ข้อ 2] แสดง/ซ่อนใบรับรองแพทย์ตาม leave_type ที่เลือก
var _sickKw = ['ลาป่วย','sick','ป่วย'];
function checkMedCert(sel) {
  var wrap = document.getElementById('medCertEditWrap');
  if (!wrap || !sel) return;
  var txt  = sel.options[sel.selectedIndex]
             ? sel.options[sel.selectedIndex].text.toLowerCase() : '';
  var sick = _sickKw.some(function(k){ return txt.indexOf(k) !== -1; });
  wrap.style.display = sick ? '' : 'none';
}

// init ตอนโหลดหน้า — ถ้าประเภทที่บันทึกไว้เป็นลาป่วย ให้แสดงบล็อกทันที
document.addEventListener('DOMContentLoaded', function(){
  var sel = document.getElementById('leaveTypeEdit');
  if (sel) checkMedCert(sel);
});
</script>
