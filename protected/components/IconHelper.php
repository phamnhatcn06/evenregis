<?php

/**
 * IconHelper - Render Font Awesome icons
 *
 * Usage:
 * IconHelper::render('view')
 * IconHelper::render('edit')
 * IconHelper::btn('view', 'btn-soft-info')
 */
class IconHelper
{
    private static $_icons = array(
        'view' => 'fa-eye',
        'update' => 'fa-pencil',
        'delete' => 'fa-trash',
        'plus' => 'fa-plus',
        'search' => 'fa-search',
        'download' => 'fa-download',
        'upload' => 'fa-upload',
        'check' => 'fa-check',
        'close' => 'fa-times',
        'back' => 'fa-chevron-left',
        'print' => 'fa-print',
        'refresh' => 'fa-refresh',
        'filter' => 'fa-filter',
        'export' => 'fa-upload',
        'import' => 'fa-download',
    );

    /**
     * Render Font Awesome icon
     * @param string $name Icon name
     * @param string $class Additional CSS class
     * @return string
     */
    public static function render($name, $class = '')
    {
        if (!isset(self::$_icons[$name])) {
            return '';
        }
        $iconClass = 'fa ' . self::$_icons[$name];
        if ($class) {
            $iconClass .= ' ' . $class;
        }
        return '<i class="' . CHtml::encode($iconClass) . '"></i>';
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

        $options['class'] = $class;
        if ($title) {
            $options['title'] = $title;
        }

        return CHtml::link(self::render($icon), $url, $options);
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

        $icon = self::render('delete');

        return '<form id="' . $formId . '" method="post" action="' . CHtml::encode($url) . '" style="display:inline;">'
            . '<input type="hidden" name="' . Yii::app()->request->csrfTokenName . '" value="' . Yii::app()->request->csrfToken . '" />'
            . '<button type="button" class="' . CHtml::encode($class) . '" title="' . CHtml::encode($title) . '" style="' . CHtml::encode($style) . '" onclick="confirmDelete(\'' . $formId . '\')">'
            . $icon
            . '</button>'
            . '</form>';
    }
}
