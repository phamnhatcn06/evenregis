<?php
$this->menu = array(
    array(
        'label' => Yii::t('app', 'Manage') . ' ' . $model->label(2),
        'labelIcon' => Yii::t('app', 'Manage'),
        'url' => $this->createUrl('admin'),
        'color' => 'primary',
        'icon' => 'fa-th',
        'id' => 'btn_manage',
    ),
    array(
        'label' => Yii::t('app', 'Create') . ' ' . $model->label(),
        'labelIcon' => Yii::t('app', 'Create'),
        'url' => $this->createUrl('create'),
        'color' => 'success',
        'icon' => 'fa-plus',
        'id' => 'btn_create',
    ),
    array(
        'label' => Yii::t('app', 'Update') . ' ' . $model->label(),
        'labelIcon' => Yii::t('app', 'Update'),
        'url' => $this->createUrl('update', array('id' => $model->id)),
        'color' => 'warning',
        'icon' => 'fa-pencil',
        'id' => 'btn_update',
    ),
);

$this->breadcrumbs = array(
    Regionals::label(2) => array('admin'),
    Yii::t('app', 'View') . ': ' . $model->name,
);

$this->Tabletitle = Yii::t('app', 'View') . ' ' . $model->label() . ': ' . CHtml::encode($model->name);

Yii::app()->clientScript->registerScriptFile(
    Yii::app()->theme->baseUrl . '/assets/js/pages/regionals-view.js',
    CClientScript::POS_END
);
?>

<div class="row">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Thông tin khu vực</h5>
            </div>
            <div class="card-body">
                <table class="table table-bordered table-striped">
                    <tbody>
                        <tr>
                            <th style="background:#f8f9fa;">Mã khu vực</th>
                            <td><?php echo CHtml::encode($model->code); ?></td>
                        </tr>
                        <tr>
                            <th style="background:#f8f9fa;">Tên khu vực</th>
                            <td><?php echo CHtml::encode($model->name); ?></td>
                        </tr>
                        <tr>
                            <th style="background:#f8f9fa;">Mô tả</th>
                            <td><?php echo CHtml::encode($model->description); ?></td>
                        </tr>
                        <tr>
                            <th style="background:#f8f9fa;">Trạng thái</th>
                            <td>
                                <?php if ($model->status == 1): ?>
                                    <span class="badge bg-success">Hoạt động</span>
                                <?php else: ?>
                                    <span class="badge bg-secondary">Không hoạt động</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="col-md-6">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0">Danh sách đơn vị (<?php echo count($organizations); ?>)</h5>
                <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#assignOrganizationsModal">
                    <i class="fa fa-plus"></i> Cập nhật đơn vị
                </button>
            </div>
            <div class="card-body">
                <?php if (!empty($organizations)): ?>
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover">
                            <thead>
                                <tr>
                                    <th>STT</th>
                                    <th>Mã đơn vị</th>
                                    <th>Tên đơn vị</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($organizations as $index => $org): ?>
                                    <tr>
                                        <td><?php echo $index + 1; ?></td>
                                        <td><?php echo CHtml::encode($org['code']); ?></td>
                                        <td><?php echo CHtml::encode($org['name']); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <p class="text-muted text-center">Chưa có đơn vị nào trong khu vực này.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Modal -->
<div class="modal fade" id="assignOrganizationsModal" tabindex="-1" aria-labelledby="assignOrganizationsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form method="post" action="<?php echo $this->createUrl('assignOrganizations', array('id' => $model->id)); ?>">
                <div class="modal-header">
                    <h5 class="modal-title" id="assignOrganizationsModalLabel">Quản lý đơn vị trong khu vực</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-5">
                            <label class="form-label fw-bold">Đơn vị chưa gán</label>
                            <select id="availableOrgs" class="form-select" size="15" multiple>
                            </select>
                        </div>
                        <div class="col-md-2 d-flex flex-column justify-content-center align-items-center">
                            <button type="button" class="btn btn-outline-primary btn-sm mb-2" id="btnAddSelected">
                                <i class="fa fa-arrow-right"></i>
                            </button>
                            <button type="button" class="btn btn-outline-primary btn-sm mb-2" id="btnAddAll">
                                <i class="fa fa-arrow-right"></i><i class="fa fa-arrow-right"></i>
                            </button>
                            <button type="button" class="btn btn-outline-secondary btn-sm mb-2" id="btnRemoveSelected">
                                <i class="fa fa-arrow-left"></i>
                            </button>
                            <button type="button" class="btn btn-outline-secondary btn-sm" id="btnRemoveAll">
                                <i class="fa fa-arrow-left"></i><i class="fa fa-arrow-left"></i>
                            </button>
                        </div>
                        <div class="col-md-5">
                            <label class="form-label fw-bold">Đơn vị đã gán</label>
                            <select id="assignedOrgs" class="form-select" size="15" multiple>
                                <?php foreach ($organizations as $org): ?>
                                    <option value="<?php echo $org['id']; ?>"><?php echo CHtml::encode($org['code'] . ' - ' . $org['name']); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div id="hiddenInputs"></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
                    <button type="submit" class="btn btn-primary" id="btnSave">Lưu thay đổi</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    var regionalId = <?php echo $model->id; ?>;
    var assignedOrgIds = <?php echo json_encode(array_column($organizations, 'id')); ?>;
    var allPropertiesUrl = '<?php echo $this->createUrl('/admin/properties/listJson'); ?>';
</script>