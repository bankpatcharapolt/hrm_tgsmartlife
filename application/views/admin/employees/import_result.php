<?php defined('BASEPATH') OR exit(); $r=$result; ?>
<?php if(isset($r['error'])):?>
<div class="alert alert-danger"><i class="bi bi-exclamation-circle me-2"></i><?=$r['error']?></div>
<?php else:?>

<div class="row g-3 mb-4">
  <div class="col-md-3">
    <div class="stat-card"><div class="s-ico" style="background:#f0fdf4;color:#16a34a"><i class="bi bi-person-plus"></i></div><div><div class="s-lbl">สร้างใหม่</div><div class="s-val text-success"><?=$r['success']??0?></div></div></div>
  </div>
  <div class="col-md-3">
    <div class="stat-card"><div class="s-ico" style="background:#eff6ff;color:#1a56db"><i class="bi bi-arrow-repeat"></i></div><div><div class="s-lbl">อัปเดต</div><div class="s-val text-primary"><?=$r['updated']??0?></div></div></div>
  </div>
  <div class="col-md-3">
    <div class="stat-card"><div class="s-ico" style="background:#fff7ed;color:#ea580c"><i class="bi bi-skip-forward"></i></div><div><div class="s-lbl">ข้ามแถวว่าง</div><div class="s-val text-warning"><?=$r['skipped']??0?></div></div></div>
  </div>
  <div class="col-md-3">
    <div class="stat-card"><div class="s-ico" style="background:#fef2f2;color:#dc2626"><i class="bi bi-exclamation-triangle"></i></div><div><div class="s-lbl">ข้อผิดพลาด</div><div class="s-val text-danger"><?=count($r['errors']??[])?></div></div></div>
  </div>
</div>

<?php if(!empty($r['errors'])):?>
<div class="card mb-3">
  <div class="card-header text-danger"><i class="bi bi-exclamation-triangle me-2"></i>รายการที่มีข้อผิดพลาด</div>
  <div class="card-body p-0">
    <table class="table table-sm mb-0">
      <thead><tr><th>#</th><th>รายละเอียด</th></tr></thead>
      <tbody>
        <?php foreach($r['errors'] as $i=>$e):?>
        <tr><td><?=$i+1?></td><td style="font-size:.83rem" class="text-danger"><?=htmlspecialchars($e)?></td></tr>
        <?php endforeach;?>
      </tbody>
    </table>
  </div>
</div>
<?php endif;?>

<?php if(($r['success']??0)+($r['updated']??0) > 0):?>
<div class="alert alert-success">
  <i class="bi bi-check-circle me-2"></i>
  <strong>นำเข้าข้อมูลสำเร็จ!</strong> สร้างพนักงานใหม่ <?=$r['success']??0?> คน, อัปเดต <?=$r['updated']??0?> คน
</div>
<?php endif;?>

<?php endif;?>

<div class="d-flex gap-2 mt-3">
  <a href="<?=base_url('admin/employees')?>" class="btn btn-primary"><i class="bi bi-people me-1"></i>ดูรายการพนักงาน</a>
  <a href="<?=base_url('admin/employees_import/import')?>" class="btn btn-outline-secondary"><i class="bi bi-upload me-1"></i>นำเข้าอีกครั้ง</a>
</div>
