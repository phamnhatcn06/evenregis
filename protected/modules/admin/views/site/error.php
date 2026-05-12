<?php
$this->pageTitle = 'Lỗi ' . $code . ' - Admin';
?>

<div class="text-center py-5">
    <h1 class="display-1 text-danger"><?php echo $code; ?></h1>
    <h2><?php echo CHtml::encode($message); ?></h2>
    <p class="text-muted">Đã xảy ra lỗi.</p>
    <a href="<?php echo $this->createUrl('/admin/site/index'); ?>" class="btn btn-dark">Dashboard</a>
</div>
