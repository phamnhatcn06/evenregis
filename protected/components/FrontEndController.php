<?php

class FrontEndController extends Controller
{
  //public $specialMatch=null;
  public $config;
  public $nitification;
  public $user;
  public $count_notify;
  public $menu;
  public $frontTitle;
  public $hotNews;
  public $message = '';
  public $bodyClass = '';
  public $isLoginPage = false;

  public function init()
  {
    Yii::app()->theme = 'highadmin';
    $this->layout = 'main';
  }

  public function accessRules()
  {
    $listAllowUser = array(md5(time()));
    return array(
      array('allow',
        'actions' => array('adminLogin', 'login', 'message', 'register', 'comment', 'lostPassword', 'captcha', 'tin', 'getTemplate', 'toggle', 'updateField'),
        'users' => array('*'),
      ),
      array('allow',
        'actions' => array('profile', 'edit', 'changePassword', 'save', 'logout', 'comment', 'register'),
        'users' => array('@'),
      ),
      array('deny',
        'users' => array('*'),
      ),
    );
  }

  public function filters()
  {
    return array(
      array('ext.booster.filters.BoosterFilter - delete')
    );
  }

  public function setMetaTitle($title)
  {
    if (!empty($title) && !is_null($title)) {
      $this->frontendTitle = htmlspecialchars(Params::METADATA_TITLE_PREFIX . $title);
    }
  }

  public function setMetaDescription($description)
  {
    if (!empty($description) && !is_null($description)) {
      $this->metaDescription = $description;
    }
  }

  public function setDescription($description)
  {
    if (!empty($description) && !is_null($description)) {
      $this->description = $description;
    }
  }

  public function setMetaKeywords($keywords)
  {
    if (!empty($keywords) && !is_null($keywords)) {
      $this->metaKeywords = $keywords;
    }
  }

}

?>