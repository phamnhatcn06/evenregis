<?php
/**
 * Admin Site Controller
 */
class SiteController extends CController
{
    public $layout = '//layouts/admin';

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
                'actions' => array('login', 'error'),
                'users' => array('*'),
            ),
            array('allow',
                'actions' => array('index', 'logout'),
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
                $this->redirect(array('index'));
            }
        }

        $this->render('login', array('model' => $model));
    }

    public function actionLogout(): void
    {
        Yii::app()->user->logout();
        $this->redirect(array('login'));
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
