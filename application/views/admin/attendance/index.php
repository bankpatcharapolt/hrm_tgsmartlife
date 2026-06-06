<?php defined('BASEPATH') OR exit(); ?>
<!-- Filter Bar -->
<div class="card mb-3">
  <div class="card-body py-2">
    <?= form_open('admin/attendance', array('method' => 'GET', 'class' => 'row g-2 align-items-end')) ?>
    <div class="col-md-2"><select name="year"
        class="form-select form-select-sm"><?php for ($y = date('Y'); $y >= date('Y') - 2; $y--): ?>
          <option value="<?= $y ?>" <?= $year == $y ? 'selected' : '' ?>><?= $y ?></option><?php endfor; ?>
      </select></div>
    <div class="col-md-2"><select name="month"
        class="form-select form-select-sm"><?php $mn = array('1' => 'ม.ค.', '2' => 'ก.พ.', '3' => 'มี.ค.', '4' => 'เม.ย.', '5' => 'พ.ค.', '6' => 'มิ.ย.', '7' => 'ก.ค.', '8' => 'ส.ค.', '9' => 'ก.ย.', '10' => 'ต.ค.', '11' => 'พ.ย.', '12' => 'ธ.ค.');
        foreach ($mn as $k => $v): ?>
          <option value="<?= $k ?>" <?= $month == $k ? 'selected' : '' ?>><?= $v ?></option><?php endforeach; ?>
      </select></div>
    <div class="col-md-2"><select name="dept" class="form-select form-select-sm ts-select">
        <option value="">-- ทุกแผนก --</option><?php foreach ($departments as $d): ?>
          <option value="<?= $d->id ?>" <?= $dept == $d->id ? 'selected' : '' ?>><?= $d->name ?></option><?php endforeach; ?>
      </select></div>
    <div class="col-md-2"><select name="shift_id" class="form-select form-select-sm">
        <option value="">-- ทุกกะ --</option><?php foreach ($shifts as $s): ?>
          <option value="<?= $s->id ?>" <?= $shift_id == $s->id ? 'selected' : '' ?>><?= $s->name ?></option><?php endforeach; ?>
      </select></div>
    <div class="col-md-2"><select name="status" class="form-select form-select-sm">
        <option value="">-- ทุกสถานะ --</option>
        <option value="present" <?= isset($sel_status) && $sel_status === 'present' ? 'selected' : '' ?>>มา</option>
        <option value="absent" <?= isset($sel_status) && $sel_status === 'absent' ? 'selected' : '' ?>>ขาดงาน</option>
        <option value="late" <?= isset($sel_status) && $sel_status === 'late' ? 'selected' : '' ?>>มาสาย</option>

      </select></div>
    <div class="col-auto"><button type="submit" class="btn btn-primary btn-sm"><i class="bi bi-search"></i></button>
    </div>
    <?= form_close() ?>
  </div>
</div>

<div class="d-flex justify-content-between align-items-center mb-2 flex-wrap gap-2">
  <span class="text-muted small">รายการทั้งหมด <strong><?= $total ?></strong> รายการ | หน้า
    <?= $page ?>/<?= $total_pages ?></span>
  <div class="d-flex gap-2">
    <a href="<?= base_url('admin/attendance/shifts') ?>" class="btn btn-outline-secondary btn-sm"><i
        class="bi bi-clock me-1"></i>จัดการกะ</a>
    <a href="<?= base_url('admin/attendance/manual') ?>" class="btn btn-primary btn-sm"><i
        class="bi bi-plus-lg me-1"></i>บันทึกด้วยตนเอง</a>
  </div>
</div>

