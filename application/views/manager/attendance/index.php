<?php defined('BASEPATH') OR exit(); ?>

<!-- Filter bar -->
<div class="card mb-3">
  <div class="card-body py-2">
    <?= form_open('manager/attendance', array('method'=>'GET','class'=>'row g-2 align-items-end')) ?>
      <div class="col-md-2">
        <select name="year" class="form-select form-select-sm">
          <?php for ($yy = date('Y'); $yy >= date('Y')-2; $yy--): ?>
            <option value="<?=$yy?>" <?=$year==$yy?'selected':''?>><?=$yy?></option>
          <?php endfor; ?>
        </select>
      </div>
      <div class="col-md-2">
        <select name="month" class="form-select form-select-sm">
          <?php
          $mn = array(1=>'มกราคม',2=>'กุมภาพันธ์',3=>'มีนาคม',4=>'เมษายน',
                      5=>'พฤษภาคม',6=>'มิถุนายน',7=>'กรกฎาคม',8=>'สิงหาคม',
                      9=>'กันยายน',10=>'ตุลาคม',11=>'พฤศจิกายน',12=>'ธันวาคม');
          foreach ($mn as $k=>$v): ?>
            <option value="<?=$k?>" <?=$month==$k?'selected':''?>><?=$v?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="col-md-3">
        <select name="user_id" class="form-select form-select-sm">
          <option value="">-- พนักงานทุกคนในทีม --</option>
          <?php foreach ($team_members as $tm): ?>
            <option value="<?=$tm->id?>" <?=$uid_filter==$tm->id?'selected':''?>>
              <?=$tm->employee_id?> – <?=$tm->first_name.' '.$tm->last_name?>
            </option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="col-auto">
        <button type="submit" class="btn btn-primary btn-sm">
          <i class="bi bi-search me-1"></i>ค้นหา
        </button>
      </div>
    <?= form_close() ?>
  </div>
</div>

<?php
// ── รวม records + absent_map เป็น all_rows เรียงตามวันที่ (DESC) + ชื่อ (ASC) ──
// all_rows[key] = array('type'=>'record'|'absent', 'date'=>..., ...)

$all_rows = array();

foreach ($records as $r) {
    // key: วันที่+user_id เพื่อกัน key ชน
    $key = $r->date . '_' . $r->user_id;
    $all_rows[$key] = array('type' => 'record', 'data' => $r, 'date' => $r->date);
}

foreach ($absent_map as $date => $members) {
    foreach ($members as $idx => $mem) {
        $key = $date . '_absent_' . $mem['user_id'] . '_' . $idx;
        $all_rows[$key] = array('type' => 'absent', 'date' => $date, 'member' => $mem);
    }
}

// เรียง DESC วันที่, ASC ชื่อ
uasort($all_rows, function($a, $b) {
    $dc = strcmp($b['date'], $a['date']); // DESC date
    if ($dc !== 0) return $dc;
    // ถ้าวันเดียวกัน เรียง ASC ชื่อ
    $na = $a['type']==='record' ? $a['data']->first_name : $a['member']['first_name'];
    $nb = $b['type']==='record' ? $b['data']->first_name : $b['member']['first_name'];
    return strcmp($na, $nb);
});

// สรุป summary จาก records
$summary = array('present'=>0,'late'=>0,'absent_cnt'=>0,'ot'=>0.0,'total_late_min'=>0);
foreach ($records as $r) {
    if (in_array($r->status, array('present','half_day'))) $summary['present']++;
    if ($r->is_late) { $summary['late']++; $summary['total_late_min'] += $r->late_minutes; }
    if ($r->status === 'absent') $summary['absent_cnt']++;
    $summary['ot'] += (float)$r->ot_hours;
}
// นับวันขาดงานจาก absent_map
$absent_total = 0;
foreach ($absent_map as $d => $mems) $absent_total += count($mems);
$summary['absent_cnt'] += $absent_total;
?>

<!-- Summary cards -->
<div class="row g-2 mb-3">
  <div class="col-6 col-md-3">
    <div class="stat-card">
      <div class="s-ico" style="background:#f0fdf4;color:#16a34a"><i class="bi bi-check-circle"></i></div>
      <div><div class="s-lbl">มาทำงาน</div><div class="s-val text-success"><?=$summary['present']?></div><div class="s-sub">รายการ</div></div>
    </div>
  </div>
  <div class="col-6 col-md-3">
    <div class="stat-card">
      <div class="s-ico" style="background:#fffbeb;color:#d97706"><i class="bi bi-clock-history"></i></div>
      <div><div class="s-lbl">มาสาย</div><div class="s-val text-warning"><?=$summary['late']?></div><div class="s-sub"><?=$summary['total_late_min']?> นาที</div></div>
    </div>
  </div>
  <div class="col-6 col-md-3">
    <div class="stat-card">
      <div class="s-ico" style="background:#fef2f2;color:#dc2626"><i class="bi bi-x-circle"></i></div>
      <div><div class="s-lbl">ขาดงาน</div><div class="s-val text-danger"><?=$summary['absent_cnt']?></div><div class="s-sub">รายการ (ไม่รวม ส-อ, วันหยุด)</div></div>
    </div>
  </div>
  <div class="col-6 col-md-3">
    <div class="stat-card">
      <div class="s-ico" style="background:#f0fdf4;color:#059669"><i class="bi bi-lightning-charge"></i></div>
      <div><div class="s-lbl">OT รวม</div><div class="s-val"><?=number_format($summary['ot'],1)?></div><div class="s-sub">ชม.</div></div>
    </div>
  </div>
