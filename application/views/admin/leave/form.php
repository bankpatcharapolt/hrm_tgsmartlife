<?php defined('BASEPATH') OR exit(); $r=$req; ?>
<div class="card" style="max-width:820px">
  <div class="card-header"><i class="bi bi-calendar-plus me-2"></i><?=$page_title?></div>
  <div class="card-body">

    <?php if($r && $r->status==='approved'):?>
    <div class="alert alert-warning py-2 px-3 mb-3" style="font-size:.83rem">
      <i class="bi bi-exclamation-triangle me-1"></i>
      คำขอนี้ <strong>อนุมัติแล้ว</strong> การแก้ไขจะส่งผลต่อสถิติการลาของพนักงาน
    </div>
    <?php endif;?>

    <?=form_open_multipart($r ? 'admin/leave/edit/'.$r->id : 'admin/leave/store')?>
    <input type="hidden" name="<?=$this->security->get_csrf_token_name()?>" value="<?=$this->security->get_csrf_hash()?>">

    <div class="row g-3">
      <!-- พนักงาน -->
      <div class="col-md-6">
        <label class="form-label">พนักงาน <span class="text-danger">*</span></label>
        <select name="user_id" class="form-select" required>
          <option value="">-- เลือกพนักงาน --</option>
          <?php foreach($employees as $e):?>
          <option value="<?=$e->id?>" <?=($r && $r->user_id==$e->id)?'selected':''?>>
            <?=$e->employee_id?> – <?=$e->first_name.' '.$e->last_name?>
          </option>
          <?php endforeach;?>
        </select>
      </div>

      <!-- ประเภทการลา -->
      <div class="col-md-6">
        <label class="form-label">ประเภทการลา <span class="text-danger">*</span></label>
        <select name="leave_type_id" class="form-select" required>
          <option value="">-- เลือกประเภทการลา --</option>
          <?php foreach($leave_types as $lt):?>
          <option value="<?=$lt->id?>" <?=($r && $r->leave_type_id==$lt->id)?'selected':''?>>
            <?=$lt->name?><?=$lt->quota_per_year>0?' (โควต้า '.$lt->quota_per_year.' วัน/ปี)':' (ไม่จำกัด)'?>
          </option>
          <?php endforeach;?>
        </select>
      </div>

      <!-- หน่วยการลา -->
      <div class="col-md-4">
        <label class="form-label">หน่วยการลา</label>
        <select name="leave_unit" class="form-select" id="leaveUnit" onchange="toggleHourSection(this.value)">
          <option value="day"  <?=(!$r || ($r->leave_unit??'day')==='day') ?'selected':''?>>ลาเต็มวัน</option>
          <option value="hour" <?=($r && ($r->leave_unit??'')==='hour')     ?'selected':''?>>ลาเป็นชั่วโมง</option>
        </select>
      </div>

      <!-- วันที่เริ่มลา -->
      <div class="col-md-4">
        <label class="form-label">วันที่เริ่มลา <span class="text-danger">*</span></label>
        <input type="text" name="start_date_display" class="form-control jq-date-only"
               placeholder="dd/mm/yyyy" autocomplete="off" readonly style="cursor:pointer"
               value="<?=$r?date('d/m/Y',strtotime($r->start_date)):''?>" id="startDate" required>
        <input type="hidden" name="start_date" id="startDateHidden"
               value="<?=$r?$r->start_date:''?>">
      </div>

      <!-- วันที่สิ้นสุด -->
      <div class="col-md-4" id="endDateWrap">
        <label class="form-label">วันที่สิ้นสุดการลา <span class="text-danger">*</span></label>
        <input type="text" name="end_date_display" class="form-control jq-date-only"
               placeholder="dd/mm/yyyy" autocomplete="off" readonly style="cursor:pointer"
               value="<?=$r?date('d/m/Y',strtotime($r->end_date)):''?>" id="endDate" required>
        <input type="hidden" name="end_date" id="endDateHidden"
               value="<?=$r?$r->end_date:''?>">
      </div>

      <!-- ช่วงเวลา (ลาชั่วโมง) -->
      <div class="col-12" id="hourSection" <?=(!$r || ($r->leave_unit??'day')!=='hour')?'style="display:none"':''?>>
        <div class="p-3 rounded" style="background:#eff6ff;border:1px solid #bae6fd">
          <div class="fw-semibold small mb-2"><i class="bi bi-clock me-1"></i>ช่วงเวลาที่ลา</div>
          <div class="row g-2 align-items-end">
            <div class="col-md-3">
              <label class="form-label small">เวลาเริ่มลา</label>
              <div class="leave-time-wrap" id="lsTimeWrap">
                <input type="hidden" name="leave_start_time" id="lsTime"
                       value="<?=$r&&!empty($r->start_time)?substr($r->start_time,0,5):''?>">
              </div>
            </div>
            <div class="col-md-3">
              <label class="form-label small">เวลาสิ้นสุดลา</label>
              <div class="leave-time-wrap" id="leTimeWrap">
                <input type="hidden" name="leave_end_time" id="leTime"
                       value="<?=$r&&!empty($r->end_time)?substr($r->end_time,0,5):''?>">
              </div>
            </div>
            <div class="col-md-4 d-flex align-items-end">
              <div class="alert alert-info py-1 px-2 mb-0 small w-100" id="hoursResult" style="display:none">
                รวม <strong id="hoursNum">0</strong> ชั่วโมง
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- สรุปวัน -->
      <div class="col-12">
        <div id="daysInfo" class="alert alert-info py-2 px-3 mb-0 small" <?=$r?'':'style="display:none"'?>>
          <i class="bi bi-calendar me-1"></i>รวม <strong id="daysCount"><?=$r?$r->total_days:0?></strong> วัน
        </div>
      </div>

      <!-- เหตุผล -->
      <div class="col-12">
        <label class="form-label">เหตุผล <span class="text-danger">*</span></label>
        <textarea name="reason" class="form-control" rows="3" required
                  placeholder="ระบุเหตุผลในการลา"><?=$r?htmlspecialchars($r->reason):''?></textarea>
      </div>

      <!-- สถานะ -->
      <div class="col-md-4">
        <label class="form-label">สถานะ</label>
        <select name="status" class="form-select">
          <option value="pending"   <?=($r&&$r->status==='pending')  ?'selected':(!$r?'selected':'')?>>รอการอนุมัติ</option>
          <option value="approved"  <?=($r&&$r->status==='approved') ?'selected':''?>>อนุมัติแล้ว</option>
          <option value="rejected"  <?=($r&&$r->status==='rejected') ?'selected':''?>>ปฏิเสธ</option>
          <option value="cancelled" <?=($r&&$r->status==='cancelled')?'selected':''?>>ยกเลิก</option>
        </select>
      </div>

      <!-- หมายเหตุผู้อนุมัติ -->
      <div class="col-md-8">
        <label class="form-label">หมายเหตุผู้อนุมัติ</label>
        <input type="text" name="approver_note" class="form-control"
               value="<?=$r?htmlspecialchars($r->approver_note??''):''?>"
               placeholder="หมายเหตุสำหรับพนักงาน (ถ้ามี)">
      </div>

      <!-- เอกสารประกอบ -->
      <div class="col-12">
        <label class="form-label">เอกสารประกอบ (PDF/รูปภาพ)</label>
        <input type="file" name="document" class="form-control" accept=".pdf,.jpg,.jpeg,.png">
        <?php if($r && !empty($r->document_path)):?>
        <div class="mt-1">
          <a href="<?=base_url($r->document_path)?>" target="_blank" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-file-earmark me-1"></i>ดูเอกสารปัจจุบัน
          </a>
          <small class="text-muted ms-2">(อัปโหลดไฟล์ใหม่เพื่อแทนที่)</small>
        </div>
        <?php endif;?>
        <div class="form-text text-muted">รองรับ PDF, JPG, PNG ขนาดไม่เกิน 5MB</div>
      </div>

      <!-- [ข้อ 2] ใบรับรองแพทย์ (แสดงเฉพาะเมื่อเลือก leave_type ที่มีชื่อ "ลาป่วย") -->
      <div class="col-12" id="medCertWrap" style="display:none">
        <div class="p-3 rounded" style="background:#fff7ed;border:1px solid #fed7aa">
          <label class="form-label fw-semibold" style="color:#c2410c">
            <i class="bi bi-file-medical me-1"></i>ใบรับรองแพทย์
            <span class="text-danger">*</span>
            <span class="badge bg-warning text-dark ms-1" style="font-size:.68rem">ลาป่วย</span>
          </label>
          <input type="file" name="medical_cert" id="medCertInput"
                 class="form-control" accept=".pdf,.jpg,.jpeg,.png">
          <?php if($r && !empty($r->medical_cert_path)):?>
          <div class="mt-2">
            <a href="<?=base_url($r->medical_cert_path)?>" target="_blank"
               class="btn btn-outline-warning btn-sm">
              <i class="bi bi-file-medical me-1"></i>ดูใบรับรองแพทย์ปัจจุบัน
            </a>
            <small class="text-muted ms-2">(อัปโหลดใหม่เพื่อแทนที่)</small>
          </div>
          <?php elseif($r && !empty($r->medical_cert_path) === false && $r->leave_type_id):?>
          <?php endif;?>
          <div class="form-text">รองรับ PDF, JPG, PNG ขนาดไม่เกิน 5MB</div>
        </div>
      </div>
    </div>

    <div class="mt-4 d-flex gap-2 flex-wrap">
      <button type="submit" class="btn btn-primary">
        <i class="bi bi-save me-1"></i><?=$r?'บันทึกการแก้ไข':'สร้างคำขอลา'?>
      </button>
      <a href="<?=base_url('admin/leave')?>" class="btn btn-outline-secondary">ยกเลิก</a>
      <?php if($r):?>
      <a href="<?=base_url('admin/leave/delete/'.$r->id)?>"
         onclick="return confirm('ลบคำขอลานี้ใช่ไหม?')"
         class="btn btn-outline-danger ms-auto">
        <i class="bi bi-trash me-1"></i>ลบคำขอลา
      </a>
      <?php endif;?>
    </div>
    <?=form_close()?>
  </div>