<div class="card">
  <div class="card-body p-0">
    <div class="table-responsive">
      <table class="table table-hover mb-0">
        <thead>
          <tr>
            <th>วันที่</th>
            <th>พนักงาน</th>
            <th>กะ</th>
            <th>เข้างาน</th>
            <th>ออกงาน</th>
            <th>สถานะ</th>
            <th>สาย</th>
            <th>ลา (ชม.)</th>
            <th>OT</th>
            <th>จัดการ</th>
          </tr>
        </thead>
        <tbody>
          <?php if (!empty($records)):
            foreach ($records as $r): ?>
              <tr>
                <td style="font-size:.83rem;white-space:nowrap"><?= date('d/m/Y', strtotime($r->date)) ?></td>
                <td>
                  <div class="fw-semibold" style="font-size:.875rem"><?= $r->first_name . ' ' . $r->last_name ?></div>
                  <div style="font-size:.72rem;color:#6b7280"><?= $r->employee_id ?></div>
                </td>
                <td>
                  <?php if (!empty($r->shift_name)): ?>
                    <span class="badge"
                      style="background:<?= $r->shift_color ?? '#6b7280' ?>;font-size:.68rem"><?= $r->shift_name ?></span>
                  <?php else: ?><span class="text-muted small">–</span><?php endif; ?>
                </td>
                <td class="<?= $r->is_late ? 'text-danger fw-semibold' : '' ?>" style="font-size:.83rem">
                  <?= $r->check_in_time ? date('H:i', strtotime($r->check_in_time)) : '–' ?>
                </td>
                <td style="font-size:.83rem"><?= $r->check_out_time ? date('H:i', strtotime($r->check_out_time)) : '–' ?></td>
                <td>
                  <?php
                  $sc = array('present' => 'success', 'absent' => 'danger', 'leave' => 'info text-dark', 'holiday' => 'warning text-dark', 'half_day' => 'secondary');
                  $sl = array('present' => 'มา', 'absent' => 'ขาด', 'leave' => 'ลา', 'holiday' => 'วันหยุด', 'half_day' => 'ครึ่งวัน');
                  ?>
                  <span class="badge bg-<?= $sc[$r->status] ?? 'secondary' ?>"><?= $sl[$r->status] ?? $r->status ?></span>
                  <?php if ($r->is_late): ?><br><small class="text-danger">สาย <?= $r->late_minutes ?>
                      นาที</small><?php endif; ?>
                  <?php if (!empty($r->leave_type_name)): ?><br><small
                      class="text-info"><?= $r->leave_type_name ?></small><?php endif; ?>
                </td>
                <td class="<?= $r->late_minutes > 0 ? 'text-danger' : '' ?> small">
                  <?= $r->late_minutes > 0 ? $r->late_minutes . ' น.' : '–' ?></td>
                <td class="small">
                  <?php if ($r->status === 'leave' && $r->leave_hours > 0): ?>
                    <span class="badge bg-info text-dark"><?= $r->leave_hours ?> ชม.</span>
                  <?php else: ?>–<?php endif; ?>
                </td>
                <td class="small"><?= $r->ot_hours > 0 ? number_format($r->ot_hours, 1) . ' ชม.' : '–' ?></td>
                <td>
                  <?php if (!empty($r->id)): ?>
                    <a href="<?= base_url('admin/attendance/edit/' . $r->id) ?>"
                      class="btn btn-outline-secondary btn-sm px-2 py-0" title="แก้ไข"><i class="bi bi-pencil"></i></a>
                    <a href="<?= base_url('admin/attendance/delete/' . $r->id) ?>"
                      onclick="return confirm('ลบรายการวันที่ <?= date('d/m/Y', strtotime($r->date)) ?> ของ <?= $r->first_name ?> ใช่ไหม?')"
                      class="btn btn-outline-danger btn-sm px-2 py-0 ms-1" title="ลบ"><i class="bi bi-trash"></i></a>

                  <?php endif; ?>
                </td>
              </tr>
            <?php endforeach; else: ?>
            <tr>
              <td colspan="10" class="text-center text-muted py-5"><i
                  class="bi bi-clock fs-1 d-block mb-2"></i>ไม่มีข้อมูลการเข้างาน</td>
            </tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<?php
// ── Pagination ────────────────────────────────────────────────────
if ($total_pages > 1):
  // สร้าง base query string จาก filter ปัจจุบัน
  $q = array(
    'year' => $year,
    'month' => $month,
    'dept' => $dept,
    'shift_id' => $shift_id,
    'status' => $sel_status,
  );
  $base_q = http_build_query(array_filter($q, function ($v) {
    return $v !== '' && $v !== null; }));
  $base_url_p = base_url('admin/attendance') . '?' . $base_q . '&page=';
  ?>
  <nav class="mt-3">
    <ul class="pagination pagination-sm justify-content-center flex-wrap mb-0">
      <!-- ปุ่ม ก่อนหน้า -->
      <li class="page-item <?= $page <= 1 ? 'disabled' : '' ?>">
        <a class="page-link" href="<?= $base_url_p . ($page - 1) ?>">‹</a>
      </li>

      <?php
      // แสดงเลขหน้า: หน้าแรก ... กลุ่ม ... หน้าสุดท้าย
      $range = 2; // จำนวนหน้าที่แสดงรอบหน้าปัจจุบัน
      for ($p = 1; $p <= $total_pages; $p++):
        $show = ($p == 1 || $p == $total_pages ||
          ($p >= $page - $range && $p <= $page + $range));
        $gap_before = ($p == $total_pages && $page + $range + 1 < $total_pages);
        $gap_after = ($p == 2 && $page - $range - 1 > 1);
        if (!$show):
          continue;
        endif;
        ?>
        <?php if ($gap_after): ?>
          <li class="page-item disabled"><span class="page-link">…</span></li><?php endif; ?>
        <li class="page-item <?= $p == $page ? 'active' : '' ?>">
          <a class="page-link" href="<?= $base_url_p . $p ?>"><?= $p ?></a>
        </li>
        <?php if ($gap_before): ?>
          <li class="page-item disabled"><span class="page-link">…</span></li><?php endif; ?>
      <?php endfor; ?>

      <!-- ปุ่ม ถัดไป -->
      <li class="page-item <?= $page >= $total_pages ? 'disabled' : '' ?>">
        <a class="page-link" href="<?= $base_url_p . ($page + 1) ?>">›</a>
      </li>
    </ul>
    <div class="text-center text-muted mt-1" style="font-size:.75rem">
      แสดง <?= ($offset ?? 0) + 1 ?>–<?= min(($offset ?? 0) + $per_page, $total) ?> จาก <?= $total ?> รายการ
    </div>
  </nav>
<?php endif; ?>