<?php
/**
 * Modal gắn thí sinh vào vòng thi
 * @var BeautyRounds $model
 * @var array $availableContestants
 */
?>
<div class="modal fade" id="modal_assign_contestant" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fa fa-user-plus me-2"></i>Thêm thí sinh vào vòng</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <input type="text" id="search_contestant" class="form-control"
                        placeholder="Tìm kiếm theo tên, SBD, đơn vị...">
                </div>
                <div class="table-responsive" style="max-height:400px;overflow-y:auto;">
                    <table class="table table-hover table-bordered" id="tbl_available_contestants">
                        <thead class="table-light sticky-top">
                            <tr>
                                <th style="width:50px">
                                    <input type="checkbox" id="check_all_contestants" class="form-check-input">
                                </th>
                                <th style="width:60px">Ảnh</th>
                                <th>SBD</th>
                                <th>Họ tên</th>
                                <th>Đơn vị</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($availableContestants)): ?>
                                <tr id="no_contestant_row">
                                    <td colspan="5" class="text-center text-muted">Không có thí sinh nào để thêm</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($availableContestants as $c): ?>
                                <tr class="contestant-row"
                                    data-search="<?php echo CHtml::encode(strtolower($c['contestant_name'] . ' ' . $c['contestant_number'] . ' ' . $c['property_name'])); ?>">
                                    <td class="text-center">
                                        <input type="checkbox" class="form-check-input contestant-check"
                                            value="<?php echo $c['id']; ?>">
                                    </td>
                                    <td class="text-center">
                                        <?php if (!empty($c['photo_portrait'])): ?>
                                            <img src="<?php echo CHtml::encode($c['photo_portrait']); ?>"
                                                class="rounded" style="width:40px;height:40px;object-fit:cover;">
                                        <?php else: ?>
                                            <i class="fa fa-user text-muted"></i>
                                        <?php endif; ?>
                                    </td>
                                    <td><strong><?php echo CHtml::encode($c['contestant_number']); ?></strong></td>
                                    <td><?php echo CHtml::encode($c['contestant_name']); ?></td>
                                    <td><?php echo CHtml::encode($c['property_name']); ?></td>
                                </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
                <div class="mt-2 text-muted">
                    Đã chọn: <span id="selected_contestant_count">0</span> thí sinh
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
                <button type="button" class="btn btn-primary" id="btn_assign_contestant">
                    <i class="fa fa-save me-1"></i>Thêm vào vòng
                </button>
            </div>
        </div>
    </div>
</div>

<input type="hidden" id="assign_contestant_url" value="<?php echo Yii::app()->createUrl('/admin/beautyRounds/assignContestants', array('id' => $model->id)); ?>">
