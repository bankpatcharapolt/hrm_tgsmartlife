<?php defined('BASEPATH') OR exit(); ?>
<div class="d-flex justify-content-end mb-3">
  <a href="<?=base_url('admin/teams')?>" class="btn btn-outline-primary btn-sm">
    <i class="bi bi-diagram-3 me-1"></i>จัดการทีม/สาขา
  </a>
</div>
<div class="row g-3">
  <?php foreach($roles as $r): $pc=array('can_checkin'=>'ลงเวลา','can_view_own_salary'=>'ดูเงินเดือนตัวเอง','can_approve_leave'=>'อนุมัติการลา','can_manage_employees'=>'จัดการพนักงาน','can_view_sales'=>'ดูยอดขาย','can_send_notifications'=>'ส่งแจ้งเตือน','can_manage_salary'=>'จัดการเงินเดือน','can_upload_documents'=>'อัปโหลดเอกสาร','can_view_reports'=>'ดูรายงาน','can_monitor_attendance'=>'ดูการเข้างาน'); ?>
  <div class="col-md-6">
    <div class="card">
      <div class="card-header d-flex justify-content-between align-items-center">
        <span><i class="bi bi-shield-check me-2"></i><?=$r->name?> <code class="fs-6">(<?=$r->slug?>)</code></span>
        <a href="<?=base_url('admin/roles/edit/'.$r->id)?>" class="btn btn-outline-primary btn-sm"><i class="bi bi-pencil"></i></a>
      </div>
      <div class="card-body">
        <div class="row g-1">
          <?php if($r->is_full_access):?><div class="col-12"><span class="badge bg-danger">Full Access</span></div><?php endif;?>
          <?php foreach($pc as $key=>$label):?>
          <div class="col-6"><span class="badge bg-<?=$r->{$key}?'success':'light text-muted border'?>"><?=$r->{$key}?'✓':'✗'?> <?=$label?></span></div>
          <?php endforeach;?>
        </div>
        <hr class="my-2">
        <div class="row text-center">
          <div class="col-4"><div style="font-size:.72rem;color:#6b7280">เวลาเข้า</div><div style="font-size:.83rem;font-weight:600"><?=substr($r->work_start_time,0,5)?></div></div>
          <div class="col-4"><div style="font-size:.72rem;color:#6b7280">เวลาออก</div><div style="font-size:.83rem;font-weight:600"><?=substr($r->work_end_time,0,5)?></div></div>
          <div class="col-4"><div style="font-size:.72rem;color:#6b7280">ลาป่วย</div><div style="font-size:.83rem;font-weight:600"><?=$r->leave_quota_sick?> วัน</div></div>
        </div>
      </div>
    </div>
  </div>
  <?php endforeach;?>
</div>
