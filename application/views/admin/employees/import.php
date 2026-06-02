<?php defined('BASEPATH') OR exit(); ?>
<div class="row g-3">
  <div class="col-lg-7">
    <div class="card">
      <div class="card-header"><i class="bi bi-upload me-2 text-primary"></i>นำเข้าข้อมูลพนักงาน (Excel)</div>
      <div class="card-body">

        

        <div class="alert alert-info py-2 px-3 mb-4" style="font-size:.83rem">
          <i class="bi bi-info-circle me-1"></i>
          <strong>รูปแบบไฟล์:</strong> Excel (.xlsx) ที่มี sheet <code>รายงานพนักงาน</code><br>
          <ul class="mb-0 mt-1 ps-3">
            <li>รหัสพนักงานซ้ำ = <strong>อัปเดต</strong> ข้อมูล (ไม่สร้างซ้ำ)</li>
            <li>ไม่มี username = ใช้ส่วนหน้า @ ของ email หรือรหัสพนักงาน</li>
            <li>ไม่มี password = ใช้ <strong>1234</strong></li>
            <li>ถ้าไม่พบสาขา/ทีมที่ไม่มี = สร้างใหม่อัตโนมัติ</li>
          </ul>
        </div>

        <?=form_open_multipart('admin/employees_import/do_import')?>
        <input type="hidden" name="<?=$this->security->get_csrf_token_name()?>" value="<?=$this->security->get_csrf_hash()?>">
        <div class="mb-4">
          <label class="form-label fw-semibold">เลือกไฟล์ Excel (.xlsx)</label>
          <input type="file" name="excel_file" id="xlFile" class="form-control" accept=".xlsx,.xls" required>
          <div class="form-text">รองรับ .xlsx ขนาดไม่เกิน 20MB</div>
        </div>
        <div id="fileInfo" style="display:none" class="mb-3">
          <div class="d-flex align-items-center gap-2 p-2 rounded" style="background:#f0fdf4;border:1px solid #86efac">
            <i class="bi bi-file-earmark-spreadsheet text-success fs-4"></i>
            <div><div class="fw-semibold small" id="fName"></div><div class="text-muted" style="font-size:.78rem" id="fSize"></div></div>
          </div>
        </div>
        <div class="d-flex gap-2 flex-wrap">
          <button type="submit" class="btn btn-primary" id="impBtn">
            <i class="bi bi-upload me-1"></i>นำเข้าข้อมูล
          </button>
          <a href="<?=base_url('admin/employees')?>" class="btn btn-outline-secondary">ยกเลิก</a>
          <a href="<?=base_url('admin/employees_import/export')?>" class="btn btn-outline-success ms-auto">
            <i class="bi bi-download me-1"></i>Export Excel
          </a>
        </div>
        <?=form_close()?>
      </div>
    </div>
  </div>

  <div class="col-lg-5">
    <div class="card">
      <div class="card-header"><i class="bi bi-table me-2"></i>30 Columns ที่รองรับ</div>
      <div class="card-body p-0">
        <div style="max-height:500px;overflow-y:auto">
          <table class="table table-sm mb-0">
            <thead><tr><th>#</th><th>คอลัมน์</th><th>ตัวอย่าง</th></tr></thead>
            <tbody style="font-size:.77rem">
              <?php $cols=[
                ['รหัสพนักงาน *','SL002'],['คำนำหน้า','นาย'],['ชื่อจริง *','สมชาย'],
                ['นามสกุล *','ใจดี'],['ชื่อจริง (EN)','Somchai'],['นามสกุล (EN)','Jaidee'],
                ['เลขบัตรประชาชน','1101800657588'],array('แผนก','การขาย'),['ตำแหน่ง','ผู้จัดการ'],
                ['ประเภทพนักงาน','รายเดือน'],['อีเมล','name@mail.com'],['เบอร์โทร','0812345678'],
                ['ผู้ติดต่อฉุกเฉิน','ชื่อ นามสกุล'],['เบอร์ติดต่อฉุกเฉิน','0898765432'],
                ['ที่อยู่','123 ถ.สุขุมวิท'],['แขวง/ตำบล','คลองเตย'],['เขต/อำเภอ','คลองเตย'],
                ['จังหวัด','กรุงเทพมหานคร'],['รหัสไปรษณีย์','10110'],
                ['วันที่เริ่มทำงาน','01/10/2022'],['เงินเดือน','25000'],
                ['บัญชีเงินเดือน','530101 เงินเดือน'],['ประกันสังคม','ขึ้นทะเบียน...'],
                ['หัก ณ ที่จ่าย','0'],['ช่องทางรับเงิน','ธ.กสิกรไทย'],
                ['เลขที่บัญชี','1234567890'],['สถานะ','พนักงาน/ลาออก'],
                ['ทีม','สาขาระยอง'],['username','(ว่างได้)'],['password','(ว่างได้ = 1234)'],
              ]; foreach($cols as $i=>$c):?>
              <tr>
                <td class="text-muted"><?=$i+1?></td>
                <td><?=$c[0]?></td>
                <td class="text-muted"><?=$c[1]?></td>
              </tr>
              <?php endforeach;?>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</div>
<script>
document.getElementById('xlFile').onchange=function(){
  var f=this.files[0];
  if(f){document.getElementById('fName').textContent=f.name;document.getElementById('fSize').textContent=(f.size/1024/1024).toFixed(2)+' MB';document.getElementById('fileInfo').style.display='';}
};
document.querySelector('form').onsubmit=function(){
  var b=document.getElementById('impBtn');b.disabled=true;
  b.innerHTML='<span class="spinner-border spinner-border-sm me-1"></span>กำลังนำเข้า กรุณารอ...';
};
</script>
