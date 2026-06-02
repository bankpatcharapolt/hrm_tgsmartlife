<?php defined('BASEPATH') OR exit(); ?>
<div class="row g-3">
  <!-- Form เพิ่ม/แก้ไข -->
  <div class="col-lg-5">
    <div class="card">
      <div class="card-header" id="formTitle"><i class="bi bi-plus-circle me-2"></i>เพิ่มทีม/สาขาใหม่</div>
      <div class="card-body">
        <?=form_open('admin/teams/store')?>
        <input type="hidden" name="<?=$this->security->get_csrf_token_name()?>" value="<?=$this->security->get_csrf_hash()?>">
        <input type="hidden" name="team_id" id="teamId" value="">
        <div class="row g-3">
          <div class="col-md-4">
            <label class="form-label">รหัสทีม <span class="text-danger">*</span></label>
            <input type="text" name="team_code" id="tCode" class="form-control text-uppercase" required placeholder="HQ, BKK, RYG">
            <div class="form-text">ตัวอักษรพิมพ์ใหญ่ ไม่ซ้ำ</div>
          </div>
          <div class="col-md-8">
            <label class="form-label">ชื่อทีม/สาขา <span class="text-danger">*</span></label>
            <input type="text" name="team_name" id="tName" class="form-control" required placeholder="เช่น สำนักงานใหญ่ สาขาระยอง">
          </div>
          <div class="col-md-6">
            <label class="form-label">พื้นที่/จังหวัด</label>
            <input type="text" name="location" id="tLoc" class="form-control" placeholder="กรุงเทพมหานคร">
          </div>
          <div class="col-md-6">
            <label class="form-label">รหัสพนักงานหัวหน้า</label>
            <input type="text" name="manager_emp_id" id="tMgr" class="form-control" placeholder="SL001">
          </div>
          <div class="col-md-8">
            <label class="form-label">เป้ายอดขายต่อเดือน (฿)</label>
            <div class="input-group"><span class="input-group-text">฿</span><input type="number" name="monthly_target" id="tTarget" class="form-control" min="0" step="1000" value="0"></div>
          </div>
          <div class="col-md-4 d-flex align-items-end pb-1">
            <div class="form-check form-switch">
              <input class="form-check-input" type="checkbox" name="is_active" id="tActive" value="1" checked>
              <label class="form-check-label small" for="tActive">ใช้งาน</label>
            </div>
          </div>
        </div>
        <div class="mt-3 d-flex gap-2">
          <button type="submit" class="btn btn-primary" id="tBtn"><i class="bi bi-save me-1"></i>บันทึก</button>
          <button type="button" class="btn btn-outline-secondary" onclick="resetForm()">ล้าง</button>
        </div>
        <?=form_close()?>
      </div>
    </div>
  </div>

  <!-- รายการทีม -->
  <div class="col-lg-7">
    <div class="card">
      <div class="card-header"><i class="bi bi-diagram-3 me-2"></i>ทีมทั้งหมด <span class="badge bg-secondary"><?=count($teams)?></span></div>
      <div class="card-body p-0">
        <div class="table-responsive">
          <table class="table table-hover mb-0">
            <thead><tr><th>รหัส</th><th>ชื่อทีม</th><th>พื้นที่</th><th>สมาชิก</th><th>เป้า/เดือน</th><th>สถานะ</th><th>จัดการ</th></tr></thead>
            <tbody>
              <?php if(!empty($teams)):foreach($teams as $t):?>
              <tr>
                <td><code><?=$t->team_code?></code></td>
                <td>
                  <div class="fw-semibold" style="font-size:.875rem"><?=$t->team_name?></div>
                  <?php if($t->manager_emp_id):?><div style="font-size:.72rem;color:#6b7280">หัวหน้า: <?=$t->manager_emp_id?></div><?php endif;?>
                </td>
                <td style="font-size:.83rem"><?=$t->location??'–'?></td>
                <td>
                  <span class="badge bg-info text-dark"><?=$t->member_count?> คน</span>
                </td>
                <td style="font-size:.83rem">฿<?=number_format($t->monthly_target,0)?></td>
                <td><span class="badge bg-<?=$t->is_active?'success':'secondary'?>"><?=$t->is_active?'ใช้งาน':'ปิด'?></span></td>
                <td>
                  <button class="btn btn-outline-secondary btn-sm px-2 py-0"
                    onclick="editTeam(<?=$t->id?>,'<?=addslashes($t->team_code)?>','<?=addslashes($t->team_name)?>','<?=addslashes($t->location??'')?>','<?=addslashes($t->manager_emp_id??'')?>', <?=(float)$t->monthly_target?>,<?=$t->is_active?>)"
                    title="แก้ไข"><i class="bi bi-pencil"></i></button>
                  <?php if($t->member_count==0 && $t->team_code!=='HQ'):?>
                  <a href="<?=base_url('admin/teams/delete/'.$t->id)?>"
                     onclick="return confirm('ลบทีม <?=addslashes($t->team_name)?>?')"
                     class="btn btn-outline-danger btn-sm px-2 py-0 ms-1" title="ลบ">
                    <i class="bi bi-trash"></i>
                  </a>
                  <?php else:?>
                  <button class="btn btn-outline-secondary btn-sm px-2 py-0 ms-1" disabled title="<?=$t->team_code==='HQ'?'ทีมหลักลบไม่ได้':'มีสมาชิกอยู่'?>">
                    <i class="bi bi-trash"></i>
                  </button>
                  <?php endif;?>
                </td>
              </tr>
              <?php endforeach;else:?>
              <tr><td colspan="7" class="text-center text-muted py-4">ยังไม่มีทีม</td></tr>
              <?php endif;?>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</div>

<script>
function editTeam(id, code, name, loc, mgr, target, active) {
  document.getElementById('teamId').value = id;
  document.getElementById('tCode').value = code;
  document.getElementById('tCode').readOnly = true;  // ไม่เปลี่ยน code
  document.getElementById('tName').value = name;
  document.getElementById('tLoc').value = loc;
  document.getElementById('tMgr').value = mgr;
  document.getElementById('tTarget').value = target;
  document.getElementById('tActive').checked = active == 1;
  document.getElementById('formTitle').innerHTML = '<i class="bi bi-pencil me-2"></i>แก้ไขทีม: ' + name;
  document.getElementById('tBtn').innerHTML = '<i class="bi bi-save me-1"></i>อัปเดต';
  window.scrollTo({top: 0, behavior: 'smooth'});
}
function resetForm() {
  document.getElementById('teamId').value = '';
  document.getElementById('tCode').readOnly = false;
  document.getElementById('tCode').value = '';
  document.getElementById('tName').value = '';
  document.getElementById('tLoc').value = '';
  document.getElementById('tMgr').value = '';
  document.getElementById('tTarget').value = '0';
  document.getElementById('tActive').checked = true;
  document.getElementById('formTitle').innerHTML = '<i class="bi bi-plus-circle me-2"></i>เพิ่มทีม/สาขาใหม่';
  document.getElementById('tBtn').innerHTML = '<i class="bi bi-save me-1"></i>บันทึก';
}
// Auto uppercase team_code
document.getElementById('tCode').addEventListener('input', function(){
  this.value = this.value.toUpperCase();
});
</script>
