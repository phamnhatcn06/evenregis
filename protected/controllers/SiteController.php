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
                // Fetch full user profile from SSO API
                $userProfile = AuthHandler::fetchUserProfile($ssoToken);

                // Debug: xem SSO profile trả về gì
                echo '<pre>SSO Profile: '; print_r($userProfile); echo '</pre>';
                echo '<pre>Session User: '; print_r(AuthHandler::getUser()); echo '</pre>';
                die();

                // Update session with profile data (property_code, property_id, etc.)
                AuthHandler::updateSessionWithProfile($userProfile);
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
