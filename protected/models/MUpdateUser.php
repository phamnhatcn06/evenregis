<?php

class MUpdateUser extends MUsers
{
	public static function model($className=__CLASS__) {
		return parent::model($className);
	}
    
    public function rules() {
		return array(
			array('role_id, username, password,  email, name, gender', 'required'),
                        array('email','email'),
                        array('username, email','unique'),
                        array('status','safe'),
			array('email, name', 'length', 'max'=>255),
			array('status', 'default', 'setOnEmpty' => true, 'value' => null),
		);
	}
}