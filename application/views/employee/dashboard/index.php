<?php defined('BASEPATH') OR exit(); ?>
<div class="row g-3">
  <!-- Check-in Card -->
  <div class="col-md-5 col-lg-4">
    <div class="ci-card">
      <div class="ci-time" id="liveClock">--:--:--</div>
      <div style="font-size:.83rem;opacity:.8;margin-bottom:1.2rem" id="liveDate"></div>

      <?php if(!$today || !$today->check_in_time): ?>
      <button class="btn btn-light fw-semibold w-100" id="btnCI" onclick="openCheckinModal()">
        <i class="bi bi-box-arrow-in-right me-2"></i>ลงเวลาเข้างาน
      </button>

      <?php elseif(!$today->check_out_time): ?>
      <div class="mb-2" style="font-size:.83rem;opacity:.85">
        <i class="bi bi-clock-history me-1"></i>เข้างาน:
        <strong><?=date('H:i',strtotime($today->check_in_time))?></strong>
        <?php if($today->is_late): ?>
        <span class="badge bg-warning text-dark ms-1">สาย <?=$today->late_minutes?> นาที</span>
        <?php else: ?>
        <span class="badge bg-success ms-1">ตรงเวลา</span>
        <?php endif; ?>
      </div>
      <?php if(!empty($today->checkin_lat)): ?>
      <div class="mb-2" style="font-size:.72rem;opacity:.65">
        <i class="bi bi-geo-alt-fill text-success me-1"></i><?=round($today->checkin_lat,5)?>, <?=round($today->checkin_lng,5)?>
      </div>
      <?php endif; ?>
      <button class="btn btn-warning fw-semibold w-100" id="btnCO" onclick="openCheckoutModal()">
        <i class="bi bi-box-arrow-right me-2"></i>ลงเวลาออกงาน
      </button>

      <?php else: ?>
      <div style="font-size:.83rem;opacity:.85;margin-bottom:.5rem">
        เข้า: <strong><?=date('H:i',strtotime($today->check_in_time))?></strong>
        &nbsp;|&nbsp;
        ออก: <strong><?=date('H:i',strtotime($today->check_out_time))?></strong>
      </div>
      <?php if(!empty($today->is_late)): ?>
      <span class="badge bg-warning text-dark me-1">สาย <?=$today->late_minutes?> นาที</span>
      <?php else: ?>
      <span class="badge bg-success me-1">ตรงเวลา</span>
      <?php endif; ?>
      <?php if(!empty($today->is_early_out)): ?>
      <span class="badge bg-danger">ออกก่อน <?=$today->early_out_minutes?> นาที</span>
      <?php endif; ?>
      <div class="mt-2"><span class="badge bg-success fs-6 px-3 py-2">✓ ลงเวลาแล้ว</span></div>
      <?php endif; ?>
    </div>

    <div class="card mt-3">
      <div class="card-header">สรุปเดือน <?=date('m/Y')?></div>
      <div class="card-body p-0">
        <table class="table table-sm mb-0">
          <tr><td class="text-muted">มาทำงาน</td><td class="fw-semibold text-end text-success"><?=$att_sum['present']?> วัน</td></tr>
          <tr><td class="text-muted">ลา</td><td class="fw-semibold text-end"><?=$att_sum['leave']?> วัน</td></tr>
          <tr><td class="text-muted">มาสาย</td><td class="fw-semibold text-end text-warning"><?=$att_sum['late']?> ครั้ง</td></tr>
          <tr><td class="text-muted">OT</td><td class="fw-semibold text-end"><?=number_format($att_sum['total_ot'],1)?> ชม.</td></tr>
        </table>
      </div>
    </div>
  </div>

  <!-- Right Column -->
  <div class="col-md-7 col-lg-8">
    <div class="card mb-3">
      <div class="card-header d-flex justify-content-between align-items-center">
        <span><i class="bi bi-cash me-2 text-primary"></i>เงินเดือนล่าสุด</span>
        <a href="<?=base_url('employee/salary')?>" class="btn btn-outline-primary btn-sm">ดูทั้งหมด</a>
      </div>
      <div class="card-body p-0">
        <?php if(!empty($salaries)): $s=$salaries[0]; ?>
        <div class="p-3">
          <div class="row text-center g-0">
            <div class="col-4"><div style="font-size:.75rem;color:#6b7280">เงินเดือน <?=$s->salary_month?>/<?=$s->salary_year?></div><div style="font-size:.83rem;color:#6b7280">ฐาน</div><div class="fw-semibold">฿<?=number_format($s->base_salary,0)?></div></div>
            <div class="col-4"><div style="font-size:.75rem;color:#6b7280">รายได้รวม</div><div style="font-size:.83rem;color:#16a34a" class="fw-semibold">฿<?=number_format($s->gross_salary,0)?></div><span class="badge bg-<?=$s->payment_status==='paid'?'success':'warning text-dark'?>"><?=$s->payment_status==='paid'?'จ่ายแล้ว':'รอจ่าย'?></span></div>
            <div class="col-4"><div style="font-size:.75rem;color:#6b7280">เงินเดือนสุทธิ</div><div class="fw-bold text-primary fs-5">฿<?=number_format($s->net_salary,0)?></div></div>
          </div>
        </div>
        <?php else: ?><div class="text-center text-muted py-3 small">ยังไม่มีข้อมูลเงินเดือน</div><?php endif; ?>
      </div>
    </div>
    <div class="card mb-3">
      <div class="card-header d-flex justify-content-between align-items-center">
        <span><i class="bi bi-calendar me-2 text-warning"></i>การลาที่รอดำเนินการ</span>
        <a href="<?=base_url('employee/leave/request')?>" class="btn btn-outline-warning btn-sm"><i class="bi bi-plus me-1"></i>ยื่นลา</a>
      </div>
      <div class="card-body p-0">
        <?php if(!empty($leaves)): foreach($leaves as $l): ?>
        <div class="d-flex align-items-center justify-content-between px-3 py-2 border-bottom">
          <div><div class="fw-semibold" style="font-size:.875rem"><?=$l->leave_type_name?></div><div style="font-size:.78rem;color:#6b7280"><?=date('d/m/Y',strtotime($l->start_date))?><?=$l->start_date!=$l->end_date?' – '.date('d/m/Y',strtotime($l->end_date)):''?> (<?=$l->total_days?> วัน)</div></div>
          <span class="badge bg-warning text-dark">รอการอนุมัติ</span>
        </div>
        <?php endforeach; else: ?>
        <div class="text-center text-muted py-3 small">ไม่มีการลาที่รอดำเนินการ <a href="<?=base_url('employee/leave/request')?>">ยื่นลา</a></div>
        <?php endif; ?>
      </div>
    </div>
    <div class="card">
      <div class="card-header d-flex justify-content-between align-items-center">
        <span><i class="bi bi-bell me-2"></i>การแจ้งเตือนล่าสุด</span>
        <a href="<?=base_url('employee/notifications')?>" class="btn btn-outline-secondary btn-sm">ดูทั้งหมด</a>
      </div>
      <div class="card-body p-0">
        <?php if(!empty($notifs)): foreach($notifs as $n): ?>
        <div class="d-flex align-items-start gap-2 px-3 py-2 border-bottom <?=!$n->is_read?'bg-light':''?>">
          <div class="flex-fill"><div style="font-size:.83rem;font-weight:<?=!$n->is_read?600:400?>"><?=htmlspecialchars($n->title)?></div><div style="font-size:.77rem;color:#6b7280"><?=htmlspecialchars(mb_substr($n->message,0,60))?><?=mb_strlen($n->message)>60?'...':''?></div></div>
          <div style="font-size:.7rem;color:#9ca3af;white-space:nowrap"><?=date('d/m H:i',strtotime($n->created_at))?></div>
        </div>
        <?php endforeach; else: ?>
        <div class="text-center text-muted py-3 small">ไม่มีการแจ้งเตือน</div>
        <?php endif; ?>
      </div>
    </div>
  </div>
