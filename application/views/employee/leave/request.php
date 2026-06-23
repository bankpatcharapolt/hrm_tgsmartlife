<?php defined('BASEPATH') OR exit(); ?>
<div class="card" style="max-width:640px">
  <div class="card-header"><i class="bi bi-calendar-plus me-2"></i>ยื่นคำขอลา</div>
  <div class="card-body">
    <?= form_open_multipart('employee/leave/store') ?>
    
    <div class="row g-3">
      <div class="col-12">
        <label class="form-label">ประเภทการลา *</label>
        <select name="leave_type_id" class="form-select" required>
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
        <select name="leave_unit" class="form-select" id="leaveUnit" onchange="toggleHour(this.value)">
          <option value="day" selected>ลาเต็มวัน</option>
          <option value="hour">ลาชั่วโมง</option>
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
          <i class="bi bi-info-circle me-2"></i>จำนวน <strong id="daysCount">0</strong> วัน
        </div>
      </div> 

      <div class="col-12" id="hourSection" style="display:none">
        <div class="p-3 rounded" style="background:#eff6ff; border:1px solid #bae6fd">
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

      <!-- [ข้อ 2] ใบรับรองแพทย์ — แสดงอัตโนมัติเมื่อเลือกลาป่วย -->
      <div class="col-12" id="medCertWrap" style="display:none">
        <div class="p-3 rounded" style="background:#fff7ed;border:1px solid #fed7aa">
          <label class="form-label fw-semibold" style="color:#c2410c">
            <i class="bi bi-file-medical me-1"></i>ใบรับรองแพทย์(ถ้ามี)
          
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
// มาร์ปข้อมูลกะการทำงานจริงของ User จากฝั่ง Backend มาลงตัวแปร JS object 
// ถ้าไม่มีข้อมูลเบรกใน DB จะใช้ 12:30 - 13:30 เป็นหลัก
const shiftConfig = {
    start: "<?= !empty($shift->start_time) ? $shift->start_time : '08:30:00' ?>",
    end: "<?= !empty($shift->end_time) ? $shift->end_time : '17:30:00' ?>",
    breakStart: "<?= !empty($shift->break_start_time) ? $shift->break_start_time : '12:30:00' ?>",
    breakEnd: "<?= !empty($shift->break_end_time) ? $shift->break_end_time : '13:30:00' ?>"
};

function toggleHour(v) {
    var hourSec = document.getElementById('hourSection');
    if (hourSec) {
        hourSec.style.display = (v === 'hour') ? '' : 'none';
    }
    calcDays(); 
}

// helper: แปลง dd/mm/yyyy → YYYY-MM-DD
function _dispToISO(str) {
    if (!str) return '';
    var p = str.split('/');
    return p.length===3 ? p[2]+'-'+p[1]+'-'+p[0] : '';
}

// init datepickers เมื่อ jQuery โหลด
// time-only widget สำหรับ leave hours
function buildLeaveTimeWidget(wrapId, hiddenId) {
    var $wrap   = $('#' + wrapId);
    var initVal = $('#' + hiddenId).val() || '00:00';
    var parts   = initVal.split(':');
    var ch = parseInt(parts[0], 10) || 0;
    var cm = parseInt(parts[1], 10) || 0;
    var $selH = $("<select class=\"dt-hh\"></select>");
    for(var h=0;h<=23;h++){var hv=(h<10?"0":"")+h;var $o=$("<option>").val(hv).text(hv);if(h===ch)$o.prop("selected",true);$selH.append($o);}
    var $selM = $("<select class=\"dt-mm\"></select>");
    for(var m=0;m<=59;m++){var mv=(m<10?"0":"")+m;var $p=$("<option>").val(mv).text(mv);if(m===cm)$p.prop("selected",true);$selM.append($p);}
    $wrap.find('.dt-time-wrap').remove();
    var $tw = $('<div class="dt-time-wrap" style="flex:1"></div>');
    $tw.append('<select class="dt-hh">' + sh + '</select>');
    $tw.append('<span class="dt-colon">:</span>');
    $tw.append('<select class="dt-mm">' + sm + '</select>');
    $wrap.prepend($tw);
    function sync() {
        $('#' + hiddenId).val($wrap.find('.dt-hh').val() + ':' + $wrap.find('.dt-mm').val() + ':00');
        calcDays();
    }
    $wrap.find('.dt-hh, .dt-mm').on('change', sync);
    sync();
}

$(document).ready(function(){
    buildLeaveTimeWidget('lsTimeWrap', 'lsTime');
    buildLeaveTimeWidget('leTimeWrap', 'leTime');
    $('#startDate').datepicker({
        dateFormat:'dd/mm/yy',
        onSelect:function(d){
            $('#startDateHidden').val(_dispToISO(d));
            calcDays();
        }
    });
    $('#endDate').datepicker({
        dateFormat:'dd/mm/yy',
        onSelect:function(d){
            $('#endDateHidden').val(_dispToISO(d));
            calcDays();
        }
    });
});

