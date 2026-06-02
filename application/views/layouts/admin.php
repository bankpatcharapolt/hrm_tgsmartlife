<!DOCTYPE html>
<html lang="th"><head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1,viewport-fit=cover">
<title><?=$title??'ระบบ HRM'?></title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Sarabun:wght@300;400;500;600;700&display=swap" rel="stylesheet">
<style>
:root{--sb:240px;--pri:#1a56db;--pri2:#1e429f;--bg:#f0f4ff;--sb-bg:#0f172a;--bd:#e5e7eb;--tx:#111827;--mu:#6b7280;--dan:#dc2626;--suc:#16a34a;--r:12px;}
*{box-sizing:border-box;}body{font-family:Sarabun,sans-serif;background:var(--bg);margin:0;overflow-x:hidden;max-width:100vw;}
#sb{width:var(--sb);height:100vh;background:var(--sb-bg);position:fixed;top:0;left:0;z-index:1000;display:flex;flex-direction:column;transition:.3s;overflow:hidden;scrollbar-width:none;}
.sb-brand{padding:1.2rem 1.5rem;border-bottom:1px solid rgba(255,255,255,.08);display:flex;align-items:center;gap:.75rem;}
.sb-ico{width:38px;height:38px;background:var(--pri);border-radius:10px;display:flex;align-items:center;justify-content:center;font-size:1.1rem;}
.sb-nm{color:#fff;font-weight:700;font-size:.95rem;}.sb-sub{color:rgba(255,255,255,.4);font-size:.7rem;}
.sb-nav{flex:1;overflow-y:auto;overflow-x:hidden;padding:.5rem 0;min-height:0;scrollbar-width:thin;scrollbar-color:rgba(255,255,255,.1) transparent;}
.sb-sec{padding:.55rem 1.5rem .2rem;color:rgba(255,255,255,.28);font-size:.67rem;font-weight:600;letter-spacing:.08em;text-transform:uppercase;}
.sb-nav a{display:flex;align-items:center;gap:.6rem;padding:.5rem 1rem;color:rgba(255,255,255,.72);text-decoration:none;font-size:.82rem;border-left:3px solid transparent;transition:.15s;white-space:nowrap;overflow:hidden;}
.sb-nav a:hover{background:rgba(255,255,255,.06);color:#fff;}
.sb-nav a.on{background:rgba(26,86,219,.22);color:#fff;border-left-color:var(--pri);}
.sb-nav a i{width:18px;text-align:center;font-size:.95rem;}
.sb-user{padding:.75rem 1rem;border-top:1px solid rgba(255,255,255,.08);display:flex;align-items:center;gap:.6rem;flex-shrink:0;}
.sb-av{width:34px;height:34px;border-radius:50%;background:var(--pri);display:flex;align-items:center;justify-content:center;color:#fff;font-weight:700;font-size:.82rem;flex-shrink:0;}
.sb-user img{width:34px;height:34px;border-radius:50%;object-fit:cover;}
.sb-unm{color:#fff;font-size:.83rem;font-weight:600;}.sb-url{color:rgba(255,255,255,.42);font-size:.71rem;}
#main{margin-left:var(--sb);display:flex;flex-direction:column;min-height:100vh;min-width:0;max-width:calc(100vw - var(--sb));overflow-x:hidden;}
.topbar{background:#fff;border-bottom:1px solid var(--bd);padding:.65rem 1.5rem;display:flex;align-items:center;justify-content:space-between;position:sticky;top:0;z-index:100;box-shadow:0 1px 4px rgba(0,0,0,.05);}
.pg-title{font-size:1.02rem;font-weight:600;color:var(--tx);margin:0;}
.bico{width:36px;height:36px;border:none;background:#f3f4f6;border-radius:9px;display:flex;align-items:center;justify-content:center;color:var(--mu);cursor:pointer;position:relative;transition:.15s;}
.bico:hover{background:var(--bd);}
.nb{position:absolute;top:4px;right:4px;width:15px;height:15px;background:var(--dan);border-radius:50%;font-size:.58rem;color:#fff;display:flex;align-items:center;justify-content:center;}
.content{padding:1.5rem;flex:1;}
.card{background:#fff;border:1px solid var(--bd);border-radius:var(--r);box-shadow:0 1px 5px rgba(0,0,0,.04);}
.card-header{padding:.85rem 1.2rem;border-bottom:1px solid var(--bd);font-weight:600;background:transparent;font-size:.9rem;}
.card-body{padding:1.2rem;}
.stat-card{background:#fff;border:1px solid var(--bd);border-radius:var(--r);padding:1.15rem;display:flex;align-items:center;gap:1rem;}
.s-ico{width:46px;height:46px;border-radius:11px;display:flex;align-items:center;justify-content:center;font-size:1.25rem;flex-shrink:0;}
.s-lbl{font-size:.76rem;color:var(--mu);}.s-val{font-size:1.5rem;font-weight:700;color:var(--tx);line-height:1.1;}.s-sub{font-size:.72rem;color:var(--mu);}
.table{font-size:.865rem;}.table th{font-weight:600;color:var(--mu);font-size:.77rem;text-transform:uppercase;letter-spacing:.04em;background:#f9fafb;border-bottom:2px solid var(--bd);}.table td{vertical-align:middle;border-color:#f3f4f6;}
.badge{font-size:.69rem;font-weight:500;border-radius:5px;}
.alert{border:none;border-radius:var(--r);padding:.65rem 1rem;font-size:.875rem;}
.btn{font-family:Sarabun,sans-serif;font-size:.875rem;border-radius:8px;}
.btn-primary{background:var(--pri);border-color:var(--pri);}.btn-primary:hover{background:var(--pri2);border-color:var(--pri2);}
.form-control,.form-select{font-family:Sarabun,sans-serif;border-radius:8px;border-color:var(--bd);font-size:.875rem;}
.form-control:focus,.form-select:focus{border-color:var(--pri);box-shadow:0 0 0 3px rgba(26,86,219,.1);}
.form-label{font-size:.855rem;font-weight:500;margin-bottom:.3rem;}
.nd-drop{width:320px;max-height:380px;overflow-y:auto;}
.nd-item{padding:.6rem 1rem;border-bottom:1px solid #f3f4f6;cursor:pointer;transition:.15s;}
.nd-item:hover{background:#f9fafb;}.nd-item.unread{background:#eff6ff;}
.nd-title{font-size:.82rem;font-weight:600;margin-bottom:.15rem;}.nd-msg{font-size:.77rem;color:var(--mu);}.nd-time{font-size:.69rem;color:var(--mu);}
@media(max-width:768px){#sb{transform:translateX(-100%);} #main{margin-left:0;} #sb.open{transform:translateX(0);}}
.sb-ov{display:none;position:fixed;inset:0;background:rgba(0,0,0,.5);z-index:999;}
@media(max-width:768px){.sb-ov.show{display:block;}}
/* Tom Select override — ให้ดูเข้ากับ Bootstrap ของโปรเจค */
.ts-wrapper.form-select{padding:0;border:none;}
.ts-wrapper.form-control{padding:0;border:none;}
.ts-control{font-family:Sarabun,sans-serif;font-size:.875rem;border:1px solid var(--bd);border-radius:8px;padding:.375rem .75rem;min-height:38px;background:#fff;display:flex;align-items:center;flex-wrap:wrap;gap:3px;cursor:text;}
.ts-wrapper.is-focused .ts-control,.ts-wrapper.focus .ts-control{border-color:var(--pri);box-shadow:0 0 0 3px rgba(26,86,219,.1);outline:none;}
.ts-dropdown{font-family:Sarabun,sans-serif;font-size:.875rem;border:1px solid var(--bd);border-radius:8px;box-shadow:0 4px 16px rgba(0,0,0,.1);margin-top:2px;}
.ts-dropdown .option{padding:.45rem .85rem;}
.ts-dropdown .option.selected,.ts-dropdown .option:hover{background:rgba(26,86,219,.08);color:var(--pri);}
.ts-dropdown .option.active{background:var(--pri);color:#fff;}
.ts-dropdown-content{max-height:220px;}
</style></head><body>
<link href="https://cdn.jsdelivr.net/npm/tom-select@2.3.1/dist/css/tom-select.bootstrap5.min.css" rel="stylesheet">
<div class="sb-ov" id="ov" onclick="toggleSB()"></div>
<nav id="sb">
  <div class="sb-brand">
   
    <div><div class="sb-nm">ระบบ HRM</div><div class="sb-sub">Human Resource Mgmt</div></div>
  </div>
  <div class="sb-nav">
    <?php $ctrl=strtolower($this->router->fetch_class()); $seg3=$this->uri->segment(3); ?>
    <div class="sb-sec">หน้าหลัก</div>
    <a href="<?=base_url('admin/dashboard')?>" class="<?=$ctrl==='dashboard'?'on':''?>"><i class="bi bi-speedometer2"></i>แดชบอร์ด</a>
    <div class="sb-sec">พนักงาน</div>
    <a href="<?=base_url('admin/employees')?>" class="<?=($ctrl==='employees')?'on':''?>"><i class="bi bi-people-fill"></i>ข้อมูลพนักงาน</a>
    <a href="<?=base_url('admin/employees_import/import')?>" class="<?=$ctrl==='employees_import'?'on':''?>" style="padding-left:2.5rem;font-size:.82rem"><i class="bi bi-upload"></i>Import / Export Excel</a>
    <a href="<?=base_url('admin/attendance')?>" class="<?=($ctrl==='attendance'&&$seg3!=='shifts')?'on':''?>"><i class="bi bi-clock-fill"></i>การเข้างาน</a>
    <a href="<?=base_url('admin/attendance/shifts')?>" class="<?=($ctrl==='attendance'&&$seg3==='shifts')?'on':''?>" style="padding-left:2.5rem;font-size:.82rem"><i class="bi bi-diagram-3"></i>จัดการกะ</a>
    <a href="<?=base_url('admin/leave')?>" class="<?=$ctrl==='leave'?'on':''?>"><i class="bi bi-calendar-check-fill"></i>การลา</a>
    <div class="sb-sec">การเงิน</div>
    <a href="<?=base_url('admin/salary')?>" class="<?=($ctrl==='salary'&&$seg3!=='bonus'&&$seg3!=='tax_docs'&&$seg3!=='slips')?'on':''?>"><i class="bi bi-currency-dollar"></i>เงินเดือน</a>
    <a href="<?=base_url('admin/salary/slips')?>" class="<?=($ctrl==='salary'&&$seg3==='slips')?'on':''?>" style="padding-left:2rem;font-size:.8rem"><i class="bi bi-file-earmark-pdf text-danger"></i>รายการสลิป</a>
    <a href="<?=base_url('admin/salary/bonus')?>" class="<?=($ctrl==='salary'&&$seg3==='bonus')?'on':''?>"><i class="bi bi-gift-fill"></i>โบนัสประจำปี</a>
    <a href="<?=base_url('admin/salary/tax_docs')?>" class="<?=($ctrl==='salary'&&$seg3==='tax_docs')?'on':''?>"><i class="bi bi-file-earmark-text-fill"></i>ทวิ 50</a>
    <div class="sb-sec">รายงาน</div>
    <a href="<?=base_url('admin/sales')?>" class="<?=$ctrl==='sales'?'on':''?>"><i class="bi bi-graph-up-arrow"></i>ยอดขาย</a>
    <div class="sb-sec">ระบบ</div>
    <a href="<?=base_url('admin/notifications')?>" class="<?=$ctrl==='notifications'?'on':''?>"><i class="bi bi-bell-fill"></i>ส่งการแจ้งเตือน</a>
    <a href="<?=base_url('admin/teams')?>" class="<?=$ctrl==='teams'?'on':''?>"><i class="bi bi-diagram-3"></i>จัดการทีม</a>
    <?php if(!empty($current_user->is_full_access) || !empty($current_user->can_manage_employees)):?>
    <a href="<?=base_url('admin/roles')?>" class="<?=$ctrl==='roles'?'on':''?>"><i class="bi bi-shield-check"></i>บทบาทและสิทธิ์</a>
    <?php endif;?>
    <a href="<?=base_url('employee/profile')?>"><i class="bi bi-person-circle"></i>โปรไฟล์ของฉัน</a>
    <a href="<?=base_url('auth/logout')?>" onclick="return confirm('ออกจากระบบ?')"><i class="bi bi-box-arrow-right"></i>ออกจากระบบ</a>
  </div>
  <div class="sb-user">
    <?php if(!empty($current_user->photo)):?><img src="<?=base_url($current_user->photo)?>"><?php else:?><div class="sb-av"><?=mb_substr($current_user->first_name??'A',0,1)?></div><?php endif;?>
    <div><div class="sb-unm"><?=$current_user->full_name??''?></div><div class="sb-url"><?=$current_user->role_name??''?></div></div>
  </div>
</nav>
<div id="main">
  <div class="topbar">
    <div class="d-flex align-items-center gap-2">
      <button class="bico d-lg-none" onclick="toggleSB()"><i class="bi bi-list fs-5"></i></button>
      <h1 class="pg-title"><?=$page_title??''?></h1>
    </div>
    <div class="d-flex align-items-center gap-2">
      <div class="dropdown">
        <button class="bico" data-bs-toggle="dropdown">
          <i class="bi bi-bell fs-5"></i>
          <?php if(!empty($unread_notifications)):?><span class="nb"><?=$unread_notifications?></span><?php endif;?>
        </button>
        <div class="dropdown-menu dropdown-menu-end nd-drop p-0 shadow">
          <div class="d-flex justify-content-between align-items-center px-3 py-2 border-bottom"><strong style="font-size:.83rem">การแจ้งเตือน</strong><a href="<?=base_url('employee/notifications')?>" class="text-primary text-decoration-none" style="font-size:.77rem">ดูทั้งหมด</a></div>
          <?php if(!empty($recent_notifications)):foreach($recent_notifications as $n):?>
          <div class="nd-item <?=!$n->is_read?'unread':''?>" onclick="markRead(<?=$n->id?>,'<?=addslashes($n->link??'')?>')">
            <div class="nd-title"><?=htmlspecialchars($n->title)?></div>
            <div class="nd-msg"><?=htmlspecialchars(mb_substr($n->message,0,55))?><?=mb_strlen($n->message)>55?'...':''?></div>
            <div class="nd-time"><?=date('d/m H:i',strtotime($n->created_at))?></div>
          </div>
          <?php endforeach;else:?><div class="text-center text-muted py-4 small">ไม่มีการแจ้งเตือน</div><?php endif;?>
        </div>
      </div>
      <div class="dropdown">
        <button class="bico" data-bs-toggle="dropdown">
          <?php if(!empty($current_user->photo)):?><img src="<?=base_url($current_user->photo)?>" style="width:24px;height:24px;border-radius:50%;object-fit:cover;"><?php else:?><i class="bi bi-person-circle fs-5"></i><?php endif;?>
        </button>
        <ul class="dropdown-menu dropdown-menu-end shadow">
          <li class="px-3 py-2 border-bottom"><small class="text-muted"><?=$current_user->role_name??''?></small></li>
          <li><a class="dropdown-item" href="<?=base_url('employee/profile')?>"><i class="bi bi-person me-2"></i>โปรไฟล์</a></li>
          <li><hr class="dropdown-divider"></li>
          <li><a class="dropdown-item text-danger" href="<?=base_url('auth/logout')?>" onclick="return confirm('ออกจากระบบ?')"><i class="bi bi-box-arrow-right me-2"></i>ออกจากระบบ</a></li>
        </ul>
      </div>
    </div>
  </div>
  <div class="px-4 pt-3">
    <?php foreach(array('success'=>'check-circle','error'=>'exclamation-circle','warning'=>'exclamation-triangle','info'=>'info-circle') as $t=>$ic):if(!empty(${'flash_'.$t}??'')):?>
    <div class="alert alert-<?=$t==='error'?'danger':$t?> alert-dismissible fade show">
      <i class="bi bi-<?=$ic?> me-2"></i><?=${'flash_'.$t}?>
      <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    <?php endif;endforeach;?>
  </div>
  <div class="content"><?=$content_view??''?></div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.4/dist/chart.umd.min.js"></script>
<script>
function toggleSB(){document.getElementById('sb').classList.toggle('open');document.getElementById('ov').classList.toggle('show');}
function markRead(id,link){fetch('<?=base_url('api/notifications/mark_read/')?>'+id,{method:'POST',headers:{'X-Requested-With':'XMLHttpRequest'}});if(link)window.location.href=link;}
setTimeout(()=>{document.querySelectorAll('.alert').forEach(a=>bootstrap.Alert.getOrCreateInstance(a).close());},4000);
</script>
<script src="https://cdn.jsdelivr.net/npm/tom-select@2.3.1/dist/js/tom-select.complete.min.js"></script>
<script>
// ── Tom Select: เริ่มต้นอัตโนมัติทุก select ที่มี class "ts-select" ──
// ใน view แต่ละหน้าแค่เพิ่ม class="form-select ts-select" ก็พอ
// หรือจะใช้ data attribute: data-ts="true"
document.addEventListener('DOMContentLoaded', function () {
  initTomSelects(document);
});
// รองรับ Modal (Bootstrap modal โหลด DOM ใหม่หลัง show)
document.addEventListener('shown.bs.modal', function (e) {
  initTomSelects(e.target);
});
function initTomSelects(root) {
  root.querySelectorAll('select.ts-select:not(.tomselected)').forEach(function (el) {
    var placeholder = el.getAttribute('data-placeholder') || '-- เลือก / พิมพ์ค้นหา --';
    new TomSelect(el, {
      placeholder      : placeholder,
      allowEmptyOption : true,
      maxOptions       : 300,
      highlight        : true,
      render: {
        no_results: function () {
          return '<div class="no-results" style="padding:.5rem .85rem;color:#6b7280;font-size:.83rem">ไม่พบรายการที่ค้นหา</div>';
        }
      }
    });
  });
}
</script>
<?php if(!empty($extra_js))echo $extra_js;?>
</body></html>
