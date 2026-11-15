<?php
/**
 * Chatbot API - Sheba Bondhu AI Assistant
 * Enhanced with: Secure API, Caching, Dynamic DB, Retry Logic
 */

header('Content-Type: application/json');
header('X-Content-Type-Options: nosniff');

require_once __DIR__ . '/includes/config.php';

// ==========================================
// 🔐 SECURE API KEY MANAGEMENT
// ==========================================
function getApiKey(): string {
    // 1. Check .env file
    $envFile = __DIR__ . '/.env';
    if (file_exists($envFile)) {
        $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        foreach ($lines as $line) {
            if (strpos($line, '#') === 0) continue;
            if (strpos($line, 'OPENAI_API_KEY=') === 0) {
                $val = trim(substr($line, strlen('OPENAI_API_KEY=')));
                if ($val && $val !== '') return $val;
            }
        }
    }
    // 2. Check defined constant
    if (defined('OPENAI_API_KEY') && OPENAI_API_KEY !== '') {
        return OPENAI_API_KEY;
    }
    // 3. Check environment variable
    $envKey = getenv('OPENAI_API_KEY');
    if ($envKey && $envKey !== '') return $envKey;

    return '';
}

// ==========================================
// ⚡ CACHING SYSTEM (1hr TTL)
// ==========================================
function setCache($key, $data) {
    $cacheDir = __DIR__ . '/cache/';
    if (!is_dir($cacheDir)) mkdir($cacheDir, 0755, true);
    $payload = ['data' => $data, 'time' => time()];
    file_put_contents($cacheDir . md5($key) . '.json', json_encode($payload, JSON_UNESCAPED_UNICODE));
    return true;
}

function getCache($key, $ttl = 3600) {
    $cacheDir = __DIR__ . '/cache/';
    $file = $cacheDir . md5($key) . '.json';
    if (!file_exists($file)) return null;
    $payload = json_decode(file_get_contents($file), true);
    if (!$payload || (time() - ($payload['time'] ?? 0)) > $ttl) {
        @unlink($file);
        return null;
    }
    return $payload['data'] ?? null;
}

// ==========================================
// 📝 ERROR LOGGING
// ==========================================
function logError($context, $message, $extra = []) {
    $logDir = __DIR__ . '/cache/logs/';
    if (!is_dir($logDir)) mkdir($logDir, 0755, true);
    $entry = [
        'time'    => date('Y-m-d H:i:s'),
        'context' => $context,
        'message' => $message,
        'extra'   => $extra
    ];
    file_put_contents(
        $logDir . 'chatbot_' . date('Y-m-d') . '.log',
        json_encode($entry, JSON_UNESCAPED_UNICODE) . "\n",
        FILE_APPEND | LOCK_EX
    );
}

