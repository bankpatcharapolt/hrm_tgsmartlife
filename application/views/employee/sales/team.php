<?php defined('BASEPATH') OR exit();
$mn_th = array(1=>'ม.ค.',2=>'ก.พ.',3=>'มี.ค.',4=>'เม.ย.',5=>'พ.ค.',6=>'มิ.ย.',
               7=>'ก.ค.',8=>'ส.ค.',9=>'ก.ย.',10=>'ต.ค.',11=>'พ.ย.',12=>'ธ.ค.');

$total_actual  = $yearly_total ? (float)$yearly_total->total_actual : 0;
$total_target  = $yearly_total ? (float)$yearly_total->total_target : 0;
$total_achieve = $total_target > 0 ? round($total_actual / $total_target * 100, 1) : 0;
$cur_actual    = $current_month ? (float)$current_month->actual_amount : 0;
$cur_target    = $current_month ? (float)$current_month->target_amount : 0;
$cur_achieve   = $cur_target > 0 ? round($cur_actual / $cur_target * 100, 1) : 0;

$chart_actual = array_fill(1, 12, 0);
$chart_target = array_fill(1, 12, 0);
foreach ($monthly as $r) {
    $chart_actual[(int)$r->record_month] = (float)$r->actual_amount;
    $chart_target[(int)$r->record_month] = (float)$r->target_amount;
}
?>

<?php if(!$team): ?>
<div class="alert alert-warning">
  <i class="bi bi-exclamation-triangle me-2"></i>
  คุณยังไม่ได้เป็นสมาชิกของทีมใด กรุณาติดต่อผู้ดูแลระบบ
</div>
<?php return; endif; ?>

<!-- Filter -->
<div class="d-flex gap-2 mb-3 flex-wrap align-items-center justify-content-between">
  <div class="d-flex align-items-center gap-2 flex-wrap">
    <!-- Team badge -->
    <div class="d-flex align-items-center gap-2 px-3 py-2 rounded-3"
         style="background:#eff6ff;border:1px solid #bae6fd">
      <i class="bi bi-people-fill text-primary"></i>
      <span class="fw-semibold" style="font-size:.9rem"><?=htmlspecialchars($team->team_name)?></span>
      <?php if(!empty($team->location)):?>
      <span class="text-muted" style="font-size:.78rem">(<?=htmlspecialchars($team->location)?>)</span>
      <?php endif;?>
    </div>
  </div>
  <div class="d-flex gap-2">
    <select id="selYear" class="form-select form-select-sm" style="width:auto">
      <?php for($y2=date('Y');$y2>=date('Y')-4;$y2--):?>
      <option value="<?=$y2?>" <?=$year==$y2?'selected':''?>><?=$y2?></option>
      <?php endfor;?>
    </select>
    <select id="selMonth" class="form-select form-select-sm" style="width:auto">
      <?php foreach($mn_th as $k=>$v):?>
      <option value="<?=$k?>" <?=$month==$k?'selected':''?>><?=$v?></option>
      <?php endforeach;?>
    </select>
    <button id="btnFilter" class="btn btn-primary btn-sm"
            data-base="<?=base_url('employee/sales/team')?>">
      <i class="bi bi-search me-1"></i>ดูข้อมูล
    </button>
  </div>
</div>

