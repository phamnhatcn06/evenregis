<?php

class DefaultController extends FrontEndController
{
  public function actionIndex()
  {
    $this->render('index');
  }
}