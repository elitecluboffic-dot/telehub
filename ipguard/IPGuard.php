<?php

/**
 * IPGuard - VPN/Proxy Blocker + IP & Country Tracker + Bot Allowlist
 * Pakai VPNAPI.io Detection API
 *
 * Cara pakai (di index.php, SETELAH require config.php):
 *   require __DIR__ . '/config.php';
 *   require __DIR__ . '/ipguard/IPGuard.php';
 *   $guard = new IPGuard();
 *   $guard->protect();
 *
 * Konstanta yang harus di-define di config.php:
 *   define('VPNAPI_API_KEY',      'isi_api_key_kamu');
 *   define('VPNAPI_BLOCK_MESSAGE', 'Akses diblokir: VPN/Proxy terdeteksi.');
 *   define('VPNAPI_LOG_FILE',      __DIR__ . '/ipguard/visitor_log.json');
 *   define('VPNAPI_BLOCK_VPN',     true);
 *   define('VPNAPI_BLOCK_PROXY',   true);
 *   define('VPNAPI_BLOCK_TOR',     true);
 *   define('VPNAPI_BLOCK_RELAY',   true);  // iCloud Private Relay, dsb
 *
 * STRATEGI BOT ALLOWLIST (2 lapis):
 *   Lapis 1 — Bot ternama (Google, Bing, dst): diverifikasi KETAT pakai
 *             reverse DNS + forward-confirm, supaya User-Agent palsu
 *             ("saya Googlebot" padahal bukan) tetap ketahuan & ditolak.
 *   Lapis 2 — Bot/crawler lain yang TIDAK dikenal namanya: tetap diizinkan
 *             SELAMA User-Agent jelas menyatakan diri sebagai bot/crawler/
 *             spider DAN hasil cek VPNAPI bersih (bukan vpn/proxy/tor).
 *             Jadi bot kecil/legit (SEO tools, link-preview generator,
 *             dsb) tetap bisa masuk, tapi kalau botnya jalan di atas VPN/
 *             proxy/Tor (yang sering dipakai scraper jahat), tetap diblok.
 */

class IPGuard
{
    // Base endpoint VPNAPI.io
    private const API_BASE = 'https://vpnapi.io/api/';

    // ──────────────────────────────────────────────
    // BOT TERNAMA — diverifikasi ketat via reverse DNS
    // key   = potongan string yang dicari di User-Agent (lowercase)
    // value = daftar domain resmi yang valid buat reverse DNS match
    // ──────────────────────────────────────────────
    private const VERIFIED_BOTS = [
        // ── Search engines ──
        'googlebot'            => ['.googlebot.com', '.google.com'],
        'google-inspectiontool' => ['.googlebot.com', '.google.com'],
        'googleother'          => ['.googlebot.com', '.google.com'],
        'storebot-google'      => ['.googlebot.com', '.google.com'],
        'google-extended'      => ['.googlebot.com', '.google.com'],
        'adsbot-google'        => ['.googlebot.com', '.google.com'],
        'mediapartners-google' => ['.googlebot.com', '.google.com'],
        'apis-google'          => ['.googlebot.com', '.google.com'],
        'bingbot'              => ['.search.msn.com'],
        'bingpreview'          => ['.search.msn.com'],
        'msnbot'               => ['.search.msn.com'],
        'slurp'                => ['.crawl.yahoo.net'],
        'duckduckbot'          => ['.duckduckgo.com'],
        'baiduspider'          => ['.baidu.com', '.baidu.jp'],
        'yandexbot'            => ['.yandex.ru', '.yandex.com', '.yandex.net'],
        'yandeximages'         => ['.yandex.ru', '.yandex.com', '.yandex.net'],
        'sogou'                => ['.sogou.com'],
        'exabot'               => ['.exabot.com'],
        'naverbot'             => ['.naver.com', '.naver.jp'],
        'seznambot'            => ['.seznam.cz'],
        'coccocbot'            => ['.coccoc.com'],

        // ── Social-media link preview / crawler ──
        'facebookexternalhit'  => ['.fbsv.net', '.facebook.com'],
        'facebookcatalog'      => ['.fbsv.net', '.facebook.com'],
        'twitterbot'           => ['.twttr.com'],
        'linkedinbot'          => ['.linkedin.com'],
        'pinterest'            => ['.pinterest.com'],
        'whatsapp'             => [], // tidak punya rDNS publik konsisten, izinkan via UA saja
        'telegrambot'          => [], // sama seperti whatsapp
        'discordbot'           => [], // sama seperti whatsapp
        'slackbot'             => ['.slack.com'],
        'redditbot'            => ['.reddit.com'],
        'skypeuripreview'      => [],

        // ── SEO / monitoring tools ──
        'ahrefsbot'            => ['.ahrefs.com'],
        'semrushbot'           => ['.semrush.com'],
        'mj12bot'              => ['.majestic12.co.uk', '.mj12bot.com'],
        'dotbot'               => ['.opensiteexplorer.org', '.moz.com'],
        'screaming frog'       => [],
        'uptimerobot'          => ['.uptimerobot.com'],
        'pingdom'              => ['.pingdom.com'],

        // ── Apple / others ──
        'applebot'             => ['.applebot.apple.com'],
        'ia_archiver'          => ['.archive.org'],     // Alexa/Wayback
        'archive.org_bot'      => ['.archive.org'],
    ];

