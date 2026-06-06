<?php defined('BASEPATH') OR exit(); ?>
<div class="card mb-3">
  <div class="card-body py-2">
    <?= form_open('manager/leave', array('method' => 'GET', 'class' => 'row g-2 align-items-end')) ?>
    <div class="col-md-3"><select name="status" class="form-select form-select-sm">
        <option value="pending" <?= ($filters['status'] ?? 'pending') === 'pending' ? 'selected' : '' ?>>รอการอนุมัติ</option>
        <option value="approved" <?= ($filters['status'] ?? '') === 'approved' ? 'selected' : '' ?>>อนุมัติแล้ว</option>
        <option value="rejected" <?= ($filters['status'] ?? '') === 'rejected' ? 'selected' : '' ?>>ปฏิเสธแล้ว</option>
        <!-- <option value="">ทุกสถานะ</option> -->
      </select></div>
    <div class="col-md-2"><select name="year"
        class="form-select form-select-sm"><?php for ($y = date('Y'); $y >= date('Y') - 2; $y--): ?>
          <option value="<?= $y ?>" <?= ($filters['year'] ?? date('Y')) == $y ? 'selected' : '' ?>><?= $y ?></option><?php endfor; ?>
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
            <th>พนักงาน</th>
            <th>ประเภท</th>
            <th>วันที่</th>
            <th>วัน</th>
            <th>เหตุผล</th>
            <th>สถานะ</th>
            <th>จัดการ</th>
          </tr>
        </thead>
        <tbody>
          <?php if (!empty($requests)):
            foreach ($requests as $req): ?>
              <tr>
                <td class="fw-semibold" style="font-size:.875rem"><?= $req->first_name . ' ' . $req->last_name ?></td>
                <td><span class="badge bg-info text-dark"><?= $req->leave_type_name ?></span></td>
                <td style="font-size:.82rem">
                  <?= date('d/m/Y', strtotime($req->start_date)) ?>    <?= $req->start_date != $req->end_date ? ' – ' . date('d/m/Y', strtotime($req->end_date)) : '' ?>
                </td>
                <td><?= $req->total_days ?></td>
                <td style="font-size:.8rem;max-width:140px">
                  <?= htmlspecialchars(mb_substr($req->reason, 0, 40)) ?>    <?= mb_strlen($req->reason) > 40 ? '...' : '' ?></td>
                <td><span
                    class="badge bg-<?= array('pending' => 'warning text-dark', 'approved' => 'success', 'rejected' => 'danger', 'cancelled' => 'secondary')[$req->status] ?? 'secondary' ?>"><?= array('pending' => 'รอการอนุมัติ', 'approved' => 'อนุมัติ', 'rejected' => 'ปฏิเสธ', 'cancelled' => 'ยกเลิก')[$req->status] ?? $req->status ?></span>
                </td>
                <td>
                  <?php if ($req->status === 'pending'): ?>
                    <button class="btn btn-success btn-sm px-2 py-0" data-bs-toggle="modal"
                      data-bs-target="#mAp<?= $req->id ?>">✓</button>
                    <button class="btn btn-danger btn-sm px-2 py-0 ms-1" data-bs-toggle="modal"
                      data-bs-target="#mRj<?= $req->id ?>">✗</button>
                  <?php else: ?><span class="text-muted small">ดำเนินการแล้ว</span><?php endif; ?>
                </td>
              </tr>
              <div class="modal fade" id="mAp<?= $req->id ?>" tabindex="-1">
                <div class="modal-dialog modal-sm">
                  <div class="modal-content">
                    <div class="modal-header py-2 bg-success text-white">
                      <h6 class="modal-title mb-0">อนุมัติการลา</h6><button class="btn-close btn-close-white"
                        data-bs-dismiss="modal"></button>
                    </div><?= form_open('manager/leave/approve/' . $req->id) ?><input type="hidden"
                      name="<?= $this->security->get_csrf_token_name() ?>" value="<?= $this->security->get_csrf_hash() ?>">
                    <div class="modal-body">
                      <p class="small mb-2">อนุมัติการลาของ <strong><?= $req->first_name ?></strong> จำนวน
                        <?= $req->total_days ?> วัน?</p><textarea name="note" class="form-control form-control-sm" rows="2"
                        placeholder="หมายเหตุ (ถ้ามี)"></textarea>
                    </div>
                    <div class="modal-footer py-2"><button type="submit" class="btn btn-success btn-sm">ยืนยัน</button>
                    </div><?= form_close() ?>
                  </div>
                </div>
              </div>
              <div class="modal fade" id="mRj<?= $req->id ?>" tabindex="-1">
                <div class="modal-dialog modal-sm">
                  <div class="modal-content">
                    <div class="modal-header py-2 bg-danger text-white">
                      <h6 class="modal-title mb-0">ปฏิเสธการลา</h6><button class="btn-close btn-close-white"
                        data-bs-dismiss="modal"></button>
                    </div><?= form_open('manager/leave/reject/' . $req->id) ?><input type="hidden"
                      name="<?= $this->security->get_csrf_token_name() ?>" value="<?= $this->security->get_csrf_hash() ?>">
                    <div class="modal-body"><textarea name="note" class="form-control form-control-sm" rows="2"
                        placeholder="เหตุผล (จำเป็น)" required></textarea></div>
                    <div class="modal-footer py-2"><button type="submit" class="btn btn-danger btn-sm">ยืนยัน</button></div>
                    <?= form_close() ?>
                  </div>
                </div>
              </div>
            <?php endforeach; else: ?>
            <tr>
              <td colspan="7" class="text-center text-muted py-5">ไม่มีข้อมูลการลา</td>
            </tr><?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>