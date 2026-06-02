# ระบบ HRM (Human Resource Management)
**PHP CodeIgniter 3 + Bootstrap 5 + MySQL | ภาษาไทย**

## 🚀 วิธีติดตั้ง

### 1. ย้ายโฟลเดอร์
```
hrm_tgsmartlife/ → htdocs/hrm_tgsmartlife/  (XAMPP)
hrm_tgsmartlife/ → www/hrm_tgsmartlife/     (WAMP/Laragon)
```

### 2. นำเข้า Database
```bash
mysql -u root -p < sql/hrm_full.sql
```

### 3. แก้ไข Base URL
ไฟล์: `application/config/config.php`
```php
$config['base_url'] = 'http://localhost/hrm_tgsmartlife/';
```

### 4. แก้ไข Database
ไฟล์: `application/config/database.php`
```php
'hostname' => 'localhost',
'username' => 'root',
'password' => '',
'database' => 'hrm_db',
```

### 5. แก้ไข Encryption Key
ไฟล์: `application/config/config.php`
```php
$config['encryption_key'] = 'YOUR_SECRET_KEY_HERE';
```

### 6. สิทธิ์โฟลเดอร์
```bash
chmod -R 777 uploads/
```

---

## 👤 บัญชีผู้ใช้เริ่มต้น

| บัญชี | รหัสผ่าน | บทบาท |
|-------|---------|-------|
| `owner` | `Admin@1234` | เจ้าของ (Full Access) |
| `admin1` | `Admin@1234` | แอดมิน |
| `manager1` | `Admin@1234` | หัวหน้างาน |
| `emp001` | `Admin@1234` | พนักงาน |
| `emp002` | `Admin@1234` | พนักงาน |
| `emp003` | `Admin@1234` | พนักงาน |

---

## 📋 โมดูลหลัก

| โมดูล | คำอธิบาย |
|-------|---------|
| **แดชบอร์ด** | ภาพรวมระบบ สถิติ กิจกรรม |
| **พนักงาน** | เพิ่ม/แก้ไข/ดูข้อมูล |
| **การเข้างาน** | ลงเวลาเข้า-ออก (AJAX), รายงานรายเดือน |
| **การลา** | ยื่น/อนุมัติ/ปฏิเสธ, แจ้งเตือนอัตโนมัติ |
| **เงินเดือน** | บันทึก/คำนวณ, อัปโหลดสลิป |
| **โบนัสประจำปี** | บันทึก + แจ้งเตือน |
| **ทวิ 50** | อัปโหลด + ดาวน์โหลด |
| **ยอดขาย** | รายบุคคล/ทีม, กราฟ, Top 5 |
| **การแจ้งเตือน** | ส่งถึงทุกคน/บทบาท/รายบุคคล |
| **บทบาทและสิทธิ์** | กำหนด permission แต่ละบทบาท |

---

## 🔐 สิทธิ์การเข้าถึง

| สิทธิ์ | พนักงาน | หัวหน้า | แอดมิน | เจ้าของ |
|--------|:------:|:------:|:------:|:------:|
| ลงเวลาเข้า-ออก | ✅ | ✅ | ✅ | ✅ |
| ดูเงินเดือนตัวเอง | ✅ | ✅ | ✅ | ✅ |
| ยื่นคำขอลา | ✅ | ✅ | ✅ | ✅ |
| อนุมัติการลา | ❌ | ✅ | ✅ | ✅ |
| จัดการพนักงาน | ❌ | ❌ | ✅ | ✅ |
| จัดการเงินเดือน | ❌ | ❌ | ✅ | ✅ |
| ตั้งค่าบทบาท | ❌ | ❌ | ❌ | ✅ |

---

## 📁 โครงสร้างไฟล์

```
hrm_tgsmartlife/
├── application/
│   ├── config/          (database, routes, config, autoload)
│   ├── controllers/     (Auth, admin/*, employee/*, manager/*, api/*)
│   ├── core/            (MY_Controller)
│   ├── models/          (User, Notification, Attendance, Leave, Salary, Sales)
│   └── views/           (layouts, auth, admin/*, employee/*, manager/*, errors)
├── system/              (CodeIgniter 3 framework)
├── uploads/             (photos, slips, tax_docs, leave_docs)
├── sql/                 (hrm_full.sql)
└── index.php
```

---

## 📞 ต้องการความช่วยเหลือ
ระบบสร้างด้วย CodeIgniter 3.1.13 | PHP 7.4+ | MySQL 5.7+
