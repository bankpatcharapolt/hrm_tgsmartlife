<?php defined('BASEPATH') OR exit(); $r=$req; ?>
<div class="card" style="max-width:680px">
  <div class="card-header"><i class="bi bi-pencil me-2"></i>แก้ไขคำขอลา</div>
  <div class="card-body">
    <div class="alert alert-info py-2 px-3 mb-3 small">
      <i class="bi bi-info-circle me-1"></i>แก้ไขได้เฉพาะคำขอที่ <strong>รอการอนุมัติ</strong> เท่านั้น
    </div>
    <?=form_open_multipart('employee/leave/edit/'.$r->id)?>
    <input type="hidden" name="<?=$this->security->get_csrf_token_name()?>" value="<?=$this->security->get_csrf_hash()?>">
    <div class="row g-3">
      <div class="col-12">
        <label class="form-label">ประเภทการลา *</label>
        <select name="leave_type_id" id="leaveTypeEdit" class="form-select" onchange="checkMedCert(this)" required>
          <option value="">-- เลือก --</option>
          <?php foreach($leave_types as $lt):?>
          <option value="<?=$lt->id?>" <?=$r->leave_type_id==$lt->id?'selected':''?>>
            <?=$lt->name?><?=$lt->quota_per_year>0?' (โควต้า '.$lt->quota_per_year.' วัน/ปี)':' (ไม่จำกัด)'?>
          </option>
          <?php endforeach;?>
        </select>
      </div>
      <div class="col-md-4">
        <label class="form-label">หน่วยการลา</label>
        <select name="leave_unit" class="form-select" id="leaveUnit" onchange="toggleHour(this.value)">
          <option value="day"  <?=(!$r->leave_unit||$r->leave_unit==='day' )?'selected':''?>>ลาเต็มวัน</option>
          <option value="hour" <?=($r->leave_unit==='hour')?'selected':''?>>ลาชั่วโมง</option>
        </select>
      </div>
      <div class="col-md-4">
        <label class="form-label">วันที่เริ่มลา *</label>
        <input type="text" class="form-control jq-date-only" id="startDate"
               placeholder="dd/mm/yyyy" autocomplete="off" readonly style="cursor:pointer"
               value="<?=$r->start_date?date('d/m/Y',strtotime($r->start_date)):''?>" required>
        <input type="hidden" name="start_date" id="startDateHidden" value="<?=$r->start_date?>">
      </div>
      <div class="col-md-4">
        <label class="form-label">วันที่สิ้นสุด *</label>
        <input type="text" class="form-control jq-date-only" id="endDate"
               placeholder="dd/mm/yyyy" autocomplete="off" readonly style="cursor:pointer"
               value="<?=$r->end_date?date('d/m/Y',strtotime($r->end_date)):''?>" required>
        <input type="hidden" name="end_date" id="endDateHidden" value="<?=$r->end_date?>">
      </div>
      <!-- ชั่วโมง -->
      <div class="col-12" id="hourSection" <?=($r->leave_unit==='hour')?'':'style="display:none"'?>>
        <div class="p-2 rounded" style="background:#eff6ff;border:1px solid #bae6fd">
          <div class="row g-2 align-items-end">
            <div class="col-5">
              <label class="form-label small">เวลาเริ่มลา</label>
              <div class="leave-time-wrap" id="lsTimeWrap">
                <input type="hidden" name="leave_start_time" id="lsTime" value="<?=$r->start_time?substr($r->start_time,0,5):''">
              </div>
            </div>
            <div class="col-2 d-flex align-items-end pb-1 justify-content-center">–</div>
            <div class="col-5">
              <label class="form-label small">เวลาสิ้นสุด</label>
              <div class="leave-time-wrap" id="leTimeWrap">
                <input type="hidden" name="leave_end_time" id="leTime" value="<?=$r->end_time?substr($r->end_time,0,5):''">
              </div>
            </div>
          </div>
        </div>
      </div>
      <div class="col-12" id="daysInfo" style="font-size:.83rem;color:#1a56db"></div>
      <div class="col-12">
        <label class="form-label">เหตุผล *</label>
        <textarea name="reason" class="form-control" rows="3" required><?=htmlspecialchars($r->reason)?></textarea>
      </div>
      <div class="col-12">
        <label class="form-label">เอกสารประกอบ (ถ้ามี)</label>
        <input type="file" name="document" class="form-control" accept=".pdf,.jpg,.jpeg,.png">
        <?php if(!empty($r->document_path)):?>
        <div class="mt-1">
          <a href="<?=base_url($r->document_path)?>" target="_blank"
             class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-file-earmark me-1"></i>ดูเอกสารปัจจุบัน
          </a>
          <small class="text-muted ms-1">(อัปโหลดใหม่เพื่อแทนที่)</small>
        </div>
        <?php endif;?>
      </div>

      <!-- [ข้อ 2] ใบรับรองแพทย์ — แสดงเฉพาะลาป่วย -->
      <div class="col-12" id="medCertEditWrap" style="display:none">
        <div class="p-3 rounded" style="background:#fff7ed;border:1px solid #fed7aa">
          <label class="form-label fw-semibold" style="color:#c2410c">
            <i class="bi bi-file-medical me-1"></i>ใบรับรองแพทย์
            <span class="text-muted fw-normal" style="font-size:.75rem">(ถ้ามี)</span>
          </label>
          <!-- แสดงไฟล์เดิมถ้ามี -->
          <?php if(!empty($r->medical_cert_path)):?>
          <div class="mb-2 d-flex align-items-center gap-2">
            <a href="<?=base_url($r->medical_cert_path)?>" target="_blank"
               class="btn btn-outline-warning btn-sm">
              <i class="bi bi-file-medical me-1"></i>ดูใบรับรองแพทย์ปัจจุบัน
            </a>
            <small class="text-muted">(อัปโหลดใหม่เพื่อแทนที่)</small>
          </div>
          <?php endif;?>
          <input type="file" name="medical_cert" id="medCertFileEdit"
                 class="form-control" accept=".pdf,.jpg,.jpeg,.png">
          <div class="form-text">รองรับ PDF, JPG, PNG ขนาดไม่เกิน 5MB</div>
        </div>
      </div>
    </div>
    <div class="mt-4 d-flex gap-2">
      <button type="submit" class="btn btn-primary"><i class="bi bi-save me-1"></i>บันทึกการแก้ไข</button>
      <a href="<?=base_url('employee/leave')?>" class="btn btn-outline-secondary">ยกเลิก</a>
    </div>
    <?=form_close()?>
  </div>
