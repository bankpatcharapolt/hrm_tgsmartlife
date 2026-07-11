<!DOCTYPE html>
<html lang="th">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?=$title ?? 'แผนที่พนักงาน'?></title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Sarabun:wght@300;400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://code.jquery.com/ui/1.14.2/themes/base/jquery-ui.css">
  <style>
    body { font-family: Sarabun, sans-serif; font-size: .875rem; background: #f8fafc; margin: 0; }
    .top-bar {
      background: #0f172a; color: #fff; padding: .5rem 1rem;
      display: flex; align-items: center; gap: .75rem;
    }
    .top-bar .logo { font-weight: 700; font-size: 1rem; letter-spacing: .5px; }
    .top-bar .sub  { font-size: .75rem; color: #94a3b8; }
    .page-content { padding: .75rem; }
    /* datepicker */
    .ui-datepicker { font-family: Sarabun,sans-serif!important; font-size:.875rem; z-index:9999!important;
      min-width:240px; box-shadow:0 4px 16px rgba(0,0,0,.12); border-radius:10px; overflow:hidden; }
    .ui-datepicker-header { background:#1a56db!important; color:#fff!important; border:none!important; padding:.5rem; }
    .ui-datepicker select.ui-datepicker-month,
    .ui-datepicker select.ui-datepicker-year {
      font-family:Sarabun,sans-serif; font-size:.8rem; border-radius:4px;
      border:1px solid rgba(255,255,255,.4); background:rgba(255,255,255,.15);
      color:#fff; padding:1px 2px;
    }
    .ui-datepicker select.ui-datepicker-month option,
    .ui-datepicker select.ui-datepicker-year option { background:#1a56db; color:#fff; }
  </style>
</head>
<body>
  <div class="top-bar">
    <i class="bi bi-map-fill fs-5"></i>
    <div>
      <div class="logo">ระบบ HRM</div>
      <div class="sub">แผนที่พนักงาน (Realtime)</div>
    </div>
  </div>
  <div class="page-content">
    <?=$content_view?>
  </div>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/jquery@3.7.1/dist/jquery.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/jquery-ui@1.13.2/dist/jquery-ui.min.js"></script>
</body>
</html>
