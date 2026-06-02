<?php defined('BASEPATH') OR exit(); ?>
<!-- Filter -->
<div class="card mb-3">
  <div class="card-body py-2">
    <?=form_open('admin/leave',['method'=>'GET','class'=>'row g-2 align-items-end'])?>
      <div class="col-md-2">
        <select name="status" class="form-select form-select-sm">
          <option value="">ทุกสถานะ</option>
          <option value="pending"   <?=($filters['status']??'')==='pending'  ?'selected':''?>>รอการอนุมัติ</option>
          <option value="approved"  <?=($filters['status']??'')==='approved' ?'selected':''?>>อนุมัติแล้ว</option>
          <option value="rejected"  <?=($filters['status']??'')==='rejected' ?'selected':''?>>ปฏิเสธแล้ว</option>
          <option value="cancelled" <?=($filters['status']??'')==='cancelled'?'selected':''?>>ยกเลิก</option>
        </select>
      </div>
      <div class="col-md-2">
        <select name="dept" class="form-select form-select-sm">
          <option value="">-- ทุกแผนก --</option>
          <?php foreach($departments as $d):?>
          <option value="<?=$d->id?>" <?=($filters['dept_id']??'')==$d->id?'selected':''?>><?=$d->name?></option>
          <?php endforeach;?>
        </select>
      </div>
      <div class="col-md-2">
        <select name="year" class="form-select form-select-sm">
          <?php for($y=date('Y');$y>=date('Y')-3;$y--):?>
          <option value="<?=$y?>" <?=($filters['year']??date('Y'))==$y?'selected':''?>><?=$y?></option>
          <?php endfor;?>
        </select>
      </div>
      <div class="col-auto">
        <button type="submit" class="btn btn-primary btn-sm"><i class="bi bi-search"></i></button>
        <a href="<?=base_url('admin/leave')?>" class="btn btn-outline-secondary btn-sm ms-1">ล้าง</a>
      </div>
      <div class="col-auto ms-auto">
        <a href="<?=base_url('admin/leave/create')?>" class="btn btn-success btn-sm">
          <i class="bi bi-plus-lg me-1"></i>เพิ่มคำขอลา
        </a>
      </div>
    <?=form_close()?>
  </div>
</div>

