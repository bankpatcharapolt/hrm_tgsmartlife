<?php defined('BASEPATH') OR exit(); ?>
<div class="row g-3">
  <!-- Upload form -->
  <div class="col-lg-4">
    <div class="card">
      <div class="card-header"><i class="bi bi-upload me-2"></i>อัปโหลดทวิ 50</div>
      <div class="card-body">
        <form method="POST" action="<?=base_url('admin/salary/upload_tax')?>" enctype="multipart/form-data">
        <input type="hidden" name="<?=$this->security->get_csrf_token_name()?>" value="<?=$this->security->get_csrf_hash()?>">
        <div class="mb-3">
          <label class="form-label">พนักงาน <span class="text-danger">*</span></label>
          <select name="user_id" class="form-select ts-select" required>
            <option value="">-- เลือก --</option>
            <?php foreach($employees as $e):?><option value="<?=$e->id?>"><?=$e->employee_id?> – <?=$e->first_name.' '.$e->last_name?></option><?php endforeach;?>
          </select>
        </div>
        <div class="mb-3">
          <label class="form-label">ปีภาษี <span class="text-danger">*</span></label>
          <input type="number" name="tax_year" class="form-control" value="<?=$year?>" required>
        </div>
        <div class="mb-3">
          <label class="form-label">ไฟล์ PDF ทวิ 50</label>
          <input type="file" name="tax_file" class="form-control" accept=".pdf" required>
          <div class="form-text">ขนาดไม่เกิน 10MB</div>
        </div>
        <button type="submit" class="btn btn-primary w-100"><i class="bi bi-upload me-1"></i>อัปโหลด</button>
        </form>
      </div>
    </div>
  </div>
  <!-- List -->
  <div class="col-lg-8">
    <div class="card mb-2">
      <div class="card-body py-2">
        <?=form_open('admin/salary/tax_docs',array('method'=>'GET','class'=>'row g-2 align-items-end'))?>
          <div class="col-md-3"><select name="year" class="form-select form-select-sm"><?php for($y=date('Y');$y>=date('Y')-5;$y--):?><option value="<?=$y?>" <?=$year==$y?'selected':''?>><?=$y?></option><?php endfor;?></select></div>
          <div class="col-md-4"><select name="user_id" class="form-select form-select-sm ts-select"><option value="">-- ทุกคน --</option><?php foreach($employees as $e):?><option value="<?=$e->id?>" <?=$sel_uid==$e->id?'selected':''?>><?=$e->employee_id?> – <?=$e->first_name.' '.$e->last_name?></option><?php endforeach;?></select></div>
          <div class="col-auto"><button type="submit" class="btn btn-primary btn-sm"><i class="bi bi-search"></i></button></div>
        <?=form_close()?>
      </div>
    </div>
    <div class="card">
      <div class="card-header"><i class="bi bi-file-earmark-text me-2 text-primary"></i>รายการทวิ 50 <span class="badge bg-secondary"><?=count($tax_list)?></span></div>
      <div class="card-body p-0">
        <div class="table-responsive">
          <table class="table table-hover mb-0">
            <thead><tr><th>ปีภาษี</th><th>พนักงาน</th><th>แผนก</th><th>ชื่อไฟล์</th><th>จัดการ</th></tr></thead>
            <tbody>
              <?php if(!empty($tax_list)):foreach($tax_list as $t):?>
              <tr>
                <td><span class="badge bg-primary"><?=$t->tax_year?></span></td>
                <td><div class="fw-semibold" style="font-size:.875rem"><?=$t->first_name.' '.$t->last_name?></div><div style="font-size:.72rem;color:#6b7280"><?=$t->employee_id?></div></td>
                <td style="font-size:.82rem"><?=$t->dept_name??'–'?></td>
                <td><a href="<?=base_url($t->file_path)?>" target="_blank" class="text-primary text-decoration-none"><i class="bi bi-file-earmark-text me-1"></i><?=htmlspecialchars($t->file_name)?></a></td>
                <td>
                  <a href="<?=base_url($t->file_path)?>" target="_blank" class="btn btn-outline-secondary btn-sm px-2 py-0"><i class="bi bi-eye"></i></a>
                  <a href="<?=base_url('admin/salary/delete_tax/'.$t->id)?>" onclick="return confirm('ลบเอกสารนี้?')" class="btn btn-outline-danger btn-sm px-2 py-0 ms-1"><i class="bi bi-trash"></i></a>
                </td>
              </tr>
              <?php endforeach;else:?><tr><td colspan="5" class="text-center text-muted py-4">ไม่มีเอกสารทวิ 50</td></tr><?php endif;?>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</div>
