<?php

Yii::import('application.models._base.BaseMUsers');

class MUsers extends BaseMUsers
{

    public $repeatPassword;
    public $verifyCode;
    public $captchaEnable = true;

    public static function model($className = __CLASS__)
    {
        return parent::model($className);
    }

    public static function label($n = 1)
    {
        return Yii::t('app', 'Users', $n);
    }

    public function rules()
    {
        return array(
            array('role_id, password, email, name, gender', 'required'),
            array('password', 'length', 'max' => 128, 'min' => 6, 'message' => Yii::t('app', 'Incorrect password (minimal length 6 symbols)')),
            array('email', 'email'),
            array('username, email', 'unique'),
            array('repeatPassword', 'compare', 'compareAttribute' => 'password', 'allowEmpty' => true),
            array('role_id, gender, status', 'numerical', 'integerOnly' => true),
            array('password_token, username, password, email, name', 'length', 'max' => 255),
            array(' password_token, status', 'default', 'setOnEmpty' => true, 'value' => null),
            array('access_mod,id, role_id, username, password, email, name, gender, status, created,image,info_file,date_upload_file,verifyCode,department_id,is_changePass_first,phoneNumber,linePhone,hotelManager,can_create_request,access_mkt_report', 'safe', 'on' => 'search'),
        );
    }

    public function attributeLabels()
    {
        return array(
            'id' => Yii::t('app', 'ID'),
            'role_id' => null,
            'username' => Yii::t('app', 'Username'),
            'password' => Yii::t('app', 'Password'),
            'repeatPassword' => Yii::t('app', 'Repeat Password'),
            'email' => Yii::t('app', 'Email'),
            'name' => Yii::t('app', 'Name'),
            'gender' => Yii::t('app', 'Gender'),
            'status' => Yii::t('app', 'Status'),
            'created' => Yii::t('app', 'Created'),
            'hotel_id' => Yii::t('app', 'Khách sạn'),
            'department_id' => Yii::t('app', 'Phòng ban'),
            'nhatuyendung_id' => Yii::t('app', 'Nhà tuyển dụng'),
            'can_create_report' => Yii::t('app', 'Có thể tạo báo cáo'),
            'can_create_request' => Yii::t('app', 'Có thể tạo kiến nghị'),
            'role' => null,
        );
    }

    public static function getAll()
    {
        return MUsers::model()->findAll();
    }

    public static function getByHotel($id)
    {
        return MUsers::model()->findAllByAttributes(array('hotel_id' => $id));
    }
}
