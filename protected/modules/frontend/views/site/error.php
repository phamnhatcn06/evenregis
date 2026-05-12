<?php
$this->pageTitle = 'Lỗi ' . $code . ' - ' . Yii::app()->name;
?>

<div class="text-center py-5">
    <h1 class="display-1 text-danger"><?php echo $code; ?></h1>
    <h2><?php echo CHtml::encode($message); ?></h2>
    <p class="text-muted">Xin lỗi, đã xảy ra lỗi khi xử lý yêu cầu của bạn.</p>
    <a href="<?php echo Yii::app()->homeUrl; ?>" class="btn btn-primary">Về trang chủ</a>
</div>
