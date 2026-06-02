<?php defined('BASEPATH') OR exit(); ?>
<div class="d-flex gap-2 mb-3 flex-wrap align-items-center">
  <select class="form-select form-select-sm" style="width:auto" onchange="goF(this,'year')">
    <?php for ($y = date('Y'); $y >= date('Y') - 2; $y--): ?>
      <option value="<?= $y ?>" <?= $year == $y ? 'selected' : '' ?>><?= $y ?></option>
    <?php endfor; ?>
  </select>
  <select class="form-select form-select-sm" style="width:auto" onchange="goF(this,'month')">
    <?php $mn = array('1'=>'ม.ค.','2'=>'ก.พ.','3'=>'มี.ค.','4'=>'เม.ย.','5'=>'พ.ค.','6'=>'มิ.ย.','7'=>'ก.ค.','8'=>'ส.ค.','9'=>'ก.ย.','10'=>'ต.ค.','11'=>'พ.ย.','12'=>'ธ.ค.');
    foreach ($mn as $k => $v): ?>
      <option value="<?= $k ?>" <?= $month == $k ? 'selected' : '' ?>><?= $v ?></option>
    <?php endforeach; ?>
  </select>
  <div class="ms-auto">
    <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addModal">
      <i class="bi bi-plus-lg me-1"></i>ลงข้อมูลการมาทำงานย้อนหลัง
    </button>
  </div>
</div>

<!-- สรุปเดือน -->
<div class="row g-2 mb-3">
  <div class="col-6 col-md-3">
    <div class="stat-card">
      <div class="s-ico" style="background:#f0fdf4;color:#16a34a"><i class="bi bi-check-circle"></i></div>
      <div><div class="s-lbl">มาทำงาน</div><div class="s-val text-success"><?= $summary['present'] ?></div><div class="s-sub">วัน</div></div>
    </div>
  </div>
  <div class="col-6 col-md-3">
    <div class="stat-card">
      <div class="s-ico" style="background:#fffbeb;color:#d97706"><i class="bi bi-clock-history"></i></div>
      <div><div class="s-lbl">มาสาย</div><div class="s-val text-warning"><?= $summary['late'] ?></div><div class="s-sub"><?= $summary['total_late_min'] ?> นาที</div></div>
    </div>
  </div>
  <!-- [ข้อ 3] แสดงวันขาดงาน -->
  <div class="col-6 col-md-3">
    <div class="stat-card">
      <div class="s-ico" style="background:#fef2f2;color:#dc2626"><i class="bi bi-x-circle"></i></div>
      <div>
        <div class="s-lbl">ขาดงาน</div>
        <div class="s-val text-danger"><?= count($absent_days) ?></div>
        <div class="s-sub">วัน (ไม่รวม ส-อ, วันหยุด, วันลา)</div>
      </div>
    </div>
  </div>
  <div class="col-6 col-md-3">
    <div class="stat-card">
      <div class="s-ico" style="background:#f0fdf4;color:#059669"><i class="bi bi-lightning-charge"></i></div>
      <div><div class="s-lbl">OT</div><div class="s-val"><?= number_format($summary['total_ot'], 1) ?></div><div class="s-sub">ชม.</div></div>
    </div>
  </div>
</div>