<!-- Summary Cards -->
<div class="row g-2 mb-3">
  <div class="col-6 col-md-3">
    <div class="stat-card">
      <div class="s-ico" style="background:#eff6ff;color:#1a56db"><i class="bi bi-graph-up-arrow"></i></div>
      <div>
        <div class="s-lbl">ยอดขายทีม <?=$mn_th[$month]?></div>
        <div class="s-val text-primary" style="font-size:1.1rem">฿<?=number_format($cur_actual,0)?></div>
        <div class="s-sub">เป้า ฿<?=number_format($cur_target,0)?></div>
      </div>
    </div>
  </div>
  <div class="col-6 col-md-3">
    <div class="stat-card">
      <div class="s-ico" style="background:<?=$cur_achieve>=100?'#f0fdf4':($cur_achieve>=70?'#fffbeb':'#fef2f2')?>;color:<?=$cur_achieve>=100?'#16a34a':($cur_achieve>=70?'#d97706':'#dc2626')?>"><i class="bi bi-bullseye"></i></div>
      <div>
        <div class="s-lbl">บรรลุเป้า</div>
        <div class="s-val <?=$cur_achieve>=100?'text-success':($cur_achieve>=70?'text-warning':'text-danger')?>" style="font-size:1.1rem"><?=$cur_achieve?>%</div>
        <div class="s-sub"><?=$mn_th[$month]?> <?=$year?></div>
      </div>
    </div>
  </div>
  <div class="col-6 col-md-3">
    <div class="stat-card">
      <div class="s-ico" style="background:#f0fdf4;color:#16a34a"><i class="bi bi-currency-dollar"></i></div>
      <div>
        <div class="s-lbl">ยอดรวมทีมปี <?=$year?></div>
        <div class="s-val text-success" style="font-size:1.1rem">฿<?=number_format($total_actual,0)?></div>
        <div class="s-sub"><?=$total_achieve?>% ของเป้า</div>
      </div>
    </div>
  </div>
  <div class="col-6 col-md-3">
    <div class="stat-card">
      <div class="s-ico" style="background:#fdf4ff;color:#9333ea"><i class="bi bi-people-fill"></i></div>
      <div>
        <div class="s-lbl">สมาชิกทีม</div>
        <div class="s-val" style="font-size:1.1rem;color:#9333ea"><?=count($members)?></div>
        <div class="s-sub">คน</div>
      </div>
    </div>
  </div>
</div>

<!-- Charts -->
<div class="row g-3 mb-3">
  <!-- Bar chart ยอดขายทีมรายเดือน -->
  <div class="col-lg-8">
    <div class="card">
      <div class="card-header d-flex justify-content-between align-items-center">
        <span><i class="bi bi-bar-chart me-2 text-primary"></i>ยอดขายทีมรายเดือน ปี <?=$year?></span>
        <span class="badge bg-success">เป้ารวม ฿<?=number_format($total_target,0)?></span>
      </div>
      <div class="card-body"><canvas id="teamSalesChart" height="130"></canvas></div>
    </div>
  </div>
  <!-- เปรียบเทียบย้อนหลัง -->
  <div class="col-lg-4">
    <div class="card">
      <div class="card-header"><i class="bi bi-clock-history me-2 text-warning"></i>เปรียบเทียบย้อนหลัง</div>
      <div class="card-body">
        <canvas id="historyChart" height="170"></canvas>
        <div class="mt-3">
          <?php foreach(array_reverse($history, true) as $hy=>$htotal):?>
          <div class="d-flex justify-content-between align-items-center py-1 border-bottom" style="font-size:.83rem">
            <span class="<?=$hy==$year?'fw-bold text-primary':''?>">ปี <?=$hy?></span>
            <span class="<?=$hy==$year?'fw-bold text-primary':''?>">฿<?=number_format($htotal,0)?></span>
          </div>
          <?php endforeach;?>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Progress เป้าปีนี้ -->
<?php if($total_target > 0):?>
<div class="card mb-3">
  <div class="card-body py-2">
    <div class="d-flex justify-content-between align-items-center mb-1">
      <span style="font-size:.84rem;font-weight:600">ความคืบหน้าเป้าหมายทีมปี <?=$year?></span>
      <span class="fw-bold <?=$total_achieve>=100?'text-success':'text-primary'?>"><?=$total_achieve?>%</span>
    </div>
    <div class="progress" style="height:10px;border-radius:6px">
      <div class="progress-bar <?=$total_achieve>=100?'bg-success':($total_achieve>=70?'bg-warning':'bg-primary')?>"
           style="width:<?=min(100,$total_achieve)?>%;border-radius:6px"></div>
    </div>
    <div class="d-flex justify-content-between mt-1" style="font-size:.75rem;color:#6b7280">
      <span>ทำได้ ฿<?=number_format($total_actual,0)?></span>
      <span>เป้า ฿<?=number_format($total_target,0)?></span>
    </div>
  </div>
</div>
<?php endif;?>

