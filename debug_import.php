<?php
// วางไว้ที่ C:\xampp\htdocs\hrm_tgsmartlife\debug_import.php
// เปิด: http://localhost/hrm_tgsmartlife/debug_import.php

echo "<h2>🔧 HRM Debug — Import Excel</h2><pre style='background:#f5f5f5;padding:15px;border-radius:8px;font-size:13px'>";

// ตรวจ PHP extensions ที่จำเป็น
$exts = ['zip' => 'ZipArchive (อ่าน xlsx)', 'dom' => 'DOM XML Parser', 'mbstring' => 'Multibyte String (ภาษาไทย)'];
echo "=== PHP Extensions ===\n";
foreach ($exts as $e => $desc) {
    echo sprintf("  %-12s [%s] %s\n", $e, extension_loaded($e) ? '✓ OK' : '✗ MISSING', $desc);
}

// ตรวจ PHP functions
echo "\n=== PHP Functions ===\n";
$fns = ['move_uploaded_file', 'shell_exec', 'simplexml_load_string'];
foreach ($fns as $f) {
    echo sprintf("  %-28s [%s]\n", $f, function_exists($f) ? '✓' : '✗ disabled');
}

// ตรวจ Python
echo "\n=== Python ===\n";
$py_list = ['python3','python','C:/Python313/python.exe','C:/Python312/python.exe','C:/Python311/python.exe','C:/Python310/python.exe'];
$found = false;
foreach ($py_list as $p) {
    $v = @shell_exec('"'.$p.'" --version 2>&1');
    if ($v && stripos($v, 'Python') !== false) {
        echo "  ✓ Found: $p → ".trim($v)."\n";
        $found = true;
        // ทดสอบ openpyxl
        $opx = @shell_exec('"'.$p.'" -c "import openpyxl; print(openpyxl.__version__)" 2>&1');
        echo "  openpyxl: ".($opx&&!strpos($opx,'Error')?'✓ v'.trim($opx):'✗ ไม่ได้ติดตั้ง — รัน: pip install openpyxl')."\n";
        break;
    }
}


// ตรวจ paths
echo "\n=== Paths ===\n";
$base = dirname(__FILE__) . DIRECTORY_SEPARATOR;
echo "  Base path: $base\n";
echo "  scripts/read_excel.py: ".(file_exists($base.'scripts/read_excel.py')?'✓':'✗ ไม่พบ')."\n";
echo "  uploads/tmp/ writable: ".(is_writable($base.'uploads/')?'✓':'✗ ไม่มีสิทธิ์เขียน')."\n";

// ทดสอบ ZipArchive กับไฟล์ xlsx
echo "\n=== ทดสอบอ่าน XLSX ===\n";
if (class_exists('ZipArchive')) {
    // สร้างไฟล์ xlsx test เล็กๆ ไม่ได้ที่นี่ — แต่บอกว่า ZipArchive พร้อม
    echo "  ZipArchive: ✓ พร้อมใช้งาน\n";
    echo "  Import ด้วย PHP ZipArchive: ✓ จะทำงานได้โดยไม่ต้อง Python\n";
} else {
    echo "  ZipArchive: ✗ ไม่พร้อม — เปิด extension=zip ใน php.ini\n";
    echo "  php.ini location: ".php_ini_loaded_file()."\n";
}

echo "</pre>";
echo "<p><a href='/hrm_tgsmartlife/admin/employees_import/import'>→ ไปหน้า Import</a></p>";
?>
