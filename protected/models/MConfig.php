<?php

Yii::import('application.models._base.BaseMConfig');

class MConfig extends BaseMConfig
{
	public static function model($className=__CLASS__) {
		return parent::model($className);
	}
    
    public static function label($n = 1) {
		return Yii::t('app', 'Configs', $n);
	}
	public function rules()
	{
		return array(
			array('day_start_month_mod, count_now_cp', 'numerical', 'integerOnly' => true),
			array('hotline, title, facebook, google, youtube', 'length', 'max' => 255),
			array('logo, favicon, top_background, email_username, email_pass', 'length', 'max' => 500),
			array('tam_nhin, su_menh, gioi_thieu, lich_su, phat_trien, thuong_hieu, chan_thanh, cam_ket, can_bang, ton_trong, thich_ung, thong_nhat, chinh_sach_nld, van_hoa, iframe_gioithieu, iframe_lichsu, footer_description', 'safe'),
			array('hotline, title, logo, favicon, facebook, google, youtube, tam_nhin, su_menh, gioi_thieu, lich_su, phat_trien, thuong_hieu, chan_thanh, cam_ket, can_bang, ton_trong, thich_ung, thong_nhat, chinh_sach_nld, van_hoa, iframe_gioithieu, iframe_lichsu, top_background, footer_description, email_username, email_pass, day_start_month_mod, count_now_cp', 'default', 'setOnEmpty' => true, 'value' => null),
			array('id, hotline, title, logo, favicon, facebook, google, youtube, tam_nhin, su_menh, gioi_thieu, lich_su, phat_trien, thuong_hieu, chan_thanh, cam_ket, can_bang, ton_trong, thich_ung, thong_nhat, chinh_sach_nld, van_hoa, iframe_gioithieu, iframe_lichsu, top_background, footer_description, email_username, email_pass, day_start_month_mod, count_now_cp', 'safe', 'on' => 'search'),
		);
	}
}