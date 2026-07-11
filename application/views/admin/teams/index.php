<?php defined('BASEPATH') OR exit(); ?>
<div class="row g-3">
  <!-- Form เพิ่ม/แก้ไข -->
  <div class="col-lg-5">
    <div class="card">
      <div class="card-header" id="formTitle"><i class="bi bi-plus-circle me-2"></i>เพิ่มทีม/สาขาใหม่</div>
      <div class="card-body">
        <?=form_open('admin/teams/store')?>
        <input type="hidden" name="<?=$this->security->get_csrf_token_name()?>" value="<?=$this->security->get_csrf_hash()?>">
        <input type="hidden" name="team_id" id="teamId" value="">
        <div class="row g-3">
          <div class="col-md-4">
            <label class="form-label">รหัสทีม <span class="text-danger">*</span></label>
            <input type="text" name="team_code" id="tCode" class="form-control text-uppercase" required placeholder="HQ, BKK, RYG">
            <div class="form-text">ตัวอักษรพิมพ์ใหญ่ ไม่ซ้ำ</div>
          </div>
          <div class="col-md-8">
            <label class="form-label">ชื่อทีม/สาขา <span class="text-danger">*</span></label>
            <input type="text" name="team_name" id="tName" class="form-control" required placeholder="เช่น สำนักงานใหญ่ สาขาระยอง">
          </div>
          <div class="col-md-6">
            <label class="form-label">พื้นที่/จังหวัด</label>
            <input type="text" name="location" id="tLoc" class="form-control" placeholder="กรุงเทพมหานคร">
          </div>
          <div class="col-md-6">
            <label class="form-label">รหัสพนักงานหัวหน้า</label>
            <input type="text" name="manager_emp_id" id="tMgr" class="form-control" placeholder="SL001">
          </div>
          <div class="col-12">
            <label class="form-label">ชื่อสถานที่</label>
            <input type="text" name="place_name" id="tPlaceName" class="form-control"
                   placeholder="เช่น อาคารมหาธร ชั้น 3, ตลาดระยอง">
          </div>
          <div class="col-12">
            <label class="form-label">ที่อยู่</label>
            <textarea name="address" id="tAddress" class="form-control" rows="2"
                      placeholder="เช่น 88/5 ถ.สุขุมวิท แขวงคลองเตย กรุงเทพฯ 10110"></textarea>
          </div>
          <div class="col-md-8">
            <label class="form-label">เป้ายอดขายต่อเดือน (฿)</label>
            <div class="input-group"><span class="input-group-text">฿</span>
              <input type="number" name="monthly_target" id="tTarget" class="form-control" min="0" step="1000" value="0">
            </div>
          </div>
          <div class="col-md-4 d-flex align-items-end pb-1">
            <div class="form-check form-switch">
              <input class="form-check-input" type="checkbox" name="is_active" id="tActive" value="1" checked>
              <label class="form-check-label small" for="tActive">ใช้งาน</label>
            </div>
          </div>

          <!-- ── พิกัดสาขา ── -->
          <div class="col-12">
            <hr class="my-1">
            <div class="fw-semibold small mb-2"><i class="bi bi-geo-alt me-1"></i>พิกัดที่ตั้งสาขา (สำหรับแผนที่พนักงาน)</div>
          </div>
          <div class="col-md-5">
            <label class="form-label small">ละติจูด (Latitude)</label>
            <input type="number" name="lat" id="tLat" class="form-control form-control-sm"
                   step="0.0000001" placeholder="เช่น 13.7563" min="-90" max="90">
          </div>
          <div class="col-md-5">
            <label class="form-label small">ลองจิจูด (Longitude)</label>
            <input type="number" name="lng" id="tLng" class="form-control form-control-sm"
                   step="0.0000001" placeholder="เช่น 100.5018" min="-180" max="180">
          </div>
          <div class="col-md-2">
            <label class="form-label small">รัศมี (กม.)</label>
            <input type="number" name="checkin_radius_km" id="tRadius" class="form-control form-control-sm"
                   step="0.1" min="0.1" max="50" value="5" placeholder="5">
          </div>
          <div class="col-12">
            <button type="button" class="btn btn-outline-secondary btn-sm" onclick="pickLocation()">
              <i class="bi bi-crosshair me-1"></i>เลือกพิกัดจากแผนที่
            </button>
            <span id="latLngDisplay" class="small text-muted ms-2"></span>
          </div>
          <!-- mini map picker -->
          <div class="col-12" id="mapPickerWrap" style="display:none">
            <!-- Search box เหนือแผนที่ -->
            <div style="position:relative;margin-bottom:.4rem">
              <div class="input-group input-group-sm">
                <span class="input-group-text"><i class="bi bi-search"></i></span>
                <input type="text" id="placeSearch" class="form-control"
                       placeholder="พิมพ์ค้นหาสถานที่... เช่น สยามพารากอน, ระยอง"
                       autocomplete="off">
                <button type="button" class="btn btn-primary btn-sm" onclick="doSearch()">ค้นหา</button>
              </div>
              <div id="placeResults" style="display:none;position:absolute;top:100%;left:0;right:0;
                   background:#fff;border:1px solid #d1d5db;border-radius:6px;z-index:9999;
                   max-height:200px;overflow-y:auto;box-shadow:0 4px 12px rgba(0,0,0,.15)">
              </div>
            </div>
            <!-- Leaflet map -->
            <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"/>
            <div id="mapPicker" style="height:260px;border-radius:8px;border:1px solid #e5e7eb;z-index:0"></div>
            <div class="form-text">ค้นหาสถานที่หรือคลิกบนแผนที่เพื่อเลือกพิกัดสาขา</div>
          </div>
        </div>

        <div class="mt-3 d-flex gap-2">
          <button type="submit" class="btn btn-primary" id="tBtn"><i class="bi bi-save me-1"></i>บันทึก</button>
          <button type="button" class="btn btn-outline-secondary" onclick="resetForm()">ล้าง</button>
        </div>
        <?=form_close()?>
      </div>
    </div>
  </div>

  <!-- รายการทีม -->
  <div class="col-lg-7">
    <div class="card">
      <div class="card-header"><i class="bi bi-diagram-3 me-2"></i>ทีมทั้งหมด <span class="badge bg-secondary"><?=count($teams)?></span></div>
      <div class="card-body p-0">
        <div class="table-responsive">
          <table class="table table-hover mb-0">
            <thead>
              <tr><th>รหัส</th><th>ชื่อทีม</th><th>พื้นที่</th><th>พิกัด</th><th>รัศมี</th><th>สมาชิก</th><th>เป้า/เดือน</th><th>สถานะ</th><th>จัดการ</th></tr>
            </thead>
            <tbody>
              <?php if(!empty($teams)):foreach($teams as $t):?>
              <tr>
                <td><code><?=$t->team_code?></code></td>
                <td>
                  <div class="fw-semibold" style="font-size:.875rem"><?=$t->team_name?></div>
                  <?php if($t->manager_emp_id):?>
                    <div style="font-size:.72rem;color:#6b7280">หัวหน้า: <?=$t->manager_emp_id?></div>
                  <?php endif;?>
                </td>
                <td style="font-size:.83rem"><?=$t->location??'–'?></td>
                <td style="font-size:.75rem">
                  <?php if($t->lat && $t->lng):?>
                    <span class="text-success"><i class="bi bi-geo-alt-fill"></i></span>
                    <span class="text-muted"><?=number_format($t->lat,4)?>,<?=number_format($t->lng,4)?></span>
                  <?php else:?>
                    <span class="text-muted">–</span>
                  <?php endif;?>
                </td>
                <td style="font-size:.83rem">
                  <?=$t->lat ? number_format($t->checkin_radius_km??5,1).' กม.' : '–'?>
                </td>
                <td><span class="badge bg-info text-dark"><?=$t->member_count?> คน</span></td>
                <td style="font-size:.83rem">฿<?=number_format($t->monthly_target,0)?></td>
                <td><span class="badge bg-<?=$t->is_active?'success':'secondary'?>"><?=$t->is_active?'ใช้งาน':'ปิด'?></span></td>
                <td>
                  <button class="btn btn-outline-secondary btn-sm px-2 py-0"
                    onclick="editTeam(<?=$t->id?>,'<?=addslashes($t->team_code)?>','<?=addslashes($t->team_name)?>','<?=addslashes($t->location??'')?>','<?=addslashes($t->manager_emp_id??'')?>', <?=(float)$t->monthly_target?>,<?=$t->is_active?>,<?=$t->lat??'null'?>,<?=$t->lng??'null'?>,<?=(float)($t->checkin_radius_km??5)?>,'<?=addslashes($t->place_name??'')?>','<?=addslashes($t->address??'')?>')"
                    title="แก้ไข"><i class="bi bi-pencil"></i></button>
                  <?php if($t->member_count==0 && $t->team_code!=='HQ'):?>
                  <a href="<?=base_url('admin/teams/delete/'.$t->id)?>"
                     onclick="return confirm('ลบทีม <?=addslashes($t->team_name)?>?')"
                     class="btn btn-outline-danger btn-sm px-2 py-0 ms-1"><i class="bi bi-trash"></i></a>
                  <?php else:?>
                  <button class="btn btn-outline-secondary btn-sm px-2 py-0 ms-1" disabled
                    title="<?=$t->team_code==='HQ'?'ทีมหลักลบไม่ได้':'มีสมาชิกอยู่'?>"><i class="bi bi-trash"></i></button>
                  <?php endif;?>
                </td>
              </tr>
              <?php endforeach;else:?>
              <tr><td colspan="9" class="text-center text-muted py-4">ยังไม่มีทีม</td></tr>
              <?php endif;?>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Google Maps JS (dev mode - no key) -->

