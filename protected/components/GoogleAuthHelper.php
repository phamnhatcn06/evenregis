<?php

/**
 * GoogleAuthHelper - Xác thực Google ID Token (Google Sign-In) phía server.
 *
 * Luồng: trình duyệt nhận "credential" (ID token dạng JWT) từ Google, gửi lên
 * server; server gọi Google tokeninfo để xác minh chữ ký + aud + hết hạn, rồi
 * trả về thông tin tài khoản (sub, email, name, picture).
 *
 * Dùng cho cổng bình chọn Miss public — mỗi tài khoản Google = một danh tính.
 */
class GoogleAuthHelper
{
    const TOKENINFO_URL = 'https://oauth2.googleapis.com/tokeninfo';

    /**
     * Xác minh ID token Google.
     * @param string $idToken credential nhận từ Google Sign-In (phía client)
     * @return array|null thông tin tài khoản nếu hợp lệ, null nếu không hợp lệ
     *   array('sub' => ..., 'email' => ..., 'name' => ..., 'picture' => ...)
     */
    public static function verify($idToken)
    {
        if (empty($idToken)) {
            return null;
        }

        $clientId = self::getClientId();
        if (empty($clientId)) {
            Yii::log('GoogleAuthHelper: thiếu cấu hình client_id', CLogger::LEVEL_ERROR, 'google');
            return null;
        }

        $url = self::TOKENINFO_URL . '?id_token=' . urlencode($idToken);

        $ch = curl_init($url);
        curl_setopt_array($ch, array(
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 10,
            CURLOPT_CONNECTTIMEOUT => 5,
            CURLOPT_SSL_VERIFYPEER => true,
        ));
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $errno = curl_errno($ch);
        curl_close($ch);

        if ($errno || $httpCode !== 200) {
            Yii::log('GoogleAuthHelper: xác minh thất bại HTTP ' . $httpCode, CLogger::LEVEL_WARNING, 'google');
            return null;
        }

        $payload = json_decode($response, true);
        if (!is_array($payload)) {
            return null;
        }

        // aud phải trùng client_id của ứng dụng
        if (!isset($payload['aud']) || $payload['aud'] !== $clientId) {
            Yii::log('GoogleAuthHelper: aud không khớp client_id', CLogger::LEVEL_WARNING, 'google');
            return null;
        }

        // Token phải còn hạn
        if (isset($payload['exp']) && (int) $payload['exp'] < time()) {
            return null;
        }

        // Người phát hành hợp lệ
        $iss = isset($payload['iss']) ? $payload['iss'] : '';
        if ($iss !== 'accounts.google.com' && $iss !== 'https://accounts.google.com') {
            return null;
        }

        return array(
            'sub' => isset($payload['sub']) ? $payload['sub'] : null,
            'email' => isset($payload['email']) ? $payload['email'] : null,
            'name' => isset($payload['name']) ? $payload['name'] : '',
            'picture' => isset($payload['picture']) ? $payload['picture'] : '',
        );
    }

    /**
     * Lấy Google OAuth Client ID từ params.
     * @return string
     */
    public static function getClientId()
    {
        $cfg = Yii::app()->params['googleSignIn'];
        return isset($cfg['client_id']) ? $cfg['client_id'] : '';
    }
}
