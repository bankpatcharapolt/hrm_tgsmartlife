<?php defined('BASEPATH') OR exit(); ?>
<div class="d-flex gap-2 mb-3 flex-wrap align-items-center">
  <select class="form-select form-select-sm" style="width:auto" onchange="goF(this,'year')">
    <?php for($y=date('Y');$y>=date('Y')-2;$y--):?>
    <option value="<?=$y?>" <?=$year==$y?'selected':''?>><?=$y?></option>
    <?php endfor;?>
  </select>
  <select class="form-select form-select-sm" style="width:auto" onchange="goF(this,'month')">
    <?php $mn=array('1'=>'ม.ค.','2'=>'ก.พ.','3'=>'มี.ค.','4'=>'เม.ย.','5'=>'พ.ค.','6'=>'มิ.ย.','7'=>'ก.ค.','8'=>'ส.ค.','9'=>'ก.ย.','10'=>'ต.ค.','11'=>'พ.ย.','12'=>'ธ.ค.');
    foreach($mn as $k=>$v):?>
    <option value="<?=$k?>" <?=$month==$k?'selected':''?>><?=$v?></option>
    <?php endforeach;?>
  </select>
  <div class="ms-auto">
    <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addModal">
      <i class="bi bi-plus-lg me-1"></i>บันทึกด้วยตนเอง
    </button>
  </div>
</div>

<!-- สรุปเดือน -->
<div class="row g-2 mb-3">
  <div class="col-6 col-md-3"><div class="stat-card"><div class="s-ico" style="background:#f0fdf4;color:#16a34a"><i class="bi bi-check-circle"></i></div><div><div class="s-lbl">มาทำงาน</div><div class="s-val text-success"><?=$summary['present']?></div><div class="s-sub">วัน</div></div></div></div>
  <div class="col-6 col-md-3"><div class="stat-card"><div class="s-ico" style="background:#fffbeb;color:#d97706"><i class="bi bi-clock-history"></i></div><div><div class="s-lbl">มาสาย</div><div class="s-val text-warning"><?=$summary['late']?></div><div class="s-sub"><?=$summary['total_late_min']?> นาที</div></div></div></div>
  <div class="col-6 col-md-3"><div class="stat-card"><div class="s-ico" style="background:#eff6ff;color:#1a56db"><i class="bi bi-calendar-check"></i></div><div><div class="s-lbl">ลา</div><div class="s-val"><?=$summary['leave']?></div><div class="s-sub">วัน</div></div></div></div>
  <div class="col-6 col-md-3"><div class="stat-card"><div class="s-ico" style="background:#f0fdf4;color:#059669"><i class="bi bi-lightning-charge"></i></div><div><div class="s-lbl">OT</div><div class="s-val"><?=number_format($summary['total_ot'],1)?></div><div class="s-sub">ชม.</div></div></div></div>
</div>

<div class="card">
  <div class="card-body p-0">
    <div class="table-responsive">
      <table class="table table-hover mb-0">
        <thead>
          <tr><th>วันที่</th><th>กะ</th><th>เข้างาน</th><th>ออกงาน</th><th>สถานะ</th><th>สาย</th><th>ลา (ชม.)</th><th>OT</th><th>หมายเหตุ</th><th>จัดการ</th></tr>
        </thead>
        <tbody>
          <?php if(!empty($records)):foreach($records as $r):?>
          <tr>
            <td style="font-size:.83rem;white-space:nowrap"><?=date('d/m/Y',strtotime($r->date))?><br><small class="text-muted"><?=date('D',strtotime($r->date))?></small></td>
            <td>
              <?php if(!empty($r->shift_name)):?>
              <span class="badge" style="background:<?=$r->shift_color??'#6b7280'?>;font-size:.67rem"><?=$r->shift_name?></span>
              <?php else:?><span class="text-muted small">–</span><?php endif;?>
            </td>
            <td class="<?=$r->is_late?'text-danger fw-semibold':''?>" style="font-size:.83rem">
              <?=$r->check_in_time ? date('H:i',strtotime($r->check_in_time)) : '–'?>
            </td>
            <td style="font-size:.83rem">
              <?=$r->check_out_time ? date('H:i',strtotime($r->check_out_time)) : '–'?>
            </td>
            <td>
              <?php
              $sc=array('present'=>'success','absent'=>'danger','leave'=>'info text-dark','holiday'=>'warning text-dark','half_day'=>'secondary');
              $sl=array('present'=>'มา','absent'=>'ขาด','leave'=>'ลา','holiday'=>'วันหยุด','half_day'=>'ครึ่งวัน');
              ?>
              <span class="badge bg-<?=$sc[$r->status]??'secondary'?>"><?=$sl[$r->status]??$r->status?></span>
              <?php if($r->is_late):?><br><small class="text-danger">สาย <?=$r->late_minutes?> น.</small><?php endif;?>
            </td>
            <td class="small <?=$r->late_minutes>0?'text-danger':''?>"><?=$r->late_minutes>0?$r->late_minutes.' น.':'–'?></td>
            <td class="small">
              <?php if($r->status==='leave'&&$r->leave_hours>0):?>
              <span class="badge bg-info text-dark"><?=$r->leave_hours?> ชม.</span>
              <?php else:?>–<?php endif;?>
            </td>
            <td class="small"><?=$r->ot_hours>0?number_format($r->ot_hours,1).' ชม.':'–'?></td>
            <td style="font-size:.8rem;max-width:120px"><?=htmlspecialchars($r->note??'')?></td>
            <td>
              <a href="<?=base_url('employee/attendance/edit/'.$r->id)?>"
                 class="btn btn-outline-secondary btn-sm px-2 py-0" title="แก้ไข">
                <i class="bi bi-pencil"></i>
              </a>
              <a href="<?=base_url('employee/attendance/delete/'.$r->id)?>"
                 onclick="return confirm('ลบรายการวันที่ <?=date('d/m/Y',strtotime($r->date))?> ใช่ไหม?')"
                 class="btn btn-outline-danger btn-sm px-2 py-0 ms-1" title="ลบ">
                <i class="bi bi-trash"></i>
              </a>
            </td>
          </tr>
          <?php endforeach;else:?>
          <tr><td colspan="10" class="text-center text-muted py-5">
            <i class="bi bi-clock fs-1 d-block mb-2"></i>ไม่มีข้อมูลการเข้างาน
          </td></tr>
          <?php endif;?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<!-- Modal: บันทึกด้วยตนเอง -->
