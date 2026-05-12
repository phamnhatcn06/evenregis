<?php

Yii::import('zii.widgets.CMenu', true);

class ActiveMenu extends CMenu {

    public function init() {
        $criteria = new CDbCriteria;
        $criteria->condition = 'published=:idpub AND menu_controller=:menu';
        $criteria->params = array(':idpub' => 1, ':menu' => '#');
        
        $currentMenu = Yii::app()->getBaseUrl(true) . $_SERVER['REQUEST_URI'];
        
        $output = '<ul id="leftMenus">';
        foreach ($this->items as $item) {
            $url = empty($item['menu']->link) ? 'javascript:' : $item['menu']->link;
            $childActive = false;
            $child = '';
            if (isset($item['submenu'])) {
                $children = '';
                foreach ($item['submenu'] as $submenu){
                    $childClass = '';
                    if($currentMenu == $submenu->link){
                        $childActive = true;
                        $childClass = 'active';
                    }
                    $children .= "<li class='left-submenu " . $childClass . "'><a href='" . $submenu->link . "'><span class='ico-item submenu'></span>" . $submenu->title . "</a></li>";
                }
                $child .= "<ul class='submenus " . ($childActive ? 'active' : 'hide') . "'>";
                $child .= $children;
                $child .= "</ul>";
            }
            $output .= "<li class='left-menu'><a href='" . $url . "'><span class='ico-item'></span>" . $item['menu']->title . "</a>";
            $output .= $child;
            $output .= "</li>";
        }
        $output .= "</ul>";
        
        echo $output;
        
        parent::init();
    }

    public function relations() {
        return array(
            'parent' => array(self::BELONGS_TO, 'Menu', 'parent_id'),
            'children' => array(self::HAS_MANY, 'Menu', 'parent_id'),
        );
    }

}