<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
var _pickMap = null, _pickMarker = null, _searchTimer = null;
var _pendingLat = null, _pendingLng = null;

// ── เปิด/ปิด map picker ─────────────────────────────────────────────
function pickLocation() {
  var wrap = document.getElementById('mapPickerWrap');
  var show = wrap.style.display === 'none';
  wrap.style.display = show ? '' : 'none';
  if (show) {
    // ใช้ pending coord ถ้ามี ไม่งั้นใช้ค่าใน input
    var lat = _pendingLat !== null ? _pendingLat
            : (parseFloat(document.getElementById('tLat').value) || 13.7563);
    var lng = _pendingLng !== null ? _pendingLng
            : (parseFloat(document.getElementById('tLng').value) || 100.5018);

    if (!_pickMap) {
      // สร้าง map ใหม่
      setTimeout(function() {
        _pickMap = L.map('mapPicker').setView([lat, lng], lat === 13.7563 ? 11 : 14);
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
          attribution: '© OpenStreetMap'
        }).addTo(_pickMap);
        // ปักหมุดถ้ามีพิกัด
        if (_pendingLat !== null || document.getElementById('tLat').value) {
          _pickMarker = L.marker([lat, lng]).addTo(_pickMap);
        }
        _pendingLat = null; _pendingLng = null;
        _pickMap.on('click', function(e) {
          _setLatLng(e.latlng.lat, e.latlng.lng);
        });
      }, 50);
    } else {
      // map มีอยู่แล้ว → ลบ marker เดิม ปักใหม่ตาม pending
      setTimeout(function() {
        _pickMap.invalidateSize();
        if (_pickMarker) { _pickMap.removeLayer(_pickMarker); _pickMarker = null; }
        if (_pendingLat !== null) {
          _pickMap.setView([_pendingLat, _pendingLng], 14);
          _pickMarker = L.marker([_pendingLat, _pendingLng]).addTo(_pickMap);
        } else if (document.getElementById('tLat').value) {
          var la = parseFloat(document.getElementById('tLat').value);
          var ln = parseFloat(document.getElementById('tLng').value);
          _pickMap.setView([la, ln], 14);
          _pickMarker = L.marker([la, ln]).addTo(_pickMap);
        } else {
          _pickMap.setView([13.7563, 100.5018], 11);
        }
        _pendingLat = null; _pendingLng = null;
      }, 50);
    }
    setTimeout(function(){
      var s = document.getElementById('placeSearch');
      if (s) s.focus();
    }, 150);
  }
}

