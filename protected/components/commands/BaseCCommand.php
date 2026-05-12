<?php
/**
 * Created by PhpStorm.
 * User: NhatThao
 * Date: 11/25/2018
 * Time: 10:12 AM
 */

class BaseCCommand extends CConsoleCommand {

    protected $requestTimeStart = 0;

    public function init() {
        $requestTimeStart = time();
        parent::init();
    }
}

?>
