<?php defined('BASEPATH') OR exit(); ?>

<!-- Filter bar -->
<div class="card mb-3">
  <div class="card-body py-2">
    <?= form_open('manager/attendance', array('method'=>'GET','class'=>'row g-2 align-items-end')) ?>
      <!-- ปี -->
      <div class="col-6 col-md-auto">
        <select name="year" class="form-select form-select-sm">
          <?php for ($yy = date('Y'); $yy >= date('Y')-2; $yy--): ?>
            <option value="<?=$yy?>" <?=$year==$yy?'selected':''?>><?=$yy?></option>
          <?php endfor; ?>
        </select>
      </div>

      <!-- เดือน -->
      <div class="col-6 col-md-auto">
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

      <!-- พนักงาน -->
      <div class="col-12 col-md-3">
        <select name="user_id" class="form-select form-select-sm">
          <option value="">-- พนักงานทุกคนในทีม --</option>
          <?php foreach ($team_members as $tm): ?>
            <option value="<?=$tm->id?>" <?=$uid_filter==$tm->id?'selected':''?>>
              <?=htmlspecialchars($tm->employee_id)?> – <?=htmlspecialchars($tm->first_name.' '.$tm->last_name)?>
            </option>
          <?php endforeach; ?>
        </select>
      </div>

      <!-- สถานะ -->
      <div class="col-12 col-md-auto">
        <?php
        $sf_opts = array(
          'present' => array('label'=>'มาทำงาน (ตรงเวลา)', 'icon'=>'bi-check-circle',    'cls'=>'btn-success'),
          'late'    => array('label'=>'มาสาย',              'icon'=>'bi-clock-history',   'cls'=>'btn-warning'),
          'absent'  => array('label'=>'ขาดงาน',             'icon'=>'bi-x-circle',        'cls'=>'btn-danger'),
          'leave'   => array('label'=>'ลา',                 'icon'=>'bi-calendar-check',  'cls'=>'btn-info'),
          'all'     => array('label'=>'ทั้งหมด',            'icon'=>'bi-list-ul',         'cls'=>'btn-secondary'),
        );
        ?>
        <div class="d-flex flex-wrap gap-1">
          <?php foreach ($sf_opts as $val => $opt): ?>
            <button type="submit" name="status_filter" value="<?=$val?>"
                    class="btn btn-sm <?= $status_filter===$val ? $opt['cls'] : 'btn-outline-'.str_replace('btn-','',$opt['cls']) ?>">
              <i class="bi <?=$opt['icon']?> me-1"></i><?=$opt['label']?>
            </button>
          <?php endforeach; ?>
        </div>
      </div>

    <?= form_close() ?>
  </div>
</div>

<?php
// ─────────────────────────────────────────────────────────────────────────────
// สร้าง all_rows รวม records + absent_map แล้ว filter ตาม status_filter
// ─────────────────────────────────────────────────────────────────────────────
$all_rows = array();

// เพิ่ม records
foreach ($records as $r) {
    $all_rows[$r->date.'_'.$r->user_id] = array(
        'type' => 'record',
        'date' => $r->date,
        'data' => $r,
    );
}

// เพิ่ม absent rows (เฉพาะ status_filter = absent หรือ all)
if ($status_filter === 'absent' || $status_filter === 'all') {
    foreach ($absent_map as $date => $mems) {
        foreach ($mems as $idx => $mem) {
            $key = $date.'_absent_'.$mem['user_id'].'_'.$idx;
            $all_rows[$key] = array('type'=>'absent','date'=>$date,'member'=>$mem);
        }
    }
}

