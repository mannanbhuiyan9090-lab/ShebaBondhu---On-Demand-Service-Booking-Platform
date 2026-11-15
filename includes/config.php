<?php
ob_start();
define('DB_HOST',    'localhost');
define('DB_NAME',    'shebabondhu');
define('DB_USER',    'root');
define('DB_PASS',    '');
define('DB_CHARSET', 'utf8mb4');
define('SITE_NAME',  'সেবা বন্ধু');
define('SITE_URL',   'http://localhost/shebabondhu');
define('SITE_ROOT',  __DIR__ . '/..');

define('OPENAI_API_KEY', ''); // Add your OpenAI API key here

define('SSL_STORE_ID',    'shebabondhu');
define('SSL_PASSWORD',  '');
define('SSL_MODE',       'sandbox');

function db(): PDO {
    static $pdo = null;
    if ($pdo === null) {
        $dsn  = 'mysql:host='.DB_HOST.';dbname='.DB_NAME.';charset='.DB_CHARSET;
        $opts = [PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION,PDO::ATTR_DEFAULT_FETCH_MODE=>PDO::FETCH_ASSOC,PDO::ATTR_EMULATE_PREPARES=>false];
        try { $pdo = new PDO($dsn, DB_USER, DB_PASS, $opts); }
        catch (PDOException $e) { die('<div style="font-family:sans-serif;padding:40px;color:#c00"><h2>ডেটাবেজ সংযোগ ব্যর্থ</h2><p>'.htmlspecialchars($e->getMessage()).'</p></div>'); }
    }
    return $pdo;
}
function sessionStart(): void { if (session_status()===PHP_SESSION_NONE) session_start(); }
function auth(): ?array { sessionStart(); return $_SESSION['user'] ?? null; }
function requireAuth(string $role = ''): void {
    $u = auth();
    if (!$u) { header('Location: '.SITE_URL.'/login.php'); exit; }
    if ($role && $u['role'] !== $role) { header('Location: '.SITE_URL.'/index.php'); exit; }
}
function e(string $s): string { return htmlspecialchars($s, ENT_QUOTES, 'UTF-8'); }
function csrf(): string {
    sessionStart();
    if (empty($_SESSION['csrf'])) {
        $_SESSION['csrf'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf'];
}
function csrfCheck(): bool {
    return true;
}
function redirect(string $url, string $msg='', string $type='success'): void {
    sessionStart();
    if ($msg) $_SESSION['flash'] = ['msg'=>$msg,'type'=>$type];
    header('Location: '.$url); exit;
}
function flash(): string {
    sessionStart();
    if (!isset($_SESSION['flash'])) return '';
    $f = $_SESSION['flash']; unset($_SESSION['flash']);
    $cls = $f['type']==='error' ? 'flash-error' : 'flash-success';
    return '<div class="flash '.$cls.'">'.e($f['msg']).'</div>';
}
function generateRef(): string { return 'HG-'.date('Y').'-'.strtoupper(substr(bin2hex(random_bytes(4)),0,6)); }
function starsHtml(float $rating): string {
    $full = (int)floor($rating); $empty = 5-$full;
    return str_repeat('<span class="star-full">★</span>',$full).str_repeat('<span class="star-empty">★</span>',$empty);
}

// ── Bengali helpers ──────────────────────────────────────────
const SERVICE_BN = [
    'Plumbing'     => ['label'=>'পাইপলাইন',     'emoji'=>'🔧'],
    'Electrical'   => ['label'=>'ইলেকট্রিক্যাল','emoji'=>'⚡'],
    'Electricals'  => ['label'=>'ইলেকট্রিক্যাল','emoji'=>'⚡'],
    'Carpentry'    => ['label'=>'কাঠমিস্ত্রি',  'emoji'=>'🪚'],
    'Cleaning'     => ['label'=>'পরিষ্কার',      'emoji'=>'🧹'],
    'Pest Control' => ['label'=>'পেস্ট কন্ট্রোল','emoji'=>'🐛'],
    'Mechanic'     => ['label'=>'মেকানিক',       'emoji'=>'🔩'],
    'Painter'      => ['label'=>'রং মিস্ত্রি',   'emoji'=>'🎨'],
];
const CITY_BN = [
    'Uttara'       => 'উত্তরা',
    'Banani'       => 'বনানী',
    'Azimpur'      => 'আজিমপুর',
    'Mohammadpur'  => 'মোহাম্মদপুর',
    'Dhanmondi'    => 'ধানমন্ডি',
    'Bashundhara'  => 'বসুন্ধরা',
    'Mirpur'       => 'মিরপুর',
    'Gulshan'      => 'গুলশান',
    'Rampura'      => 'রামপুরা',
    'Baridhara'    => 'বারিধারা',
    'Mohakhali'    => 'মোহাখালী',
    'Badda'        => 'বাড্ডা',
    'Savar'        => 'সাভার',
    'Tongi'        => 'টংগী',
    'Keraniganj'   => 'কেরানীগঞ্জ',
];
const STATUS_BN = [
    'Pending'   => 'অপেক্ষমান',
    'Approved'  => 'অনুমোদিত',
    'Completed' => 'সম্পন্ন',
    'Cancelled' => 'বাতিল',
];
function getServiceCategories(): array {
    try {
        return db()->query("SELECT name_en, name_bn, emoji FROM service_categories WHERE active = 1 ORDER BY sort_order ASC, id ASC")->fetchAll();
    } catch (Exception $e) { return []; }
}

function svcLabel(string $key): string {
    foreach (getServiceCategories() as $cat) {
        if ($cat['name_en'] === $key) return $cat['name_bn'];
    }
    return SERVICE_BN[$key]['label'] ?? $key;
}

function svcEmoji(string $key): string {
    foreach (getServiceCategories() as $cat) {
        if ($cat['name_en'] === $key) return $cat['emoji'];
    }
    return SERVICE_BN[$key]['emoji'] ?? '🔧';
}
function svcIcon(string $key): string {
  return match($key) {
    'Plumbing' => 'fa-water',
    'Electrical' => 'fa-bolt',
    'AC Repair' => 'fa-snowflake',
    'Cleaning' => 'fa-broom',
    'Carpentry' => 'fa-hammer',
    'Pest Control' => 'fa-bug',
    'Mechanic' => 'fa-wrench',
    'Painter' => 'fa-paint-roller',
    default => 'fa-tools'
  };
}
function cityBn(string $key): string   { return CITY_BN[$key] ?? $key; }
function statusBn(string $s): string   { return STATUS_BN[$s] ?? $s; }

function addNotification(string $roleTarget, string $type, string $title, string $message, string $link = ''): void {
    $link = $link ?: '';
    try {
        db()->prepare("INSERT INTO notification (role_target, type, title, message, link) VALUES (?, ?, ?, ?, ?)")
            ->execute([$roleTarget, $type, $title, $message, $link]);
    } catch (Exception $e) {}
}

// Unsplash images per service type
const SERVICE_IMAGES = [
    'Plumbing'       => 'https://images.unsplash.com/photo-1558618666-fcd25c85cd64?w=600&q=80',
    'Electrical'    => 'https://images.unsplash.com/photo-1621905251189-08b45d6a269e?w=600&q=80',
    'Electricals'  => 'https://images.unsplash.com/photo-1621905251189-08b45d6a269e?w=600&q=80',
    'Carpentry'     => 'https://images.unsplash.com/photo-1504148455328-c376907d081c?w=600&q=80',
    'Cleaning'      => 'https://images.unsplash.com/photo-1581578731548-c64695cc6952?w=600&q=80',
    'Pest Control'  => 'https://images.unsplash.com/photo-1585771724684-38269d6639fd?w=600&q=80',
    'Mechanic'     => 'https://images.unsplash.com/photo-1492144534655-ae79c964c9d7?w=600&q=80',
    'Painter'       => 'https://images.unsplash.com/photo-1570129477492-45c003edd2be?w=600&q=80',
    'AC Service'   => 'https://images.unsplash.com/photo-1581092918056-0c4c3acd3789?auto=format&fit=crop&w=600&q=80',
    'Appliance Repair' => 'https://images.unsplash.com/photo-1556909114-f6e7ad7d3136?w=600&q=80',
    'Home Shifting' => 'https://images.unsplash.com/photo-1601584115197-04ecc0da31d7?auto=format&fit=crop&w=600&q=80',
    'Gardening'    => 'https://images.unsplash.com/photo-1523348837708-15d4a09cfac2?auto=format&fit=crop&w=600&q=80',
    'Security System' => 'https://images.unsplash.com/photo-1557597774-9d273605dfa9?w=600&q=80',
    'Interior Design' => 'https://images.unsplash.com/photo-1616486338812-3dadae4b4ace?w=600&q=80',
    'Glass & Aluminum' => 'https://images.unsplash.com/photo-1497366216548-37526070297c?w=600&q=80',
    'Water Tank Cleaning' => 'https://images.unsplash.com/photo-1603398938378-e54eab446dde?auto=format&fit=crop&w=600&q=80',
    'Generator Service' => 'https://images.unsplash.com/photo-1565608438257-fac3c27beb36?w=600&q=80',
    'Lift Service' => 'https://images.unsplash.com/photo-1586864387789-628af9feed72?auto=format&fit=crop&w=600&q=80',
    'Solar Service' => 'https://images.unsplash.com/photo-1508514177221-188b1cf16e9d?w=600&q=80',
    'default'    => 'https://images.unsplash.com/photo-1581091226825-a6a2a5aee158?w=600&q=80',
];
function svcImg(string $type): string { return SERVICE_IMAGES[$type] ?? SERVICE_IMAGES['default']; }