// ==========================================
// 🔍 SMART FAQ MATCHING (Regex-based)
// ==========================================
function matchFAQ($msgLower) {
    $faqs = [
        // Greetings
        ['pattern' => '/(হ্যালো|হাই|হেলো|hello|hi|hey|assalamu|আসসালামু)/u', 'reply' => 'হ্যালো! 👋 আমি সেবা বন্ধুর AI সহকারী। আপনাকে কিভাবে সাহায্য করতে পারি? সেবা খুঁজুন, বুকিং করুন, বা যেকোনো প্রশ্ন করুন!'],
        // Provider count
        ['pattern' => '/(কতজন|কত জন|সেবাদাতা আছ|provider|how many)/u', 'reply' => null, 'action' => 'count_providers'],
        // Service list
        ['pattern' => '/(কী কী সেবা|সেবা তালিকা|সেবা আছে|service list|what service)/u', 'reply' => null, 'action' => 'list_services'],
        // Top rated
        ['pattern' => '/(শীর্ষ|সেরা|top|best|রেটেড|rated)/u', 'reply' => null, 'action' => 'top_rated'],
        // Area/location
        ['pattern' => '/(কোথায়|এলাকা|area|location|কোন কোন)/u', 'reply' => null, 'action' => 'list_areas'],
        // Pricing
        ['pattern' => '/(দাম|মূল্য|price|cost|কত টাকা|খরচ)/u', 'reply' => 'আমাদের সেবার মূল্য সেবার ধরন ও জটিলতার উপর নির্ভর করে। সাধারণত ৳৫০০ - ৳৫,০০০ এর মধ্যে। নির্দিষ্ট সেবার মূল্য জানতে সেবার নাম বলুন! 💰'],
        // Booking
        ['pattern' => '/(বুকিং|বুক করতে|book|booking|appointment)/u', 'reply' => 'বুকিং করতে: ১) সেবা পাতায় যান ২) পছন্দের সেবাদাতা বেছে নিন ৩) "বুক করুন" বাটনে ক্লিক করুন ৪) তারিখ ও সময় দিন। খুবই সহজ! 📅'],
        // Contact
        ['pattern' => '/(যোগাযোগ|ফোন|contact|call|phone|কল)/u', 'reply' => 'যোগাযোগ করুন: 📞 0171954698 | ✉️ info@shebabondhu.com | অথবা আমাদের যোগাযোগ পাতায় মেসেজ পাঠান!'],
        // Thanks
        ['pattern' => '/(ধন্যবাদ|thanks|thank you|শুকরিয়া)/u', 'reply' => 'আপনাকেও ধন্যবাদ! 😊 সেবা বন্ধু সবসময় আপনার পাশে। আর কিছু জানতে চাইলে জিজ্ঞেস করুন!'],
    ];

    foreach ($faqs as $faq) {
        if (preg_match($faq['pattern'], $msgLower)) {
            return $faq;
        }
    }
    return null;
}

// ==========================================
// 🗄️ DYNAMIC DATABASE QUERIES
// ==========================================
function dbCountProviders() {
    try {
        $r = db()->query("SELECT COUNT(*) as cnt FROM providers WHERE verified=1")->fetch();
        $total = $r['cnt'] ?? 0;
        $avail = db()->query("SELECT COUNT(*) as cnt FROM providers WHERE verified=1 AND available=1")->fetch()['cnt'] ?? 0;
        return "আমাদের মোট {$total} জন যাচাইকৃত সেবাদাতা আছেন, যার মধ্যে {$avail} জন এখন উপলব্ধ! 🎯";
    } catch (Exception $e) {
        logError('dbCountProviders', $e->getMessage());
        return 'দুঃখিত, তথ্য লোড করতে সমস্যা হচ্ছে। একটু পরে আবার চেষ্টা করুন।';
    }
}

function dbListServices() {
    try {
        $cats = db()->query("SELECT name_bn, emoji FROM service_categories WHERE active=1 ORDER BY sort_order ASC")->fetchAll();
        if (empty($cats)) return 'এখনও সেবা তালিকা লোড হয়নি।';
        $list = "আমাদের সেবা তালিকা:\n";
        foreach ($cats as $c) {
            $list .= "{$c['emoji']} {$c['name_bn']}\n";
        }
        $list .= "\nযেকোনো সেবায় বুকিং করতে services পাতায় যান! 🔗";
        return $list;
    } catch (Exception $e) {
        logError('dbListServices', $e->getMessage());
        return 'সেবা তালিকা লোড করতে সমস্যা হচ্ছে।';
    }
}

function dbTopRated() {
    try {
        $rows = db()->query("SELECT p.company_name, p.service_type, p.rating, p.review_count, p.city FROM providers p WHERE p.verified=1 ORDER BY p.rating DESC, p.review_count DESC LIMIT 5")->fetchAll();
        if (empty($rows)) return 'এখনও কোনো রেটিং পাওয়া যায়নি।';
        $msg = "⭐ শীর্ষ সেবাদাতা:\n";
        foreach ($rows as $i => $r) {
            $n = $i + 1;
            $msg .= "{$n}. {$r['company_name']} — {$r['rating']}★ ({$r['review_count']} রিভিউ) — {$r['city']}\n";
        }
        return $msg;
    } catch (Exception $e) {
        logError('dbTopRated', $e->getMessage());
        return 'শীর্ষ সেবাদাতা লোড করতে সমস্যা হচ্ছে।';
    }
}