</div>
<script>
function toggleHour(v){
  document.getElementById('hourSection').style.display = v==='hour' ? '' : 'none';
}
// helper: dd/mm/yyyy → YYYY-MM-DD
function _dispToISO(str) {
    if (!str) return '';
    var p = str.split('/');
    return p.length===3 ? p[2]+'-'+p[1]+'-'+p[0] : '';
}

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
    }
    $wrap.find('.dt-hh, .dt-mm').on('change', sync);
    sync();
}

$(document).ready(function(){
    buildLeaveTimeWidget('lsTimeWrap', 'lsTime');
    buildLeaveTimeWidget('leTimeWrap', 'leTime');
    $('#startDate').datepicker({
        dateFormat:'dd/mm/yy',
        onSelect:function(d){$('#startDateHidden').val(_dispToISO(d));calcDays();}
    });
    $('#endDate').datepicker({
        dateFormat:'dd/mm/yy',
        onSelect:function(d){$('#endDateHidden').val(_dispToISO(d));calcDays();}
    });
    calcDays();
    var selEdit = document.getElementById('leaveTypeEdit');
    if (selEdit) checkMedCert(selEdit);
});

function calcDays(){
  var s = document.getElementById('startDateHidden').value;
  var e = document.getElementById('endDateHidden').value;
  var u = document.getElementById('leaveUnit').value;
  var di = document.getElementById('daysInfo');
  if(s && e && u==='day'){
    var d = Math.round((new Date(e)-new Date(s))/86400000)+1;
    if(d>0){ di.textContent='รวม '+d+' วัน'; return; }
  }
  di.textContent='';
}

// [ข้อ 2] แสดง/ซ่อนใบรับรองแพทย์ตาม leave_type ที่เลือก
var _sickKw = ['ลาป่วย','sick','ป่วย'];
function checkMedCert(sel) {
  var wrap = document.getElementById('medCertEditWrap');
  if (!wrap || !sel) return;
  var txt  = sel.options[sel.selectedIndex]
             ? sel.options[sel.selectedIndex].text.toLowerCase() : '';
  var sick = _sickKw.some(function(k){ return txt.indexOf(k) !== -1; });
  wrap.style.display = sick ? '' : 'none';
}

// init checkMedCert ถูกเรียกแล้วใน $(document).ready
</script>
