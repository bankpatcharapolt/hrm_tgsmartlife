<?php defined('BASEPATH') OR exit(); ?>
<div class="row g-3">
  <!-- Check-in Card -->
  <div class="col-md-5 col-lg-4">
    <div class="ci-card">
      <div class="ci-time" id="liveClock">--:--:--</div>
      <div style="font-size:.83rem;opacity:.8;margin-bottom:1.2rem" id="liveDate"></div>
      <?php if(!$today||!$today->check_in_time): ?>
      <button class="btn btn-light fw-semibold w-100" id="btnCI" onclick="doCheckIn()"><i class="bi bi-box-arrow-in-right me-2"></i>ลงเวลาเข้างาน</button>
      <?php elseif(!$today->check_out_time): ?>
      <div class="mb-2" style="font-size:.83rem;opacity:.8">เข้างาน: <strong><?=date('H:i',strtotime($today->check_in_time))?></strong><?=$today->is_late?' <span class="badge bg-warning text-dark">สาย '.$today->late_minutes.' นาที</span>':''?></div>
      <button class="btn btn-warning fw-semibold w-100" id="btnCO" onclick="doCheckOut()"><i class="bi bi-box-arrow-right me-2"></i>ลงเวลาออกงาน</button>
      <?php else: ?>
      <div style="font-size:.83rem;opacity:.8;margin-bottom:.5rem">เข้า: <strong><?=date('H:i',strtotime($today->check_in_time))?></strong> | ออก: <strong><?=date('H:i',strtotime($today->check_out_time))?></strong></div>
      <span class="badge bg-success fs-6 px-3 py-2">✓ ลงเวลาแล้ว</span>
      <?php endif; ?>
    </div>
    <!-- This Month Summary -->
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
  <!-- Right Column -->
  <div class="col-md-7 col-lg-8">
    <!-- Salary -->
    <div class="card mb-3">
      <div class="card-header d-flex justify-content-between align-items-center">
        <span><i class="bi bi-cash me-2 text-primary"></i>เงินเดือนล่าสุด</span>
        <a href="<?=base_url('employee/salary')?>" class="btn btn-outline-primary btn-sm">ดูทั้งหมด</a>
      </div>
      <div class="card-body p-0">
        <?php if(!empty($salaries)): $s=$salaries[0]; ?>
        <div class="p-3">
          <div class="row text-center g-0">
            <div class="col-4"><div style="font-size:.75rem;color:#6b7280">เงินเดือน <?=$s->salary_month?>/<?=$s->salary_year?></div><div style="font-size:.83rem;color:#6b7280">ฐาน</div><div class="fw-semibold">฿<?=number_format($s->base_salary,0)?></div></div>
            <div class="col-4"><div style="font-size:.75rem;color:#6b7280">รายได้รวม</div><div style="font-size:.83rem;color:#16a34a" class="fw-semibold">฿<?=number_format($s->gross_salary,0)?></div><span class="badge bg-<?=$s->payment_status==='paid'?'success':'warning text-dark'?>"><?=$s->payment_status==='paid'?'จ่ายแล้ว':'รอจ่าย'?></span></div>
            <div class="col-4"><div style="font-size:.75rem;color:#6b7280">เงินเดือนสุทธิ</div><div class="fw-bold text-primary fs-5">฿<?=number_format($s->net_salary,0)?></div></div>
          </div>
        </div>
        <?php else: ?><div class="text-center text-muted py-3 small">ยังไม่มีข้อมูลเงินเดือน</div><?php endif; ?>
      </div>
    </div>
    <!-- Leave Requests -->
    <div class="card mb-3">
      <div class="card-header d-flex justify-content-between align-items-center">
        <span><i class="bi bi-calendar me-2 text-warning"></i>การลาที่รอดำเนินการ</span>
        <a href="<?=base_url('employee/leave/request')?>" class="btn btn-outline-warning btn-sm"><i class="bi bi-plus me-1"></i>ยื่นลา</a>
      </div>
      <div class="card-body p-0">
        <?php if(!empty($leaves)): foreach($leaves as $l): ?>
        <div class="d-flex align-items-center justify-content-between px-3 py-2 border-bottom">
          <div><div class="fw-semibold" style="font-size:.875rem"><?=$l->leave_type_name?></div><div style="font-size:.78rem;color:#6b7280"><?=date('d/m/Y',strtotime($l->start_date))?><?=$l->start_date!=$l->end_date?' – '.date('d/m/Y',strtotime($l->end_date)):''?> (<?=$l->total_days?> วัน)</div></div>
          <span class="badge bg-warning text-dark">รอการอนุมัติ</span>
        </div>
        <?php endforeach; else: ?>
        <div class="text-center text-muted py-3 small">ไม่มีการลาที่รอดำเนินการ <a href="<?=base_url('employee/leave/request')?>">ยื่นลา</a></div>
        <?php endif; ?>
      </div>
    </div>
    <!-- Notifications -->
    <div class="card">
      <div class="card-header d-flex justify-content-between align-items-center">
        <span><i class="bi bi-bell me-2"></i>การแจ้งเตือนล่าสุด</span>
        <a href="<?=base_url('employee/notifications')?>" class="btn btn-outline-secondary btn-sm">ดูทั้งหมด</a>
      </div>
      <div class="card-body p-0">
        <?php if(!empty($notifs)): foreach($notifs as $n): ?>
        <div class="d-flex align-items-start gap-2 px-3 py-2 border-bottom <?=!$n->is_read?'bg-light':''?>">
          <div class="flex-fill"><div style="font-size:.83rem;font-weight:<?=!$n->is_read?600:400?>"><?=htmlspecialchars($n->title)?></div><div style="font-size:.77rem;color:#6b7280"><?=htmlspecialchars(mb_substr($n->message,0,60))?><?=mb_strlen($n->message)>60?'...':''?></div></div>
          <div style="font-size:.7rem;color:#9ca3af;white-space:nowrap"><?=date('d/m H:i',strtotime($n->created_at))?></div>
        </div>
        <?php endforeach; else: ?>
        <div class="text-center text-muted py-3 small">ไม่มีการแจ้งเตือน</div>
        <?php endif; ?>
      </div>
    </div>
  </div>
