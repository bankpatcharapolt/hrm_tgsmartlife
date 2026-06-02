<?php defined('BASEPATH') OR exit(); ?>
<div class="card mb-3">
  <div class="card-body py-2">
    <?=form_open('admin/salary/slips',array('method'=>'GET','class'=>'row g-2 align-items-end'))?>
      <div class="col-md-2"><select name="year" class="form-select form-select-sm"><?php for($y=date('Y');$y>=date('Y')-5;$y--):?><option value="<?=$y?>" <?=$year==$y?'selected':''?>><?=$y?></option><?php endfor;?></select></div>
      <div class="col-md-2"><select name="month" class="form-select form-select-sm"><?php $mn=array('0'=>'ทุกเดือน','1'=>'ม.ค.','2'=>'ก.พ.','3'=>'มี.ค.','4'=>'เม.ย.','5'=>'พ.ค.','6'=>'มิ.ย.','7'=>'ก.ค.','8'=>'ส.ค.','9'=>'ก.ย.','10'=>'ต.ค.','11'=>'พ.ย.','12'=>'ธ.ค.'); foreach($mn as $k=>$v):?><option value="<?=$k?>" <?=$month==$k?'selected':''?>><?=$v?></option><?php endforeach;?></select></div>
      <div class="col-md-3"><select name="user_id" class="form-select form-select-sm ts-select"><option value="">-- ทุกคน --</option><?php foreach($employees as $e):?><option value="<?=$e->id?>" <?=$sel_uid==$e->id?'selected':''?>><?=$e->employee_id?> – <?=$e->first_name.' '.$e->last_name?></option><?php endforeach;?></select></div>
      <div class="col-auto"><button type="submit" class="btn btn-primary btn-sm"><i class="bi bi-search"></i></button></div>
      <div class="col-auto ms-auto"><a href="<?=base_url('admin/salary')?>" class="btn btn-outline-secondary btn-sm">← กลับ</a></div>
    <?=form_close()?>
  </div>
</div>
<div class="card">
  <div class="card-header"><i class="bi bi-file-earmark-pdf me-2 text-danger"></i>รายการสลิปเงินเดือน <span class="badge bg-secondary"><?=count($slips)?></span></div>
  <div class="card-body p-0">
    <div class="table-responsive">
      <table class="table table-hover mb-0">
        <thead><tr><th>เดือน/ปี</th><th>พนักงาน</th><th>แผนก</th><th>ชื่อไฟล์</th><th>ขนาด</th><th>จัดการ</th></tr></thead>
        <tbody>
          <?php if(!empty($slips)):foreach($slips as $s):?>
          <tr>
            <td><span class="badge bg-secondary"><?php $mn2=array(1=>'ม.ค.',2=>'ก.พ.',3=>'มี.ค.',4=>'เม.ย.',5=>'พ.ค.',6=>'มิ.ย.',7=>'ก.ค.',8=>'ส.ค.',9=>'ก.ย.',10=>'ต.ค.',11=>'พ.ย.',12=>'ธ.ค.'); echo $mn2[$s->slip_month].' '.$s->slip_year;?></span></td>
            <td><div class="fw-semibold" style="font-size:.875rem"><?=$s->first_name.' '.$s->last_name?></div><div style="font-size:.72rem;color:#6b7280"><?=$s->employee_id?></div></td>
            <td style="font-size:.82rem"><?=$s->dept_name??'–'?></td>
            <td><a href="<?=base_url($s->file_path)?>" target="_blank" class="text-danger text-decoration-none"><i class="bi bi-file-earmark-pdf me-1"></i><?=htmlspecialchars($s->file_name)?></a></td>
            <td style="font-size:.82rem"><?=number_format($s->file_size/1024,0)?> KB</td>
            <td>
              <a href="<?=base_url($s->file_path)?>" target="_blank" class="btn btn-outline-secondary btn-sm px-2 py-0" title="ดู"><i class="bi bi-eye"></i></a>
              <a href="<?=base_url('admin/salary/delete_slip/'.$s->id)?>" onclick="return confirm('ลบสลิปนี้?')" class="btn btn-outline-danger btn-sm px-2 py-0 ms-1" title="ลบ"><i class="bi bi-trash"></i></a>
            </td>
          </tr>
          <?php endforeach;else:?><tr><td colspan="6" class="text-center text-muted py-5"><i class="bi bi-file-earmark-x fs-1 d-block mb-2"></i>ไม่มีรายการสลิป</td></tr><?php endif;?>
        </tbody>
      </table>
    </div>
  </div>
</div>
