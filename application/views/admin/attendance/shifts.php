<?php defined('BASEPATH') OR exit(); ?>
<div class="row g-3">
  <!-- Add/Edit Shift Form -->
  <div class="col-lg-5">
    <div class="card">
      <div class="card-header"><i class="bi bi-plus-circle me-2"></i>เพิ่ม/แก้ไขกะการทำงาน</div>
      <div class="card-body">
        <?=form_open('admin/attendance/store_shift', array('onsubmit'=>'return validateShiftForm()'))?>
        <input type="hidden" name="<?=$this->security->get_csrf_token_name()?>" value="<?=$this->security->get_csrf_hash()?>">
        <input type="hidden" name="shift_id" id="shiftId" value="">
        <div class="mb-3">
          <label class="form-label">ชื่อกะ *</label>
          <input type="text" name="name" id="sfName" class="form-control" required placeholder="เช่น กะเช้า กะบ่าย กะดึก">
        </div>
        <div class="row g-2 mb-3">
          <div class="col-6">
            <label class="form-label">เวลาเริ่มกะ *</label>
            <div class="shift-time-wrap" id="sfStartWrap" style="display:flex;flex-wrap:nowrap;gap:6px;align-items:stretch">
              <!-- ไม่มี dt-date — time-only widget -->
              <input type="hidden" name="start_time" id="sfStart" value="08:30:00">
            </div>
          </div>
          <div class="col-6">
            <label class="form-label">เวลาสิ้นสุดกะ *</label>
            <div class="shift-time-wrap" id="sfEndWrap" style="display:flex;flex-wrap:nowrap;gap:6px;align-items:stretch">
              <input type="hidden" name="end_time" id="sfEnd" value="17:30:00">
            </div>
          </div>
        </div>
        <div class="row g-2 mb-3">
          <div class="col-4">
            <label class="form-label">พักกลางวัน (นาที)</label>
            <input type="number" name="break_minutes" id="sfBreak" class="form-control" value="60" min="0">
          </div>
          <div class="col-4">
            <label class="form-label">สายเมื่อ (นาที)</label>
            <input type="number" name="late_threshold_minutes" id="sfLate" class="form-control" value="15" min="0">
          </div>
          <div class="col-4">
            <label class="form-label">OT หลังออก (นาที)</label>
            <input type="number" name="ot_starts_after_minutes" id="sfOT" class="form-control" value="0" min="0">
          </div>
        </div>
        <div class="row g-2 mb-3">
          <div class="col-8">
            <label class="form-label">สีแสดงผล</label>
            <div class="input-group">
              <input type="color" name="color" id="sfColor" class="form-control form-control-color" value="#1a56db">
              <input type="text" class="form-control" id="sfColorHex" value="#1a56db" oninput="document.getElementById('sfColor').value=this.value">
            </div>
          </div>
          <div class="col-4 d-flex align-items-end">
            <div class="form-check mb-2">
              <input class="form-check-input" type="checkbox" name="is_night_shift" id="sfNight" value="1">
              <label class="form-check-label small" for="sfNight">กะข้ามวัน</label>
            </div>
          </div>
        </div>
        <div class="d-flex gap-2">
          <button type="submit" class="btn btn-primary" id="sfBtn"><i class="bi bi-save me-1"></i>บันทึก</button>
          <button type="button" class="btn btn-outline-secondary" onclick="resetShiftForm()">ล้าง</button>
        </div>
        <?=form_close()?>
      </div>
    </div>

    <!-- Assign Shift to Employee -->
    <div class="card mt-3">
      <div class="card-header"><i class="bi bi-person-gear me-2"></i>กำหนดกะให้พนักงาน</div>
      <div class="card-body">
        <?=form_open('admin/attendance/assign_shift')?>
        <input type="hidden" name="<?=$this->security->get_csrf_token_name()?>" value="<?=$this->security->get_csrf_hash()?>">
        <div class="mb-3">
          <label class="form-label">เลือกพนักงาน</label>
          <select name="user_id" class="form-select" required>
            <option value="">-- เลือก --</option>
            <?php foreach($this->User_model->get_all(array('status'=>'active'),200) as $e):?>
            <option value="<?=$e->id?>"><?=$e->employee_id?> – <?=$e->first_name.' '.$e->last_name?></option>
            <?php endforeach;?>
          </select>
        </div>
        <div class="mb-3">
          <label class="form-label">กะที่กำหนด</label>
          <select name="shift_id" class="form-select">
            <option value="">– ไม่กำหนดกะ –</option>
            <?php foreach($shifts as $s):?>
            <option value="<?=$s->id?>"><?=$s->name?> (<?=substr($s->start_time,0,5)?>–<?=substr($s->end_time,0,5)?>)</option>
            <?php endforeach;?>
          </select>
        </div>
        <button type="submit" class="btn btn-primary btn-sm"><i class="bi bi-check me-1"></i>กำหนดกะ</button>
        <?=form_close()?>
      </div>
    </div>
  </div>

  <!-- Shift List -->
  <div class="col-lg-7">
    <div class="card">
      <div class="card-header"><i class="bi bi-list-ul me-2"></i>กะทั้งหมด</div>
      <div class="card-body p-0">
        <div class="table-responsive">
          <table class="table table-hover mb-0">
            <thead><tr><th>ชื่อกะ</th><th>เวลา</th><th>พัก</th><th>สายเมื่อ</th><th>สถานะ</th><th>จัดการ</th></tr></thead>
            <tbody>
              <?php if(!empty($shifts)):foreach($shifts as $s):?>
              <tr>
                <td>
                  <span class="badge me-1" style="background:<?=$s->color?>">&nbsp;</span>
                  <strong><?=$s->name?></strong>
                  <?php if($s->is_night_shift):?><span class="badge bg-dark ms-1" style="font-size:.65rem">ข้ามวัน</span><?php endif;?>
                </td>
                <td style="font-size:.83rem"><?=substr($s->start_time,0,5)?> – <?=substr($s->end_time,0,5)?></td>
                <td style="font-size:.83rem"><?=$s->break_minutes?> น.</td>
                <td style="font-size:.83rem"><?=$s->late_threshold_minutes?> น.</td>
                <td><span class="badge bg-<?=$s->status==='active'?'success':'secondary'?>"><?=$s->status==='active'?'ใช้งาน':'ปิด'?></span></td>
                <td>
                  <button class="btn btn-outline-secondary btn-sm px-2 py-0"
                    onclick="editShift(<?=$s->id?>,'<?=addslashes($s->name)?>','<?=substr($s->start_time,0,5)?>','<?=substr($s->end_time,0,5)?>',<?=$s->break_minutes?>,<?=$s->late_threshold_minutes?>,<?=$s->ot_starts_after_minutes?>,'<?=$s->color?>',<?=$s->is_night_shift?>)">
                    <i class="bi bi-pencil"></i>
                  </button>
                  <a href="<?=base_url('admin/attendance/delete_shift/'.$s->id)?>"
                     onclick="return confirm('ลบกะ <?=addslashes($s->name)?>?')"
                     class="btn btn-outline-danger btn-sm px-2 py-0 ms-1"><i class="bi bi-trash"></i></a>
                </td>
              </tr>
              <?php endforeach;else:?>
              <tr><td colspan="6" class="text-center text-muted py-3">ยังไม่มีกะ</td></tr>
              <?php endif;?>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</div>