function dbListAreas() {
    try {
        $rows = db()->query("SELECT city, COUNT(*) as cnt FROM providers WHERE verified=1 GROUP BY city ORDER BY cnt DESC")->fetchAll();
        if (empty($rows)) return 'এলাকার তথ্য পাওয়া যায়নি।';
        $msg = "📍 সেবা প্রদানকারী এলাকাসমূহ:\n";
        foreach ($rows as $r) {
            $bn = CITY_BN[$r['city']] ?? $r['city'];
            $msg .= "🏠 {$bn} — {$r['cnt']} জন সেবাদাতা\n";
        }
        return $msg;
    } catch (Exception $e) {
        logError('dbListAreas', $e->getMessage());
        return 'এলাকার তথ্য লোড করতে সমস্যা হচ্ছে।';
    }
}

// ==========================================
// 🌍 LOCATION-BASED PROVIDER SEARCH
// ==========================================
function getLocationData($msgLower) {
    $locations = [
        'dhaka_areas' => [
            'uttara' => ['উত্তরা', 'uttara'], 'dhanmondi' => ['ধানমন্ডি', 'dhanmondi', 'danmondi'],
            'mohammadpur' => ['মহাম্মদপুর', 'mohammadpur'], 'mirpur' => ['মিরপুর', 'mirpur'],
            'gulshan' => ['গুলশান', 'gulshan'], 'banani' => ['বনানী', 'banani'],
            'baridhara' => ['বারিধারা', 'baridhara'], 'bashundhara' => ['বসুন্ধরা', 'bashundhara'],
            'savar' => ['সাভার', 'savar'], 'rampura' => ['রামপুরা', 'rampura'],
            'mohakhali' => ['মোহাখালী', 'mohakhali'], 'badda' => ['বাড্ডা', 'badda'],
            'tongi' => ['টংগী', 'tongi'], 'keraniganj' => ['কেরানীগঞ্জ', 'keraniganj'],
            'azimpur' => ['আজিমপুর', 'azimpur'],
        ],
        'other_cities' => [
            'chittagong' => ['চট্টগ্রাম', 'chittagong', 'ctg'],
            'sylhet' => ['সিলেট', 'sylhet'], 'rajshahi' => ['রাজশাহী', 'rajshahi'],
            'khulna' => ['খুলনা', 'khulna'], 'barisal' => ['বরিশাল', 'barisal'],
        ]
    ];

    foreach (['dhaka_areas', 'other_cities'] as $group) {
        foreach ($locations[$group] as $key => $aliases) {
            foreach ($aliases as $alias) {
                if (mb_strpos($msgLower, $alias) !== false) {
                    return queryLocationProviders($key);
                }
            }
        }
    }
    return null;
}

