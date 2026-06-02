<?php defined('BASEPATH') OR exit(); $e=$emp; ?>
<div class="row g-3">
  <div class="col-md-4">
    <div class="card text-center p-3">
      <?php if($e->photo):?><img src="<?=base_url($e->photo)?>" class="rounded-circle mb-3" style="width:96px;height:96px;object-fit:cover;"><?php else:?><div style="width:96px;height:96px;background:#e0e7ff;border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:2.4rem;font-weight:700;color:#1a56db;margin:0 auto .75rem"><?=mb_substr($e->first_name,0,1)?></div><?php endif;?>
      <h5 class="mb-0"><?=$e->first_name.' '.$e->last_name?></h5>
      <?php if($e->nickname):?><div class="text-muted small">(<?=$e->nickname?>)</div><?php endif;?>
      <div class="mt-2"><span class="badge bg-<?=$e->status==='active'?'success':'secondary'?>"><?=$e->status==='active'?'ใช้งาน':'ไม่ใช้งาน'?></span></div>
      <div class="mt-1"><span class="badge bg-primary"><?=$e->role_name?></span></div>
      <?php if($e->department_name):?><div class="text-muted small mt-1"><?=$e->department_name?></div><?php endif;?>
    </div>
    <div class="card mt-3">
      <div class="card-header">สรุปเดือน <?=date('m/Y')?></div>
      <div class="card-body p-0">
        <table class="table table-sm mb-0">
          <tr><td class="text-muted">มาทำงาน</td><td class="fw-semibold text-end text-success"><?=$att_sum['present']?> วัน</td></tr>
          <tr><td class="text-muted">ลา</td><td class="fw-semibold text-end"><?=$att_sum['leave']?> วัน</td></tr>
          <tr><td class="text-muted">มาสาย</td><td class="fw-semibold text-end text-warning"><?=$att_sum['late']?> ครั้ง</td></tr>
          <tr><td class="text-muted">OT</td><td class="fw-semibold text-end"><?=number_format($att_sum['total_ot'],1)?> ชม.</td></tr>
        </table>
      </div>
    </div>
  </div>
  <div class="col-md-8">
    <div class="card">
      <div class="card-header">ข้อมูลส่วนตัว</div>
      <div class="card-body">
        <table class="table table-sm mb-0">
          <tr><th style="width:38%">รหัสพนักงาน</th><td><code><?=$e->employee_id?></code></td></tr>
          <tr><th>เพศ</th><td><?=$e->gender==='male'?'ชาย':($e->gender==='female'?'หญิง':'อื่นๆ')?></td></tr>
          <tr><th>วันเกิด</th><td><?=$e->date_of_birth?date('d/m/Y',strtotime($e->date_of_birth)):'-'?></td></tr>
          <tr><th>เลขบัตรประชาชน</th><td><?=$e->id_card_number??'-'?></td></tr>
          <tr><th>เบอร์โทร</th><td><?=$e->phone??'-'?></td></tr>
          <tr><th>อีเมล</th><td><?=$e->email??'-'?></td></tr>
          <tr><th>วันเริ่มงาน</th><td><?=date('d/m/Y',strtotime($e->start_date))?></td></tr>
          <tr><th>เงินเดือนฐาน</th><td class="text-primary fw-semibold">฿<?=number_format($e->base_salary,2)?></td></tr>
          <tr><th>รหัสประกันสังคม</th><td><?=$e->social_security_id??'-'?></td></tr>
          <tr><th>เลขผู้เสียภาษี</th><td><?=$e->tax_id??'-'?></td></tr>
          <tr><th>ที่อยู่</th><td><?=nl2br(htmlspecialchars($e->address??'-'))?></td></tr>
        </table>
      </div>
    </div>
    <div class="d-flex gap-2 mt-3">
      <a href="<?=base_url('admin/employees/edit/'.$e->id)?>" class="btn btn-primary"><i class="bi bi-pencil me-1"></i>แก้ไข</a>
      <a href="<?=base_url('admin/employees')?>" class="btn btn-outline-secondary">กลับ</a>
      <?php if($e->status==='active'):?><a href="<?=base_url('admin/employees/deactivate/'.$e->id)?>" onclick="return confirm('ปิดการใช้งาน?')" class="btn btn-outline-danger ms-auto"><i class="bi bi-person-dash me-1"></i>ปิดการใช้งาน</a><?php endif;?>
    </div>
  </div>
</div>
