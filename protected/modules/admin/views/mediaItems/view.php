<?php
$this->menu = array(
    array('label' => 'Về danh sách mục', 'labelIcon' => 'Về danh sách mục', 'url' => $this->createUrl('admin', array('album_id' => $model->album_id)), 'color' => 'primary', 'icon' => 'fa-th', 'id' => 'btn_manage'),
    array('label' => 'Cập nhật', 'labelIcon' => 'Cập nhật', 'url' => $this->createUrl('update', array('id' => $model->id)), 'color' => 'warning', 'icon' => 'fa-pencil', 'id' => 'btn_update'),
);
$this->breadcrumbs = array(
    'Thư viện' => $this->createUrl('/admin/mediaAlbums/admin'),
    'Mục #' . CHtml::encode($model->id),
);
$this->Tabletitle = 'Chi tiết mục #' . CHtml::encode($model->id);

$attributes = array(
    array('label' => 'ID', 'value' => $model->id),
    array('label' => 'Album', 'value' => $model->album_id),
    array('label' => 'Loại', 'value' => $model->type === 'video' ? 'Video' : 'Ảnh'),
    array('label' => 'Tiêu đề', 'value' => $model->title),
    array('label' => 'Thứ tự', 'value' => $model->sort_order),
    array('label' => 'Hiển thị', 'value' => $model->is_active ? '<span class="badge bg-success">Bật</span>' : '<span class="badge bg-secondary">Tắt</span>', 'raw' => true),
);
?>
<div class="card"><div class="card-body">
    <table class="table table-bordered table-striped">
        <tbody>
        <?php foreach ($attributes as $attr): ?>
            <tr>
                <th style="width:30%;background:#f8f9fa;"><?php echo CHtml::encode($attr['label']); ?></th>
                <td><?php echo isset($attr['raw']) && $attr['raw'] ? $attr['value'] : CHtml::encode($attr['value']); ?></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>

    <?php if (!empty($model->caption)): ?>
    <div class="mt-3"><h6>Chú thích</h6><div class="border rounded p-3 bg-light"><?php echo CHtml::encode($model->caption); ?></div></div>
    <?php endif; ?>

    <div class="mt-3">
        <h6>Xem trước</h6>
        <?php if ($model->type === 'image' && $model->url): ?>
            <img src="<?php echo CHtml::encode($model->url); ?>" alt="item" style="max-width:480px;" class="img-fluid rounded border">
        <?php elseif ($model->url): ?>
            <a href="<?php echo CHtml::encode($model->url); ?>" target="_blank" rel="noopener"><?php echo CHtml::encode($model->url); ?></a>
        <?php endif; ?>
    </div>
</div></div>
