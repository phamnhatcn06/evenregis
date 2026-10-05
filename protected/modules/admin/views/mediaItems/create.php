<?php
$this->breadcrumbs = array(
    'Thư viện' => $this->createUrl('/admin/mediaAlbums/admin'),
    CHtml::encode($album->title) => $this->createUrl('admin', array('album_id' => $album->id)),
    'Thêm mục',
);
$this->menu = array(
    array('label' => 'Về danh sách mục', 'labelIcon' => 'Về danh sách mục', 'url' => $this->createUrl('admin', array('album_id' => $album->id)), 'color' => 'primary', 'icon' => 'fa-th', 'id' => 'btn_manage'),
);
$this->Tabletitle = 'Thêm mục vào album: ' . CHtml::encode($album->title);
?>
<div class="card"><div class="card-body">
    <?php $this->renderPartial('_form', array('model' => $model, 'album' => $album)); ?>
</div></div>
