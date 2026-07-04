<?php defined('BASEPATH') OR exit(); ?>
<div class="card mb-3">
  <div class="card-body py-2">
    <?= form_open('manager/leave', array('method' => 'GET', 'class' => 'row g-2 align-items-end')) ?>
    <div class="col-md-3"><select name="status" class="form-select form-select-sm">
        <option value="pending" <?= ($filters['status'] ?? 'pending') === 'pending' ? 'selected' : '' ?>>รอการอนุมัติ</option>
        <option value="approved" <?= ($filters['status'] ?? '') === 'approved' ? 'selected' : '' ?>>อนุมัติแล้ว</option>
        <option value="rejected" <?= ($filters['status'] ?? '') === 'rejected' ? 'selected' : '' ?>>ปฏิเสธแล้ว</option>
      </select></div>
    <div class="col-md-2"><select name="year" class="form-select form-select-sm">
        <?php for ($y = date('Y'); $y >= date('Y') - 2; $y--): ?>
          <option value="<?= $y ?>" <?= ($filters['year'] ?? date('Y')) == $y ? 'selected' : '' ?>><?= $y ?></option>
        <?php endfor; ?>
      </select></div>
    <div class="col-auto"><button type="submit" class="btn btn-primary btn-sm">ค้นหา</button></div>
    <?= form_close() ?>
  </div>
</div>

<div class="card">
  <div class="card-header"><i class="bi bi-check2-circle me-2"></i>รายการคำขอลา</div>
  <div class="card-body p-0">
    <div class="table-responsive">
      <table class="table table-hover mb-0">
        <thead>
          <tr>
            <th>พนักงาน</th><th>ประเภท</th><th>วันที่</th>
            <th>วัน</th><th>เหตุผล</th><th>สถานะ</th><th>เอกสาร</th><th>จัดการ</th>
          </tr>
        </thead>
        <tbody>
          <?php if (!empty($requests)):
            foreach ($requests as $req): ?>
          <tr>
            <td class="fw-semibold" style="font-size:.875rem">
              <?= $req->first_name . ' ' . $req->last_name ?>
              <br><small class="text-muted"><?= $req->employee_id ?></small>
            </td>
            <td><span class="badge bg-info text-dark"><?= htmlspecialchars($req->leave_type_name ?? '') ?></span></td>
            <td style="font-size:.83rem;white-space:nowrap">
              <?= date('d/m/Y', strtotime($req->start_date)) ?>
              <?= ($req->start_date !== $req->end_date) ? ' – ' . date('d/m/Y', strtotime($req->end_date)) : '' ?>
            </td>
            <td><?= $req->total_days ?></td>
            <td style="font-size:.83rem;max-width:140px"><?= htmlspecialchars($req->reason ?? '') ?></td>
            <td>
              <?php
              $sb = array('pending'=>'bg-warning text-dark','approved'=>'bg-success','rejected'=>'bg-danger');
              $sl = array('pending'=>'รอการอนุมัติ','approved'=>'อนุมัติแล้ว','rejected'=>'ปฏิเสธ');
              $st = $req->status ?? 'pending';
              ?>
              <span class="badge <?= $sb[$st] ?? 'bg-secondary' ?>"><?= $sl[$st] ?? $st ?></span>
            </td>
            <td>
              <?php if (!empty($req->attachment)): ?>
                <a href="<?= base_url('uploads/leave/' . $req->attachment) ?>" target="_blank"
                   class="btn btn-outline-secondary btn-sm py-0 px-1">
                  <i class="bi bi-paperclip"></i>
                </a>
              <?php else: ?>–<?php endif; ?>
            </td>
            <td>
              <?php if ($req->status === 'pending'): ?>
                <button type="button" class="btn btn-success btn-sm py-0 px-2"
                        onclick="openApprove(<?= $req->id ?>, '<?= htmlspecialchars($req->first_name) ?>', <?= $req->total_days ?>)">
                  <i class="bi bi-check-lg"></i>
                </button>
                <button type="button" class="btn btn-danger btn-sm py-0 px-2 ms-1"
                        onclick="openReject(<?= $req->id ?>, '<?= htmlspecialchars($req->first_name) ?>')">
                  <i class="bi bi-x-lg"></i>
                </button>
              <?php endif; ?>
            </td>
          </tr>
          <?php endforeach; else: ?>
          <tr>
            <td colspan="8" class="text-center text-muted py-5">ไม่มีข้อมูลการลา</td>
          </tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<!-- ── Modal อนุมัติ (นอก table — รองรับทุก browser) ── -->
