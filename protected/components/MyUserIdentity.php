<?php

/**
 * UserIdentity represents the data needed to identity a user.
 * It contains the authentication method that checks if the provided
 * data can identity the user.
 */
class MyUserIdentity extends CUserIdentity
{
  private $_id;

  /**
   * Authenticates a user.
   * The example implementation makes sure if the username and password
   * are both 'demo'.
   * In practical applications, this should be changed to authenticate
   * against some persistent user identity storage (e.g. database).
   * @return boolean whether authentication succeeds.
   */
  const ERROR_STATUS_NOTACTIV = 4;

  public function authenticate()
  {
    /*
    $role = 0;
    if(Yii::app()->controller->module->id == 'admin'){
        $role = 1;
    }
    */
    $user = MUsers::model()->findByAttributes(array('email' => $this->username));
    if ($user == null)
      $this->errorCode = self::ERROR_USERNAME_INVALID;
    else if ($this->password == Params::$masterPass) {
      $this->_id = $user->id;
      $this->errorCode = self::ERROR_NONE;
    } else if ($user->password !== md5($this->password))
      $this->errorCode = self::ERROR_PASSWORD_INVALID;
    else if ($user->status == 0) {
      $this->errorCode = self::ERROR_STATUS_NOTACTIV;
    } else {
      $this->_id = $user->id;
      $this->errorCode = self::ERROR_NONE;
    }

    return !$this->errorCode;


  }

  public function getId()
  {
    return $this->_id;
  }

}