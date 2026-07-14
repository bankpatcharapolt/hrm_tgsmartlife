<?php defined('BASEPATH') OR exit(); ?>
<!-- Google Maps CSS -->
<style>
#empMap { height: calc(100vh - 200px); min-height: 480px; border-radius: 12px; z-index:0; }
@media (max-width:767px) { #empMap { height: 55vh; min-height: 320px; } }

/* Filter bar */
.map-filter { background:#fff; border-radius:12px; box-shadow:0 1px 8px rgba(0,0,0,.08);
              padding:.65rem 1rem; margin-bottom:.75rem; }
.map-filter .form-select, .map-filter .form-control { font-size:.85rem; }

/* Summary badges */
.sum-bar { display:flex; flex-wrap:wrap; gap:.5rem; margin-bottom:.75rem; }
.sum-pill { display:flex; align-items:center; gap:.35rem; padding:.3rem .75rem;
            border-radius:999px; font-size:.78rem; font-weight:600; cursor:pointer;
            transition:.15s; border:2px solid transparent; }
.sum-pill:hover { opacity:.85; }
.sum-pill.active { border-color:#1a56db; }
.sum-pill .dot { width:10px; height:10px; border-radius:50%; }

/* Map marker styles */
.emp-avatar-wrap { position:relative; width:44px; height:44px; }
.emp-avatar { width:44px; height:44px; border-radius:50%; object-fit:cover;
              border:3px solid #fff; box-shadow:0 2px 8px rgba(0,0,0,.25); }
.emp-status-dot { position:absolute; bottom:1px; right:1px; width:13px; height:13px;
                  border-radius:50%; border:2px solid #fff; }

/* Popup styles */
.emp-popup { min-width:200px; font-family:'Sarabun',sans-serif; }
.emp-popup .pop-name { font-weight:700; font-size:.95rem; margin-bottom:.15rem; }
.emp-popup .pop-meta { font-size:.78rem; color:#6b7280; }
.emp-popup .pop-sales { margin-top:.5rem; }
.pop-bar-wrap { background:#f3f4f6; border-radius:4px; height:8px; margin:.3rem 0; overflow:hidden; }
.pop-bar-fill { height:8px; border-radius:4px; background:linear-gradient(90deg,#22c55e,#16a34a); transition:.4s; }
.pop-pct { font-size:.75rem; font-weight:700; color:#16a34a; }

/* Loading overlay */
#mapLoading { position:absolute; inset:0; background:rgba(255,255,255,.6);
              display:flex; align-items:center; justify-content:center;
              border-radius:12px; z-index:500; pointer-events:none; }
</style>

<!-- Filter bar -->
<div class="map-filter">
  <div class="row g-2 align-items-end">
    <div class="col-6 col-md-2">
      <label class="form-label small mb-1">ทีม/สาขา</label>
      <select id="filterTeam" class="form-select form-select-sm">
        <option value="">ทั้งหมด</option>
        <?php foreach($teams as $t):?>
        <option value="<?=$t->id?>"><?=$t->team_name?></option>
        <?php endforeach;?>
      </select>
    </div>
    <div class="col-6 col-md-2">
      <label class="form-label small mb-1">แผนก</label>
      <select id="filterDept" class="form-select form-select-sm">
        <option value="">ทุกแผนก</option>
        <?php foreach($departments as $dp):?>
        <option value="<?=$dp->id?>"><?=htmlspecialchars($dp->name)?></option>
        <?php endforeach;?>
      </select>
    </div>
    <div class="col-6 col-md-2">
      <label class="form-label small mb-1">วันที่</label>
      <input type="text" id="filterDate" class="form-control form-control-sm jq-date-only"
             placeholder="dd/mm/yyyy" autocomplete="off" readonly style="cursor:pointer"
             value="<?=date('d/m/Y')?>">
      <input type="hidden" id="filterDateHidden" value="<?=date('Y-m-d')?>">
    </div>
    <div class="col-6 col-md-3">
      <label class="form-label small mb-1">ค้นหาชื่อพนักงาน</label>
      <input type="text" id="filterName" class="form-control form-control-sm"
             placeholder="พิมพ์ชื่อ...">
    </div>
    <div class="col-6 col-md-auto">
      <button class="btn btn-primary btn-sm w-100" onclick="loadMap()">
        <i class="bi bi-search me-1"></i>โหลดข้อมูล
      </button>
    </div>
    <div class="col-12 col-md-auto ms-md-auto">
      <button class="btn btn-outline-secondary btn-sm" onclick="loadMap()" id="btnRefresh">
        <i class="bi bi-arrow-clockwise me-1"></i>รีเฟรช
      </button>
    </div>
  </div>
</div>

<!-- Summary bar -->
<div class="sum-bar" id="sumBar" style="display:none">
  <div class="sum-pill" id="sp-all" onclick="filterStatus('')" style="background:#eff6ff;color:#1a56db">
    <span class="dot" style="background:#1a56db"></span>
    <span id="sc-all">ทั้งหมด 0</span>
  </div>
  <div class="sum-pill" id="sp-checked_in" onclick="filterStatus('checked_in')" style="background:#f0fdf4;color:#16a34a">
    <span class="dot" style="background:#16a34a"></span>
    <span id="sc-checked_in">มาทำงาน 0</span>
  </div>
  <div class="sum-pill" id="sp-late" onclick="filterStatus('late')" style="background:#fff7ed;color:#c2410c">
    <span class="dot" style="background:#f97316"></span>
    <span id="sc-late">สาย 0</span>
  </div>
  <div class="sum-pill" id="sp-checked_in_late" onclick="filterStatus('checked_in_late')" style="background:#fffbeb;color:#92400e">
    <span class="dot" style="background:#d97706"></span>
    <span id="sc-checked_in_late">เข้างานแล้ว (สาย) 0</span>
  </div>
  <div class="sum-pill" id="sp-forgot_checkout" onclick="filterStatus('forgot_checkout')" style="background:#fff7ed;color:#c2410c">
    <span class="dot" style="background:#f97316"></span>
    <span id="sc-forgot_checkout">ลืมลงเวลาออก 0</span>
  </div>
  <div class="sum-pill" id="sp-checked_out" onclick="filterStatus('checked_out')" style="background:#f8fafc;color:#64748b">
    <span class="dot" style="background:#94a3b8"></span>
    <span id="sc-checked_out">ออกงานแล้ว 0</span>
  </div>
  <div class="sum-pill" id="sp-on_leave" onclick="filterStatus('on_leave')" style="background:#fef9c3;color:#854d0e">
    <span class="dot" style="background:#eab308"></span>
    <span id="sc-on_leave">ลา 0</span>
  </div>
  <div class="sum-pill" id="sp-not_in" onclick="filterStatus('not_in')" style="background:#fef2f2;color:#dc2626">
    <span class="dot" style="background:#dc2626"></span>
    <span id="sc-not_in">ยังไม่เข้างาน 0</span>
  </div>
</div>

<!-- Map -->
<div style="position:relative">
  <div id="empMap"></div>
  <div id="mapLoading" style="display:none">
    <div class="spinner-border text-primary" style="width:2.5rem;height:2.5rem"></div>
  </div>
</div>

<script>
// ── Config ────────────────────────────────────────────────────────────────
var BASE_URL = '<?=base_url()?>';
var API_URL  = BASE_URL + 'admin/map/data';

var STATUS_COLOR = {
  checked_in:      '#16a34a',  // เขียว
  late:            '#f97316',  // ส้ม (สาย วันนี้)
  checked_out:     '#94a3b8',  // เทา
  forgot_checkout: '#f97316',  // ส้ม
  checked_in_late: '#d97706',  // เหลืองเข้ม (สาย วันก่อน)
  on_leave:        '#eab308',  // เหลือง
  not_in:          '#dc2626',  // แดง
};
var STATUS_LABEL = {
  checked_in:      'เข้างานแล้ว',
  late:            'สาย',
  checked_out:     'ออกงานแล้ว',
  forgot_checkout: 'เข้างานแล้ว (ลืมลงเวลาออก)',
  checked_in_late: 'เข้างานแล้ว (สาย)',
  on_leave:        'ลา',
  not_in:          'ยังไม่เข้างาน(ขาดงาน)',
};

// ── State ─────────────────────────────────────────────────────────────────
var _map       = null;
var _markers   = [];   // { gmarker, infoWindow, data }
var _allData   = [];
var _curFilter = '';
var _openInfo  = null;

// ── Google Maps init callback ─────────────────────────────────────────────
window.__gmMapInit = function() {
  _map = new google.maps.Map(document.getElementById('empMap'), {
    center: { lat: 13.7563, lng: 100.5018 },
    zoom: 11,
    mapTypeControl: false,
    streetViewControl: false,
    fullscreenControl: true,
    zoomControlOptions: { position: google.maps.ControlPosition.RIGHT_CENTER },
  });
  loadMap();
}

// ── Datepicker (jQuery UI) ────────────────────────────────────────────────
window.addEventListener('load', function() {
  if (typeof $ !== 'undefined' && typeof $.fn.datepicker !== 'undefined') {
    $('#filterDate').datepicker({
      dateFormat: 'dd/mm/yy',
      maxDate: 0,
      onSelect: function(d) {
        var p = d.split('/');
        document.getElementById('filterDateHidden').value = p[2]+'-'+p[1]+'-'+p[0];
      }
    });
  }
});

// ── Load data ─────────────────────────────────────────────────────────────
function loadMap() {
  var date    = document.getElementById('filterDateHidden').value;
  var team_id = document.getElementById('filterTeam').value;
  var dept_id = document.getElementById('filterDept').value;

  // อ่านค่า search ชื่อ ณ เวลาที่กด loadMap
  _curName   = (document.getElementById('filterName').value || '').trim();
  _curFilter = '';

  document.getElementById('mapLoading').style.display = '';
  clearMarkers();

  var url = API_URL + '?date=' + encodeURIComponent(date)
          + (team_id ? '&team_id=' + team_id : '')
          + (dept_id ? '&dept_id=' + dept_id : '');

  fetch(url, { credentials: 'same-origin' })
    .then(function(r) { return r.json(); })
    .then(function(res) {
      document.getElementById('mapLoading').style.display = 'none';
      if (!res.success) return;
      _allData = res.markers;
      updateSummary(res.summary, res.is_today);
      renderMarkers(_allData, res.is_today);
      _curFilter = '';
      updateFilterPills('');
      // apply name filter หลัง render ถ้ามีค่า
      if (_curName) _applyFilter();
    })
    .catch(function() {
      document.getElementById('mapLoading').style.display = 'none';
      alert('โหลดข้อมูลไม่สำเร็จ');
    });
}

function clearMarkers() {
  if (_openInfo) { _openInfo.close(); _openInfo = null; }
  _markers.forEach(function(m) { m.gmarker.setMap(null); });
  _markers = [];
}

function renderMarkers(data, isToday) {
  clearMarkers();
  _coordCount = {}; // reset jitter counter
  var bounds = new google.maps.LatLngBounds();
  var hasPt  = false;
  data.forEach(function(d) {
    var obj = createMarker(d, isToday);
    if (obj) {
      _markers.push(obj);
      bounds.extend({ lat: d.lat, lng: d.lng });
      hasPt = true;
    }
  });
  if (hasPt) _map.fitBounds(bounds);
}

// ── Jitter: กระจาย marker ที่อยู่จุดเดียวกัน ────────────────────────
var _coordCount = {};
function _jitter(lat, lng) {
  var key = lat.toFixed(4) + ',' + lng.toFixed(4);
  _coordCount[key] = (_coordCount[key] || 0) + 1;
  var n = _coordCount[key];
  if (n <= 1) return { lat: lat, lng: lng };
  // กระจายเป็นวง spiral ห่างประมาณ 30-60 เมตร
  var angle  = (n - 2) * 137.5 * Math.PI / 180; // golden angle
  var radius = 0.0003 * Math.ceil((n - 1) / 8);  // ~33 เมตร ต่อรอบ
  return {
    lat: lat + radius * Math.cos(angle),
    lng: lng + radius * Math.sin(angle),
  };
}

// ── วาด icon ผ่าน Canvas (รองรับรูปภาพจริง) ──────────────────────
function _makeIconCanvas(d, callback) {
  var color   = STATUS_COLOR[d.status] || '#6b7280';
  var size    = 48;
  var canvas  = document.createElement('canvas');
  canvas.width = size; canvas.height = size + 8;
  var ctx = canvas.getContext('2d');

  function _drawFinal(imgEl) {
    ctx.clearRect(0, 0, canvas.width, canvas.height);

    // วงกลมขอบสี
    ctx.beginPath();
    ctx.arc(size/2, size/2, size/2 - 1, 0, Math.PI*2);
    ctx.fillStyle = color;
    ctx.fill();

    // วงกลมขาว inner
    ctx.beginPath();
    ctx.arc(size/2, size/2, size/2 - 4, 0, Math.PI*2);
    ctx.fillStyle = '#fff';
    ctx.fill();

    if (imgEl) {
      // clip รูป
      ctx.save();
      ctx.beginPath();
      ctx.arc(size/2, size/2, size/2 - 5, 0, Math.PI*2);
      ctx.clip();
      ctx.drawImage(imgEl, 5, 5, size-10, size-10);
      ctx.restore();
    } else {
      // ตัวอักษร
      ctx.fillStyle = color;
      ctx.font = 'bold ' + (size*0.35) + 'px Arial,sans-serif';
      ctx.textAlign = 'center';
      ctx.textBaseline = 'middle';
      ctx.fillText(d.name.charAt(0), size/2, size/2);
    }

    // จุดสถานะมุมขวาล่าง
    ctx.beginPath();
    ctx.arc(size - 8, size - 8, 6, 0, Math.PI*2);
    ctx.fillStyle = color;
    ctx.strokeStyle = '#fff';
    ctx.lineWidth = 2;
    ctx.fill(); ctx.stroke();

    callback({
      url: canvas.toDataURL(),
      scaledSize: new google.maps.Size(size, size + 8),
      anchor: new google.maps.Point(size/2, size + 8),
    });
  }

  if (d.photo) {
    var img = new Image();
    img.crossOrigin = 'anonymous';
    img.onload  = function() { _drawFinal(img); };
    img.onerror = function() { _drawFinal(null); };
    img.src = d.photo;
  } else {
    _drawFinal(null);
  }
}

function createMarker(d, isToday) {
  // Bug 3: jitter พิกัดซ้ำ
  var pos = _jitter(d.lat, d.lng);

  // สร้าง marker ด้วย default icon ก่อน แล้วค่อยเปลี่ยนเป็น canvas
  var gmarker = new google.maps.Marker({
    position: pos,
    map: _map,
    title: d.name,
    icon: {
      path: google.maps.SymbolPath.CIRCLE,
      scale: 14,
      fillColor: STATUS_COLOR[d.status] || '#6b7280',
      fillOpacity: 1,
      strokeColor: '#fff',
      strokeWeight: 2,
    },
  });

  // วาด canvas icon แบบ async (รูปจริง)
  _makeIconCanvas(d, function(icon) {
    gmarker.setIcon(icon);
  });

  var infoWindow = new google.maps.InfoWindow({
    content: buildPopup(d, isToday),
    maxWidth: 260,
  });

  gmarker.addListener('click', function() {
    if (_openInfo) _openInfo.close();
    infoWindow.open(_map, gmarker);
    _openInfo = infoWindow;
  });

  return { gmarker: gmarker, infoWindow: infoWindow, data: d };
}

function buildPopup(d, isToday) {
  var statusLabel = STATUS_LABEL[d.status] || d.status;
  var statusColor = STATUS_COLOR[d.status] || '#6b7280';
  var html = '<div style="font-family:Sarabun,Arial,sans-serif;min-width:200px;padding:.25rem">';

  // header
  if (d.photo) {
    html += '<div style="display:flex;align-items:center;gap:.6rem;margin-bottom:.4rem">'
          + '<img src="' + d.photo + '" style="width:40px;height:40px;border-radius:50%;object-fit:cover;border:2px solid #e5e7eb">'
          + '<div>'
          + '<div style="font-weight:700;font-size:.95rem">' + d.name + '</div>'
          + '<div style="font-size:.78rem;color:#6b7280">' + d.employee_id + '</div>'
          + '</div></div>';
  } else {
    html += '<div style="font-weight:700;font-size:.95rem">' + d.name + '</div>'
          + '<div style="font-size:.78rem;color:#6b7280">' + d.employee_id + '</div>';
  }

  html += '<div style="font-size:.78rem;color:#6b7280;margin:.25rem 0">'
        + (d.position ? d.position + '<br>' : '')
        + (d.team_name || '–')
        + '</div>';

  html += '<span style="display:inline-block;padding:.15rem .55rem;border-radius:999px;font-size:.72rem;font-weight:600;background:' + statusColor + '22;color:' + statusColor + '">'
        + statusLabel + '</span>';

  // ── เวลาเข้า/ออกงาน ──────────────────────────────────────────────────
  var timeHtml = '';
  if (d.check_in_time) {
    timeHtml += '<span style="font-size:.75rem;color:#374151"><i style="display:inline-block;width:14px;text-align:center">🕐</i> เข้า: <b>' + d.check_in_time + '</b></span>';
    if (d.is_late && d.late_minutes > 0) {
      timeHtml += ' <span style="font-size:.72rem;color:#f97316;font-weight:600">สาย ' + d.late_minutes + ' น.</span>';
    }
  }
  if (d.check_out_time) {
    timeHtml += (timeHtml ? '<br>' : '')
              + '<span style="font-size:.75rem;color:#374151"><i style="display:inline-block;width:14px;text-align:center">🕕</i> ออก: <b>' + d.check_out_time + '</b></span>';
  }
  if (timeHtml) {
    html += '<div style="margin-top:.35rem;padding:.25rem .4rem;background:#f8fafc;border-radius:6px;line-height:1.6">' + timeHtml + '</div>';
  }
  if (d.is_sales) {
    var thMonth = ['','ม.ค.','ก.พ.','มี.ค.','เม.ย.','พ.ค.','มิ.ย.','ก.ค.','ส.ค.','ก.ย.','ต.ค.','พ.ย.','ธ.ค.'];
    var mLabel     = thMonth[d.sale_month] || d.sale_month;
    var actual     = parseFloat(d.sales_actual || 0);
    var target     = parseFloat(d.sales_target || 0);
    var pct        = d.sales_pct || 0;
    var yearActual = parseFloat(d.sales_year_actual || 0);

    html += '<div style="margin-top:.5rem;border-top:1px solid #f3f4f6;padding-top:.4rem">'

          // ── ยอดขายรายเดือน ──
          + '<div style="font-size:.72rem;color:#6b7280;font-weight:600">ยอดขายรวม (' + mLabel + ' ' + d.sale_year + ')</div>'
          + '<div style="font-size:1.05rem;font-weight:700;color:#16a34a">฿' + actual.toLocaleString('th-TH') + '</div>'
          + '<div style="font-size:.72rem;color:#6b7280">เป้าหมาย: ฿' + target.toLocaleString('th-TH') + '</div>'
          + '<div style="background:#f3f4f6;border-radius:4px;height:7px;margin:.25rem 0;overflow:hidden">'
          + '<div style="width:' + Math.min(pct,100) + '%;height:7px;border-radius:4px;background:linear-gradient(90deg,#22c55e,#16a34a)"></div></div>'
          + '<div style="font-size:.72rem;font-weight:700;color:#16a34a">' + pct + '%</div>'

          // ── ยอดขายสะสมทั้งปี ──
          + '<div style="margin-top:.4rem;padding-top:.35rem;border-top:1px dashed #e5e7eb">'
          + '<div style="font-size:.72rem;color:#6b7280;font-weight:600">ยอดขายสะสมทั้งปี ' + d.sale_year + '</div>'
          + '<div style="font-size:1.05rem;font-weight:700;color:#1a56db">฿' + yearActual.toLocaleString('th-TH') + '</div>'
          + '</div>'

          + '</div>';
  }

  html += '</div>';
  return html;
}

// ── Summary bar ───────────────────────────────────────────────────────────
function updateSummary(sum, isToday) {
  document.getElementById('sumBar').style.display = '';

  // pill ที่แสดงแต่ละกรณี
  var todayPills    = ['checked_in', 'late', 'checked_out', 'on_leave', 'not_in'];
  var prevDayPills  = ['checked_in', 'checked_in_late', 'forgot_checkout', 'on_leave', 'not_in'];
  var activePills   = isToday ? todayPills : prevDayPills;
  var allStatuses   = ['checked_in', 'late', 'checked_out', 'forgot_checkout', 'checked_in_late', 'on_leave', 'not_in'];

  // อัปเดตตัวเลขทุก pill
  var total = 0;
  allStatuses.forEach(function(k) {
    var n = sum[k] || 0;
    total += n;
    var el = document.getElementById('sc-' + k);
    if (el) el.textContent = STATUS_LABEL[k] + ' ' + n;
  });
  document.getElementById('sc-all').textContent = 'ทั้งหมด ' + total;

  // แสดง/ซ่อน pill ตาม rule — แสดงทุกตัวที่อยู่ใน activePills เสมอ (แม้ 0)
  allStatuses.forEach(function(k) {
    var pill = document.getElementById('sp-' + k);
    if (!pill) return;
    pill.style.display = activePills.indexOf(k) !== -1 ? '' : 'none';
  });
}

// ── Filter by status + ชื่อพนักงาน ───────────────────────────────────────
var _curFilter = '';
var _curName   = '';

function filterStatus(status) {
  _curFilter = status;
  updateFilterPills(status);
  _applyFilter();
}

function _applyFilter() {
  if (_openInfo) { _openInfo.close(); _openInfo = null; }
  var nameQ = _curName.toLowerCase();
  _markers.forEach(function(m) {
    var statusOk = !_curFilter || m.data.status === _curFilter;
    var nameOk   = !nameQ || m.data.name.toLowerCase().indexOf(nameQ) !== -1;
    m.gmarker.setMap((statusOk && nameOk) ? _map : null);
  });
}

function updateFilterPills(status) {
  document.querySelectorAll('.sum-pill').forEach(function(el){ el.classList.remove('active'); });
  var target = status ? 'sp-' + status : 'sp-all';
  var el = document.getElementById(target);
  if (el) el.classList.add('active');
}

// ── Search ชื่อพนักงาน — ทำงานเมื่อกด "โหลดข้อมูล" เท่านั้น ───────────────
// (ไม่ใช้ realtime input event เพราะต้องกด loadMap ก่อน)
document.addEventListener('DOMContentLoaded', function() {
  var nameInput = document.getElementById('filterName');
  if (nameInput) {
    // กด Enter ใน search box → loadMap
    nameInput.addEventListener('keydown', function(e) {
      if (e.key === 'Enter') { e.preventDefault(); loadMap(); }
    });
  }
});

// ── Auto-refresh ทุก 2 นาทีถ้าเป็นวันนี้ ─────────────────────────────────
setInterval(function() {
  var date = document.getElementById('filterDateHidden').value;
  if (date === '<?=date('Y-m-d')?>') loadMap();
}, 120000);
</script>

<!-- Google Maps JS: โหลดหลัง JS block เพื่อให้ callback พร้อมก่อน -->
 


  <!-- production -->
   <!--

        -->

<?php
$_gmap_key = $this->config->item('google_maps_api_key');
$_gmap_src = 'https://maps.googleapis.com/maps/api/js?v=weekly&callback=__gmMapInit'
           . ($_gmap_key ? '&key=' . htmlspecialchars($_gmap_key) : '');
?>
<script src="<?=$_gmap_src?>" async defer></script>
