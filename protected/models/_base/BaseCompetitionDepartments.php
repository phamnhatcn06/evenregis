<?php

/**
 * Base model for table "competition_departments".
 *
 * @property string $id
 * @property string $competition_id
 * @property string $department_code
 * @property string $created_at
 *
 * @property Competitions $competition
 */
abstract class BaseCompetitionDepartments extends GxActiveRecord
{
    public static function model($className = __CLASS__)
    {
        return parent::model($className);
    }

    public function tableName()
    {
        return 'competition_departments';
    }

    public static function label($n = 1)
    {
        return Yii::t('app', 'Phòng ban thi nghiệp vụ');
    }

    public static function representingColumn()
    {
        return 'department_code';
    }

    public function rules()
    {
        return array(
            array('competition_id, department_code', 'required'),
            array('competition_id', 'length', 'max' => 20),
            array('department_code', 'length', 'max' => 50),
            array('created_at', 'safe'),
            array('created_at', 'default', 'setOnEmpty' => true, 'value' => null),
            array('id, competition_id, department_code, created_at', 'safe', 'on' => 'search'),
        );
    }

    public function relations()
    {
        return array(
            'competition' => array(self::BELONGS_TO, 'Competitions', 'competition_id'),
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
            'competition_id' => Yii::t('app', 'Cuộc thi'),
            'department_code' => Yii::t('app', 'Mã phòng ban'),
            'created_at' => Yii::t('app', 'Ngày tạo'),
            'competition' => null,
        );
    }

    public function search()
    {
        $criteria = new CDbCriteria;

        $criteria->compare('id', $this->id, true);
        $criteria->compare('competition_id', $this->competition_id);
        $criteria->compare('department_code', $this->department_code, true);
        $criteria->compare('created_at', $this->created_at, true);

        return new CActiveDataProvider($this, array(
            'criteria' => $criteria,
        ));
    }
}
