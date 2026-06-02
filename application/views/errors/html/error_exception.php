<?php
$heading = isset($heading) ? $heading : 'Exception';
?>
<!DOCTYPE html>
<html lang="th"><head><meta charset="UTF-8"><title>Error</title>
<style>body{font-family:sans-serif;background:#fff8f8;margin:0;padding:20px;}
.box{background:#fff;border-left:4px solid #dc2626;padding:16px;border-radius:4px;box-shadow:0 1px 4px rgba(0,0,0,.1);max-width:900px;margin:0 auto;}
h4{color:#dc2626;margin:0 0 8px;}p{margin:4px 0;font-size:.875rem;color:#374151;line-height:1.6;}</style>
</head><body>
<div class="box">
  <h4><?php echo $heading; ?></h4>
  <p><?php echo isset($message) ? $message : ''; ?></p>
</div>
</body></html>
