<?php

/**
 * Controller is the customized base controller class.
 * All controller classes for this application should extend from this base class.
 */
class Controller extends CController {

    /**
     * @var string the default layout for the controller view. Defaults to '//layouts/column1',
     * meaning using a single column layout. See 'protected/views/layouts/column1.php'.
     */
    public $layout = '//layouts/column1';

    /**
     * @var array context menu items. This property will be assigned to {@link CMenu::items}.
     */
    public $menu = array();

    /**
     * @var array the breadcrumbs of the current page. The value of this property will
     * be assigned to {@link CBreadcrumbs::links}. Please refer to {@link CBreadcrumbs::links}
     * for more details on how to specify this property.
     */
    public $breadcrumbs = array();

    public function init() {
        /*
          if(Yii::app()->session['languageCode'] == null){
          Yii::app()->language = 'en';
          }else{
          Yii::app()->language = Yii::app()->session['languageCode'];
          }
         */
    }

    public function actions() {
        return array(
            // captcha action renders the CAPTCHA image displayed on the contact page
            'captcha' => array(
                'class' => 'CCaptchaAction',
                'backColor' => 0xFFFFFF,
            ),
            // page action renders "static" pages stored under 'protected/views/site/pages'
            // They can be accessed via: index.php?r=site/page&view=FileName
            'page' => array(
                'class' => 'CViewAction',
            ),
        );
    }

    /**
     * Returns the data model based on the primary key or another attribute.
     * This method is designed to work with the values passed via GET.
     * If the data model is not found or there's a malformed key, an
     * HTTP exception will be raised.
     * #MethodTracker
     * This method is based on the gii generated method controller::loadModel, from version 1.1.7 (r3135). Changes:
     * <ul>
     * <li>Support to composite PK.</li>
     * <li>Support to use any attribute (column) name besides the PK.</li>
     * <li>Support to multiple attributes.</li>
     * <li>Automatically detects the PK column names.</li>
     * </ul>
     * @param mixed $key The key or keys of the model to be loaded.
     * If the key is a string or an integer, the method will use the tables' PK if
     * the PK has a single column. If the table has a composite PK and the key
     * has a separator (see below), the method will detect it and use it.
     * <pre>
     * $key = '12-27'; // PK values with separator for tables with composite PK.
     * </pre>
     * If $key is an array, it can be indexed by integers or by attribute (column)
     * names, as for {@link CActiveRecord::findByAttributes}.
     * If the array doesn't have attribute names, as below, the method will use
     * the table composite PK.
     * <pre>
     * $key = array(
     *   12,
     *   27,
     *   ...,
     * );
     * </pre>
     * If the array is indexed by attribute names, as below, the method will use
     * the attribute names to search for and load the model.
     * <pre>
     * $key = array(
     *   'model_id' => 44,
     * 	 ...,
     * );
     * </pre>
     * Warning: each attribute used should have an index on the database and the set of
     * attributes used should identify only one item on the database (the attributes being
     * ideally part of or multiple unique keys).
     * @param string $modelClass The model class name.
     * @return GxActiveRecord The loaded model.
     * @see GxActiveRecord::pkSeparator
     * @throws CHttpException if there's an invalid request (with code 400) or if the model is not found (with code 404).
     */
    public function loadModel($key, $modelClass) {

        // Get the static model.
        $staticModel = GxActiveRecord::model($modelClass);

        if (is_array($key)) {
            // The key is an array.
            // Check if there are column names indexing the values in the array.
            reset($key);
            if (key($key) === 0) {
                // There are no attribute names.
                // Check if there are multiple PK values. If there's only one, start again using only the value.
                if (count($key) === 1)
                    return $this->loadModel($key[0], $modelClass);

                // Now we will use the composite PK.
                // Check if the table has composite PK.
                $tablePk = $staticModel->getTableSchema()->primaryKey;
                if (!is_array($tablePk))
                    throw new CHttpException(400, Yii::t('giix', 'Your request is invalid.'));

                // Check if there are the correct number of keys.
                if (count($key) !== count($tablePk))
                    throw new CHttpException(400, Yii::t('giix', 'Your request is invalid.'));

                // Get an array of PK values indexed by the column names.
                $pk = $staticModel->fillPkColumnNames($key);

                // Then load the model.
                $model = $staticModel->findByPk($pk);
            } else {
                // There are attribute names.
                // Then we load the model now.
                $model = $staticModel->findByAttributes($key);
            }
        } else {
            // The key is not an array.
            // Check if the table has composite PK.
            $tablePk = $staticModel->getTableSchema()->primaryKey;
            if (is_array($tablePk)) {
                // The table has a composite PK.
                // The key must be a string to have a PK separator.
                if (!is_string($key))
                    throw new CHttpException(400, Yii::t('giix', 'Your request is invalid.'));

                // There must be a PK separator in the key.
                if (stripos($key, GxActiveRecord::$pkSeparator) === false)
                    throw new CHttpException(400, Yii::t('giix', 'Your request is invalid.'));

                // Generate an array, splitting by the separator.
                $keyValues = explode(GxActiveRecord::$pkSeparator, $key);

                // Start again using the array.
                return $this->loadModel($keyValues, $modelClass);
            } else {
                // The table has a single PK.
                // Then we load the model now.
                $model = $staticModel->findByPk($key);
            }
        }

        // Check if we have a model.
        if ($model === null)
            throw new CHttpException(404, Yii::t('giix', 'The requested page does not exist.'));
        return $model;
    }

    public function beforeAction($action) {
        if (parent::beforeAction($action)) {
            /* @var $script CClientScript */
            $script = Yii::app()->clientScript;
            /* @var $theme CTheme */
            $theme = Yii::app()->theme;
            return true;
        }
        return false;
    }

}
