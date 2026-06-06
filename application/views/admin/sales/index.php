<?php defined('BASEPATH') OR exit();
$mn_arr = array('1'=>'ม.ค.','2'=>'ก.พ.','3'=>'มี.ค.','4'=>'เม.ย.','5'=>'พ.ค.',
                '6'=>'มิ.ย.','7'=>'ก.ค.','8'=>'ส.ค.','9'=>'ก.ย.','10'=>'ต.ค.',
                '11'=>'พ.ย.','12'=>'ธ.ค.');

// ── สรุปยอดรวมปีนี้ (individual เท่านั้น) ──────────────────────
$total_actual_year  = 0; $total_target_year  = 0; $total_customers = 0;
foreach ($yearly as $r) {
    $total_actual_year  += (float)$r->actual;
    $total_target_year  += (float)$r->target;
    $total_customers    += (int)($r->customers ?? 0);
}
$total_achieve_year = $total_target_year > 0
    ? round($total_actual_year / $total_target_year * 100, 1) : 0;

// ── สรุปเดือนนี้ (individual เท่านั้น — controller ส่ง records_individual มาให้แล้ว)
$month_actual = 0; $month_target = 0; $month_customers = 0;
foreach ($records_individual as $r) {
    $month_actual    += (float)$r->actual_amount;
    $month_target    += (float)$r->target_amount;
    $month_customers += (int)($r->customer_count ?? 0);
}

// ── สรุปยอดทีมเดือนนี้ ───────────────────────────────────────────
$month_team_actual = 0; $month_team_target = 0;
foreach ($records_team as $r) {
    $month_team_actual += (float)$r->actual_amount;
    $month_team_target += (float)$r->target_amount;
}
?>

<!-- ── Filter Bar ── -->
<div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
  <div class="d-flex gap-2 flex-wrap">
    <select class="form-select form-select-sm" style="width:auto" id="selYear">
      <?php for($y=date('Y');$y>=date('Y')-4;$y--):?>
      <option value="<?=$y?>" <?=$year==$y?'selected':''?>><?=$y?></option>
      <?php endfor;?>
    </select>
    <select class="form-select form-select-sm" style="width:auto" id="selMonth">
      <?php foreach($mn_arr as $k=>$v):?>
      <option value="<?=$k?>" <?=$month==$k?'selected':''?>><?=$v?></option>
      <?php endforeach;?>
    </select>
    <button class="btn btn-primary btn-sm" onclick="goFilter()"><i class="bi bi-search me-1"></i>ค้นหา</button>
  </div>
  <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addSalesMod">
    <i class="bi bi-plus-lg me-1"></i>เพิ่มข้อมูลยอดขาย
  </button>
</div>

<!-- ── Summary Cards ── -->
<div class="row g-2 mb-3">
  <div class="col-6 col-md-3">
    <div class="stat-card">
      <div class="s-ico" style="background:#eff6ff;color:#1a56db"><i class="bi bi-graph-up-arrow"></i></div>
      <div>
        <div class="s-lbl">ยอดขาย<br>(คำนวณจากยอดขายรายบุคคล)<br> <?=$mn_arr[$month]?></div>
        <div class="s-val text-primary">฿<?=number_format($month_actual,0)?></div>
        <div class="s-sub">เป้า ฿<?=number_format($month_target,0)?></div>
      </div>
    </div>
  </div>
  <div class="col-6 col-md-3">
    <div class="stat-card">
      <div class="s-ico" style="background:#f0fdf4;color:#16a34a"><i class="bi bi-graph-up-arrow"></i></div>
      <div>
        <div class="s-lbl">ยอดรวมปี<br>(คำนวณจากยอดขายรายบุคคล)<br>  <?=$year?></div>
        <div class="s-val text-success">฿<?=number_format($total_actual_year,0)?></div>
        <div class="s-sub"><?=$total_achieve_year?>% ของเป้า</div>
      </div>
    </div>
  </div>
  <div class="col-6 col-md-3">
    <div class="stat-card">
      <div class="s-ico" style="background:#fdf4ff;color:#9333ea"><i class="bi bi-people-fill"></i></div>
      <div>
        <div class="s-lbl">ลูกค้าเดือนนี้</div>
        <div class="s-val" style="color:#9333ea"><?=number_format($month_customers,0)?></div>
        <div class="s-sub">ราย</div>
      </div>
    </div>
  </div>
  <div class="col-6 col-md-3">
    <div class="stat-card">
      <div class="s-ico" style="background:#fffbeb;color:#d97706"><i class="bi bi-bullseye"></i></div>
      <div>
        <div class="s-lbl">บรรลุเป้าปีนี้</div>
        <div class="s-val text-<?=$total_achieve_year>=100?'success':($total_achieve_year>=70?'warning':'danger')?>"><?=$total_achieve_year?>%</div>
        <div class="s-sub">เป้า ฿<?=number_format($total_target_year,0)?></div>
      </div>
    </div>
  </div>
