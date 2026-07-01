<?php
namespace App\Core;

class GoogleOAuth
{
    private static function clientId(): string
    {
        return $_ENV['GOOGLE_CLIENT_ID'] ?? '';
    }

    private static function clientSecret(): string
    {
        return $_ENV['GOOGLE_CLIENT_SECRET'] ?? '';
    }

    public static function authUrl(string $redirectUri, string $state = ''): string
    {
        $params = http_build_query([
            'client_id'     => self::clientId(),
            'redirect_uri'  => $redirectUri,
            'response_type' => 'code',
            'scope'         => 'openid email profile',
            'access_type'   => 'online',
            'prompt'        => 'select_account',
            'state'         => $state ?: bin2hex(random_bytes(16)),
        ]);
        return "https://accounts.google.com/o/oauth2/v2/auth?{$params}";
    }

    public static function exchangeCode(string $code, string $redirectUri): ?array
    {
        $response = self::post('https://oauth2.googleapis.com/token', [
            'code'          => $code,
            'client_id'     => self::clientId(),
            'client_secret' => self::clientSecret(),
            'redirect_uri'  => $redirectUri,
            'grant_type'    => 'authorization_code',
        ]);

        return $response['access_token'] ?? null ? $response : null;
    }

    public static function getProfile(string $accessToken): ?array
    {
        $ch = curl_init('https://www.googleapis.com/oauth2/v3/userinfo');
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER     => ["Authorization: Bearer {$accessToken}"],
            CURLOPT_TIMEOUT        => 10,
        ]);
        $body = curl_exec($ch);
        curl_close($ch);

        $data = json_decode($body, true);
        if (empty($data['sub'])) return null;

        return [
            'google_id' => $data['sub'],
            'email'     => $data['email'] ?? '',
            'name'      => $data['name'] ?? '',
            'avatar'    => $data['picture'] ?? '',
        ];
    }

    private static function post(string $url, array $data): array
    {
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => http_build_query($data),
            CURLOPT_HTTPHEADER     => ['Content-Type: application/x-www-form-urlencoded'],
            CURLOPT_TIMEOUT        => 15,
        ]);
        $body = curl_exec($ch);
        curl_close($ch);
        return json_decode($body, true) ?? [];
    }
}
