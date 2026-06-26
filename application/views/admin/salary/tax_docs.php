<?php defined('BASEPATH') OR exit(); ?>
<div class="row g-3">

  <!-- ── Upload Form ── -->
  <div class="col-lg-5">
    <div class="card">
      <div class="card-header"><i class="bi bi-upload me-2"></i>อัปโหลดใบทวิ 50</div>
      <div class="card-body">
        <form method="POST" action="<?=base_url('admin/salary/upload_tax')?>" enctype="multipart/form-data" id="taxUploadForm">
          <input type="hidden" name="<?=$this->security->get_csrf_token_name()?>" value="<?=$this->security->get_csrf_hash()?>">

          <!-- รูปแบบชื่อไฟล์ -->
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
          <div class="mb-3">
            <label class="form-label">ปีภาษี <span class="text-danger">*</span></label>
            <input type="number" name="tax_year" class="form-control" value="<?=$year?>" required>
            <div class="form-text text-muted">ไฟล์ทั้งหมดในชุดนี้จะใช้ปีภาษีเดียวกัน</div>
          </div>

          <!-- Drop zone อัปโหลดหลายไฟล์ -->
          <div class="mb-3">
            <label class="form-label">ไฟล์ PDF ทวิ 50 <span class="text-danger">*</span></label>
            <div id="dropZone"
                 class="rounded-3 text-center py-4 px-3"
                 style="border:2px dashed #bae6fd;background:#f0f9ff;cursor:pointer;transition:.2s"
                 ondragover="onDragOver(event)" ondragleave="onDragLeave(event)" ondrop="onDrop(event)"
                 onclick="document.getElementById('taxFiles').click()">
              <i class="bi bi-cloud-upload fs-2 text-primary mb-2 d-block"></i>
              <div class="fw-semibold" style="font-size:.9rem">คลิกหรือลากไฟล์มาวางที่นี่</div>
              <div class="text-muted mt-1" style="font-size:.78rem">รองรับ PDF เท่านั้น · สูงสุด 10MB/ไฟล์ · อัปโหลดได้หลายไฟล์พร้อมกัน</div>
            </div>
            <input type="file" id="taxFiles" name="tax_files[]" multiple accept=".pdf" class="d-none" onchange="onFilePick(this.files)">
          </div>

          <!-- รายการไฟล์ที่เลือก + จับคู่พนักงาน -->
          <div id="fileList" class="mb-3" style="display:none">
            <div class="d-flex justify-content-between align-items-center mb-2">
              <div class="fw-semibold small">ไฟล์ที่เลือก (<span id="fileCount">0</span> ไฟล์)</div>
              <button type="button" class="btn btn-outline-secondary btn-sm py-0" onclick="clearFiles()">
                <i class="bi bi-x me-1"></i>ล้าง
              </button>
            </div>
            <div id="fileItems" class="d-flex flex-column gap-2"></div>
          </div>

          <button type="submit" class="btn btn-primary w-100" id="submitBtn" disabled>
            <i class="bi bi-upload me-1"></i>อัปโหลดทั้งหมด
          </button>
        </form>
      </div>
    </div>
  </div>

  <!-- ── รายการทวิ 50 ── -->
  <div class="col-lg-7">
    <!-- Filter -->
    <div class="card mb-2">
      <div class="card-body py-2">
        <?=form_open('admin/salary/tax_docs', array('method'=>'GET','class'=>'row g-2 align-items-end'))?>
          <div class="col-md-3">
            <select name="year" class="form-select form-select-sm">
              <?php for($y=date('Y');$y>=date('Y')-5;$y--):?>
              <option value="<?=$y?>" <?=$year==$y?'selected':''?>><?=$y?></option>
              <?php endfor;?>
            </select>
          </div>
          <div class="col-md-5">
            <select name="user_id" class="form-select form-select-sm ts-select">
              <option value="">-- ทุกคน --</option>
              <?php foreach($employees as $e):?>
              <option value="<?=$e->id?>" <?=$sel_uid==$e->id?'selected':''?>><?=$e->employee_id?> – <?=$e->first_name.' '.$e->last_name?></option>
              <?php endforeach;?>
            </select>
          </div>
          <div class="col-auto">
            <button type="submit" class="btn btn-primary btn-sm"><i class="bi bi-search"></i></button>
          </div>
        <?=form_close()?>
      </div>
    </div>

    <!-- List -->
    <div class="card">
      <div class="card-header">
        <i class="bi bi-file-earmark-text me-2 text-primary"></i>รายการใบทวิ 50
        <span class="badge bg-secondary ms-1"><?=count($tax_list)?></span>
      </div>
      <div class="card-body p-0">
        <div class="table-responsive">
          <table class="table table-hover mb-0">
            <thead>
              <tr><th>ปีภาษี</th><th>พนักงาน</th><th>แผนก</th><th>ไฟล์</th><th>จัดการ</th></tr>
            </thead>
            <tbody>
              <?php if(!empty($tax_list)):foreach($tax_list as $t):?>
              <tr>
                <td><span class="badge bg-primary"><?=$t->tax_year?></span></td>
                <td>
                  <div class="fw-semibold" style="font-size:.875rem"><?=$t->first_name.' '.$t->last_name?></div>
                  <div style="font-size:.72rem;color:#6b7280"><?=$t->employee_id?></div>
                </td>
                <td style="font-size:.82rem"><?=$t->dept_name??'–'?></td>
                <td>
                  <a href="<?=base_url($t->file_path)?>" target="_blank"
                     class="text-primary text-decoration-none d-flex align-items-center gap-1"
                     style="font-size:.82rem;max-width:180px">
                    <i class="bi bi-file-earmark-pdf text-danger flex-shrink-0"></i>
                    <span class="text-truncate"><?=htmlspecialchars($t->file_name)?></span>
                  </a>
                </td>
                <td>
                  <a href="<?=base_url($t->file_path)?>" target="_blank"
                     class="btn btn-outline-secondary btn-sm px-2 py-0" title="ดู">
                    <i class="bi bi-eye"></i>
                  </a>
                  <a href="<?=base_url('admin/salary/delete_tax/'.$t->id)?>"
                     onclick="return confirm('ลบเอกสารนี้?')"
                     class="btn btn-outline-danger btn-sm px-2 py-0 ms-1" title="ลบ">
                    <i class="bi bi-trash"></i>
                  </a>
                </td>
              </tr>
              <?php endforeach;else:?>
              <tr><td colspan="5" class="text-center text-muted py-5">
                <i class="bi bi-file-earmark-x fs-2 d-block mb-2"></i>ไม่มีเอกสารทวิ 50
              </td></tr>
              <?php endif;?>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</div>