</div>

<!-- ── Charts Row ── -->
<div class="row g-3 mb-3">
  <!-- Bar chart รายเดือน -->
  <div class="col-lg-8">
    <div class="card">
      <div class="card-header"><i class="bi bi-bar-chart me-2 text-primary"></i>ยอดขายรายเดือน ปี <?=$year?></div>
      <div class="card-body"><canvas id="salesChart" height="120"></canvas></div>
    </div>
  </div>
  <!-- Top 5 พนักงาน -->
  <div class="col-lg-4">
    <div class="card">
      <div class="card-header">Top 5 พนักงาน <?=$mn_arr[$month]?></div>
      <div class="card-body p-0">
        <div class="list-group list-group-flush">
          <?php if(!empty($top)):foreach($top as $i=>$t):?>
          <div class="list-group-item d-flex align-items-center gap-2 py-2">
            <span class="badge bg-<?=$i===0?'warning text-dark':($i===1?'secondary':($i===2?'danger':'light text-dark'))?> rounded-circle" style="width:24px;height:24px;display:flex;align-items:center;justify-content:center;flex-shrink:0"><?=$i+1?></span>
            <div class="flex-fill" style="min-width:0">
              <div style="font-size:.83rem;font-weight:600"><?=htmlspecialchars($t->first_name.' '.$t->last_name)?></div>
              <div style="font-size:.72rem;color:#6b7280"><?=$t->employee_id?></div>
            </div>
            <div class="text-end flex-shrink-0">
              <div class="text-success fw-semibold" style="font-size:.83rem">฿<?=number_format($t->actual_amount,0)?></div>
              <div style="font-size:.7rem;color:#6b7280"><?=number_format($t->achievement_pct,1)?>% <?=!empty($t->customer_count)?'· '.$t->customer_count.' ราย':''?></div>
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

<!-- ── Tab: รายเดือน | แยกพนักงาน | แยกทีม ── -->
<ul class="nav nav-tabs mb-3" id="salesTab">
  <li class="nav-item"><a class="nav-link active" data-bs-toggle="tab" href="#tabMonthly">ยอดรายเดือน (<?=$mn_arr[$month]?>)</a></li>
  <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#tabEmployee">แยกพนักงาน</a></li>
  <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#tabTeam">แยกทีม</a></li>
  <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#tabYearly">ยอดรายปี</a></li>
</ul>

