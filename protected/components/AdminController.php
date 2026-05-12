<?php

class AdminController extends Controller
{

    //    public $layout = 'column1';
    public $_baseLanguageCode = 'en';
    public $_oneNewsInCategory = array(3);
    public $user;
    public $role;
    public $pageTitle = 'Admin website kiểm soát chất lượng';
    public $isLogin;
    public $isDefault;
    public $title;
    public $checkUser = 0;
    public $bodyClass = '';
    public $isLoginPage = false;
    public $config;
    public $currentController;
    public $currentControllerTitle;
    public $currentControllerId;
    public $event;

    public $Tabletitle;

    public function init()
    {
        parent::init();

        // Check SSO authentication
        if (!AuthHandler::isAuthenticated()) {
            Yii::app()->user->setFlash('error', 'Phiên đăng nhập đã hết hạn.');
            $this->redirect(array('/site/login'));
            return;
        }

        $this->user = AuthHandler::getUser();
    }

    public function filters()
    {
        return array(
            'accessControl',
            array('ext.booster.filters.BoosterFilter - delete')
        );
    }

    public function accessRules()
    {
        return array(
            array(
                'allow',
                'users' => array('*'),
                'expression' => 'AuthHandler::isAuthenticated()',
            ),

            array(
                'deny',
                'users' => array('*'),
            ),
        );
    }

    /**
     * Actions that all authenticated users can access
     */
    protected $publicActions = array('index', 'view', 'list', 'search', 'admin', 'addContent', 'removeContent');

    /**
     * Check permission before action
     * @param CAction $action
     * @return bool
     */
    public function beforeAction($action)
    {
        if (!parent::beforeAction($action)) {
            return false;
        }

        $actionId = strtolower($action->id);
        $controllerId = strtolower($this->id);

        // Skip permission check for public actions
        if (in_array($actionId, $this->publicActions)) {
            return true;
        }

        // Map action to CRUD operation
        $operation = $this->mapActionToOperation($actionId);

        // Check permission
        if ($operation && !PermissionHelper::can($controllerId, $operation)) {
            throw new CHttpException(403, 'Bạn không có quyền thực hiện thao tác này.');
        }

        return true;
    }

    /**
     * Map action name to CRUD operation
     * @param string $action
     * @return string|null
     */
    protected function mapActionToOperation($action)
    {
        $map = array(
            // Create
            'create' => 'create',
            'add' => 'create',
            'store' => 'create',
            // Read
            'index' => 'read',
            'view' => 'read',
            'list' => 'read',
            'admin' => 'read',
            'search' => 'read',
            // Update
            'update' => 'update',
            'edit' => 'update',
            'save' => 'update',
            // Delete
            'delete' => 'delete',
            'destroy' => 'delete',
            'remove' => 'delete',
        );

        return isset($map[$action]) ? $map[$action] : null;
    }

    public function dataTree()
    {
        $mUsers = $this->loadModel(Yii::app()->user->id, 'MUsers');
        $mRoles = $this->loadModel($mUsers->role_id, 'MRoles');
        $dataTree = array();
        $controllers = MControllers::model()->findAll('id >0 and menu = 1 order by sort');
        $contArr = explode(',', $mRoles->controllers);
        if (count($contArr) > 0) {
            foreach ($controllers as $row) {
                if ($row->parent_id == 1) {
                    $chilArr = array();
                    foreach ($controllers as $child) {
                        if ($child->parent_id == $row->id && in_array($child->id, $contArr)) {
                            $subChildArr = array();
                            foreach ($controllers as $subChild) {
                                if ($subChild->parent_id == $child->id && in_array($subChild->id, $contArr)) {
                                    $subChildArr[] = array('text' => CHtml::link('<i class="' . $subChild->icon . '"></i>&nbsp;'
                                        . Yii::t(
                                            'app',
                                            $subChild->title
                                        ), array($subChild->code)));
                                }
                            }
                            if (count($subChildArr) > 0) {
                                $chilArr[] = array(
                                    'text' => '<a href="javascript: void(0);"><i class="'
                                        . $child->icon
                                        . '"></i><span>' .
                                        $child->title . '</span> <span
class="menu-arrow"></span></a>',
                                    'children' => $subChildArr
                                );
                            } else {
                                $chilArr[] = array('text' => CHtml::link('<i class="'
                                    . $child->icon
                                    . '"></i>&nbsp;<span>' . Yii::t('app', $child->title) . '</span>', array($child->code .
                                    '/admin')));
                            }
                        }
                    }

                    if (count($chilArr) > 0) {
                        $children = array(
                            'text' => Yii::t('app', $row->title),
                            'children' => $chilArr
                        );
                        $dataTree[] = $children;
                    }
                }
            }
        }

        //var_dump($dataTree); die;

        return $dataTree;
    }

    protected function performAjaxValidation($model, $form)
    {
        if (isset($_POST['ajax']) && $_POST['ajax'] === $form) {
            echo CActiveForm::validate($model);
            Yii::app()->end();
        }
    }
}