<?php $extra_js = <<<'JSEOF'
<script>
// time-only widget สำหรับกะการทำงาน
function buildTimeWidget(wrapId, hiddenId, initVal) {
  var parts = (initVal || "00:00").split(":");
  var ch = parseInt(parts[0], 10);
  var cm = parseInt(parts[1], 10);
  if (isNaN(ch)) ch = 0;
  if (isNaN(cm)) cm = 0;

  var $wrap = $("#" + wrapId);
  $wrap.find(".dt-time-wrap").remove();

  var $tw = $('<div class="dt-time-wrap" style="flex:1"></div>');
  var $selH = $('<select class="dt-hh"></select>');
  for (var h = 0; h <= 23; h++) {
    var hv = (h < 10 ? "0" : "") + h;
    var $o = $("<option>").val(hv).text(hv);
    if (h === ch) $o.prop("selected", true);
    $selH.append($o);
  }
  var $selM = $('<select class="dt-mm"></select>');
  for (var m = 0; m <= 59; m++) {
    var mv = (m < 10 ? "0" : "") + m;
    var $p = $("<option>").val(mv).text(mv);
    if (m === cm) $p.prop("selected", true);
    $selM.append($p);
  }
  $tw.append($selH);
  $tw.append('<span class="dt-colon">:</span>');
  $tw.append($selM);
  $wrap.empty().append($tw);

  function sync() {
    var hVal = $wrap.find(".dt-hh").val();
    var mVal = $wrap.find(".dt-mm").val();
    $("#" + hiddenId).val(hVal + ":" + mVal + ":00");
  }
  $selH.on("change", sync);
  $selM.on("change", sync);
  sync(); // sync ทันทีหลัง build
}

$(document).ready(function() {
  buildTimeWidget("sfStartWrap", "sfStart", "08:30");
  buildTimeWidget("sfEndWrap",   "sfEnd",   "17:30");
  document.getElementById("sfColor").addEventListener("input", function() {
    document.getElementById("sfColorHex").value = this.value;
  });
});

function editShift(id, name, start, end, brk, late, ot, color, night) {
  document.getElementById("shiftId").value    = id;
  document.getElementById("sfName").value     = name;
  buildTimeWidget("sfStartWrap", "sfStart", start);
  buildTimeWidget("sfEndWrap",   "sfEnd",   end);
  document.getElementById("sfBreak").value    = brk;
  document.getElementById("sfLate").value     = late;
  document.getElementById("sfOT").value       = ot;
  document.getElementById("sfColor").value    = color;
  document.getElementById("sfColorHex").value = color;
  document.getElementById("sfNight").checked  = (night == 1);
  document.getElementById("sfBtn").innerHTML  = '<i class="bi bi-save me-1"></i>อัปเดตกะ';
  window.scrollTo({ top: 0, behavior: "smooth" });
}

function resetShiftForm() {
  document.getElementById("shiftId").value   = "";
  buildTimeWidget("sfStartWrap", "sfStart", "08:30");
  buildTimeWidget("sfEndWrap",   "sfEnd",   "17:30");
  document.getElementById("sfBtn").innerHTML = '<i class="bi bi-save me-1"></i>บันทึก';
}

function validateShiftForm() {
  var st = document.getElementById("sfStart").value;
  var en = document.getElementById("sfEnd").value;
  var nm = document.getElementById("sfName").value.trim();
  if (!nm)                  { alert("กรุณาระบุชื่อกะ"); return false; }
  if (!st || st === "::")   { alert("กรุณาเลือกเวลาเริ่มกะ"); return false; }
  if (!en || en === "::")   { alert("กรุณาเลือกเวลาสิ้นสุดกะ"); return false; }
  return true;
}
</script>
JSEOF;
?>