    // ──────────────────────────────────────────────
    // GENERIC BOT PATTERN — buat lapis 2 (bot tak dikenal namanya)
    // Kalau User-Agent mengandung salah satu kata ini, dianggap "klaim
    // sebagai bot", lalu tetap dicek bersih/tidaknya via VPNAPI.
    // ──────────────────────────────────────────────
    private const GENERIC_BOT_PATTERNS = [
        'bot', 'crawler', 'crawl', 'spider', 'slurp', 'fetcher',
        'scraper', 'indexer', 'archiver', 'monitor', 'preview',
        'validator', 'checker', 'scan',
    ];

    public function __construct()
    {
        if (!defined('VPNAPI_API_KEY')) {
            throw new Exception('IPGuard: konstanta VPNAPI_API_KEY belum di-define di config.php');
        }
        if (!defined('VPNAPI_LOG_FILE')) {
            throw new Exception('IPGuard: konstanta VPNAPI_LOG_FILE belum di-define di config.php');
        }
        if (!defined('VPNAPI_BLOCK_MESSAGE')) {
            throw new Exception('IPGuard: konstanta VPNAPI_BLOCK_MESSAGE belum di-define di config.php');
        }
    }

    // ──────────────────────────────────────────────
    // PROTECT — panggil ini di paling atas index.php
    // ──────────────────────────────────────────────
    public function protect(): void
    {
        $ip = $this->getVisitorIp();
        $ua = strtolower($_SERVER['HTTP_USER_AGENT'] ?? '');

        // ── Lapis 1: bot ternama, diverifikasi ketat via rDNS ──
        if ($this->isKnownVerifiedBot($ip, $ua)) {
            $this->logVisitor($ip, $this->emptySecurityData(), false, true, 'verified_known');
            return;
        }

        // Untuk lapis 2, kita tetap butuh hasil cek VPNAPI dulu
        // (supaya bot generic baru lolos kalau IP-nya beneran bersih)
        $data = $this->checkIp($ip);

        // Kalau API gagal/error, jangan block (fail-open)
        if ($data === null) {
            return;
        }

        $security = $data['security'] ?? [];

        $isVpn   = !empty($security['vpn']);
        $isProxy = !empty($security['proxy']);
        $isTor   = !empty($security['tor']);
        $isRelay = !empty($security['relay']);
        $isClean = !$isVpn && !$isProxy && !$isTor && !$isRelay;

        // ── Lapis 2: bot generik tak dikenal, lolos kalau IP-nya bersih ──
        if ($isClean && $this->looksLikeGenericBot($ua)) {
            $this->logVisitor($ip, $data, false, true, 'generic_clean');
            return;
        }

        $shouldBlock = (
            ($isVpn   && (defined('VPNAPI_BLOCK_VPN')   ? VPNAPI_BLOCK_VPN   : true)) ||
            ($isProxy && (defined('VPNAPI_BLOCK_PROXY') ? VPNAPI_BLOCK_PROXY : true)) ||
            ($isTor   && (defined('VPNAPI_BLOCK_TOR')   ? VPNAPI_BLOCK_TOR   : true)) ||
            ($isRelay && (defined('VPNAPI_BLOCK_RELAY') ? VPNAPI_BLOCK_RELAY : false))
        );

        $isAnyBad = $isVpn || $isProxy || $isTor || $isRelay;

        // Log semua pengunjung (manusia maupun bot generic yang ke-block)
        $this->logVisitor($ip, $data, $isAnyBad, false, null);

        if ($shouldBlock) {
            http_response_code(403);
            exit(VPNAPI_BLOCK_MESSAGE);
        }
    }