<!-- สมาชิกทีม + ยอดขายรายคน -->
<div class="card mb-3">
  <div class="card-header"><i class="bi bi-people me-2"></i>ยอดขายสมาชิกทีม <?=$mn_th[$month]?>/<?=$year?></div>
  <div class="card-body p-0">
    <div class="table-responsive">
      <table class="table table-hover mb-0">
        <thead>
          <tr><th>#</th><th>พนักงาน</th><th>ยอดขาย</th><th>เป้าหมาย</th><th>% บรรลุ</th></tr>
        </thead>
        <tbody>
          <?php if(!empty($members)):foreach($members as $i=>$mem):
            $mpct = $mem->target_amount > 0 ? round($mem->actual_amount/$mem->target_amount*100,1) : 0;
            $is_me = ($mem->id == $uid);
          ?>
          <tr class="<?=$is_me?'table-primary':''?>">
            <td style="font-size:.83rem;color:#6b7280"><?=$i+1?></td>
            <td>
              <div class="d-flex align-items-center gap-2">
                <?php if(!empty($mem->photo)):?>
                <img src="<?=base_url($mem->photo)?>" style="width:28px;height:28px;border-radius:50%;object-fit:cover">
                <?php else:?>
                <div style="width:28px;height:28px;background:#1a56db;border-radius:50%;display:flex;align-items:center;justify-content:center;color:#fff;font-size:.75rem;font-weight:700;flex-shrink:0"><?=mb_substr($mem->first_name,0,1)?></div>
                <?php endif;?>
                <div>
                  <div class="fw-semibold" style="font-size:.875rem">
                    <?=$mem->first_name.' '.$mem->last_name?>
                    <?php if($is_me):?><span class="badge bg-primary ms-1" style="font-size:.65rem">ฉัน</span><?php endif;?>
                  </div>
                  <div style="font-size:.72rem;color:#6b7280"><?=$mem->employee_id?></div>
                </div>
              </div>
            </td>
            <td class="fw-semibold <?=$mem->actual_amount>0?'text-success':'text-muted'?>">
              <?=$mem->actual_amount>0?'฿'.number_format($mem->actual_amount,0):'–'?>
            </td>
            <td style="font-size:.83rem">
              <?=$mem->target_amount>0?'฿'.number_format($mem->target_amount,0):'–'?>
            </td>
            <td>
              <?php if($mem->target_amount > 0):?>
              <div class="d-flex align-items-center gap-1">
                <div class="progress flex-fill" style="height:6px;min-width:50px">
                  <div class="progress-bar <?=$mpct>=100?'bg-success':($mpct>=70?'bg-warning':'bg-danger')?>"
                       style="width:<?=min(100,$mpct)?>%"></div>
                </div>
                <span style="font-size:.78rem" class="<?=$mpct>=100?'text-success':($mpct>=70?'text-warning':'text-danger')?>"><?=$mpct?>%</span>
              </div>
              <?php else:?>–<?php endif;?>
            </td>
          </tr>
          <?php endforeach;else:?>
          <tr><td colspan="5" class="text-center text-muted py-4 small">ไม่มีข้อมูลสมาชิก</td></tr>
          <?php endif;?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<!-- ตารางรายเดือนทีม -->