function _setLatLng(lat, lng) {
  var la = lat.toFixed(7), ln = lng.toFixed(7);
  document.getElementById('tLat').value = la;
  document.getElementById('tLng').value = ln;
  document.getElementById('latLngDisplay').textContent = la + ', ' + ln;
  if (_pickMarker) _pickMap.removeLayer(_pickMarker);
  _pickMarker = L.marker([lat, lng]).addTo(_pickMap);
}

// ── Nominatim Search ────────────────────────────────────────────────
document.addEventListener('DOMContentLoaded', function() {
  var input = document.getElementById('placeSearch');
  if (!input) return;
  input.addEventListener('input', function() {
    clearTimeout(_searchTimer);
    var q = this.value.trim();
    if (q.length < 2) { _hideDropdown(); return; }
    _searchTimer = setTimeout(function(){ _fetchPlaces(q); }, 400);
  });
  input.addEventListener('keydown', function(e) {
    if (e.key === 'Enter') { e.preventDefault(); doSearch(); }
  });
  document.addEventListener('click', function(e) {
    if (!e.target.closest('#placeSearch') && !e.target.closest('#placeResults')) {
      _hideDropdown();
    }
  });
});

function doSearch() {
  var q = document.getElementById('placeSearch').value.trim();
  if (q) _fetchPlaces(q);
}

function _fetchPlaces(q) {
  fetch(
    'https://nominatim.openstreetmap.org/search'
    + '?format=json&limit=6&addressdetails=1'
    + '&accept-language=th'
    + '&q=' + encodeURIComponent(q),
    { headers: { 'User-Agent': 'HRM-TGSmartLife/1.0' } }
  )
  .then(function(r){ return r.json(); })
  .then(function(data) {
    if (!data || !data.length) {
      _showDropdown([{ display_name: 'ไม่พบสถานที่', lat: null, lon: null }]);
      return;
    }
    _showDropdown(data);
  })
  .catch(function() {
    _showDropdown([{ display_name: 'ค้นหาไม่สำเร็จ กรุณาลองใหม่', lat: null, lon: null }]);
  });
}