function calcDays() {
    var sEl = document.getElementById("startDateHidden"),
        eEl = document.getElementById("endDateHidden"),
        uEl = document.getElementById("leaveUnit"),
        infoEl = document.getElementById("daysInfo"),
        countEl = document.getElementById("daysCount"),
        shEl = document.getElementById('lsTime'),
        ehEl = document.getElementById('leTime');
    
    if (!sEl || !eEl || !uEl || !infoEl || !countEl) return;

    var s = sEl.value, 
        e = eEl.value,
        u = uEl.value;
    
    if(!s || !e) {
        infoEl.style.display = "none";
        return;
    }

    var d1 = new Date(s);
    var d2 = new Date(e);
    var daysDiff = Math.round((d2 - d1) / 86400000);

    if (daysDiff < 0) {
        countEl.innerHTML = "<span class='text-danger'>วันที่สิ้นสุดต้องไม่น้อยกว่าวันที่เริ่ม</span>";
        infoEl.style.display = "";
        return;
    }

    if (u === 'day') {
        var d = daysDiff + 1;
        countEl.textContent = d + " วัน";
        infoEl.style.display = "";
    } else if (u === 'hour') {
        if (!shEl || !ehEl) return;
        var sTime = shEl.value;
        var eTime = ehEl.value;
        
        if (!sTime || !eTime) {
            infoEl.style.display = "none";
            return;
        }

        // แปลงชั่วโมง String เป็น ทศนิยม (เช่น 08:30 -> 8.5)
        function timeToDecimal(timeStr) {
            if (!timeStr) return 0;
            var parts = timeStr.split(':');
            return parseInt(parts[0], 10) + (parseInt(parts[1], 10) / 60);
        }

        var sStart = timeToDecimal(shiftConfig.start);
        var sEnd = timeToDecimal(shiftConfig.end);
        var bStart = timeToDecimal(shiftConfig.breakStart);
        var bEnd = timeToDecimal(shiftConfig.breakEnd);

        var startH = timeToDecimal(sTime);
        var endH = timeToDecimal(eTime);

        // ฟังก์ชันคำนวณชั่วโมงรายวันแบบติดลบเงื่อนไขเบรกออกไป
        function calcHoursForDay(reqStart, reqEnd) {
            var workStart = Math.max(reqStart, sStart);
            var workEnd = Math.min(reqEnd, sEnd);
            if (workStart >= workEnd) return 0;
            
            var total = workEnd - workStart;
            var overlapBreakStart = Math.max(workStart, bStart);
            var overlapBreakEnd = Math.min(workEnd, bEnd);
            
            if (overlapBreakStart < overlapBreakEnd) {
                total -= (overlapBreakEnd - overlapBreakStart);
            }
            return total;
        }

        var fullDayHours = calcHoursForDay(sStart, sEnd);
        if (fullDayHours <= 0) fullDayHours = 8;
        var totalHours = 0;

        if (daysDiff === 0) {
            totalHours = calcHoursForDay(startH, endH);
        } else {
            var firstDayHours = calcHoursForDay(startH, sEnd);
            var lastDayHours = calcHoursForDay(sStart, endH);
            var middleDays = daysDiff - 1;
            totalHours = firstDayHours + lastDayHours + (middleDays * fullDayHours);
        }

        if (totalHours >= 0) {
            // โค้ดแปลงทศนิยมเป็น "x ชั่วโมง y นาที" ให้ดูง่าย
            var hrs = Math.floor(totalHours);
            var mins = Math.round((totalHours - hrs) * 60);
            var timeText = "";
            
            if (hrs > 0) timeText += hrs + " ชั่วโมง ";
            if (mins > 0) timeText += mins + " นาที";
            if (timeText === "") timeText = "0 นาที";

            countEl.textContent = timeText;
            infoEl.style.display = "";
        } else {
            infoEl.style.display = "none";
        }
    }
}

// [ข้อ 2] แสดง/ซ่อนใบรับรองแพทย์ตาม leave_type ที่เลือก
var sickNames = ['ลาป่วย','sick','ป่วย'];
function checkMedCert() {
    var sel  = document.querySelector('[name=leave_type_id]');
    var wrap = document.getElementById('medCertWrap');
    var inp  = document.getElementById('medCertInput');
    if (!sel || !wrap) return;
    var txt = (sel.options[sel.selectedIndex] ? sel.options[sel.selectedIndex].text : '').toLowerCase();
    var isSick = sickNames.some(function(k){ return txt.indexOf(k) !== -1; });
    wrap.style.display = isSick ? '' : 'none';
    // if (inp) inp.required = isSick;
}
document.addEventListener('DOMContentLoaded', function(){
    var sel = document.querySelector('[name=leave_type_id]');
    if (sel) sel.addEventListener('change', checkMedCert);
});
</script>