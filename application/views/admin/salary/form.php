<?php defined('BASEPATH') OR exit(); $r=$rec; ?>
<div class="card" style="max-width:860px">
  <div class="card-header"><i class="bi bi-cash me-2"></i><?=$page_title?></div>
  <div class="card-body">
    <?=form_open($r?'admin/salary/update/'.$r->id:'admin/salary/store')?>
    <input type="hidden" name="<?=$this->security->get_csrf_token_name()?>" value="<?=$this->security->get_csrf_hash()?>">
    <div class="row g-3">
      <div class="col-md-6"><label class="form-label">พนักงาน *</label><select name="user_id" class="form-select" required><?php foreach($employees as $e):?><option value="<?=$e->id?>" <?=($r&&$r->user_id==$e->id)?'selected':''?>><?=$e->employee_id?> – <?=$e->first_name.' '.$e->last_name?></option><?php endforeach;?></select></div>
      <div class="col-md-3"><label class="form-label">ปี</label><input type="number" name="salary_year" class="form-control" value="<?=$r?$r->salary_year:$year?>" required></div>
      <div class="col-md-3"><label class="form-label">เดือน</label><input type="number" name="salary_month" class="form-control" min="1" max="12" value="<?=$r?$r->salary_month:$month?>" required></div>
      <div class="col-12"><hr class="my-1"><div class="fw-semibold small text-muted mb-1">รายได้</div></div>
      <?php $inc_fields=[['base_salary','เงินเดือนฐาน'],['commission','ค่าคอมมิชชัน'],['ot_pay','ค่าล่วงเวลา (OT)'],['monthly_bonus','โบนัสรายเดือน'],['special_bonus','โบนัสพิเศษ'],['other_income','รายได้อื่นๆ']]; foreach($inc_fields as $f):?>
      <div class="col-md-4"><label class="form-label"><?=$f[1]?></label><div class="input-group"><span class="input-group-text">฿</span><input type="number" name="<?=$f[0]?>" class="form-control income" value="<?=$r?(float)$r->{$f[0]}:0?>" min="0" step="0.01"></div></div>
      <?php endforeach;?>
      <div class="col-12"><hr class="my-1"><div class="fw-semibold small text-muted mb-1">รายการหัก</div></div>
      <?php $ded_fields=[['social_security_deduct','หักประกันสังคม'],['tax_deduct','หักภาษี ณ ที่จ่าย'],['absent_deduct','หักขาดงาน'],['late_deduct','หักมาสาย'],['other_deduct','หักอื่นๆ']]; foreach($ded_fields as $f):?>
      <div class="col-md-4"><label class="form-label"><?=$f[1]?></label><div class="input-group"><span class="input-group-text">฿</span><input type="number" name="<?=$f[0]?>" class="form-control deduct" value="<?=$r?(float)$r->{$f[0]}:0?>" min="0" step="0.01"></div></div>
      <?php endforeach;?>
      <div class="col-12">
        <div class="p-3 rounded" style="background:#f0f9ff;border:1px solid #bae6fd">
          <div class="row text-center g-0">
            <div class="col-4"><div style="font-size:.75rem;color:#6b7280">รายได้รวม</div><div class="fw-bold text-success" style="font-size:1.25rem" id="showGross">฿0</div></div>
            <div class="col-4"><div style="font-size:.75rem;color:#6b7280">หักรวม</div><div class="fw-bold text-danger" style="font-size:1.25rem" id="showDed">฿0</div></div>
            <div class="col-4"><div style="font-size:.75rem;color:#6b7280">เงินเดือนสุทธิ</div><div class="fw-bold text-primary" style="font-size:1.25rem" id="showNet">฿0</div></div>
          </div>
        </div>
      </div>
      <div class="col-md-4"><label class="form-label">สถานะ</label><select name="payment_status" class="form-select"><option value="draft" <?=($r&&$r->payment_status==='draft')?'selected':''?>>ร่าง</option><option value="processed" <?=($r&&$r->payment_status==='processed')?'selected':''?>>ประมวลผลแล้ว</option><option value="paid" <?=($r&&$r->payment_status==='paid')?'selected':''?>>จ่ายแล้ว</option></select></div>
      <div class="col-8"><label class="form-label">หมายเหตุ</label><input type="text" name="note" class="form-control" value="<?=$r?htmlspecialchars($r->note??''):''?>"></div>
    </div>
    <div class="mt-4 d-flex gap-2">
      <button type="submit" class="btn btn-primary"><i class="bi bi-save me-1"></i>บันทึก</button>
      <a href="<?=base_url('admin/salary')?>" class="btn btn-outline-secondary">ยกเลิก</a>
    </div>
    <?=form_close()?>
  </div>
</div>
<?php $extra_js='<script>
function calc(){
  var b=parseFloat(document.querySelector("[name=base_salary]").value)||0;
  var inc=b; document.querySelectorAll(".income").forEach(function(i){inc+=parseFloat(i.value)||0;});
  var ded=0; document.querySelectorAll(".deduct").forEach(function(i){ded+=parseFloat(i.value)||0;});
  var fmt=function(n){return "฿"+n.toLocaleString("th-TH",{minimumFractionDigits:2});};
  document.getElementById("showGross").textContent=fmt(inc);
  document.getElementById("showDed").textContent=fmt(ded);
  document.getElementById("showNet").textContent=fmt(inc-ded);
}
document.querySelectorAll("input[type=number]").forEach(function(i){i.addEventListener("input",calc);});
calc();
</script>';?>
