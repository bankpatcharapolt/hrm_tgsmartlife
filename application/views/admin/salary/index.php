<?php defined('BASEPATH') OR exit(); ?>
<?php
$mn_list = array('0'=>'ทุกเดือน','1'=>'ม.ค.','2'=>'ก.พ.','3'=>'มี.ค.','4'=>'เม.ย.',
                 '5'=>'พ.ค.','6'=>'มิ.ย.','7'=>'ก.ค.','8'=>'ส.ค.','9'=>'ก.ย.',
                 '10'=>'ต.ค.','11'=>'พ.ย.','12'=>'ธ.ค.');
?>
<div class="d-flex gap-2 mb-3 flex-wrap align-items-center justify-content-between">
  <div class="d-flex gap-2 flex-wrap align-items-center">
    <select id="selY" class="form-select form-select-sm" style="width:auto">
      <?php for($y=date('Y');$y>=date('Y')-5;$y--):?>
      <option value="<?=$y?>" <?=(int)$year===$y?'selected':''?>><?=$y?></option>
      <?php endfor;?>
    </select>
    <select id="selM" class="form-select form-select-sm" style="width:auto">
      <?php foreach($mn_list as $k=>$v):?>
      <option value="<?=$k?>" <?=(string)$month===(string)$k?'selected':''?>><?=$v?></option>
      <?php endforeach;?>
    </select>
    <!-- data-base ใส่ URL จริงจาก PHP ตรงนี้เลย JS อ่านจาก attribute -->
    <button id="btnSearch"
            data-base="<?=base_url('admin/salary')?>"
            class="btn btn-primary btn-sm px-3">
      <i class="bi bi-search me-1"></i>ค้นหา
    </button>
    <?php if((int)$month > 0 || (int)$year != (int)date('Y')): ?>
    <a href="<?=base_url('admin/salary')?>" class="btn btn-outline-secondary btn-sm">
      <i class="bi bi-x me-1"></i>ล้าง
    </a>
    <?php endif; ?>
  </div>
  <div class="d-flex gap-2 flex-wrap">
    <a href="<?=base_url('admin/salary/slips')?>" class="btn btn-outline-secondary btn-sm">
      <i class="bi bi-file-earmark-pdf me-1"></i>รายการสลิป
    </a>
    <a href="<?=base_url('admin/salary/create')?>" class="btn btn-primary btn-sm">
      <i class="bi bi-plus-lg me-1"></i>บันทึกเงินเดือน
    </a>
    <button class="btn btn-outline-secondary btn-sm" data-bs-toggle="modal" data-bs-target="#slipMod">
      <i class="bi bi-upload me-1"></i>อัปโหลดสลิป
    </button>
  </div>
</div>

<?php if($summary && $summary->total_emp > 0):?>
<div class="row g-2 mb-3">
  <div class="col-6 col-md-3"><div class="stat-card"><div class="s-ico" style="background:#eff6ff;color:#1a56db"><i class="bi bi-people"></i></div><div><div class="s-lbl">พนักงาน</div><div class="s-val"><?=$summary->total_emp?></div></div></div></div>
  <div class="col-6 col-md-3"><div class="stat-card"><div class="s-ico" style="background:#f0fdf4;color:#16a34a"><i class="bi bi-arrow-up-circle"></i></div><div><div class="s-lbl">รายได้รวม</div><div class="s-val" style="font-size:1.1rem">฿<?=number_format($summary->total_gross,0)?></div></div></div></div>
  <div class="col-6 col-md-3"><div class="stat-card"><div class="s-ico" style="background:#fef2f2;color:#dc2626"><i class="bi bi-arrow-down-circle"></i></div><div><div class="s-lbl">หักรวม</div><div class="s-val" style="font-size:1.1rem">฿<?=number_format($summary->total_ss+$summary->total_tax,0)?></div></div></div></div>
  <div class="col-6 col-md-3"><div class="stat-card"><div class="s-ico" style="background:#fffbeb;color:#d97706"><i class="bi bi-wallet2"></i></div><div><div class="s-lbl">สุทธิ</div><div class="s-val text-primary" style="font-size:1.1rem">฿<?=number_format($summary->total_net,0)?></div></div></div></div>
</div>
<?php endif;?>