// filter records ตาม status_filter
$filtered_rows = array();
foreach ($all_rows as $key => $row) {
    if ($row['type'] === 'absent') {
        // แสดงเฉพาะตอน filter = absent หรือ all
        if ($status_filter === 'absent' || $status_filter === 'all') {
            $filtered_rows[$key] = $row;
        }
        continue;
    }
    // record rows
    $r = $row['data'];
    $is_present = in_array($r->status, array('present','half_day'));
    $is_late    = (bool)$r->is_late;
    $is_leave   = ($r->status === 'leave');

    switch ($status_filter) {
        case 'present': // มาทำงานตรงเวลา (ไม่สาย)
            if ($is_present && !$is_late) $filtered_rows[$key] = $row;
            break;
        case 'late':    // มาสาย (มาแต่สาย)
            if ($is_present && $is_late)  $filtered_rows[$key] = $row;
            break;
        case 'leave':
            if ($is_leave) $filtered_rows[$key] = $row;
            break;
        case 'absent':  // แสดงเฉพาะ absent_map ไม่แสดง record
            break;
        case 'all':
        default:
            $filtered_rows[$key] = $row;
            break;
    }
}

// เรียง DESC วันที่, ASC ชื่อ
uasort($filtered_rows, function($a, $b) {
    $dc = strcmp($b['date'], $a['date']);
    if ($dc !== 0) return $dc;
    $na = $a['type']==='record' ? $a['data']->first_name : $a['member']['first_name'];
    $nb = $b['type']==='record' ? $b['data']->first_name : $b['member']['first_name'];
    return strcmp($na, $nb);
});

// สรุป (จาก records + absent_map ทั้งหมด ไม่ใช่ filtered)
$sum_present = 0; $sum_late = 0; $sum_absent = 0; $sum_ot = 0.0; $sum_late_min = 0;
foreach ($records as $r) {
    if (in_array($r->status, array('present','half_day'))) {
        $sum_present++;
        if ($r->is_late) { $sum_late++; $sum_late_min += $r->late_minutes; }
    }
    if ($r->status === 'absent') $sum_absent++;
    $sum_ot += (float)$r->ot_hours;
}
foreach ($absent_map as $d => $mems) $sum_absent += count($mems);
?>

<!-- Summary cards (แสดงยอดจริงทั้งหมดเสมอ ไม่ขึ้นกับ filter) -->
<div class="row g-2 mb-3">
  <div class="col-6 col-md-3">
    <div class="stat-card <?=$status_filter==='present'?'border border-success':''?>">
      <div class="s-ico" style="background:#f0fdf4;color:#16a34a"><i class="bi bi-check-circle"></i></div>
      <div>
        <div class="s-lbl">มาทำงาน (ตรงเวลา)</div>
        <div class="s-val text-success"><?=$sum_present - $sum_late?></div>
        <div class="s-sub">รายการ</div>
      </div>
    </div>
  </div>
  <div class="col-6 col-md-3">
    <div class="stat-card <?=$status_filter==='late'?'border border-warning':''?>">
      <div class="s-ico" style="background:#fffbeb;color:#d97706"><i class="bi bi-clock-history"></i></div>
      <div>
        <div class="s-lbl">มาสาย</div>
        <div class="s-val text-warning"><?=$sum_late?></div>
        <div class="s-sub"><?=$sum_late_min?> นาที</div>
      </div>
    </div>
  </div>
  <div class="col-6 col-md-3">
    <div class="stat-card <?=$status_filter==='absent'?'border border-danger':''?>">
      <div class="s-ico" style="background:#fef2f2;color:#dc2626"><i class="bi bi-x-circle"></i></div>
      <div>
        <div class="s-lbl">ขาดงาน</div>
        <div class="s-val text-danger"><?=$sum_absent?></div>
        <div class="s-sub">รายการ</div>
      </div>
    </div>
  </div>
  <div class="col-6 col-md-3">
    <div class="stat-card">
      <div class="s-ico" style="background:#f0fdf4;color:#059669"><i class="bi bi-lightning-charge"></i></div>
      <div>
        <div class="s-lbl">OT รวม</div>
        <div class="s-val"><?=number_format($sum_ot,1)?></div>
        <div class="s-sub">ชม.</div>
      </div>
    </div>
  </div>
</div>

