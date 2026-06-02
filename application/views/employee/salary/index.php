<?php defined('BASEPATH') OR exit(); ?>
<div class="row g-3">
  <div class="col-lg-8">
    <div class="card mb-3">
      <div class="card-header d-flex justify-content-between align-items-center">
        <span><i class="bi bi-cash-stack me-2 text-primary"></i>รายการเงินเดือน</span>
        <select class="form-select form-select-sm" style="width:auto" onchange="window.location='<?=base_url('employee/salary')?>?year='+this.value"><?php for($y=date('Y');$y>=date('Y')-3;$y--):?><option value="<?=$y?>" <?=$year==$y?'selected':''?>><?=$y?></option><?php endfor;?></select>
      </div>
      <div class="card-body p-0">
        <div class="table-responsive">
          <table class="table table-hover mb-0">
            <thead><tr><th>เดือน/ปี</th><th>รายได้รวม</th><th>หักรวม</th><th>สุทธิ</th><th>สถานะ</th></tr></thead>
            <tbody>
              <?php if(!empty($records)):foreach($records as $r):$d=$r->social_security_deduct+$r->tax_deduct+$r->other_deduct+$r->absent_deduct+$r->late_deduct;?>
              <tr>
                <td style="font-size:.875rem"><?php $mn=array('1'=>'ม.ค.','2'=>'ก.พ.','3'=>'มี.ค.','4'=>'เม.ย.','5'=>'พ.ค.','6'=>'มิ.ย.','7'=>'ก.ค.','8'=>'ส.ค.','9'=>'ก.ย.','10'=>'ต.ค.','11'=>'พ.ย.','12'=>'ธ.ค.'); echo $mn[$r->salary_month].' '.$r->salary_year;?></td>
                <td class="text-success">฿<?=number_format($r->gross_salary,2)?></td>
                <td class="text-danger">-฿<?=number_format($d,2)?></td>
                <td class="fw-semibold text-primary">฿<?=number_format($r->net_salary,2)?></td>
                <td><span class="badge bg-<?=$r->payment_status==='paid'?'success':($r->payment_status==='processed'?'warning text-dark':'secondary')?>"><?=$r->payment_status==='paid'?'จ่ายแล้ว':($r->payment_status==='processed'?'ประมวลผล':'รอดำเนินการ')?></span></td>
              </tr>
              <?php endforeach;else:?><tr><td colspan="5" class="text-center text-muted py-4">ไม่มีข้อมูลเงินเดือนปีนี้</td></tr><?php endif;?>
            </tbody>
          </table>
        </div>
      </div>
    </div>
    <!-- Bonuses 3 ประเภท -->
    <div class="card">
      <div class="card-header"><i class="bi bi-gift me-2 text-warning"></i>โบนัสของฉัน</div>
      <?php
      $type_label = array('monthly'=>'โบนัสรายเดือน','special'=>'โบนัสพิเศษ','sales'=>'โบนัสตามยอดขาย');
      $type_color = array('monthly'=>'primary','special'=>'warning','sales'=>'success');
      $type_icon  = array('monthly'=>'calendar-check','special'=>'gift','sales'=>'graph-up-arrow');
      $mn_th = array('1'=>'ม.ค.','2'=>'ก.พ.','3'=>'มี.ค.','4'=>'เม.ย.','5'=>'พ.ค.','6'=>'มิ.ย.',
                     '7'=>'ก.ค.','8'=>'ส.ค.','9'=>'ก.ย.','10'=>'ต.ค.','11'=>'พ.ย.','12'=>'ธ.ค.');
      // สรุปยอดแต่ละ type
      $totals = array('monthly'=>0,'special'=>0,'sales'=>0);
      if(!empty($bonuses)) foreach($bonuses as $b){ $totals[$b->bonus_type??'special'] += $b->amount; }
      ?>
      <?php if(!empty($bonuses)):?>
      <!-- สรุปการ์ด 3 ประเภท -->
      <div class="card-body border-bottom pb-2">
        <div class="row g-2">
          <?php foreach($type_label as $t=>$lbl):?>
          <div class="col-4 text-center">
            <div class="small text-muted mb-1"><i class="bi bi-<?=$type_icon[$t]?> me-1"></i><?=$lbl?></div>
            <div class="fw-bold text-<?=$type_color[$t]?>" style="font-size:1rem">
              ฿<?=number_format($totals[$t],0)?>
            </div>
          </div>
          <?php endforeach;?>
        </div>
      </div>
      <!-- รายละเอียด -->
      <div class="card-body p-0">
        <table class="table table-sm mb-0">
          <thead>
            <tr><th>ประเภท</th><th>ปี/เดือน</th><th>จำนวน</th><th>วันที่จ่าย</th><th>หมายเหตุ</th></tr>
          </thead>
          <tbody>
            <?php foreach($bonuses as $b):
              $btype = $b->bonus_type ?? 'special';
            ?>
            <tr>
              <td>
                <span class="badge bg-<?=$type_color[$btype]?> text-<?=$btype==='special'?'dark':''?>">
                  <i class="bi bi-<?=$type_icon[$btype]?> me-1"></i><?=$type_label[$btype]??$btype?>
                </span>
              </td>
              <td style="font-size:.83rem">
                <?=$b->bonus_year?>
                <?php if($btype==='monthly' && !empty($b->bonus_month)):?>
                <span class="badge bg-light text-dark border ms-1"><?=$mn_th[$b->bonus_month]??$b->bonus_month?></span>
                <?php endif;?>
              </td>
              <td class="fw-semibold text-success">฿<?=number_format($b->amount,2)?></td>
              <td style="font-size:.83rem"><?=$b->payment_date?date('d/m/Y',strtotime($b->payment_date)):'-'?></td>
              <td style="font-size:.8rem"><?=htmlspecialchars($b->remarks??'')?></td>
            </tr>
            <?php endforeach;?>
          </tbody>
        </table>
      </div>
      <?php else:?>
      <div class="card-body text-center text-muted py-4 small">
        <i class="bi bi-gift fs-2 d-block mb-2 text-warning opacity-50"></i>ยังไม่มีข้อมูลโบนัส
      </div>
      <?php endif;?>
    </div>
  </div>
  <div class="col-lg-4">
    <!-- Salary Slips -->
    <div class="card mb-3">
      <div class="card-header"><i class="bi bi-file-earmark-pdf me-2 text-danger"></i>สลิปเงินเดือน</div>
      <div class="card-body p-0">
        <div class="list-group list-group-flush">
          <?php if(!empty($slips)):foreach($slips as $s):?>
          <a href="<?=base_url($s->file_path)?>" target="_blank" class="list-group-item list-group-item-action d-flex align-items-center gap-2 py-2">
            <i class="bi bi-file-earmark-pdf text-danger"></i>
            <div class="flex-fill"><div style="font-size:.83rem"><?php $mn=array('1'=>'ม.ค.','2'=>'ก.พ.','3'=>'มี.ค.','4'=>'เม.ย.','5'=>'พ.ค.','6'=>'มิ.ย.','7'=>'ก.ค.','8'=>'ส.ค.','9'=>'ก.ย.','10'=>'ต.ค.','11'=>'พ.ย.','12'=>'ธ.ค.'); echo 'สลิป '.$mn[$s->slip_month].' '.$s->slip_year;?></div><div style="font-size:.72rem;color:#6b7280"><?=htmlspecialchars($s->file_name)?></div></div>
            <i class="bi bi-download text-muted"></i>
          </a>
          <?php endforeach;else:?><div class="list-group-item text-center text-muted py-3 small">ยังไม่มีสลิปเงินเดือน</div><?php endif;?>
        </div>
      </div>
    </div>
    <!-- Tax Documents -->
    <div class="card">
      <div class="card-header"><i class="bi bi-file-earmark-text me-2 text-primary"></i>เอกสารทวิ 50</div>
      <div class="card-body p-0">
        <div class="list-group list-group-flush">
          <?php if(!empty($tax_docs)):foreach($tax_docs as $t):?>
          <a href="<?=base_url($t->file_path)?>" target="_blank" class="list-group-item list-group-item-action d-flex align-items-center gap-2 py-2">
            <i class="bi bi-file-earmark-text text-primary"></i>
            <div class="flex-fill"><div style="font-size:.83rem">ทวิ 50 ปี <?=$t->tax_year?></div><div style="font-size:.72rem;color:#6b7280"><?=htmlspecialchars($t->file_name)?></div></div>
            <i class="bi bi-download text-muted"></i>
          </a>
          <?php endforeach;else:?><div class="list-group-item text-center text-muted py-3 small">ยังไม่มีเอกสารทวิ 50</div><?php endif;?>
        </div>
      </div>
    </div>
  </div>
</div>
