<?php defined('BASEPATH') OR exit(); ?>
<div class="card">
  <div class="card-header"><i class="bi bi-bell me-2"></i>การแจ้งเตือนทั้งหมด</div>
  <div class="card-body p-0">
    <?php if(!empty($notifs)):foreach($notifs as $n):
      $ic=['leave_request'=>'calendar','leave_approved'=>'check-circle text-success','leave_rejected'=>'x-circle text-danger','salary_paid'=>'cash text-success','bonus_paid'=>'gift text-warning','document_uploaded'=>'file-earmark-arrow-down text-primary','late_checkin'=>'clock text-warning','general'=>'info-circle','meeting'=>'people text-primary','holiday'=>'calendar-event','target'=>'graph-up-arrow text-success'];
      $icon=$ic[$n->type]??'bell';?>
    <div class="d-flex align-items-start gap-3 px-3 py-3 border-bottom <?=!$n->is_read?'bg-light':''?>"><?php if($n->link):?><a href="<?=$n->link?>" style="text-decoration:none;color:inherit;display:contents;"><?php endif;?>
      <div style="width:38px;height:38px;border-radius:50%;background:#f3f4f6;display:flex;align-items:center;justify-content:center;flex-shrink:0"><i class="bi bi-<?=$icon?> fs-5"></i></div>
      <div class="flex-fill">
        <div style="font-size:.875rem;font-weight:<?=!$n->is_read?600:400?>"><?=htmlspecialchars($n->title)?></div>
        <div style="font-size:.82rem;color:#6b7280;margin-top:.15rem"><?=htmlspecialchars($n->message)?></div>
        <div style="font-size:.72rem;color:#9ca3af;margin-top:.25rem"><?=date('d เดือน m ปี Y เวลา H:i น.',strtotime($n->created_at))?></div>
      </div>
      <?php if($n->link):?></a><?php endif;?>
      <?php if(!$n->is_read):?><span class="badge rounded-pill bg-primary align-self-start mt-1" style="font-size:.58rem">ใหม่</span><?php endif;?>
    </div>
    <?php endforeach;else:?>
    <div class="text-center text-muted py-5"><i class="bi bi-bell-slash fs-1 d-block mb-2"></i>ไม่มีการแจ้งเตือน</div>
    <?php endif;?>
  </div>
</div>
