<?php
/**
 * Vanjski API-ji (Frankfurter – tečaj, Open-Meteo – vrijeme).
 * Pozivi su server-side, s file-cacheom i tihim fallbackom:
 * ako je API nedostupan, funkcije vraćaju null pa stranica radi normalno.
 */

function karmenta_api_get(string $url, string $cacheKey, int $ttl): ?array
{
    $cacheFile = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'karmenta_' . $cacheKey . '.json';

    // Svježi cache? Vrati ga.
    if (is_file($cacheFile) && (time() - filemtime($cacheFile)) < $ttl) {
        $cached = json_decode((string)file_get_contents($cacheFile), true);
        if (is_array($cached)) return $cached;
    }

    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT        => 12,
        CURLOPT_CONNECTTIMEOUT => 6,
        CURLOPT_USERAGENT      => 'Karmenta/1.0',
    ]);
    $body = curl_exec($ch);
    $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($body !== false && $code === 200) {
        $data = json_decode($body, true);
        if (is_array($data)) {
            @file_put_contents($cacheFile, $body);
            return $data;
        }
    }

    // API pao – pokušaj sa starim cacheom ako postoji.
    if (is_file($cacheFile)) {
        $stale = json_decode((string)file_get_contents($cacheFile), true);
        if (is_array($stale)) return $stale;
    }

    return null;
}

/** Tečaj 1 EUR -> tražene valute. Vraća npr. ['USD'=>1.16, ...] ili null. */
function get_exchange_rates(): ?array
{
    $data = karmenta_api_get(
        'https://api.frankfurter.dev/v1/latest?base=EUR&symbols=USD,GBP,CHF',
        'rates',
        3600 // 1 sat
    );
    return $data['rates'] ?? null;
}

/** Trenutno vrijeme u Zagrebu (vanjski API #2 – Open-Meteo) ili null. */
function get_zagreb_weather(): ?array
{
    $data = karmenta_api_get(
        'https://api.open-meteo.com/v1/forecast?latitude=45.81&longitude=15.98&current=temperature_2m,weather_code,wind_speed_10m&timezone=Europe%2FZagreb',
        'weather',
        900 // 15 min
    );
    if (!isset($data['current'])) return null;

    $c = $data['current'];
    [$icon, $desc] = weather_describe((int)($c['weather_code'] ?? -1));
    return [
        'temp' => round((float)($c['temperature_2m'] ?? 0)),
        'wind' => round((float)($c['wind_speed_10m'] ?? 0)),
        'icon' => $icon,
        'desc' => $desc,
    ];
}

/** WMO weather code -> [emoji, hrvatski opis]. */
function weather_describe(int $code): array
{
    $map = [
        0  => ['☀️', 'Vedro'],
        1  => ['🌤️', 'Pretežno vedro'],
        2  => ['⛅', 'Djelomično oblačno'],
        3  => ['☁️', 'Oblačno'],
        45 => ['🌫️', 'Magla'],
        48 => ['🌫️', 'Magla s injem'],
        51 => ['🌦️', 'Slaba rosulja'],
        53 => ['🌦️', 'Rosulja'],
        55 => ['🌧️', 'Jaka rosulja'],
        61 => ['🌦️', 'Slaba kiša'],
        63 => ['🌧️', 'Kiša'],
        65 => ['🌧️', 'Jaka kiša'],
        71 => ['🌨️', 'Slab snijeg'],
        73 => ['🌨️', 'Snijeg'],
        75 => ['❄️', 'Jak snijeg'],
        80 => ['🌦️', 'Pljuskovi'],
        81 => ['🌧️', 'Jaki pljuskovi'],
        82 => ['⛈️', 'Olujni pljuskovi'],
        95 => ['⛈️', 'Grmljavina'],
        96 => ['⛈️', 'Grmljavina s tučom'],
        99 => ['⛈️', 'Jaka grmljavina s tučom'],
    ];
    return $map[$code] ?? ['🌡️', 'Trenutno vrijeme'];
}
