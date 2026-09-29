<?php
/**
 * Modal Bổ sung người tham dự (chỉ quản trị toàn quyền).
 * Cột trái: hồ sơ người bổ sung (chọn nhân sự SMILE / người có sẵn / nhập thủ công + ảnh, hồ sơ, vai trò).
 * Cột phải: các NỘI DUNG ĐÃ CÓ trên phiếu — tích để gán người bổ sung vào.
 *
 * @var Registrations $model
 * @var array $roles                    role_id => role_name
 * @var array $staffList                [ [id,name,position,code], ... ]
 * @var array $otherAttendees           [ [id,name,position,id_card,in_current], ... ]
 * @var array $sportTeams               danh sách đội (object: id, sport_name, team_name)
 * @var array $competitionRegistrations [comp_id => [competition_id, competition_name, ...]]
 * @var array $beautyContestants        [contest_id => [contest_id, contest_name, ...]]
 * @var array $talentEntries            danh sách tiết mục (object/array: id, title, category_name)
 * @var array $allianceTalentEntries    tiết mục liên quân
 */

$val = function ($item, $key, $default = '') {
    if (is_array($item)) {
        return isset($item[$key]) ? $item[$key] : $default;
    }
    return isset($item->$key) ? $item->$key : $default;
};

$fileFields = array(
    array('k' => 'portrait',   'label' => 'Ảnh chân dung', 'req' => true,  'pdf' => false),
    array('k' => 'cccd_front', 'label' => 'CCCD trước',    'req' => false, 'pdf' => false),
    array('k' => 'cccd_back',  'label' => 'CCCD sau',      'req' => false, 'pdf' => false),
    array('k' => 'contract',   'label' => 'Hợp đồng',      'req' => false, 'pdf' => true),
);
?>
<div class="modal fade" id="addAttendeeModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title"><i class="fa fa-user-plus me-2"></i>Bổ sung người tham dự</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Đóng"></button>
            </div>
            <form id="addAttendeeForm" enctype="multipart/form-data" class="d-flex flex-column overflow-hidden" style="min-height:0;">
                <div class="modal-body flex-grow-1 overflow-auto" style="min-height:0;">
                    <input type="hidden" name="registration_id" value="<?php echo $model->id; ?>">
                    <input type="hidden" name="event_id" value="<?php echo $model->event_id; ?>">
                    <input type="hidden" name="property_id" value="<?php echo $model->property_id; ?>">
                    <input type="hidden" name="existing_attendee_id" id="add_existing_id">

                    <div class="row g-3">
                        <!-- Cột trái: hồ sơ người bổ sung -->
                        <div class="col-md-6">
                            <div class="card h-100">
                                <div class="card-header bg-light"><strong><i class="fa fa-user me-1"></i>Người bổ sung</strong></div>
                                <div class="card-body">
                                    <div class="mb-2">
                                        <label class="form-label small mb-1">Nhân sự SMILE</label>
                                        <select class="form-select form-select-sm" id="add_staff">
                                            <option value="">-- Chọn nhân sự SMILE --</option>
                                            <?php foreach ($staffList as $st): ?>
                                                <option value="<?php echo CHtml::encode($val($st, 'id')); ?>"
                                                        data-name="<?php echo CHtml::encode($val($st, 'name')); ?>"
                                                        data-code="<?php echo CHtml::encode($val($st, 'code')); ?>"
                                                        data-position="<?php echo CHtml::encode($val($st, 'position')); ?>">
                                                    <?php echo CHtml::encode($val($st, 'name') . ($val($st, 'code') ? ' (' . $val($st, 'code') . ')' : '')); ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <?php if (!empty($otherAttendees)): ?>
                                    <div class="mb-2">
                                        <label class="form-label small mb-1">Hoặc chọn người đã có của đơn vị</label>
                                        <select class="form-select form-select-sm" id="add_other">
                                            <option value="">-- Chọn người đã có --</option>
                                            <?php foreach ($otherAttendees as $a):
                                                $label = $val($a, 'name', '#' . $val($a, 'id'));
                                                if ($val($a, 'position')) { $label .= ' · ' . $val($a, 'position'); }
                                                if ($val($a, 'id_card')) { $label .= ' · CCCD ' . $val($a, 'id_card'); }
                                                if ($val($a, 'in_current')) { $label .= ' · (đăng ký hiện tại)'; }
                                            ?>
                                                <option value="<?php echo CHtml::encode($val($a, 'id')); ?>"
                                                        data-name="<?php echo CHtml::encode($val($a, 'name')); ?>"
                                                        data-position="<?php echo CHtml::encode($val($a, 'position')); ?>"
                                                        data-idcard="<?php echo CHtml::encode($val($a, 'id_card')); ?>"
                                                        data-incurrent="<?php echo $val($a, 'in_current') ? '1' : '0'; ?>">
                                                    <?php echo CHtml::encode($label); ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                        <small class="text-muted">Người ở đăng ký hiện tại sẽ được dùng lại, không tạo bản ghi mới.</small>
                                    </div>
                                    <?php endif; ?>

                                    <div class="row g-2 mb-2">
                                        <div class="col-12"><input type="text" class="form-control form-control-sm" id="add_name" placeholder="Hoặc nhập họ tên (thủ công)"></div>
                                        <div class="col-6"><input type="text" class="form-control form-control-sm" id="add_pos" placeholder="Chức danh"></div>
                                        <div class="col-6"><input type="text" class="form-control form-control-sm" id="add_idcard" placeholder="Số CCCD/CMND"></div>
                                    </div>

                                    <div class="mb-2">
                                        <label class="form-label small mb-1">Vai trò <span class="text-danger">*</span></label>
                                        <select class="form-select form-select-sm" id="add_roles" multiple size="3">
                                            <?php foreach ($roles as $rid => $rname): ?>
                                                <option value="<?php echo CHtml::encode($rid); ?>"><?php echo CHtml::encode($rname); ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                        <small class="text-muted">Giữ Ctrl để chọn nhiều.</small>
                                    </div>

                                    <div class="row g-2">
                                        <?php foreach ($fileFields as $f): ?>
                                            <div class="col-6 mb-2">
                                                <label class="form-label small fw-bold mb-1"><?php echo $f['label']; ?><?php echo $f['req'] ? ' <span class="text-danger">*</span>' : ''; ?></label>
                                                <div id="add_prev_<?php echo $f['k']; ?>" class="border rounded text-center p-1 mb-1" style="min-height:44px;"></div>
                                                <input type="file" class="form-control form-control-sm" name="sub_<?php echo $f['k']; ?>_file_0"
                                                       accept="image/*<?php echo $f['pdf'] ? ',.pdf' : ''; ?>"
                                                       onchange="addPreviewFile(this, 'add_prev_<?php echo $f['k']; ?>')">
                                                <input type="hidden" class="add-url" data-path="<?php echo $f['k']; ?>" id="add_url_<?php echo $f['k']; ?>">
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                    <div class="alert alert-success py-1 px-2 mt-1 small d-none" id="add_profile_alert"></div>
                                </div>
                            </div>
                        </div>

                        <!-- Cột phải: gán vào nội dung đã có -->
                        <div class="col-md-6">
                            <div class="card h-100">
                                <div class="card-header bg-light"><strong><i class="fa fa-list-check me-1"></i>Gán vào nội dung đã có</strong></div>
                                <div class="card-body">
                                    <p class="small text-muted mb-2">Tích chọn nội dung muốn gán người bổ sung vào. Có thể bỏ trống nếu chỉ thêm người.</p>

                                    <h6 class="mb-1"><i class="fa fa-futbol-o me-1 text-primary"></i>Đội thể thao</h6>
                                    <?php if (!empty($sportTeams)): ?>
                                        <?php foreach ($sportTeams as $team):
                                            $tid = $val($team, 'id'); if (!$tid) continue;
                                        ?>
                                            <div class="border rounded p-2 mb-1">
                                                <div class="form-check">
                                                    <input class="form-check-input add-assign-team" type="checkbox" name="assign_team[<?php echo $tid; ?>]" value="1" id="add_team_<?php echo $tid; ?>" data-id="<?php echo $tid; ?>">
                                                    <label class="form-check-label" for="add_team_<?php echo $tid; ?>">
                                                        <strong><?php echo CHtml::encode($val($team, 'sport_name', $val($team, 'team_name'))); ?></strong>
                                                        <small class="text-muted"><?php echo CHtml::encode($val($team, 'team_name')); ?></small>
                                                    </label>
                                                </div>
                                                <div class="row g-1 mt-1 add-team-opts d-none" data-team="<?php echo $tid; ?>">
                                                    <div class="col-4"><input type="text" class="form-control form-control-sm" name="team_jersey[<?php echo $tid; ?>]" placeholder="Số áo"></div>
                                                    <div class="col-5"><input type="text" class="form-control form-control-sm" name="team_position[<?php echo $tid; ?>]" placeholder="Vị trí"></div>
                                                    <div class="col-3 d-flex align-items-center">
                                                        <div class="form-check mb-0">
                                                            <input class="form-check-input" type="checkbox" name="team_captain[<?php echo $tid; ?>]" value="1" id="add_cap_<?php echo $tid; ?>">
                                                            <label class="form-check-label small" for="add_cap_<?php echo $tid; ?>">ĐT</label>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <p class="text-muted small mb-2">Không có đội thể thao.</p>
                                    <?php endif; ?>

                                    <h6 class="mb-1 mt-2"><i class="fa fa-trophy me-1 text-warning"></i>Thi nghiệp vụ</h6>
                                    <?php if (!empty($competitionRegistrations)): ?>
                                        <?php foreach ($competitionRegistrations as $comp):
                                            $cid = $val($comp, 'competition_id'); if (!$cid) continue;
                                        ?>
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" name="assign_comp[<?php echo $cid; ?>]" value="1" id="add_comp_<?php echo $cid; ?>">
                                                <label class="form-check-label" for="add_comp_<?php echo $cid; ?>"><?php echo CHtml::encode($val($comp, 'competition_name')); ?></label>
                                            </div>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <p class="text-muted small mb-2">Không có cuộc thi nghiệp vụ.</p>
                                    <?php endif; ?>

                                    <h6 class="mb-1 mt-2"><i class="fa fa-star me-1 text-danger"></i>Thi Miss</h6>
                                    <?php if (!empty($beautyContestants)): ?>
                                        <?php foreach ($beautyContestants as $bc):
                                            $bid = $val($bc, 'contest_id'); if (!$bid) continue;
                                        ?>
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" name="assign_beauty[<?php echo $bid; ?>]" value="1" id="add_beauty_<?php echo $bid; ?>">
                                                <label class="form-check-label" for="add_beauty_<?php echo $bid; ?>"><?php echo CHtml::encode($val($bc, 'contest_name')); ?></label>
                                            </div>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <p class="text-muted small mb-2">Không có cuộc thi Miss.</p>
                                    <?php endif; ?>

                                    <h6 class="mb-1 mt-2"><i class="fa fa-music me-1 text-info"></i>Văn nghệ</h6>
                                    <?php
                                    $allTalents = array_merge(
                                        is_array($talentEntries) ? $talentEntries : array(),
                                        isset($allianceTalentEntries) && is_array($allianceTalentEntries) ? $allianceTalentEntries : array()
                                    );
                                    ?>
                                    <?php if (!empty($allTalents)): ?>
                                        <?php foreach ($allTalents as $entry):
                                            $eid = $val($entry, 'id'); if (!$eid) continue;
                                            $etitle = $val($entry, 'title', $val($entry, 'entry_title'));
                                        ?>
                                            <div class="border rounded p-2 mb-1">
                                                <div class="form-check">
                                                    <input class="form-check-input add-assign-talent" type="checkbox" name="assign_talent[<?php echo $eid; ?>]" value="1" id="add_talent_<?php echo $eid; ?>" data-id="<?php echo $eid; ?>">
                                                    <label class="form-check-label" for="add_talent_<?php echo $eid; ?>">
                                                        <?php echo CHtml::encode($etitle); ?>
                                                        <?php if ($val($entry, 'category_name')): ?><span class="badge bg-secondary"><?php echo CHtml::encode($val($entry, 'category_name')); ?></span><?php endif; ?>
                                                    </label>
                                                </div>
                                                <div class="mt-1 add-talent-opts d-none" data-talent="<?php echo $eid; ?>">
                                                    <input type="text" class="form-control form-control-sm" name="talent_role[<?php echo $eid; ?>]" placeholder="Vai trò trong tiết mục (tuỳ chọn)">
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <p class="text-muted small mb-0">Không có tiết mục văn nghệ.</p>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
                    <button type="submit" class="btn btn-success" id="btn_submit_add">
                        <i class="fa fa-user-plus me-1"></i>Bổ sung người
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
