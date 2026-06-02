<?php defined('BASEPATH') OR exit();
$type_label = array('monthly'=>'รายเดือน','special'=>'พิเศษ','sales'=>'ตามยอดขาย','annual'=>'ประจำปี');
$type_color = array('monthly'=>'primary','special'=>'warning','sales'=>'success','annual'=>'danger');
$type_icon  = array('monthly'=>'calendar-check','special'=>'gift','sales'=>'graph-up-arrow','annual'=>'trophy');
$mn_th = array('1'=>'ม.ค.','2'=>'ก.พ.','3'=>'มี.ค.','4'=>'เม.ย.','5'=>'พ.ค.','6'=>'มิ.ย.',
               '7'=>'ก.ค.','8'=>'ส.ค.','9'=>'ก.ย.','10'=>'ต.ค.','11'=>'พ.ย.','12'=>'ธ.ค.');
?>

<!-- สรุปยอดตาม type -->
<?php
$totals = array('monthly'=>0,'special'=>0,'sales'=>0,'annual'=>0,'all'=>0);
foreach($bonuses as $b){
    $totals[$b->bonus_type] = ($totals[$b->bonus_type]??0) + $b->amount;
    $totals['all'] += $b->amount;
}
?>
<div class="row g-2 mb-3">
  <?php foreach(array('monthly','special','sales','annual') as $t):?>
  <div class="col-md-4">
    <div class="stat-card">
      <div class="s-ico" style="background:<?=$t==='monthly'?'#eff6ff':($t==='special'?'#fffbeb':($t==='sales'?'#f0fdf4':'#fef2f2'))?>;color:var(--<?=$type_color[$t]?>)">
        <i class="bi bi-<?=$type_icon[$t]?>"></i>
      </div>
      <div>
        <div class="s-lbl">โบนัส<?=$type_label[$t]?></div>
        <div class="s-val" style="font-size:1.1rem">฿<?=number_format($totals[$t],0)?></div>
        <div class="s-sub"><?=count(array_filter((array)$bonuses,function($b) use($t){return $b->bonus_type===$t;}))?> รายการ</div>
      </div>
    </div>
  </div>
  <?php endforeach;?>
</div>

