<!DOCTYPE html>
<html lang="th">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width,initial-scale=1,viewport-fit=cover">
  <title><?= $title ?? 'ระบบ HRM' ?></title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/jquery-ui@1.13.2/themes/base/jquery-ui.min.css">
   <link rel="stylesheet" href="https://code.jquery.com/ui/1.14.2/themes/base/jquery-ui.css">
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
    /* ── jQuery datepicker datetime widget ── */
    .dt-hidden{display:none!important}
    .jq-dt-wrap{display:flex;flex-wrap:nowrap;gap:6px;align-items:stretch}
    .jq-dt-wrap .dt-date{flex:1 1 110px;min-width:100px;max-width:140px}
    .dt-time-wrap{display:flex;gap:0;align-items:center;flex-shrink:0;
                  border:1px solid #d1d5db;border-radius:8px;overflow:hidden;background:#fff}
    .dt-time-wrap:focus-within{border-color:#1a56db;box-shadow:0 0 0 3px rgba(26,86,219,.1)}
    .dt-time-wrap select{
      width:52px;padding:.4rem .2rem;
      font-size:.875rem;font-family:Sarabun,sans-serif;
      border:none;outline:none;background:transparent;color:#374151;
      text-align:center;text-align-last:center;
      cursor:pointer;
      -webkit-appearance:none;-moz-appearance:none;appearance:none
    }
    .dt-time-wrap select:hover{background:rgba(26,86,219,.04)}
    .dt-colon{
      font-weight:700;color:#9ca3af;padding:0 2px;
      line-height:1;align-self:center;user-select:none;font-size:1rem
    }
    /* ── time-only widget wrapper (leave/shift) — ไม่ถูก initDTPickers ทับ ── */
    .leave-time-wrap,.shift-time-wrap{display:flex;flex-wrap:nowrap;gap:6px;align-items:stretch;flex:1}
    .leave-time-wrap .dt-time-wrap,.shift-time-wrap .dt-time-wrap{flex:1}
    /* ขนาด input วันที่ใน Bootstrap col */
    .jq-dt-wrap .form-control.dt-date{min-height:38px;height:38px}
  </style>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Sarabun:wght@300;400;500;600;700&display=swap" rel="stylesheet">
  <style>
    :root {
      --sb: 240px;
      --pri: #1a56db;
      --pri2: #1e429f;
      --bg: #f0f4ff;
      --sb-bg: #0f172a;
      --bd: #e5e7eb;
      --tx: #111827;
      --mu: #6b7280;
      --dan: #dc2626;
      --suc: #16a34a;
      --r: 12px;
    }

    * {
      box-sizing: border-box;
    }

    body {
      font-family: Sarabun, sans-serif;
      background: var(--bg);
      margin: 0;
      overflow-x: hidden;
      max-width: 100vw;
    }

    #sb {
      width: var(--sb);
      height: 100vh;
      background: var(--sb-bg);
      position: fixed;
      top: 0;
      left: 0;
      z-index: 1000;
      display: flex;
      flex-direction: column;
      transition: .3s;
      overflow: hidden;
      scrollbar-width: none;
    }

    .sb-brand {
      padding: 1.2rem 1.5rem;
      border-bottom: 1px solid rgba(255, 255, 255, .08);
      display: flex;
      align-items: center;
      gap: .75rem;
    }

    .sb-ico {
      width: 38px;
      height: 38px;
      background: var(--pri);
      border-radius: 10px;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 1.1rem;
    }

    .sb-nm {
      color: #fff;
      font-weight: 700;
      font-size: .95rem;
    }

    .sb-sub {
      color: rgba(255, 255, 255, .4);
      font-size: .7rem;
    }

    .sb-nav {
      flex: 1;
      overflow-y: auto;
      overflow-x: hidden;
      padding: .5rem 0;
      min-height: 0;
      scrollbar-width: thin;
      scrollbar-color: rgba(255, 255, 255, .1) transparent;
    }

    .sb-sec {
      padding: .55rem 1.5rem .2rem;
      color: rgba(255, 255, 255, .28);
      font-size: .67rem;
      font-weight: 600;
      letter-spacing: .08em;
      text-transform: uppercase;
    }

    .sb-nav a {
      display: flex;
      align-items: center;
      gap: .6rem;
      padding: .5rem 1rem;
      color: rgba(255, 255, 255, .72);
      text-decoration: none;
      font-size: .82rem;
      border-left: 3px solid transparent;
      transition: .15s;
      white-space: nowrap;
      overflow: hidden;
    }

    .sb-nav a:hover {
      background: rgba(255, 255, 255, .06);
      color: #fff;
    }

    .sb-nav a.on {
      background: rgba(26, 86, 219, .22);
      color: #fff;
      border-left-color: var(--pri);
    }

    .sb-nav a i {
      width: 18px;
      text-align: center;
      font-size: .95rem;
    }

    .sb-user {
      padding: .75rem 1rem;
      border-top: 1px solid rgba(255, 255, 255, .08);
      display: flex;
      align-items: center;
      gap: .6rem;
      flex-shrink: 0;
    }

    .sb-av {
      width: 34px;
      height: 34px;
      border-radius: 50%;
      background: var(--pri);
      display: flex;
      align-items: center;
      justify-content: center;
      color: #fff;
      font-weight: 700;
      font-size: .82rem;
      flex-shrink: 0;
    }

    .sb-user img {
      width: 34px;
      height: 34px;
      border-radius: 50%;
      object-fit: cover;
    }

    .sb-unm {
      color: #fff;
      font-size: .83rem;
      font-weight: 600;
    }

    .sb-url {
      color: rgba(255, 255, 255, .42);
      font-size: .71rem;
    }

    #main {
      margin-left: var(--sb);
      display: flex;
      flex-direction: column;
      min-height: 100vh;
      min-width: 0;
      max-width: calc(100vw - var(--sb));
      overflow-x: hidden;
    }

    .topbar {
      background: #fff;
      border-bottom: 1px solid var(--bd);
      padding: .65rem 1.5rem;
      display: flex;
      align-items: center;
      justify-content: space-between;
      position: sticky;
      top: 0;
      z-index: 100;
      box-shadow: 0 1px 4px rgba(0, 0, 0, .05);
    }

    .pg-title {
      font-size: 1.02rem;
      font-weight: 600;
      color: var(--tx);
      margin: 0;
    }

    .bico {
      width: 36px;
      height: 36px;
      border: none;
      background: #f3f4f6;
      border-radius: 9px;
      display: flex;
      align-items: center;
      justify-content: center;
      color: var(--mu);
      cursor: pointer;
      position: relative;
      transition: .15s;
    }

    .bico:hover {
      background: var(--bd);
    }

    .nb {
      position: absolute;
      top: 4px;
      right: 4px;
      width: 15px;
      height: 15px;
      background: var(--dan);
      border-radius: 50%;
      font-size: .58rem;
      color: #fff;
      display: flex;
      align-items: center;
      justify-content: center;
    }

    .content {
      padding: 1.5rem;
      flex: 1;
    }

    .card {
      background: #fff;
      border: 1px solid var(--bd);
      border-radius: var(--r);
      box-shadow: 0 1px 5px rgba(0, 0, 0, .04);
    }

    .card-header {
      padding: .85rem 1.2rem;
      border-bottom: 1px solid var(--bd);
      font-weight: 600;
      background: transparent;
      font-size: .9rem;
    }

    .card-body {
      padding: 1.2rem;
    }

    .stat-card {
      background: #fff;
      border: 1px solid var(--bd);
      border-radius: var(--r);
      padding: 1.15rem;
      display: flex;
      align-items: center;
      gap: 1rem;
    }

    .s-ico {
      width: 46px;
      height: 46px;
      border-radius: 11px;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 1.25rem;
      flex-shrink: 0;
    }

    .s-lbl {
      font-size: .76rem;
      color: var(--mu);
    }

    .s-val {
      font-size: 1.5rem;
      font-weight: 700;
      color: var(--tx);
      line-height: 1.1;
    }

    .s-sub {
      font-size: .72rem;
      color: var(--mu);
    }

    .table {
      font-size: .865rem;
    }

    .table th {
      font-weight: 600;
      color: var(--mu);
      font-size: .77rem;
      text-transform: uppercase;
      letter-spacing: .04em;
      background: #f9fafb;
      border-bottom: 2px solid var(--bd);
    }

    .table td {
      vertical-align: middle;
      border-color: #f3f4f6;
    }

    .badge {
      font-size: .69rem;
      font-weight: 500;
      border-radius: 5px;
    }

    .alert {
      border: none;
      border-radius: var(--r);
      padding: .65rem 1rem;
      font-size: .875rem;
    }

    .btn {
      font-family: Sarabun, sans-serif;
      font-size: .875rem;
      border-radius: 8px;
    }

    .btn-primary {
      background: var(--pri);
      border-color: var(--pri);
    }

    .btn-primary:hover {
      background: var(--pri2);
      border-color: var(--pri2);
    }

    .form-control,
    .form-select {
      font-family: Sarabun, sans-serif;
      border-radius: 8px;
      border-color: var(--bd);
      font-size: .875rem;
    }

    .form-control:focus,
    .form-select:focus {
      border-color: var(--pri);
      box-shadow: 0 0 0 3px rgba(26, 86, 219, .1);
    }

    .form-label {
      font-size: .855rem;
      font-weight: 500;
      margin-bottom: .3rem;
    }

    .nd-drop {
      width: 320px;
      max-height: 380px;
      overflow-y: auto;
    }

    .nd-item {
      padding: .6rem 1rem;
      border-bottom: 1px solid #f3f4f6;
      cursor: pointer;
      transition: .15s;
    }

    .nd-item:hover {
      background: #f9fafb;
    }

    .nd-item.unread {
      background: #eff6ff;
    }

    .nd-title {
      font-size: .82rem;
      font-weight: 600;
      margin-bottom: .15rem;
    }

    .nd-msg {
      font-size: .77rem;
      color: var(--mu);
    }

    .nd-time {
      font-size: .69rem;
      color: var(--mu);
    }

    @media(max-width:768px) {
      #sb {
        transform: translateX(-100%);
      }

      #main {
        margin-left: 0;
        max-width: 100vw;
      }

      #sb.open {
        transform: translateX(0);
      }

      #sbToggle {
        display: inline-flex !important;
      }

      .stat-card {
        flex-direction: column;
      }

      .table-responsive {
        font-size: .8rem;
      }

      th,
      td {
        padding: .35rem .5rem !important;
      }
    }

    .sb-ov {
      display: none;
      position: fixed;
      inset: 0;
      background: rgba(0, 0, 0, .5);
      z-index: 999;
    }

    @media(max-width:768px) {
      .sb-ov.show {
        display: block;
      }
    }

    /* Tom Select override — ให้ดูเข้ากับ Bootstrap ของโปรเจค */
    .ts-wrapper.form-select {
      padding: 0;
      border: none;
    }

    .ts-wrapper.form-control {
      padding: 0;
      border: none;
    }

    .ts-control {
      font-family: Sarabun, sans-serif;
      font-size: .875rem;
      border: 1px solid var(--bd);
      border-radius: 8px;
      padding: .375rem .75rem;
      min-height: 38px;
      background: #fff;
      display: flex;
      align-items: center;
      flex-wrap: wrap;
      gap: 3px;
      cursor: text;
    }

    .ts-wrapper.is-focused .ts-control,
    .ts-wrapper.focus .ts-control {
      border-color: var(--pri);
      box-shadow: 0 0 0 3px rgba(26, 86, 219, .1);
      outline: none;
    }

    .ts-dropdown {
      font-family: Sarabun, sans-serif;
      font-size: .875rem;
      border: 1px solid var(--bd);
      border-radius: 8px;
      box-shadow: 0 4px 16px rgba(0, 0, 0, .1);
      margin-top: 2px;
    }

    .ts-dropdown .option {
      padding: .45rem .85rem;
    }

    .ts-dropdown .option.selected,
    .ts-dropdown .option:hover {
      background: rgba(26, 86, 219, .08);
      color: var(--pri);
    }

    .ts-dropdown .option.active {
      background: var(--pri);
      color: #fff;
    }

    .ts-dropdown-content {
      max-height: 220px;
    }
  </style>
