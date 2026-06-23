<?php defined('BASEPATH') OR exit(); ?>
<div class="card" style="max-width:640px">
  <div class="card-header"><i class="bi bi-calendar-plus me-2"></i>ยื่นคำขอลา</div>
  <div class="card-body">
    <?= form_open_multipart('employee/leave/store') ?>
    
    <div class="row g-3">
      <div class="col-12">
        <label class="form-label">ประเภทการลา *</label>
        <select name="leave_type_id" class="form-select" id="leaveTypeId" required>
          <option value="">-- เลือกประเภทการลา --</option>
          <?php foreach ($leave_types as $t): ?>
            <option value="<?= $t->id ?>">
              <?= $t->name ?>  <?= $t->quota_per_year > 0 ? ' (โควต้า ' . $t->quota_per_year . ' วัน/ปี)' : ' (ไม่จำกัด)' ?>
            </option>
          <?php endforeach; ?>
        </select>
      </div>

      <div class="col-md-4">
        <label class="form-label">หน่วยการลา</label>
        <select name="leave_unit" class="form-select" id="leaveUnit">
          <option value="day" selected>ลาเต็มวัน</option>
          <option value="hour">ลาเป็นชั่วโมง</option>
        </select>
      </div>

      <div class="col-md-4">
        <label class="form-label">วันที่เริ่มลา *</label>
        <input type="text" class="form-control jq-date-only" id="startDate"
               placeholder="dd/mm/yyyy" autocomplete="off" readonly style="cursor:pointer" required>
        <input type="hidden" name="start_date" id="startDateHidden">
      </div>

      <div class="col-md-4">
        <label class="form-label">วันที่สิ้นสุดการลา *</label>
        <input type="text" class="form-control jq-date-only" id="endDate"
               placeholder="dd/mm/yyyy" autocomplete="off" readonly style="cursor:pointer" required>
        <input type="hidden" name="end_date" id="endDateHidden">
      </div>

      <div class="col-12">
        <div class="alert alert-info py-2 px-3 mb-0" id="daysInfo" style="display:none">
          <i class="bi bi-info-circle me-2"></i>จำนวน <strong id="daysCount">0</strong>
        </div>
      </div> 

      <div class="col-12" id="hourSection" style="display:none">
        <div class="p-3 rounded" style="background:#eff6ff; border:1px solid #bae6fd">
          <p class="small fw-semibold mb-2"><i class="bi bi-clock me-1"></i>ช่วงเวลาที่ลา</p>
          <div class="row g-2 align-items-end">
            <div class="col-5">
              <label class="form-label small">เวลาเริ่มลา</label>
              <div class="leave-time-wrap" id="lsTimeWrap">
                <input type="hidden" name="leave_start_time" id="lsTime" value="">
              </div>
            </div>
            <div class="col-2 d-flex align-items-end pb-1 justify-content-center">–</div>
            <div class="col-5">
              <label class="form-label small">เวลาสิ้นสุด</label>
              <div class="leave-time-wrap" id="leTimeWrap">
                <input type="hidden" name="leave_end_time" id="leTime" value="">
              </div>
            </div>
          </div>
        </div>
      </div>

      <div class="col-12">
        <label class="form-label">เหตุผล *</label>
        <textarea name="reason" class="form-control" rows="3" required placeholder="กรุณาระบุเหตุผลในการลา"></textarea>
      </div>

      <div class="col-12">
        <label class="form-label">เอกสารประกอบอื่นๆ (รองรับไฟล์ PDF/รูปภาพ) (ถ้ามี)</label>
        <input type="file" name="document" class="form-control" accept=".pdf,.jpg,.jpeg,.png">
        <div class="form-text text-muted">ขนาดไม่เกิน 5MB</div>
      </div>

      <div class="col-12" id="medCertWrap" style="display:none">
        <div class="p-3 rounded" style="background:#fff7ed;border:1px solid #fed7aa">
          <label class="form-label fw-semibold" style="color:#c2410c">
            <i class="bi bi-file-medical me-1"></i>ใบรับรองแพทย์ (ถ้ามี)
          </label>
          <input type="file" name="medical_cert" id="medCertInput"
                 class="form-control" accept=".pdf,.jpg,.jpeg,.png">
          <div class="form-text">แนบใบรับรองแพทย์ (PDF, JPG, PNG ขนาดไม่เกิน 5MB)</div>
        </div>
      </div>
    </div>

    <div class="mt-4 d-flex gap-2">
      <button type="submit" class="btn btn-primary"><i class="bi bi-send me-1"></i>ส่งคำขอลา</button>
      <a href="<?= base_url('employee/leave') ?>" class="btn btn-outline-secondary">ยกเลิก</a>
    </div>
    <?= form_close() ?>
  </div>