<?php
// ส่ง employee list ไป JS เพื่อ render dropdown แต่ละแถว
$emp_js = array();
foreach($employees as $e) {
    $emp_js[] = array('id'=>(int)$e->id, 'text'=>$e->employee_id.' – '.$e->first_name.' '.$e->last_name);
}
?>

<?php $extra_js = '<script>
var employees = '.json_encode($emp_js, JSON_UNESCAPED_UNICODE).';
var selectedFiles = [];

// ── Drag & Drop ──────────────────────────────────────────
function onDragOver(e){
  e.preventDefault();
  document.getElementById("dropZone").style.borderColor = "var(--pri)";
  document.getElementById("dropZone").style.background  = "#eff6ff";
}
function onDragLeave(e){
  document.getElementById("dropZone").style.borderColor = "#bae6fd";
  document.getElementById("dropZone").style.background  = "#f0f9ff";
}
function onDrop(e){
  e.preventDefault();
  onDragLeave(e);
  addFiles(e.dataTransfer.files);
}
function onFilePick(files){ addFiles(files); }

function addFiles(files){
  for(var i=0;i<files.length;i++){
    var f = files[i];
    if(f.type !== "application/pdf"){ alert("รองรับเฉพาะ PDF: "+f.name); continue; }
    if(f.size > 10485760){ alert("ไฟล์ใหญ่เกิน 10MB: "+f.name); continue; }
    // ตรวจซ้ำ
    var dup = false;
    for(var j=0;j<selectedFiles.length;j++){ if(selectedFiles[j].name===f.name){ dup=true; break; } }
    if(!dup) selectedFiles.push(f);
  }
  renderFileList();
}

