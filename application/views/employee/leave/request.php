<?php defined('BASEPATH') OR exit(); ?>
<div class="card" style="max-width:640px">
  <div class="card-header"><i class="bi bi-calendar-plus me-2"></i>ยื่นคำขอลา</div>
  <div class="card-body">
    <?=form_open_multipart('employee/leave/store')?>
    <input type="hidden" name="<?=$this->security->get_csrf_token_name()?>" value="<?=$this->security->get_csrf_hash()?>">
    <div class="row g-3">
      <div class="col-12"><label class="form-label">ประเภทการลา *</label><select name="leave_type_id" class="form-select" required><option value="">-- เลือกประเภทการลา --</option><?php foreach($leave_types as $t):?><option value="<?=$t->id?>"><?=$t->name?><?=$t->quota_per_year>0?' (โควต้า '.$t->quota_per_year.' วัน/ปี)':' (ไม่จำกัด)'?></option><?php endforeach;?></select></div>
      <div class="col-md-6"><label class="form-label">วันที่เริ่มลา *</label><input type="date" name="start_date" class="form-control" id="startDate" onchange="calcDays()" required></div>
      <div class="col-md-6"><label class="form-label">วันที่สิ้นสุดการลา *</label><input type="date" name="end_date" class="form-control" id="endDate" onchange="calcDays()" required></div>
      <div class="col-12"><div class="alert alert-info py-2 px-3 mb-0" id="daysInfo" style="display:none"><i class="bi bi-info-circle me-2"></i>จำนวน <strong id="daysCount">0</strong> วัน</div></div>
      <div class="col-12"><label class="form-label">เหตุผล *</label><textarea name="reason" class="form-control" rows="3" required placeholder="กรุณาระบุเหตุผลในการลา"></textarea></div>
      <div class="col-12"><label class="form-label">เอกสารประกอบ (PDF/รูปภาพ)</label><input type="file" name="document" class="form-control" accept=".pdf,.jpg,.jpeg,.png"><div class="form-text text-muted">ขนาดไม่เกิน 5MB</div></div>
    </div>
    <div class="mt-4 d-flex gap-2">
      <button type="submit" class="btn btn-primary"><i class="bi bi-send me-1"></i>ส่งคำขอลา</button>
      <a href="<?=base_url('employee/leave')?>" class="btn btn-outline-secondary">ยกเลิก</a>
    </div>
    <?=form_close()?>
  </div>
</div>
<?php $extra_js='<script>
function calcDays(){var s=document.getElementById("startDate").value,e=document.getElementById("endDate").value;if(s&&e){var d=Math.round((new Date(e)-new Date(s))/86400000)+1;if(d>0){document.getElementById("daysCount").textContent=d;document.getElementById("daysInfo").style.display="";}}}
</script>';?>
