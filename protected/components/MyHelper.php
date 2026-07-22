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
            "/assets/css/responsive-1366.css?v=1.0.0",
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
        if (is_numeric($date)) {
            return date('d-m-Y', $date);
        }
        return date('d-m-Y', strtotime($date));
    }

    /**
     * Tính tuổi từ ngày sinh (chấp nhận timestamp hoặc chuỗi ngày).
     * @return int|null Số tuổi, hoặc null nếu không xác định được ngày sinh
     */
    public static function calculateAge($birthday)
    {
        if (empty($birthday)) return null;
        $ts = is_numeric($birthday) ? (int) $birthday : strtotime($birthday);
        if ($ts === false || $ts <= 0) return null;
        try {
            $dob = new DateTime('@' . $ts);
            $now = new DateTime();
            if ($dob > $now) return null;
            return (int) $now->diff($dob)->y;
        } catch (Exception $e) {
            return null;
        }
    }

    /**
     * Lấy năm sinh (yyyy) từ ngày sinh.
     * @return string Năm sinh, hoặc chuỗi rỗng nếu không xác định được
     */
    public static function getBirthYear($birthday)
    {
        if (empty($birthday)) return '';
        $ts = is_numeric($birthday) ? (int) $birthday : strtotime($birthday);
        if ($ts === false || $ts <= 0) return '';
        return date('Y', $ts);
    }

    /**
     * Format datetime to dd-mm-yyyy HH:ii
     */
    public static function formatDateTime($date)
    {
        if (empty($date)) return '';
        if (is_numeric($date)) {
            return date('d-m-Y H:i', $date);
        }
        return date('d-m-Y H:i', strtotime($date));
    }

    public static function renderActionMenu($menu)
    {
        if (count($menu) == 0) return;
        foreach ($menu as $item) {
            $class = '';
            $target = '';
            $action = '';
            $itemId = isset($item['id']) ? $item['id'] : '';
            if ($itemId == 'btn_create') {
                $class = 'btn btn-primary btn-sm';
                $action = (isset($item['grid_id']) && $item['grid_id'] != '') ? 'createItem("' . $item['grid_id'] . '",this);return false;' : '';
            } elseif ($itemId == 'btn_update') {
                $class = 'btn btn-warning btn-sm';
                $action = (isset($item['grid_id']) && $item['grid_id'] != '') ? 'updateItem("' . $item['grid_id'] . '",this);return false;' : '';
            } elseif ($itemId == 'btn_delete') {
                $class = 'btn btn-danger btn-sm';
                if (isset($item['grid_id']) && $item['grid_id'] != '') {
                    $action = 'deleteItem("' . $item['grid_id'] . '",this);return false;';
                } else {
                    self::renderDeleteButton($item, $class);
                    continue;
                }
            } elseif ($itemId == 'btn_view') {
                $class = 'btn btn-' . $item['color'] . ' btn-bitbucket btn-sm';
                $action = (isset($item['grid_id']) && $item['grid_id'] != '') ? 'viewItem("' . $item['grid_id'] . '",this);return false;' : '';
            } elseif (!isset($item['url']) || $item['url'] == '') {
                $class = (isset($item['class']) && $item['class'] != '') ? 'right-sidebar-toggle btn btn-sm  btn-bitbucket btn-success' : 'btn btn-bitbucket btn-sm';
                $action = (isset($item['action']) && $item['action'] != '') ? $item['action'] . ';return false;' : '';
            } else {
                $class = 'btn btn-' . $item['color'] . ' btn-bitbucket btn-sm ';
                $action = (isset($item['action']) && $item['action'] != '') ? $item['action'] . ';return false;' : '';
            }
            if (isset($item['target']) && $item['target'] != '') {
                $target = $item['target'];
            }
            echo CHtml::link(' <i class="fa ' . $item['icon'] . '"></i> ' . $item['label'], $item['url'], ['class' => $class, 'target' => $target, 'onclick' => $action, 'id' => $itemId]);
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

    /**
     * Convert Vietnamese string to non-accented slug
     * @param string $str Vietnamese string
     * @return string Non-accented lowercase slug
     */
    public static function toSlug($str)
    {
        $str = trim($str);
        $str = self::removeVietnameseAccents($str);
        $str = strtolower($str);
        $str = preg_replace('/[^a-z0-9\s-]/', '', $str);
        $str = preg_replace('/[\s-]+/', '-', $str);
        $str = trim($str, '-');
        return $str;
    }

    /**
     * Remove Vietnamese accents from string
     * @param string $str Vietnamese string
     * @return string String without accents
     */
    public static function removeVietnameseAccents($str)
    {
        $accents = array(
            'à', 'á', 'ạ', 'ả', 'ã', 'â', 'ầ', 'ấ', 'ậ', 'ẩ', 'ẫ', 'ă', 'ằ', 'ắ', 'ặ', 'ẳ', 'ẵ',
            'è', 'é', 'ẹ', 'ẻ', 'ẽ', 'ê', 'ề', 'ế', 'ệ', 'ể', 'ễ',
            'ì', 'í', 'ị', 'ỉ', 'ĩ',
            'ò', 'ó', 'ọ', 'ỏ', 'õ', 'ô', 'ồ', 'ố', 'ộ', 'ổ', 'ỗ', 'ơ', 'ờ', 'ớ', 'ợ', 'ở', 'ỡ',
            'ù', 'ú', 'ụ', 'ủ', 'ũ', 'ư', 'ừ', 'ứ', 'ự', 'ử', 'ữ',
            'ỳ', 'ý', 'ỵ', 'ỷ', 'ỹ',
            'đ',
            'À', 'Á', 'Ạ', 'Ả', 'Ã', 'Â', 'Ầ', 'Ấ', 'Ậ', 'Ẩ', 'Ẫ', 'Ă', 'Ằ', 'Ắ', 'Ặ', 'Ẳ', 'Ẵ',
            'È', 'É', 'Ẹ', 'Ẻ', 'Ẽ', 'Ê', 'Ề', 'Ế', 'Ệ', 'Ể', 'Ễ',
            'Ì', 'Í', 'Ị', 'Ỉ', 'Ĩ',
            'Ò', 'Ó', 'Ọ', 'Ỏ', 'Õ', 'Ô', 'Ồ', 'Ố', 'Ộ', 'Ổ', 'Ỗ', 'Ơ', 'Ờ', 'Ớ', 'Ợ', 'Ở', 'Ỡ',
            'Ù', 'Ú', 'Ụ', 'Ủ', 'Ũ', 'Ư', 'Ừ', 'Ứ', 'Ự', 'Ử', 'Ữ',
            'Ỳ', 'Ý', 'Ỵ', 'Ỷ', 'Ỹ',
            'Đ',
        );
        $noAccents = array(
            'a', 'a', 'a', 'a', 'a', 'a', 'a', 'a', 'a', 'a', 'a', 'a', 'a', 'a', 'a', 'a', 'a',
            'e', 'e', 'e', 'e', 'e', 'e', 'e', 'e', 'e', 'e', 'e',
            'i', 'i', 'i', 'i', 'i',
            'o', 'o', 'o', 'o', 'o', 'o', 'o', 'o', 'o', 'o', 'o', 'o', 'o', 'o', 'o', 'o', 'o',
            'u', 'u', 'u', 'u', 'u', 'u', 'u', 'u', 'u', 'u', 'u',
            'y', 'y', 'y', 'y', 'y',
            'd',
            'A', 'A', 'A', 'A', 'A', 'A', 'A', 'A', 'A', 'A', 'A', 'A', 'A', 'A', 'A', 'A', 'A',
            'E', 'E', 'E', 'E', 'E', 'E', 'E', 'E', 'E', 'E', 'E',
            'I', 'I', 'I', 'I', 'I',
            'O', 'O', 'O', 'O', 'O', 'O', 'O', 'O', 'O', 'O', 'O', 'O', 'O', 'O', 'O', 'O', 'O',
            'U', 'U', 'U', 'U', 'U', 'U', 'U', 'U', 'U', 'U', 'U',
            'Y', 'Y', 'Y', 'Y', 'Y',
            'D',
        );
        return str_replace($accents, $noAccents, $str);
    }

    /**
     * Send email using SMTP
     * @param string $to Email recipient
     * @param string $subject Email subject
     * @param string $view View name in application.views.mail folder
     * @param array $data Data to pass to view
     * @param array $attachments File paths to attach
     * @return bool
     */
    public static function sendMail($to, $subject, $view, $data = array(), $attachments = array())
    {
        $mail = Yii::app()->mail;
        $params = Yii::app()->params['mail'];

        if (empty($params)) {
            throw new Exception("Mail params not configured in params.php");
        }

        $viewPath = Yii::getPathOfAlias('application.views.mail.' . $view) . '.php';
        if (!file_exists($viewPath)) {
            throw new Exception("Email view not found: {$viewPath}");
        }

        extract($data);
        ob_start();
        include($viewPath);
        $body = ob_get_clean();

        $message = new YiiMailMessage();
        $message->setSubject($subject);
        $message->setFrom(array($params['from_email'] => $params['from_name']));
        $message->setTo($to);
        $message->setBody($body, 'text/html');

        foreach ($attachments as $attachment) {
            if (is_string($attachment)) {
                $message->attach(Swift_Attachment::fromPath($attachment));
            }
        }

        try {
            $failedRecipients = array();
            $result = $mail->send($message, $failedRecipients);
            if ($result > 0) {
                return true;
            } else {
                throw new Exception("Send returned 0. Failed: " . implode(', ', $failedRecipients));
            }
        } catch (Swift_TransportException $e) {
            throw new Exception("SMTP error: " . $e->getMessage());
        } catch (Swift_RfcComplianceException $e) {
            throw new Exception("Email format error: " . $e->getMessage());
        }
    }
}
?>