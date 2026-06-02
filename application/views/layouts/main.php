<!DOCTYPE html>
<html lang="th"><head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title><?=$title??'ระบบ HRM'?></title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Sarabun:wght@300;400;500;600;700&display=swap" rel="stylesheet">
<style>
:root{--pri:#1a56db;--pri2:#1e429f;--bg:#f0f4ff;--bd:#e5e7eb;--tx:#111827;--mu:#6b7280;--r:12px;}
*{box-sizing:border-box;}body{font-family:Sarabun,sans-serif;background:var(--bg);margin:0;}
.navbar{background:#0f172a;padding:.5rem 1.5rem;}
.navbar-brand{color:#fff!important;font-weight:700;font-size:1rem;display:flex;align-items:center;gap:.5rem;}
.b-ico{width:30px;height:30px;background:var(--pri);border-radius:7px;display:flex;align-items:center;justify-content:center;font-size:.8rem;}
.nav-link{color:rgba(255,255,255,.72)!important;font-size:.865rem;padding:.38rem .7rem!important;border-radius:7px;transition:.15s;}
.nav-link:hover,.nav-link.on{color:#fff!important;background:rgba(255,255,255,.1);}
.pw{min-height:calc(100vh - 56px);padding:1.5rem;}
.card{background:#fff;border:1px solid var(--bd);border-radius:var(--r);box-shadow:0 1px 5px rgba(0,0,0,.04);}
.card-header{padding:.85rem 1.2rem;border-bottom:1px solid var(--bd);font-weight:600;background:transparent;font-size:.9rem;}
.card-body{padding:1.2rem;}
.table{font-size:.865rem;}.table th{font-weight:600;color:var(--mu);font-size:.77rem;background:#f9fafb;border-bottom:2px solid var(--bd);}.table td{vertical-align:middle;border-color:#f3f4f6;}
.badge{font-size:.69rem;font-weight:500;border-radius:5px;}
.alert{border:none;border-radius:var(--r);}
.btn{font-family:Sarabun,sans-serif;font-size:.875rem;border-radius:8px;}
.btn-primary{background:var(--pri);border-color:var(--pri);}
.form-control,.form-select{font-family:Sarabun,sans-serif;border-radius:8px;border-color:var(--bd);font-size:.875rem;}
.form-control:focus,.form-select:focus{border-color:var(--pri);box-shadow:0 0 0 3px rgba(26,86,219,.1);}
.form-label{font-size:.855rem;font-weight:500;margin-bottom:.3rem;}
.ci-card{background:linear-gradient(135deg,var(--pri),var(--pri2));color:#fff;border-radius:var(--r);padding:1.5rem;}
.ci-time{font-size:2.6rem;font-weight:700;font-variant-numeric:tabular-nums;}
</style></head><body>
<nav class="navbar navbar-expand-lg">
  <div class="container-fluid">
    <a class="navbar-brand" href="<?=base_url('employee/dashboard')?>"><div class="b-ico"></div>ระบบ HRM</a>
    <button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse" data-bs-target="#nm" style="color:rgba(255,255,255,.7)"><i class="bi bi-list fs-4"></i></button>
    <div class="collapse navbar-collapse" id="nm">
      <ul class="navbar-nav me-auto">
        <?php $c=strtolower($this->router->fetch_class()); ?>
        <li class="nav-item"><a class="nav-link <?=$c==='dashboard'?'on':''?>" href="<?=base_url('employee/dashboard')?>"><i class="bi bi-house me-1"></i>หน้าหลัก</a></li>
        <?php if(!empty($current_user->can_checkin)):?><li class="nav-item"><a class="nav-link <?=$c==='attendance'?'on':''?>" href="<?=base_url('employee/attendance')?>"><i class="bi bi-clock me-1"></i>การเข้างาน</a></li><?php endif;?>
        <li class="nav-item"><a class="nav-link <?=$c==='leave'?'on':''?>" href="<?=base_url('employee/leave')?>"><i class="bi bi-calendar-check me-1"></i>การลา</a></li>
        <?php if(!empty($current_user->can_view_own_salary)):?><li class="nav-item"><a class="nav-link <?=$c==='salary'?'on':''?>" href="<?=base_url('employee/salary')?>"><i class="bi bi-cash me-1"></i>เงินเดือน</a></li><?php endif;?>
        <?php if(!empty($current_user->can_approve_leave)):?><li class="nav-item"><a class="nav-link" href="<?=base_url('manager/leave')?>"><i class="bi bi-check2-circle me-1"></i>อนุมัติการลา</a></li><?php endif;?>
        <?php if(!empty($current_user->can_manage_employees)):?><li class="nav-item"><a class="nav-link" href="<?=base_url('admin/dashboard')?>"><i class="bi bi-gear me-1"></i>จัดการระบบ</a></li><?php endif;?>
      </ul>
      <ul class="navbar-nav ms-auto align-items-center">
        <li class="nav-item dropdown">
          <a class="nav-link position-relative" href="#" data-bs-toggle="dropdown"><i class="bi bi-bell-fill fs-5"></i><?php if(!empty($unread_notifications)):?><span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" style="font-size:.58rem"><?=$unread_notifications?></span><?php endif;?></a>
          <div class="dropdown-menu dropdown-menu-end shadow p-0" style="width:305px;max-height:370px;overflow-y:auto;">
            <div class="d-flex justify-content-between px-3 py-2 border-bottom"><strong style="font-size:.83rem">การแจ้งเตือน</strong><a href="<?=base_url('employee/notifications')?>" class="text-primary text-decoration-none" style="font-size:.77rem">ดูทั้งหมด</a></div>
            <?php if(!empty($recent_notifications)):foreach($recent_notifications as $n):?><a href="<?=$n->link??'#'?>" class="dropdown-item py-2 border-bottom <?=!$n->is_read?'bg-light':''?>" style="white-space:normal"><div style="font-size:.82rem;font-weight:<?=!$n->is_read?600:400?>"><?=htmlspecialchars($n->title)?></div><div style="font-size:.76rem;color:var(--mu)"><?=htmlspecialchars(mb_substr($n->message,0,55))?><?=mb_strlen($n->message)>55?'...':''?></div><div style="font-size:.69rem;color:var(--mu)"><?=date('d/m H:i',strtotime($n->created_at))?></div></a><?php endforeach;else:?><div class="text-center text-muted py-4 small">ไม่มีการแจ้งเตือน</div><?php endif;?>
          </div>
        </li>
        <li class="nav-item dropdown ms-1">
          <a class="nav-link d-flex align-items-center gap-2" href="#" data-bs-toggle="dropdown">
            <?php if(!empty($current_user->photo)):?><img src="<?=base_url($current_user->photo)?>" style="width:28px;height:28px;border-radius:50%;object-fit:cover;"><?php else:?><div style="width:28px;height:28px;background:var(--pri);border-radius:50%;display:flex;align-items:center;justify-content:center;color:#fff;font-weight:700;font-size:.8rem"><?=mb_substr($current_user->first_name??'A',0,1)?></div><?php endif;?>
            <span style="color:rgba(255,255,255,.72);font-size:.865rem"><?=$current_user->first_name??''?></span>
          </a>
          <ul class="dropdown-menu dropdown-menu-end shadow">
            <li class="px-3 py-2 border-bottom"><small class="text-muted"><?=$current_user->role_name??''?></small></li>
            <li><a class="dropdown-item" href="<?=base_url('employee/profile')?>"><i class="bi bi-person me-2"></i>โปรไฟล์</a></li>
            <li><hr class="dropdown-divider"></li>
            <li><a class="dropdown-item text-danger" href="<?=base_url('auth/logout')?>" onclick="return confirm('ออกจากระบบ?')"><i class="bi bi-box-arrow-right me-2"></i>ออกจากระบบ</a></li>
          </ul>
        </li>
      </ul>
    </div>
  </div>
</nav>
<div class="pw">
  <?php foreach(array('success'=>'check-circle','error'=>'exclamation-circle','warning'=>'exclamation-triangle','info'=>'info-circle') as $t=>$ic):if(!empty(${'flash_'.$t}??'')):?>
  <div class="alert alert-<?=$t==='error'?'danger':$t?> alert-dismissible fade show mb-3">
    <i class="bi bi-<?=$ic?> me-2"></i><?=${'flash_'.$t}?>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
  </div>
  <?php endif;endforeach;?>
  <?=$content_view??''?>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.4/dist/chart.umd.min.js"></script>
<script>setTimeout(()=>{document.querySelectorAll('.alert').forEach(a=>bootstrap.Alert.getOrCreateInstance(a).close());},4000);</script>
<?php if(!empty($extra_js))echo $extra_js;?>
</body></html>
