<?php

/**
 * This is the model base class for the table "beauty_rounds".
 *
 * @property string $id
 * @property string $contest_id
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
 * @property BeautyContests $contest
 * @property BeautyRoundResults[] $beautyRoundResults
 */
abstract class BaseBeautyRounds extends GxActiveRecord
{
    public static function model($className = __CLASS__)
    {
        return parent::model($className);
    }

    public function tableName()
    {
        return 'beauty_rounds';
    }

    public static function label($n = 1)
    {
        return Yii::t('app', 'BeautyRounds|BeautyRounds', $n);
    }

    public static function representingColumn()
    {
        return 'name';
    }

    public function rules()
    {
        return array(
            array('contest_id, name, round_type', 'required'),
            array('round_order', 'numerical', 'integerOnly' => true),
            array('max_score, weight', 'numerical'),
            array('contest_id', 'length', 'max' => 20),
            array('name', 'length', 'max' => 255),
            array('round_type', 'length', 'max' => 10),
            array('start_time, end_time, note, created_at, updated_at, deleted_at', 'safe'),
            array('round_order, max_score, weight, start_time, end_time, note, created_at, updated_at, deleted_at', 'default', 'setOnEmpty' => true, 'value' => null),
            array('id, contest_id, name, round_type, round_order, max_score, weight, start_time, end_time, note, created_at, updated_at, deleted_at', 'safe', 'on' => 'search'),
        );
    }

    public function relations()
    {
        return array(
            'contest' => array(self::BELONGS_TO, 'BeautyContests', 'contest_id'),
            'beautyRoundResults' => array(self::HAS_MANY, 'BeautyRoundResults', 'round_id'),
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
            'contest_id' => null,
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
            'contest' => null,
            'beautyRoundResults' => null,
        );
    }

    public function search()
    {
        $criteria = new CDbCriteria;

        $criteria->compare('id', $this->id, true);
        $criteria->compare('contest_id', $this->contest_id);
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
