<?php defined('BASEPATH') OR exit(); $e=$emp; ?>
<div class="row g-3">
  <div class="col-md-4">
    <div class="card text-center p-3">
      <?php if($e->photo):?><img src="<?=base_url($e->photo)?>" class="rounded-circle mb-3" style="width:100px;height:100px;object-fit:cover;"><?php else:?><div style="width:100px;height:100px;background:#e0e7ff;border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:2.5rem;font-weight:700;color:#1a56db;margin:0 auto .75rem"><?=mb_substr($e->first_name,0,1)?></div><?php endif;?>
      <h5 class="mb-0"><?=$e->first_name.' '.$e->last_name?></h5>
      <?php if($e->nickname):?><div class="text-muted small">(<?=$e->nickname?>)</div><?php endif;?>
      <div class="mt-2"><span class="badge bg-primary"><?=$e->role_name?></span></div>
      <?php if($e->department_name):?><div class="text-muted small mt-1"><?=$e->department_name?></div><?php endif;?>
      <div class="mt-2"><code style="font-size:.8rem"><?=$e->employee_id?></code></div>
    </div>
    <div class="card mt-3">
      <div class="card-body p-0">
        <table class="table table-sm mb-0">
          <tr><td class="text-muted" style="width:40%">เงินเดือน</td><td class="fw-semibold text-primary">฿<?=number_format($e->base_salary,0)?></td></tr>
          <tr><td class="text-muted">เริ่มงาน</td><td><?=date('d/m/Y',strtotime($e->start_date))?></td></tr>
          <tr><td class="text-muted">สถานะ</td><td><span class="badge bg-<?=$e->status==='active'?'success':'secondary'?>"><?=$e->status==='active'?'ใช้งาน':'ไม่ใช้งาน'?></span></td></tr>
        </table>
      </div>
    </div>
  </div>
  <div class="col-md-8">
    <div class="card">
      <div class="card-header"><i class="bi bi-pencil me-2"></i>แก้ไขข้อมูลส่วนตัว</div>
      <div class="card-body">
        <?=form_open_multipart('employee/profile/update')?>
        <input type="hidden" name="<?=$this->security->get_csrf_token_name()?>" value="<?=$this->security->get_csrf_hash()?>">
        <div class="row g-3">
          <div class="col-md-6"><label class="form-label">ชื่อเล่น</label><input type="text" name="nickname" class="form-control" value="<?=htmlspecialchars($e->nickname??'')?>"></div>
          <div class="col-md-6"><label class="form-label">เบอร์โทร</label><input type="text" name="phone" class="form-control" value="<?=htmlspecialchars($e->phone??'')?>"></div>
          <div class="col-12"><label class="form-label">อีเมล</label><input type="email" name="email" class="form-control" value="<?=htmlspecialchars($e->email??'')?>"></div>
          <div class="col-12"><label class="form-label">ที่อยู่</label><textarea name="address" class="form-control" rows="2"><?=htmlspecialchars($e->address??'')?></textarea></div>
          <div class="col-12"><label class="form-label">รูปถ่าย</label><input type="file" name="photo" class="form-control" accept="image/*"></div>
          <div class="col-12"><hr><div class="fw-semibold small text-muted mb-2">เปลี่ยนรหัสผ่าน (เว้นว่างถ้าไม่ต้องการเปลี่ยน)</div></div>
          <div class="col-md-6"><label class="form-label">รหัสผ่านใหม่</label><input type="password" name="new_password" class="form-control" minlength="8" placeholder="อย่างน้อย 8 ตัวอักษร"></div>
          <div class="col-md-6"><label class="form-label">ยืนยันรหัสผ่านใหม่</label><input type="password" name="confirm_password" class="form-control"></div>
        </div>
        <div class="mt-4">
          <button type="submit" class="btn btn-primary"><i class="bi bi-save me-1"></i>บันทึกการเปลี่ยนแปลง</button>
        </div>
        <?=form_close()?>
      </div>
    </div>
    <!-- Read-only info -->
    <div class="card mt-3">
      <div class="card-header">ข้อมูลที่แก้ไขไม่ได้ (ติดต่อแอดมิน)</div>
      <div class="card-body p-0">
        <table class="table table-sm mb-0">
          <tr><th style="width:35%">ชื่อจริง-นามสกุล</th><td><?=$e->first_name.' '.$e->last_name?></td></tr>
          <tr><th>วันเกิด</th><td><?=$e->date_of_birth?date('d/m/Y',strtotime($e->date_of_birth)):'-'?></td></tr>
          <!-- <tr><th>เลขบัตรประชาชน</th><td><?=$e->id_card_number?str_repeat('*',9).substr($e->id_card_number,-4):'-'?></td></tr> -->
            <tr><th>เลขบัตรประชาชน</th><td><?=$e->id_card_number?$e->id_card_number:'-'?></td></tr>
          <tr><th>เลขผู้เสียภาษี</th><td><?=$e->tax_id??'-'?></td></tr>
          <tr><th>ประกันสังคม</th><td><?=$e->social_security_id??'-'?></td></tr>
        </table>
      </div>
    </div>
  </div>
</div>