<div class="card">
  <div class="card-header">
    <i class="bi bi-cash-stack me-2"></i>รายการเงินเดือน
    <?php
    $mn_hdr = array(1=>'ม.ค.',2=>'ก.พ.',3=>'มี.ค.',4=>'เม.ย.',5=>'พ.ค.',6=>'มิ.ย.',
                    7=>'ก.ค.',8=>'ส.ค.',9=>'ก.ย.',10=>'ต.ค.',11=>'พ.ย.',12=>'ธ.ค.');
    echo ((int)$month > 0 && isset($mn_hdr[(int)$month]))
        ? $mn_hdr[(int)$month].' '.$year
        : 'ทุกเดือน '.$year;
    ?>
  </div>
  <div class="card-body p-0">
    <div class="table-responsive">
      <table class="table table-hover mb-0">
        <thead>
          <tr><th>เดือน/ปี</th><th>พนักงาน</th><th>แผนก</th><th>เงินเดือน</th><th>รายได้รวม</th><th>หักรวม</th><th>สุทธิ</th><th>สถานะ</th><th>จัดการ</th></tr>
        </thead>
        <tbody>
          <?php
          $mn2 = array(1=>'ม.ค.',2=>'ก.พ.',3=>'มี.ค.',4=>'เม.ย.',5=>'พ.ค.',6=>'มิ.ย.',
                       7=>'ก.ค.',8=>'ส.ค.',9=>'ก.ย.',10=>'ต.ค.',11=>'พ.ย.',12=>'ธ.ค.');
          if(!empty($records)):foreach($records as $r):
            $ded = $r->social_security_deduct+$r->tax_deduct+$r->other_deduct+$r->absent_deduct+$r->late_deduct;
          ?>
          <tr>
            <td style="white-space:nowrap">
              <span class="badge bg-secondary"><?=$mn2[$r->salary_month].' '.$r->salary_year?></span>
            </td>
            <td>
              <div class="fw-semibold" style="font-size:.875rem"><?=$r->first_name.' '.$r->last_name?></div>
              <div style="font-size:.72rem;color:#6b7280"><?=$r->employee_id?></div>
            </td>
            <td style="font-size:.82rem"><?php echo isset($r->dept_name) ? $r->dept_name : '–'; ?></td>
            <td>฿<?=number_format($r->base_salary,0)?></td>
            <td class="text-success">฿<?=number_format($r->gross_salary,2)?></td>
            <td class="text-danger">-฿<?=number_format($ded,2)?></td>
            <td class="fw-semibold text-primary">฿<?=number_format($r->net_salary,2)?></td>
            <td>
              <?php
              if ($r->payment_status === 'paid') {
                  echo '<span class="badge bg-success">จ่ายแล้ว</span>';
              } elseif ($r->payment_status === 'processed') {
                  echo '<span class="badge bg-warning text-dark">ประมวลผล</span>';
              } else {
                  echo '<span class="badge bg-secondary">ร่าง</span>';
              }
              ?>
            </td>
            <td>
              <a href="<?=base_url('admin/salary/edit/'.$r->id)?>" class="btn btn-outline-secondary btn-sm px-2 py-0"><i class="bi bi-pencil"></i></a>
              <?php if($r->payment_status !== 'paid'):?>
              <form method="POST" action="<?=base_url('admin/salary/mark_paid/'.$r->id)?>" class="d-inline">
                <input type="hidden" name="<?=$this->security->get_csrf_token_name()?>" value="<?=$this->security->get_csrf_hash()?>">
                <button class="btn btn-outline-success btn-sm px-2 py-0 ms-1" title="จ่ายแล้ว"><i class="bi bi-check2"></i></button>
              </form>
              <?php endif;?>
            </td>
          </tr>
          <?php endforeach; else:?>
          <tr><td colspan="9" class="text-center text-muted py-5">
            <i class="bi bi-inbox fs-1 d-block mb-2"></i>ไม่มีข้อมูลเงินเดือน
          </td></tr>
          <?php endif;?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<!-- Upload Slip Modal -->