</div>

<!-- ══════════════════════════════════════════════════════
     Modal เช็กอิน / เช็กเอาท์
     Step 1: GPS  →  Step 2: Camera  →  Step 3: Confirm
══════════════════════════════════════════════════════ -->
<div class="modal fade" id="attendModal" tabindex="-1"
     data-bs-backdrop="static" data-bs-keyboard="false">
  <div class="modal-dialog modal-dialog-centered" style="max-width:380px">
    <div class="modal-content">

      <div class="modal-header border-0 pb-1">
        <h6 class="modal-title fw-bold" id="modalTitle">ลงเวลาเข้างาน</h6>
        <button type="button" class="btn-close btn-sm"
                data-bs-dismiss="modal" onclick="cancelModal()"></button>
      </div>

      <!-- Step indicator -->
      <div class="px-3 pb-2">
        <div class="d-flex gap-1">
          <div class="step-dot active" id="dot1" style="flex:1;height:4px;border-radius:3px;background:#1a56db;transition:.3s"></div>
          <div class="step-dot" id="dot2" style="flex:1;height:4px;border-radius:3px;background:#e5e7eb;transition:.3s"></div>
          <div class="step-dot" id="dot3" style="flex:1;height:4px;border-radius:3px;background:#e5e7eb;transition:.3s"></div>
        </div>
        <div class="d-flex justify-content-between mt-1" style="font-size:.68rem;color:#9ca3af">
          <span>GPS</span><span>ถ่ายรูป</span><span>ยืนยัน</span>
        </div>
      </div>

      <div class="modal-body pt-1 pb-2">

        <!-- Step 1: GPS -->
        <div id="stepGPS" class="text-center py-3">
          
          <div class="fw-semibold mb-1" id="gpsTitleTxt">กำลังขอตำแหน่ง GPS...</div>
          <div class="text-muted mb-3" style="font-size:.82rem" id="gpsDescTxt">
            กรุณาอนุญาตการเข้าถึงตำแหน่งของคุณ
          </div>
          <div id="gpsSpinner"><div class="spinner-border spinner-border-sm text-primary"></div></div>
          <div id="gpsResult" style="display:none;font-size:.8rem;color:#6b7280">
            <span id="gpsResultIcon"></span>
            <span id="gpsResultCoords" class="ms-1"></span>
          </div>
          <div id="gpsActions" class="mt-3 d-flex gap-2 justify-content-center" style="display:none!important"></div>
        </div>

        <!-- Step 2: Camera -->
        <div id="stepCamera" style="display:none">
          <div class="text-center mb-2">
            <div class="fw-semibold">ถ่ายรูปยืนยันตัวตน</div>
            <div class="text-muted" style="font-size:.78rem">ไม่บังคับ — กดข้ามได้</div>
          </div>
          <div id="camLiveWrap" style="position:relative;background:#000;border-radius:10px;overflow:hidden;min-height:180px">
            <video id="camVideo" autoplay playsinline muted
                   style="width:100%;max-height:220px;object-fit:cover;display:block"></video>
            <!-- ปุ่มชัตเตอร์ -->
            <button type="button" onclick="snapPhoto()"
                    style="position:absolute;bottom:10px;left:50%;transform:translateX(-50%);
                           width:52px;height:52px;border-radius:50%;background:#fff;
                           border:3px solid #1a56db;font-size:1.35rem;cursor:pointer;
                           display:flex;align-items:center;justify-content:center;
                           box-shadow:0 2px 8px rgba(0,0,0,.25)"
                    title="ถ่ายภาพ" id="shutterBtn">📷</button>
          </div>
          <div id="camPreviewWrap" style="display:none">
            <img id="snapImg" style="width:100%;border-radius:10px;max-height:220px;object-fit:cover">
          </div>
          <div class="d-flex gap-2 mt-2">
            <button class="btn btn-outline-secondary btn-sm flex-fill"
                    id="btnSkipPhoto" onclick="skipPhoto()">ข้ามรูปถ่าย</button>
            <button class="btn btn-outline-secondary btn-sm flex-fill"
                    id="btnRetake" onclick="retakePhoto()" style="display:none">
              <i class="bi bi-arrow-repeat me-1"></i>ถ่ายใหม่</button>
            <button class="btn btn-primary btn-sm flex-fill"
                    id="btnUsePhoto" onclick="goToConfirm()" style="display:none">
              ใช้รูปนี้ →</button>
          </div>
        </div>

        <!-- Step 3: Confirm -->
        <div id="stepConfirm" style="display:none">
          <div class="text-center py-2">
            <div style="font-size:2.2rem;line-height:1;margin-bottom:.4rem" id="confirmIcon">🟢</div>
            <div class="fw-bold" style="font-size:1.4rem;font-variant-numeric:tabular-nums" id="confirmTime">--:--</div>
            <div class="text-muted mb-3" style="font-size:.84rem" id="confirmLabel">ยืนยันลงเวลาเข้างาน</div>
          </div>
          <!-- GPS summary -->
          <div id="confirmGPSRow" class="d-flex align-items-center gap-2 rounded px-3 py-2 mb-2"
               style="background:#f0fdf4;display:none!important">
            <i class="bi bi-geo-alt-fill text-success"></i>
            <span id="confirmGPSTxt" style="font-size:.8rem"></span>
          </div>
          <!-- Photo summary -->
          <div id="confirmPhotoRow" class="mb-2" style="display:none">
            <img id="confirmPhoto" style="width:100%;border-radius:8px;max-height:90px;object-fit:cover">
          </div>
          <!-- Spinner ส่ง -->
          <div id="confirmSpinner" class="text-center py-2" style="display:none">
            <div class="spinner-border text-primary spinner-border-sm me-2"></div>
            <span class="text-muted" style="font-size:.85rem">กำลังบันทึก...</span>
          </div>
        </div>

      </div><!-- /modal-body -->

      <div class="modal-footer border-0 pt-0 gap-2" id="modalFooter"></div>

    </div>
  </div>
