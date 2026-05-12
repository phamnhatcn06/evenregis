<?php

Yii::import('application.models._base.BaseMRoles');

class MRoles extends BaseMRoles
{
    public static function model($className = __CLASS__)
    {
        return parent::model($className);
    }

    public static function label($n = 1)
    {
        return Yii::t('app', 'Roles', $n);
    }

    public function rules()
    {
        return array(
            array('title,module_slug', 'required'),
            array('title, controllers', 'length', 'max' => 255),
            array('id, title, controllers,module_slug,is_hotel', 'safe'),
        );
    }
}