    // ──────────────────────────────────────────────
    // LAPIS 1 — cek bot ternama + verifikasi reverse DNS
    // ──────────────────────────────────────────────
    private function isKnownVerifiedBot(string $ip, string $ua): bool
    {
        if ($ua === '') {
            return false;
        }

        foreach (self::VERIFIED_BOTS as $botName => $validDomains) {
            if (strpos($ua, $botName) === false) {
                continue;
            }

            // Bot tanpa rDNS resmi publik (WhatsApp, Telegram, Discord, dst)
            // → cukup percaya User-Agent, risiko rendah karena cuma dipakai
            //   untuk link-preview, bukan akses data sensitif.
            if (empty($validDomains)) {
                return true;
            }

            $host = @gethostbyaddr($ip);
            if ($host === false || $host === $ip) {
                continue; // rDNS gagal → jangan auto-percaya, lanjut cek normal
            }
            $host = strtolower($host);

            foreach ($validDomains as $domain) {
                if (str_ends_with($host, $domain)) {
                    // Forward-confirm: domain hasil rDNS harus resolve balik ke IP yang sama
                    $forward = @gethostbynamel($host);
                    if (is_array($forward) && in_array($ip, $forward, true)) {
                        return true;
                    }
                }
            }
        }

        return false;
    }

    // ──────────────────────────────────────────────
    // LAPIS 2 — cek apakah UA "mengklaim" diri sebagai bot
    // (dipakai HANYA setelah IP terbukti bersih dari VPN/proxy/Tor)
    // ──────────────────────────────────────────────
    private function looksLikeGenericBot(string $ua): bool
    {
        if ($ua === '') {
            return false;
        }
        foreach (self::GENERIC_BOT_PATTERNS as $pattern) {
            if (strpos($ua, $pattern) !== false) {
                return true;
            }
        }
        return false;
    }

    private function emptySecurityData(): array
    {
        return [
            'security' => ['vpn' => false, 'proxy' => false, 'tor' => false, 'relay' => false],
            'location' => [],
            'network'  => [],
        ];
    }