</div>

<script>
var CSRF_NAME = '<?=$this->security->get_csrf_token_name()?>';
var CSRF_HASH = '<?=$this->security->get_csrf_hash()?>';
var API_CI    = '<?=base_url('api/attendance/checkin')?>';
var API_CO    = '<?=base_url('api/attendance/checkout')?>';

// state
var _mode      = 'in';
var _gpsData   = null;
var _photoB64  = null;
var _camStream = null;
var _bsModal   = null;

// ── นาฬิกา ────────────────────────────────────────────────────────
(function tick(){
  var n = new Date();
  var cl = document.getElementById('liveClock');
  var dl = document.getElementById('liveDate');
  if (cl) cl.textContent = n.toLocaleTimeString('th-TH');
  if (dl) {
    var days = ['อาทิตย์','จันทร์','อังคาร','พุธ','พฤหัส','ศุกร์','เสาร์'];
    dl.textContent = 'วัน'+days[n.getDay()]+' '+n.toLocaleDateString('th-TH');
  }
  setTimeout(tick, 1000);
})();

// ── เปิด modal ─────────────────────────────────────────────────────
function openCheckinModal()  { _open('in');  }
function openCheckoutModal() { _open('out'); }

function _open(mode) {
  _mode = mode; _gpsData = null; _photoB64 = null;
  document.getElementById('modalTitle').textContent =
    mode === 'in' ? 'ลงเวลาเข้างาน' : 'ลงเวลาออกงาน';
  _bsModal = new bootstrap.Modal(document.getElementById('attendModal'));
  _bsModal.show();
  _step('GPS');
  _doGPS();
}