</div>
<script>
var CSRF_NAME = "<?=$this->security->get_csrf_token_name()?>", 
    CSRF_HASH = "<?=$this->security->get_csrf_hash()?>";

function updClock() {
    var n = new Date();
    document.getElementById("liveClock").textContent = n.toLocaleTimeString("th-TH");
    var days = ["อาทิตย์", "จันทร์", "อังคาร", "พุธ", "พฤหัส", "ศุกร์", "เสาร์"];
    document.getElementById("liveDate").textContent = "วัน" + days[n.getDay()] + " " + n.toLocaleDateString("th-TH");
}
updClock();
setInterval(updClock, 1000);

function doCheckIn() {
    fetch("<?=base_url('api/attendance/checkin')?>", {
        method: "POST",
        headers: {"Content-Type": "application/x-www-form-urlencoded"},
        body: CSRF_NAME + "=" + CSRF_HASH
    }).then(r => r.json()).then(d => {
        if(d.success){
            alert(d.message + (d.data.is_late ? " (สาย " + d.data.late_minutes + " นาที)" : ""));
            location.reload();
        } else alert(d.message);
    });
}

function doCheckOut() {
    fetch("<?=base_url('api/attendance/checkout')?>", {
        method: "POST",
        headers: {"Content-Type": "application/x-www-form-urlencoded"},
        body: CSRF_NAME + "=" + CSRF_HASH
    }).then(r => r.json()).then(d => {
        if(d.success){
            alert(d.message);
            location.reload();
        } else alert(d.message);
    });
}
</script>