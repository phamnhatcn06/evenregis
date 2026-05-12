<?php

class MChangePassword extends MUsers
{
  public $repeatPassword;
  public $oldPassword;
  public $newPassword;

  public static function model($className = __CLASS__)
  {
    return parent::model($className);
  }

  public function rules()
  {
    return array(
      array('password,newPassword, repeatPassword', 'required'),
      array('newPassword', 'compare', 'compareAttribute' => 'repeatPassword'),
      array('newPassword', 'length', 'max' => 255),
    );
  }

  public function attributeLabels()
  {
    return array(
      'oldPassword' => Yii::t('app','Mật khẩu cũ'),
      'newPassword' => Yii::t('app', 'New Password'),
      'repeatPassword' => Yii::t('app', 'Repeat Password'),
    );
  }
}