// ── Step indicator ─────────────────────────────────────────────────
function _step(name) {
  var map = { GPS:1, Camera:2, Confirm:3 };
  var n   = map[name] || 1;
  ['stepGPS','stepCamera','stepConfirm'].forEach(function(id,i){
    var el = document.getElementById(id);
    if (el) el.style.display = (i+1 === n) ? '' : 'none';
  });
  for (var i = 1; i <= 3; i++) {
    var dot = document.getElementById('dot'+i);
    if (dot) dot.style.background = i <= n ? '#1a56db' : '#e5e7eb';
  }
}

// ── GPS ─────────────────────────────────────────────────────────────
function _doGPS() {
  _footer([]);
  var sp = document.getElementById('gpsSpinner');
  var re = document.getElementById('gpsResult');
  var ac = document.getElementById('gpsActions');
  if (sp) sp.style.display = '';
  if (re) re.style.display = 'none';
  if (ac) ac.style.display = 'none';
  _setText('gpsTitleTxt','กำลังขอตำแหน่ง GPS...');
  _setText('gpsDescTxt','กรุณาอนุญาตการเข้าถึงตำแหน่งของคุณ');

  if (!navigator.geolocation) { _gpsErr('เบราว์เซอร์ไม่รองรับ GPS'); return; }

  navigator.geolocation.getCurrentPosition(
    function(pos) {
      _gpsData = { lat: pos.coords.latitude, lng: pos.coords.longitude };
      if (sp) sp.style.display = 'none';
      if (re) re.style.display = '';
      _setText('gpsResultIcon', '');
      // แสดง lat,lng ก่อนระหว่างรอชื่อสถานที่
      _setText('gpsResultCoords', _gpsData.lat.toFixed(5) + ', ' + _gpsData.lng.toFixed(5));
      _setText('gpsTitleTxt', 'ระบุตำแหน่งสำเร็จ');
      _setText('gpsDescTxt', 'กำลังค้นหาชื่อสถานที่...');
      _footer([{ t:'ถัดไป → ถ่ายรูป', c:'btn-primary', f:'_toCamera()' }]);

      // ── Reverse geocode ด้วย Nominatim (OpenStreetMap) ────────────
      // ฟรี ไม่ต้อง API key — ขอแค่ส่ง User-Agent ที่บ่งบอกตัวตน
      fetch(
        'https://nominatim.openstreetmap.org/reverse'
        + '?format=json'
        + '&lat=' + _gpsData.lat
        + '&lon=' + _gpsData.lng
        + '&zoom=16'
        + '&addressdetails=1'
        + '&accept-language=th',
        {
          headers: {
            'User-Agent': 'HRM-TGSmartLife/1.0 (internal)'
          }
        }
      )
      .then(function(r){ return r.json(); })
      .then(function(d) {
        if (!d || !d.address) { _setText('gpsDescTxt',''); return; }
        var a    = d.address;
        var parts = [];

        // ลำดับ: อาคาร/สถานที่ → ถนน/ซอย → แขวง/ตำบล → เขต/อำเภอ → จังหวัด
        if (a.amenity || a.building || a.shop || a.office)
          parts.push(a.amenity || a.building || a.shop || a.office);
        if (a.road || a.pedestrian)
          parts.push(a.road || a.pedestrian);
        if (a.suburb || a.subdistrict || a.quarter)
          parts.push(a.suburb || a.subdistrict || a.quarter);
        if (a.city_district || a.district)
          parts.push(a.city_district || a.district);
        if (a.city || a.town || a.village || a.county)
          parts.push(a.city || a.town || a.village || a.county);
        if (a.state || a.province)
          parts.push(a.state || a.province);

        var placeName = parts.length > 0 ? parts.join(', ') : (d.display_name || '');
        // ตัดให้ไม่ยาวเกิน 60 ตัวอักษร
        if (placeName.length > 120) placeName = placeName.substring(0, 120) + '…';

        _setText('gpsDescTxt', placeName);
        // เก็บชื่อสถานที่ไว้ใน _gpsData เพื่อส่งไปพร้อม check-in/out
        _gpsData.place_name = placeName;
      })
      .catch(function() {
        // geocode ล้มเหลว — ไม่ error แค่ไม่แสดงชื่อ
        _setText('gpsDescTxt', '');
      });
    },
    function(err) {
      var msg = err.code===1 ? 'ไม่ได้รับอนุญาต GPS'
              : err.code===2 ? 'ไม่สามารถระบุตำแหน่งได้' : 'หมดเวลาขอ GPS';
      _gpsErr(msg);
    },
    { timeout:10000, enableHighAccuracy:true }
  );
}

