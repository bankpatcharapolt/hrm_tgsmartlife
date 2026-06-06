<?php defined('BASEPATH') OR exit(); ?>

<div class="row g-3">

  <!-- ── ฟอร์มเพิ่ม/แก้ไข ── -->
  <div class="col-lg-4">
    <div class="card">
      <div class="card-header">
        <i class="bi bi-<?=$rec?'pencil-square':'plus-circle'?> me-2 text-primary"></i>
        <?=$rec?'แก้ไขประเภทการลา':'เพิ่มประเภทการลา'?>
      </div>
      <div class="card-body">
        <?php
        $action = $rec
            ? form_open('admin/leave_types/update/'.$rec->id)
            : form_open('admin/leave_types/store');
        echo $action;
        ?>
        <input type="hidden" name="<?=$this->security->get_csrf_token_name()?>"
               value="<?=$this->security->get_csrf_hash()?>">

        <div class="row g-3">

          <div class="col-6">
            <label class="form-label">รหัสการลา(ถ้ามี)</label>
            <input type="text" name="leave_code" class="form-control"
                   placeholder="เช่น SL, AL, PL"
                   value="<?=htmlspecialchars($rec->leave_code??'')?>"
                   maxlength="10" style="text-transform:uppercase">
            <div class="form-text">SL=ป่วย, AL=พักร้อน, PL=กิจ</div>
          </div>

          <div class="col-6">
            <label class="form-label">โควต้าต่อปี (วัน)</label>
            <input type="number" name="quota_per_year" class="form-control"
                   min="0" value="<?=$rec?(int)$rec->quota_per_year:0?>">
            <div class="form-text">0 = ไม่จำกัด</div>
          </div>

          <div class="col-12">
            <label class="form-label">ชื่อประเภทการลา <span class="text-danger">*</span></label>
            <input type="text" name="name" class="form-control" required
                   placeholder="เช่น ลาป่วย, ลากิจ, ลาพักร้อน"
                   value="<?=htmlspecialchars($rec->name??'')?>">
          </div>

          <!-- checkboxes -->
          <div class="col-12">
            <label class="form-label small fw-semibold text-muted">ตัวเลือก</label>
            <div class="d-flex flex-column gap-2">

              <div class="form-check">
                <input class="form-check-input" type="checkbox" name="is_paid" id="isPaid" value="1"
                       <?=$rec&&$rec->is_paid?'checked':(!$rec?'checked':'')?>>
                <label class="form-check-label small" for="isPaid">
                  ลาแบบได้รับเงินเดือน
                </label>
              </div>

              <div class="form-check">
                <input class="form-check-input" type="checkbox" name="is_deduct_salary" id="isDeduct" value="1"
                       <?=$rec&&$rec->is_deduct_salary?'checked':''?>>
                <label class="form-check-label small" for="isDeduct">
                  หักเงินเดือนเมื่อลาเกินโควต้า
                </label>
              </div>

              <div class="form-check">
                <input class="form-check-input" type="checkbox" name="can_leave_by_hour" id="byHour" value="1"
                       <?=$rec&&$rec->can_leave_by_hour?'checked':''?>>
                <label class="form-check-label small" for="byHour">
                  ลาเป็นชั่วโมงได้
                </label>
              </div>

              <div class="form-check">
                <input class="form-check-input" type="checkbox" name="is_carry_forward" id="carry" value="1"
                       <?=$rec&&$rec->is_carry_forward?'checked':''?>>
                <label class="form-check-label small" for="carry">
                  ทบวันลาไปปีถัดไปได้
                </label>
              </div>

              <div class="form-check">
                <input class="form-check-input" type="checkbox" name="requires_doc" id="reqDoc" value="1"
                       onchange="document.getElementById('docDaysWrap').style.display=this.checked?'':'none'"
                       <?=$rec&&$rec->requires_doc?'checked':''?>>
                <label class="form-check-label small" for="reqDoc">
                  ต้องแนบเอกสาร
                </label>
              </div>

              <div id="docDaysWrap" class="ps-4"
                   style="display:<?=$rec&&$rec->requires_doc?'':'none'?>">
                <label class="form-label small mb-1">ต้องแนบเมื่อลาติดต่อกัน (วัน)</label>
                <input type="number" name="require_doc_days" class="form-control form-control-sm"
                       min="0" style="width:80px"
                       value="<?=$rec?(int)$rec->require_doc_days:0?>">
                <div class="form-text" style="font-size:.72rem">0 = ต้องแนบทุกครั้ง</div>
              </div>

            </div>
          </div>

          <div class="col-12">
            <label class="form-label">หมายเหตุ/เงื่อนไข</label>
            <textarea name="description" class="form-control" rows="2"
                      placeholder="เช่น สำหรับพนักงานที่อายุงานเกิน 1 ปี"><?=htmlspecialchars($rec->description??'')?></textarea>
          </div>

        </div><!-- /row g-3 -->

        <div class="d-flex gap-2 mt-3">
          <button type="submit" class="btn btn-primary flex-fill">
            <i class="bi bi-save me-1"></i><?=$rec?'บันทึกการแก้ไข':'เพิ่มประเภทการลา'?>
          </button>
          <?php if($rec):?>
          <a href="<?=base_url('admin/leave_types')?>" class="btn btn-outline-secondary">ยกเลิก</a>
          <?php endif;?>
        </div>

        <?=form_close()?>
      </div>
    </div>
  </div>

  <!-- ── รายการทั้งหมด ── -->
  <div class="col-lg-8">
    <div class="card">
      <div class="card-header d-flex justify-content-between align-items-center">
        <span><i class="bi bi-list-check me-2"></i>ประเภทการลาทั้งหมด</span>
        <span class="badge bg-secondary"><?=count($types)?> ประเภท</span>
      </div>
      <div class="card-body p-0">
        <div class="table-responsive">
          <table class="table table-hover mb-0">
            <thead>
              <tr>
                <th>รหัส</th>
                <th>ชื่อประเภท</th>
                <th class="text-center">โควต้า/ปี</th>
                <th class="text-center">รับเงิน</th>
                <th class="text-center">แนบเอกสาร</th>
                <th class="text-center">ลาชั่วโมง</th>
                <th class="text-center">ทบวัน</th>
                <th>จัดการ</th>
              </tr>
            </thead>
            <tbody>
              <?php if(!empty($types)):foreach($types as $t):?>
              <tr class="<?=$rec&&$rec->id==$t->id?'table-primary':''?>">
                <td>
                  <?php if(!empty($t->leave_code)):?>
                  <span class="badge bg-light text-dark border" style="font-size:.78rem;font-weight:600"><?=htmlspecialchars($t->leave_code)?></span>
                  <?php else:?><span class="text-muted small">–</span><?php endif;?>
                </td>
                <td>
                  <div class="fw-semibold" style="font-size:.875rem"><?=htmlspecialchars($t->name)?></div>
                  <?php if(!empty($t->description)):?>
                  <div style="font-size:.72rem;color:#6b7280"><?=htmlspecialchars(mb_substr($t->description,0,40))?><?=mb_strlen($t->description??'')>40?'...':''?></div>
                  <?php endif;?>
                </td>
                <td class="text-center">
                  <?php if($t->quota_per_year > 0):?>
                  <span class="fw-semibold text-primary"><?=$t->quota_per_year?></span>
                  <span class="text-muted" style="font-size:.75rem"> วัน</span>
                  <?php else:?>
                  <span class="badge bg-light text-dark border" style="font-size:.72rem">ไม่จำกัด</span>
                  <?php endif;?>
                </td>
                <td class="text-center">
                  <?=$t->is_paid
                      ?'<i class="bi bi-check-circle-fill text-success"></i>'
                      :'<i class="bi bi-x-circle text-muted"></i>'?>
                </td>
                <td class="text-center">
                  <?php if($t->requires_doc):?>
                  <i class="bi bi-check-circle-fill text-warning"></i>
                  <?php if($t->require_doc_days > 0):?>
                  <span style="font-size:.7rem;color:#6b7280">(≥<?=$t->require_doc_days?>วัน)</span>
                  <?php endif;?>
                  <?php else:?>
                  <i class="bi bi-x-circle text-muted"></i>
                  <?php endif;?>
                </td>
                <td class="text-center">
                  <?=$t->can_leave_by_hour
                      ?'<i class="bi bi-check-circle-fill text-info"></i>'
                      :'<i class="bi bi-x-circle text-muted"></i>'?>
                </td>
                <td class="text-center">
                  <?=$t->is_carry_forward
                      ?'<i class="bi bi-check-circle-fill text-purple" style="color:#9333ea"></i>'
                      :'<i class="bi bi-x-circle text-muted"></i>'?>
                </td>
                <td>
                  <a href="<?=base_url('admin/leave_types/edit/'.$t->id)?>"
                     class="btn btn-outline-secondary btn-sm px-2 py-0"
                     title="แก้ไข">
                    <i class="bi bi-pencil"></i>
                  </a>
                  <a href="<?=base_url('admin/leave_types/delete/'.$t->id)?>"
                     onclick="return confirm('ลบประเภท \"<?=htmlspecialchars($t->name)?>\" ใช่ไหม?')"
                     class="btn btn-outline-danger btn-sm px-2 py-0 ms-1"
                     title="ลบ">
                    <i class="bi bi-trash"></i>
                  </a>
                </td>
              </tr>
              <?php endforeach;else:?>
              <tr><td colspan="8" class="text-center text-muted py-5">
                <i class="bi bi-calendar-x fs-2 d-block mb-2"></i>ยังไม่มีประเภทการลา
              </td></tr>
              <?php endif;?>
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <!-- ── คำอธิบาย columns ── -->
    <div class="mt-2 d-flex flex-wrap gap-3" style="font-size:.75rem;color:#6b7280">
      <span><i class="bi bi-check-circle-fill text-success me-1"></i>รับเงินเดือน</span>
      <span><i class="bi bi-check-circle-fill text-warning me-1"></i>ต้องแนบเอกสาร</span>
      <span><i class="bi bi-check-circle-fill text-info me-1"></i>ลาชั่วโมงได้</span>
      <span><i class="bi bi-check-circle-fill me-1" style="color:#9333ea"></i>ทบวันลาปีถัดไป</span>
      <span><i class="bi bi-x-circle text-muted me-1"></i>ไม่รองรับ</span>
    </div>
  </div>

</div>
