<?php defined('BASEPATH') OR exit(); ?>
<div class="card" style="max-width:720px">
  <div class="card-header"><i class="bi bi-bell me-2"></i><?=$page_title?></div>
  <div class="card-body">
    <?=form_open('admin/notifications/send')?>
    <input type="hidden" name="<?=$this->security->get_csrf_token_name()?>" value="<?=$this->security->get_csrf_hash()?>">
    <div class="mb-3"><label class="form-label">ส่งถึง</label>
      <select name="target_type" class="form-select" id="ttype" onchange="toggleTarget(this.value)">
        <option value="all">ทุกคนในระบบ</option>
        <option value="role">เฉพาะบทบาท</option>
        <option value="individual">เฉพาะบุคคล</option>
      </select>
    </div>
    <div id="t_role" class="mb-3" style="display:none">
      <label class="form-label">บทบาท</label>
      <select name="role_slug" class="form-select"><option value="">-- เลือก --</option><?php foreach($roles as $r):?><option value="<?=$r->slug?>"><?=$r->name?></option><?php endforeach;?></select>
    </div>
    <div id="t_ind" class="mb-3" style="display:none">
      <label class="form-label">พนักงาน</label>
      <select name="user_id" class="form-select"><option value="">-- เลือก --</option><?php foreach($employees as $e):?><option value="<?=$e->id?>"><?=$e->employee_id?> – <?=$e->first_name.' '.$e->last_name?></option><?php endforeach;?></select>
    </div>
    <div class="mb-3"><label class="form-label">ประเภทการแจ้งเตือน</label>
      <select name="notif_type" class="form-select">
        <option value="general">ทั่วไป</option><option value="meeting">การประชุม</option><option value="holiday">วันหยุด</option><option value="target">เป้าหมาย</option>
      </select>
    </div>
    <div class="mb-3"><label class="form-label">หัวข้อ *</label><input type="text" name="title" class="form-control" required placeholder="หัวข้อการแจ้งเตือน"></div>
    <div class="mb-3"><label class="form-label">ข้อความ *</label><textarea name="message" class="form-control" rows="4" required placeholder="รายละเอียดการแจ้งเตือน"></textarea></div>
    <div class="mb-3"><label class="form-label">ลิงก์ (ถ้ามี)</label><input type="text" name="link" class="form-control" placeholder="https://..."></div>
    <button type="submit" class="btn btn-primary"><i class="bi bi-send me-1"></i>ส่งการแจ้งเตือน</button>
    <?=form_close()?>
  </div>
</div>
<?php $extra_js='<script>
function toggleTarget(v){
  document.getElementById("t_role").style.display=v==="role"?"":"none";
  document.getElementById("t_ind").style.display=v==="individual"?"":"none";
}
</script>';?>