<div class="tab-content">

  <!-- Tab 1: รายเดือน (รายการ) -->
  <div class="tab-pane fade show active" id="tabMonthly">
    <div class="card">
      <div class="card-header">รายการยอดขาย <?=$mn_arr[$month]?>/<?=$year?> <span class="badge bg-secondary"><?=count($records)?></span></div>
      <div class="card-body p-0">
        <div class="table-responsive">
          <table class="table table-hover mb-0">
            <thead><tr><th>พนักงาน/ทีม</th><th>แผนก/ทีม</th><th>เป้าหมาย</th><th>ยอดจริง</th><th>ลูกค้า</th><th>%</th><th>ประเภท</th><th></th></tr></thead>
            <tbody>
              <?php if(!empty($records)):foreach($records as $r):$pct=$r->achievement_pct;?>
              <tr>
                <td>
                  <?php if(!empty($r->first_name)):?><?=$r->first_name.' '.$r->last_name?><div style="font-size:.72rem;color:#6b7280"><?=$r->employee_id?></div>
                  <?php elseif(!empty($r->team_name)):?><div class="fw-semibold"><?=$r->team_name?></div>
                  <?php else:?><span class="text-muted small">–</span><?php endif;?>
                </td>
                <td style="font-size:.82rem"><?=isset($r->dept_name)&&$r->dept_name?$r->dept_name:(isset($r->team_name)&&$r->team_name?$r->team_name:'–')?></td>
                <td>฿<?=number_format($r->target_amount,0)?></td>
                <td class="<?=$r->actual_amount>=$r->target_amount?'text-success':'text-danger'?> fw-semibold">฿<?=number_format($r->actual_amount,0)?></td>
                <td><?=number_format($r->customer_count??0,0)?> ราย</td>
                <td>
                  <div class="progress" style="height:6px;width:70px"><div class="progress-bar bg-<?=$pct>=100?'success':($pct>=80?'warning':'danger')?>" style="width:<?=min(100,$pct)?>%"></div></div>
                  <small><?=number_format($pct,1)?>%</small>
                </td>
                <td><span class="badge bg-<?=$r->sales_type==='individual'?'primary':'success'?>"><?=$r->sales_type==='individual'?'รายบุคคล':'ทีม'?></span></td>
                <td><a href="<?=base_url('admin/sales/delete/'.$r->id)?>" onclick="return confirm('ลบรายการนี้?')" class="btn btn-outline-danger btn-sm px-2 py-0"><i class="bi bi-trash"></i></a></td>
              </tr>
              <?php endforeach;else:?>
              <tr><td colspan="8" class="text-center text-muted py-4">ไม่มีข้อมูลยอดขาย</td></tr>
              <?php endif;?>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>

  <!-- Tab 2: แยกพนักงาน -->
  <div class="tab-pane fade" id="tabEmployee">
    <div class="card">
      <div class="card-header">ยอดขายแยกพนักงาน ปี <?=$year?></div>
      <div class="card-body p-0">
        <div class="table-responsive">
          <table class="table table-hover mb-0">
            <thead><tr><th>พนักงาน</th><th>แผนก</th><th>ยอดขายรวม</th><th>เป้ารวม</th><th>ลูกค้ารวม</th><th>% เฉลี่ย</th></tr></thead>
            <tbody>
            <?php
            // group by user จาก $records_year (ส่งมาจาก controller)
            $emp_sum = array();
            foreach ($records_year_individual as $r) {
                $uid = $r->user_id;
                if (!isset($emp_sum[$uid])) {
                    $emp_sum[$uid] = array(
                        'name' => $r->first_name.' '.$r->last_name,
                        'eid'  => $r->employee_id,
                        'dept' => $r->dept_name ?? '–',
                        'act'  => 0, 'tgt' => 0, 'cust' => 0, 'cnt' => 0,
                    );
                }
                $emp_sum[$uid]['act']  += (float)$r->actual_amount;
                $emp_sum[$uid]['tgt']  += (float)$r->target_amount;
                $emp_sum[$uid]['cust'] += (int)($r->customer_count ?? 0);
                $emp_sum[$uid]['cnt']++;
            }
            usort($emp_sum, function($a,$b){ return $b['act'] <=> $a['act']; });
            foreach ($emp_sum as $e):
                $ep = $e['tgt']>0 ? round($e['act']/$e['tgt']*100,1) : 0;
            ?>
            <tr>
              <td><div class="fw-semibold" style="font-size:.875rem"><?=$e['name']?></div><div style="font-size:.72rem;color:#6b7280"><?=$e['eid']?></div></td>
              <td style="font-size:.82rem"><?=$e['dept']?></td>
              <td class="text-success fw-semibold">฿<?=number_format($e['act'],0)?></td>
              <td style="font-size:.83rem">฿<?=number_format($e['tgt'],0)?></td>
              <td><?=number_format($e['cust'],0)?> ราย</td>
              <td>
                <div class="d-flex align-items-center gap-1">
                  <div class="progress flex-fill" style="height:6px;min-width:50px"><div class="progress-bar bg-<?=$ep>=100?'success':($ep>=70?'warning':'danger')?>" style="width:<?=min(100,$ep)?>%"></div></div>
                  <span style="font-size:.78rem"><?=$ep?>%</span>
                </div>
              </td>
            </tr>
            <?php endforeach; if(empty($emp_sum)):?>
            <tr><td colspan="6" class="text-center text-muted py-4">ไม่มีข้อมูล</td></tr>
            <?php endif;?>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>

  <!-- Tab 3: แยกทีม -->
  <div class="tab-pane fade" id="tabTeam">
    <div class="card">
      <div class="card-header">ยอดขายแยกทีม ปี <?=$year?></div>
      <div class="card-body p-0">
        <div class="table-responsive">
          <table class="table table-hover mb-0">
            <thead><tr><th>ทีม</th><th>ยอดขายรวม</th><th>เป้ารวม</th><th>%</th></tr></thead>
            <tbody>
            <?php
            $team_sum = array();
            foreach ($records_year_team as $r) {
                $tid = $r->team_id;
                if (!isset($team_sum[$tid])) {
                    $team_sum[$tid] = array('name'=>$r->team_name??'ทีม '.$tid,'act'=>0,'tgt'=>0);
                }
                $team_sum[$tid]['act'] += (float)$r->actual_amount;
                $team_sum[$tid]['tgt'] += (float)$r->target_amount;
            }
            usort($team_sum, function($a,$b){ return $b['act'] <=> $a['act']; });
            foreach ($team_sum as $t):
                $tp = $t['tgt']>0 ? round($t['act']/$t['tgt']*100,1) : 0;
            ?>
            <tr>
              <td class="fw-semibold"><?=$t['name']?></td>
              <td class="text-success fw-semibold">฿<?=number_format($t['act'],0)?></td>
              <td style="font-size:.83rem">฿<?=number_format($t['tgt'],0)?></td>
              <td>
                <div class="d-flex align-items-center gap-1">
                  <div class="progress flex-fill" style="height:6px;min-width:60px"><div class="progress-bar bg-<?=$tp>=100?'success':($tp>=70?'warning':'danger')?>" style="width:<?=min(100,$tp)?>%"></div></div>
                  <span style="font-size:.78rem"><?=$tp?>%</span>
                </div>
              </td>
            </tr>
            <?php endforeach; if(empty($team_sum)):?>
            <tr><td colspan="4" class="text-center text-muted py-4">ไม่มีข้อมูลทีม</td></tr>
            <?php endif;?>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>

  <!-- Tab 4: ยอดรายปี -->
  <div class="tab-pane fade" id="tabYearly">
    <div class="card">
      <div class="card-header">ยอดขายรายปี <?=$year?> (แยกเดือน)</div>
      <div class="card-body p-0">
        <div class="table-responsive">
          <table class="table mb-0">
            <thead><tr><th>เดือน</th><th>ยอดขายรวม</th><th>เป้าหมาย</th><th>ลูกค้า</th><th>% บรรลุ</th></tr></thead>
            <tbody>
            <?php
            $yearly_map = array();
            foreach ($yearly as $r) { $yearly_map[(int)$r->record_month] = $r; }
            $grand_act = 0; $grand_tgt = 0; $grand_cust = 0;
            for ($mo = 1; $mo <= 12; $mo++):
                $row = isset($yearly_map[$mo]) ? $yearly_map[$mo] : null;
                $act  = $row ? (float)$row->actual   : 0;
                $tgt  = $row ? (float)$row->target    : 0;
                $cust = $row ? (int)($row->customers??0) : 0;
                $pct  = $tgt>0 ? round($act/$tgt*100,1) : 0;
                $grand_act += $act; $grand_tgt += $tgt; $grand_cust += $cust;
            ?>
            <tr <?=$mo==$month?'class="table-primary"':($act==0?'class="text-muted"':'')?>>
              <td><?=$mn_arr[$mo]?> <?=$mo==$month?'<span class="badge bg-primary ms-1" style="font-size:.65rem">เดือนนี้</span>':''?></td>
              <td class="<?=$act>=$tgt&&$tgt>0?'text-success fw-semibold':($act==0?'':'text-primary')?>"><?=$act>0?'฿'.number_format($act,0):'–'?></td>
              <td style="font-size:.83rem"><?=$tgt>0?'฿'.number_format($tgt,0):'–'?></td>
              <td><?=$cust>0?number_format($cust,0).' ราย':'–'?></td>
              <td>
                <?php if($tgt>0):?>
                <div class="d-flex align-items-center gap-1">
                  <div class="progress flex-fill" style="height:5px;min-width:50px"><div class="progress-bar bg-<?=$pct>=100?'success':($pct>=70?'warning':'danger')?>" style="width:<?=min(100,$pct)?>%"></div></div>
                  <span style="font-size:.78rem"><?=$pct?>%</span>
                </div>
                <?php else:?>–<?php endif;?>
              </td>
            </tr>
            <?php endfor;?>
            </tbody>
            <tfoot class="table-light fw-semibold">
              <tr>
                <td>รวมทั้งปี</td>
                <td class="text-success">฿<?=number_format($grand_act,0)?></td>
                <td style="font-size:.83rem">฿<?=number_format($grand_tgt,0)?></td>
                <td><?=number_format($grand_cust,0)?> ราย</td>
                <td><?=$grand_tgt>0?round($grand_act/$grand_tgt*100,1).'%':'–'?></td>
              </tr>
            </tfoot>
          </table>
        </div>
      </div>
    </div>
  </div>