</div>

<script>
// ข้อมูลกะการทำงานของ User จาก backend
var shiftConfig = {
  start:      "<?= !empty($shift->start_time)       ? $shift->start_time       : '08:30:00' ?>",
  end:        "<?= !empty($shift->end_time)         ? $shift->end_time         : '17:30:00' ?>",
  breakStart: "<?= !empty($shift->break_start_time) ? $shift->break_start_time : '12:30:00' ?>",
  breakEnd:   "<?= !empty($shift->break_end_time)   ? $shift->break_end_time   : '13:30:00' ?>"
};

// ── Pure vanilla JS time-only widget ──────────────────────────────────────
function buildLeaveTimeWidget(wrapId, hiddenId) {
  var wrap    = document.getElementById(wrapId);
  var hidden  = document.getElementById(hiddenId);
  var initVal = (hidden ? hidden.value : '') || '00:00';
  var parts   = initVal.split(':');
  var ch = parseInt(parts[0], 10) || 0;
  var cm = parseInt(parts[1], 10) || 0;

  // ลบ dt-time-wrap เดิม
  var old = wrap.querySelector('.dt-time-wrap');
  if (old) old.parentNode.removeChild(old);

  var tw = document.createElement('div');
  tw.className = 'dt-time-wrap';
  tw.style.flex = '1';

  var selH = document.createElement('select');
  selH.className = 'dt-hh';
  for (var h = 0; h <= 23; h++) {
    var hv = (h < 10 ? '0' : '') + h;
    var o  = document.createElement('option');
    o.value = hv; o.textContent = hv;
    if (h === ch) o.selected = true;
    selH.appendChild(o);
  }

  var colon = document.createElement('span');
  colon.className = 'dt-colon';
  colon.textContent = ':';

  var selM = document.createElement('select');
  selM.className = 'dt-mm';
  for (var m = 0; m <= 59; m++) {
    var mv = (m < 10 ? '0' : '') + m;
    var p  = document.createElement('option');
    p.value = mv; p.textContent = mv;
    if (m === cm) p.selected = true;
    selM.appendChild(p);
  }

  tw.appendChild(selH);
  tw.appendChild(colon);
  tw.appendChild(selM);
  wrap.appendChild(tw);

  function sync() {
    var hh = tw.querySelector('.dt-hh').value;
    var mm = tw.querySelector('.dt-mm').value;
    if (hidden) hidden.value = hh + ':' + mm + ':00';
    calcDays();
  }
  selH.addEventListener('change', sync);
  selM.addEventListener('change', sync);
  sync();
}

// ── Datepicker: ใช้ jQuery UI ที่ layout โหลดไว้แล้ว ──────────────────────
// initDTPickers() ใน layout จะ bind .jq-date-only อัตโนมัติ
// แต่เราต้องการ callback เพิ่มเพื่อ sync hidden + calcDays
// → override หลัง jQuery พร้อม (layout load jQuery ท้าย <body>)
document.addEventListener('DOMContentLoaded', function () {
  // สร้าง leave time widgets
  buildLeaveTimeWidget('lsTimeWrap', 'lsTime');
  buildLeaveTimeWidget('leTimeWrap', 'leTime');

  // bind leaveUnit change
  var unitSel = document.getElementById('leaveUnit');
  if (unitSel) {
    unitSel.addEventListener('change', function () {
      var hourSec = document.getElementById('hourSection');
      if (hourSec) hourSec.style.display = (this.value === 'hour') ? '' : 'none';
      calcDays();
    });
  }

  // bind leave type → ใบรับรองแพทย์
  var typeSel = document.getElementById('leaveTypeId');
  if (typeSel) typeSel.addEventListener('change', checkMedCert);

  // bind datepickers (jQuery UI ถูก load หลัง DOMContentLoaded ใน layout)
  // ใช้ setTimeout เพื่อรอ jQuery จาก CDN โหลดเสร็จ
  waitForjQuery(function () {
    $.datepicker.setDefaults({ dateFormat: 'dd/mm/yy', changeMonth: true, changeYear: true });

    $('#startDate').datepicker({
      dateFormat: 'dd/mm/yy',
      onSelect: function (d) {
        document.getElementById('startDateHidden').value = _dispToISO(d);
        calcDays();
      }
    });
    $('#endDate').datepicker({
      dateFormat: 'dd/mm/yy',
      onSelect: function (d) {
        document.getElementById('endDateHidden').value = _dispToISO(d);
        calcDays();
      }
    });
  });
});

