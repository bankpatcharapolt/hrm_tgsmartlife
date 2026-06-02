<?php defined('BASEPATH') OR exit(); ?>
<div class="row g-3 mb-4">
  <div class="col-6 col-xl-3"><div class="stat-card"><div class="s-ico" style="background:#eff6ff;color:#1a56db"><i class="bi bi-people-fill"></i></div><div><div class="s-lbl">พนักงานทั้งหมด</div><div class="s-val"><?=$stats['total_emp']?></div><div class="s-sub"><?=$stats['total_dept']?> แผนก</div></div></div></div>
  <div class="col-6 col-xl-3"><div class="stat-card"><div class="s-ico" style="background:#f0fdf4;color:#16a34a"><i class="bi bi-person-check-fill"></i></div><div><div class="s-lbl">มาทำงานวันนี้</div><div class="s-val text-success"><?=$stats['present_today']?></div><div class="s-sub">คน</div></div></div></div>
  <div class="col-6 col-xl-3"><div class="stat-card"><div class="s-ico" style="background:#fff7ed;color:#ea580c"><i class="bi bi-clock-history"></i></div><div><div class="s-lbl">มาสายวันนี้</div><div class="s-val text-warning"><?=$stats['late_today']?></div><div class="s-sub">คน</div></div></div></div>
  <div class="col-6 col-xl-3"><div class="stat-card"><div class="s-ico" style="background:#fffbeb;color:#d97706"><i class="bi bi-hourglass-split"></i></div><div><div class="s-lbl">รอการอนุมัติลา</div><div class="s-val text-warning"><?=$stats['pending_leave']?></div><div class="s-sub">คำขอ</div></div></div></div>
</div>
<div class="row g-3">
  <div class="col-lg-4">
    <div class="card h-100">
      <div class="card-header"><i class="bi bi-cash-stack me-2 text-primary"></i>สรุปเงินเดือน <?=$month?>/<?=$year?></div>
      <div class="card-body">
        <?php if($salary_sum&&$salary_sum->total_emp>0):?>
        <table class="table table-sm mb-0">
          <tr><td class="text-muted">จำนวนพนักงาน</td><td class="fw-semibold text-end"><?=number_format($salary_sum->total_emp)?> คน</td></tr>
          <tr><td class="text-muted">รายได้รวม</td><td class="fw-semibold text-success text-end">฿<?=number_format($salary_sum->total_gross,2)?></td></tr>
          <tr><td class="text-muted">ประกันสังคม</td><td class="fw-semibold text-danger text-end">-฿<?=number_format($salary_sum->total_ss,2)?></td></tr>
          <tr><td class="text-muted">ภาษีหัก ณ ที่จ่าย</td><td class="fw-semibold text-danger text-end">-฿<?=number_format($salary_sum->total_tax,2)?></td></tr>
          <tr style="background:#eff6ff"><td><strong>เงินเดือนสุทธิ</strong></td><td class="fw-bold text-primary text-end">฿<?=number_format($salary_sum->total_net,2)?></td></tr>
        </table>
        <?php else:?><div class="text-center text-muted py-4 small"><i class="bi bi-inbox fs-2 d-block mb-2"></i>ยังไม่มีข้อมูลเงินเดือนเดือนนี้</div><?php endif;?>
        <a href="<?=base_url('admin/salary')?>" class="btn btn-outline-primary btn-sm w-100 mt-3">จัดการเงินเดือน</a>
      </div>
    </div>
  </div>
  <div class="col-lg-8">
    <div class="card h-100">
      <div class="card-header d-flex justify-content-between align-items-center">
        <span><i class="bi bi-calendar-check me-2 text-warning"></i>คำขอลา รอการอนุมัติ</span>
        <a href="<?=base_url('admin/leave')?>" class="btn btn-outline-warning btn-sm">ดูทั้งหมด</a>
      </div>
      <div class="card-body p-0">
        <div class="table-responsive">
          <table class="table table-hover mb-0">
            <thead><tr><th>พนักงาน</th><th>ประเภท</th><th>วันที่</th><th>วัน</th><th>จัดการ</th></tr></thead>
            <tbody>
              <?php if(!empty($pending_leaves)):foreach($pending_leaves as $l):?>
              <tr>
                <td class="fw-semibold"><?=$l->first_name.' '.$l->last_name?></td>
                <td><span class="badge bg-info text-dark"><?=$l->leave_type_name?></span></td>
                <td style="font-size:.8rem"><?=date('d/m/Y',strtotime($l->start_date))?><?=$l->start_date!=$l->end_date?' – '.date('d/m/Y',strtotime($l->end_date)):''?></td>
                <td><?=$l->total_days?></td>
                <td>
                  <form method="POST" action="<?=base_url('admin/leave/approve/'.$l->id)?>" class="d-inline">
                    <input type="hidden" name="<?=$this->security->get_csrf_token_name()?>" value="<?=$this->security->get_csrf_hash()?>">
                    <button class="btn btn-success btn-sm px-2 py-0">✓</button>
                  </form>
                  <form method="POST" action="<?=base_url('admin/leave/reject/'.$l->id)?>" class="d-inline ms-1">
                    <input type="hidden" name="<?=$this->security->get_csrf_token_name()?>" value="<?=$this->security->get_csrf_hash()?>">
                    <button class="btn btn-danger btn-sm px-2 py-0">✗</button>
                  </form>
                </td>
              </tr>
              <?php endforeach;else:?><tr><td colspan="5" class="text-center text-muted py-4">ไม่มีคำขอลาที่รอดำเนินการ <a href="<?=base_url('admin/leave')?>">ดูทั้งหมด</a></td></tr><?php endif;?>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</div>
<div class="card mt-3">
  <div class="card-header"><i class="bi bi-activity me-2 text-muted"></i>กิจกรรมล่าสุด</div>
  <div class="card-body p-0">
    <div class="table-responsive">
      <table class="table table-sm mb-0">
        <thead><tr><th>เวลา</th><th>โมดูล</th><th>การกระทำ</th><th>รายละเอียด</th></tr></thead>
        <tbody>
          <?php if(!empty($activities)):foreach($activities as $a):?>
          <tr><td style="font-size:.75rem;white-space:nowrap"><?=date('d/m H:i',strtotime($a->created_at))?></td><td><span class="badge bg-secondary"><?=$a->module?></span></td><td style="font-size:.82rem"><?=$a->action?></td><td style="font-size:.8rem;color:#6b7280"><?=htmlspecialchars(mb_substr($a->description??'',0,60))?></td></tr>
          <?php endforeach;else:?><tr><td colspan="4" class="text-center text-muted py-3 small">ไม่มีกิจกรรม</td></tr><?php endif;?>
        </tbody>
      </table>
    </div>
  </div>
</div>
