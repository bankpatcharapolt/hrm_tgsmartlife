<!DOCTYPE html>
<html lang="th"><head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title><?=$title??'ระบบ HRM'?></title>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/jquery-ui@1.13.2/themes/base/jquery-ui.min.css">
<style>
.ui-datepicker{font-family:Sarabun,sans-serif!important;font-size:.875rem;z-index:9999!important;min-width:240px;box-shadow:0 4px 16px rgba(0,0,0,.12);border-radius:10px;overflow:hidden}
.ui-datepicker-header{background:#1a56db!important;color:#fff!important;border:none!important;padding:.5rem}
.ui-datepicker-prev,.ui-datepicker-next{cursor:pointer;top:6px!important}
.ui-datepicker-prev span,.ui-datepicker-next span{display:none!important}
.ui-datepicker-prev:after{content:"‹";color:#fff;font-size:1.3rem;line-height:1}
.ui-datepicker-next:after{content:"›";color:#fff;font-size:1.3rem;line-height:1}
.ui-datepicker th{color:#6b7280!important;font-size:.75rem;background:#f9fafb;padding:.3rem}
.ui-datepicker td{padding:1px}
.ui-datepicker td a,.ui-datepicker td span{text-align:center!important;padding:.25rem;border-radius:5px}
.ui-state-default{border:none!important;background:transparent!important;color:#374151!important}
.ui-state-hover{background:#f3f4f6!important;color:#111827!important}
.ui-state-highlight{background:#eff6ff!important;color:#1a56db!important;border:1px solid #bae6fd!important;border-radius:5px}
.ui-state-active{background:#1a56db!important;color:#fff!important;border:none!important;border-radius:5px}
.ui-datepicker select.ui-datepicker-month,.ui-datepicker select.ui-datepicker-year{font-family:Sarabun,sans-serif;font-size:.8rem;border-radius:4px;border:1px solid rgba(255,255,255,.4);background:rgba(255,255,255,.15);color:#fff;padding:1px 2px}
</style>
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
.stat-card{background:#fff;border:1px solid var(--bd);border-radius:var(--r);padding:.85rem 1rem;display:flex;align-items:center;gap:.85rem;}
.s-ico{width:38px;height:38px;border-radius:9px;display:flex;align-items:center;justify-content:center;font-size:1.1rem;flex-shrink:0;}
.s-lbl{font-size:.73rem;color:var(--mu);}
.s-val{font-size:1.45rem;font-weight:700;line-height:1.15;}
.s-sub{font-size:.7rem;color:var(--mu);}
/* Toast notification */
.notif-toast-wrap{position:fixed;bottom:1.5rem;right:1.5rem;z-index:9999;display:flex;flex-direction:column;gap:.5rem;pointer-events:none;}
.notif-toast{background:#fff;border:1px solid var(--bd);border-radius:var(--r);box-shadow:0 4px 20px rgba(0,0,0,.13);padding:.75rem 1rem;min-width:280px;max-width:340px;border-left:4px solid var(--pri);animation:tIn .25s ease;pointer-events:auto;cursor:pointer;}
.notif-toast.out{animation:tOut .25s ease forwards;}
@keyframes tIn{from{opacity:0;transform:translateY(8px)}to{opacity:1;transform:translateY(0)}}
@keyframes tOut{from{opacity:1;transform:translateY(0)}to{opacity:0;transform:translateY(8px)}}
.dt-hidden{display:none!important}
.jq-dt-wrap{display:flex;flex-wrap:nowrap;gap:6px;align-items:stretch}
.jq-dt-wrap .dt-date{flex:1 1 110px;min-width:100px;max-width:140px;height:38px;min-height:38px}
.dt-time-wrap{display:flex;gap:0;align-items:center;flex-shrink:0;
              border:1px solid #d1d5db;border-radius:8px;overflow:hidden;background:#fff}
.dt-time-wrap:focus-within{border-color:#1a56db;box-shadow:0 0 0 3px rgba(26,86,219,.1)}
.dt-time-wrap select{width:52px;padding:.4rem .2rem;font-size:.875rem;font-family:Sarabun,sans-serif;
  border:none;outline:none;background:transparent;color:#374151;
  text-align:center;text-align-last:center;cursor:pointer;
  -webkit-appearance:none;-moz-appearance:none;appearance:none}
.dt-time-wrap select:hover{background:rgba(26,86,219,.04)}
.dt-colon{font-weight:700;color:#9ca3af;padding:0 2px;line-height:1;align-self:center;user-select:none;font-size:1rem}
.jq-date-only{cursor:pointer!important;background-color:#fff!important}
.leave-time-wrap,.shift-time-wrap{display:flex;flex-wrap:nowrap;gap:6px;align-items:stretch;flex:1}
.leave-time-wrap .dt-time-wrap,.shift-time-wrap .dt-time-wrap{flex:1}
</style>
<link href="https://cdn.jsdelivr.net/npm/tom-select@2.3.1/dist/css/tom-select.bootstrap5.min.css" rel="stylesheet">
<style>
/* Tom Select override */
.ts-control{font-family:Sarabun,sans-serif;font-size:.875rem;border:1px solid var(--bd);border-radius:8px;padding:.375rem .75rem;min-height:38px;background:#fff;display:flex;align-items:center;flex-wrap:wrap;gap:3px;}
.ts-wrapper.is-focused .ts-control,.ts-wrapper.focus .ts-control{border-color:var(--pri);box-shadow:0 0 0 3px rgba(26,86,219,.1);}
.ts-dropdown{font-family:Sarabun,sans-serif;font-size:.875rem;border:1px solid var(--bd);border-radius:8px;box-shadow:0 4px 16px rgba(0,0,0,.1);margin-top:2px;}
.ts-dropdown .option{padding:.45rem .85rem;}
.ts-dropdown .option.selected,.ts-dropdown .option:hover{background:rgba(26,86,219,.08);color:var(--pri);}
.ts-dropdown .option.active{background:var(--pri);color:#fff;}
.ts-dropdown-content{max-height:220px;}
</style>
</head><body>
<nav class="navbar navbar-expand-lg">
  <div class="container-fluid">
    <a class="navbar-brand" href="<?=base_url('employee/dashboard')?>">ระบบ HRM</a>
    <button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse" data-bs-target="#nm" style="color:rgba(255,255,255,.7)"><i class="bi bi-list fs-4"></i></button>
    <div class="collapse navbar-collapse" id="nm">
      <ul class="navbar-nav me-auto">
        <?php $c=strtolower($this->router->fetch_class()); ?>
        <li class="nav-item"><a class="nav-link <?=$c==='dashboard'?'on':''?>" href="<?=base_url('employee/dashboard')?>"><i class="bi bi-house me-1"></i>หน้าหลัก</a></li>
        <?php if(!empty($current_user->can_checkin)):?><li class="nav-item"><a class="nav-link <?=$c==='attendance'?'on':''?>" href="<?=base_url('employee/attendance')?>"><i class="bi bi-clock me-1"></i>การเข้างาน</a></li><?php endif;?>
        <li class="nav-item"><a class="nav-link <?=$c==='leave'?'on':''?>" href="<?=base_url('employee/leave')?>"><i class="bi bi-calendar-check me-1"></i>การลา</a></li>
        <?php if(!empty($current_user->can_view_own_salary)):?><li class="nav-item"><a class="nav-link <?=$c==='salary'?'on':''?>" href="<?=base_url('employee/salary')?>"><i class="bi bi-cash me-1"></i>ข้อมูลการจ่ายเงิน</a></li><?php endif;?>
        <?php
        // แสดงเมนูยอดขายเฉพาะแผนกที่เกี่ยวกับการขาย
        $dept_nm = strtolower($current_user->department_name ?? '');
        $is_sales_dept = (strpos($dept_nm,'ขาย') !== false || strpos($dept_nm,'sale') !== false);
        $role_slug = strtolower($current_user->role_slug ?? $current_user->role_name ?? '');
        $is_manager_or_above = in_array($role_slug, ['manager','admin','owner','เจ้าของ','ผู้ดูแลระบบ','หัวหน้างาน']);
        // แสดงเมนูยอดขาย: มี can_view_sales หรือ เป็น manager ขึ้นไป — ทั้งคู่ต้องอยู่แผนกขาย
        if((!empty($current_user->can_view_sales) || $is_manager_or_above) && $is_sales_dept):
        ?>
        <li class="nav-item"><a class="nav-link <?=$c==='sales'&&$this->router->fetch_method()!=='team'?'on':''?>" href="<?=base_url('employee/sales')?>"><i class="bi bi-graph-up-arrow me-1"></i>ยอดขายของฉัน</a></li>
        <li class="nav-item"><a class="nav-link <?=$c==='sales'&&$this->router->fetch_method()==='team'?'on':''?>" href="<?=base_url('employee/sales/team')?>"><i class="bi bi-people-fill me-1"></i>ยอดขายของทีม</a></li>
        <?php endif;?>
        <?php if(!empty($current_user->can_approve_leave)):?><li class="nav-item"><a class="nav-link" href="<?=base_url('manager/leave')?>"><i class="bi bi-check2-circle me-1"></i>อนุมัติการลา</a></li><?php endif;?>
        <?php if(!empty($current_user->can_manage_employees)):?><li class="nav-item"><a class="nav-link" href="<?=base_url('admin/dashboard')?>"><i class="bi bi-gear me-1"></i>จัดการระบบ</a></li><?php endif;?>
      </ul>
      <ul class="navbar-nav ms-auto align-items-center">
        <li class="nav-item dropdown">
          <a class="nav-link position-relative" href="#" id="bellBtn" data-bs-toggle="dropdown" aria-expanded="false">
            <i class="bi bi-bell-fill fs-5"></i>
            <span id="bellBadge"
              class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger"
              style="font-size:.58rem;<?=empty($unread_notifications)?'display:none':''?>">
              <?=!empty($unread_notifications)?(int)$unread_notifications:''?>
            </span>
          </a>
          <div class="dropdown-menu dropdown-menu-end shadow p-0" style="width:305px;max-height:370px;overflow-y:auto;">
            <div class="d-flex justify-content-between px-3 py-2 border-bottom">
              <strong style="font-size:.83rem">การแจ้งเตือน</strong>
              <a href="<?=base_url('employee/notifications')?>" class="text-primary text-decoration-none" style="font-size:.77rem">ดูทั้งหมด</a>
            </div>
            <div id="notifList">
              <?php if(!empty($recent_notifications)):foreach($recent_notifications as $n):?>
              <a href="<?=$n->link??'#'?>" class="dropdown-item py-2 border-bottom <?=!$n->is_read?'bg-light':''?>" style="white-space:normal">
                <div style="font-size:.82rem;font-weight:<?=!$n->is_read?600:400?>"><?=htmlspecialchars($n->title)?></div>
                <div style="font-size:.76rem;color:var(--mu)"><?=htmlspecialchars(mb_substr($n->message,0,55))?><?=mb_strlen($n->message)>55?'...':''?></div>
                <div style="font-size:.69rem;color:var(--mu)"><?=date('d/m H:i',strtotime($n->created_at))?></div>
              </a>
              <?php endforeach;else:?>
              <div class="text-center text-muted py-4 small">ไม่มีการแจ้งเตือน</div>
              <?php endif;?>
            </div>
          </div>
        </li>
        <li class="nav-item dropdown ms-1">
          <a class="nav-link d-flex align-items-center gap-2" href="#" data-bs-toggle="dropdown">
            <?php if(!empty($current_user->photo)):?>
            <img src="<?=base_url($current_user->photo)?>" style="width:28px;height:28px;border-radius:50%;object-fit:cover;">
            <?php else:?>
            <div style="width:28px;height:28px;background:var(--pri);border-radius:50%;display:flex;align-items:center;justify-content:center;color:#fff;font-weight:700;font-size:.8rem"><?=mb_substr($current_user->first_name??'A',0,1)?></div>
            <?php endif;?>
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
  <div class="alert alert-<?=$t==='error'?'danger':$t?> alert-dismissible fade show mb-3 flash-alert">
    <i class="bi bi-<?=$ic?> me-2"></i><?=${'flash_'.$t}?>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
  </div>
  <?php endif;endforeach;?>
  <?=$content_view??''?>
</div>

<!-- Toast container -->
<div class="notif-toast-wrap" id="toastWrap"></div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.4/dist/chart.umd.min.js"></script>
<script>
// auto-dismiss flash alerts only (not static info/hint boxes)
setTimeout(function(){
  document.querySelectorAll('.flash-alert').forEach(function(a){
    bootstrap.Alert.getOrCreateInstance(a).close();
  });
}, 4000);
</script>

<script>
// ── Notification SSE ──────────────────────────────────────────────────────
// PHP ตอบทันทีแล้วจบ (ไม่ sleep ไม่ค้าง Apache worker)
// EventSource spec: เมื่อ connection ปิด จะ reconnect อัตโนมัติตาม retry: 30000
// ผลลัพธ์ = poll ทุก 30 วิ โดยไม่ต้องเขียน setInterval เอง
// ────────────────────────────────────────────────────────────────────────────
(function () {
  'use strict';

  var BASE      = '<?= base_url() ?>';
  var SSE_URL   = BASE + 'api/notifications/stream';
  var prevCount = <?= !empty($unread_notifications) ? (int)$unread_notifications : 0 ?>;
  var latestTs  = 0;
  var es        = null;

  // ── เริ่ม EventSource พร้อมส่ง ?since= ────────────────────────
  function startSSE() {
    if (!window.EventSource) return;
    if (es) { es.close(); es = null; }

    var url = SSE_URL + (latestTs ? '?since=' + latestTs : '');
    es = new EventSource(url);

    es.addEventListener('notification', function (e) {
      try {
        var d = JSON.parse(e.data);
        if (d.latest_ts) latestTs = d.latest_ts;
        updateBell(d.count, d.items);
        if (d.count > prevCount && d.items && d.items.length > 0) {
          showToast(d.items[0]);
        }
        prevCount = d.count;
        // SSE ส่ง retry:10000 → reconnect ทุก 10 วิ แต่ต้องปิดก่อนแล้วเปิดใหม่
        // เพื่อส่ง since= ที่อัปเดตแล้ว
        es.close();
        setTimeout(startSSE, 10000);
      } catch (err) { /* JSON parse error */ }
    });

    es.onerror = function () {
      es.close();
      setTimeout(startSSE, 15000); // retry หลัง error
    };
  }

  // ── กดกระดิ่ง → mark all read ───────────────────────────────────
  document.addEventListener('DOMContentLoaded', function () {
    var bellBtn = document.getElementById('bellBtn');
    if (bellBtn) {
      bellBtn.addEventListener('click', function () {
        var badge = document.getElementById('bellBadge');
        if (badge && badge.style.display !== 'none' && parseInt(badge.textContent || '0', 10) > 0) {
          fetch(BASE + 'api/notifications/mark_all_read', {
            method: 'POST',
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
          });
          prevCount = 0;
          badge.style.display = 'none';
          badge.textContent = '';
        }
      });
    }
  });

  // ── อัปเดต bell badge + dropdown list ──────────────────────────
  function updateBell(count, items) {
    var badge = document.getElementById('bellBadge');
    if (badge) {
      badge.textContent  = count > 99 ? '99+' : (count || '');
      badge.style.display = count > 0 ? '' : 'none';
    }

    var list = document.getElementById('notifList');
    if (!list || !items) return;

    if (!items.length) {
      list.innerHTML = '<div class="text-center text-muted py-4 small">ไม่มีการแจ้งเตือน</div>';
      return;
    }

    list.innerHTML = items.map(function (n) {
      var msg  = esc(n.message || '');
      var short = msg.length > 55 ? msg.substring(0, 55) + '…' : msg;
      return '<a href="' + esc(n.link || '#') + '" '
           + 'class="dropdown-item py-2 border-bottom' + (n.is_read ? '' : ' bg-light') + '" '
           + 'style="white-space:normal">'
           + '<div style="font-size:.82rem;font-weight:' + (n.is_read ? 400 : 600) + '">' + esc(n.title || '') + '</div>'
           + '<div style="font-size:.76rem;color:var(--mu)">' + short + '</div>'
           + '<div style="font-size:.69rem;color:var(--mu)">' + esc(n.time_ago || '') + '</div>'
           + '</a>';
    }).join('');
  }

  // ── Toast popup ─────────────────────────────────────────────────
  function showToast(n) {
    var wrap = document.getElementById('toastWrap');
    if (!wrap) return;

    var t    = document.createElement('div');
    t.className = 'notif-toast';
    t.innerHTML =
      '<div class="d-flex justify-content-between align-items-start gap-2">'
      + '<div>'
      + '<div class="fw-semibold" style="font-size:.83rem">🔔 ' + esc(n.title || '') + '</div>'
      + '<div style="font-size:.78rem;color:var(--mu);margin-top:2px">'
      +   esc((n.message || '').substring(0, 70))
      + '</div>'
      + '</div>'
      + '<button type="button" class="btn-close" style="font-size:.55rem;flex-shrink:0"></button>'
      + '</div>';

    // คลิกปุ่ม × ปิด toast
    t.querySelector('.btn-close').addEventListener('click', function (e) {
      e.stopPropagation();
      removeToast(t);
    });

    // คลิก toast ทั้งก้อน → ไปที่ link
    t.addEventListener('click', function () {
      if (n.link) window.location.href = n.link;
    });

    wrap.appendChild(t);

    // auto-remove หลัง 6 วิ
    setTimeout(function () { removeToast(t); }, 6000);
  }

  function removeToast(t) {
    if (!t.parentNode) return;
    t.classList.add('out');
    setTimeout(function () { t.parentNode && t.parentNode.removeChild(t); }, 260);
  }

  // ── XSS escape ──────────────────────────────────────────────────
  function esc(s) {
    return String(s)
      .replace(/&/g, '&amp;')
      .replace(/</g, '&lt;')
      .replace(/>/g, '&gt;')
      .replace(/"/g, '&quot;');
  }

  startSSE();
})();
</script>
  <link rel="stylesheet" href="https://code.jquery.com/ui/1.14.2/themes/base/jquery-ui.css">
<script src="https://cdn.jsdelivr.net/npm/tom-select@2.3.1/dist/js/tom-select.complete.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
  initTomSelects(document);
});
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
  <script src="https://cdn.jsdelivr.net/npm/jquery@3.7.1/dist/jquery.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/jquery-ui@1.13.2/dist/jquery-ui.min.js"></script>
  <script>
  $.datepicker.setDefaults({dateFormat:"dd/mm/yy",changeMonth:true,changeYear:true,yearRange:"2010:2035"});
  var _HH="<option value="00">00</option><option value="01">01</option><option value="02">02</option><option value="03">03</option><option value="04">04</option><option value="05">05</option><option value="06">06</option><option value="07">07</option><option value="08">08</option><option value="09">09</option><option value="10">10</option><option value="11">11</option><option value="12">12</option><option value="13">13</option><option value="14">14</option><option value="15">15</option><option value="16">16</option><option value="17">17</option><option value="18">18</option><option value="19">19</option><option value="20">20</option><option value="21">21</option><option value="22">22</option><option value="23">23</option>",_MM="<option value="00">00</option><option value="01">01</option><option value="02">02</option><option value="03">03</option><option value="04">04</option><option value="05">05</option><option value="06">06</option><option value="07">07</option><option value="08">08</option><option value="09">09</option><option value="10">10</option><option value="11">11</option><option value="12">12</option><option value="13">13</option><option value="14">14</option><option value="15">15</option><option value="16">16</option><option value="17">17</option><option value="18">18</option><option value="19">19</option><option value="20">20</option><option value="21">21</option><option value="22">22</option><option value="23">23</option><option value="24">24</option><option value="25">25</option><option value="26">26</option><option value="27">27</option><option value="28">28</option><option value="29">29</option><option value="30">30</option><option value="31">31</option><option value="32">32</option><option value="33">33</option><option value="34">34</option><option value="35">35</option><option value="36">36</option><option value="37">37</option><option value="38">38</option><option value="39">39</option><option value="40">40</option><option value="41">41</option><option value="42">42</option><option value="43">43</option><option value="44">44</option><option value="45">45</option><option value="46">46</option><option value="47">47</option><option value="48">48</option><option value="49">49</option><option value="50">50</option><option value="51">51</option><option value="52">52</option><option value="53">53</option><option value="54">54</option><option value="55">55</option><option value="56">56</option><option value="57">57</option><option value="58">58</option><option value="59">59</option>";
  function initDTPickers(root){
    var ctx=root?$(root):$(document);
    ctx.find(".jq-dt-wrap:not([data-dt-init])").each(function(){
      $(this).attr("data-dt-init","1");
      var $w=$(this),$d=$w.find(".dt-date"),$h=$w.find(".dt-hidden");
      $d.prop("readonly",true).css("cursor","pointer");
      var cv=$h.val()||"",ch=0,cm=0;
      if(cv&&cv.indexOf(" ")>0){var t=cv.split(" ")[1];ch=parseInt(t.substr(0,2),10);cm=parseInt(t.substr(3,2),10);}
      var $tw=$("<div class=\"dt-time-wrap\"></div>");
      var $selH=$("<select class=\"dt-hh\"></select>");
      for(var h=0;h<=23;h++){var hv=(h<10?"0":"")+h;var $o=$("<option>").val(hv).text(hv);if(h===ch)$o.prop("selected",true);$selH.append($o);}
      var $selM=$("<select class=\"dt-mm\"></select>");
      for(var m=0;m<=59;m++){var mv=(m<10?"0":"")+m;var $p=$("<option>").val(mv).text(mv);if(m===cm)$p.prop("selected",true);$selM.append($p);}
      $tw.append($selH);
      $tw.append("<span class=\"dt-colon\">:</span>");
      $tw.append($selM);
      $w.append($tw);
      var $sh=$w.find(".dt-hh"),$sm=$w.find(".dt-mm");
      function merge(){var dv=$d.val(),hh=$sh.val(),mm=$sm.val();if(!dv){$h.val("");return;}
        var p=dv.split("/");if(p.length===3)$h.val(p[2]+"-"+p[1]+"-"+p[0]+" "+hh+":"+mm+":00");}
      $d.datepicker({dateFormat:"dd/mm/yy",onSelect:function(d){merge();}});
      $sh.on("change",merge);$sm.on("change",merge);merge();
    });
    ctx.find(".jq-date-only:not(.hasDatepicker)").each(function(){
      var $inp=$(this);
      $inp.prop("readonly",true).css("cursor","pointer");
      var hidId=$inp.attr("id")?$inp.attr("id").replace(/Display$/,"Hidden"):null;
      var $hid=hidId?$("#"+hidId):null;
      $inp.datepicker({
        dateFormat:"dd/mm/yy",
        onSelect:function(d){
          if($hid&&$hid.length){var p=d.split("/");$hid.val(p.length===3?p[2]+"-"+p[1]+"-"+p[0]:"");}
        }
      });
    });
  }
  $(document).ready(function(){initDTPickers();});
  $(document).on("shown.bs.modal",function(e){initDTPickers(e.target);});
  </script>
<?php if (!empty($extra_js)) echo $extra_js; ?>
</body></html>
