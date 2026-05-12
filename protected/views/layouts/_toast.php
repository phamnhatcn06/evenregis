<?php
/**
 * Toast Notification Partial
 * Include trong layout để hiển thị flash messages dạng Toast
 *
 * Usage trong layout:
 * <?php $this->renderPartial('//layouts/_toast'); ?>
 */

Yii::app()->clientScript->registerScriptFile(
    Yii::app()->theme->baseUrl . '/assets/js/plugins/toast.js',
    CClientScript::POS_END
);

$flashMessages = Yii::app()->user->getFlashes();
if (!empty($flashMessages)):
?>
<script>
document.addEventListener('DOMContentLoaded', function() {
    <?php foreach ($flashMessages as $type => $message):
        $toastType = 'info';
        if ($type === 'success') $toastType = 'success';
        elseif ($type === 'error' || $type === 'danger') $toastType = 'error';
        elseif ($type === 'warning') $toastType = 'warning';
    ?>
    Toast.<?php echo $toastType; ?>('<?php echo addslashes($message); ?>');
    <?php endforeach; ?>
});
</script>
<?php endif; ?>