function clearFiles(){
  selectedFiles = [];
  document.getElementById("taxFiles").value = "";
  renderFileList();
}

function removeFile(idx){
  selectedFiles.splice(idx,1);
  renderFileList();
}

function renderFileList(){
  var wrap = document.getElementById("fileList");
  var items = document.getElementById("fileItems");
  var cnt   = document.getElementById("fileCount");
  var btn   = document.getElementById("submitBtn");

  if(!selectedFiles.length){ wrap.style.display="none"; btn.disabled=true; return; }

  wrap.style.display = "";
  cnt.textContent    = selectedFiles.length;
  btn.disabled       = false;

  var html = "";
  selectedFiles.forEach(function(f, idx){
    var kb = (f.size/1024).toFixed(0);
    // สร้าง select พนักงาน
    var opts = employees.map(function(e){
      return \'<option value="\'+e.id+\'">\'+e.text+\'</option>\';
    }).join("");

    html += \'<div class="p-2 rounded" style="background:#f9fafb;border:1px solid #e5e7eb">\'+
      \'<div class="d-flex align-items-center gap-2 mb-2">\'+
        \'<i class="bi bi-file-earmark-pdf text-danger"></i>\'+
        \'<div class="flex-fill" style="font-size:.83rem;font-weight:600;min-width:0">\'+
          \'<span class="text-truncate d-block">\'+esc(f.name)+\'</span>\'+
          \'<span class="text-muted" style="font-size:.72rem">\'+kb+\' KB</span>\'+
        \'</div>\'+
        \'<button type="button" class="btn btn-outline-danger btn-sm px-2 py-0" onclick="removeFile(\'+idx+\')">\'+
          \'<i class="bi bi-x"></i>\'+
        \'</button>\'+
      \'</div>\'+
      \'<div>\'+
        \'<label class="form-label" style="font-size:.78rem;margin-bottom:.2rem">พนักงาน <span class="text-danger">*</span></label>\'+
        \'<select name="tax_user_ids[]" class="form-select form-select-sm ts-select" required data-idx="\'+idx+\'">\'+
          \'<option value="">-- เลือกพนักงาน --</option>\'+opts+
        \'</select>\'+
      \'</div>\'+
    \'</div>\';
  });

  items.innerHTML = html;

  // เริ่ม TomSelect ใน fileItems
  if(typeof TomSelect !== "undefined"){
    items.querySelectorAll("select.ts-select:not(.tomselected)").forEach(function(el){
      new TomSelect(el,{placeholder:"-- เลือก / พิมพ์ค้นหา --",allowEmptyOption:true,maxOptions:300});
    });
  }

  // sync file input — สร้าง DataTransfer ใหม่
  syncFileInput();
}

function syncFileInput(){
  try {
    var dt = new DataTransfer();
    selectedFiles.forEach(function(f){ dt.items.add(f); });
    document.getElementById("taxFiles").files = dt.files;
  } catch(e){ /* Firefox fallback — form submit ใช้ hidden fields แทน */ }
}

function esc(s){
  return String(s).replace(/&/g,"&amp;").replace(/</g,"&lt;").replace(/>/g,"&gt;").replace(/"/g,"&quot;");
}

// validate ก่อน submit: ทุกไฟล์ต้องเลือกพนักงาน
document.getElementById("taxUploadForm").addEventListener("submit", function(e){
  var selects = document.querySelectorAll("[name=\'tax_user_ids[]\']");
  for(var i=0;i<selects.length;i++){
    if(!selects[i].value){
      e.preventDefault();
      alert("กรุณาเลือกพนักงานให้ครบทุกไฟล์");
      selects[i].focus();
      return;
    }
  }
});
</script>'; ?>
