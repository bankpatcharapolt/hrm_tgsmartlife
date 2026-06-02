# HRM Fix Patch — สรุปการแก้ไขทั้ง 7 ข้อ

## วิธีติดตั้ง

### 1. รัน SQL Migration ก่อน
```sql
mysql -u root -p hrm_db < sql/hrm_fix_patch.sql
```

### 2. Copy ไฟล์ทับของเดิม
```
application/config/routes.php
application/controllers/api/Notifications.php
application/controllers/employee/Attendance.php
application/controllers/employee/Profile.php
application/models/Attendance_model.php
application/models/Salary_model.php
application/views/admin/salary/form.php
application/views/employee/attendance/index.php
application/views/employee/leave/index.php
application/views/layouts/main.php
```

---

## รายละเอียดการแก้ไขแต่ละข้อ

---

### ข้อ 1 — อัปโหลดสลิป/ทวิ50 ซ้ำชื่อ → ทับแทนของเก่า
**ไฟล์:** `application/models/Salary_model.php`

- `save_slip()`: ก่อน insert ตรวจว่ามี `user_id + slip_year + slip_month + file_name` ซ้ำไหม  
  ถ้าซ้ำ → ลบไฟล์เก่า → `UPDATE` ทับ  
- `save_tax_doc()`: ตรวจ `user_id + tax_year` ซ้ำ → ลบไฟล์เก่า → `UPDATE` ทับ

---

### ข้อ 2 — Edit เงินเดือนไม่คำนวณ
**ไฟล์:** `application/views/admin/salary/form.php`

**สาเหตุ bug:** JS `calc()` เดิมนับ `base_salary` 2 ครั้ง
- ครั้งแรกจาก `b = parseFloat(base_salary.value)` 
- ครั้งที่สองเพราะ `base_salary` input มี class `income` ด้วย → `forEach(.income)` นับอีกรอบ

**วิธีแก้:** แยก `base_salary` input ออกจาก class `income`  
ใช้ `id="base_salary"` + `var base = parseFloat(document.getElementById('base_salary').value)`  
แล้ว `inc = base + sum(.income)`

นอกจากนี้เพิ่ม `calc()` ตอนโหลดหน้า เพื่อให้แสดงยอดทันทีตอน edit

---

### ข้อ 3 — แสดงวันขาดงานในหน้าการเข้างาน
**ไฟล์:** 
- `application/models/Attendance_model.php` — เพิ่ม `get_absent_days()`
- `application/controllers/employee/Attendance.php` — ส่ง `$absent_days` ไป view
- `application/views/employee/attendance/index.php` — แสดง alert + แถวสีแดง

**Logic:**
- วนวันใน เดือน/ปี ที่เลือก จนถึงวันนี้
- ข้ามเสาร์-อาทิตย์
- ข้ามวันหยุดนักขัตฤกษ์ไทย (บริษัทเอกชน) — รายการคงที่ + ปฏิทินจันทรคติ ปี 2024-2027
- ข้ามวันที่มี attendance record แล้ว
- ข้ามวันที่มี `leave_requests` ที่ `status = approved`
- วันที่เหลือ = ขาดงาน

สรุปจำนวนวันขาดในการ์ด + alert บอกวันที่

---

### ข้อ 4 — แสดงวันลาคงเหลือ/ใช้ไปแต่ละประเภท
**ไฟล์:** `application/views/employee/leave/index.php`

เพิ่มส่วนสรุปเหนือตารางคำขอ:
- แสดงทุก leave_type → quota_per_year, ใช้ไป (approved ปีนี้), คงเหลือ
- Progress bar สีเปลี่ยนตาม % ที่ใช้ไป (เขียว/เหลือง/แดง)

---

### ข้อ 5 — อัปโหลดรูปโปรไฟล์ไม่ได้ (PHP Error: Undefined property MY_Upload::$Sales)
**ไฟล์:** `application/controllers/employee/Profile.php`

**สาเหตุ:** `MY_Upload` library ถูก autoload และมี property conflict กับ `Sales` controller  
ตอน CI3 inject CI instance เข้า library มีการเรียก `$this->Sales` ที่ undefined

**วิธีแก้:** เลิกใช้ `$this->load->library('upload')` ใน Profile controller  
เปลี่ยนมาใช้ native PHP upload แทน:
- `$_FILES['photo']` โดยตรง
- ตรวจ MIME type ด้วย `mime_content_type()` (ปลอดภัยกว่า `$_FILES['type']`)
- จำกัด allowed types: jpg/jpeg, png, webp, gif
- จำกัด max size: 2MB

---

### ข้อ 6 — Real-time notification ด้วย Server-Sent Events (SSE)
**ไฟล์:**
- `application/controllers/api/Notifications.php` — เพิ่ม `stream()` method
- `application/views/layouts/main.php` — เพิ่ม SSE JavaScript
- `application/config/routes.php` — เพิ่ม route `api/notifications/stream`

**การทำงาน:**
- Browser เปิด `EventSource('/api/notifications/stream')` ต่อ SSE
- Server ส่ง `event: notification` ทุก 15 วิ พร้อม `{count, items}`
- Client อัปเดต bell badge + dropdown notification list
- ถ้ามี notification ใหม่ (count เพิ่มขึ้น) → แสดง toast popup ล่างขวา 6 วิแล้วหาย
- Server หยุดส่งหลัง 20 รอบ (~5 นาที) → client reconnect เองอัตโนมัติ

**หมายเหตุ:** SSE ต้องการ PHP process-based server (Apache/Nginx + PHP-FPM)  
ถ้าใช้ `output_buffering = On` ใน php.ini ให้ปิดหรือเพิ่มใน .htaccess:
```
php_value output_buffering Off
```

---

### ข้อ 7 — ลงข้อมูลการมาทำงานย้อนหลัง + ปรับ Modal
**ไฟล์:**
- `application/controllers/employee/Attendance.php`
- `application/views/employee/attendance/index.php`
- `sql/hrm_fix_patch.sql` — เพิ่มคอลัมน์ `work_hours`, `leave_hours`, `leave_type_id`

**การเปลี่ยนแปลง:**
1. **Shift dropdown** → ดึง shift_id ของ user มา pre-select + `disabled` แก้ไขไม่ได้  
   (ใช้ hidden input เพราะ disabled field ไม่ส่ง POST)
2. **สถานะ "ครึ่งวัน"** → เพิ่ม input ชั่วโมงที่มาทำงาน (default 4 ชม.)
3. **สถานะ "มาทำงานรายชั่วโมง"** → เลือกเวลาเข้า-ออก ระบบคำนวณชั่วโมงให้อัตโนมัติ  
   บันทึกเป็น `status='present'` + `work_hours` = ชั่วโมงจริง