<!-- [ข้อ 3] แสดงรายการวันขาดงาน (ถ้ามี) -->
<?php if (!empty($absent_days)): ?>
<div class="alert alert-danger d-flex align-items-start gap-2 mb-3">
  <i class="bi bi-exclamation-triangle-fill mt-1"></i>
  <div>
    <strong>วันที่ขาดงาน (<?= count($absent_days) ?> วัน)</strong>
    <div class="mt-1" style="font-size:.83rem">
      <?php
      $mn_th = array('01'=>'ม.ค.','02'=>'ก.พ.','03'=>'มี.ค.','04'=>'เม.ย.','05'=>'พ.ค.','06'=>'มิ.ย.','07'=>'ก.ค.','08'=>'ส.ค.','09'=>'ก.ย.','10'=>'ต.ค.','11'=>'พ.ย.','12'=>'ธ.ค.');
      $day_th = array('Mon'=>'จ.','Tue'=>'อ.','Wed'=>'พ.','Thu'=>'พฤ.','Fri'=>'ศ.');
      $labels = array();
      foreach ($absent_days as $d) {
          $dow = date('D', strtotime($d));
          $dth = $day_th[$dow] ?? $dow;
          list($dy,$dm,$dd) = explode('-',$d);
          $labels[] = $dth.' '.(int)$dd.' '.$mn_th[$dm];
      }
      echo implode(', ', $labels);
      ?>
    </div>
  </div>
</div>
<?php endif; ?>

<div class="card">
  <div class="card-body p-0">
    <div class="table-responsive">
      <table class="table table-hover mb-0">
        <thead>
          <tr>
            <th>วันที่</th><th>กะ</th><th>เข้างาน</th><th>ออกงาน</th>
            <th>สถานะ</th><th>สาย</th><th>OT</th><th>หมายเหตุ</th><th>จัดการ</th>
          </tr>
        </thead>
        <tbody>
          <?php if (!empty($records)):
            // สร้าง map วันที่มีข้อมูล
            $rec_dates = array();
            foreach ($records as $r) $rec_dates[$r->date] = true;

            // รวม records + absent rows เพื่อเรียงวันที่
            $all_rows = array();
            foreach ($records as $r) $all_rows[$r->date] = array('type'=>'record','data'=>$r);
            foreach ($absent_days as $d) $all_rows[$d] = array('type'=>'absent','date'=>$d);
            ksort($all_rows);

            foreach ($all_rows as $date_key => $row):
              if ($row['type'] === 'absent'):
                $d = $row['date'];
                $dow_th = array('Mon'=>'จ.','Tue'=>'อ.','Wed'=>'พ.','Thu'=>'พฤ.','Fri'=>'ศ.');
                $dname = $dow_th[date('D',strtotime($d))] ?? date('D',strtotime($d));
          ?>
              <tr class="table-danger">
                <td style="font-size:.83rem;white-space:nowrap">
                  <?= date('d/m/Y', strtotime($d)) ?>
                  <br><small class="text-danger fw-semibold"><?= $dname ?></small>
                </td>
                <td colspan="7" class="text-danger" style="font-size:.83rem">
                  <i class="bi bi-x-circle me-1"></i><strong>ขาดงาน</strong>
                </td>
                <td>–</td>
              </tr>
          <?php else:
            $r = $row['data'];
            $sc = array('present'=>'success','absent'=>'danger','leave'=>'info text-dark','holiday'=>'warning text-dark','half_day'=>'secondary');
            $sl = array('present'=>'มา','absent'=>'ขาด','leave'=>'ลา','holiday'=>'วันหยุด','half_day'=>'ครึ่งวัน');
          ?>
              <tr>
                <td style="font-size:.83rem;white-space:nowrap">
                  <?= date('d/m/Y', strtotime($r->date)) ?>
                  <br><small class="text-muted"><?= date('D', strtotime($r->date)) ?></small>
                </td>
                <td>
                  <?php if (!empty($r->shift_name)): ?>
                    <span class="badge" style="background:<?= $r->shift_color ?? '#6b7280' ?>;font-size:.67rem"><?= $r->shift_name ?></span>
                  <?php else: ?><span class="text-muted small">–</span><?php endif; ?>
                </td>
                <td class="<?= $r->is_late ? 'text-danger fw-semibold' : '' ?>" style="font-size:.83rem">
                  <?= $r->check_in_time ? date('H:i', strtotime($r->check_in_time)) : '–' ?>
                </td>
                <td style="font-size:.83rem">
                  <?= $r->check_out_time ? date('H:i', strtotime($r->check_out_time)) : '–' ?>
                </td>
                <td>
                  <span class="badge bg-<?= $sc[$r->status] ?? 'secondary' ?>"><?= $sl[$r->status] ?? $r->status ?></span>
                  <?php if ($r->is_late): ?><br><small class="text-danger">สาย <?= $r->late_minutes ?> น.</small><?php endif; ?>
                  <?php if ($r->status === 'half_day' && !empty($r->work_hours)): ?>
                    <br><small class="text-secondary"><?= $r->work_hours ?> ชม.</small>
                  <?php endif; ?>
                </td>
                <td class="small <?= $r->late_minutes > 0 ? 'text-danger' : '' ?>">
                  <?= $r->late_minutes > 0 ? $r->late_minutes . ' น.' : '–' ?></td>
                <td class="small"><?= $r->ot_hours > 0 ? number_format($r->ot_hours, 1) . ' ชม.' : '–' ?></td>
                <td style="font-size:.8rem;max-width:120px"><?= htmlspecialchars($r->note ?? '') ?></td>
                <td>
                  <a href="<?= base_url('employee/attendance/delete/' . $r->id) ?>"
                    onclick="return confirm('ลบรายการวันที่ <?= date('d/m/Y', strtotime($r->date)) ?> ใช่ไหม?')"
                    class="btn btn-outline-danger btn-sm px-2 py-0 ms-1" title="ลบ">
                    <i class="bi bi-trash"></i>
                  </a>
                </td>
              </tr>
          <?php endif; endforeach; else: ?>
            <tr>
              <td colspan="9" class="text-center text-muted py-5">
                <i class="bi bi-clock fs-1 d-block mb-2"></i>ไม่มีข้อมูลการเข้างาน
              </td>
            </tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<!-- Modal: ลงข้อมูลการมาทำงานย้อนหลัง [ข้อ 7] -->
