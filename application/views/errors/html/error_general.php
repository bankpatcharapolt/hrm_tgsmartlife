<?php
$heading = isset($heading) ? $heading : 'Error';
$message = isset($message) ? $message : '';
?>
<!DOCTYPE html>
<html lang="th"><head><meta charset="UTF-8"><title><?php echo $heading; ?></title>
<style>body{font-family:sans-serif;background:#f0f4ff;display:flex;align-items:center;justify-content:center;min-height:100vh;margin:0;}
.box{background:#fff;padding:2.5rem;border-radius:12px;text-align:center;max-width:500px;width:90%;box-shadow:0 4px 20px rgba(0,0,0,.1);}
h1{color:#dc2626;font-size:1.5rem;}p{color:#6b7280;line-height:1.6;}a{display:inline-block;margin-top:1rem;padding:.5rem 1.5rem;background:#1a56db;color:#fff;border-radius:8px;text-decoration:none;}</style>
</head><body>
<div class="box">
  <h1><?php echo $heading; ?></h1>
  <p><?php echo $message; ?></p>
  <a href="javascript:history.back()">← กลับ</a>
</div>
</body></html>
