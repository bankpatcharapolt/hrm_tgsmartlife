<?php defined('BASEPATH') OR exit(); $r=$role; ?>
<div class="card" style="max-width:700px">
  <div class="card-header"><i class="bi bi-shield me-2"></i>แก้ไขบทบาท: <?=$r->name?></div>
  <div class="card-body">
    <?=form_open('admin/roles/update/'.$r->id)?>
    <input type="hidden" name="<?=$this->security->get_csrf_token_name()?>" value="<?=$this->security->get_csrf_hash()?>">
    <div class="row g-3">
      <div class="col-12"><label class="form-label">ชื่อบทบาท</label><input type="text" name="name" class="form-control" value="<?=$r->name?>" required></div>
      <div class="col-md-6"><label class="form-label">เวลาเข้างาน</label><div class="leave-time-wrap" id="wstWrap">
        <input type="hidden" name="work_start_time" id="wstHidden" value="<?=!empty($r->work_start_time)?$r->work_start_time:'09:00:00'?>">
      </div></div>
      <div class="col-md-6"><label class="form-label">เวลาออกงาน</label><div class="leave-time-wrap" id="wetWrap">
        <input type="hidden" name="work_end_time" id="wetHidden" value="<?=!empty($r->work_end_time)?$r->work_end_time:'18:00:00'?>">
      </div></div>
      <div class="col-md-4"><label class="form-label">โควต้าลาป่วย (วัน)</label><input type="number" name="leave_quota_sick" class="form-control" value="<?=$r->leave_quota_sick?>" min="0"></div>
      <div class="col-md-4"><label class="form-label">โควต้าลากิจ (วัน)</label><input type="number" name="leave_quota_personal" class="form-control" value="<?=$r->leave_quota_personal?>" min="0"></div>
      <div class="col-md-4"><label class="form-label">โควต้าลาพักร้อน (วัน)</label><input type="number" name="leave_quota_vacation" class="form-control" value="<?=$r->leave_quota_vacation?>" min="0"></div>
      <div class="col-12"><div class="fw-semibold small text-muted mb-2">สิทธิ์การเข้าถึง</div>
        <div class="row g-2">
          <?php $pc=array('can_checkin'=>'ลงเวลาเข้า-ออกงาน','can_view_own_salary'=>'ดูเงินเดือนตัวเอง','can_approve_leave'=>'อนุมัติการลา','can_manage_employees'=>'จัดการข้อมูลพนักงาน','can_view_sales'=>'ดูรายงานยอดขาย','can_send_notifications'=>'ส่งการแจ้งเตือน','can_manage_salary'=>'จัดการเงินเดือน','can_upload_documents'=>'อัปโหลดเอกสาร','can_view_reports'=>'ดูรายงานทั้งหมด','can_monitor_attendance'=>'ตรวจสอบการเข้างาน'); foreach($pc as $key=>$label):?>
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

<script>
$(document).ready(function(){
  function buildRTW(wrapId, hiddenId) {
    var $w=$("#"+wrapId), iv=$("#"+hiddenId).val()||"00:00";
    var p=iv.split(":"), ch=parseInt(p[0],10)||0, cm=parseInt(p[1],10)||0;
    var $selH = $("<select class=\"dt-hh\"></select>");
    for(var h=0;h<=23;h++){var hv=(h<10?"0":"")+h;var $o=$("<option>").val(hv).text(hv);if(h===ch)$o.prop("selected",true);$selH.append($o);}
    var $selM = $("<select class=\"dt-mm\"></select>");
    for(var m=0;m<=59;m++){var mv=(m<10?"0":"")+m;var $p=$("<option>").val(mv).text(mv);if(m===cm)$p.prop("selected",true);$selM.append($p);}
    $w.find(".dt-time-wrap").remove();
    var $t=$('<div class="dt-time-wrap" style="flex:1"></div>');
    $t.append('<select class="dt-hh">'+sh+'</select>');
    $t.append('<span class="dt-colon">:</span>');
    $t.append('<select class="dt-mm">'+sm+'</select>');
    $w.prepend($t);
    function s(){$("#"+hiddenId).val($w.find(".dt-hh").val()+":"+$w.find(".dt-mm").val()+":00");}
    $w.find(".dt-hh,.dt-mm").on("change",s); s();
  }
  buildRTW("wstWrap","wstHidden");
  buildRTW("wetWrap","wetHidden");
});
</script>