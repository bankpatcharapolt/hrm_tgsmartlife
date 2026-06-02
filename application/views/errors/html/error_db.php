<?php
$heading = isset($heading) ? $heading : 'Database Error';
$message = isset($message) ? $message : '';
?>
<!DOCTYPE html>
<html lang="th"><head><meta charset="UTF-8"><title>Database Error</title>
<style>body{font-family:sans-serif;background:#fff8f8;margin:0;padding:20px;}
.box{background:#fff;border-left:4px solid #dc2626;padding:16px;border-radius:4px;max-width:900px;margin:0 auto;}</style>
</head><body>
<div class="box">
  <b style="color:#dc2626"><?php echo $heading; ?></b>
  <p style="font-size:.875rem;color:#374151"><?php echo $message; ?></p>
</div>
</body></html>
