<?php defined('BASEPATH') OR exit(); ?>
<div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
  <div class="d-flex gap-2">
    <select class="form-select form-select-sm" style="width:auto" id="selYear">
      <?php for($y=date('Y');$y>=date('Y')-4;$y--):?>
      <option value="<?=$y?>" <?=$year==$y?'selected':''?>><?=$y?></option>
      <?php endfor;?>
    </select>
    <select class="form-select form-select-sm" style="width:auto" id="selMonth">
      <?php
      $mn_arr = array('1'=>'ม.ค.','2'=>'ก.พ.','3'=>'มี.ค.','4'=>'เม.ย.','5'=>'พ.ค.',
                      '6'=>'มิ.ย.','7'=>'ก.ค.','8'=>'ส.ค.','9'=>'ก.ย.','10'=>'ต.ค.',
                      '11'=>'พ.ย.','12'=>'ธ.ค.');
      foreach($mn_arr as $k=>$v):?>
      <option value="<?=$k?>" <?=$month==$k?'selected':''?>><?=$v?></option>
      <?php endforeach;?>
    </select>
    <button class="btn btn-primary btn-sm" onclick="goFilter()"><i class="bi bi-search"></i></button>
  </div>
  <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addSalesMod">
    <i class="bi bi-plus-lg me-1"></i>เพิ่มข้อมูลยอดขาย
  </button>
</div>

<div class="row g-3 mb-3">
  <div class="col-lg-8">
    <div class="card">
      <div class="card-header"><i class="bi bi-bar-chart me-2 text-primary"></i>ยอดขายรายเดือน ปี <?=$year?></div>
      <div class="card-body"><canvas id="salesChart" height="120"></canvas></div>
    </div>
  </div>
  <div class="col-lg-4">
    <div class="card">
      <div class="card-header"><i class="bi bi-trophy me-2 text-warning"></i>Top 5 พนักงาน</div>
      <div class="card-body p-0">
        <div class="list-group list-group-flush">
          <?php if(!empty($top)):foreach($top as $i=>$t):?>
          <div class="list-group-item d-flex align-items-center gap-2 py-2">
            <span class="badge bg-<?=$i===0?'warning text-dark':($i===1?'secondary':($i===2?'danger':'light text-dark'))?> rounded-circle" style="width:24px;height:24px;display:flex;align-items:center;justify-content:center"><?=$i+1?></span>
            <div class="flex-fill">
              <div style="font-size:.83rem;font-weight:600"><?php echo $t->first_name ? $t->first_name.' '.$t->last_name : '(ทีม)'; ?></div>
              <div style="font-size:.72rem;color:#6b7280"><?php echo isset($t->employee_id) ? $t->employee_id : (isset($t->team_name) ? $t->team_name : ''); ?></div>
            </div>
            <div class="text-end">
              <div class="text-success fw-semibold" style="font-size:.83rem">฿<?=number_format($t->actual_amount,0)?></div>
              <div style="font-size:.7rem;color:#6b7280"><?=number_format($t->achievement_pct,1)?>%</div>
            </div>
          </div>
          <?php endforeach;else:?>
          <div class="list-group-item text-center text-muted py-3 small">ไม่มีข้อมูล</div>
          <?php endif;?>
        </div>
      </div>
    </div>
  </div>
</div>

