<?php

Yii::import('application.models._base.BaseMControllers');

class MControllers extends BaseMControllers
{
    public static function model($className = __CLASS__)
    {
        return parent::model($className);
    }

    public static function label($n = 1)
    {
        return Yii::t('app', 'Controllers', $n);
    }

    public function rules()
    {
        return array(
            array('code, parent_id, title', 'required'),
            array('code', 'unique'),
            array('parent_id, sort, menu, permission', 'numerical', 'integerOnly' => true),
            array('code, title', 'length', 'max' => 255),
            array('sort, menu, permission,icon', 'default', 'setOnEmpty' => true, 'value' => null),
            array('id, code, parent_id, title, sort, menu, permission,icon', 'safe', 'on' => 'search'),
        );
    }
}