</div><!-- /tab-content -->

<!-- ── Modal เพิ่มยอดขาย (+ customer_count) ── -->
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
          <div class="mb-3">
            <label class="form-label">ประเภท</label>
            <select name="sales_type" id="salesTypeSelect" class="form-select">
              <option value="individual">รายบุคคล</option>
              <option value="team">ทีม/สาขา</option>
            </select>
          </div>
          <div id="secEmployee" class="mb-3">
            <label class="form-label">พนักงาน</label>
            <select name="user_id" class="form-select ts-select">
              <option value="">-- เลือกพนักงาน --</option>
              <?php foreach($employees as $e):?>
              <option value="<?=$e->id?>"><?=$e->employee_id?> – <?=$e->first_name.' '.$e->last_name?></option>
              <?php endforeach;?>
            </select>
          </div>
          <div id="secTeamSelect" class="mb-3" style="display:none">
            <label class="form-label">ทีม/สาขา <span class="text-danger">*</span></label>
            <select name="team_id" id="teamIdSelect" class="form-select" onchange="fillTeamTarget(this)">
              <option value="" data-target="">-- เลือกทีม --</option>
              <?php foreach($teams as $t):?>
              <option value="<?=$t->id?>"
                      data-target="<?=(float)($t->monthly_target??0)?>">
                <?=$t->team_name?><?php if(!empty($t->location)):?> (<?=$t->location?>)<?php endif;?>
              </option>
              <?php endforeach;?>
            </select>
          </div>
          <div class="row g-2 mb-3">
            <div class="col-6"><label class="form-label">ปี</label><input type="number" name="record_year" class="form-control" value="<?=$year?>" required></div>
            <div class="col-6"><label class="form-label">เดือน</label><input type="number" name="record_month" class="form-control" min="1" max="12" value="<?=$month?>" required></div>
          </div>
          <div class="row g-2 mb-3">
            <div class="col-6"><label class="form-label">เป้าหมาย (฿)</label><input type="number" name="target_amount" class="form-control" min="0" step="0.01" required></div>
            <div class="col-6"><label class="form-label">ยอดจริง (฿)</label><input type="number" name="actual_amount" class="form-control" min="0" step="0.01" required></div>
          </div>
          <!-- [ข้อ 3.2] จำนวนลูกค้า -->
          <div class="mb-3">
            <label class="form-label"><i class="bi bi-people me-1 text-primary"></i>จำนวนลูกค้า</label>
            <div class="input-group">
              <input type="number" name="customer_count" class="form-control" min="0" value="0" placeholder="0">
              <span class="input-group-text">ราย</span>
            </div>
          </div>
          <div><label class="form-label">หมายเหตุ</label><input type="text" name="note" class="form-control"></div>
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
// chart data
$yData = array(); $tData = array();
foreach($yearly as $row) {
    $yData[$row->record_month] = $row->actual;
    $tData[$row->record_month] = $row->target;
}
$chart_labels = array(); $chart_actual = array(); $chart_target = array();
for($cm=1;$cm<=12;$cm++){
    $chart_labels[] = $cm;
    $chart_actual[] = isset($yData[$cm]) ? (float)$yData[$cm] : 0;
    $chart_target[] = isset($tData[$cm]) ? (float)$tData[$cm] : 0;
}
?>
<script>
function switchSalesType(val) {
  document.getElementById('secEmployee').style.display  = val==='team'?'none':'';
  document.getElementById('secTeamSelect').style.display = val==='team'?'':'none';
}
document.getElementById('salesTypeSelect').addEventListener('change',function(){ switchSalesType(this.value); });
document.getElementById('addSalesMod').addEventListener('show.bs.modal',function(){
  document.getElementById('salesTypeSelect').value='individual'; switchSalesType('individual');
  // reset team select และ target เมื่อเปิด modal ใหม่
  var ts = document.getElementById('teamIdSelect');
  if (ts) { ts.value = ''; }
  var ta = document.querySelector('[name=target_amount]');
  if (ta) { ta.value = ''; ta.removeAttribute('placeholder'); }
});