<div class="card">
  <div class="card-header d-flex justify-content-between align-items-center">
    <span><i class="bi bi-calendar-check me-2"></i>รายการคำขอลา <span class="badge bg-secondary ms-1"><?=count($requests)?></span></span>
  </div>
  <div class="card-body p-0">
    <div class="table-responsive">
      <table class="table table-hover mb-0">
        <thead>
          <tr><th>พนักงาน</th><th>ประเภทการลา</th><th>วันที่</th><th>จำนวน</th><th>เหตุผล</th><th>สถานะ</th><th>เอกสาร</th><th>จัดการ</th></tr>
        </thead>
        <tbody>
          <?php if(!empty($requests)):foreach($requests as $req):
            $sc=['pending'=>'warning text-dark','approved'=>'success','rejected'=>'danger','cancelled'=>'secondary'];
            $sl=['pending'=>'รอการอนุมัติ','approved'=>'อนุมัติแล้ว','rejected'=>'ปฏิเสธแล้ว','cancelled'=>'ยกเลิก'];
          ?>
          <tr>
            <td>
              <div class="fw-semibold" style="font-size:.875rem"><?=$req->first_name.' '.$req->last_name?></div>
              <div style="font-size:.72rem;color:#6b7280"><?=$req->employee_id?></div>
            </td>
            <td><span class="badge bg-info text-dark"><?=$req->leave_type_name?></span></td>
            <td style="font-size:.83rem;white-space:nowrap">
              <?=date('d/m/Y',strtotime($req->start_date))?>
              <?php if($req->start_date !== $req->end_date):?><br><small class="text-muted">ถึง <?=date('d/m/Y',strtotime($req->end_date))?></small><?php endif;?>
              <?php if(!empty($req->leave_unit) && $req->leave_unit==='hour' && !empty($req->start_time)):?>
              <br><small class="text-primary"><?=substr($req->start_time,0,5)?> – <?=substr($req->end_time??'',0,5)?></small>
              <?php endif;?>
            </td>
            <td>
              <?php if(!empty($req->leave_unit) && $req->leave_unit==='hour'):?>
              <span class="badge bg-primary"><?=number_format($req->total_hours??0,1)?> ชม.</span>
              <?php else:?>
              <?=$req->total_days?> วัน
              <?php endif;?>
            </td>
            <td style="font-size:.82rem;max-width:180px">
              <?=htmlspecialchars(mb_substr($req->reason,0,60))?><?=mb_strlen($req->reason)>60?'...':''?>
              <?php if(!empty($req->approver_note)):?>
              <br><small class="text-muted"><i class="bi bi-chat-left-text"></i> <?=htmlspecialchars(mb_substr($req->approver_note,0,40))?></small>
              <?php endif;?>
            </td>
            <td>
              <span class="badge bg-<?=$sc[$req->status]??'secondary'?>"><?=$sl[$req->status]??$req->status?></span>
              <?php if(!empty($req->ap_fn)):?>
              <br><small class="text-muted">โดย <?=$req->ap_fn?></small>
              <?php endif;?>
            </td>
            <td>
              <?php if(!empty($req->document_path)):?>
              <a href="<?=base_url($req->document_path)?>" target="_blank" class="btn btn-outline-secondary btn-sm px-2 py-0" title="ดูเอกสาร">
                <i class="bi bi-file-earmark"></i>
              </a>
              <?php else:?><span class="text-muted small">–</span><?php endif;?>
            </td>
            <td>
              <!-- Quick Approve/Reject (pending only) -->
              <?php if($req->status==='pending'):?>
              <button class="btn btn-success btn-sm px-2 py-0 mb-1"
                      data-bs-toggle="modal" data-bs-target="#apMod<?=$req->id?>" title="อนุมัติ">
                <i class="bi bi-check-lg"></i>
              </button>
              <button class="btn btn-danger btn-sm px-2 py-0 mb-1"
                      data-bs-toggle="modal" data-bs-target="#rjMod<?=$req->id?>" title="ปฏิเสธ">
                <i class="bi bi-x-lg"></i>
              </button>
              <?php endif;?>
              <!-- Edit/Delete -->
              <a href="<?=base_url('admin/leave/edit/'.$req->id)?>"
                 class="btn btn-outline-secondary btn-sm px-2 py-0 mb-1" title="แก้ไข">
                <i class="bi bi-pencil"></i>
              </a>
              <a href="<?=base_url('admin/leave/delete/'.$req->id)?>"
                 onclick="return confirm('ลบคำขอลาของ <?=addslashes($req->first_name)?> ใช่ไหม?\n(ไม่สามารถกู้คืนได้)')"
                 class="btn btn-outline-danger btn-sm px-2 py-0 mb-1" title="ลบ">
                <i class="bi bi-trash"></i>
              </a>
            </td>
          </tr>

          <!-- Approve Modal -->
          <div class="modal fade" id="apMod<?=$req->id?>" tabindex="-1">
            <div class="modal-dialog modal-sm"><div class="modal-content">
              <div class="modal-header bg-success text-white py-2">
                <h6 class="modal-title mb-0"><i class="bi bi-check-circle me-1"></i>อนุมัติการลา</h6>
                <button class="btn-close btn-close-white btn-sm" data-bs-dismiss="modal"></button>
              </div>
              <?=form_open('admin/leave/approve/'.$req->id)?>
              <input type="hidden" name="<?=$this->security->get_csrf_token_name()?>" value="<?=$this->security->get_csrf_hash()?>">
              <div class="modal-body">
                <p class="small mb-2">อนุมัติการลาของ <strong><?=$req->first_name.' '.$req->last_name?></strong><br>
                <?=date('d/m/Y',strtotime($req->start_date))?> – <?=date('d/m/Y',strtotime($req->end_date))?> (<?=$req->total_days?> วัน)</p>
                <textarea name="note" class="form-control form-control-sm" rows="2" placeholder="หมายเหตุ (ถ้ามี)"></textarea>
              </div>
              <div class="modal-footer py-2">
                <button type="submit" class="btn btn-success btn-sm"><i class="bi bi-check me-1"></i>ยืนยันอนุมัติ</button>
              </div>
              <?=form_close()?>
            </div></div>
          </div>

          <!-- Reject Modal -->
          <div class="modal fade" id="rjMod<?=$req->id?>" tabindex="-1">
            <div class="modal-dialog modal-sm"><div class="modal-content">
              <div class="modal-header bg-danger text-white py-2">
                <h6 class="modal-title mb-0"><i class="bi bi-x-circle me-1"></i>ปฏิเสธการลา</h6>
                <button class="btn-close btn-close-white btn-sm" data-bs-dismiss="modal"></button>
              </div>
              <?=form_open('admin/leave/reject/'.$req->id)?>
              <input type="hidden" name="<?=$this->security->get_csrf_token_name()?>" value="<?=$this->security->get_csrf_hash()?>">
              <div class="modal-body">
                <p class="small mb-2">ปฏิเสธการลาของ <strong><?=$req->first_name.' '.$req->last_name?></strong></p>
                <textarea name="note" class="form-control form-control-sm" rows="2" placeholder="เหตุผลที่ปฏิเสธ (จำเป็น)" required></textarea>
              </div>
              <div class="modal-footer py-2">
                <button type="submit" class="btn btn-danger btn-sm"><i class="bi bi-x me-1"></i>ยืนยันปฏิเสธ</button>
              </div>
              <?=form_close()?>
            </div></div>
          </div>

          <?php endforeach;else:?>
          <tr><td colspan="8" class="text-center text-muted py-5">
            <i class="bi bi-calendar-x fs-1 d-block mb-2"></i>ไม่มีข้อมูลการลา
            <div class="mt-2"><a href="<?=base_url('admin/leave/create')?>" class="btn btn-success btn-sm"><i class="bi bi-plus me-1"></i>เพิ่มคำขอลา</a></div>
          </td></tr>
          <?php endif;?>
        </tbody>
      </table>
    </div>
  </div>
</div>
