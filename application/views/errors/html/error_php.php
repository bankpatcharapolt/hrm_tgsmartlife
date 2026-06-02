<?php
// CI3 passes: $severity, $message, $filepath, $line
// ต้องสร้าง $heading เองถ้าไม่มี
$heading = isset($heading) ? $heading : 'PHP Error';
?>
<div style="border-left:4px solid #dc2626;padding:12px 16px;margin:8px;font-family:monospace;background:#fff8f8;font-size:13px">
  <b style="color:#dc2626"><?php echo $heading; ?></b><br>
  <span><?php echo isset($message) ? $message : ''; ?></span>
</div>
