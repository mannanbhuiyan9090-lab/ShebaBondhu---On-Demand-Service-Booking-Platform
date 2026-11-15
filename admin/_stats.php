<?php
declare(strict_types=1);

function getAdminStats(): array
{
    try {
        // Ensure amount column exists
        try {
            db()->query("ALTER TABLE bookings ADD COLUMN amount DECIMAL(10,2) DEFAULT NULL AFTER payment_code");
        } catch (Exception $e) {}
        db()->exec("CREATE TABLE IF NOT EXISTS `service_categories` (
            id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            name_en VARCHAR(80) NOT NULL UNIQUE,
            name_bn VARCHAR(100) NOT NULL,
            emoji VARCHAR(10) NOT NULL DEFAULT '?',
            description TEXT,
            manpower INT DEFAULT 1,
            active TINYINT(1) NOT NULL DEFAULT 1,
            sort_order SMALLINT NOT NULL DEFAULT 0,
            created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
        
        try {
            db()->exec("ALTER TABLE service_categories ADD COLUMN manpower INT DEFAULT 1 AFTER description");
        } catch (Exception $e) {}

        db()->exec('CREATE TABLE IF NOT EXISTS `notification` (
            id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            role_target VARCHAR(30) NOT NULL DEFAULT \'admin\',
            type VARCHAR(40) NOT NULL DEFAULT \'system\',
            title VARCHAR(180) NOT NULL,
            message TEXT NOT NULL,
            link VARCHAR(255) DEFAULT NULL,
            is_read TINYINT(1) NOT NULL DEFAULT 0,
            created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
            KEY idx_role_read (role_target,is_read)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci');

        $row = db()->query('SELECT
            (SELECT COUNT(*) FROM users WHERE role="customer") AS cu,
            (SELECT COUNT(*) FROM providers) AS pu,
            (SELECT COUNT(*) FROM providers WHERE verified=1) AS pv,
            (SELECT COUNT(*) FROM bookings) AS bo,
            (SELECT COUNT(*) FROM bookings WHERE status="Pending") AS bp,
            (SELECT COUNT(*) FROM bookings WHERE status="Completed") AS bc,
            (SELECT COUNT(*) FROM provider_applications WHERE status="pending") AS ap,
            (SELECT COUNT(*) FROM reviews) AS rv,
            (SELECT COUNT(*) FROM service_categories WHERE active=1) AS sc,
            (SELECT COUNT(*) FROM notification WHERE role_target="admin" AND is_read=0) AS nt,
            (SELECT COALESCE(SUM(amount), 0) * 0.15 FROM bookings WHERE status="Completed" AND payment_status="paid") AS revenue,
            (SELECT COALESCE(SUM(amount), 0) FROM bookings WHERE status="Completed" AND payment_status="paid") AS total_amount,
            (SELECT COALESCE(SUM(amount), 0) * 0.15 FROM bookings WHERE status="Completed" AND payment_status="paid" AND created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)) AS weekly_revenue,
            (SELECT COALESCE(SUM(amount), 0) * 0.15 FROM bookings WHERE status="Completed" AND payment_status="paid" AND created_at >= DATE_FORMAT(NOW(), "%Y-%m-01")) AS monthly_revenue,
            (SELECT COALESCE(SUM(amount), 0) * 0.15 FROM bookings WHERE status="Completed" AND payment_status="paid" AND created_at >= DATE_FORMAT(NOW(), "%Y-01-01")) AS yearly_revenue
        ')->fetch(PDO::FETCH_ASSOC);

        $result = [];
        if ($row) {
            $result = [
                'cu' => (int)$row['cu'],
                'pu' => (int)$row['pu'],
                'pv' => (int)$row['pv'],
                'bo' => (int)$row['bo'],
                'bp' => (int)$row['bp'],
                'bc' => (int)$row['bc'],
                'ap' => (int)$row['ap'],
                'rv' => (int)$row['rv'],
                'sc' => (int)$row['sc'],
                'nt' => (int)$row['nt'],
                'revenue' => (float)$row['revenue'],
                'total_amount' => (float)$row['total_amount'],
                'weekly_revenue' => (float)$row['weekly_revenue'],
                'monthly_revenue' => (float)$row['monthly_revenue'],
                'yearly_revenue' => (float)$row['yearly_revenue'],
            ];
        }
        return $result ?: array_fill_keys(['cu','pu','pv','bo','bp','bc','ap','rv','sc','nt','revenue','total_amount','weekly_revenue','monthly_revenue','yearly_revenue'], 0);
    } catch (PDOException $e) {
        return array_fill_keys(['cu','pu','pv','bo','bp','bc','ap','rv','sc','nt'], 0);
    }
}

function adminPendingBookings(): int {
    try {
        return (int)db()->query("SELECT COUNT(*) FROM bookings WHERE status='Pending'")->fetchColumn();
    } catch (Throwable $e) {
        return 0;
    }
}

function renderBookingBadge(): void {
    $count = adminPendingBookings();
    if ($count > 0): ?><span class="ml-auto bg-red-500 text-white text-xs rounded-full w-5 h-5 flex items-center justify-center font-bold"><?= $count ?></span><?php endif;
}

