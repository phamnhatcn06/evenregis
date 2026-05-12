<?php
/**
 * Frontend Site Controller
 */
class SiteController extends CController
{
    public $layout = '//layouts/frontend';

    public function filters(): array
    {
        return array(
            'accessControl',
        );
    }

    public function accessRules(): array
    {
        return array(
            array('allow',
                'actions' => array('index', 'login', 'register', 'error'),
                'users' => array('*'),
            ),
            array('allow',
                'actions' => array('logout', 'profile'),
                'users' => array('@'),
            ),
            array('deny',
                'users' => array('*'),
            ),
        );
    }

    public function actionIndex(): void
    {
        $this->render('index');
    }

    public function actionLogin(): void
    {
        $model = new LoginForm();

        if (isset($_POST['LoginForm'])) {
            $model->attributes = $_POST['LoginForm'];
            if ($model->validate() && $model->login()) {
                $this->redirect(Yii::app()->user->returnUrl);
            }
        }

        $this->render('login', array('model' => $model));
    }

    public function actionRegister(): void
    {
        $model = new User('register');

        if (isset($_POST['User'])) {
            $model->attributes = $_POST['User'];
            if ($model->save()) {
                Yii::app()->user->setFlash('success', 'Đăng ký thành công!');
                $this->redirect(array('login'));
            }
        }

        $this->render('register', array('model' => $model));
    }

    public function actionLogout(): void
    {
        Yii::app()->user->logout();
        $this->redirect(Yii::app()->homeUrl);
    }

    public function actionError(): void
    {
        if ($error = Yii::app()->errorHandler->error) {
            if (Yii::app()->request->isAjaxRequest) {
                echo $error['message'];
            } else {
                $this->render('error', $error);
            }
        }
    }
}