function queryLocationProviders($locationKey) {
    $cacheKey = 'loc_' . $locationKey;
    $cached = getCache($cacheKey);
    if ($cached) return $cached;

    try {
        $like = "%{$locationKey}%";
        $stmt = db()->prepare("SELECT COUNT(*) as total, AVG(rating) as avg_rating, COUNT(CASE WHEN available=1 THEN 1 END) as avail FROM providers WHERE verified=1 AND (LOWER(city) LIKE ? OR LOWER(city) LIKE ?)");
        $stmt->execute([$like, $like]);
        $stats = $stmt->fetch();

        $name = CITY_BN[ucfirst($locationKey)] ?? ucfirst($locationKey);
        $total = (int)($stats['total'] ?? 0);

        if ($total > 0) {
            $rating = round($stats['avg_rating'] ?? 0, 1);
            $avail  = (int)($stats['avail'] ?? 0);

            // Get top providers for this area
            $topStmt = db()->prepare("SELECT p.company_name, p.service_type, p.rating FROM providers p WHERE p.verified=1 AND LOWER(p.city) LIKE ? ORDER BY p.rating DESC LIMIT 3");
            $topStmt->execute([$like]);
            $topProviders = $topStmt->fetchAll();

            $reply = "✅ {$name} এ {$total} জন যাচাইকৃত সেবাদাতা ({$rating}/5 ★)। {$avail} জন এখন উপলব্ধ।\n";
            if (!empty($topProviders)) {
                $reply .= "\n🏆 শীর্ষ সেবাদাতা:\n";
                foreach ($topProviders as $tp) {
                    $reply .= "• {$tp['company_name']} — {$tp['rating']}★\n";
                }
            }
            $reply .= "\nএখনই বুক করুন! 📅";
        } else {
            $reply = "❌ {$name} এ এখনও সেবাদাতা নেই। কিন্তু কাছের এলাকায় সেবা পেতে পারেন! কল করুন: 0171954698 📞";
        }

        $result = ['reply' => $reply, 'source' => 'location_db'];
        setCache($cacheKey, $result);
        return $result;
    } catch (Exception $e) {
        logError('queryLocationProviders', $e->getMessage());
        return ['reply' => 'এলাকার তথ্য লোড করতে সমস্যা হচ্ছে। একটু পরে চেষ্টা করুন।', 'source' => 'error'];
    }
}

// ==========================================
// 🔧 SERVICE-SPECIFIC PROVIDER SEARCH
// ==========================================
function getServiceData($msgLower) {
    $serviceMap = [
        'plumbing'     => ['প্লাম্বিং', 'পাইপলাইন', 'পাইপ', 'plumbing', 'pipe'],
        'electrical'   => ['ইলেকট্রিক', 'বৈদ্যুতিক', 'electrical', 'wiring'],
        'cleaning'     => ['ক্লিনিং', 'পরিষ্কার', 'cleaning', 'clean'],
        'carpentry'    => ['কার্পেন্ট্রি', 'কাঠমিস্ত্রি', 'কাঠ', 'carpentry', 'wood'],
        'pest control' => ['পেস্ট', 'পোকামাকড়', 'pest'],
        'painter'      => ['পেইন্ট', 'রং', 'paint', 'painter'],
        'mechanic'     => ['মেকানিক', 'mechanic'],
        'ac service'   => ['এসি', 'ac ', 'air condition'],
    ];

    foreach ($serviceMap as $type => $keywords) {
        foreach ($keywords as $kw) {
            if (mb_strpos($msgLower, $kw) !== false) {
                return queryServiceProviders($type);
            }
        }
    }
    return null;
}

function queryServiceProviders($serviceType) {
    $cacheKey = 'svc_' . $serviceType;
    $cached = getCache($cacheKey);
    if ($cached) return $cached;

    try {
        $stmt = db()->prepare("SELECT p.company_name, p.rating, p.city, p.available, p.price_range FROM providers p WHERE p.verified=1 AND LOWER(p.service_type) LIKE ? ORDER BY p.rating DESC LIMIT 5");
        $like = "%{$serviceType}%";
        $stmt->execute([$like]);
        $providers = $stmt->fetchAll();

        $svcLabel = SERVICE_BN[ucfirst($serviceType)]['label'] ?? ucfirst($serviceType);

        if (empty($providers)) {
            $reply = "দুঃখিত, {$svcLabel} সেবায় এখন কোনো সেবাদাতা নেই। শীঘ্রই যোগ হবে! 🔜";
        } else {
            $reply = "🔧 {$svcLabel} সেবায় সেরা সেবাদাতা:\n\n";
            foreach ($providers as $p) {
                $status = $p['available'] ? '🟢' : '🔴';
                $city = CITY_BN[$p['city']] ?? $p['city'];
                $reply .= "{$status} {$p['company_name']} — {$p['rating']}★ — {$city}\n";
            }
            $reply .= "\nবুক করতে services পাতায় যান! 📅";
        }

        $result = ['reply' => $reply, 'source' => 'service_db'];
        setCache($cacheKey, $result);
        return $result;
    } catch (Exception $e) {
        logError('queryServiceProviders', $e->getMessage());
        return ['reply' => 'সেবার তথ্য লোড করতে সমস্যা হচ্ছে।', 'source' => 'error'];
    }
}

