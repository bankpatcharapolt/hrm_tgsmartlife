<?php defined('BASEPATH') OR exit(); ?>
<div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
  <span class="text-muted small">ทั้งหมด <strong><?=$total?></strong> คน</span>
  <a href="<?=base_url('admin/employees/create')?>" class="btn btn-primary"><i class="bi bi-plus-lg me-1"></i>เพิ่มพนักงานใหม่</a>
</div>
<div class="card mb-3">
  <div class="card-body py-2">
    <?=form_open('admin/employees',array('method'=>'GET','class'=>'row g-2 align-items-end'))?>
      <div class="col-md-3"><input type="text" name="search" class="form-control form-control-sm" placeholder="ชื่อ / รหัส / เบอร์โทร" value="<?=htmlspecialchars($filters['search']??'')?>"></div>
      <div class="col-md-2"><select name="dept" class="form-select form-select-sm"><option value="">-- ทุกแผนก --</option><?php foreach($departments as $d):?><option value="<?=$d->id?>" <?=($filters['department_id']??'')==$d->id?'selected':''?>><?=$d->name?></option><?php endforeach;?></select></div>
      <div class="col-md-2"><select name="role" class="form-select form-select-sm"><option value="">-- ทุกบทบาท --</option><?php foreach($roles as $r):?><option value="<?=$r->id?>" <?=($filters['role_id']??'')==$r->id?'selected':''?>><?=$r->name?></option><?php endforeach;?></select></div>
      <div class="col-md-2"><select name="status" class="form-select form-select-sm"><option value="active" <?=($filters['status']??'active')==='active'?'selected':''?>>ใช้งาน</option><option value="inactive" <?=($filters['status']??'')==='inactive'?'selected':''?>>ไม่ใช้งาน</option><option value="">ทั้งหมด</option></select></div>
      <div class="col-auto"><button type="submit" class="btn btn-primary btn-sm"><i class="bi bi-search"></i></button> <a href="<?=base_url('admin/employees')?>" class="btn btn-outline-secondary btn-sm">ล้าง</a></div>
    <?=form_close()?>
  </div>
</div>
<div class="card">
  <div class="card-body p-0">
    <div class="table-responsive">
      <table class="table table-hover mb-0">
        <thead><tr><th>รหัส</th><th>ชื่อ-สกุล</th><th>แผนก</th><th>ตำแหน่ง</th><th>เบอร์โทร</th><th>เงินเดือน</th><th>สถานะ</th><th>จัดการ</th></tr></thead>
        <tbody>
          <?php if(!empty($employees)):foreach($employees as $e):?>
          <tr>
            <td><span class="badge bg-secondary"><?=$e->employee_id?></span></td>
            <td>
              <div class="d-flex align-items-center gap-2">
                <?php if(!empty($e->photo)):?><img src="<?=base_url($e->photo)?>" style="width:30px;height:30px;border-radius:50%;object-fit:cover;"><?php else:?><div style="width:30px;height:30px;border-radius:50%;background:#e0e7ff;display:flex;align-items:center;justify-content:center;font-weight:700;color:#1a56db;font-size:.78rem"><?=mb_substr($e->first_name,0,1)?></div><?php endif;?>
                <div><div class="fw-semibold" style="font-size:.875rem"><?=$e->first_name.' '.$e->last_name?></div><?php if($e->nickname):?><div style="font-size:.72rem;color:#6b7280">(<?=$e->nickname?>)</div><?php endif;?></div>
              </div>
            </td>
            <td style="font-size:.83rem"><?=$e->department_name??'–'?></td>
            <td><span class="badge bg-light text-dark border"><?=$e->role_name?></span></td>
            <td style="font-size:.83rem"><?=$e->phone??'–'?></td>
            <td style="font-size:.83rem">฿<?=number_format($e->base_salary,0)?></td>
            <td><span class="badge bg-<?=$e->status==='active'?'success':($e->status==='inactive'?'secondary':'danger')?>"><?=$e->status==='active'?'ใช้งาน':($e->status==='inactive'?'ไม่ใช้งาน':'ระงับ')?></span></td>
            <td>
              <a href="<?=base_url('admin/employees/view/'.$e->id)?>" class="btn btn-outline-primary btn-sm px-2 py-0" title="ดูข้อมูล"><i class="bi bi-eye"></i></a>
              <a href="<?=base_url('admin/employees/edit/'.$e->id)?>" class="btn btn-outline-secondary btn-sm px-2 py-0" title="แก้ไข"><i class="bi bi-pencil"></i></a>
              <?php if($e->status==='active'):?><a href="<?=base_url('admin/employees/deactivate/'.$e->id)?>" onclick="return confirm('ปิดการใช้งานพนักงานคนนี้?')" class="btn btn-outline-danger btn-sm px-2 py-0" title="ปิดใช้งาน"><i class="bi bi-person-dash"></i></a><?php endif;?>
            </td>
          </tr>
          <?php endforeach;else:?><tr><td colspan="8" class="text-center text-muted py-5"><i class="bi bi-people fs-1 d-block mb-2 text-muted"></i>ไม่พบข้อมูลพนักงาน</td></tr><?php endif;?>
        </tbody>
      </table>
    </div>
  </div>
</div>
<?php if($total_pages>1):?><nav class="mt-3"><ul class="pagination pagination-sm justify-content-center mb-0"><?php for($p=1;$p<=$total_pages;$p++):?><li class="page-item <?=$p==$page?'active':''?>"><a class="page-link" href="?<?=http_build_query(array_merge($filters,array('page'=>$p)))?>"><?=$p?></a></li><?php endfor;?></ul></nav><?php endif;?>
