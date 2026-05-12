<?php

class UsersController extends FrontEndController
{
    public function actionIndex()
    {
        $this->render('index');
    }

    public function actionMessage()
    {
        $message = Yii::app()->request->getParam('message');
        $redirect = Yii::app()->request->getParam('redirect');
        $this->render('message', array(
            'message' => $message,
            'redirect' => $redirect,
        ));
    }

    public function actionLogin()
    {
        $this->layout = 'main';
        $this->isLoginPage = true;
        $this->bodyClass = 'account-pages';
        if (Yii::app()->user->isGuest) {
            $model = new LoginForm;
            $model->scenario = 'captchaRequired';
            if (isset($_POST['ajax']) && $_POST['ajax'] === 'login-form') {
                echo CActiveForm::validate($model);
                Yii::app()->end();
            }
            if (isset($_POST['LoginForm'])) {
                $model->attributes = $_POST['LoginForm'];
                if ($model->validate() && $model->login()) {
                    $cookie_name = "user";
                    setcookie($cookie_name, Yii::app()->user->id, time() + (3600 * 24 * 30), "/");
                    $this->redirect(array('/admin/users/profile'));
                }
            }
            $this->render('login', array('model' => $model));
        } else {
            $this->redirect(array('/admin/users/profile'));
        }
    }

    public
    function actionLostPassword()
    {
        require Yii::app()->basePath . '/extensions/PHPMailer-master/PHPMailerAutoload.php';
        $this->layout = 'main';
        $token = '';
        $message = '';
        $model = new MChangePassword();
        $token = Yii::app()->request->getParam('token');
        if ($token == '') {
            if (isset($_POST['MChangePassword'])) {
                $check = MUsers::model()->findByAttributes(array('email' => $_POST['MChangePassword']['email']));
                if ($check == NULL) {
                    $model->addError('email', 'Email không tồn tại trong hệ thống. Kiểm tra lại.');
                } else {
                    $genToken = md5($_POST['MChangePassword']['email']);
                    $check->password_token = $genToken;
                    $check->save(false);
                    $url = Params::$mainLink . '/quen-mat-khau?token=' . $genToken;
                    $mailTo = $_POST['MChangePassword']['email'];
                    $mailTitle = "Xác nhận thay đổi mật khẩu.";
                    $content = "<BODY bgColor=\"#ffffff\"><FONT face=\"Verdana\" size=\"2\">";
                    $content .= "Chào bạn.<br /><br />Chúng tôi đã nhận được yêu cầu lấy lại mật khẩu của bạn từ website kiểm soát chất lượng tập đoàn Mường Thanh.<br /><br />Tên đănh nhập của bạn là: " . $check->email . "<br/>";
                    $content .= "Để hoàn tất quá trình lấy lại mật khẩu vui lòng click vào đường dẫn sau:";
                    $content .= '<br/><br/>' . $url;
                    $content .= '<br/><br/>';
                    $content .= "Chúc bạn một ngày tốt lành.<br /></FONT></FONT></BODY>";
                    HelperBase::SendMail($mailTo, '', $mailTitle, $content);
                    $message = 'Chúng tôi đã gửi cho bạn email để lấy lại mật khẩu. Vui lòng kiểm tra email để hoàn tất quá trình.';
                    header("Location:" . $this->createUrl('/frontend/users/message') . '?message=' . urlencode($message) . '&redirect=' . urlencode('/dang-nhap'));
                    exit;
                }
            } else {
                $this->render('recovery', array(
                    'token' => $token,
                    'model' => $model,
                    'message' => $message
                ));
            }
        }

        if ($token != '') {
            $model = MChangePassword::model()->findByAttributes(array('password_token' => $token));
            if ($model == null) {
                $model->addError('email', 'Người dùng đã hoàn tất quá trình đổi mật khẩu trước. Vui lòng kiểm tra lại.');
            } else {
                if (isset($_POST['MChangePassword'])) {
                    if (isset($_POST['MChangePassword'])) {
                        $model->setAttributes($_POST['MChangePassword']);
                        $model->password = $_POST['MChangePassword']['password'];
                        $model->newPassword = $_POST['MChangePassword']['password'];
                        if ($model->validate()) {
                            $model->password = md5($model->password);
                            $model->password_token = md5($_POST['MChangePassword']['password']) . time();
                            $model->save(false, array('password', 'password_token'));
                            $message = 'Bạn đã đổi mật khẩu thành công.';
                            header("Location:" . $this->createUrl('/frontend/users/message') . '?message=' . urlencode($message) . '&redirect=' . urlencode('/dang-nhap'));
                            exit;
                        }
                    }
                }
            }

        }
        $this->render('recovery', array(
            'token' => $token,
            'model' => $model,
            'message' => $message
        ));
    }
}