<div class="card">
  <div class="card-header">รายการยอดขาย <?=$month?>/<?=$year?></div>
  <div class="card-body p-0">
    <div class="table-responsive">
      <table class="table table-hover mb-0">
        <thead><tr><th>พนักงาน/ทีม</th><th>แผนก/ทีม</th><th>เป้าหมาย</th><th>ยอดจริง</th><th>%</th><th>ประเภท</th><th>จัดการ</th></tr></thead>
        <tbody>
          <?php if(!empty($records)):foreach($records as $r):$pct=$r->achievement_pct;?>
          <tr>
            <td>
              <?php if(!empty($r->first_name)):?>
              <?=$r->first_name.' '.$r->last_name?>
              <div style="font-size:.72rem;color:#6b7280"><?=$r->employee_id?></div>
              <?php elseif(!empty($r->team_name)):?>
              <div class="fw-semibold"><?=$r->team_name?></div>
              <?php else:?>
              <span class="text-muted small">–</span>
              <?php endif;?>
            </td>
            <td style="font-size:.82rem"><?php echo isset($r->dept_name)&&$r->dept_name ? $r->dept_name : (isset($r->team_name)&&$r->team_name ? $r->team_name : '–'); ?></td>
            <td>฿<?=number_format($r->target_amount,0)?></td>
            <td class="<?=$r->actual_amount>=$r->target_amount?'text-success':'text-danger'?> fw-semibold">฿<?=number_format($r->actual_amount,0)?></td>
            <td>
              <div class="progress" style="height:6px;width:80px">
                <div class="progress-bar bg-<?=$pct>=100?'success':($pct>=80?'warning':'danger')?>" style="width:<?=min(100,$pct)?>%"></div>
              </div>
              <small><?=number_format($pct,1)?>%</small>
            </td>
            <td><span class="badge bg-<?=$r->sales_type==='individual'?'primary':'success'?>"><?=$r->sales_type==='individual'?'รายบุคคล':'ทีม'?></span></td>
            <td>
              <a href="<?=base_url('admin/sales/delete/'.$r->id)?>" onclick="return confirm('ลบรายการนี้?')" class="btn btn-outline-danger btn-sm px-2 py-0"><i class="bi bi-trash"></i></a>
            </td>
          </tr>
          <?php endforeach;else:?>
          <tr><td colspan="7" class="text-center text-muted py-4">ไม่มีข้อมูลยอดขาย</td></tr>
          <?php endif;?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<!-- ══════════════════════════════════════════
     Modal เพิ่มยอดขาย
     - รายบุคคล: แสดง dropdown พนักงาน
     - ทีม/แผนก: แสดง dropdown ทีม (ซ่อนพนักงาน)
══════════════════════════════════════════ -->
<div class="modal fade" id="addSalesMod" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title"><i class="bi bi-graph-up me-2"></i>เพิ่มข้อมูลยอดขาย</h5>
        <button class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <form method="POST" action="<?=base_url('admin/sales/store')?>">
        <input type="hidden" name="<?=$this->security->get_csrf_token_name()?>" value="<?=$this->security->get_csrf_hash()?>">
        <div class="modal-body">

          <!-- ประเภท -->
          <div class="mb-3">
            <label class="form-label">ประเภท</label>
            <select name="sales_type" id="salesTypeSelect" class="form-select">
              <option value="individual">รายบุคคล</option>
              <option value="team">ทีม/สาขา</option>
            </select>
          </div>

          <!-- Section พนักงาน (แสดงเมื่อ individual) -->
          <div id="secEmployee" class="mb-3">
            <label class="form-label">พนักงาน</label>
            <select name="user_id" class="form-select ts-select">
              <option value="">-- เลือกพนักงาน --</option>
              <?php foreach($employees as $e):?>
              <option value="<?=$e->id?>"><?=$e->employee_id?> – <?=$e->first_name.' '.$e->last_name?></option>
              <?php endforeach;?>
            </select>
          </div>

          <!-- Section ทีม (ซ่อนโดย default) -->
          <div id="secTeamSelect" class="mb-3" style="display:none">
            <label class="form-label">ทีม/สาขา <span class="text-danger">*</span></label>
            <select name="team_id" class="form-select">
              <option value="">-- เลือกทีม --</option>
              <?php foreach($teams as $t):?>
              <option value="<?=$t->id?>">
                <?=$t->team_name?>
                <?php if(!empty($t->location)):?> (<?=$t->location?>)<?php endif;?>
              </option>
              <?php endforeach;?>
            </select>
            <?php if(empty($teams)):?>
            <div class="alert alert-warning py-1 px-2 mt-1 small">
              <i class="bi bi-exclamation-triangle me-1"></i>
              ยังไม่มีทีม <a href="<?=base_url('admin/teams')?>" target="_blank">เพิ่มทีม</a>
            </div>
            <?php endif;?>
          </div>

          <!-- ปี/เดือน -->
          <div class="row g-2 mb-3">
            <div class="col-6">
              <label class="form-label">ปี</label>
              <input type="number" name="record_year" class="form-control" value="<?=$year?>" required>
            </div>
            <div class="col-6">
              <label class="form-label">เดือน</label>
              <input type="number" name="record_month" class="form-control" min="1" max="12" value="<?=$month?>" required>
            </div>
          </div>

          <div class="row g-2 mb-3">
            <div class="col-6">
              <label class="form-label">เป้าหมาย (฿)</label>
              <input type="number" name="target_amount" class="form-control" min="0" step="0.01" required>
            </div>
            <div class="col-6">
              <label class="form-label">ยอดจริง (฿)</label>
              <input type="number" name="actual_amount" class="form-control" min="0" step="0.01" required>
            </div>
          </div>

          <div>
            <label class="form-label">หมายเหตุ</label>
            <input type="text" name="note" class="form-control">
          </div>
        </div>
        <div class="modal-footer">
          <button type="submit" class="btn btn-primary">บันทึก</button>
          <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">ยกเลิก</button>
        </div>
      </form>
    </div>
  </div>
