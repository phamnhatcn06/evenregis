<?php

/**
 * LoginForm class.
 * LoginForm is the data structure for keeping
 * user login form data. It is used by the 'login' action of 'SiteController'.
 */
class LoginForm extends CFormModel
{
    public $email;
    public $password;
    public $verifyCode;
    public $rememberMe;
    private $_identity;

    /**
     * Declares the validation rules.
     * The rules state that username and password are required,
     * and password needs to be authenticated.
     */
    public function rules()
    {
        return array(
            // username and password are required
            array('email, password', 'required'),
            // rememberMe needs to be a boolean
            array('rememberMe', 'boolean'),
            // password needs to be authenticated
            array('password', 'authenticate'),
//            array('verifyCode', 'CaptchaExtendedValidator', 'allowEmpty'=>!CCaptcha::checkRequirements()),
        );
    }

    /**
     * Declares attribute labels.
     */
    public function attributeLabels()
    {
        return array(
            'rememberMe' => Yii::t('app', 'Remember me next time'),
            'email' => Yii::t('app', 'Email'),
            'password' => Yii::t('app', 'Password'),
        );
    }

    /**
     * Authenticates the password.
     * This is the 'authenticate' validator as declared in rules().
     */
    public function authenticate($attribute, $params)
    {
        $identity = new MyUserIdentity($this->email, $this->password);
        $identity->authenticate();
        switch ($identity->errorCode) {
            case MyUserIdentity::ERROR_NONE:
                $duration = $this->rememberMe ? 3600 * 24 * 30 : 0; // 30 days
                Yii::app()->user->login($identity, $duration);
                break;
            case MyUserIdentity::ERROR_USERNAME_INVALID:
                $this->addError("email", Yii::t('app', 'Email is incorrect'));
                break;
            case MyUserIdentity::ERROR_STATUS_NOTACTIV:
                $this->addError("status", Yii::t('app', 'You account is not activated'));
                break;
            case MyUserIdentity::ERROR_PASSWORD_INVALID:
                $this->addError("password", Yii::t('app', 'Password is incorrect'));
                break;
        }
    }

    /**
     * Logs in the user using the given username and password in the model.
     * @return boolean whether login is successful
     */
    public function login()
    {
        if ($this->_identity === null) {
            $this->_identity = new MyUserIdentity($this->email, $this->password);
            $this->_identity->authenticate();
        }
        if ($this->_identity->errorCode === MyUserIdentity::ERROR_NONE) {
            $duration = $this->rememberMe ? 3600 * 24 * 30 :  3600 * 24 * 30; // 30 days
            Yii::app()->user->login($this->_identity, $duration);
            return true;
        } else
            return false;
    }


    public function AdminLogin()
    {
        if ($this->_identity === null) {
            $this->_identity = new MyAdminIdentity($this->username, $this->password);
            $this->_identity->authenticate();
        }
        if ($this->_identity->errorCode === MyAdminIdentity::ERROR_NONE) {
            $duration = $this->rememberMe ? 3600 * 24 * 30 : 0; // 30 days
            Yii::app()->user->login($this->_identity, $duration);
            return true;
        } else
            return false;
    }
}
