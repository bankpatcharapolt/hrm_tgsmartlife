<?php defined('BASEPATH') OR exit(); $e=$emp; ?>
<div class="card">
  <div class="card-header"><i class="bi bi-person-plus me-2"></i><?=$page_title?></div>
  <div class="card-body">
    <?=form_open_multipart($e?'admin/employees/update/'.$e->id:'admin/employees/store')?>
    <input type="hidden" name="<?=$this->security->get_csrf_token_name()?>" value="<?=$this->security->get_csrf_hash()?>">

    <!-- ══ ข้อมูลหลัก ══ -->
    <div class="fw-semibold small text-muted mb-2 mt-1"><i class="bi bi-person me-1"></i>ข้อมูลพนักงาน</div>
    <div class="row g-3 mb-3">
      <div class="col-md-2"><label class="form-label">รหัสพนักงาน *</label><input type="text" name="employee_id" class="form-control" value="<?=$e?$e->employee_id:''?>" required placeholder="SL001"></div>
      <div class="col-md-2"><label class="form-label">คำนำหน้า</label><select name="title" class="form-select"><option value="">–</option><?php foreach(['นาย','นาง','นางสาว','ดร.','อื่นๆ'] as $t):?><option <?=($e&&$e->title===$t)?'selected':''?>><?=$t?></option><?php endforeach;?></select></div>
      <div class="col-md-4"><label class="form-label">ชื่อจริง (ไทย) *</label><input type="text" name="first_name" class="form-control" value="<?=$e?htmlspecialchars($e->first_name):''?>" required></div>
      <div class="col-md-4"><label class="form-label">นามสกุล (ไทย) *</label><input type="text" name="last_name" class="form-control" value="<?=$e?htmlspecialchars($e->last_name):''?>" required></div>
      <div class="col-md-3"><label class="form-label">ชื่อจริง (EN)</label><input type="text" name="first_name_en" class="form-control" value="<?=$e?htmlspecialchars($e->first_name_en??''):''?>"></div>
      <div class="col-md-3"><label class="form-label">นามสกุล (EN)</label><input type="text" name="last_name_en" class="form-control" value="<?=$e?htmlspecialchars($e->last_name_en??''):''?>"></div>
      <div class="col-md-3"><label class="form-label">ชื่อเล่น</label><input type="text" name="nickname" class="form-control" value="<?=$e?htmlspecialchars($e->nickname??''):''?>"></div>
      <div class="col-md-3"><label class="form-label">เพศ</label><select name="gender" class="form-select"><option value="male" <?=($e&&$e->gender==='male')?'selected':''?>>ชาย</option><option value="female" <?=($e&&$e->gender==='female')?'selected':''?>>หญิง</option><option value="other">อื่นๆ</option></select></div>
      <div class="col-md-3"><label class="form-label">วันเกิด</label><input type="date" name="dob" class="form-control" value="<?=$e?$e->date_of_birth:''?>"></div>
      <div class="col-md-4"><label class="form-label">เลขบัตรประชาชน</label><input type="text" name="id_card" class="form-control" maxlength="13" value="<?=$e?htmlspecialchars($e->id_card_number??''):''?>"></div>
    </div>

    <!-- ══ ข้อมูลการทำงาน ══ -->
    <hr class="my-2">
    <div class="fw-semibold small text-muted mb-2"><i class="bi bi-briefcase me-1"></i>ข้อมูลการทำงาน</div>
    <div class="row g-3 mb-3">
      <div class="col-md-4"><label class="form-label">แผนก</label><select name="department_id" class="form-select"><option value="">– เลือกแผนก –</option><?php foreach($departments as $d):?><option value="<?=$d->id?>" <?=($e&&$e->department_id==$d->id)?'selected':''?>><?=$d->name?></option><?php endforeach;?></select></div>
      <div class="col-md-4"><label class="form-label">ทีม/สาขา</label><select name="team_id" class="form-select"><option value="">– เลือกทีม –</option><?php foreach($teams??[] as $t):?><option value="<?=$t->id?>" <?=($e&&($e->team_id??0)==$t->id)?'selected':''?>><?=$t->team_name?></option><?php endforeach;?></select></div>
      <div class="col-md-4"><label class="form-label">ตำแหน่ง</label><input type="text" name="position" class="form-control" value="<?=$e?htmlspecialchars($e->position??''):''?>" placeholder="ผู้จัดการ / พนักงานขาย ฯลฯ"></div>
      <div class="col-md-4"><label class="form-label">บทบาทในระบบ *</label><select name="role_id" class="form-select" required><?php foreach($roles as $r):?><option value="<?=$r->id?>" <?=($e&&$e->role_id==$r->id)?'selected':''?>><?=$r->name?></option><?php endforeach;?></select></div>
      <div class="col-md-4"><label class="form-label">ประเภทพนักงาน</label><select name="employee_type" class="form-select"><?php foreach(['รายเดือน','รายวัน','พาร์ทไทม์','สัญญาจ้าง'] as $et):?><option <?=($e&&($e->employee_type??'รายเดือน')===$et)?'selected':''?>><?=$et?></option><?php endforeach;?></select></div>
      <div class="col-md-4"><label class="form-label">วันที่เริ่มทำงาน *</label><input type="date" name="start_date" class="form-control" value="<?=$e?$e->start_date:date('Y-m-d')?>" required></div>
      <div class="col-md-4"><label class="form-label">สถานะ</label><select name="status" class="form-select"><option value="active" <?=($e&&$e->status==='active')?'selected':''?>>ใช้งาน</option><option value="inactive" <?=($e&&$e->status==='inactive')?'selected':''?>>ไม่ใช้งาน / ลาออก</option><option value="suspended" <?=($e&&$e->status==='suspended')?'selected':''?>>ระงับ</option></select></div>
    </div>

    <!-- ══ ข้อมูลติดต่อ ══ -->
    <hr class="my-2">
    <div class="fw-semibold small text-muted mb-2"><i class="bi bi-telephone me-1"></i>ข้อมูลติดต่อ</div>
    <div class="row g-3 mb-3">
      <div class="col-md-4"><label class="form-label">เบอร์โทร</label><input type="text" name="phone" class="form-control" value="<?=$e?htmlspecialchars($e->phone??''):''?>"></div>
      <div class="col-md-4"><label class="form-label">อีเมล</label><input type="email" name="email" class="form-control" value="<?=$e?htmlspecialchars($e->email??''):''?>"></div>
      <div class="col-md-4"><label class="form-label">รูปถ่าย</label><input type="file" name="photo" class="form-control" accept="image/*"><?php if($e&&$e->photo):?><img src="<?=base_url($e->photo)?>" style="width:48px;height:48px;border-radius:50%;object-fit:cover;margin-top:.4rem"><?php endif;?></div>
      <div class="col-md-4"><label class="form-label">ผู้ติดต่อฉุกเฉิน</label><input type="text" name="emergency_contact" class="form-control" value="<?=$e?htmlspecialchars($e->emergency_contact??''):''?>"></div>
      <div class="col-md-4"><label class="form-label">เบอร์ติดต่อฉุกเฉิน</label><input type="text" name="emergency_phone" class="form-control" value="<?=$e?htmlspecialchars($e->emergency_phone??''):''?>"></div>
    </div>

    <!-- ══ ที่อยู่ ══ -->
    <hr class="my-2">
    <div class="fw-semibold small text-muted mb-2"><i class="bi bi-geo-alt me-1"></i>ที่อยู่</div>
    <div class="row g-3 mb-3">
      <div class="col-12"><label class="form-label">ที่อยู่</label><input type="text" name="address" class="form-control" value="<?=$e?htmlspecialchars($e->address??''):''?>" placeholder="บ้านเลขที่ ถนน หมู่บ้าน"></div>
      <div class="col-md-3"><label class="form-label">แขวง/ตำบล</label><input type="text" name="sub_district" class="form-control" value="<?=$e?htmlspecialchars($e->sub_district??''):''?>"></div>
      <div class="col-md-3"><label class="form-label">เขต/อำเภอ</label><input type="text" name="district" class="form-control" value="<?=$e?htmlspecialchars($e->district??''):''?>"></div>
      <div class="col-md-3"><label class="form-label">จังหวัด</label><input type="text" name="province" class="form-control" value="<?=$e?htmlspecialchars($e->province??''):''?>"></div>
      <div class="col-md-3"><label class="form-label">รหัสไปรษณีย์</label><input type="text" name="postal_code" class="form-control" maxlength="5" value="<?=$e?htmlspecialchars($e->postal_code??''):''?>"></div>
    </div>

    <!-- ══ ข้อมูลการเงิน ══ -->
    <hr class="my-2">
    <div class="fw-semibold small text-muted mb-2"><i class="bi bi-currency-dollar me-1"></i>ข้อมูลการเงิน</div>
    <div class="row g-3 mb-3">
      <div class="col-md-3"><label class="form-label">เงินเดือนฐาน (บาท)</label><div class="input-group"><span class="input-group-text">฿</span><input type="number" name="base_salary" class="form-control" value="<?=$e?(float)$e->base_salary:0?>" min="0" step="100"></div></div>
      <div class="col-md-5"><label class="form-label">บัญชีเงินเดือนที่บันทึก</label><input type="text" name="salary_account" class="form-control" value="<?=$e?htmlspecialchars($e->salary_account??''):''?>" placeholder="530101 เงินเดือน ค่าจ้าง"></div>
      <div class="col-md-4"><label class="form-label">สถานะประกันสังคม</label><select name="social_security_status" class="form-select"><option value="ขึ้นทะเบียนประกันสังคม" <?=($e&&($e->social_security_status??'')==='ขึ้นทะเบียนประกันสังคม')?'selected':''?>>ขึ้นทะเบียนประกันสังคม</option><option value="ไม่ขึ้นทะเบียนประกันสังคม" <?=($e&&($e->social_security_status??'')==='ไม่ขึ้นทะเบียนประกันสังคม')?'selected':''?>>ไม่ขึ้นทะเบียนประกันสังคม</option></select></div>
      <div class="col-md-3"><label class="form-label">รหัสประกันสังคม</label><input type="text" name="ssid" class="form-control" value="<?=$e?htmlspecialchars($e->social_security_id??''):''?>"></div>
      <div class="col-md-3"><label class="form-label">ยอดหัก ณ ที่จ่าย (ภ.ง.ด.1)</label><div class="input-group"><span class="input-group-text">฿</span><input type="number" name="withholding_tax" class="form-control" value="<?=$e?(float)($e->withholding_tax??0):0?>" min="0" step="0.01"></div></div>
      <div class="col-md-3"><label class="form-label">เลขประจำตัวผู้เสียภาษี</label><input type="text" name="tax_id" class="form-control" value="<?=$e?htmlspecialchars($e->tax_id??''):''?>"></div>
      <div class="col-md-5"><label class="form-label">ช่องทางรับเงิน</label><input type="text" name="payment_channel" class="form-control" value="<?=$e?htmlspecialchars($e->payment_channel??''):''?>" placeholder="ธ.กสิกรไทย ออมทรัพย์"></div>
      <div class="col-md-4"><label class="form-label">เลขที่บัญชี</label><input type="text" name="bank_account" class="form-control" value="<?=$e?htmlspecialchars($e->bank_account??''):''?>"></div>
    </div>

    <!-- ══ บัญชีผู้ใช้ ══ -->
    <hr class="my-2">
    <div class="fw-semibold small text-muted mb-2"><i class="bi bi-shield-lock me-1"></i>บัญชีผู้ใช้งาน</div>
    <div class="row g-3">
      <div class="col-md-4"><label class="form-label">ชื่อผู้ใช้ *</label><input type="text" name="username" class="form-control" value="<?=$e?$e->username:''?>" required></div>
      <div class="col-md-4"><label class="form-label">รหัสผ่าน <?=!$e?'*':''?></label><input type="password" name="password" class="form-control" <?=!$e?'required':''?> placeholder="<?=$e?'เว้นว่างถ้าไม่ต้องการเปลี่ยน':''?>"></div>
    </div>

    <div class="mt-4 d-flex gap-2">
      <button type="submit" class="btn btn-primary"><i class="bi bi-save me-1"></i>บันทึก</button>
      <a href="<?=base_url('admin/employees')?>" class="btn btn-outline-secondary">ยกเลิก</a>
    </div>
    <?=form_close()?>
  </div>
</div>
