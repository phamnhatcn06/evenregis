<?php
/**
 * Simple JWT decode library for Yii 1.x
 */
class JWT
{
    /**
     * Decode a JWT token
     * @param string $token
     * @param string $secret
     * @param string $algorithm
     * @return object|false
     */
    public static function decode($token, $secret, $algorithm = 'HS256')
    {
        $parts = explode('.', $token);
        if (count($parts) !== 3) {
            return false;
        }

        list($headerB64, $payloadB64, $signatureB64) = $parts;

        $header = json_decode(self::base64UrlDecode($headerB64));
        $payload = json_decode(self::base64UrlDecode($payloadB64));
        $signature = self::base64UrlDecode($signatureB64);

        if (!$header || !$payload) {
            return false;
        }

        // Verify signature
        $expectedSignature = self::sign($headerB64 . '.' . $payloadB64, $secret, $algorithm);
        if (!self::constantTimeEquals($signature, $expectedSignature)) {
            return false;
        }

        // Check expiration
        if (isset($payload->exp) && $payload->exp < time()) {
            return false;
        }

        return $payload;
    }

    /**
     * Create signature
     */
    private static function sign($data, $secret, $algorithm)
    {
        $algorithms = array(
            'HS256' => 'sha256',
            'HS384' => 'sha384',
            'HS512' => 'sha512',
        );

        if (!isset($algorithms[$algorithm])) {
            return false;
        }

        return hash_hmac($algorithms[$algorithm], $data, $secret, true);
    }

    /**
     * Base64 URL decode
     */
    private static function base64UrlDecode($data)
    {
        $remainder = strlen($data) % 4;
        if ($remainder) {
            $data .= str_repeat('=', 4 - $remainder);
        }
        return base64_decode(strtr($data, '-_', '+/'));
    }

    /**
     * Constant time string comparison
     */
    private static function constantTimeEquals($a, $b)
    {
        if (function_exists('hash_equals')) {
            return hash_equals($a, $b);
        }

        if (strlen($a) !== strlen($b)) {
            return false;
        }

        $result = 0;
        for ($i = 0; $i < strlen($a); $i++) {
            $result |= ord($a[$i]) ^ ord($b[$i]);
        }
        return $result === 0;
    }
}