<!-- ตาราง -->
<div class="card">
  <div class="card-header d-flex align-items-center justify-content-between">
    <span>
      <i class="bi bi-people me-2"></i>การเข้างานทีม
      <?php if ($uid_filter): ?>
        — <?php foreach($team_members as $tm) if($tm->id==$uid_filter) echo htmlspecialchars($tm->first_name.' '.$tm->last_name); ?>
      <?php endif; ?>
      <?php
        $sf_label = isset($sf_opts[$status_filter]) ? $sf_opts[$status_filter]['label'] : 'ทั้งหมด';
      ?>
      <span class="badge bg-secondary ms-1"><?=$sf_label?></span>
    </span>
    <span class="badge bg-secondary"><?=count($filtered_rows)?> รายการ</span>
  </div>
  <div class="card-body p-0">
    <div class="table-responsive">
      <table class="table table-hover mb-0">
        <thead>
          <tr>
            <th>วันที่</th><th>พนักงาน</th><th>กะ</th>
            <th>เข้างาน</th><th>ออกงาน</th><th>สถานะ</th>
            <th>สาย</th><th>OT</th><th>หมายเหตุ</th><th>ย้อนหลัง</th>
          </tr>
        </thead>
        <tbody>
          <?php
          $dow_th = array('Mon'=>'จ.','Tue'=>'อ.','Wed'=>'พ.','Thu'=>'พฤ.','Fri'=>'ศ.','Sat'=>'ส.','Sun'=>'อา.');
          $sc = array('present'=>'success','absent'=>'danger','leave'=>'info text-dark','holiday'=>'warning text-dark','half_day'=>'secondary');
          $sl = array('present'=>'มาทำงาน','absent'=>'ขาดงาน','leave'=>'ลา','holiday'=>'วันหยุด','half_day'=>'ครึ่งวัน');

          if (!empty($filtered_rows)):
            foreach ($filtered_rows as $row):
              $dow = $dow_th[date('D', strtotime($row['date']))] ?? date('D', strtotime($row['date']));
              if ($row['type'] === 'absent'):
                $mem = $row['member'];
          ?>
          <tr class="table-danger">
            <td style="font-size:.83rem;white-space:nowrap">
              <?=date('d/m/Y', strtotime($row['date']))?>
              <br><small class="text-danger fw-semibold"><?=$dow?></small>
            </td>
            <td style="font-size:.83rem">
              <span class="fw-semibold"><?=htmlspecialchars($mem['first_name'].' '.$mem['last_name'])?></span>
              <br><small class="text-muted"><?=htmlspecialchars($mem['employee_id'])?></small>
            </td>
            <td colspan="6" class="text-danger align-middle" style="font-size:.83rem">
              <i class="bi bi-x-circle me-1"></i><strong>ขาดงาน</strong>
              <small class="text-muted ms-1">(ไม่มีบันทึก)</small>
            </td>
            <td>–</td><td>–</td>
          </tr>

          <?php else:
            $r = $row['data'];
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
              <span class="badge bg-<?=$sc[$r->status]??'secondary'?>"><?=$sl[$r->status]??$r->status?></span>
              <?php if ($r->is_late): ?>
                <br><small class="text-danger">สาย <?=$r->late_minutes?> น.</small>
              <?php endif; ?>
            </td>
            <td class="small <?=$r->late_minutes>0?'text-danger':''?>">
              <?=$r->late_minutes>0 ? $r->late_minutes.' น.' : '–'?>
            </td>
            <td class="small"><?=$r->ot_hours>0 ? number_format($r->ot_hours,1).' ชม.' : '–'?></td>
            <td style="font-size:.8rem;max-width:120px"><?=htmlspecialchars($r->note??'')?></td>
            <td>
              <?php if (!empty($r->is_manual)):
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
              <i class="bi bi-calendar-x fs-2 d-block mb-2"></i>
              ไม่มีข้อมูล "<?=$sf_label?>" ในช่วงเวลานี้
            </td>
          </tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>
