<?php

/**
 * IconHelper - Render SVG icons
 *
 * Usage:
 * IconHelper::render('view')
 * IconHelper::render('edit', 'icon-20')
 * IconHelper::btn('view', 'btn-soft-info')
 */
class IconHelper
{
    private static $_icons = array(
        'view' => '<svg class="{class}" width="{size}" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
            <path fill-rule="evenodd" clip-rule="evenodd" d="M15.1614 12.0531C15.1614 13.7991 13.7454 15.2141 11.9994 15.2141C10.2534 15.2141 8.83838 13.7991 8.83838 12.0531C8.83838 10.3061 10.2534 8.89111 11.9994 8.89111C13.7454 8.89111 15.1614 10.3061 15.1614 12.0531Z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
            <path fill-rule="evenodd" clip-rule="evenodd" d="M11.998 19.355C15.806 19.355 19.289 16.617 21.25 12.053C19.289 7.48898 15.806 4.75098 11.998 4.75098H12.002C8.194 4.75098 4.711 7.48898 2.75 12.053C4.711 16.617 8.194 19.355 12.002 19.355H11.998Z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
        </svg>',

        'update' => '<svg class="{class}" width="{size}" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
            <path d="M13.7476 20.4428H21.0002" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
            <path fill-rule="evenodd" clip-rule="evenodd" d="M12.78 3.79479C13.5557 2.86779 14.95 2.73186 15.8962 3.49173C15.9485 3.53296 17.6295 4.83879 17.6295 4.83879C18.669 5.46719 18.992 6.80311 18.3494 7.82259C18.3153 7.87718 8.81195 19.7645 8.81195 19.7645C8.49578 20.1589 8.01583 20.3918 7.50291 20.3973L3.86353 20.443L3.04353 16.9723C2.92866 16.4843 3.04353 15.9718 3.3597 15.5773L12.78 3.79479Z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
            <path d="M11.0215 6.00098L16.4737 10.1881" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
        </svg>',

        'delete' => '<svg class="{class}" width="{size}" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
            <path d="M19.3248 9.46826C19.3248 9.46826 18.7818 16.2033 18.4668 19.0403C18.3168 20.3953 17.4798 21.1893 16.1088 21.2143C13.4998 21.2613 10.8878 21.2643 8.27979 21.2093C6.96079 21.1823 6.13779 20.3783 5.99079 19.0473C5.67379 16.1853 5.13379 9.46826 5.13379 9.46826" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
            <path d="M20.708 6.23975H3.75" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
            <path d="M17.4406 6.23973C16.6556 6.23973 15.9796 5.68473 15.8256 4.91573L15.5826 3.69973C15.4326 3.13873 14.9246 2.75073 14.3456 2.75073H10.1126C9.53358 2.75073 9.02558 3.13873 8.87558 3.69973L8.63258 4.91573C8.47858 5.68473 7.80258 6.23973 7.01758 6.23973" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
        </svg>',

        'plus' => '<svg class="{class}" width="{size}" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
            <path d="M12 5V19" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
            <path d="M5 12H19" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
        </svg>',

        'search' => '<svg class="{class}" width="{size}" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
            <circle cx="11.7669" cy="11.7666" r="8.98856" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
            <path d="M18.0186 18.4851L21.5426 22" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
        </svg>',

        'download' => '<svg class="{class}" width="{size}" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
            <path d="M12 16V4" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
            <path d="M16 12L12 16L8 12" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
            <path d="M20 20H4" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
        </svg>',

        'upload' => '<svg class="{class}" width="{size}" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
            <path d="M12 8V20" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
            <path d="M8 12L12 8L16 12" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
            <path d="M20 4H4" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
        </svg>',

        'check' => '<svg class="{class}" width="{size}" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
            <path d="M5 13L9 17L19 7" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
        </svg>',

        'close' => '<svg class="{class}" width="{size}" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
            <path d="M18 6L6 18" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
            <path d="M6 6L18 18" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
        </svg>',

        'back' => '<svg class="{class}" width="{size}" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
            <path d="M15 19L8 12L15 5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
        </svg>',

        'print' => '<svg class="{class}" width="{size}" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
            <path d="M6 9V2H18V9" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
            <path d="M6 18H4C3.46957 18 2.96086 17.7893 2.58579 17.4142C2.21071 17.0391 2 16.5304 2 16V11C2 10.4696 2.21071 9.96086 2.58579 9.58579C2.96086 9.21071 3.46957 9 4 9H20C20.5304 9 21.0391 9.21071 21.4142 9.58579C21.7893 9.96086 22 10.4696 22 11V16C22 16.5304 21.7893 17.0391 21.4142 17.4142C21.0391 17.7893 20.5304 18 20 18H18" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
            <path d="M18 14H6V22H18V14Z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
        </svg>',

        'refresh' => '<svg class="{class}" width="{size}" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
            <path d="M23 4V10H17" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
            <path d="M1 20V14H7" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
            <path d="M3.51 9.00001C4.01717 7.56679 4.87913 6.28541 6.01547 5.27543C7.1518 4.26545 8.52547 3.55978 10.0083 3.22427C11.4911 2.88877 13.0348 2.93436 14.4952 3.35679C15.9556 3.77922 17.2853 4.56473 18.36 5.64001L23 10M1 14L5.64 18.36C6.71475 19.4353 8.04437 20.2208 9.50481 20.6432C10.9652 21.0657 12.5089 21.1112 13.9917 20.7757C15.4745 20.4402 16.8482 19.7346 17.9845 18.7246C19.1209 17.7146 19.9828 16.4332 20.49 15" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
        </svg>',

        'filter' => '<svg class="{class}" width="{size}" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
            <path d="M22 3H2L10 12.46V19L14 21V12.46L22 3Z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
        </svg>',

        'export' => '<svg class="{class}" width="{size}" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
            <path d="M21 15V19C21 19.5304 20.7893 20.0391 20.4142 20.4142C20.0391 20.7893 19.5304 21 19 21H5C4.46957 21 3.96086 20.7893 3.58579 20.4142C3.21071 20.0391 3 19.5304 3 19V15" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
            <path d="M17 8L12 3L7 8" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
            <path d="M12 3V15" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
        </svg>',

        'import' => '<svg class="{class}" width="{size}" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
            <path d="M21 15V19C21 19.5304 20.7893 20.0391 20.4142 20.4142C20.0391 20.7893 19.5304 21 19 21H5C4.46957 21 3.96086 20.7893 3.58579 20.4142C3.21071 20.0391 3 19.5304 3 19V15" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
            <path d="M7 10L12 15L17 10" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
            <path d="M12 15V3" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
        </svg>',
    );

