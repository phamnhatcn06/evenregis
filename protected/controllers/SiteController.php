<?php

/**
 * SiteController - Handle SSO authentication and common pages
 */
class SiteController extends Controller
{
    public $title = 'Đăng nhập';
    public $layout = false;

    public function filters()
    {
        return array(
            'accessControl',
        );
    }

    public function accessRules()
    {
        return array(
            array(
                'allow',
                'actions' => array('index', 'error', 'login', 'logout', 'debugToken', 'me', 'menuPermissions', 'debugPermissions'),
                'users' => array('*'),
            ),
            array(
                'deny',
                'users' => array('*'),
            ),
        );
    }

    /**
     * Homepage - handle SSO token if present
     * URL: /?sso_token=xxx
     */
    public function actionIndex()
    {
        $ssoToken = Yii::app()->request->getParam('sso_token');
        if ($ssoToken) {
            // Clear old session before processing new token
            AuthHandler::logout();
            $userData = AuthHandler::handleCallback($ssoToken);

            if (!$userData) {
                // Token không hợp lệ / hết hạn → về landing
                Yii::app()->user->setFlash('error', 'Token không hợp lệ hoặc đã hết hạn.');
                $this->redirect(array('/site/login'));
                return;
            }

            // Lưu quyền + profile vào SESSION (menu permissions sẽ được layout admin
            // tự sync xuống localStorage ở mỗi lần load trang).
            AuthHandler::fetchPermissions($ssoToken);
            $userProfile = AuthHandler::fetchUserProfile($ssoToken);
            AuthHandler::updateSessionWithProfile($userProfile);

            Yii::app()->user->setFlash('success', 'Đăng nhập thành công. Xin chào ' . $userData['full_name']);

            // Redirect PHÍA SERVER (302) → token biến mất khỏi URL ngay lập tức.
            $this->redirect(array('/site/index'));
            return;
        }

        // Đã đăng nhập (session còn hiệu lực) → hiển thị TRANG CHỦ.
        // Load trang → gọi Portal kiểm tra token; nếu hết hiệu lực thì về landing.
        if (AuthHandler::isAuthenticated()) {
            if (!AuthHandler::validateWithPortal()) {
                Yii::app()->user->setFlash('error', 'Phiên đăng nhập đã hết hạn.');
                $this->render('login');
                return;
            }
            $this->render('home', array(
                'user' => AuthHandler::getUser(),
                'hasAdminAccess' => PermissionHelper::hasAnyPermission(),
            ));
            return;
        }

        // Chưa đăng nhập: hiển thị trang landing công khai.
        // KHÔNG check token / KHÔNG ép đăng nhập ở bước này.
        // Token chỉ được kiểm tra khi người dùng bấm nút "Đăng nhập nội bộ"
        // và Portal redirect về kèm sso_token (xử lý ở đầu action này).
        $this->render('login');
    }

    /**
     * Login page - redirect to Portal SSO
     */
    public function actionLogin()
    {
        // Check for SSO token in URL (in case of direct access)
        $ssoToken = Yii::app()->request->getParam('sso_token');
        if ($ssoToken) {
            $this->redirect(array('/site/index', 'sso_token' => $ssoToken));
            return;
        }
        // Ưu tiên session hiện có: nếu còn hợp lệ thì vào thẳng trang chủ,
        // KHÔNG redirect ra Portal nữa (dùng lại token đã lưu trong session).
        if (AuthHandler::isAuthenticated() && AuthHandler::validateWithPortal()) {
            $this->redirect(array('/site/index'));
            return;
        }

        // Chưa có session hợp lệ → mới chuyển sang Portal SSO để lấy token.
        AuthHandler::redirectToPortal();
    }

    /**
     * Logout action
     */
    public function actionLogout()
    {
        AuthHandler::logout();
        Yii::app()->user->logout();
        Yii::app()->user->setFlash('info', 'Bạn đã đăng xuất thành công.');
        $this->redirect(array('/site/login'));
    }

    /**
     * Error page
     */
    public function actionError()
    {
        if ($error = Yii::app()->errorHandler->error) {
            if (Yii::app()->request->isAjaxRequest) {
                echo $error['message'];
            } else {
                $this->render('error', $error);
            }
        }
    }