// ==========================================
// 🤖 OPENAI API CALL WITH RETRY + BACKOFF
// ==========================================
function callOpenAI($message, $maxRetries = 3) {
    $apiKey = getApiKey();
    if (!$apiKey) {
        logError('callOpenAI', 'No API key configured');
        return null;
    }

    $systemPrompt = "তুমি সেবা বন্ধুর AI সহকারী। সেবা বন্ধু বাংলাদেশের একটি হোম সার্ভিস প্ল্যাটফর্ম যেখানে যাচাইকৃত সেবাদাতা পাওয়া যায়। "
        . "তুমি বাংলায় উত্তর দেবে। উত্তর সংক্ষিপ্ত, বন্ধুত্বপূর্ণ ও সহায়ক হবে। "
        . "সেবাসমূহ: প্লাম্বিং, ইলেকট্রিক্যাল, ক্লিনিং, কার্পেন্ট্রি, পেস্ট কন্ট্রোল, পেইন্টিং, মেকানিক, এসি সার্ভিস ইত্যাদি। "
        . "ওয়েবসাইট: shebabondhu.com | ফোন: 0171954698";

    $payload = json_encode([
        'model'       => 'gpt-3.5-turbo',
        'messages'    => [
            ['role' => 'system', 'content' => $systemPrompt],
            ['role' => 'user',   'content' => $message]
        ],
        'temperature' => 0.3,
        'max_tokens'  => 300,
    ]);

    for ($attempt = 1; $attempt <= $maxRetries; $attempt++) {
        $ch = curl_init('https://api.openai.com/v1/chat/completions');
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST           => true,
            CURLOPT_HTTPHEADER     => [
                'Content-Type: application/json',
                'Authorization: Bearer ' . $apiKey,
            ],
            CURLOPT_POSTFIELDS     => $payload,
            CURLOPT_TIMEOUT        => 15,
            CURLOPT_CONNECTTIMEOUT => 5,
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        curl_close($ch);

        if ($curlError) {
            logError('callOpenAI', "cURL error (attempt {$attempt})", ['error' => $curlError]);
            if ($attempt < $maxRetries) { usleep(pow(2, $attempt) * 200000); continue; }
            return null;
        }

        if ($httpCode === 200) {
            $data = json_decode($response, true);
            return $data['choices'][0]['message']['content'] ?? null;
        }

        if ($httpCode === 429 || $httpCode >= 500) {
            logError('callOpenAI', "HTTP {$httpCode} (attempt {$attempt})", ['response' => substr($response, 0, 300)]);
            if ($attempt < $maxRetries) { usleep(pow(2, $attempt) * 500000); continue; }
        } else {
            logError('callOpenAI', "HTTP {$httpCode} fatal", ['response' => substr($response, 0, 300)]);
            return null;
        }
    }
    return null;
}