// รอ jQuery พร้อมก่อนเรียก (layout inject jQuery ท้าย <body>)
function waitForjQuery(fn) {
  if (typeof jQuery !== 'undefined') { fn(); return; }
  var t = setInterval(function () {
    if (typeof jQuery !== 'undefined') { clearInterval(t); fn(); }
  }, 30);
}

// helper: dd/mm/yyyy → YYYY-MM-DD
function _dispToISO(str) {
  if (!str) return '';
  var p = str.split('/');
  return p.length === 3 ? p[2] + '-' + p[1] + '-' + p[0] : '';
}

// ── คำนวณจำนวนวัน/ชั่วโมง ────────────────────────────────────────────────
function calcDays() {
  var sEl     = document.getElementById('startDateHidden');
  var eEl     = document.getElementById('endDateHidden');
  var uEl     = document.getElementById('leaveUnit');
  var infoEl  = document.getElementById('daysInfo');
  var countEl = document.getElementById('daysCount');
  if (!sEl || !eEl || !uEl || !infoEl || !countEl) return;

  var s = sEl.value;
  var e = eEl.value;
  var u = uEl.value;

  if (!s || !e) { infoEl.style.display = 'none'; return; }

  var d1 = new Date(s);
  var d2 = new Date(e);
  var daysDiff = Math.round((d2 - d1) / 86400000);

  if (daysDiff < 0) {
    countEl.innerHTML = "<span class='text-danger'>วันที่สิ้นสุดต้องไม่น้อยกว่าวันที่เริ่ม</span>";
    infoEl.style.display = '';
    return;
  }

  if (u === 'day') {
    countEl.textContent = (daysDiff + 1) + ' วัน';
    infoEl.style.display = '';
    return;
  }

  // hour mode
  var sTime = document.getElementById('lsTime').value;
  var eTime = document.getElementById('leTime').value;
  if (!sTime || !eTime) { infoEl.style.display = 'none'; return; }

  function timeToDecimal(t) {
    if (!t) return 0;
    var p = t.split(':');
    return parseInt(p[0], 10) + (parseInt(p[1], 10) / 60);
  }

  var sStart = timeToDecimal(shiftConfig.start);
  var sEnd   = timeToDecimal(shiftConfig.end);
  var bStart = timeToDecimal(shiftConfig.breakStart);
  var bEnd   = timeToDecimal(shiftConfig.breakEnd);

  function calcHoursForDay(reqStart, reqEnd) {
    var ws = Math.max(reqStart, sStart);
    var we = Math.min(reqEnd, sEnd);
    if (ws >= we) return 0;
    var total = we - ws;
    var obs = Math.max(ws, bStart);
    var obe = Math.min(we, bEnd);
    if (obs < obe) total -= (obe - obs);
    return total;
  }

  var startH   = timeToDecimal(sTime);
  var endH     = timeToDecimal(eTime);
  var fullDayH = calcHoursForDay(sStart, sEnd) || 8;
  var totalH   = 0;

  if (daysDiff === 0) {
    totalH = calcHoursForDay(startH, endH);
  } else {
    totalH = calcHoursForDay(startH, sEnd)
           + calcHoursForDay(sStart, endH)
           + (daysDiff - 1) * fullDayH;
  }

  if (totalH >= 0) {
    var hrs  = Math.floor(totalH);
    var mins = Math.round((totalH - hrs) * 60);
    var txt  = (hrs  > 0 ? hrs  + ' ชั่วโมง ' : '')
             + (mins > 0 ? mins + ' นาที'      : '');
    countEl.textContent = txt || '0 นาที';
    infoEl.style.display = '';
  } else {
    infoEl.style.display = 'none';
  }
}

// ── ตรวจใบรับรองแพทย์ ────────────────────────────────────────────────────
var sickNames = ['ลาป่วย', 'sick', 'ป่วย'];
function checkMedCert() {
  var sel  = document.getElementById('leaveTypeId');
  var wrap = document.getElementById('medCertWrap');
  if (!sel || !wrap) return;
  var txt    = sel.options[sel.selectedIndex] ? sel.options[sel.selectedIndex].text.toLowerCase() : '';
  var isSick = sickNames.some(function (k) { return txt.indexOf(k) !== -1; });
  wrap.style.display = isSick ? '' : 'none';
}
</script>
