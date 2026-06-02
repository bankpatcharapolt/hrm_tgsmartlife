<?php defined('BASEPATH') OR exit('No direct script access allowed');
// Extend CI3 Upload เพิ่ม MIME types สำหรับ xlsx/xls
class MY_Upload extends CI_Upload {
    public function __construct($config = array()) {
        parent::__construct($config);
        // เพิ่ม MIME types หลัง parent::__construct เพื่อให้ $this->mimes พร้อมแล้ว
        if (!is_array($this->mimes)) $this->mimes = array();
        $this->mimes['xlsx'] = array(
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'application/octet-stream',
            'application/zip',
            'application/x-zip',
            'application/x-zip-compressed',
        );
        $this->mimes['xls'] = array(
            'application/vnd.ms-excel',
            'application/msexcel',
            'application/x-msexcel',
            'application/octet-stream',
        );
    }
}
