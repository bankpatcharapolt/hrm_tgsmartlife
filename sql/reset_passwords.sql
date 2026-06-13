-- รีเซ็ตรหัสผ่านทุกคนเป็น 123456
-- bcrypt cost=12
UPDATE `users` SET `password` = '$2b$12$sAQNMXKMvV1SyjqutgdA9udvwW.Iin46T4XHjATgmLyTHK1inSt1a';

-- ตรวจสอบ
SELECT id, username, first_name, last_name FROM `users` ORDER BY id;
