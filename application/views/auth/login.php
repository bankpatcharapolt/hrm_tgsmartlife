<!DOCTYPE html>
<html lang="th"><head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title><?=$title??'เข้าสู่ระบบ'?></title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Sarabun:wght@300;400;500;600;700&display=swap" rel="stylesheet">
<style>
*{box-sizing:border-box;}
body{font-family:Sarabun,sans-serif;background:#0f172a;min-height:100vh;display:flex;align-items:center;justify-content:center;padding:1rem;background-image:radial-gradient(circle at 20% 50%,rgba(26,86,219,.15),transparent 50%),radial-gradient(circle at 80% 20%,rgba(245,158,11,.07),transparent 40%);}
.lc{background:#fff;border-radius:16px;width:100%;max-width:420px;overflow:hidden;box-shadow:0 25px 60px rgba(0,0,0,.45);border:none;}
.ch{background:linear-gradient(135deg,#1a56db,#1e429f);padding:2rem;text-align:center;position:relative;}
.ch::after{content:'';position:absolute;bottom:-1px;left:0;right:0;height:18px;background:#fff;border-radius:50% 50% 0 0/100% 100% 0 0;}
.bi-ico{width:56px;height:56px;background:rgba(255,255,255,.18);border-radius:14px;display:inline-flex;align-items:center;justify-content:center;font-size:1.6rem;margin-bottom:.7rem;backdrop-filter:blur(8px);}
.ch-t{color:#fff;font-size:1.2rem;font-weight:700;margin:0;}.ch-s{color:rgba(255,255,255,.62);font-size:.8rem;}
.cb{padding:1.75rem 2rem 2rem;}
.form-label{font-size:.855rem;font-weight:600;color:#374151;margin-bottom:.32rem;display:block;}
.iw{position:relative;}
.iw .pre{position:absolute;left:.82rem;top:50%;transform:translateY(-50%);color:#9ca3af;font-size:.95rem;}
.form-control{height:44px;padding-left:2.5rem;border:1.5px solid #e5e7eb;border-radius:9px;font-family:Sarabun,sans-serif;font-size:.875rem;background:#fafafa;transition:.2s;}
.form-control:focus{border-color:#1a56db;background:#fff;box-shadow:0 0 0 3px rgba(26,86,219,.1);outline:none;}
.tpw{position:absolute;right:.82rem;top:50%;transform:translateY(-50%);background:none;border:none;color:#9ca3af;cursor:pointer;padding:0;}
.btn-lg{width:100%;height:46px;background:linear-gradient(135deg,#1a56db,#1e429f);border:none;border-radius:9px;color:#fff;font-family:Sarabun,sans-serif;font-size:.95rem;font-weight:600;cursor:pointer;transition:.2s;display:flex;align-items:center;justify-content:center;gap:.45rem;margin-top:1.2rem;}
.btn-lg:hover{opacity:.92;transform:translateY(-1px);}
.btn-lg:disabled{opacity:.65;cursor:not-allowed;transform:none;}
.alert{border:none;border-radius:9px;font-size:.855rem;padding:.65rem .9rem;display:flex;align-items:flex-start;gap:.45rem;margin-bottom:1rem;}
.cf{background:#f9fafb;border-top:1px solid #e5e7eb;text-align:center;padding:.8rem;font-size:.74rem;color:#9ca3af;}
.demo-box{background:#f0f9ff;border:1px solid #bae6fd;border-radius:8px;padding:.6rem .9rem;font-size:.8rem;color:#0369a1;margin-top:.75rem;}
</style></head><body>
<div class="lc">
  <div class="ch">
   
    <h1 class="ch-t">ระบบ HRM</h1>
    <p class="ch-s">Human Resource Management</p>
  </div>
  <div class="cb">
    <h2 style="font-size:.95rem;font-weight:600;text-align:center;color:#111827;margin-bottom:1.25rem">กรุณาเข้าสู่ระบบ</h2>
    <?php if(!empty($error)):?><div class="alert alert-danger"><i class="bi bi-exclamation-circle-fill mt-1"></i><span><?=htmlspecialchars($error)?></span></div><?php endif;?>
    <?php if(!empty($success)):?><div class="alert alert-success"><i class="bi bi-check-circle-fill mt-1"></i><span><?=htmlspecialchars($success)?></span></div><?php endif;?>
    <?=form_open('auth/process_login',['id'=>'lf','novalidate'=>''])?>
    <input type="hidden" name="<?=$this->security->get_csrf_token_name()?>" value="<?=$this->security->get_csrf_hash()?>">
    <div class="mb-3">
      <label class="form-label">ชื่อผู้ใช้</label>
      <div class="iw"><i class="bi bi-person pre"></i><input type="text" name="username" class="form-control" placeholder="กรอกชื่อผู้ใช้" value="<?=set_value('username','owner')?>" autocomplete="username" required></div>
    </div>
    <div class="mb-3">
      <label class="form-label">รหัสผ่าน</label>
      <div class="iw"><i class="bi bi-lock pre"></i><input type="password" name="password" id="pw" class="form-control" placeholder="กรอกรหัสผ่าน" autocomplete="current-password" required><button type="button" class="tpw" id="tpw"><i class="bi bi-eye" id="pwi"></i></button></div>
    </div>
    <button type="submit" class="btn-lg" id="bl"><i class="bi bi-box-arrow-in-right"></i><span id="blt">เข้าสู่ระบบ</span></button>
    <?=form_close()?>
 </div>
  
</div>
<script>
document.getElementById('tpw').onclick=function(){var i=document.getElementById('pw'),ic=document.getElementById('pwi');i.type=i.type==='password'?'text':'password';ic.className=i.type==='password'?'bi bi-eye':'bi bi-eye-slash';};
document.getElementById('lf').onsubmit=function(){var b=document.getElementById('bl'),t=document.getElementById('blt');b.disabled=true;t.innerHTML='<span class="spinner-border spinner-border-sm me-1"></span>กำลังเข้าสู่ระบบ...';};
</script>
</body></html>