function _showDropdown(items) {
  var box = document.getElementById('placeResults');
  box.innerHTML = '';
  items.forEach(function(item) {
    var div = document.createElement('div');
    div.style.cssText = 'padding:.45rem .75rem;font-size:.82rem;cursor:pointer;border-bottom:1px solid #f3f4f6;line-height:1.4';
    div.textContent = item.display_name;
    div.addEventListener('mouseenter', function(){ this.style.background='#eff6ff'; });
    div.addEventListener('mouseleave', function(){ this.style.background=''; });
    if (item.lat && item.lon) {
      (function(la, ln, name) {
        div.addEventListener('click', function() {
          _hideDropdown();
          document.getElementById('placeSearch').value = name.split(',')[0].trim();
          var lat = parseFloat(la), lng = parseFloat(ln);
          _setLatLng(lat, lng);
          if (_pickMap) {
            _pickMap.setView([lat, lng], 15);
          }
        });
      })(item.lat, item.lon, item.display_name);
    }
    box.appendChild(div);
  });
  box.style.display = '';
}

function _hideDropdown() {
  var box = document.getElementById('placeResults');
  if (box) box.style.display = 'none';
}

// ── Edit team ────────────────────────────────────────────────────────
function editTeam(id, code, name, loc, mgr, target, active, lat, lng, radius, placeName, address) {
  document.getElementById('teamId').value = id;
  document.getElementById('tCode').value = code;
  document.getElementById('tCode').readOnly = true;
  document.getElementById('tName').value = name;
  document.getElementById('tLoc').value = loc;
  document.getElementById('tMgr').value = mgr;
  document.getElementById('tTarget').value = target;
  document.getElementById('tActive').checked = active == 1;
  document.getElementById('tLat').value = (lat !== null && lat !== 'null') ? lat : '';
  document.getElementById('tLng').value = (lng !== null && lng !== 'null') ? lng : '';
  document.getElementById('tRadius').value = radius || 5;
  document.getElementById('tPlaceName').value = (placeName && placeName !== 'null') ? placeName : '';
  document.getElementById('tAddress').value   = (address   && address   !== 'null') ? address   : '';

  // ── แก้ marker: ลบเดิมออกก่อนเสมอ ─────────────────────────────────
  if (_pickMarker && _pickMap) {
    _pickMap.removeLayer(_pickMarker);
    _pickMarker = null;
  }

  var hasCoord = lat && lat !== 'null' && lng && lng !== 'null';

  if (hasCoord) {
    document.getElementById('latLngDisplay').textContent = lat + ', ' + lng;
  } else {
    document.getElementById('latLngDisplay').textContent = '';
  }

  // ── อัปเดต map ถ้าเปิดอยู่, ถ้าปิดอยู่เก็บ pending ไว้ ─────────────
  // ปิด map picker ก่อน แล้วค่อย reset state
  // เพื่อป้องกัน invalidateSize ขณะ display:none
  document.getElementById('mapPickerWrap').style.display = 'none';
  // _pickMap ยังคงอยู่ใช้ได้ แต่จะ update ตอนเปิดใหม่
  // เก็บพิกัดที่ต้องการไว้
  _pendingLat = hasCoord ? parseFloat(lat) : null;
  _pendingLng = hasCoord ? parseFloat(lng) : null;

  document.getElementById('formTitle').innerHTML = '<i class="bi bi-pencil me-2"></i>แก้ไขทีม: ' + name;
  document.getElementById('tBtn').innerHTML = '<i class="bi bi-save me-1"></i>อัปเดต';
  window.scrollTo({top: 0, behavior: 'smooth'});
}

function resetForm() {
  document.getElementById('teamId').value = '';
  document.getElementById('tCode').readOnly = false;
  ['tCode','tName','tLoc','tMgr','tLat','tLng','tPlaceName','tAddress'].forEach(function(id){
    document.getElementById(id).value = '';
  });
  document.getElementById('tTarget').value = '0';
  document.getElementById('tRadius').value = '5';
  document.getElementById('tActive').checked = true;
  document.getElementById('latLngDisplay').textContent = '';
  document.getElementById('placeSearch') && (document.getElementById('placeSearch').value = '');
  // ปิด map + reset pending
  document.getElementById('mapPickerWrap').style.display = 'none';
  _pendingLat = null; _pendingLng = null;
  if (_pickMarker && _pickMap) { _pickMap.removeLayer(_pickMarker); _pickMarker = null; }
  document.getElementById('formTitle').innerHTML = '<i class="bi bi-plus-circle me-2"></i>เพิ่มทีม/สาขาใหม่';
  document.getElementById('tBtn').innerHTML = '<i class="bi bi-save me-1"></i>บันทึก';
}

document.getElementById('tCode').addEventListener('input', function(){ this.value = this.value.toUpperCase(); });
</script>
