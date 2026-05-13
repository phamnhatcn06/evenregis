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
                'actions' => array('index', 'error', 'login', 'logout'),
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
            if ($userData) {
                // Debug: test API call directly
                $url = 'https://api.portal.muongthanh.vn/api/sso/me';
                $ch = curl_init($url);
                curl_setopt_array($ch, array(
                    CURLOPT_RETURNTRANSFER => true,
                    CURLOPT_HTTPHEADER => array(
                        'Authorization: Bearer ' . $ssoToken,
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

                echo '<h3>Debug SSO API</h3>';
                echo '<p><strong>URL:</strong> ' . $url . '</p>';
                echo '<p><strong>HTTP Code:</strong> ' . $httpCode . '</p>';
                echo '<p><strong>Curl Error:</strong> ' . ($error ?: 'None') . '</p>';
                echo '<p><strong>Token (first 100 chars):</strong> ' . substr($ssoToken, 0, 100) . '...</p>';
                echo '<p><strong>Response:</strong></p><pre>' . htmlspecialchars($response) . '</pre>';
                die();
                // Render callback page to save profile to localStorage
                Yii::app()->user->setFlash('success', 'Đăng nhập thành công. Xin chào ' . $userData['full_name']);
                $this->render('callback', array(
                    'userProfile' => $userProfile,
                    'redirectUrl' => Yii::app()->createUrl('/admin/default/index'),
                ));
                return;
            } else {
                // Login failed
                Yii::app()->user->setFlash('error', 'Token không hợp lệ hoặc đã hết hạn.');
                $this->redirect(array('/site/login'));
                return;
            }
        }

        // Check if already authenticated
        if (AuthHandler::isAuthenticated()) {
            $this->redirect(array('/admin/default/index'));
            return;
        }

        // Redirect to login
        $this->redirect(array('/site/login'));
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
        // If already authenticated, go to dashboard
        if (AuthHandler::isAuthenticated()) {
            $this->redirect(array('/admin/default/index'));
            return;
        }
        // Render login page with Portal redirect button
        $this->render('login');
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
}