// ==========================================
// 🎯 MAIN EXECUTION FLOW
// ==========================================
try {
    // Validate request
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(405);
        echo json_encode(['reply' => 'Method not allowed', 'source' => 'error']);
        exit;
    }

    $message = trim($_POST['message'] ?? '');
    if ($message === '') {
        echo json_encode(['reply' => 'দয়া করে একটি প্রশ্ন লিখুন! 😊', 'source' => 'validation']);
        exit;
    }

    // Rate limiting (simple per-IP)
    $clientIP = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    $rateKey = 'rate_' . md5($clientIP) . '_' . date('YmdHi');
    $rateCount = getCache($rateKey, 60) ?? 0;
    if ($rateCount > 20) {
        echo json_encode(['reply' => 'আপনি অনেক বেশি মেসেজ পাঠাচ্ছেন। একটু অপেক্ষা করুন। ⏳', 'source' => 'rate_limit']);
        exit;
    }
    setCache($rateKey, $rateCount + 1);

    $msgLower = mb_strtolower($message, 'UTF-8');

    // Check full-response cache first
    $responseCache = getCache('resp_' . md5($msgLower));
    if ($responseCache) {
        $responseCache['cached'] = true;
        echo json_encode($responseCache, JSON_UNESCAPED_UNICODE);
        exit;
    }

    // STEP 1: FAQ matching
    $faq = matchFAQ($msgLower);
    if ($faq) {
        if (!empty($faq['reply'])) {
            $response = ['reply' => $faq['reply'], 'source' => 'faq'];
        } else {
            // Dynamic FAQ actions
            $actionReply = match($faq['action'] ?? '') {
                'count_providers' => dbCountProviders(),
                'list_services'  => dbListServices(),
                'top_rated'      => dbTopRated(),
                'list_areas'     => dbListAreas(),
                default          => 'আপনার প্রশ্নের উত্তর খুঁজছি...'
            };
            $response = ['reply' => $actionReply, 'source' => 'faq_db'];
        }
        setCache('resp_' . md5($msgLower), $response);
        echo json_encode($response, JSON_UNESCAPED_UNICODE);
        exit;
    }

    // STEP 2: Location-based search
    $locationResult = getLocationData($msgLower);
    if ($locationResult) {
        setCache('resp_' . md5($msgLower), $locationResult);
        echo json_encode($locationResult, JSON_UNESCAPED_UNICODE);
        exit;
    }

    // STEP 3: Service-based search
    $serviceResult = getServiceData($msgLower);
    if ($serviceResult) {
        setCache('resp_' . md5($msgLower), $serviceResult);
        echo json_encode($serviceResult, JSON_UNESCAPED_UNICODE);
        exit;
    }

    // STEP 4: OpenAI fallback
    $aiReply = callOpenAI($message);
    if ($aiReply) {
        $response = ['reply' => $aiReply, 'source' => 'openai'];
        setCache('resp_' . md5($msgLower), $response);
        echo json_encode($response, JSON_UNESCAPED_UNICODE);
        exit;
    }

    // STEP 5: Graceful fallback
    $fallbacks = [
        'আমি আপনার প্রশ্ন বুঝতে পারছি! 😊 নির্দিষ্ট সেবা বা এলাকার নাম বললে আরও ভালো সাহায্য করতে পারব।',
        'দুঃখিত, এই মুহূর্তে উত্তর দিতে পারছি না। আমাদের সেবা পাতায় সরাসরি খুঁজুন অথবা কল করুন: 0171954698 📞',
        'আপনি কি কোনো নির্দিষ্ট সেবা (যেমন: প্লাম্বিং, ইলেকট্রিক) বা এলাকা (যেমন: উত্তরা, গুলশান) সম্পর্কে জানতে চান? 🤔',
    ];
    $response = ['reply' => $fallbacks[array_rand($fallbacks)], 'source' => 'fallback'];
    echo json_encode($response, JSON_UNESCAPED_UNICODE);

} catch (Exception $e) {
    logError('main', $e->getMessage(), ['trace' => $e->getTraceAsString()]);
    echo json_encode([
        'reply'  => 'দুঃখিত, একটি সমস্যা হয়েছে। দয়া করে আবার চেষ্টা করুন। 🔄',
        'source' => 'error'
    ], JSON_UNESCAPED_UNICODE);
}
?>