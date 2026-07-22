<?php

/**
 * This is the model base class for the table "talent_rounds".
 *
 * @property string $id
 * @property string $talent_show_id
 * @property string $name
 * @property string $round_type
 * @property integer $round_order
 * @property double $max_score
 * @property double $weight
 * @property string $start_time
 * @property string $end_time
 * @property string $note
 * @property string $created_at
 * @property string $updated_at
 * @property string $deleted_at
 *
 * @property TalentShows $talentShow
 */
abstract class BaseTalentRounds extends GxActiveRecord
{
    public static function model($className = __CLASS__)
    {
        return parent::model($className);
    }

    public function tableName()
    {
        return 'talent_rounds';
    }

    public static function label($n = 1)
    {
        return Yii::t('app', 'TalentRounds|TalentRounds', $n);
    }

    public static function representingColumn()
    {
        return 'name';
    }

    public function rules()
    {
        return array(
            array('talent_show_id, name, round_type', 'required'),
            array('round_order', 'numerical', 'integerOnly' => true),
            array('max_score, weight', 'numerical'),
            array('talent_show_id', 'length', 'max' => 20),
            array('name', 'length', 'max' => 255),
            array('round_type', 'length', 'max' => 20),
            array('start_time, end_time, note, created_at, updated_at, deleted_at', 'safe'),
            array('round_order, max_score, weight, start_time, end_time, note, created_at, updated_at, deleted_at', 'default', 'setOnEmpty' => true, 'value' => null),
            array('id, talent_show_id, name, round_type, round_order, max_score, weight, start_time, end_time, note, created_at, updated_at, deleted_at', 'safe', 'on' => 'search'),
        );
    }

    public function relations()
    {
        return array(
            'talentShow' => array(self::BELONGS_TO, 'TalentShows', 'talent_show_id'),
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
            'talent_show_id' => null,
            'name' => Yii::t('app', 'Name'),
            'round_type' => Yii::t('app', 'Round Type'),
            'round_order' => Yii::t('app', 'Round Order'),
            'max_score' => Yii::t('app', 'Max Score'),
            'weight' => Yii::t('app', 'Weight'),
            'start_time' => Yii::t('app', 'Start Time'),
            'end_time' => Yii::t('app', 'End Time'),
            'note' => Yii::t('app', 'Note'),
            'created_at' => Yii::t('app', 'Created At'),
            'updated_at' => Yii::t('app', 'Updated At'),
            'deleted_at' => Yii::t('app', 'Deleted At'),
            'talentShow' => null,
        );
    }

    public function search()
    {
        $criteria = new CDbCriteria;

        $criteria->compare('id', $this->id, true);
        $criteria->compare('talent_show_id', $this->talent_show_id);
        $criteria->compare('name', $this->name, true);
        $criteria->compare('round_type', $this->round_type, true);
        $criteria->compare('round_order', $this->round_order);
        $criteria->compare('max_score', $this->max_score);
        $criteria->compare('weight', $this->weight);
        $criteria->compare('start_time', $this->start_time, true);
        $criteria->compare('end_time', $this->end_time, true);
        $criteria->compare('note', $this->note, true);
        $criteria->compare('created_at', $this->created_at, true);
        $criteria->compare('updated_at', $this->updated_at, true);
        $criteria->compare('deleted_at', $this->deleted_at, true);

        return new CActiveDataProvider($this, array(
            'criteria' => $criteria,
        ));
    }
}
