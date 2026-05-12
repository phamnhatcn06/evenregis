<?php

class MyHelper
{

    public static function renderJs($islogin = false)
    {
        $cs = Yii::app()->clientScript;
        $cs->scriptMap['notify.min.js'] = false;
        if ($islogin) {
            $arrayJs = array(
                "/vertical/assets/js/popper.min.js",
                "/vertical/assets/js/metisMenu.min.js",
                "/vertical/assets/js/jquery.slimscroll.js",
                "/vertical/assets/js/jquery.core.js",
                "/vertical/assets/js/jquery.app.js",

            );
        } else {
            $arrayJs = array(
                "/vertical/assets/js/popper.min.js",
                "/vertical/assets/js/bootstrap.min.js",
                "/vertical/assets/js/metisMenu.min.js",
                "/vertical/assets/js/jquery.slimscroll.js",
                "/vertical/assets/js/ladda.min.js",
                "/vertical/assets/js/spin.min.js",
                "/plugins/switchery/switchery.min.js",
                "/plugins/bootstrap-tagsinput/js/bootstrap-tagsinput.min.js",
                "/plugins/autoNumeric/autoNumeric.js",
                "/plugins/select2/js/select2.min.js",
                "/plugins/bootstrap-select/js/bootstrap-select.js",
                "/plugins/datatables/media/js/jquery.dataTables.min.js",
                "/plugins/datatables.net-buttons/js/dataTables.buttons.min.js",
                "/plugins/datatables.net-responsive/js/dataTables.responsive.min.js",
                "/plugins/bootstrap-switch/dist/js/bootstrap-switch.min.js",
                "/plugins/jquery-knob/excanvas.js",
                "/plugins/jquery-knob/jquery.knob.js",
                "/plugins/bootstrap-treeview/dist/bootstrap-treeview.min.js",
                "/plugins/dropify/dist/js/dropify.min.js",
                "/plugins/sweetalert/dist/sweetalert.min.js",
                "/vertical/assets/js/jquery.core.js",
                "/vertical/assets/js/jquery.app.js",
                "/plugins/custombox/js/custombox.min.js",
                "/plugins/custombox/js/legacy.min.js",
                "/plugins/bootstrap-fileupload/bootstrap-fileupload.js",
                "/plugins/dropzone/dropzone.js",
                "/plugins/multiselect/js/jquery.multi-select.js",
                "/plugins/moment/moment.js",
                "/plugins/tooltipster/tooltipster.bundle.min.js",
                "/plugins/slick/slick.min.js",
                "/vertical/assets/pages/jquery.tooltipster.js",
                "/plugins/fancybox/jquery.mousewheel-3.0.4.pack.js",
                "/plugins/jquery-toastr/jquery.toast.min.js",
                "/plugins/bootstrap-daterangepicker/daterangepicker.js",
                "/plugins/bootstrap-datepicker/js/bootstrap-datepicker.min.js",
                "/plugins/fancybox/jquery.fancybox-1.3.4.pack.js",
                "/vertical/assets/js/Chart.bundle.min.js",
                "/vertical/assets/js/jquery.scannerdetection.js",
                // "/vertical/assets/js/bootstrap-editable.min.js",
                // "/vertical/assets/js/chartjs.init.js",
                "/vertical/assets/js/custom.js",
            );
        }
        foreach ($arrayJs as $js) { ?>
            <?php Yii::app()->clientScript->registerScriptFile(
                Yii::app()->theme->baseUrl . $js,
                CClientScript::POS_END
            ); ?>
        <?php }
    }

    public static function renderCss()
    {
        $listCss = array(
            "/assets/css/core/libs.min.css",
            // "/assets/css/hope-ui.min.css?v=2.0.0",
            "/assets/css/hope-ui-thangvc.css?v=2.0.0",
            "/assets/css/custom.min.css?v=2.0.0",
            "/assets/css/dark.min.css",
            "/assets/css/customizer.min.css",
            "/assets/css/rtl.min.css",
        );
        foreach ($listCss as $css) { ?>
            <link href=" <?= Yii::app()->theme->getBaseUrl() . $css ?>" rel="stylesheet" type="text/css" />
<?php }
    }


    /**
     * Format date to dd-mm-yyyy
     */
    public static function formatDate($date)
    {
        if (empty($date)) return '';
        return date('d-m-Y', strtotime($date));
    }

    /**
     * Format datetime to dd-mm-yyyy HH:ii
     */
    public static function formatDateTime($date)
    {
        if (empty($date)) return '';
        return date('d-m-Y H:i', strtotime($date));
    }

    public static function renderActionMenu($menu)
    {
        if (count($menu) == 0) return;
        foreach ($menu as $item) {
            $class = '';
            $target = '';
            $action = '';
            if ($item['id'] == 'btn_create') {
                $class = 'btn btn-primary btn-sm';
                $action = (isset($item['grid_id']) && $item['grid_id'] != '') ? 'createItem("' . $item['grid_id'] . '",this);return false;' : '';
            } elseif ($item['id'] == 'btn_update') {
                $class = 'btn btn-warning btn-sm';
                $action = (isset($item['grid_id']) && $item['grid_id'] != '') ? 'updateItem("' . $item['grid_id'] . '",this);return false;' : '';
            } elseif ($item['id'] == 'btn_delete') {
                $class = 'btn btn-danger btn-sm';
                if (isset($item['grid_id']) && $item['grid_id'] != '') {
                    $action = 'deleteItem("' . $item['grid_id'] . '",this);return false;';
                } else {
                    self::renderDeleteButton($item, $class);
                    continue;
                }
            } elseif ($item['id'] == 'btn_view') {
                $class = 'btn btn-' . $item['color'] . ' btn-bitbucket btn-sm';
                $action = (isset($item['grid_id']) && $item['grid_id'] != '') ? 'viewItem("' . $item['grid_id'] . '",this);return false;' : '';
            } elseif ($item['url'] == '') {
                $class = (isset($item['class']) && $item['class'] != '') ? 'right-sidebar-toggle btn btn-sm  btn-bitbucket btn-success' : 'btn btn-bitbucket btn-sm';
                $action = (isset($item['action']) && $item['action'] != '') ? $item['action'] . ';return false;' : '';
            } else {
                $class = 'btn btn-' . $item['color'] . ' btn-bitbucket btn-sm ';
                $action = (isset($item['action']) && $item['action'] != '') ? $item['action'] . ';return false;' : '';
            }
            if (isset($item['target']) && $item['target'] != '') {
                $target = $item['target'];
            }
            echo CHtml::link(' <i class="fa ' . $item['icon'] . '"></i> ' . $item['label'], $item['url'], ['class' => $class, 'target' => $target, 'onclick' => $action, 'id' => $item['id']]);
        }
    }

    /**
     * Render delete button with POST form and SweetAlert confirmation
     */
    public static function renderDeleteButton($item, $class)
    {
        if (isset($item['visible']) && !$item['visible']) {
            return;
        }
        $formId = 'delete-form-' . uniqid();
        echo '<form id="' . $formId . '" method="post" action="' . CHtml::encode($item['url']) . '" style="display:inline;">'
            . '<input type="hidden" name="' . Yii::app()->request->csrfTokenName . '" value="' . Yii::app()->request->csrfToken . '" />'
            . '<button type="button" class="' . CHtml::encode($class) . '" id="' . CHtml::encode($item['id']) . '" onclick="confirmDelete(\'' . $formId . '\')">'
            . ' <i class="fa ' . $item['icon'] . '"></i> ' . $item['label']
            . '</button>'
            . '</form>';
    }
}
?>