function fillTeamTarget(sel) {
  var opt    = sel.options[sel.selectedIndex];
  var target = opt ? parseFloat(opt.getAttribute('data-target')) : 0;
  var ta     = document.querySelector('[name=target_amount]');
  if (!ta) return;
  if (target > 0) {
    ta.value       = target;
    ta.placeholder = 'เป้าทีม: ฿' + Number(target).toLocaleString('th-TH');
    ta.style.borderColor = '#1a56db';
  } else {
    ta.value       = '';
    ta.placeholder = '';
    ta.style.borderColor = '';
  }
}
function goFilter(){
  window.location='<?=base_url('admin/sales')?>?year='+document.getElementById('selYear').value+'&month='+document.getElementById('selMonth').value;
}
new Chart(document.getElementById('salesChart').getContext('2d'),{
  type:'bar',
  data:{
    labels:[<?=implode(',', $chart_labels)?>],
    datasets:[
      {label:'ยอดจริง',data:[<?=implode(',',$chart_actual)?>],backgroundColor:'rgba(26,86,219,.7)',borderRadius:4},
      {label:'เป้าหมาย',data:[<?=implode(',',$chart_target)?>],type:'line',borderColor:'#f59e0b',backgroundColor:'transparent',tension:.4,borderWidth:2,pointRadius:3}
    ]
  },
  options:{responsive:true,plugins:{legend:{position:'bottom',labels:{font:{family:'Sarabun'}}}},
    scales:{y:{ticks:{callback:function(v){return '฿'+Number(v).toLocaleString();},font:{family:'Sarabun'}}}}}
});
</script>
