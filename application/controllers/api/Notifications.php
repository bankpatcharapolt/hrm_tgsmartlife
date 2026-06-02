<?php defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * API Notifications — SSE ที่ใช้ได้บน Shared Host / Apache / DirectAdmin
 *
 * หลักการ: PHP ตอบทันทีแล้วจบ (ไม่มี sleep, ไม่มี while loop)
 * JS ใช้ EventSource ปกติ แต่ reconnect เองทุก 30 วิ ผ่าน retry header
 *
 * Flow:
 *   Browser → GET /api/notifications/stream
 *   PHP      → ส่ง 1 event แล้ว exit ทันที   (ไม่ค้าง worker เลย)
 *   Browser  → reconnect อัตโนมัติหลัง 30 วิ (EventSource spec)
 *   วนซ้ำ
 */
class Notifications extends MY_Controller {

    public function __construct() {
        parent::__construct();
        $this->require_login();
    }

    // ── endpoint เดิม ────────────────────────────────────────────────
    public function unread() {
        $uid = $this->current_user->user_id;
        $this->json_ok(array(
            'count' => $this->Notification_model->count_unread($uid),
            'items' => $this->Notification_model->get_recent($uid, 5),
        ));
    }

    public function mark_read($id) {
        $this->Notification_model->mark_read($id, $this->current_user->user_id);
        $this->json_ok();
    }

    // ── SSE stream: ตอบทันที ไม่ sleep ─────────────────────────────
    public function stream() {
        $uid = $this->current_user->user_id;

        // ปิด output buffering ทุกชั้น
        while (ob_get_level() > 0) {
            ob_end_clean();
        }

        // SSE headers
        header('Content-Type: text/event-stream; charset=utf-8');
        header('Cache-Control: no-cache, no-store, must-revalidate');
        header('Pragma: no-cache');
        header('Expires: 0');
        header('X-Accel-Buffering: no');   // nginx
        header('Connection: close');        // บอก Apache ให้ปิด connection หลัง response

        // ดึงข้อมูล notification — query เดียว จบเลย
        $count = $this->Notification_model->count_unread($uid);
        $rows  = $this->Notification_model->get_recent($uid, 5);

        $items = array();
        foreach ($rows as $n) {
            $items[] = array(
                'id'       => (int)$n->id,
                'title'    => $n->title,
                'message'  => mb_substr($n->message, 0, 80),
                'link'     => $n->link ?? '',
                'is_read'  => (int)$n->is_read,
                'time_ago' => $this->_time_ago($n->created_at),
            );
        }

        $payload = json_encode(array(
            'count' => $count,
            'items' => $items,
        ), JSON_UNESCAPED_UNICODE);

        // retry: 30000 = บอก EventSource ให้ reconnect ใหม่ทุก 30 วิ
        echo "retry: 30000\n";
        echo "event: notification\n";
        echo "data: {$payload}\n\n";

        // flush แล้วจบ — ไม่มี sleep, ไม่ค้าง worker
        flush();
        exit;
    }

    // ── helper ──────────────────────────────────────────────────────
    private function _time_ago($datetime) {
        $diff = time() - strtotime($datetime);
        if ($diff < 60)    return 'เมื่อกี้';
        if ($diff < 3600)  return round($diff / 60) . ' นาทีที่แล้ว';
        if ($diff < 86400) return round($diff / 3600) . ' ชม.ที่แล้ว';
        return date('d/m H:i', strtotime($datetime));
    }
}
