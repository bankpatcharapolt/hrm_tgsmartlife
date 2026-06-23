<?php defined('BASEPATH') OR exit(); $r=$rec; ?>
<div class="card" style="max-width:640px">
  <div class="card-header"><i class="bi bi-pencil me-2"></i>แก้ไขการเข้างาน — <?=date('d/m/Y',strtotime($r->date))?></div>
  <div class="card-body">
    <?=form_open('employee/attendance/edit/'.$r->id)?>
    <input type="hidden" name="<?=$this->security->get_csrf_token_name()?>" value="<?=$this->security->get_csrf_hash()?>">
    <div class="row g-3">
      <div class="col-md-6">
        <label class="form-label">เวลาเข้างาน</label>
        <div class="jq-dt-wrap">
          <input type="text" class="form-control dt-date" placeholder="dd/mm/yyyy"
                 autocomplete="off" readonly style="cursor:pointer"
                 value="<?=$r->check_in_time?date('d/m/Y',strtotime($r->check_in_time)):''?>">
          <input type="hidden" name="check_in" class="dt-hidden"
                 value="<?=$r->check_in_time??''?>">
        </div>
      </div>
      <div class="col-md-6">
        <label class="form-label">เวลาออกงาน</label>
        <div class="jq-dt-wrap">
          <input type="text" class="form-control dt-date" placeholder="dd/mm/yyyy"
                 autocomplete="off" readonly style="cursor:pointer"
                 value="<?=$r->check_out_time?date('d/m/Y',strtotime($r->check_out_time)):''?>">
          <input type="hidden" name="check_out" class="dt-hidden"
                 value="<?=$r->check_out_time??''?>">
        </div>
      </div>
      <div class="col-md-6">
        <label class="form-label">สถานะ</label>
        <select name="status" class="form-select" onchange="toggleLeave(this.value)">
          <option value="present" <?=$r->status==='present'?'selected':''?>>มาทำงานเต็มวัน</option>
          <option value="half_day"<?=$r->status==='half_day'?'selected':''?>>มาทำงานครึ่งวัน</option>
        </select>
      </div>
      <div class="col-md-6">
        <label class="form-label">หมายเหตุ</label>
        <input type="text" name="note" class="form-control" value="<?=htmlspecialchars($r->note??'')?>">
      </div>
      <!-- Leave section -->
      <div class="col-12" id="leaveSection" <?=$r->status==='leave'?'':'style="display:none"'?>>
        <div class="p-3 rounded" style="background:#eff6ff;border:1px solid #bae6fd">
          <div class="row g-2">
            <div class="col-md-5">
              <label class="form-label small">ประเภทการลา</label>
              <select name="leave_type_id" class="form-select form-select-sm ts-select">
                <option value="">– เลือก –</option>
                <?php foreach($leave_types as $lt):?>
                <option value="<?=$lt->id?>" <?=($r->leave_type_id??'')==$lt->id?'selected':''?>><?=$lt->name?></option>
                <?php endforeach;?>
              </select>
            </div>
            <div class="col-md-4">
              <label class="form-label small">หน่วย</label>
              <select name="leave_unit" class="form-select form-select-sm" onchange="toggleHour(this.value)">
                <option value="day" <?=(!$r->leave_hours)?'selected':''?>>ลาเต็มวัน</option>
                <option value="hour" <?=($r->leave_hours>0)?'selected':''?>>ลาชั่วโมง</option>
              </select>
            </div>
            <div class="col-12" id="hourSection" <?=($r->leave_hours>0)?'':'style="display:none"'?>>
              <div class="row g-1">
                <div class="col-5">
                  <label class="form-label small">เวลาเริ่ม</label>
                  <div class="leave-time-wrap" id="lshWrap">
                    <input type="hidden" name="leave_start_hour" id="lshHidden" value="<?=$r->check_in_time?date('H:i',strtotime($r->check_in_time)):''?>">
                  </div>
                </div>
                <div class="col-2 d-flex align-items-end pb-1 justify-content-center"><span>–</span></div>
                <div class="col-5">
                  <label class="form-label small">เวลาสิ้นสุด</label>
                  <div class="leave-time-wrap" id="lehWrap">
                    <input type="hidden" name="leave_end_hour" id="lehHidden" value="<?=$r->check_out_time?date('H:i',strtotime($r->check_out_time)):''?>">
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
    <div class="mt-4 d-flex gap-2">
      <button type="submit" class="btn btn-primary"><i class="bi bi-save me-1"></i>บันทึกการแก้ไข</button>
      <a href="<?=base_url('employee/attendance')?>" class="btn btn-outline-secondary">ยกเลิก</a>
      <a href="<?=base_url('employee/attendance/delete/'.$r->id)?>"
         onclick="return confirm('ลบรายการนี้?')"
         class="btn btn-outline-danger ms-auto">
        <i class="bi bi-trash me-1"></i>ลบ
      </a>
    </div>
    <?=form_close()?>
  </div>
</div>
<script>
function toggleLeave(v){document.getElementById('leaveSection').style.display=v==='leave'?'':'none';}
function toggleHour(v){document.getElementById('hourSection').style.display=v==='hour'?'':'none';}
</script>

<script>
// init leave hour time widgets (runs after jQuery via layout)
$(document).ready(function(){
  function buildLTW(wrapId, hiddenId) {
    var $w=$("#"+wrapId), iv=$("#"+hiddenId).val()||"00:00";
    var p=iv.split(":"), ch=parseInt(p[0],10)||0, cm=parseInt(p[1],10)||0;
    var $selH = $("<select class=\"dt-hh\"></select>");
    for(var h=0;h<=23;h++){var hv=(h<10?"0":"")+h;var $o=$("<option>").val(hv).text(hv);if(h===ch)$o.prop("selected",true);$selH.append($o);}
    var $selM = $("<select class=\"dt-mm\"></select>");
    for(var m=0;m<=59;m++){var mv=(m<10?"0":"")+m;var $p=$("<option>").val(mv).text(mv);if(m===cm)$p.prop("selected",true);$selM.append($p);}
    $w.find(".dt-time-wrap").remove();
    var $t=$('<div class="dt-time-wrap" style="flex:1"></div>');
    $t.append('<select class="dt-hh">'+sh+'</select>');
    $t.append('<span class="dt-colon">:</span>');
    $t.append('<select class="dt-mm">'+sm+'</select>');
    $w.prepend($t);
    function s(){$("#"+hiddenId).val($w.find(".dt-hh").val()+":"+$w.find(".dt-mm").val()+":00");}
    $w.find(".dt-hh,.dt-mm").on("change",s); s();
  }
  buildLTW("lshWrap","lshHidden");
  buildLTW("lehWrap","lehHidden");
});
</script>