    // ──────────────────────────────────────────────
    // CEK IP KE VPNAPI.IO
    // ──────────────────────────────────────────────
    public function checkIp(string $ip): ?array
    {
        $url = self::API_BASE . urlencode($ip) . '?key=' . urlencode(VPNAPI_API_KEY);

        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT        => 5,
            CURLOPT_CONNECTTIMEOUT => 3,
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_USERAGENT      => 'IPGuard/2.0',
        ]);

        $response = curl_exec($ch);
        $error    = curl_error($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        // Debug log
        $this->debugLog([
            'time'      => date('Y-m-d H:i:s'),
            'ip'        => $ip,
            'curl_err'  => $error ?: null,
            'http_code' => $httpCode,
            'response'  => $response,
        ]);

        if ($error || !$response) {
            return null;
        }

        // VPNAPI.io balikin 200 kalau sukses
        if ($httpCode !== 200) {
            return null;
        }

        $json = json_decode($response, true);

        // Validasi struktur response VPNAPI.io
        if (!is_array($json) || !isset($json['security'])) {
            return null;
        }

        return $json;
    }

    // ──────────────────────────────────────────────
    // AMBIL IP ASLI PENGUNJUNG
    // ──────────────────────────────────────────────
    private function getVisitorIp(): string
    {
        $headers = [
            'HTTP_CF_CONNECTING_IP', // Cloudflare
            'HTTP_X_FORWARDED_FOR',
            'HTTP_CLIENT_IP',
            'REMOTE_ADDR',
        ];

        foreach ($headers as $header) {
            if (!empty($_SERVER[$header])) {
                // X-Forwarded-For bisa berisi beberapa IP dipisah koma
                $ipList = explode(',', $_SERVER[$header]);
                $ip     = trim($ipList[0]);
                if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
                    return $ip;
                }
            }
        }

        // Fallback ke REMOTE_ADDR meski private
        return $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
    }

    // ──────────────────────────────────────────────
    // LOG PENGUNJUNG
    // Response VPNAPI.io contoh:
    // {
    //   "ip": "1.2.3.4",
    //   "security": { "vpn": false, "proxy": false, "tor": false, "relay": false },
    //   "location": { "country_code": "ID", "country_name": "Indonesia", "city": "Jakarta", ... },
    //   "network": { "autonomous_system_organization": "PT Telkom", ... }
    // }
    // ──────────────────────────────────────────────
    private function logVisitor(string $ip, array $data, bool $isBad, bool $isBot = false, ?string $botType = null): void
    {
        $logFile  = VPNAPI_LOG_FILE;
        $location = $data['location'] ?? [];
        $security = $data['security'] ?? [];
        $network  = $data['network']  ?? [];

        $entry = [
            'time'        => date('Y-m-d H:i:s'),
            'ip'          => $ip,
            'country'     => $location['country_code']  ?? 'UNKNOWN',
            'country_name'=> $location['country_name']  ?? 'UNKNOWN',
            'city'        => $location['city']           ?? 'UNKNOWN',
            'isp'         => $network['autonomous_system_organization'] ?? 'UNKNOWN',
            'vpn'         => !empty($security['vpn']),
            'proxy'       => !empty($security['proxy']),
            'tor'         => !empty($security['tor']),
            'relay'       => !empty($security['relay']),
            'blocked'     => $isBad,
            'bot'         => $isBot,
            'bot_type'    => $botType, // 'verified_known' | 'generic_clean' | null
            'user_agent'  => $_SERVER['HTTP_USER_AGENT'] ?? '',
        ];

        $logs = [];
        if (file_exists($logFile)) {
            $decoded = json_decode(file_get_contents($logFile), true);
            if (is_array($decoded)) {
                $logs = $decoded;
            }
        }

        $logs[] = $entry;

        // Max 5000 entri
        if (count($logs) > 5000) {
            $logs = array_slice($logs, -5000);
        }

        file_put_contents($logFile, json_encode($logs, JSON_PRETTY_PRINT));
    }

    // ──────────────────────────────────────────────
    // DEBUG LOG
    // ──────────────────────────────────────────────
    private function debugLog(array $entry): void
    {
        $debugFile = __DIR__ . '/debug_log.json';
        $logs = [];

        if (file_exists($debugFile)) {
            $decoded = json_decode(file_get_contents($debugFile), true);
            if (is_array($decoded)) {
                $logs = $decoded;
            }
        }

        $logs[] = $entry;

        // Max 50 entri debug
        if (count($logs) > 50) {
            $logs = array_slice($logs, -50);
        }

        file_put_contents($debugFile, json_encode($logs, JSON_PRETTY_PRINT));
    }

    // ──────────────────────────────────────────────
    // HELPER — ambil semua log
    // ──────────────────────────────────────────────
    public function getLogs(): array
    {
        $logFile = VPNAPI_LOG_FILE;
        if (!file_exists($logFile)) {
            return [];
        }
        $decoded = json_decode(file_get_contents($logFile), true);
        return is_array($decoded) ? $decoded : [];
    }

    // ──────────────────────────────────────────────
    // HELPER — ambil log yang diblokir saja
    // ──────────────────────────────────────────────
    public function getBlockedLogs(): array
    {
        return array_values(array_filter($this->getLogs(), fn($e) => !empty($e['blocked'])));
    }

    // ──────────────────────────────────────────────
    // HELPER — ambil log bot saja
    // ──────────────────────────────────────────────
    public function getBotLogs(): array
    {
        return array_values(array_filter($this->getLogs(), fn($e) => !empty($e['bot'])));
    }

    // ──────────────────────────────────────────────
    // HELPER — statistik singkat
    // ──────────────────────────────────────────────
    public function getStats(): array
    {
        $logs    = $this->getLogs();
        $total   = count($logs);
        $blocked = count(array_filter($logs, fn($e) => !empty($e['blocked'])));
        $bots    = count(array_filter($logs, fn($e) => !empty($e['bot'])));

        $countries = [];
        foreach ($logs as $e) {
            $c = $e['country'] ?? 'UNKNOWN';
            $countries[$c] = ($countries[$c] ?? 0) + 1;
        }
        arsort($countries);

        return [
            'total'     => $total,
            'blocked'   => $blocked,
            'allowed'   => $total - $blocked,
            'bots'      => $bots,
            'countries' => $countries,
        ];
    }
}
