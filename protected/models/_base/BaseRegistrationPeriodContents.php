<?php

/**
 * Base model for table "registration_period_contents".
 *
 * @property string $id
 * @property string $period_id
 * @property string $content_id
 * @property string $created_at
 *
 * @property RegistrationPeriods $period
 * @property Contents $content
 */
abstract class BaseRegistrationPeriodContents extends GxActiveRecord
{
    public static function model($className = __CLASS__)
    {
        return parent::model($className);
    }

    public function tableName()
    {
        return 'registration_period_contents';
    }

    public static function label($n = 1)
    {
        return Yii::t('app', 'Nội dung đợt đăng ký', $n);
    }

    public static function representingColumn()
    {
        return 'id';
    }

    public function rules()
    {
        return array(
            array('period_id, content_id', 'required'),
            array('period_id, content_id', 'numerical', 'integerOnly' => true),
            array('created_at', 'safe'),
            array('created_at', 'default', 'setOnEmpty' => true, 'value' => null),
            array('id, period_id, content_id, created_at', 'safe', 'on' => 'search'),
        );
    }

    public function relations()
    {
        return array(
            'period' => array(self::BELONGS_TO, 'RegistrationPeriods', 'period_id'),
            'content' => array(self::BELONGS_TO, 'Contents', 'content_id'),
        );
    }

    public function pivotModels()
    {
        return array();
    }

    public function attributeLabels()
    {
        return array(
            'id' => Yii::t('app', 'ID'),
            'period_id' => Yii::t('app', 'Đợt đăng ký'),
            'content_id' => Yii::t('app', 'Nội dung'),
            'created_at' => Yii::t('app', 'Ngày tạo'),
            'period' => null,
            'content' => null,
        );
    }

    public function search()
    {
        $criteria = new CDbCriteria;

        $criteria->compare('id', $this->id, true);
        $criteria->compare('period_id', $this->period_id, true);
        $criteria->compare('content_id', $this->content_id, true);
        $criteria->compare('created_at', $this->created_at, true);

        return new CActiveDataProvider($this, array(
            'criteria' => $criteria,
        ));
    }
}
