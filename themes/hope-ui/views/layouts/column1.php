<?php

/**
 * Created by PhpStorm.
 * User: Nhat.IT
 * Date: 02/01/2018
 * Time: 3:17 CH
 */
$this->beginContent('//layouts/main'); ?>
<div class="conatiner-fluid content-inner mt-n5 py-2">
    <div class="card mb-3">
        <div class="card-body py-3 d-flex justify-content-between align-items-center">
            <?php if (!empty($this->Tabletitle)): ?>
                <div class="breadcrumb-main mb-0">
                    <h4 class="card-title fw-bold mb-0"><?= CHtml::encode($this->Tabletitle); ?></h4>
                </div>
            <?php endif; ?>
            <div class="form-action d-flex justify-content-end align-items-center ms-auto" style="gap: 10px;">
                <?php
                if ($this->menu) {
                    MyHelper::renderActionMenu($this->menu);
                }
                ?>
            </div>
        </div>
    </div>
    <?php echo $content; ?>
</div>
<!-- ============================================================== -->
<!-- End Right content here -->
<!-- ============================================================== -->
<?php $this->endContent(); ?>