<div class="modal fade" id="addModal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title"><i class="bi bi-plus-circle me-2"></i>บันทึกการเข้างานย้อนหลัง</h5>
        <button class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <?= form_open('employee/attendance/add') ?>
      <input type="hidden" name="<?= $this->security->get_csrf_token_name() ?>" value="<?= $this->security->get_csrf_hash() ?>">
      <div class="modal-body">
        <div class="row g-3">
          <div class="col-6">
            <label class="form-label small">วันที่ *</label>
            <input type="date" name="date" class="form-control form-control-sm" value="<?= date('Y-m-d') ?>" required>
          </div>
          <!-- [ข้อ 7] กะการทำงาน: pre-select กะของ user + disabled แก้ไขไม่ได้ -->
          <div class="col-6">
            <label class="form-label small">กะการทำงาน</label>
            <select name="shift_id" class="form-select form-select-sm" disabled>
              <?php foreach ($shifts as $s): ?>
                <option value="<?= $s->id ?>" <?= ($default_shift_id == $s->id) ? 'selected' : '' ?>>
                  <?= $s->name ?> (<?= substr($s->start_time, 0, 5) ?>–<?= substr($s->end_time, 0, 5) ?>)
                </option>
              <?php endforeach; ?>
            </select>
            <!-- hidden เพราะ disabled ไม่ submit -->
            <input type="hidden" name="shift_id" value="<?= $default_shift_id ?>">
          </div>
          <div class="col-6">
            <label class="form-label small">เวลาเข้างาน</label>
            <input type="datetime-local" name="check_in" class="form-control form-control-sm" id="addCheckIn">
          </div>
          <div class="col-6">
            <label class="form-label small">เวลาออกงาน</label>
            <input type="datetime-local" name="check_out" class="form-control form-control-sm" id="addCheckOut">
          </div>
          <!-- [ข้อ 7] เพิ่มสถานะ + half_day hours + hourly -->
          <div class="col-12">
            <label class="form-label small">สถานะ</label>
            <select name="status" id="addStatus" class="form-select form-select-sm" onchange="onStatusChange(this.value)">
              <option value="present">มาทำงานเต็มวัน</option>
              <option value="half_day">มาทำงานครึ่งวัน</option>
              <option value="hourly">มาทำงานรายชั่วโมง</option>
            </select>
          </div>

          <!-- [ข้อ 7] ครึ่งวัน: ใส่ชั่วโมง -->
          <div class="col-12" id="halfDaySection" style="display:none">
            <div class="p-2 rounded" style="background:#f0fdf4;border:1px solid #bbf7d0">
              <label class="form-label small mb-1">จำนวนชั่วโมงที่มาทำงาน</label>
              <div class="input-group input-group-sm">
                <input type="number" name="half_day_hours" class="form-control" value="4" min="0.5" max="8" step="0.5">
                <span class="input-group-text">ชม.</span>
              </div>
            </div>
          </div>

          <!-- [ข้อ 7] รายชั่วโมง: แสดงช่วงเวลา -->
          <div class="col-12" id="hourlySection" style="display:none">
            <div class="p-2 rounded" style="background:#eff6ff;border:1px solid #bae6fd">
              <div class="small text-muted mb-1"><i class="bi bi-info-circle me-1"></i>ระบุเวลาเข้า-ออกด้านบน ระบบจะคำนวณชั่วโมงให้อัตโนมัติ</div>
              <div id="hourlyCalc" class="fw-semibold text-primary small"></div>
            </div>
          </div>

          <div class="col-12">
            <label class="form-label small">เหตุผล</label>
            <input type="text" name="note" class="form-control form-control-sm">
          </div>
        </div>
      </div>
      <div class="modal-footer py-2">
        <button type="submit" class="btn btn-primary btn-sm"><i class="bi bi-save me-1"></i>บันทึก</button>
        <span class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">ยกเลิก</span>
      </div>
      <?= form_close() ?>
    </div>
  </div>