</head>

<body>
  <link href="https://cdn.jsdelivr.net/npm/tom-select@2.3.1/dist/css/tom-select.bootstrap5.min.css" rel="stylesheet">
  <div class="sb-ov" id="ov" onclick="toggleSB()"></div>
  <nav id="sb">
    <div class="sb-brand">

      <div>
        <div class="sb-nm">ระบบ HRM</div>
        <div class="sb-sub">Human Resource Mgmt</div>
      </div>
    </div>
    <div class="sb-nav">
      <?php $ctrl = strtolower($this->router->fetch_class());
      $seg3 = $this->uri->segment(3); ?>
      <div class="sb-sec">หน้าหลัก</div>
      <a href="<?= base_url('admin/dashboard') ?>" class="<?= $ctrl === 'dashboard' ? 'on' : '' ?>">
        <i
          class="bi bi-speedometer2"></i>แดชบอร์ด</a>
      <div class="sb-sec">พนักงาน</div>
      <a href="<?= base_url('admin/employees') ?>" class="<?= ($ctrl === 'employees') ? 'on' : '' ?>"><i
          class="bi bi-people-fill"></i>ข้อมูลพนักงาน</a>
      <a href="<?= base_url('admin/employees_import/import') ?>" class="<?= $ctrl === 'employees_import' ? 'on' : '' ?>"
        style="padding-left:2.5rem;font-size:.82rem"><i class="bi bi-upload"></i>Import / Export Excel</a>
      <a href="<?= base_url('admin/attendance') ?>" class="<?= ($ctrl === 'attendance' && $seg3 !== 'shifts') ? 'on' : '' ?>"><i
          class="bi bi-clock-fill"></i>การเข้างาน</a>
      <a href="<?= base_url('admin/attendance/shifts') ?>" class="<?= ($ctrl === 'attendance' && $seg3 === 'shifts') ? 'on' : '' ?>"
        style="padding-left:2.5rem;font-size:.82rem"><i class="bi bi-diagram-3"></i>จัดการกะ</a>
      <a href="<?= base_url('admin/leave') ?>" class="<?= $ctrl === 'leave' ? 'on' : '' ?>"><i
          class="bi bi-calendar-check-fill"></i>การลา</a>
      <?php if (!empty($current_user->is_full_access) || in_array($current_user->role_name ?? '', ['admin', 'owner', 'เจ้าของ', 'ผู้ดูแลระบบ'])): ?>
        <a href="<?= base_url('admin/leave_types') ?>" class="<?= $ctrl === 'leave_types' ? 'on' : '' ?>"
          style="padding-left:2.5rem;font-size:.82rem"><i class="bi bi-sliders"></i>จัดการวันลา</a>
      <?php endif; ?>
      <div class="sb-sec">การเงิน</div>
      <a href="<?= base_url('admin/salary') ?>"
        class="<?= ($ctrl === 'salary' && $seg3 !== 'bonus' && $seg3 !== 'tax_docs' && $seg3 !== 'slips') ? 'on' : '' ?>"><i
          class="bi bi-currency-dollar"></i>เงินเดือน</a>
      <a href="<?= base_url('admin/salary/slips') ?>" class="<?= ($ctrl === 'salary' && $seg3 === 'slips') ? 'on' : '' ?>"
        style="padding-left:2rem;font-size:.8rem"><i class="bi bi-file-earmark-pdf text-danger"></i>รายการสลิป</a>
      <a href="<?= base_url('admin/salary/bonus') ?>" class="<?= ($ctrl === 'salary' && $seg3 === 'bonus') ? 'on' : '' ?>"><i
          class="bi bi-gift-fill"></i>โบนัสประจำปี</a>
      <a href="<?= base_url('admin/salary/tax_docs') ?>" class="<?= ($ctrl === 'salary' && $seg3 === 'tax_docs') ? 'on' : '' ?>"><i
          class="bi bi-file-earmark-text-fill"></i>ใบทวิ 50</a>
      <div class="sb-sec">รายงาน</div>
      <a href="<?= base_url('admin/sales') ?>" class="<?= $ctrl === 'sales' ? 'on' : '' ?>"><i
          class="bi bi-graph-up-arrow"></i>ยอดขาย</a>
      <div class="sb-sec">ระบบ</div>
      <a href="<?= base_url('admin/notifications') ?>" class="<?= $ctrl === 'notifications' ? 'on' : '' ?>"><i
          class="bi bi-bell-fill"></i>ส่งการแจ้งเตือน</a>
      <a href="<?= base_url('admin/teams') ?>" class="<?= $ctrl === 'teams' ? 'on' : '' ?>"><i
          class="bi bi-diagram-3"></i>จัดการทีม</a>
      <?php if (!empty($current_user->is_full_access) || !empty($current_user->can_manage_employees)): ?>
        <!-- <a href="<?= base_url('admin/roles') ?>" class="<?= $ctrl === 'roles' ? 'on' : '' ?>"><i
            class="bi bi-shield-check"></i>บทบาทและสิทธิ์</a> -->
      <?php endif; ?>
      <a href="<?= base_url('employee/profile') ?>"><i class="bi bi-person-circle"></i>โปรไฟล์ของฉัน</a>
      <a href="<?= base_url('auth/logout') ?>" onclick="return confirm('ออกจากระบบ?')"><i
          class="bi bi-box-arrow-right"></i>ออกจากระบบ</a>
    </div>
    <div class="sb-user">
      <?php if (!empty($current_user->photo)): ?><img src="<?= base_url($current_user->photo) ?>"><?php else: ?>
        <div class="sb-av"><?= mb_substr($current_user->first_name ?? 'A', 0, 1) ?></div><?php endif; ?>
      <div>
        <div class="sb-unm"><?= $current_user->full_name ?? '' ?></div>
        <div class="sb-url"><?= $current_user->role_name ?? '' ?></div>
      </div>
    </div>
  </nav>
  <div id="main">
    <div class="topbar">
      <div class="d-flex align-items-center gap-2">
        <button class="bico d-lg-none" onclick="toggleSB()" style="font-size:1.3rem;line-height:1">☰</button>
        <h1 class="pg-title"><?= $page_title ?? '' ?></h1>
      </div>
      <div class="d-flex align-items-center gap-2">
        <div class="dropdown">
          <button class="bico" id="adminBellBtn" data-bs-toggle="dropdown">
            <i class="bi bi-bell fs-5"></i>
            <span id="adminBellBadge" class="nb"<?= empty($unread_notifications) ? ' style="display:none"' : '' ?>><?= !empty($unread_notifications) ? (int)$unread_notifications : '' ?></span>
          </button>
          <div class="dropdown-menu dropdown-menu-end nd-drop p-0 shadow">
            <div class="d-flex justify-content-between align-items-center px-3 py-2 border-bottom"><strong
                style="font-size:.83rem">การแจ้งเตือน</strong><a href="<?= base_url('employee/notifications') ?>"
                class="text-primary text-decoration-none" style="font-size:.77rem">ดูทั้งหมด</a></div>
            <div id="adminNotifList">
              <?php if (!empty($recent_notifications)):
                foreach ($recent_notifications as $n): ?>
                  <div class="nd-item <?= !$n->is_read ? 'unread' : '' ?>"
                    onclick="markRead(<?= $n->id ?>,'<?= addslashes($n->link ?? '') ?>')">
                    <div class="nd-title"><?= htmlspecialchars($n->title) ?></div>
                    <div class="nd-msg">
                      <?= htmlspecialchars(mb_substr($n->message, 0, 55)) ?><?= mb_strlen($n->message) > 55 ? '...' : '' ?></div>
                    <div class="nd-time"><?= date('d/m H:i', strtotime($n->created_at)) ?></div>
                  </div>
                <?php endforeach; else: ?>
                <div class="text-center text-muted py-4 small">ไม่มีการแจ้งเตือน</div><?php endif; ?>
            </div>
          </div>
        </div>
        <div class="dropdown">
          <button class="bico" data-bs-toggle="dropdown">
            <?php if (!empty($current_user->photo)): ?><img src="<?= base_url($current_user->photo) ?>"
                style="width:24px;height:24px;border-radius:50%;object-fit:cover;"><?php else: ?><i
                class="bi bi-person-circle fs-5"></i><?php endif; ?>
          </button>
          <ul class="dropdown-menu dropdown-menu-end shadow">
            <li class="px-3 py-2 border-bottom"><small class="text-muted"><?= $current_user->role_name ?? '' ?></small></li>
            <li><a class="dropdown-item" href="<?= base_url('employee/profile') ?>"><i
                  class="bi bi-person me-2"></i>โปรไฟล์</a></li>
            <li>
              <hr class="dropdown-divider">
            </li>
            <li><a class="dropdown-item text-danger" href="<?= base_url('auth/logout') ?>"
                onclick="return confirm('ออกจากระบบ?')"><i class="bi bi-box-arrow-right me-2"></i>ออกจากระบบ</a></li>
          </ul>
        </div>
      </div>
    </div>
    <div class="px-4 pt-3">
      <?php foreach (array('success' => 'check-circle', 'error' => 'exclamation-circle', 'warning' => 'exclamation-triangle', 'info' => 'info-circle') as $t => $ic):
        if (!empty(${'flash_' . $t} ?? '')): ?>
          <div class="alert alert-<?= $t === 'error' ? 'danger' : $t ?> alert-dismissible fade show flash-alert">
            <i class="bi bi-<?= $ic ?> me-2"></i><?= ${'flash_' . $t} ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
          </div>
        <?php endif; endforeach; ?>
    </div>
    <div class="content"><?= $content_view ?? '' ?></div>
  </div>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.4/dist/chart.umd.min.js"></script>
  <script>
    function toggleSB() { document.getElementById('sb').classList.toggle('open'); document.getElementById('ov').classList.toggle('show'); }
    function markRead(id, link) { fetch('<?= base_url('api/notifications/mark_read/') ?>' + id, { method: 'POST', headers: { 'X-Requested-With': 'XMLHttpRequest' } }); if (link) window.location.href = link; }
  </script>

  <!-- ── Admin Notification SSE + mark-all-read ─────────────────────────── -->
  <script>
  (function () {
    'use strict';
    var BASE     = '<?= base_url() ?>';
    var prevCount = <?= !empty($unread_notifications) ? (int)$unread_notifications : 0 ?>;
    var latestTs  = 0;
    var es        = null;

    function startSSE() {
      if (!window.EventSource) return;
      if (es) { es.close(); es = null; }
      var url = BASE + 'api/notifications/stream' + (latestTs ? '?since=' + latestTs : '');
      es = new EventSource(url);
      es.addEventListener('notification', function (e) {
        try {
          var d = JSON.parse(e.data);
          if (d.latest_ts) latestTs = d.latest_ts;
          _updateBadge(d.count);
          if (d.items) _updateList(d.items);
          if (d.count > prevCount && d.items && d.items.length > 0) _showToast(d.items[0]);
          prevCount = d.count;
        } catch (err) {}
        es.close();
        setTimeout(startSSE, 10000);
      });
      es.onerror = function () { es.close(); setTimeout(startSSE, 15000); };
    }

    function _updateBadge(count) {
      var b = document.getElementById('adminBellBadge');
      if (!b) return;
      b.textContent   = count > 99 ? '99+' : (count || '');
      b.style.display = count > 0 ? '' : 'none';
    }

    function _updateList(items) {
      var list = document.getElementById('adminNotifList');
      if (!list) return;
      if (!items.length) {
        list.innerHTML = '<div class="text-center text-muted py-4 small">ไม่มีการแจ้งเตือน</div>';
        return;
      }
      list.innerHTML = items.map(function (n) {
        var short = (n.message || '').substring(0, 55) + ((n.message || '').length > 55 ? '...' : '');
        return '<div class="nd-item' + (n.is_read ? '' : ' unread') + '" onclick="markRead(' + n.id + ',\'' + (n.link || '') + '\')">'
          + '<div class="nd-title">' + _esc(n.title || '') + '</div>'
          + '<div class="nd-msg">' + _esc(short) + '</div>'
          + '<div class="nd-time">' + _esc(n.time_ago || '') + '</div>'
          + '</div>';
      }).join('');
    }

    function _showToast(n) {
      // simple toast สำหรับ admin layout
      var existing = document.getElementById('adminToastWrap');
      if (!existing) {
        existing = document.createElement('div');
        existing.id = 'adminToastWrap';
        existing.style.cssText = 'position:fixed;bottom:1.5rem;right:1.5rem;z-index:9999;display:flex;flex-direction:column;gap:.5rem;pointer-events:none;';
        document.body.appendChild(existing);
      }
      var t = document.createElement('div');
      t.style.cssText = 'background:#fff;border:1px solid #e5e7eb;border-radius:12px;box-shadow:0 4px 20px rgba(0,0,0,.13);padding:.75rem 1rem;min-width:260px;max-width:320px;border-left:4px solid #1a56db;font-family:Sarabun,sans-serif;cursor:pointer;pointer-events:auto;';
      t.innerHTML = '<div style="font-size:.83rem;font-weight:600">🔔 ' + _esc(n.title || '') + '</div>'
        + '<div style="font-size:.78rem;color:#6b7280;margin-top:2px">' + _esc((n.message || '').substring(0, 70)) + '</div>';
      t.addEventListener('click', function () { if (n.link) window.location.href = n.link; });
      existing.appendChild(t);
      setTimeout(function () {
        t.style.opacity = '0'; t.style.transition = 'opacity .3s';
        setTimeout(function () { t.parentNode && t.parentNode.removeChild(t); }, 300);
      }, 6000);
    }

    function _esc(s) {
      return String(s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
    }

    // กดกระดิ่ง → mark all read
    document.addEventListener('DOMContentLoaded', function () {
      var btn = document.getElementById('adminBellBtn');
      if (btn) {
        btn.addEventListener('click', function () {
          var b = document.getElementById('adminBellBadge');
          if (b && b.style.display !== 'none' && parseInt(b.textContent || '0', 10) > 0) {
            fetch(BASE + 'api/notifications/mark_all_read', {
              method: 'POST',
              headers: { 'X-Requested-With': 'XMLHttpRequest' }
            });
            prevCount = 0;
            b.style.display = 'none';
            b.textContent   = '';
          }
        });
      }
    });

    startSSE();
    setTimeout(() => { document.querySelectorAll('.flash-alert').forEach(a => bootstrap.Alert.getOrCreateInstance(a).close()); }, 4000);
  })();
  </script>
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
          placeholder: placeholder,
          allowEmptyOption: true,
          maxOptions: 300,
          highlight: true,
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
  $.datepicker.setDefaults({dateFormat:"dd/mm/yy",changeMonth:true,changeYear:true,yearRange:"2010:2035",firstDay:1});
  var _HH='<option value="00">00</option><option value="01">01</option><option value="02">02</option><option value="03">03</option><option value="04">04</option><option value="05">05</option><option value="06">06</option><option value="07">07</option><option value="08">08</option><option value="09">09</option><option value="10">10</option><option value="11">11</option><option value="12">12</option><option value="13">13</option><option value="14">14</option><option value="15">15</option><option value="16">16</option><option value="17">17</option><option value="18">18</option><option value="19">19</option><option value="20">20</option><option value="21">21</option><option value="22">22</option><option value="23">23</option>', _MM='<option value="00">00</option><option value="01">01</option><option value="02">02</option><option value="03">03</option><option value="04">04</option><option value="05">05</option><option value="06">06</option><option value="07">07</option><option value="08">08</option><option value="09">09</option><option value="10">10</option><option value="11">11</option><option value="12">12</option><option value="13">13</option><option value="14">14</option><option value="15">15</option><option value="16">16</option><option value="17">17</option><option value="18">18</option><option value="19">19</option><option value="20">20</option><option value="21">21</option><option value="22">22</option><option value="23">23</option><option value="24">24</option><option value="25">25</option><option value="26">26</option><option value="27">27</option><option value="28">28</option><option value="29">29</option><option value="30">30</option><option value="31">31</option><option value="32">32</option><option value="33">33</option><option value="34">34</option><option value="35">35</option><option value="36">36</option><option value="37">37</option><option value="38">38</option><option value="39">39</option><option value="40">40</option><option value="41">41</option><option value="42">42</option><option value="43">43</option><option value="44">44</option><option value="45">45</option><option value="46">46</option><option value="47">47</option><option value="48">48</option><option value="49">49</option><option value="50">50</option><option value="51">51</option><option value="52">52</option><option value="53">53</option><option value="54">54</option><option value="55">55</option><option value="56">56</option><option value="57">57</option><option value="58">58</option><option value="59">59</option>';
  function initDTPickers(root){
    var ctx=root?$(root):$(document);
    ctx.find(".jq-dt-wrap:not([data-dt-init])").each(function(){
      $(this).attr("data-dt-init","1");
      var $w=$(this),$d=$w.find(".dt-date"),$h=$w.find(".dt-hidden");
      // บังคับ readonly — ห้ามพิมพ์โดยตรง
      $d.prop("readonly",true).css("cursor","pointer");
      var cv=$h.val()||"",ch=0,cm=0;
      if(cv&&cv.indexOf(" ")>0){var t=cv.split(" ")[1];ch=parseInt(t.substr(0,2),10);cm=parseInt(t.substr(3,2),10);}
      // ใช้แค่ class dt-hh/dt-mm ไม่ใส่ form-select-sm เพื่อหลีกเลี่ยง Bootstrap override appearance
      var $tw=$('<div class="dt-time-wrap"></div>');
      var $selH=$('<select class="dt-hh"></select>');
      for(var h=0;h<=23;h++){var hv=(h<10?"0":"")+h;var $o=$("<option>").val(hv).text(hv);if(h===ch)$o.prop("selected",true);$selH.append($o);}
      var $selM=$('<select class="dt-mm"></select>');
      for(var m=0;m<=59;m++){var mv=(m<10?"0":"")+m;var $p=$("<option>").val(mv).text(mv);if(m===cm)$p.prop("selected",true);$selM.append($p);}
      $tw.append($selH);
      $tw.append('<span class="dt-colon">:</span>');
      $tw.append($selM);
      $w.append($tw);
      var $sh=$w.find(".dt-hh"),$sm=$w.find(".dt-mm");
      function merge(){
        var dv=$d.val(),hh=$sh.val(),mm=$sm.val();
        if(!dv){$h.val("");return;}
        var p=dv.split("/");
        if(p.length===3)$h.val(p[2]+"-"+p[1]+"-"+p[0]+" "+hh+":"+mm+":00");
      }
      $d.datepicker({dateFormat:"dd/mm/yy",onSelect:function(d){merge();}});
      $sh.on("change",merge);$sm.on("change",merge);
      merge();
    });
    // jq-date-only: bind datepicker, บางตัวมี hidden pair (id naming: xxxDisplay → xxxHidden)
    ctx.find(".jq-date-only:not(.hasDatepicker)").each(function(){
      var $inp=$(this);
      $inp.prop("readonly",true).css("cursor","pointer");
      var hidId=$inp.attr("id")?$inp.attr("id").replace(/Display$/,"Hidden"):null;
      var $hid=hidId?$("#"+hidId):null;
      $inp.datepicker({
        dateFormat:"dd/mm/yy",
        onSelect:function(d){
          if($hid&&$hid.length){
            var p=d.split("/");
            $hid.val(p.length===3?p[2]+"-"+p[1]+"-"+p[0]:"");
          }
        }
      });
    });
  }
  window.addEventListener("load",function(){if(typeof $!=="undefined"){initDTPickers();$(document).on("shown.bs.modal",function(e){initDTPickers(e.target);});}});
  </script>
  <?php if (!empty($extra_js))
    echo $extra_js; ?>
</body>

</html>