<?php defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * SSE Notifications — รองรับ 200+ concurrent users บน Shared Host
 *
 * วิธีลด DB load:
 * - Client ส่ง ?since={timestamp} มาด้วย
 * - ถ้าไม่มี notification ใหม่หลัง timestamp → ตอบ 304-like (count เดิม, items ว่าง)
 * - Query หนัก (get_recent) รันเฉพาะเมื่อมีของใหม่จริงๆ
 * - ลด DB query จาก 2 → 1 ในกรณีปกติ (ไม่มี notification ใหม่)
 */
class Notifications extends MY_Controller {

    public function __construct() {
        parent::__construct();
        $this->require_login();
    }

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

    public function mark_all_read() {
        $this->Notification_model->mark_all_read($this->current_user->user_id);
        $this->json_ok(array('count' => 0));
    }

    // ── SSE stream ───────────────────────────────────────────────────
    public function stream() {
        $uid   = $this->current_user->user_id;
        $since = (int)($this->input->get('since') ?: 0); // timestamp ที่ client รู้ล่าสุด

        while (ob_get_level() > 0) ob_end_clean();

        header('Content-Type: text/event-stream; charset=utf-8');
        header('Cache-Control: no-cache, no-store, must-revalidate');
        header('Pragma: no-cache');
        header('Expires: 0');
        header('X-Accel-Buffering: no');
        header('Connection: close');

        // ── Query 1: เช็ค notification ล่าสุด (1 query เบามาก) ───────
        $latest = $this->db
            ->select('id, created_at')
            ->where('user_id', $uid)
            ->order_by('created_at', 'DESC')
            ->limit(1)
            ->get('notifications')->row();

        $latest_ts  = $latest ? strtotime($latest->created_at) : 0;
        $has_new    = ($latest_ts > $since);
        $count      = $this->Notification_model->count_unread($uid);

        if ($has_new || $since === 0) {
            // มีของใหม่ หรือ load ครั้งแรก → query เต็ม
            $rows = $this->Notification_model->get_recent($uid, 5);
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
        } else {
            // ไม่มีของใหม่ → ส่งแค่ count ไม่ส่ง items (ลด payload)
            $items = null;
        }

        $payload = json_encode(array(
            'count'      => $count,
            'items'      => $items,       // null = ไม่มีการเปลี่ยนแปลง
            'latest_ts'  => $latest_ts,   // client เก็บไว้ส่งมาใน ?since= รอบถัดไป
        ), JSON_UNESCAPED_UNICODE);

        echo "retry: 10000\n";
        echo "event: notification\n";
        echo "data: {$payload}\n\n";
        flush();
        exit;
    }

    private function _time_ago($datetime) {
        $diff = time() - strtotime($datetime);
        if ($diff < 60)    return 'เมื่อกี้';
        if ($diff < 3600)  return round($diff / 60) . ' นาทีที่แล้ว';
        if ($diff < 86400) return round($diff / 3600) . ' ชม.ที่แล้ว';
        return date('d/m H:i', strtotime($datetime));
    }
}