</div>

<script>
// helper: แปลง dd/mm/yyyy → Date object
function _parseDispDate(str) {
    if (!str) return null;
    var p = str.split('/');
    if (p.length !== 3) return null;
    return new Date(parseInt(p[2],10), parseInt(p[1],10)-1, parseInt(p[0],10));
}
// helper: แปลง dd/mm/yyyy → YYYY-MM-DD
function _dispToISO(str) {
    if (!str) return '';
    var p = str.split('/');
    if (p.length !== 3) return '';
    return p[2]+'-'+p[1]+'-'+p[0];
}

function toggleHourSection(v) {
    var h = document.getElementById('hourSection');
    if (v === 'hour') {
        h.style.display = '';
        // ลาชั่วโมง → copy start_date → end_date
        var sdDisp = document.getElementById('startDate').value;
        if (sdDisp) {
            document.getElementById('endDate').value = sdDisp;
            document.getElementById('endDateHidden').value = _dispToISO(sdDisp);
        }
    } else {
        h.style.display = 'none';
    }
    calcDays();
}

function calcDays() {
    // อ่านจาก hidden (YYYY-MM-DD) เพราะ display เป็น dd/mm/yyyy
    var s = document.getElementById('startDateHidden').value;
    var e = document.getElementById('endDateHidden').value;
    var unit = document.getElementById('leaveUnit').value;
    var info = document.getElementById('daysInfo');
    var cnt  = document.getElementById('daysCount');
    if (s && e && unit === 'day') {
        var d = Math.round((new Date(e) - new Date(s)) / 86400000) + 1;
        if (d > 0) { cnt.textContent = d; info.style.display = ''; }
        else { info.style.display = 'none'; }
    } else {
        info.style.display = 'none';
    }
}

