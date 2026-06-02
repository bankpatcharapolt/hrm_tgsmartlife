<?php defined('BASEPATH') OR exit(); ?>
<div class="row g-3">
  <div class="col-lg-4">
    <div class="card">
      <div class="card-header"><i class="bi bi-gift me-2 text-warning"></i>บันทึกโบนัสประจำปี</div>
      <div class="card-body">
        <?=form_open('admin/salary/store_bonus')?>
        <input type="hidden" name="<?=$this->security->get_csrf_token_name()?>" value="<?=$this->security->get_csrf_hash()?>">
        <div class="mb-3"><label class="form-label">พนักงาน</label><select name="user_id" class="form-select ts-select" required><option value="">-- เลือก --</option><?php foreach($employees as $e):?><option value="<?=$e->id?>"><?=$e->employee_id?> – <?=$e->first_name.' '.$e->last_name?></option><?php endforeach;?></select></div>
        <div class="mb-3"><label class="form-label">ปี</label><input type="number" name="bonus_year" class="form-control" value="<?=$year?>" required></div>
        <div class="mb-3"><label class="form-label">จำนวนเงิน (บาท)</label><div class="input-group"><span class="input-group-text">฿</span><input type="number" name="amount" class="form-control" min="0" step="0.01" required></div></div>
        <div class="mb-3"><label class="form-label">วันที่จ่าย</label><input type="date" name="payment_date" class="form-control"></div>
        <div class="mb-3"><label class="form-label">หมายเหตุ</label><textarea name="remarks" class="form-control" rows="2"></textarea></div>
        <button type="submit" class="btn btn-warning w-100"><i class="bi bi-save me-1"></i>บันทึกโบนัส</button>
        <?=form_close()?>
      </div>
    </div>
  </div>
  <div class="col-lg-8">
    <div class="card">
      <div class="card-header d-flex justify-content-between align-items-center">
        รายการโบนัสปี <?=$year?>
        <select class="form-select form-select-sm" style="width:auto" onchange="window.location='<?=base_url('admin/salary/bonus')?>?year='+this.value"><?php for($y=date('Y');$y>=date('Y')-5;$y--):?><option value="<?=$y?>" <?=$year==$y?'selected':''?>><?=$y?></option><?php endfor;?></select>
      </div>
      <div class="card-body p-0">
        <div class="table-responsive">
          <table class="table table-hover mb-0">
            <thead><tr><th>พนักงาน</th><th>แผนก</th><th>จำนวนเงิน</th><th>วันที่จ่าย</th><th>หมายเหตุ</th></tr></thead>
            <tbody>
              <?php if(!empty($bonuses)):foreach($bonuses as $b):?>
              <tr><td><?=$b->first_name.' '.$b->last_name?><div style="font-size:.72rem;color:#6b7280"><?=$b->employee_id?></div></td><td style="font-size:.83rem"><?=isset($b->dept_name)?$b->dept_name:'–'?></td><td class="text-success fw-semibold">฿<?=number_format($b->amount,2)?></td><td style="font-size:.83rem"><?=$b->payment_date?date('d/m/Y',strtotime($b->payment_date)):'-'?></td><td style="font-size:.8rem"><?=htmlspecialchars(isset($b->remarks)?$b->remarks:'')?></td></tr>
              <?php endforeach;else:?><tr><td colspan="5" class="text-center text-muted py-4">ไม่มีข้อมูลโบนัส</td></tr><?php endif;?>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</div>