function _gpsErr(msg) {
  var sp = document.getElementById('gpsSpinner');
  var re = document.getElementById('gpsResult');
  if (sp) sp.style.display = 'none';
  if (re) re.style.display = '';
  _setText('gpsResultIcon','⚠️');
  _setText('gpsResultCoords', msg);
  _setText('gpsTitleTxt','ไม่ได้รับตำแหน่ง GPS');
  _setText('gpsDescTxt','');
  _footer([
    { t:'ข้ามตำแหน่ง', c:'btn-outline-secondary', f:'_skipGPS()' },
    { t:'ลองใหม่',      c:'btn-primary',            f:'_doGPS()'  }
  ]);
}
function _skipGPS() { _gpsData = null; _toCamera(); }

// ── Camera ──────────────────────────────────────────────────────────
function _toCamera() {
  _step('Camera');
  _photoB64 = null;
  _setText2('btnSkipPhoto','display','');
  _setText2('btnRetake','display','none');
  _setText2('btnUsePhoto','display','none');
  document.getElementById('camPreviewWrap').style.display = 'none';
  document.getElementById('camLiveWrap').style.display    = '';
  _footer([]);

  navigator.mediaDevices.getUserMedia({ video:{ facingMode:'user' }, audio:false })
    .then(function(s) {
      _camStream = s;
      document.getElementById('camVideo').srcObject = s;
    })
    .catch(function() {
      document.getElementById('camLiveWrap').innerHTML =
        '<div class="text-center py-4 text-muted">' +
        '<i class="bi bi-camera-video-off d-block fs-2 mb-2"></i>ไม่สามารถเปิดกล้องได้</div>';
      _footer([{ t:'ข้ามรูปถ่าย', c:'btn-outline-secondary', f:'_skipPhoto()' }]);
    });
}