<div class="modal fade" id="slipMod" tabindex="-1">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title"><i class="bi bi-upload me-2"></i>อัปโหลดสลิปเงินเดือน</h5>
        <button class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <form method="POST" action="<?=base_url('admin/salary/upload_slip')?>" enctype="multipart/form-data">
        <input type="hidden" name="<?=$this->security->get_csrf_token_name()?>" value="<?=$this->security->get_csrf_hash()?>">
        <div class="modal-body">
          <div class="alert alert-info py-2 px-3 mb-3" style="font-size:.83rem">
            <i class="bi bi-info-circle me-1"></i>
            <strong>รูปแบบชื่อไฟล์:</strong>
            <code>{เลขบัตร13หลัก}_{MON}{ปีค.ศ.}.pdf</code><br>
            <strong>ตัวอย่าง:</strong> <code>1101800657588_MAY2026.pdf</code>
          </div>
          <!-- ตารางตัวย่อเดือน -->
          <div class="mb-3 p-2 rounded" style="background:#f8fafc;border:1px solid #e2e8f0;font-size:.78rem">
            <div class="fw-semibold mb-1" style="color:#475569"><i class="bi bi-calendar3 me-1"></i>ตัวย่อเดือน (MON) ที่ใช้ในชื่อไฟล์</div>
            <div class="row g-1">
              <?php
              $months = array(
                'JAN'=>'ม.ค. (มกราคม)',  'FEB'=>'ก.พ. (กุมภาพันธ์)',
                'MAR'=>'มี.ค. (มีนาคม)',  'APR'=>'เม.ย. (เมษายน)',
                'MAY'=>'พ.ค. (พฤษภาคม)', 'JUN'=>'มิ.ย. (มิถุนายน)',
                'JUL'=>'ก.ค. (กรกฎาคม)', 'AUG'=>'ส.ค. (สิงหาคม)',
                'SEP'=>'ก.ย. (กันยายน)',  'OCT'=>'ต.ค. (ตุลาคม)',
                'NOV'=>'พ.ย. (พฤศจิกายน)','DEC'=>'ธ.ค. (ธันวาคม)',
              );
              foreach ($months as $abbr => $name): ?>
              <div class="col-6">
                <span class="badge me-1" style="background:#1a56db;font-size:.72rem"><?=$abbr?></span>
                <span style="color:#374151"><?=$name?></span>
              </div>
              <?php endforeach; ?>
            </div>
          </div>
          <label class="form-label fw-semibold">เลือกไฟล์ PDF (หลายไฟล์ได้)</label>
          <input type="file" name="slip_files[]" id="slipFiles" class="form-control" accept=".pdf" multiple required>
          <div id="slipPreview" class="mt-2" style="display:none">
            <div id="slipList" class="border rounded p-2" style="max-height:180px;overflow-y:auto;background:#f9fafb;font-size:.82rem"></div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="submit" class="btn btn-primary"><i class="bi bi-upload me-1"></i>อัปโหลด</button>
          <button class="btn btn-outline-secondary" data-bs-dismiss="modal">ยกเลิก</button>
        </div>
      </form>
    </div>
  </div>
</div>

<script>
(function(){
  var btn  = document.getElementById('btnSearch');
  var selY = document.getElementById('selY');
  var selM = document.getElementById('selM');

  if(btn){
    btn.addEventListener('click', function(){
      /* อ่าน base URL จาก data-base attribute — ไม่ต้องใช้ extra_js */
      var base = this.getAttribute('data-base');
      var y    = selY ? selY.value : '';
      var m    = selM ? selM.value : '';
      window.location.href = base + '?year=' + y + '&month=' + m;
    });
  }

  var sf = document.getElementById('slipFiles');
  if(sf){
    sf.addEventListener('change', function(){
      var files = this.files;
      var list  = document.getElementById('slipList');
      var prev  = document.getElementById('slipPreview');
      if(!files.length){ prev.style.display='none'; return; }
      prev.style.display = '';
      var html = '';
      var pat  = /^\d{13}_[A-Z]{3}\d{4}\.pdf$/i;
      for(var i=0;i<files.length;i++){
        var f  = files[i];
        var ok = pat.test(f.name);
        var sz = (f.size/1024/1024).toFixed(2);
        var ic = ok ? 'file-earmark-pdf text-danger' : 'exclamation-triangle text-warning';
        var nm = ok ? f.name : '<span style="color:#d97706;font-weight:600">'+f.name+'</span>';
        var st = ok ? '<small style="color:green">✓ format ถูกต้อง</small>'
                    : '<small style="color:#d97706">⚠ format ไม่ถูกต้อง</small>';
        html += '<div style="display:flex;align-items:center;gap:8px;padding:4px 0;border-bottom:1px solid #f3f4f6">';
        html += '<i class="bi bi-'+ic+'"></i>';
        html += '<div style="flex:1">'+nm+'<br>'+st+'</div>';
        html += '<small style="color:#6b7280">'+sz+' MB</small>';
        html += '</div>';
      }
      list.innerHTML = html;
    });
  }
})();
</script>