    /**
     * Render SVG icon
     * @param string $name Icon name
     * @param string $class CSS class (default: icon-20)
     * @param int $size Icon size (default: 20)
     * @return string
     */
    public static function render($name, $class = 'icon-20', $size = 20)
    {
        if (!isset(self::$_icons[$name])) {
            return '';
        }
        $svg = self::$_icons[$name];
        $svg = str_replace('{class}', $class, $svg);
        $svg = str_replace('{size}', $size, $svg);
        return $svg;
    }

    /**
     * Render button with icon
     * @param string $icon Icon name
     * @param string $url URL
     * @param array $options Button options (class, title, etc.)
     * @return string
     */
    public static function btn($icon, $url, $options = array())
    {
        $class = isset($options['class']) ? $options['class'] : 'btn btn-sm btn-soft-primary';
        $title = isset($options['title']) ? $options['title'] : '';
        $iconClass = isset($options['iconClass']) ? $options['iconClass'] : 'icon-20';
        $iconSize = isset($options['iconSize']) ? $options['iconSize'] : 20;

        unset($options['iconClass'], $options['iconSize']);
        $options['class'] = $class;
        if ($title) {
            $options['title'] = $title;
        }

        return CHtml::link(self::render($icon, $iconClass, $iconSize), $url, $options);
    }

    /**
     * Render action buttons (view, edit, delete)
     * @param mixed $data Model data
     * @param array $actions Actions to show (default: view, edit)
     * @param string $baseUrl Base URL for actions
     * @return string
     */
    public static function actionButtons($data, $actions = array('view', 'edit'), $baseUrl = null)
    {
        $buttons = array();
        $id = is_object($data) ? $data->id : $data['id'];

        $btnStyle = 'width:30px;height:30px;padding:0;display:inline-flex;align-items:center;justify-content:center;';
        $config = array(
            'view' => array('class' => 'btn btn-sm btn-info me-1', 'title' => 'Xem', 'style' => $btnStyle),
            'update' => array('class' => 'btn btn-sm btn-warning me-1', 'title' => 'Sửa', 'style' => $btnStyle),
            'delete' => array('class' => 'btn btn-sm btn-danger', 'title' => 'Xóa', 'style' => $btnStyle),
        );

        foreach ($actions as $action) {
            if (isset($config[$action])) {
                if ($action === 'delete') {
                    $url = $baseUrl ? $baseUrl . '/delete?id=' . $id : Yii::app()->createUrl($baseUrl . '/delete', array('id' => $id));
                    $buttons[] = self::deleteBtn($url, $config[$action]);
                } else {
                    $url = $baseUrl ? $baseUrl . '/' . $action . '?id=' . $id : array($action, 'id' => $id);
                    $buttons[] = self::btn($action, $url, $config[$action]);
                }
            }
        }

        return implode('', $buttons);
    }

    /**
     * Render delete button with POST form and SweetAlert confirmation
     * @param string $url Delete URL
     * @param array $options Button options
     * @return string
     */
    public static function deleteBtn($url, $options = array())
    {
        $class = isset($options['class']) ? $options['class'] : 'btn btn-sm btn-soft-danger';
        $title = isset($options['title']) ? $options['title'] : 'Xóa';
        $style = isset($options['style']) ? $options['style'] : '';
        $formId = 'delete-form-' . uniqid();

        $icon = self::render('delete', 'icon-20', 20);

        return '<form id="' . $formId . '" method="post" action="' . CHtml::encode($url) . '" style="display:inline;">'
            . '<input type="hidden" name="' . Yii::app()->request->csrfTokenName . '" value="' . Yii::app()->request->csrfToken . '" />'
            . '<button type="button" class="' . CHtml::encode($class) . '" title="' . CHtml::encode($title) . '" style="' . CHtml::encode($style) . '" onclick="confirmDelete(\'' . $formId . '\')">'
            . $icon
            . '</button>'
            . '</form>';
    }
}