<div class="modal fade" id="modalApprove" tabindex="-1">
  <div class="modal-dialog modal-sm">
    <div class="modal-content">
      <div class="modal-header py-2 bg-success text-white">
        <h6 class="modal-title mb-0">อนุมัติการลา</h6>
        <button class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <p class="small mb-2" id="approveMsg"></p>
        <textarea id="approveNote" class="form-control form-control-sm" rows="2"
                  placeholder="หมายเหตุ (ถ้ามี)"></textarea>
      </div>
      <div class="modal-footer py-2">
        <button type="button" class="btn btn-success btn-sm" onclick="submitApprove()">ยืนยัน</button>
        <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">ยกเลิก</button>
      </div>
    </div>
  </div>
</div>

<!-- ── Modal ปฏิเสธ (นอก table — รองรับทุก browser) ── -->
<div class="modal fade" id="modalReject" tabindex="-1">
  <div class="modal-dialog modal-sm">
    <div class="modal-content">
      <div class="modal-header py-2 bg-danger text-white">
        <h6 class="modal-title mb-0">ปฏิเสธการลา</h6>
        <button class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <p class="small mb-2" id="rejectMsg"></p>
        <textarea id="rejectNote" class="form-control form-control-sm" rows="2"
                  placeholder="เหตุผล (จำเป็น)" required></textarea>
      </div>
      <div class="modal-footer py-2">
        <button type="button" class="btn btn-danger btn-sm" onclick="submitReject()">ยืนยัน</button>
        <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">ยกเลิก</button>
      </div>
    </div>
  </div>
</div>

<script>
// ── ไม่ใช้ form submit — ใช้ fetch เพื่อรองรับ LINE browser และทุก browser ──
var _leaveId = null;
var _csrfName  = '<?= $this->security->get_csrf_token_name() ?>';
var _csrfHash  = '<?= $this->security->get_csrf_hash() ?>';
var _baseUrl   = '<?= base_url() ?>';

function openApprove(id, name, days) {
  _leaveId = id;
  document.getElementById('approveNote').value = '';
  document.getElementById('approveMsg').textContent =
    'อนุมัติการลาของ ' + name + ' จำนวน ' + days + ' วัน?';
  var m = new bootstrap.Modal(document.getElementById('modalApprove'));
  m.show();
}

function openReject(id, name) {
  _leaveId = id;
  document.getElementById('rejectNote').value = '';
  document.getElementById('rejectMsg').textContent = 'ปฏิเสธการลาของ ' + name + '?';
  var m = new bootstrap.Modal(document.getElementById('modalReject'));
  m.show();
}

function submitApprove() {
  if (!_leaveId) return;
  var note = document.getElementById('approveNote').value;
  _postAction('manager/leave/approve/' + _leaveId, note);
}

function submitReject() {
  if (!_leaveId) return;
  var note = document.getElementById('rejectNote').value;
  if (!note.trim()) { alert('กรุณาระบุเหตุผล'); return; }
  _postAction('manager/leave/reject/' + _leaveId, note);
}

function _postAction(path, note) {
  var body = _csrfName + '=' + encodeURIComponent(_csrfHash)
           + '&note=' + encodeURIComponent(note);
  fetch(_baseUrl + path, {
    method: 'POST',
    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
    body: body,
    credentials: 'same-origin'
  })
  .then(function(r) {
    if (r.ok || r.redirected) {
      window.location.href = _baseUrl + 'manager/leave';
    } else {
      alert('เกิดข้อผิดพลาด กรุณาลองใหม่');
    }
  })
  .catch(function() {
    // fallback: ถ้า fetch ไม่รองรับ ใช้ form แบบดั้งเดิม
    var f = document.createElement('form');
    f.method = 'POST';
    f.action = _baseUrl + path;
    f.innerHTML = '<input name="' + _csrfName + '" value="' + _csrfHash + '">'
                + '<input name="note" value="' + note.replace(/"/g,'&quot;') + '">';
    document.body.appendChild(f);
    f.submit();
  });
}
</script>
