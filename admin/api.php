<?php
declare(strict_types=1);
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/_stats.php';
sessionStart();

header('Content-Type: application/json; charset=utf-8');

// Must be admin
$user = auth();
if (!$user || $user['role'] !== 'admin') {
    http_response_code(403);
    echo json_encode(['ok' => false, 'msg' => 'Unauthorized']);
    exit;
}

$action = $_GET['action'] ?? $_POST['action'] ?? '';

switch ($action) {

    /* ────────────────────────────────────
       Notifications — list
       GET ?action=notifications
    ──────────────────────────────────── */
    case 'notifications':
        $rows = db()->query("SELECT id, type, title, message, is_read, created_at FROM notification WHERE role_target IN ('admin', '') OR role_target IS NULL ORDER BY created_at DESC LIMIT 5")->fetchAll();
        $unread = (int) db()->query("SELECT COUNT(*) FROM notification WHERE role_target='admin' AND is_read=0")->fetchColumn();
        echo json_encode(['ok' => true, 'unread' => $unread, 'items' => $rows]);
        break;

    /* ────────────────────────────────────
       Notifications — mark read
       POST action=mark_read  &id=X  (or id=all)
    ──────────────────────────────────── */
    case 'mark_read':
        $id = $_POST['id'] ?? '';
        if ($id === 'all') {
            db()->exec("UPDATE notification SET is_read=1 WHERE role_target='admin'");
        } else {
            db()->prepare("UPDATE notification SET is_read=1 WHERE id=?")->execute([(int)$id]);
        }
        echo json_encode(['ok' => true]);
        break;

    /* ────────────────────────────────────
       Notifications — clear all
       POST action=clear_notifications
    ──────────────────────────────────── */
    case 'clear_notifications':
        db()->exec("DELETE FROM notification WHERE role_target='admin' AND is_read=1");
        echo json_encode(['ok' => true]);
        break;

    /* ────────────────────────────────────
       Booking — set status (AJAX)
       POST action=booking_status  &id=X  &status=Approved|Cancelled|Completed|Pending
    ──────────────────────────────────── */
    case 'booking_status':
        $id     = (int) ($_POST['id'] ?? 0);
        $status = $_POST['status'] ?? '';
        $valid  = ['Pending', 'Approved', 'Completed', 'Cancelled'];

        if ($id <= 0 || !in_array($status, $valid, true)) {
            echo json_encode(['ok' => false, 'msg' => 'Invalid parameters']);
            break;
        }

        try {
            db()->prepare('UPDATE bookings SET status=? WHERE id=?')->execute([$status, $id]);

            // Fetch booking info for notification
            $bk = db()->prepare("
                SELECT b.ref_code, u.name AS cname, pr.company_name
                FROM bookings b
                JOIN users u ON u.id = b.customer_id
                JOIN providers pr ON pr.id = b.provider_id
                WHERE b.id = ?
            ");
            $bk->execute([$id]);
            $bkInfo = $bk->fetch();

            // Create notification
            $statusBn = ['Pending'=>'অপেক্ষমান','Approved'=>'অনুমোদিত','Completed'=>'সম্পন্ন','Cancelled'=>'বাতিল'];
            $title = 'বুকিং ' . ($statusBn[$status] ?? $status);
            $msg   = ($bkInfo ? $bkInfo['ref_code'] . ' — ' . $bkInfo['cname'] . ' → ' . $bkInfo['company_name'] : "Booking #$id") . ' ' . ($statusBn[$status] ?? $status) . ' হয়েছে।';
            db()->prepare("INSERT INTO notification (user_id, role_target,type,title,message,link,is_read,created_at) VALUES (0,'admin','booking',?,?,?,0,NOW())")
                ->execute([$title, $msg, 'bookings.php']);

            echo json_encode([
                'ok'     => true,
                'msg'    => 'স্ট্যাটাস আপডেট হয়েছে।',
                'status' => $status,
                'statusBn' => $statusBn[$status] ?? $status,
            ]);
        } catch (PDOException $e) {
            echo json_encode(['ok' => false, 'msg' => $e->getMessage()]);
        }
        break;

    /* ────────────────────────────────────
       Booking — delete (AJAX)
       POST action=booking_delete  &id=X
    ──────────────────────────────────── */
    case 'booking_delete':
        $id = (int) ($_POST['id'] ?? 0);
        if ($id <= 0) {
            echo json_encode(['ok' => false, 'msg' => 'Invalid ID']);
            break;
        }
        try {
            $ref = db()->prepare("SELECT ref_code FROM bookings WHERE id=?");
            $ref->execute([$id]);
            $refRow = $ref->fetch();
            db()->prepare('DELETE FROM bookings WHERE id=?')->execute([$id]);
            // notification
            db()->prepare("INSERT INTO notification (user_id, role_target,type,title,message,link,is_read,created_at) VALUES (0,'admin','booking','বুকিং মুছে ফেলা হয়েছে',?,?,0,NOW())")
                ->execute(['বুকিং ' . ($refRow['ref_code'] ?? '#'.$id) . ' মুছে ফেলা হয়েছে।', 'bookings.php']);
            echo json_encode(['ok' => true, 'msg' => 'বুকিং মুছে ফেলা হয়েছে।']);
        } catch (PDOException $e) {
            echo json_encode(['ok' => false, 'msg' => $e->getMessage()]);
        }
        break;

    /* ────────────────────────────────────
       Dashboard stats (AJAX refresh)
    ──────────────────────────────────── */
    case 'stats':
        echo json_encode(['ok' => true, 'stats' => getAdminStats()]);
        break;

    /* ────────────────────────────────────
       Unread notifications count (AJAX)
       GET ?action=unread_notifs
    ──────────────────────────────────── */
    case 'unread_notifs':
        $count = (int) db()->query("
            SELECT COUNT(*) FROM notification WHERE role_target='admin' AND is_read=0
        ")->fetchColumn();
        echo json_encode(['ok' => true, 'count' => $count]);
        break;

    /* ────────────────────────────────────
       Default
    ──────────────────────────────────── */
    default:
        echo json_encode(['ok' => false, 'msg' => 'Unknown action']);
}
