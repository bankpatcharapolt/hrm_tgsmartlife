<?php defined('BASEPATH') OR exit(); $r=$role; ?>
<div class="card" style="max-width:700px">
  <div class="card-header"><i class="bi bi-shield me-2"></i>แก้ไขบทบาท: <?=$r->name?></div>
  <div class="card-body">
    <?=form_open('admin/roles/update/'.$r->id)?>
    <input type="hidden" name="<?=$this->security->get_csrf_token_name()?>" value="<?=$this->security->get_csrf_hash()?>">
    <div class="row g-3">
      <div class="col-12"><label class="form-label">ชื่อบทบาท</label><input type="text" name="name" class="form-control" value="<?=$r->name?>" required></div>
      <div class="col-md-6"><label class="form-label">เวลาเข้างาน</label><input type="time" name="work_start_time" class="form-control" value="<?=substr($r->work_start_time,0,5)?>"></div>
      <div class="col-md-6"><label class="form-label">เวลาออกงาน</label><input type="time" name="work_end_time" class="form-control" value="<?=substr($r->work_end_time,0,5)?>"></div>
      <div class="col-md-4"><label class="form-label">โควต้าลาป่วย (วัน)</label><input type="number" name="leave_quota_sick" class="form-control" value="<?=$r->leave_quota_sick?>" min="0"></div>
      <div class="col-md-4"><label class="form-label">โควต้าลากิจ (วัน)</label><input type="number" name="leave_quota_personal" class="form-control" value="<?=$r->leave_quota_personal?>" min="0"></div>
      <div class="col-md-4"><label class="form-label">โควต้าลาพักร้อน (วัน)</label><input type="number" name="leave_quota_vacation" class="form-control" value="<?=$r->leave_quota_vacation?>" min="0"></div>
      <div class="col-12"><div class="fw-semibold small text-muted mb-2">สิทธิ์การเข้าถึง</div>
        <div class="row g-2">
          <?php $pc=['can_checkin'=>'ลงเวลาเข้า-ออกงาน','can_view_own_salary'=>'ดูเงินเดือนตัวเอง','can_approve_leave'=>'อนุมัติการลา','can_manage_employees'=>'จัดการข้อมูลพนักงาน','can_view_sales'=>'ดูรายงานยอดขาย','can_send_notifications'=>'ส่งการแจ้งเตือน','can_manage_salary'=>'จัดการเงินเดือน','can_upload_documents'=>'อัปโหลดเอกสาร','can_view_reports'=>'ดูรายงานทั้งหมด','can_monitor_attendance'=>'ตรวจสอบการเข้างาน']; foreach($pc as $key=>$label):?>
          <div class="col-md-6"><div class="form-check form-switch"><input class="form-check-input" type="checkbox" name="<?=$key?>" id="<?=$key?>" value="1" <?=$r->{$key}?'checked':''?>><label class="form-check-label small" for="<?=$key?>"><?=$label?></label></div></div>
          <?php endforeach;?>
        </div>
      </div>
    </div>
    <div class="mt-4 d-flex gap-2">
      <button type="submit" class="btn btn-primary"><i class="bi bi-save me-1"></i>บันทึก</button>
      <a href="<?=base_url('admin/roles')?>" class="btn btn-outline-secondary">ยกเลิก</a>
    </div>
    <?=form_close()?>
  </div>
</div>