</div>

<?php
// สร้าง chart data ปลอดภัย ไม่ใช้ ??
$yData = array(); $tData = array();
foreach($yearly as $row) {
    $yData[$row->record_month] = $row->actual;
    $tData[$row->record_month] = $row->target;
}
$chart_labels  = array();
$chart_actual  = array();
$chart_target  = array();
for($cm = 1; $cm <= 12; $cm++) {
    $chart_labels[] = $cm;
    $chart_actual[] = isset($yData[$cm]) ? (float)$yData[$cm] : 0;
    $chart_target[] = isset($tData[$cm]) ? (float)$tData[$cm] : 0;
}
?>
<script>
// ── สลับ dropdown ตามประเภท ──────────────────────────────
function switchSalesType(val) {
    var empSec  = document.getElementById('secEmployee');
    var teamSec = document.getElementById('secTeamSelect');
    if (val === 'team') {
        empSec.style.display  = 'none';
        teamSec.style.display = '';
    } else {
        empSec.style.display  = '';
        teamSec.style.display = 'none';
    }
}

// ผูก event กับ select ประเภท
document.getElementById('salesTypeSelect').addEventListener('change', function() {
    switchSalesType(this.value);
});

// reset ทุกครั้งที่ modal เปิด
document.getElementById('addSalesMod').addEventListener('show.bs.modal', function() {
    var sel = document.getElementById('salesTypeSelect');
    sel.value = 'individual';
    switchSalesType('individual');
});

function goFilter() {
    var y = document.getElementById('selYear').value;
    var m = document.getElementById('selMonth').value;
    window.location = '<?=base_url('admin/sales')?>?year=' + y + '&month=' + m;
}

// ── Chart ─────────────────────────────────────────────────
var salesCtx = document.getElementById('salesChart').getContext('2d');
new Chart(salesCtx, {
    type: 'bar',
    data: {
        labels: [<?php echo implode(',', $chart_labels); ?>],
        datasets: [
            {
                label: 'ยอดจริง',
                data: [<?php echo implode(',', $chart_actual); ?>],
                backgroundColor: 'rgba(26,86,219,.7)'
            },
            {
                label: 'เป้าหมาย',
                data: [<?php echo implode(',', $chart_target); ?>],
                type: 'line',
                borderColor: '#f59e0b',
                backgroundColor: 'transparent',
                tension: 0.4
            }
        ]
    },
    options: {
        responsive: true,
        plugins: { legend: { position: 'top' } }
    }
});
</script>