</div>

<!-- ตารางข้อมูล -->
<div class="card">
  <div class="card-header d-flex align-items-center justify-content-between">
    <span>
      <i class="bi bi-people me-2"></i>การเข้างานทีม
      <?php if ($uid_filter): ?>
        — <?php foreach($team_members as $tm) if($tm->id==$uid_filter) echo htmlspecialchars($tm->first_name.' '.$tm->last_name); ?>
      <?php endif; ?>
    </span>
    <span class="badge bg-secondary"><?=count($all_rows)?> รายการ</span>
  </div>
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
            <th>OT</th>
            <th>หมายเหตุ</th>
            <th>ย้อนหลัง</th>
          </tr>
        </thead>
        <tbody>
          <?php
          $dow_th = array('Mon'=>'จ.','Tue'=>'อ.','Wed'=>'พ.','Thu'=>'พฤ.','Fri'=>'ศ.','Sat'=>'ส.','Sun'=>'อา.');
          $sc = array('present'=>'success','absent'=>'danger','leave'=>'info text-dark','holiday'=>'warning text-dark','half_day'=>'secondary');
          $sl = array('present'=>'มาทำงาน','absent'=>'ขาดงาน','leave'=>'ลา','holiday'=>'วันหยุด','half_day'=>'ครึ่งวัน');

          if (!empty($all_rows)):
            foreach ($all_rows as $row):
              if ($row['type'] === 'absent'):
                $d   = $row['date'];
                $mem = $row['member'];
                $dow = $dow_th[date('D', strtotime($d))] ?? date('D', strtotime($d));
          ?>
          <tr class="table-danger">
            <td style="font-size:.83rem;white-space:nowrap">
              <?=date('d/m/Y', strtotime($d))?>
              <br><small class="text-danger fw-semibold"><?=$dow?></small>
            </td>
            <td style="font-size:.83rem">
              <span class="fw-semibold"><?=htmlspecialchars($mem['first_name'].' '.$mem['last_name'])?></span>
              <br><small class="text-muted"><?=htmlspecialchars($mem['employee_id'])?></small>
            </td>
            <td colspan="6" class="text-danger" style="font-size:.83rem">
              <i class="bi bi-x-circle me-1"></i><strong>ขาดงาน</strong>
              <small class="text-muted ms-1">(ไม่มีบันทึก)</small>
            </td>
            <td>–</td>
            <td>–</td>
          </tr>

          <?php else:
            $r   = $row['data'];
            $dow = $dow_th[date('D', strtotime($r->date))] ?? date('D', strtotime($r->date));
          ?>
          <tr>
            <td style="font-size:.83rem;white-space:nowrap">
              <?=date('d/m/Y', strtotime($r->date))?>
              <br><small class="text-muted"><?=$dow?></small>
            </td>
            <td style="font-size:.83rem">
              <span class="fw-semibold"><?=htmlspecialchars($r->first_name.' '.$r->last_name)?></span>
              <br><small class="text-muted"><?=htmlspecialchars($r->employee_id)?></small>
            </td>
            <td>
              <?php if (!empty($r->shift_name)): ?>
                <span class="badge" style="background:<?=htmlspecialchars($r->shift_color??'#6b7280')?>;font-size:.67rem">
                  <?=htmlspecialchars($r->shift_name)?>
                </span>
              <?php else: ?><span class="text-muted small">–</span><?php endif; ?>
            </td>
            <td class="<?=$r->is_late?'text-danger fw-semibold':''?>" style="font-size:.83rem">
              <?=$r->check_in_time ? date('H:i', strtotime($r->check_in_time)) : '–'?>
            </td>
            <td style="font-size:.83rem">
              <?=$r->check_out_time ? date('H:i', strtotime($r->check_out_time)) : '–'?>
            </td>
            <td>
              <span class="badge bg-<?=$sc[$r->status]??'secondary'?>">
                <?=$sl[$r->status]??$r->status?>
              </span>
              <?php if ($r->is_late): ?>
                <br><small class="text-danger">สาย <?=$r->late_minutes?> น.</small>
              <?php endif; ?>
            </td>
            <td class="small <?=$r->late_minutes>0?'text-danger':''?>">
              <?=$r->late_minutes>0 ? $r->late_minutes.' น.' : '–'?>
            </td>
            <td class="small">
              <?=$r->ot_hours>0 ? number_format($r->ot_hours,1).' ชม.' : '–'?>
            </td>
            <td style="font-size:.8rem;max-width:120px">
              <?=htmlspecialchars($r->note??'')?>
            </td>
            <td>
              <?php if (!empty($r->is_manual)): ?>
                <?php
                $ab = array('pending'=>'bg-warning text-dark','approved'=>'bg-success','rejected'=>'bg-danger');
                $lb = array('pending'=>'รออนุมัติ','approved'=>'อนุมัติ','rejected'=>'ปฏิเสธ');
                $st = $r->approval_status ?? 'pending';
                ?>
                <span class="badge <?=$ab[$st]??'bg-secondary'?>"><?=$lb[$st]??$st?></span>
              <?php else: ?>
                <span class="text-muted small">–</span>
              <?php endif; ?>
            </td>
          </tr>
          <?php endif; endforeach; else: ?>
          <tr>
            <td colspan="10" class="text-center text-muted py-5">
              <i class="bi bi-calendar-x fs-2 d-block mb-2"></i>ไม่มีข้อมูลการเข้างาน
            </td>
          </tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>