function calcHours() {
    var s = document.getElementById('lsTime').value;   // hidden HH:mm:ss
    var e = document.getElementById('leTime').value;
    var res = document.getElementById('hoursResult');
    var num = document.getElementById('hoursNum');
    if (s && e) {
        var h = ((new Date('2000-01-01 '+e)) - (new Date('2000-01-01 '+s))) / 3600000;
        if (h > 0) { num.textContent = h.toFixed(1); res.style.display = ''; }
        else { res.style.display = 'none'; }
    } else { res.style.display = 'none'; }
}

// init datepickers + time widgets
$(document).ready(function(){
    // start_date picker
    $('#startDate').datepicker({
        dateFormat:'dd/mm/yy',
        onSelect: function(d){
            $('#startDateHidden').val(_dispToISO(d));
            calcDays();
        }
    });
    // end_date picker
    $('#endDate').datepicker({
        dateFormat:'dd/mm/yy',
        onSelect: function(d){
            $('#endDateHidden').val(_dispToISO(d));
            calcDays();
        }
    });
    // time widgets สำหรับช่วงเวลาลา
    buildLeaveTimeWidget('lsTimeWrap', 'lsTime');
    buildLeaveTimeWidget('leTimeWrap', 'leTime');
    calcDays();
    calcHours();
    checkMedCert();
});

// สร้าง time-only widget สำหรับ leave (อ่านค่าเริ่มต้นจาก hidden)
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
        var hh = $wrap.find('.dt-hh').val();
        var mm = $wrap.find('.dt-mm').val();
        $('#' + hiddenId).val(hh + ':' + mm + ':00');
        calcHours();
    }
    $wrap.find('.dt-hh, .dt-mm').on('change', sync);
    sync();
}

// [ข้อ 2] ตรวจว่าเลือกลาป่วยไหม → แสดง/ซ่อนใบรับรองแพทย์
var sickNames = ['ลาป่วย','sick','ป่วย'];
function checkMedCert() {
    var sel  = document.querySelector('[name=leave_type_id]');
    var wrap = document.getElementById('medCertWrap');
    var inp  = document.getElementById('medCertInput');
    if (!sel || !wrap) return;
    var txt = (sel.options[sel.selectedIndex] ? sel.options[sel.selectedIndex].text : '').toLowerCase();
    var isSick = sickNames.some(function(k){ return txt.indexOf(k) !== -1; });
    wrap.style.display = isSick ? '' : 'none';
    if (inp) inp.required = isSick;
}
document.querySelector('[name=leave_type_id]') &&
  document.querySelector('[name=leave_type_id]').addEventListener('change', checkMedCert);
// TomSelect fires custom event
document.querySelector('[name=leave_type_id]') &&
  document.querySelector('[name=leave_type_id]').addEventListener('change', checkMedCert);
</script>
