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
     * Tính số tháng làm việc tính từ ngày bắt đầu (end_starting_date) đến hiện tại.
     * @param mixed $startDate Timestamp hoặc chuỗi ngày
     * @return int|null Số tháng, hoặc null nếu không xác định được ngày bắt đầu
     */
    public static function calculateWorkingMonths($startDate)
    {
        if (empty($startDate)) return null;
        $ts = is_numeric($startDate) ? (int) $startDate : strtotime($startDate);
        if ($ts === false || $ts <= 0) return null;
        try {
            $start = new DateTime('@' . $ts);
            $now = new DateTime();
            if ($start > $now) return 0;
            $diff = $now->diff($start);
            return (int) ($diff->y * 12 + $diff->m);
        } catch (Exception $e) {
            return null;
        }
    }

    /**
     * Chuỗi hiển thị thời gian bắt đầu làm việc cho Tập đoàn kèm số tháng đã làm.
     * Ví dụ: "01/03/2020 (65 tháng)". Dùng chung cho các mail template.
     * @param mixed $startDate Timestamp hoặc chuỗi ngày (end_starting_date)
     * @return string Chuỗi định dạng, hoặc placeholder "…/…/…. (… tháng)" nếu thiếu dữ liệu
     */
    public static function formatWorkingDuration($startDate)
    {
        if (empty($startDate)) {
            return '…/…/…. (… tháng)';
        }
        $ts = is_numeric($startDate) ? (int) $startDate : strtotime($startDate);
        if ($ts === false || $ts <= 0) {
            return '…/…/…. (… tháng)';
        }
        $months = self::calculateWorkingMonths($ts);
        $monthText = $months === null ? '… tháng' : ($months . ' tháng');
        return date('d/m/Y', $ts) . ' (' . $monthText . ')';
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
            'à',
            'á',
            'ạ',
            'ả',
            'ã',
            'â',
            'ầ',
            'ấ',
            'ậ',
            'ẩ',
            'ẫ',
            'ă',
            'ằ',
            'ắ',
            'ặ',
            'ẳ',
            'ẵ',
            'è',
            'é',
            'ẹ',
            'ẻ',
            'ẽ',
            'ê',
            'ề',
            'ế',
            'ệ',
            'ể',
            'ễ',
            'ì',
            'í',
            'ị',
            'ỉ',
            'ĩ',
            'ò',
            'ó',
            'ọ',
            'ỏ',
            'õ',
            'ô',
            'ồ',
            'ố',
            'ộ',
            'ổ',
            'ỗ',
            'ơ',
            'ờ',
            'ớ',
            'ợ',
            'ở',
            'ỡ',
            'ù',
            'ú',
            'ụ',
            'ủ',
            'ũ',
            'ư',
            'ừ',
            'ứ',
            'ự',
            'ử',
            'ữ',
            'ỳ',
            'ý',
            'ỵ',
            'ỷ',
            'ỹ',
            'đ',
            'À',
            'Á',
            'Ạ',
            'Ả',
            'Ã',
            'Â',
            'Ầ',
            'Ấ',
            'Ậ',
            'Ẩ',
            'Ẫ',
            'Ă',
            'Ằ',
            'Ắ',
            'Ặ',
            'Ẳ',
            'Ẵ',
            'È',
            'É',
            'Ẹ',
            'Ẻ',
            'Ẽ',
            'Ê',
            'Ề',
            'Ế',
            'Ệ',
            'Ể',
            'Ễ',
            'Ì',
            'Í',
            'Ị',
            'Ỉ',
            'Ĩ',
            'Ò',
            'Ó',
            'Ọ',
            'Ỏ',
            'Õ',
            'Ô',
            'Ồ',
            'Ố',
            'Ộ',
            'Ổ',
            'Ỗ',
            'Ơ',
            'Ờ',
            'Ớ',
            'Ợ',
            'Ở',
            'Ỡ',
            'Ù',
            'Ú',
            'Ụ',
            'Ủ',
            'Ũ',
            'Ư',
            'Ừ',
            'Ứ',
            'Ự',
            'Ử',
            'Ữ',
            'Ỳ',
            'Ý',
            'Ỵ',
            'Ỷ',
            'Ỹ',
            'Đ',
        );
        $noAccents = array(
            'a',
            'a',
            'a',
            'a',
            'a',
            'a',
            'a',
            'a',
            'a',
            'a',
            'a',
            'a',
            'a',
            'a',
            'a',
            'a',
            'a',
            'e',
            'e',
            'e',
            'e',
            'e',
            'e',
            'e',
            'e',
            'e',
            'e',
            'e',
            'i',
            'i',
            'i',
            'i',
            'i',
            'o',
            'o',
            'o',
            'o',
            'o',
            'o',
            'o',
            'o',
            'o',
            'o',
            'o',
            'o',
            'o',
            'o',
            'o',
            'o',
            'o',
            'u',
            'u',
            'u',
            'u',
            'u',
            'u',
            'u',
            'u',
            'u',
            'u',
            'u',
            'y',
            'y',
            'y',
            'y',
            'y',
            'd',
            'A',
            'A',
            'A',
            'A',
            'A',
            'A',
            'A',
            'A',
            'A',
            'A',
            'A',
            'A',
            'A',
            'A',
            'A',
            'A',
            'A',
            'E',
            'E',
            'E',
            'E',
            'E',
            'E',
            'E',
            'E',
            'E',
            'E',
            'E',
            'I',
            'I',
            'I',
            'I',
            'I',
            'O',
            'O',
            'O',
            'O',
            'O',
            'O',
            'O',
            'O',
            'O',
            'O',
            'O',
            'O',
            'O',
            'O',
            'O',
            'O',
            'O',
            'U',
            'U',
            'U',
            'U',
            'U',
            'U',
            'U',
            'U',
            'U',
            'U',
            'U',
            'Y',
            'Y',
            'Y',
            'Y',
            'Y',
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
    public static function sendMail($to, $subject, $view, $data = array(), $attachments = array(), $cc = array(), $bcc = array())
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
        if (!empty($cc)) {
            $message->setCc($cc);
        }
        if (!empty($bcc)) {
            $message->setBcc($bcc);
        }
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

    public static function RoundImage($path, $name, $size = array())
    {
        $filename = $path;
        $image_s = imagecreatefromstring(file_get_contents($filename));
        $width = imagesx($image_s);
        $height = imagesy($image_s);
        $newwidth = $size['width'];
        $newheight = $size['height'];
        $image = imagecreatetruecolor($newwidth, $newheight);
        imagealphablending($image, true);
        imagecopyresampled($image, $image_s, 0, 0, 0, 0, $newwidth, $newheight, $width, $height);
        //create masking
        $mask = imagecreatetruecolor($newwidth, $newheight);
        $transparent = imagecolorallocate($mask, 255, 0, 0);
        imagecolortransparent($mask, $transparent);
        imagefilledellipse($mask, $newwidth / 2, $newheight / 2, $newwidth, $newheight, $transparent);
        $red = imagecolorallocate($mask, 0, 0, 0);
        imagecopymerge($image, $mask, 0, 0, 0, 0, $newwidth, $newheight, 100);
        imagecolortransparent($image, $red);
        imagefill($image, 0, 0, $red);
        //output, save and free memory
        //header('Content-type: image/png');
        //imagepng($image);
        $baseFolder = Yii::app()->basePath . '/../uploads/nhan-vien/';
        if (!is_dir($baseFolder)) {
            mkdir($baseFolder);
        }
        $newName = self::cleanString($name) . '-' . substr(md5(self::RandomString()), 28, 5);
        $des = $baseFolder . $newName . '.png';
        imagepng($image, $des);
        imagedestroy($image);
        imagedestroy($mask);
        return  $des;
    }

    public static function ImageVDBundle($hotel_id, $hotel, $eventSlug, $imageUrl, $name, $txtJob, $jobTit, $barcode_code, $isBTC = false, $id = 0, $data = [])
    {
        $baseFolder = Yii::app()->basePath . '/../uploads/';
        //            Create First Image
        $imageModify = MyHelper::createFirstPicPNG($baseFolder . 'phoianh/blank_image_vd.png', $name, $eventSlug, $hotel_id);

        // //            Insert Image To B1.
        // $imageToInsert = MyHelper::downloadImage($name, $imageUrl, 'nhan-vien');
        // //                Resize image:
        // $imageInsert = MyHelper::resizeImage($imageToInsert, array('width' => 582, 'height' => 582), 'nhan-vien', $name, false, false, true);

        // // @unlink($imageToInsert);
        // // $roundImage = MyHelper::RoundImage($imageInsert, $name, array('width' => 540, 'height' => 540));
        // // @unlink($imageInsert);
        // $pos_y = 0;
        // if ($hotel_id == '9999'  || $isBTC == true) {
        //     $pos_y = 386;
        // } else {
        //     $pos_y = 395;
        // }
        // $imageModify = MyHelper::insertImage($firstImage,  $imageInsert, array('pos_x' => 240, 'pos_y' => $pos_y), $eventSlug .
        //     '/hotel_' . $hotel_id, $name);
        // @unlink($imageInsert);

        if ($hotel_id == '9999' || $isBTC == true) {
            $backgroundImage = $baseFolder . 'phoianh/phoi_vd.png';
        } else {
            $backgroundImage  = $baseFolder . 'phoianh/phoi_the_ks.png';
        }


        $pos_y = 0;
        if ($hotel_id == '9999') {
            $pos_y = 462;
        } else {
            $pos_y = 454;
        }
        $imageModify = MyHelper::insertImagePNG($imageModify, $backgroundImage, array('pos_x' => 0, 'pos_y' => 0), $eventSlug .
            '/hotel_' . $hotel_id, $name);
        // @unlink($imageInsert);
        // Name
        $pos_y = 0;
        if (isset($data->Gender) && $data->Gender == true) {
            $sign = 'ÔNG:';
        } else {
            $sign = 'BÀ:';
        }
        $name = mb_strtoupper($name);
        $txtInsert =  $sign . ' ' . mb_strtoupper($name);
        $pos_y = 575;
        $imageModify = MyHelper::insertTextPNG($imageModify, $txtInsert, array('pos_x' => 0, 'pos_y' => $pos_y, 'font' => 'lato-black', 'font-size' => 30, 'align' => 'C'), $eventSlug . '/hotel_' . $hotel_id, $name, false);

        // Hotel
        $pos_y = 0;
        $txt = $jobTit . ' ';
        $txt = str_replace('  ', ' ', $txt);
        $arrText = explode('\n', $txt);
        $i = 0;
        foreach ($arrText as $txtHotel) {
            if ($i == 0) {
                $pos_y = 634;
            } else {
                $pos_y += 50;
            }
            $imageModify = MyHelper::insertTextPNG($imageModify, $txtHotel, array('pos_x' => 0, 'pos_y' => $pos_y, 'font' => 'lato-black', 'font-size' => 27, 'align' => 'C'), $eventSlug . '/hotel_' . $hotel_id, $name, false);
            $i++;
        }
        // Count Year
        $pos_y = 0;
        $txt =  'Đã có ' . $barcode_code . ' năm đồng hành và phát triển \n cùng tập đoàn Mường Thanh';
        //
        // $txt = str_replace('Ẩ', 'ẩ', $txt);
        $arrText = explode('\n', $txt);
        $j = 0;
        foreach ($arrText as $txt) {
            $txt = mb_strtoupper($txt);
            if ($j == 0) {
                $pos_y = 740;
            } else {
                $pos_y += 40;
            }
            $imageModify = MyHelper::insertTextPNG($imageModify, $txt, array('pos_x' => 0, 'pos_y' => $pos_y, 'font' => 'lato-medium', 'font-size' => 22, 'align' => 'C'), $eventSlug . '/hotel_' . $hotel_id, $name, false);
            $j++;
        }
        // $imageModify = MyHelper::insertText($imageModify, $txt, array('pos_x' => 0, 'pos_y' => $pos_y, 'font' => 'lato-medium', 'font-size' => 24, 'align' => 'C'), $eventSlug . '/hotel_' . $hotel_id, $name, false);

        // $pos_y = 0;
        // if ($hotel_id == '9999' || $isBTC == 1) {
        //     $text = 'HTXV ' . $barcode_code;
        //     $pos_y = 1123;
        //     $imageModify = MyHelper::insertText($imageModify, $text, array('pos_x' => 0, 'pos_y' => $pos_y, 'font' => 'lato-black', 'font-size' => 42, 'align' => 'C'), $eventSlug . '/hotel_' . $hotel_id, $name, true);
        // } else {
        //     $text = 'HTXV ' . $barcode_code;
        //     $pos_y = 1042;
        //     $imageModify = MyHelper::insertText($imageModify, $text, array('pos_x' => 0, 'pos_y' => $pos_y, 'font' => 'lato-black', 'font-size' => 50, 'align' => 'C'), $eventSlug . '/hotel_' . $hotel_id, $name, true);
        // }




        //            ## Insert job title:
        // $pos_y = 0;
        // if ($hotel_id == '9999') {
        //     $pos_y = 1090;
        // } else {
        //     $pos_y = 1090;
        // }
        // $imageModify = MyHelper::insertText($imageModify, $jobTit, array('pos_x' => 400, 'pos_y' => $pos_y, 'font' => 'lato-medium', 'font-size' => 30), $eventSlug . '/hotel_' . $hotel_id, $name, true);
        //                ## Insert Hotel:



        $imageModify = MyHelper::resizeImage($imageModify, array('width' => 887, 'height' => 1182), $eventSlug .
            '/hotel_' . $hotel_id, $name, false, false, true);
        //                Resolution:
        // /$imageModify = MyHelper::resolutionImage($imageModify, array('width' => 300, 'height' => 300), $eventSlug . '/hotel_' . $hotel_id, $name);
    }


    public static function ImageVDGBBundle($hotel_id, $hotel, $eventSlug, $imageUrl, $name, $txtJob, $jobTit, $barcode_code, $isBTC = false, $id = 0)
    {
        $baseFolder = Yii::app()->basePath . '/../uploads/';
        //            Create First Image
        $imageModify = MyHelper::createFirstPicPNG($baseFolder . 'phoianh/blank_vd_gan_bo.png', $name, $eventSlug, $hotel_id);
        $backgroundImage = $baseFolder . 'phoianh/vinh_danh_gan_bo.png';
        $pos_y = 0;
        if ($hotel_id == '9999') {
            $pos_y = 462;
        } else {
            $pos_y = 454;
        }
        $imageModify = MyHelper::insertImagePNG($imageModify, $backgroundImage, array('pos_x' => 0, 'pos_y' => 0), $eventSlug .
            '/hotel_' . $hotel_id, $name);
        // @unlink($imageInsert);
        // Name
        $pos_y = 0;
        // $name = mb_strtoupper($name);
        $txtInsert =  $name;
        $pos_y = 1518;
        $imageModify = MyHelper::insertTextPNG($imageModify, $txtInsert, array('pos_x' => 0, 'pos_y' => $pos_y, 'font' => 'UVNThuTu_0', 'font-size' => 160, 'align' => 'C'), $eventSlug . '/hotel_' . $hotel_id, $name, false, '#b07909');

        // Hotel
        $pos_y = 0;
        $txt = $jobTit . ' ';
        $txt = str_replace('  ', ' ', $txt);
        $arrText = explode('\n', $txt);
        $i = 0;
        foreach ($arrText as $txtHotel) {
            if ($i == 0) {
                $pos_y = 1760;
            } else {
                $pos_y += 50;
            }
            $txtHotel = mb_strtoupper($txtHotel);
            $imageModify = MyHelper::insertTextPNG($imageModify, $txtHotel, array('pos_x' => 0, 'pos_y' => $pos_y, 'font' => 'lato-black', 'font-size' => 40, 'align' => 'C'), $eventSlug . '/hotel_' . $hotel_id, $name, false);
            $i++;
        }
    }

    public static function ImageBundle($hotel_id, $hotel, $eventSlug, $imageUrl, $name, $txtJob, $jobTit, $barcode_code, $isBTC = false, $id = 0)
    {
        $baseFolder = Yii::app()->basePath . '/../uploads/';
        //            Create First Image
        $firstImage = MyHelper::createFirstPic($baseFolder . 'phoianh/blank_image.png', $name, $eventSlug, $hotel_id);

        //            Insert Image To B1.
        $imageToInsert = MyHelper::downloadImage($name, $imageUrl, 'nhan-vien');
        //                Resize image:
        $imageInsert = MyHelper::resizeImage($imageToInsert, array('width' => 582, 'height' => 582), 'nhan-vien', $name, false, false, true);

        // @unlink($imageToInsert);
        // $roundImage = MyHelper::RoundImage($imageInsert, $name, array('width' => 540, 'height' => 540));
        // @unlink($imageInsert);
        $pos_y = 0;
        if ($hotel_id == '9999'  || $isBTC == true) {
            $pos_y = 386;
        } else {
            $pos_y = 395;
        }
        $imageModify = MyHelper::insertImage($firstImage,  $imageInsert, array('pos_x' => 240, 'pos_y' => $pos_y), $eventSlug .
            '/hotel_' . $hotel_id, $name);
        // @unlink($imageInsert);

        if ($hotel_id == '9999' || $isBTC == true) {
            $backgroundImage = $baseFolder . 'phoianh/phoi_the_btc.png';
        } else {
            $backgroundImage  = $baseFolder . 'phoianh/phoi_the_ks.png';
        }


        $pos_y = 0;
        if ($hotel_id == '9999') {
            $pos_y = 462;
        } else {
            $pos_y = 454;
        }
        $imageModify = MyHelper::insertImage($imageModify, $backgroundImage, array('pos_x' => 0, 'pos_y' => 0), $eventSlug .
            '/hotel_' . $hotel_id, $name);
        @unlink($imageInsert);
        $pos_y = 0;
        if ($hotel_id == '9999' || $isBTC == 1) {
            $text = 'HTXV ' . $barcode_code;
            $pos_y = 1123;
            $imageModify = MyHelper::insertText($imageModify, $text, array('pos_x' => 0, 'pos_y' => $pos_y, 'font' => 'lato-black', 'font-size' => 42, 'align' => 'C'), $eventSlug . '/hotel_' . $hotel_id, $name, true);
        } else {
            $text = 'HTXV ' . $barcode_code;
            $pos_y = 1042;
            $imageModify = MyHelper::insertText($imageModify, $text, array('pos_x' => 0, 'pos_y' => $pos_y, 'font' => 'lato-black', 'font-size' => 50, 'align' => 'C'), $eventSlug . '/hotel_' . $hotel_id, $name, true);
        }

        $pos_y = 0;
        $name = mb_strtoupper($name);
        if ($hotel_id == '9999' || $isBTC == 1) {
            $pos_y = 1199;
            $imageModify = MyHelper::insertText($imageModify, $name, array('pos_x' => 0, 'pos_y' => $pos_y, 'font' => 'lato-black', 'font-size' => 36, 'align' => 'C'), $eventSlug . '/hotel_' . $hotel_id, $name, false, '#FFFFFF');
        } else {
            $pos_y = 1137;
            $imageModify = MyHelper::insertText($imageModify, $name, array('pos_x' => 0, 'pos_y' => $pos_y, 'font' => 'lato-black', 'font-size' => 36, 'align' => 'C'), $eventSlug . '/hotel_' . $hotel_id, $name, false);
        }


        //            ## Insert job title:
        // $pos_y = 0;
        // if ($hotel_id == '9999') {
        //     $pos_y = 1090;
        // } else {
        //     $pos_y = 1090;
        // }
        // $imageModify = MyHelper::insertText($imageModify, $jobTit, array('pos_x' => 400, 'pos_y' => $pos_y, 'font' => 'lato-medium', 'font-size' => 30), $eventSlug . '/hotel_' . $hotel_id, $name, true);
        //                ## Insert Hotel:
        $pos_y = 0;
        $txt =  $jobTit . ' ';
        $txt = str_replace('  ', ' ', $txt);
        // $txt = str_replace('Ẩ', 'ẩ', $txt);
        if ($hotel_id == '9999' || $isBTC == 1) {
            $pos_y = 1260;
            $imageModify = MyHelper::insertText($imageModify, $txt, array('pos_x' => 0, 'pos_y' => $pos_y, 'font' => 'lato-black', 'font-size' => 27, 'align' => 'C'), $eventSlug . '/hotel_' . $hotel_id, $name, false, '#FFFFFF');
        } else {
            $pos_y = 1217;
            $imageModify = MyHelper::insertText($imageModify, $txt, array('pos_x' => 0, 'pos_y' => $pos_y, 'font' => 'lato-black', 'font-size' => 27, 'align' => 'C'), $eventSlug . '/hotel_' . $hotel_id, $name, false);
        }


        $pos_y = 0;

        $txt = $hotel;
        $txt = str_replace('  ', ' ', $txt);
        $txt = str_replace('KS MT ', 'Mường Thanh ', $txt);

        if ($hotel_id == '9999'  || $isBTC == 1) {
            $pos_y = 1310;
            $imageModify = MyHelper::insertText($imageModify, $txt, array('pos_x' => 0, 'pos_y' => $pos_y, 'font' => 'lato-black', 'font-size' => 27, 'align' => 'C'), $eventSlug . '/hotel_' . $hotel_id, $name, false, '#FFFFFF');
        } else {
            $pos_y = 1280;
            $imageModify = MyHelper::insertText($imageModify, $txt, array('pos_x' => 0, 'pos_y' => $pos_y, 'font' => 'lato-black', 'font-size' => 27, 'align' => 'C'), $eventSlug . '/hotel_' . $hotel_id, $name, false);
        }
        $imageModify = MyHelper::resizeImage($imageModify, array('width' => 887, 'height' => 1182), $eventSlug .
            '/hotel_' . $hotel_id, $name, false, false, true);
        //                Resolution:
        // /$imageModify = MyHelper::resolutionImage($imageModify, array('width' => 300, 'height' => 300), $eventSlug . '/hotel_' . $hotel_id, $name);
    }

    public static function ImageBundleGolf($hotel_id, $hotel, $eventSlug, $imageUrl, $name, $txtJob, $jobTit, $barcode_code, $isBTC = false, $id = 0)
    {
        $baseFolder = Yii::app()->basePath . '/../uploads/';
        //            Create First Image
        $firstImage = MyHelper::createFirstPic($baseFolder . 'phoianh/blank_golf.png', $name, $eventSlug, $hotel_id);
        $backgroundImage  = $baseFolder . 'phoianh/phoi_golf.png';
        $imageModify = MyHelper::insertImage($firstImage, $backgroundImage, array('pos_x' => 0, 'pos_y' => 0), $eventSlug .
            '/hotel_' . $hotel_id, $name);
        $pos_y = 0;
        $name = mb_strtoupper($name);
        $pos_y = 1150;
        $imageModify = MyHelper::insertText($imageModify, $name, array('pos_x' => 620, 'pos_y' => $pos_y, 'font' => 'lato-black', 'font-size' => 50, 'align' => 'L'), $eventSlug . '/hotel_' . $hotel_id, $name, false);
    }

    // =============================================================================
    // Xuất ảnh thẻ Vòng Chung Kết (VCK) — gộp mặt trước + mặt sau vào 1 file PNG.
    //
    // Dùng GD trực tiếp (không qua insertText/insertImage) để kiểm soát hoàn toàn và
    // tránh `echo $image->log` trong insertText làm hỏng luồng tải file.
    // =============================================================================

    /** Thư mục chứa phôi thẻ */
    const BADGE_TEMPLATE_DIR = 'phoianh/';

    /**
     * Toạ độ & cỡ chữ cho thẻ VCK (canvas 1063×1418, ảnh 582×582 tại x240 y390).
     * Toạ độ pos_y là ĐỈNH chữ. Cỡ chữ & vị trí đo trực tiếp từ phôi mẫu docs/the/.
     * Thẻ BTC nền tối → chữ trắng; thẻ KS (ĐVTV) nền sáng → chữ đen.
     */
    public static function finalBadgeLayout()
    {
        // Thuộc tính dùng chung (font, cỡ chữ, canh giữa) cho cả hai loại phôi.
        $name     = array('pos_x' => 0, 'font' => 'fontten',      'size' => 69, 'align' => 'C');
        $position = array('pos_x' => 0, 'font' => 'fontchucdanh', 'size' => 19, 'align' => 'C');
        $unit     = array('pos_x' => 0, 'font' => 'fontdonvi',    'size' => 21, 'align' => 'C');

        return array(
            'photo' => array('pos_x' => 240, 'pos_y' => 390, 'width' => 582, 'height' => 582),

            // Mặt trước: vị trí dòng (pos_y) + màu chữ khác nhau theo loại phôi.
            'front' => array(
                'btc' => array(
                    'name'     => array_merge($name,     array('pos_y' => 988,  'color' => '#FFFFFF')),
                    'position' => array_merge($position, array('pos_y' => 1133, 'color' => '#FFFFFF')),
                    'unit'     => array_merge($unit,     array('pos_y' => 1199, 'color' => '#FFFFFF')),
                ),
                'default' => array(
                    'name'     => array_merge($name,     array('pos_y' => 973,  'color' => '#000000')),
                    'position' => array_merge($position, array('pos_y' => 1118, 'color' => '#000000')),
                    'unit'     => array_merge($unit,     array('pos_y' => 1178, 'color' => '#000000')),
                ),
            ),

            'back' => array(
                'qr'         => array('pos_x' => 281, 'pos_y' => 420, 'size' => 500),
                'lucky_btc'  => array('pos_x' => 0, 'pos_y' => 980, 'font' => 'lato-black', 'size' => 56, 'align' => 'C', 'color' => '#FFFFFF'),
                'lucky'      => array('pos_x' => 0, 'pos_y' => 980, 'font' => 'lato-black', 'size' => 56, 'align' => 'C', 'color' => '#000000'),
            ),
        );
    }

    /**
     * Tạo ảnh thẻ VCK cho một người, gộp mặt trước + mặt sau cạnh nhau vào 1 file PNG.
     *
     * @param array  $row          Một dòng roster: full_name, position_display/position,
     *                             badge_org_name/unit_label, lucky_number, is_btc, property_code,
     *                             và avatar_url (URL ảnh chân dung).
     * @param string $loginBaseUrl URL gốc trang đăng nhập cổng cá nhân (QR = base?lucky=<lucky>).
     * @return string|false        Đường dẫn file PNG đã tạo, hoặc false nếu lỗi.
     */
    public static function FinalRosterBadge($row, $loginBaseUrl)
    {
        $baseFolder   = Yii::app()->basePath . '/../uploads/';
        $templateDir  = $baseFolder . self::BADGE_TEMPLATE_DIR;
        $layout       = self::finalBadgeLayout();
        $isBtc        = !empty($row['is_btc']);

        $frontTpl = $templateDir . ($isBtc ? 'phoi_the_btc_mt.png' : 'phoi_the_ks_mt.png');
        $backTpl  = $templateDir . ($isBtc ? 'phoi_the_btc_ms.png' : 'phoi_the_ks_ms.png');
        $blankTpl = $templateDir . 'blank_image.png';

        $front = self::loadPngCanvas($blankTpl);
        $back  = self::loadPngCanvas($blankTpl);
        if (!$front || !$back) {
            return false;
        }

        // ---- Mặt trước: ảnh chân dung → phôi → text ----
        $photo = self::fetchPortraitResource(
            isset($row['avatar_url']) ? $row['avatar_url'] : '',
            $layout['photo']['width'],
            $layout['photo']['height']
        );
        if ($photo) {
            imagecopy(
                $front, $photo,
                $layout['photo']['pos_x'], $layout['photo']['pos_y'],
                0, 0,
                $layout['photo']['width'], $layout['photo']['height']
            );
            imagedestroy($photo);
        }
        self::overlayPng($front, $frontTpl);

        $fullName = isset($row['full_name']) ? mb_strtoupper(trim($row['full_name']), 'UTF-8') : '';
        $position = '';
        if (!empty($row['position_display'])) {
            $position = $row['position_display'];
        } elseif (!empty($row['position'])) {
            $position = $row['position'];
        }
        $unit = '';
        if (!empty($row['badge_org_name'])) {
            $unit = $row['badge_org_name'];
        } elseif (!empty($row['unit_label'])) {
            $unit = $row['unit_label'];
        }

        $frontCfg = $isBtc ? $layout['front']['btc'] : $layout['front']['default'];
        self::drawText($front, $fullName, $frontCfg['name']);
        self::drawText($front, $position, $frontCfg['position']);
        self::drawText($front, $unit, $frontCfg['unit']);

        // ---- Mặt sau: phôi → QR → text MT+lucky ----
        self::overlayPng($back, $backTpl);

        $lucky = isset($row['lucky_number']) ? trim((string) $row['lucky_number']) : '';
        if ($lucky !== '') {
            $qrUrl = rtrim($loginBaseUrl, '/') . '/portal/login?lucky=' . rawurlencode($lucky);
            $qr    = self::makeQrResource($qrUrl, $layout['back']['qr']['size']);
            if ($qr) {
                imagecopy(
                    $back, $qr,
                    $layout['back']['qr']['pos_x'], $layout['back']['qr']['pos_y'],
                    0, 0,
                    imagesx($qr), imagesy($qr)
                );
                imagedestroy($qr);
            }
            self::drawText($back, 'MT' . $lucky, $isBtc ? $layout['back']['lucky_btc'] : $layout['back']['lucky']);
        }

        // ---- Gộp cạnh nhau: trước | sau ----
        $combined = self::combineSideBySide($front, $back);
        imagedestroy($front);
        imagedestroy($back);
        if (!$combined) {
            return false;
        }

        // Chia thư mục theo mã đơn vị
        $propertyCode = isset($row['property_code']) && $row['property_code'] !== ''
            ? UrlTransliterate::cleanString($row['property_code'])
            : 'khac';
        $outDir = $baseFolder . 'final_badges/' . $propertyCode . '/';
        if (!is_dir($outDir)) {
            mkdir($outDir, 0755, true);
        }

        $idPart   = isset($row['id']) ? (int) $row['id'] : 0;
        $namePart = UrlTransliterate::cleanString(isset($row['full_name']) ? $row['full_name'] : 'nguoi');
        $path     = $outDir . $namePart . '-' . $idPart . '.png';

        imagepng($combined, $path);
        imagedestroy($combined);

        return is_file($path) ? $path : false;
    }

    /** Nạp một ảnh PNG thành canvas truecolor có alpha để chỉnh sửa. */
    protected static function loadPngCanvas($path)
    {
        if (!is_file($path)) {
            return false;
        }
        $src = @imagecreatefrompng($path);
        if (!$src) {
            return false;
        }
        $w = imagesx($src);
        $h = imagesy($src);
        $canvas = imagecreatetruecolor($w, $h);
        imagealphablending($canvas, false);
        imagesavealpha($canvas, true);
        $white = imagecolorallocate($canvas, 255, 255, 255);
        imagefilledrectangle($canvas, 0, 0, $w, $h, $white);
        imagealphablending($canvas, true);
        imagecopy($canvas, $src, 0, 0, 0, 0, $w, $h);
        imagedestroy($src);
        return $canvas;
    }

    /** Đè một ảnh phôi PNG (giữ alpha) lên canvas tại (0,0). */
    protected static function overlayPng($canvas, $templatePath)
    {
        if (!is_file($templatePath)) {
            return;
        }
        $overlay = @imagecreatefrompng($templatePath);
        if (!$overlay) {
            return;
        }
        imagealphablending($canvas, true);
        imagecopy($canvas, $overlay, 0, 0, 0, 0, imagesx($overlay), imagesy($overlay));
        imagedestroy($overlay);
    }

    /**
     * Tải ảnh chân dung từ URL và cắt/thu về đúng kích thước (cover, giữ tỷ lệ, crop giữa).
     * Trả về GD resource hoặc false.
     */
    protected static function fetchPortraitResource($url, $targetW, $targetH)
    {
        if (!$url) {
            return false;
        }
        $bytes = @file_get_contents(urldecode($url));
        if ($bytes === false || $bytes === '') {
            return false;
        }
        $src = @imagecreatefromstring($bytes);
        if (!$src) {
            return false;
        }

        $srcW = imagesx($src);
        $srcH = imagesy($src);
        $scale = max($targetW / $srcW, $targetH / $srcH);
        $cropW = (int) round($targetW / $scale);
        $cropH = (int) round($targetH / $scale);
        $srcX  = (int) round(($srcW - $cropW) / 2);
        $srcY  = (int) round(($srcH - $cropH) / 2);

        $dst = imagecreatetruecolor($targetW, $targetH);
        imagecopyresampled($dst, $src, 0, 0, $srcX, $srcY, $targetW, $targetH, $cropW, $cropH);
        imagedestroy($src);
        return $dst;
    }

    /**
     * Sinh QR code thành GD resource vuông kích thước $size.
     * Dùng extension qrcode (protected/extensions/qrcode/QRCode.php).
     */
    protected static function makeQrResource($data, $size)
    {
        $classFile = Yii::getPathOfAlias('ext.qrcode.QRCode') . '.php';
        if (!class_exists('QRCode', false) && is_file($classFile)) {
            require_once($classFile);
        }
        if (!class_exists('QRCode', false)) {
            return false;
        }

        $tmp = Yii::app()->basePath . '/../uploads/final_badges/_tmp_qr_' . substr(md5($data . microtime()), 0, 10) . '.png';
        $dir = dirname($tmp);
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        try {
            $qr = new QRCode($data);
            $qr->error_correct = 'M';
            $qr->module_size   = 10;
            $qr->image_type    = 'P';
            $qr->create($tmp);
        } catch (Exception $e) {
            Yii::log('Lỗi sinh QR thẻ VCK: ' . $e->getMessage(), CLogger::LEVEL_ERROR);
            return false;
        }

        if (!is_file($tmp)) {
            return false;
        }
        $raw = @imagecreatefrompng($tmp);
        @unlink($tmp);
        if (!$raw) {
            return false;
        }

        $dst = imagecreatetruecolor($size, $size);
        $white = imagecolorallocate($dst, 255, 255, 255);
        imagefilledrectangle($dst, 0, 0, $size, $size, $white);
        imagecopyresampled($dst, $raw, 0, 0, 0, 0, $size, $size, imagesx($raw), imagesy($raw));
        imagedestroy($raw);
        return $dst;
    }

    /**
     * Vẽ text TTF lên canvas theo cấu hình layout (hỗ trợ align L/C/R và màu hex).
     * $cfg: pos_x, pos_y, font, size, align, color.
     */
    protected static function drawText($canvas, $text, $cfg)
    {
        $text = trim((string) $text);
        if ($text === '') {
            return;
        }

        $fontPath = self::badgeFontPath($cfg['font']);
        if (!$fontPath) {
            return;
        }

        $color = isset($cfg['color']) ? $cfg['color'] : '#000000';
        $rgb   = self::hexToRgb($color);
        $col   = imagecolorallocate($canvas, $rgb[0], $rgb[1], $rgb[2]);

        $size  = $cfg['size'];
        $align = isset($cfg['align']) ? $cfg['align'] : 'L';
        $box   = imagettfbbox($size, 0, $fontPath, $text);
        $textW = abs($box[2] - $box[0]);

        $x = (int) $cfg['pos_x'];
        if ($align === 'C') {
            $x = (int) round((imagesx($canvas) - $textW) / 2);
        } elseif ($align === 'R') {
            $x = imagesx($canvas) - $textW - (int) $cfg['pos_x'];
        }

        // pos_y là toạ độ đỉnh chữ (giống imagemod); baseline = pos_y + chiều cao chữ.
        $ascent = abs($box[7]);
        $y = (int) $cfg['pos_y'] + $ascent;

        imagettftext($canvas, $size, 0, $x, $y, $col, $fontPath, $text);
    }

    /** Đường dẫn file font, fallback về times.ttf nếu font chỉ định không tồn tại. */
    protected static function badgeFontPath($font)
    {
        $path = Yii::app()->basePath . '/../fonts/' . $font . '.ttf';
        if (is_file($path)) {
            return $path;
        }
        $fallback = Yii::app()->basePath . '/data/fonts/times.ttf';
        return is_file($fallback) ? $fallback : false;
    }

    /** '#RRGGBB' → array(r,g,b). */
    protected static function hexToRgb($hex)
    {
        $hex = ltrim($hex, '#');
        if (strlen($hex) === 3) {
            $hex = $hex[0] . $hex[0] . $hex[1] . $hex[1] . $hex[2] . $hex[2];
        }
        return array(
            hexdec(substr($hex, 0, 2)),
            hexdec(substr($hex, 2, 2)),
            hexdec(substr($hex, 4, 2)),
        );
    }

    /** Khoảng cách (px) giữa hai mặt thẻ khi ghép cạnh nhau. */
    const BADGE_GAP = 30;

    /** Ghép hai canvas cạnh nhau (trái | khoảng trắng | phải) thành một canvas mới. */
    protected static function combineSideBySide($left, $right, $gap = self::BADGE_GAP)
    {
        $lw = imagesx($left);
        $lh = imagesy($left);
        $rw = imagesx($right);
        $rh = imagesy($right);

        $w = $lw + $gap + $rw;
        $h = max($lh, $rh);

        $out = imagecreatetruecolor($w, $h);
        $white = imagecolorallocate($out, 255, 255, 255);
        imagefilledrectangle($out, 0, 0, $w, $h, $white);
        imagecopy($out, $left, 0, 0, 0, 0, $lw, $lh);
        imagecopy($out, $right, $lw + $gap, 0, 0, 0, $rw, $rh);
        return $out;
    }

    public static function downloadImage($name, $imageUrl, $folderPath)
    {
        $baseFolder = Yii::app()->basePath . '/../uploads/';
        if (!is_dir($baseFolder)) {
            mkdir($baseFolder);
        }
        $folder = $baseFolder . $folderPath . '/';
        if (!is_dir($folder)) {
            mkdir($folder);
        }
        if (@file_get_contents(urldecode($imageUrl)) === false) {
            return 'no-videopic.jpg';
        } else {
            $img = file_get_contents(urldecode($imageUrl));
            $name = self::cleanString($name);
            $uni = $name . '-' . substr(md5(time()), 28, 5) . '.png';
            $path = $folder . $uni;
            file_put_contents($path, $img);
            $im = imagecreatefromstring($img);
            return $path;
        }
    }

    public static function createFirstPic($imageGeneral, $name, $folder, $hotelId)
    {
        $path = '';
        $baseFolder = Yii::app()->basePath . '/../uploads/';
        if (!is_dir($baseFolder)) {
            mkdir($baseFolder);
        }
        $folder = $baseFolder . $folder . '/';
        if (!is_dir($folder)) {
            mkdir($folder);
        }
        $folder = $folder . 'hotel_' . $hotelId . '/';
        if (!is_dir($folder)) {
            mkdir($folder, 0755, true);
        }
        $path = $folder . self::cleanString($name) . '-' . substr(md5(time()), 28, 5) . '.jpg';
        copy($imageGeneral, $path);
        return $path;
    }

    public static function createFirstPicPNG($imageGeneral, $name, $folder, $hotelId)
    {
        $path = '';
        $baseFolder = Yii::app()->basePath . '/../uploads/';
        if (!is_dir($baseFolder)) {
            mkdir($baseFolder);
        }
        $folder = $baseFolder . $folder . '/';
        if (!is_dir($folder)) {
            mkdir($folder);
        }
        $folder = $folder . 'hotel_' . $hotelId . '/';
        if (!is_dir($folder)) {
            mkdir($folder, 0755, true);
        }
        $path = $folder . self::cleanString($name) . '-' . substr(md5(time()), 28, 5) . '.png';
        copy($imageGeneral, $path);
        return $path;
    }

    public static function insertImage($imageGeneral, $imageInsert, $position, $des, $name)
    {
        $baseFolder = Yii::app()->basePath . '/../uploads/';
        $folder = $baseFolder . $des . '/';
        $image = Yii::app()->imagemod->load($imageGeneral);
        if ($image->uploaded) {
            $image->image_resize = false;
            $image->image_watermark = $imageInsert;
            $image->image_watermark_position = 'L';
            $image->image_watermark_x = $position['pos_x'];
            $image->image_watermark_y = $position['pos_y'];
            $image->image_watermark_no_zoom_in = true;
            $image->image_greyscale = false;
            $newName = self::cleanString($name) . '-' . substr(md5(self::RandomString()), 28, 5);
            $image->file_new_name_body = $newName;
            $path = $folder . $newName . '.jpg';
            $image->Process($folder);
            if ($image->processed) {
                @unlink($imageGeneral); //delete original image
                return $path;
            } else {
                return false;
            }
        }
    }

    public static function insertImagePNG($imageGeneral, $imageInsert, $position, $des, $name)
    {
        $baseFolder = Yii::app()->basePath . '/../uploads/';
        $folder = $baseFolder . $des . '/';
        $image = Yii::app()->imagemod->load($imageGeneral);
        if ($image->uploaded) {
            $image->image_resize = false;
            $image->image_watermark = $imageInsert;
            $image->image_watermark_position = 'L';
            $image->image_watermark_x = $position['pos_x'];
            $image->image_watermark_y = $position['pos_y'];
            $image->image_watermark_no_zoom_in = true;
            $image->image_greyscale = false;
            $newName = self::cleanString($name) . '-' . substr(md5(self::RandomString()), 28, 5);
            $image->file_new_name_body = $newName;
            $path = $folder . $newName . '.png';
            $image->Process($folder);
            if ($image->processed) {
                @unlink($imageGeneral); //delete original image
                return $path;
            } else {
                return false;
            }
        }
    }

    public static function resizeImage($imageGeneral, $size, $des, $name, $rotate = false, $crop = false, $autoheight = false)
    {
        $baseFolder = Yii::app()->basePath . '/../uploads/';
        $folder = $baseFolder . $des . '/';
        $image = Yii::app()->imagemod->load($imageGeneral);
        if ($image->uploaded) {
            $image->image_resize = true;
            if ($crop) {
                $image->image_ratio_crop = 'T';
            }
            $image->image_x = $size['width'];
            if ($autoheight) {
                $image->image_ratio_y = true;
            } else {
                $image->image_y = $size['height'];
            }
            if ($rotate) {
                $image->image_rotate = '90';
            }
            $newName = self::cleanString($name) . '-' . substr(md5(self::RandomString()), 28, 5);
            $image->file_new_name_body = $newName;
            $path = $folder . $newName . '.' . $image->file_src_name_ext;
            $image->Process($folder);
            if ($image->processed) {
                @unlink($imageGeneral); //delete original image
                return $path;
            } else {
                return false;
            }
        }
    }

    public static function resolutionImage($imageGeneral, $size, $des, $name)
    {
        $image = file_get_contents($imageGeneral);
        $image = substr_replace($image, pack("Cnn", 0x01, 300, 300), 13, 5);
        $image = file_put_contents($imageGeneral, $image);
    }

    public
    static function insertTextPNG($imageGeneral, $text, $position, $des, $name, $isModify, $color = '#000000')
    {
        $baseFolder = Yii::app()->basePath . '/../uploads/';
        $folder = $baseFolder . $des . '/';
        $image = Yii::app()->imagemod->load($imageGeneral);
        if ($image->uploaded) {
            $image->image_resize = false;
            $image->image_overlay_opacity = 0;
            if ($isModify) {
                $image->image_text = mb_strtoupper($text, 'UTF-8') . ' ';
            } else {
                $image->image_text = $text;
            }

            $image->image_text_color = $color;
            $image->image_text_size = $position['font-size'];
            $image->image_text_x = $position['pos_x'];
            $image->image_text_y = $position['pos_y'];
            $image->image_text_padding = 5;
            // $image->image_text_padding_x  = 30;
            $image->image_text_font = Yii::app()->basePath . "/../fonts/" . $position['font'] . ".ttf";
            if (isset($position['align']) && $position['align'] != '') {
                $image->image_text_alignment = $position['align'];
            }
            $image->image_text_line_spacing = 3;
            $name = self::cleanString($name);
            $newName = self::cleanString($name) . '-' . substr(md5(self::RandomString()), 28, 5);
            $image->file_new_name_body = $newName;
            $path = $folder . $newName . '.png';
            $image->Process($folder);

            if ($image->processed) {
                // echo $image->log;
                @unlink($imageGeneral); //delete original image
                return $path;
            } else {
                return false;
            }
        }
    }


    public
    static function insertText($imageGeneral, $text, $position, $des, $name, $isModify, $color = '#000000')
    {
        $baseFolder = Yii::app()->basePath . '/../uploads/';
        $folder = $baseFolder . $des . '/';
        $image = Yii::app()->imagemod->load($imageGeneral);
        if ($image->uploaded) {
            $image->image_resize = false;
            $image->image_overlay_opacity = 0;
            if ($isModify) {
                $image->image_text = mb_strtoupper($text, 'UTF-8') . ' ';
            } else {
                $image->image_text = $text;
            }

            $image->image_text_color = $color;
            $image->image_text_size = $position['font-size'];
            $image->image_text_x = $position['pos_x'];
            $image->image_text_y = $position['pos_y'];
            $image->image_text_padding = 5;
            // $image->image_text_padding_x  = 30;
            $image->image_text_font = Yii::app()->basePath . "/../fonts/" . $position['font'] . ".ttf";
            if (isset($position['align']) && $position['align'] != '') {
                $image->image_text_alignment = $position['align'];
            }
            $image->image_text_line_spacing = 3;
            $name = self::cleanString($name);
            $newName = self::cleanString($name) . '-' . substr(md5(self::RandomString()), 28, 5);
            $image->file_new_name_body = $newName;
            $path = $folder . $newName . '.jpg';
            $image->Process($folder);
            echo $image->log;
            if ($image->processed) {
                @unlink($imageGeneral); //delete original image
                return $path;
            } else {
                return false;
            }
        }
    }

    public
    static function modifyImage($img, $name, $text, $imageInsert, $des, $childDes)
    {
        $baseFolder = Yii::app()->basePath . '/../uploads/';
        if (!is_dir($baseFolder)) {
            mkdir($baseFolder);
        }
        $folder = $baseFolder . $des . '/';
        if (!is_dir($folder)) {
            mkdir($folder);
        }
        $folder = $folder . 'hotel_' . $childDes . '/';
        if (!is_dir($folder)) {
            mkdir($folder, 0755, true);
        }
        $image = Yii::app()->imagemod->load($img);
        if ($image->uploaded) {
            $image->image_resize = false;
            $image->image_text = mb_strtoupper($text['name'], 'UTF-8');
            $image->image_text_color = '#ffffff';
            $image->image_text_size = 30;
            $image->image_text_position = 'br';
            $image->image_text_font = Yii::app()->basePath . "/../fonts/HJAvantGardeBold_0.ttf";
            $name = self::cleanString($name);
            $uni = $name . '-' . substr(md5(self::RandomString()), 28, 5);
            $image->file_new_name_body = $uni;
            if ($image->processed) {
                return 1;
                $image->clean(); //delete original image
            } else {
                return 0;
            }
        }
    }
}
?>