function snapPhoto() {
  var v = document.getElementById('camVideo');
  var c = document.createElement('canvas');
  c.width = v.videoWidth||640; c.height = v.videoHeight||480;
  c.getContext('2d').drawImage(v,0,0);
  _photoB64 = c.toDataURL('image/jpeg',0.75);
  _stopCam();
  document.getElementById('snapImg').src = _photoB64;
  document.getElementById('camLiveWrap').style.display    = 'none';
  document.getElementById('camPreviewWrap').style.display = '';
  _setText2('btnSkipPhoto','display','none');
  _setText2('btnRetake','display','');
  _setText2('btnUsePhoto','display','');
}

function retakePhoto() {
  _photoB64 = null;
  document.getElementById('camLiveWrap').style.display    = '';
  document.getElementById('camPreviewWrap').style.display = 'none';
  _setText2('btnSkipPhoto','display','');
  _setText2('btnRetake','display','none');
  _setText2('btnUsePhoto','display','none');
  navigator.mediaDevices.getUserMedia({ video:{ facingMode:'user' }, audio:false })
    .then(function(s){
      _camStream = s;
      document.getElementById('camVideo').srcObject = s;
    });
}

function _skipPhoto() { _photoB64 = null; _stopCam(); _toConfirm(); }
function skipPhoto() { _photoB64 = null; _stopCam(); _toConfirm(); }

