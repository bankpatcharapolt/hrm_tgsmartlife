<?php defined('BASEPATH') OR exit(); ?>
<?php
// [ข้อ 4] คำนวณสรุปวันลาแต่ละประเภทของปีปัจจุบัน
$current_year = date('Y');

// สร้าง map: leave_type_id => total_days ที่ใช้ไป (เฉพาะ approved)
$used_map = array();
foreach ($requests as $r) {
    if ($r->status === 'approved' && date('Y', strtotime($r->start_date)) == $current_year) {
        $tid = $r->leave_type_id;
        $used_map[$tid] = ($used_map[$tid] ?? 0) + $r->total_days;
    }
}
?>

<!-- [ข้อ 4] สรุปวันลาแต่ละประเภท -->
<?php if (!empty($leave_types)): ?>
<div class="row g-2 mb-3">
  <?php foreach ($leave_types as $lt):
    $used  = $used_map[$lt->id] ?? 0;
    $quota = $lt->quota_per_year;
    $left  = max(0, $quota - $used);
    $pct   = $quota > 0 ? min(100, round($used / $quota * 100)) : 0;
    $color = $pct >= 100 ? 'danger' : ($pct >= 75 ? 'warning' : 'success');
  ?>
  <div class="col-6 col-md-3">
    <div class="card h-100">
      <div class="card-body p-3">
        <div class="d-flex justify-content-between align-items-start mb-1">
          <div class="fw-semibold" style="font-size:.82rem"><?= htmlspecialchars($lt->name) ?></div>
          <span class="badge bg-<?= $color ?>" style="font-size:.7rem"><?= $left ?> วันเหลือ</span>
        </div>
        <div style="font-size:.77rem;color:#6b7280" class="mb-2">
          ใช้ไป <strong><?= number_format($used, 1) ?></strong>
          / <?= $quota > 0 ? $quota . ' วัน' : 'ไม่จำกัด' ?>
        </div>
        <?php if ($quota > 0): ?>
        <div class="progress" style="height:5px">
          <div class="progress-bar bg-<?= $color ?>" style="width:<?= $pct ?>%"></div>
        </div>
        <?php endif; ?>
      </div>
    </div>
  </div>
  <?php endforeach; ?>
</div>
<?php endif; ?>

<div class="d-flex justify-content-between align-items-center mb-3">
  <span class="text-muted small">คำขอลาทั้งหมด <strong><?= count($requests) ?></strong> รายการ</span>
  <a href="<?= base_url('employee/leave/request') ?>" class="btn btn-primary btn-sm">
    <i class="bi bi-plus-lg me-1"></i>ยื่นคำขอลา
  </a>
</div>
<div class="card">
  <div class="card-body p-0">
    <div class="table-responsive">
      <table class="table table-hover mb-0">
        <thead>
          <tr><th>ประเภท</th><th>วันที่</th><th>จำนวน</th><th>เหตุผล</th><th>สถานะ</th><th>หมายเหตุผู้อนุมัติ</th><th>เอกสาร</th><th>จัดการ</th></tr>
        </thead>
        <tbody>
          <?php if(!empty($requests)):foreach($requests as $r):
            $sc=array('pending'=>'warning text-dark','approved'=>'success','rejected'=>'danger','cancelled'=>'secondary');
            $sl=array('pending'=>'รอการอนุมัติ','approved'=>'อนุมัติแล้ว','rejected'=>'ปฏิเสธ','cancelled'=>'ยกเลิก');
          ?>
          <tr>
            <td><span class="badge bg-info text-dark"><?= $r->leave_type_name ?></span></td>
            <td style="font-size:.83rem;white-space:nowrap">
              <?= date('d/m/Y', strtotime($r->start_date)) ?>
              <?php if($r->start_date !== $r->end_date): ?><br><small class="text-muted">ถึง <?= date('d/m/Y', strtotime($r->end_date)) ?></small><?php endif; ?>
              <?php if(!empty($r->leave_unit) && $r->leave_unit==='hour' && !empty($r->start_time)): ?>
              <br><small class="text-primary"><?= substr($r->start_time,0,5) ?> – <?= substr($r->end_time??'',0,5) ?></small>
              <?php endif; ?>
            </td>
            <td>
              <?php if(!empty($r->leave_unit) && $r->leave_unit==='hour'): ?>
              <span class="badge bg-primary"><?= number_format($r->total_hours??0,1) ?> ชม.</span>
              <?php else: ?>
              <?= number_format($r->total_days, 0) ?> วัน
              <?php endif; ?>
            </td>
            <td style="font-size:.82rem;max-width:160px"><?= htmlspecialchars(mb_substr($r->reason,0,50)) ?><?= mb_strlen($r->reason)>50?'...':'' ?></td>
            <td>
              <span class="badge bg-<?= $sc[$r->status]??'secondary' ?>"><?= $sl[$r->status]??$r->status ?></span>
              <?php if(!empty($r->ap_fn)): ?>
              <br><small class="text-muted">โดย <?= $r->ap_fn.' '.$r->ap_ln ?></small>
              <?php endif; ?>
            </td>
            <td style="font-size:.8rem;color:#6b7280;max-width:140px"><?= htmlspecialchars($r->approver_note??'')?:'–' ?></td>
            <td>
              <div class="d-flex gap-1 flex-wrap">
                <?php if(!empty($r->document_path)): ?>
                <a href="<?= base_url($r->document_path) ?>" target="_blank"
                   class="btn btn-outline-secondary btn-sm px-2 py-0"
                   title="เอกสารประกอบ">
                  <i class="bi bi-file-earmark"></i>
                </a>
                <?php endif; ?>
                <?php if(!empty($r->medical_cert_path)): ?>
                <a href="<?= base_url($r->medical_cert_path) ?>" target="_blank"
                   class="btn btn-outline-warning btn-sm px-2 py-0"
                   title="ใบรับรองแพทย์">
                  <i class="bi bi-file-medical"></i>
                </a>
                <?php endif; ?>
                <?php if(empty($r->document_path) && empty($r->medical_cert_path)): ?>
                <span class="text-muted small">–</span>
                <?php endif; ?>
              </div>
            </td>
            <td>
              <?php if($r->status === 'pending'): ?>
              <a href="<?= base_url('employee/leave/edit/'.$r->id) ?>"
                 class="btn btn-outline-secondary btn-sm px-2 py-0" title="แก้ไข">
                <i class="bi bi-pencil"></i>
              </a>
              <a href="<?= base_url('employee/leave/cancel/'.$r->id) ?>"
                 onclick="return confirm('ลบคำขอลานี้?')"
                 class="btn btn-outline-danger btn-sm px-2 py-0 ms-1" title="ลบ">
                <i class="bi bi-trash"></i>
              </a>
              <?php elseif($r->status === 'approved'): ?>
              <span class="badge bg-success px-2 py-1"><i class="bi bi-check2"></i></span>
              <?php elseif($r->status === 'rejected'): ?>
              <span class="badge bg-danger px-2 py-1"><i class="bi bi-x"></i></span>
              <?php else: ?>
              <span class="text-muted small">–</span>
              <?php endif; ?>
            </td>
          </tr>
          <?php endforeach; else: ?>
          <tr><td colspan="8" class="text-center text-muted py-5">
            <i class="bi bi-calendar-x fs-1 d-block mb-2"></i>ยังไม่มีประวัติการลา
            <div class="mt-2"><a href="<?= base_url('employee/leave/request') ?>" class="btn btn-primary btn-sm"><i class="bi bi-plus me-1"></i>ยื่นลาตอนนี้</a></div>
          </td></tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>