<div class="card">
  <div class="card-header"><i class="bi bi-table me-2"></i>รายละเอียดรายเดือนทีม ปี <?=$year?></div>
  <div class="card-body p-0">
    <div class="table-responsive">
      <table class="table table-hover mb-0">
        <thead>
          <tr><th>เดือน</th><th>ยอดขายทีมรวม</th><th>เป้าหมาย</th><th>% บรรลุ</th><th>หมายเหตุ</th></tr>
        </thead>
        <tbody>
          <?php
          $has_data = false;
          $monthly_map = array();
          foreach ($monthly as $r) { $monthly_map[(int)$r->record_month] = $r; }
          for ($mo = 1; $mo <= 12; $mo++):
            $row = isset($monthly_map[$mo]) ? $monthly_map[$mo] : null;
            if (!$row) continue;
            $has_data = true;
            $pct = $row->target_amount > 0 ? round($row->actual_amount/$row->target_amount*100,1) : 0;
          ?>
          <tr <?=$mo==$month?'class="table-primary"':''?>>
            <td>
              <span class="fw-semibold"><?=$mn_th[$mo]?></span>
              <?php if($mo==$month):?><span class="badge bg-primary ms-1" style="font-size:.65rem">เดือนนี้</span><?php endif;?>
            </td>
            <td class="fw-semibold <?=$row->actual_amount>=$row->target_amount&&$row->target_amount>0?'text-success':'text-primary'?>">
              ฿<?=number_format($row->actual_amount,0)?>
            </td>
            <td style="font-size:.83rem"><?=$row->target_amount>0?'฿'.number_format($row->target_amount,0):'–'?></td>
            <td>
              <?php if($row->target_amount > 0):?>
              <div class="d-flex align-items-center gap-1">
                <div class="progress flex-fill" style="height:6px;min-width:50px">
                  <div class="progress-bar <?=$pct>=100?'bg-success':($pct>=70?'bg-warning':'bg-danger')?>"
                       style="width:<?=min(100,$pct)?>%"></div>
                </div>
                <span style="font-size:.78rem" class="<?=$pct>=100?'text-success':($pct>=70?'text-warning':'text-danger')?>"><?=$pct?>%</span>
              </div>
              <?php else:?>–<?php endif;?>
            </td>
            <td style="font-size:.8rem;color:#6b7280"><?=htmlspecialchars($row->note??'')?></td>
          </tr>
          <?php endfor;?>
          <?php if(!$has_data):?>
          <tr><td colspan="5" class="text-center text-muted py-5">
            <i class="bi bi-graph-up fs-2 d-block mb-2"></i>ยังไม่มีข้อมูลยอดขายทีมปี <?=$year?>
          </td></tr>
          <?php endif;?>
        </tbody>
        <?php if($has_data):?>
        <tfoot class="table-light fw-semibold">
          <tr>
            <td>รวมทั้งปี</td>
            <td class="text-success">฿<?=number_format($total_actual,0)?></td>
            <td style="font-size:.83rem">฿<?=number_format($total_target,0)?></td>
            <td><?=$total_achieve?>%</td>
            <td></td>
          </tr>
        </tfoot>
        <?php endif;?>
      </table>
    </div>
  </div>
</div>

<script>
window.addEventListener('load', function(){
  (function(){
    var btn = document.getElementById('btnFilter');
    if(btn){
      btn.addEventListener('click', function(){
        var base = this.getAttribute('data-base');
        var y = document.getElementById('selYear').value;
        var m = document.getElementById('selMonth').value;
        window.location.href = base + '?year=' + y + '&month=' + m;
      });
    }

    var actualData = <?= json_encode(array_values($chart_actual)) ?>;
    var targetData = <?= json_encode(array_values($chart_target)) ?>;
    var labels = ['ม.ค.','ก.พ.','มี.ค.','เม.ย.','พ.ค.','มิ.ย.','ก.ค.','ส.ค.','ก.ย.','ต.ค.','พ.ย.','ธ.ค.'];

    new Chart(document.getElementById('teamSalesChart'), {
      type: 'bar',
      data: {
        labels: labels,
        datasets: [
          { label: 'ยอดขายทีมจริง', data: actualData, backgroundColor: 'rgba(26,86,219,.75)', borderRadius: 5, order: 1 },
          { label: 'เป้าหมาย', data: targetData, type: 'line', borderColor: '#f59e0b',
            backgroundColor: 'transparent', borderWidth: 2, pointRadius: 3, tension: 0.3, order: 0 }
        ]
      },
      options: {
        responsive: true,
        plugins: { legend: { position: 'bottom', labels: { font: { family: 'Sarabun' } } } },
        scales: {
          x: { ticks: { font: { family: 'Sarabun', size: 11 } } },
          y: { ticks: { font: { family: 'Sarabun', size: 11 },
                        callback: function(v){ return '฿' + Number(v).toLocaleString(); } } }
        }
      }
    });

    var histYears  = <?= json_encode(array_keys($history)) ?>;
    var histTotals = <?= json_encode(array_values($history)) ?>;
    new Chart(document.getElementById('historyChart'), {
      type: 'bar',
      data: {
        labels: histYears,
        datasets: [{ label: 'ยอดขายทีมต่อปี', data: histTotals,
          backgroundColor: histYears.map(function(y,i){
            return i === histYears.length-1 ? 'rgba(26,86,219,.8)' : 'rgba(26,86,219,.3)';
          }), borderRadius: 6 }]
      },
      options: {
        responsive: true,
        plugins: { legend: { display: false } },
        scales: {
          x: { ticks: { font: { family: 'Sarabun', size: 11 } } },
          y: { ticks: { font: { family: 'Sarabun', size: 11 },
                        callback: function(v){ return '฿' + Number(v).toLocaleString(); } } }
        }
      }
    });
  })();
});
</script>