function goToConfirm() { _stopCam(); _toConfirm(); }

// ── Confirm ─────────────────────────────────────────────────────────
function _toConfirm() {
  _step('Confirm');
  var now = new Date();
  _setText('confirmTime', now.toLocaleTimeString('th-TH'));
  _setText('confirmLabel', _mode==='in' ? 'ยืนยันลงเวลาเข้างาน' : 'ยืนยันลงเวลาออกงาน');
  _setText('confirmIcon', _mode==='in' ? '' : '🔴');

  var gr = document.getElementById('confirmGPSRow');
  if (_gpsData) {
    var gpsDisplay = _gpsData.place_name
      ? _gpsData.place_name
      : _gpsData.lat.toFixed(5) + ', ' + _gpsData.lng.toFixed(5);
    _setText('confirmGPSTxt', gpsDisplay);
    gr.style.display = '';
  } else { gr.style.display = 'none'; }

  var pr = document.getElementById('confirmPhotoRow');
  if (_photoB64) {
    document.getElementById('confirmPhoto').src = _photoB64;
    pr.style.display = '';
  } else { pr.style.display = 'none'; }

  document.getElementById('confirmSpinner').style.display = 'none';

  _footer([
    { t:'← แก้ไข',  c:'btn-outline-secondary', f:'_toCamera()' },
    { t: _mode==='in' ? '✓ ยืนยันเข้างาน' : '✓ ยืนยันออกงาน',
      c:'btn-primary', f:'_submit()' }
  ]);
}

// ── Submit ───────────────────────────────────────────────────────────
function _submit() {
  _footer([]);
  document.getElementById('confirmSpinner').style.display = '';
  var payload = {}; payload[CSRF_NAME] = CSRF_HASH;
  if (_gpsData)  {
    payload.lat = _gpsData.lat;
    payload.lng = _gpsData.lng;
    if (_gpsData.place_name) payload.place_name = _gpsData.place_name;
  }
  if (_photoB64) { payload.photo = _photoB64; }
  fetch(_mode==='in' ? API_CI : API_CO, {
    method:'POST', headers:{'Content-Type':'application/json'},
    body:JSON.stringify(payload)
  })
  .then(function(r){ return r.json(); })
  .then(function(d) {
    if (_bsModal) _bsModal.hide();
    if (d.success) {
      var msg = d.message;
      if (_mode==='in' && d.data)
        msg += d.data.is_late ? '\n! มาสาย '+d.data.late_minutes+' นาที' : '\n✓ ตรงเวลา';
      if (_mode==='out' && d.data && d.data.is_early_out)
        msg += '\n⚠ ออกก่อนเวลา '+d.data.early_minutes+' นาที';
      alert(msg); location.reload();
    } else { alert('❌ '+d.message); }
  })
  .catch(function(){ if(_bsModal)_bsModal.hide(); alert('เกิดข้อผิดพลาด กรุณาลองใหม่'); });
}

// ── helpers ──────────────────────────────────────────────────────────
function cancelModal() { _stopCam(); if(_bsModal)_bsModal.hide(); }
function _stopCam() {
  if (_camStream) { _camStream.getTracks().forEach(function(t){t.stop();}); _camStream=null; }
}
function _footer(btns) {
  var f = document.getElementById('modalFooter');
  if (!f) return;
  f.innerHTML = btns.map(function(b){
    return '<button class="btn '+b.c+' flex-fill px-3" onclick="'+b.f+'">'+b.t+'</button>';
  }).join('');
}
function _setText(id, val) { var el=document.getElementById(id); if(el) el.textContent=val; }
function _setText2(id, prop, val) { var el=document.getElementById(id); if(el) el.style[prop]=val; }
</script>