    /**
     * Debug SSO token (temporary)
     */
    public function actionDebugToken()
    {
        error_reporting(E_ALL);
        ini_set('display_errors', 1);
        header('Content-Type: application/json');

        try {
            $token = Yii::app()->request->getParam('token');

            if (!$token) {
                echo CJSON::encode(array('error' => 'No token provided. Use POST or pass shorter token.'));
                Yii::app()->end();
            }

            // Decode without validation to see payload
            $debug = AuthHandler::debugToken($token);

            // Try to validate
            $params = require Yii::getPathOfAlias('application.config') . '/params.php';
            $secret = $params['portal']['jwt_secret'];

            require_once Yii::getPathOfAlias('application.extensions.jwt') . '/JWT.php';
            $payload = JWT::decode($token, $secret, 'HS256');

            echo CJSON::encode(array(
                'debug' => $debug,
                'jwt_secret_length' => strlen($secret),
                'validation_result' => $payload ? 'VALID' : 'INVALID',
                'payload' => $payload,
            ));
        } catch (Exception $e) {
            echo CJSON::encode(array(
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ));
        }
        Yii::app()->end();
    }

    /**
     * API endpoint to get current user info and permissions
     */
    public function actionMe()
    {
        header('Content-Type: application/json');

        if (!AuthHandler::isAuthenticated()) {
            echo CJSON::encode(array(
                'success' => false,
                'error' => array(
                    'code' => 'UNAUTHORIZED',
                    'message' => 'Chưa đăng nhập',
                ),
            ));
            Yii::app()->end();
        }

        $user = AuthHandler::getUser();
        $permissions = PermissionHelper::getParsedPermissions();

        echo CJSON::encode(array(
            'success' => true,
            'data' => array(
                'user' => $user,
                'permissions' => $permissions,
            ),
        ));
        Yii::app()->end();
    }

    /**
     * Debug permissions - xem raw response từ SSO API
     */
    public function actionDebugPermissions()
    {
        header('Content-Type: application/json');

        $session = Yii::app()->session;
        $token = isset($session['sso_token']) ? $session['sso_token'] : null;

        if (!$token) {
            echo CJSON::encode(array('error' => 'No token in session'));
            Yii::app()->end();
        }

        $params = require Yii::getPathOfAlias('application.config') . '/params.php';
        $url = rtrim($params['portal']['api_url'], '/') . $params['portal']['sso_permissions_endpoint'];

        $ch = curl_init($url);
        curl_setopt_array($ch, array(
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => array(
                'Authorization: Bearer ' . $token,
                'Accept: application/json',
            ),
            CURLOPT_TIMEOUT => 10,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => false,
        ));

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);

        $menuPermissions = AuthHandler::getMenuPermissions();
        $crudPermissions = AuthHandler::getPermissions();

        echo CJSON::encode(array(
            'api_url' => $url,
            'http_code' => $httpCode,
            'curl_error' => $error,
            'raw_response' => $response,
            'parsed_response' => json_decode($response, true),
            'session_menu_permissions' => $menuPermissions,
            'session_crud_permissions' => $crudPermissions,
        ));
        Yii::app()->end();
    }

    /**
     * API endpoint to get menu permissions for sidebar
     * Trả về permissions và token hash để client biết khi nào cần update
     */
    public function actionMenuPermissions()
    {
        header('Content-Type: application/json');

        if (!AuthHandler::isAuthenticated()) {
            echo CJSON::encode(array(
                'success' => false,
                'error' => array(
                    'code' => 'UNAUTHORIZED',
                    'message' => 'Chưa đăng nhập',
                ),
            ));
            Yii::app()->end();
        }

        $menuPermissions = AuthHandler::getMenuPermissions();
        $token = Yii::app()->session['sso_token'];
        $tokenHash = $token ? md5($token) : '';

        echo CJSON::encode(array(
            'success' => true,
            'data' => array(
                'permissions' => $menuPermissions,
                'token_hash' => $tokenHash,
            ),
        ));
        Yii::app()->end();
    }
}