</div>
<script>
// บังคับให้เป็น Global Function ด้วย window.
window.goF = function(el, k) {
    var url = new URL(window.location);
    url.searchParams.set(k, el.value);
    window.location = url;
};

// จัดการ status change
window.onStatusChange = function(v) {
    var halfDaySec = document.getElementById("halfDaySection");
    var hourlySec = document.getElementById("hourlySection");
    
    if (halfDaySec) halfDaySec.style.display = (v === "half_day") ? "" : "none";
    if (hourlySec) hourlySec.style.display = (v === "hourly") ? "" : "none";
    
    if (v === "hourly") window.calcHourly();
};

// คำนวณชั่วโมงรายชั่วโมง
window.calcHourly = function() {
    var ci = document.getElementById("addCheckIn").value;
    var co = document.getElementById("addCheckOut").value;
    var el = document.getElementById("hourlyCalc");
    
    if (!ci || !co) { 
        if (el) el.textContent = ""; 
        return; 
    }
    
    var diff = (new Date(co) - new Date(ci)) / 3600000;
    if (diff <= 0) { 
        if (el) el.textContent = "⚠ เวลาออกต้องหลังเวลาเข้า"; 
        return; 
    }
    if (el) el.textContent = "ชั่วโมงทำงาน: " + diff.toFixed(2) + " ชม.";
};

// ผูก Event Listener เช็คเวลาพิมพ์
document.addEventListener("DOMContentLoaded", function() {
    var addCheckIn = document.getElementById("addCheckIn");
    var addCheckOut = document.getElementById("addCheckOut");
    var addStatus = document.getElementById("addStatus");

    if (addCheckIn && addStatus) {
        addCheckIn.addEventListener("change", function() { 
            if (addStatus.value === "hourly") window.calcHourly(); 
        });
    }
    if (addCheckOut && addStatus) {
        addCheckOut.addEventListener("change", function() { 
            if (addStatus.value === "hourly") window.calcHourly(); 
        });
    }
});
</script>