<div class="modal fade" id="addModal" tabindex="-1">
  <div class="modal-dialog"><div class="modal-content">
    <div class="modal-header"><h5 class="modal-title"><i class="bi bi-plus-circle me-2"></i>บันทึกการเข้างาน</h5><button class="btn-close" data-bs-dismiss="modal"></button></div>
    <?=form_open('employee/attendance/add')?>
    <input type="hidden" name="<?=$this->security->get_csrf_token_name()?>" value="<?=$this->security->get_csrf_hash()?>">
    <div class="modal-body">
      <div class="row g-3">
        <div class="col-6">
          <label class="form-label small">วันที่ *</label>
          <input type="date" name="date" class="form-control form-control-sm" value="<?=date('Y-m-d')?>" required>
        </div>
        <div class="col-6">
          <label class="form-label small">กะการทำงาน</label>
          <select name="shift_id" class="form-select form-select-sm">
            <option value="">– ไม่ระบุ –</option>
            <?php foreach($shifts as $s):?>
            <option value="<?=$s->id?>"><?=$s->name?> (<?=substr($s->start_time,0,5)?>–<?=substr($s->end_time,0,5)?>)</option>
            <?php endforeach;?>
          </select>
        </div>
        <div class="col-6">
          <label class="form-label small">เวลาเข้างาน</label>
          <input type="datetime-local" name="check_in" class="form-control form-control-sm">
        </div>
        <div class="col-6">
          <label class="form-label small">เวลาออกงาน</label>
          <input type="datetime-local" name="check_out" class="form-control form-control-sm">
        </div>
        <div class="col-6">
          <label class="form-label small">สถานะ</label>
          <select name="status" class="form-select form-select-sm" onchange="toggleLeave(this.value,'addLeave')">
            <option value="present">มาทำงาน</option>
            <option value="absent">ขาดงาน</option>
            <option value="leave">ลา</option>
            <option value="holiday">วันหยุด</option>
            <option value="half_day">ครึ่งวัน</option>
          </select>
        </div>
        <div class="col-6">
          <label class="form-label small">หมายเหตุ</label>
          <input type="text" name="note" class="form-control form-control-sm">
        </div>
        <!-- Leave section -->
        <div class="col-12" id="addLeave" style="display:none">
          <div class="p-2 rounded" style="background:#eff6ff;border:1px solid #bae6fd">
            <div class="row g-2">
              <div class="col-5">
                <label class="form-label small">ประเภทการลา</label>
                <select name="leave_type_id" class="form-select form-select-sm">
                  <option value="">– เลือก –</option>
                  <?php foreach($leave_types as $lt):?>
                  <option value="<?=$lt->id?>"><?=$lt->name?></option>
                  <?php endforeach;?>
                </select>
              </div>
              <div class="col-4">
                <label class="form-label small">หน่วย</label>
                <select name="leave_unit" class="form-select form-select-sm" onchange="toggleHour(this.value,'addHour')">
                  <option value="day">ลาเต็มวัน</option>
                  <option value="hour">ลาชั่วโมง</option>
                </select>
              </div>
              <div class="col-12" id="addHour" style="display:none">
                <div class="row g-1">
                  <div class="col-5"><label class="form-label small">เวลาเริ่ม</label><input type="time" name="leave_start_hour" class="form-control form-control-sm"></div>
                  <div class="col-2 d-flex align-items-end pb-1 justify-content-center"><span class="text-muted">–</span></div>
                  <div class="col-5"><label class="form-label small">เวลาสิ้นสุด</label><input type="time" name="leave_end_hour" class="form-control form-control-sm"></div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
    <div class="modal-footer py-2">
      <button type="submit" class="btn btn-primary btn-sm"><i class="bi bi-save me-1"></i>บันทึก</button>
      <button class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">ยกเลิก</button>
    </div>
    <?=form_close()?>
  </div></div>
</div>

<?php $extra_js='<script>
function goF(el,k){var url=new URL(window.location);url.searchParams.set(k,el.value);window.location=url;}
function toggleLeave(v,sec){document.getElementById(sec).style.display=v==="leave"?"":"none";}
function toggleHour(v,sec){document.getElementById(sec).style.display=v==="hour"?"":"none";}
</script>';?>