<div class="row g-3">
  <!-- ── ฟอร์มบันทึก ── -->
  <div class="col-lg-4">
    <div class="card">
      <div class="card-header"><i class="bi bi-plus-circle me-2 text-primary"></i>บันทึกโบนัส</div>
      <div class="card-body">
        <?=form_open('admin/salary/store_bonus')?>
        <input type="hidden" name="<?=$this->security->get_csrf_token_name()?>" value="<?=$this->security->get_csrf_hash()?>">

        <div class="mb-3">
          <label class="form-label">พนักงาน <span class="text-danger">*</span></label>
          <select name="user_id" class="form-select ts-select" required>
            <option value="">-- เลือกพนักงาน --</option>
            <?php foreach($employees as $e):?>
            <option value="<?=$e->id?>"><?=$e->employee_id?> – <?=$e->first_name.' '.$e->last_name?></option>
            <?php endforeach;?>
          </select>
        </div>

        <div class="mb-3">
          <label class="form-label">ประเภทโบนัส <span class="text-danger">*</span></label>
          <div class="d-flex gap-2 flex-wrap" id="typeButtons">
            <?php foreach($type_label as $val=>$lbl):?>
            <label class="d-flex align-items-center gap-1 px-3 py-2 rounded-3 border type-btn"
                   style="cursor:pointer;font-size:.84rem;font-weight:500"
                   data-type="<?=$val?>">
              <input type="radio" name="bonus_type" value="<?=$val?>" class="d-none" <?=$val==='special'?'checked':''?>>
              <i class="bi bi-<?=$type_icon[$val]?>"></i><?=$lbl?>
            </label>
            <?php endforeach;?>
          </div>
        </div>

        <div class="row g-2 mb-3">
          <div class="col-6">
            <label class="form-label">ปี <span class="text-danger">*</span></label>
            <input type="number" name="bonus_year" class="form-control" value="<?=$year?>" required>
          </div>
          <!-- เดือน (สำหรับโบนัสรายเดือน) -->
          <div class="col-6" id="monthWrap" style="display:none">
            <label class="form-label">เดือน</label>
            <select name="bonus_month" class="form-select">
              <?php foreach($mn_th as $k=>$v):?>
              <option value="<?=$k?>" <?=date('n')==$k?'selected':''?>><?=$v?></option>
              <?php endforeach;?>
            </select>
          </div>
        </div>

        <div class="mb-3">
          <label class="form-label">จำนวนเงิน <span class="text-danger">*</span></label>
          <div class="input-group">
            <span class="input-group-text">฿</span>
            <input type="number" name="amount" class="form-control" min="0" step="0.01" required placeholder="0.00">
          </div>
        </div>

        <div class="mb-3">
          <label class="form-label">วันที่จ่าย</label>
          <input type="date" name="payment_date" class="form-control" value="<?=date('Y-m-d')?>">
        </div>

        <div class="mb-3">
          <label class="form-label">หมายเหตุ</label>
          <textarea name="remarks" class="form-control" rows="2" placeholder="ระบุรายละเอียดเพิ่มเติม"></textarea>
        </div>

        <button type="submit" class="btn btn-primary w-100">
          <i class="bi bi-save me-1"></i>บันทึกโบนัส
        </button>
        <?=form_close()?>
      </div>
    </div>
  </div>

  <!-- ── รายการ ── -->
  <div class="col-lg-8">
    <!-- Filter bar -->
    <div class="card mb-2">
      <div class="card-body py-2">
        <?=form_open('admin/salary/bonus',array('method'=>'GET','class'=>'row g-2 align-items-end'))?>
          <div class="col-auto">
            <select name="year" class="form-select form-select-sm">
              <?php for($y=date('Y');$y>=date('Y')-5;$y--):?>
              <option value="<?=$y?>" <?=$year==$y?'selected':''?>><?=$y?></option>
              <?php endfor;?>
            </select>
          </div>
          <div class="col-auto">
            <select name="type" class="form-select form-select-sm">
              <option value="">-- ทุกประเภท --</option>
              <?php foreach($type_label as $v=>$l):?>
              <option value="<?=$v?>" <?=$sel_type===$v?'selected':''?>><?=$l?></option>
              <?php endforeach;?>
            </select>
          </div>
          <div class="col-md-4">
            <select name="user_id" class="form-select form-select-sm ts-select">
              <option value="">-- ทุกคน --</option>
              <?php foreach($employees as $e):?>
              <option value="<?=$e->id?>" <?=$sel_uid==$e->id?'selected':''?>><?=$e->employee_id?> – <?=$e->first_name.' '.$e->last_name?></option>
              <?php endforeach;?>
            </select>
          </div>
          <div class="col-auto">
            <button type="submit" class="btn btn-primary btn-sm"><i class="bi bi-search"></i></button>
          </div>
        <?=form_close()?>
      </div>
    </div>

    <div class="card">
      <div class="card-header d-flex justify-content-between align-items-center">
        <span>รายการโบนัสปี <?=$year?> <span class="badge bg-secondary"><?=count($bonuses)?></span></span>
        <span class="fw-semibold text-success">รวม ฿<?=number_format($totals['all'],0)?></span>
      </div>
      <div class="card-body p-0">
        <div class="table-responsive">
          <table class="table table-hover mb-0">
            <thead>
              <tr><th>ประเภท</th><th>พนักงาน</th><th>ปี/เดือน</th><th>จำนวน</th><th>วันที่จ่าย</th><th>หมายเหตุ</th><th></th></tr>
            </thead>
            <tbody>
              <?php if(!empty($bonuses)):foreach($bonuses as $b):?>
              <tr>
                <td>
                  <span class="badge bg-<?=$type_color[$b->bonus_type]?? 'secondary'?> text-<?=in_array($b->bonus_type??'',['special','annual'])?'dark':''?>">
                    <i class="bi bi-<?=$type_icon[$b->bonus_type]??'gift'?> me-1"></i>
                    <?=$type_label[$b->bonus_type]??$b->bonus_type?>
                  </span>
                </td>
                <td>
                  <div class="fw-semibold" style="font-size:.875rem"><?=$b->first_name.' '.$b->last_name?></div>
                  <div style="font-size:.72rem;color:#6b7280"><?=$b->employee_id?> · <?=$b->dept_name??'–'?></div>
                </td>
                <td style="font-size:.83rem">
                  <?=$b->bonus_year?>
                  <?php if($b->bonus_type==='monthly' && !empty($b->bonus_month)):?>
                  <br><span class="badge bg-light text-dark border"><?=$mn_th[$b->bonus_month]??$b->bonus_month?></span>
                  <?php endif;?>
                </td>
                <td class="fw-semibold text-success">฿<?=number_format($b->amount,2)?></td>
                <td style="font-size:.83rem"><?=$b->payment_date?date('d/m/Y',strtotime($b->payment_date)):'-'?></td>
                <td style="font-size:.8rem;max-width:140px"><?=htmlspecialchars($b->remarks??'')?></td>
                <td>
                  <a href="<?=base_url('admin/salary/delete_bonus/'.$b->id)?>"
                     onclick="return confirm('ลบโบนัสนี้?')"
                     class="btn btn-outline-danger btn-sm px-2 py-0">
                    <i class="bi bi-trash"></i>
                  </a>
                </td>
              </tr>
              <?php endforeach;else:?>
              <tr><td colspan="7" class="text-center text-muted py-5">
                <i class="bi bi-gift fs-2 d-block mb-2"></i>ไม่มีข้อมูลโบนัส
              </td></tr>
              <?php endif;?>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</div>

<?php $extra_js = '<script>
// Type button toggle
var btns = document.querySelectorAll(".type-btn");
btns.forEach(function(btn){
  var radio = btn.querySelector("input[type=radio]");
  // init style
  if(radio && radio.checked) btn.style.cssText += ";background:#eff6ff;border-color:var(--pri)!important;color:var(--pri)";

  btn.addEventListener("click", function(){
    btns.forEach(function(b){
      b.style.background=""; b.style.borderColor=""; b.style.color="";
    });
    btn.style.cssText += ";background:#eff6ff;border-color:var(--pri)!important;color:var(--pri)";
    radio.checked = true;
    // แสดง/ซ่อนช่องเดือน
    document.getElementById("monthWrap").style.display = (radio.value==="monthly") ? "" : "none";
  });
});
// init month wrap
(function(){
  var checked = document.querySelector("[name=bonus_type]:checked");
  if(checked) document.getElementById("monthWrap").style.display = (checked.value==="monthly") ? "" : "none";
})();
</script>'; ?>
