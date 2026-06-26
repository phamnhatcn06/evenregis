<?php
/**
 * Partial view: Chi tiết tiết mục văn nghệ
 *
 * @var TalentEntries $entry - Tiết mục văn nghệ
 * @var array $members - Danh sách thành viên của tiết mục
 * @var Registrations $model - Phiếu đăng ký hiện tại
 * @var bool $canEdit - Có quyền chỉnh sửa phiếu đăng ký
 * @var array $allAllianceMembers - Danh sách thành viên từ tất cả đơn vị liên quân
 */

$entryId = isset($entry->id) ? $entry->id : (isset($entry['id']) ? $entry['id'] : null);
$entryTitle = isset($entry->title) ? $entry->title : (isset($entry['title']) ? $entry['title'] : '-');
$categoryName = isset($entry->category_name) ? $entry->category_name : (isset($entry['category_name']) ? $entry['category_name'] : '-');
$entryOrigin = isset($entry->origin) ? $entry->origin : (isset($entry['origin']) ? $entry['origin'] : '');
$entryDescription = isset($entry->description) ? $entry->description : (isset($entry['description']) ? $entry['description'] : '');
$entryContent = isset($entry->content) ? $entry->content : (isset($entry['content']) ? $entry['content'] : '');
$videoPath = isset($entry->video_path) ? $entry->video_path : (isset($entry['video_path']) ? $entry['video_path'] : '');
$musicPath = isset($entry->music_path) ? $entry->music_path : (isset($entry['music_path']) ? $entry['music_path'] : '');
$entryPropertyId = isset($entry->property_id) ? $entry->property_id : (isset($entry['property_id']) ? $entry['property_id'] : null);

// Xác định đây có phải đơn vị chủ quản (owner) hay không
$isOwner = ($entryPropertyId == $model->property_id);
?>

<div class="card mb-3 talent-entry-card" id="talent-entry-<?php echo $entryId; ?>">
    <div class="card-header bg-light d-flex justify-content-between align-items-center py-2">
        <div>
            <span class="badge bg-info me-2"><?php echo CHtml::encode($categoryName); ?></span>
            <strong><?php echo CHtml::encode($entryTitle); ?></strong>
            <?php if ($entryOrigin): ?>
                <small class="text-muted ms-2">(<?php echo CHtml::encode($entryOrigin); ?>)</small>
            <?php endif; ?>
            <?php if (!$isOwner): ?>
                <span class="badge bg-secondary ms-2"><i class="fa fa-handshake-o me-1"></i>Liên quân</span>
            <?php endif; ?>
        </div>
        <?php if ($canEdit && $isOwner): ?>
            <div>
                <button type="button" class="btn btn-sm btn-outline-primary me-1" onclick="editTalentEntry(<?php echo $entryId; ?>)" title="Sửa thông tin">
                    <i class="fa fa-pencil"></i>
                </button>
                <form method="post" action="<?php echo $this->createUrl('deleteTalentEntry', array('id' => $entryId, 'registration_id' => $model->id)); ?>" id="delete-talent-form-<?php echo $entryId; ?>" style="display:none;"></form>
                <button type="button" class="btn btn-sm btn-outline-danger" onclick="confirmDeleteTalent(<?php echo $entryId; ?>)" title="Xóa tiết mục">
                    <i class="fa fa-trash"></i>
                </button>
            </div>
        <?php endif; ?>
    </div>
    <div class="card-body">
        <div class="row">
            <!-- Cột trái: Thông tin tiết mục (chỉ owner mới được sửa) -->
            <div class="col-md-6">
                <h6 class="border-bottom pb-2 mb-3"><i class="fa fa-info-circle me-1 text-primary"></i>Thông tin tiết mục</h6>

                <!-- Mô tả ý nghĩa tiết mục -->
                <div class="mb-3">
                    <label class="form-label fw-bold"><i class="fa fa-lightbulb-o me-1"></i>Mô tả ý nghĩa tiết mục</label>
                    <small class="text-muted d-block mb-1">(VD: nguồn gốc, ý nghĩa...)</small>
                    <?php if ($isOwner && $canEdit): ?>
                        <textarea class="form-control talent-description-input"
                            data-entry-id="<?php echo $entryId; ?>"
                            rows="3"
                            placeholder="Nhập mô tả ý nghĩa tiết mục..."><?php echo CHtml::encode($entryDescription); ?></textarea>
                    <?php else: ?>
                        <div class="bg-light p-2 rounded border">
                            <?php echo $entryDescription ? nl2br(CHtml::encode($entryDescription)) : '<span class="text-muted fst-italic">Chưa có mô tả</span>'; ?>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Nội dung chi tiết dàn dựng -->
                <div class="mb-3">
                    <label class="form-label fw-bold"><i class="fa fa-file-text-o me-1"></i>Nội dung chi tiết dàn dựng</label>
                    <small class="text-muted d-block mb-1">(VD: Kịch bản dàn dựng, ý đồ nghệ thuật...)</small>
                    <?php if ($isOwner && $canEdit): ?>
                        <textarea class="form-control talent-content-input"
                            data-entry-id="<?php echo $entryId; ?>"
                            rows="4"
                            placeholder="Nhập kịch bản dàn dựng, ý đồ nghệ thuật..."><?php echo CHtml::encode($entryContent); ?></textarea>
                    <?php else: ?>
                        <div class="bg-light p-2 rounded border">
                            <?php echo $entryContent ? nl2br(CHtml::encode($entryContent)) : '<span class="text-muted fst-italic">Chưa có nội dung</span>'; ?>
                        </div>
                    <?php endif; ?>
                </div>

                <?php if ($isOwner && $canEdit): ?>
                    <button type="button" class="btn btn-sm btn-primary btn-save-talent-info" data-entry-id="<?php echo $entryId; ?>">
                        <i class="fa fa-save me-1"></i>Lưu thông tin
                    </button>
                <?php endif; ?>
            </div>

            <!-- Cột phải: Video/Audio demo -->
            <div class="col-md-6">
                <h6 class="border-bottom pb-2 mb-3"><i class="fa fa-play-circle me-1 text-primary"></i>Video/Audio demo</h6>

                <!-- Video demo -->
                <div class="mb-3">
                    <label class="form-label fw-bold"><i class="fa fa-video-camera me-1"></i>Video demo</label>
                    <small class="text-muted d-block mb-1">(Video định dạng MP4/MOV, dung lượng tối đa 1.5GB)</small>
                    <?php if ($isOwner && $canEdit): ?>
                        <div class="input-group mb-2">
                            <input type="text" class="form-control talent-video-input"
                                data-entry-id="<?php echo $entryId; ?>"
                                placeholder="Nhập link YouTube hoặc URL video..."
                                value="<?php echo CHtml::encode($videoPath); ?>">
                            <button type="button" class="btn btn-outline-secondary btn-upload-talent-video"
                                data-entry-id="<?php echo $entryId; ?>" title="Tải lên video">
                                <i class="fa fa-upload"></i>
                            </button>
                            <?php if ($videoPath): ?>
                                <button type="button" class="btn btn-outline-info btn-preview-video"
                                    data-url="<?php echo CHtml::encode($videoPath); ?>" title="Xem trước">
                                    <i class="fa fa-play"></i>
                                </button>
                                <button type="button" class="btn btn-outline-danger btn-delete-talent-video"
                                    data-entry-id="<?php echo $entryId; ?>" title="Xóa video">
                                    <i class="fa fa-trash"></i>
                                </button>
                            <?php endif; ?>
                        </div>
                        <input type="file" class="d-none talent-video-file"
                            data-entry-id="<?php echo $entryId; ?>"
                            accept="video/mp4,video/quicktime,.mp4,.mov">
                        <div class="progress talent-video-progress mt-1" data-entry-id="<?php echo $entryId; ?>" style="display:none; height: 18px;">
                            <div class="progress-bar progress-bar-striped progress-bar-animated bg-success" role="progressbar" style="width: 0%">0%</div>
                        </div>
                        <div class="small talent-video-upload-status text-muted mt-1" data-entry-id="<?php echo $entryId; ?>" style="display:none;"></div>
                    <?php elseif ($videoPath): ?>
                        <div class="d-flex align-items-center gap-2">
                            <a href="<?php echo CHtml::encode($videoPath); ?>" target="_blank" class="btn btn-sm btn-outline-info">
                                <i class="fa fa-play-circle me-1"></i>Xem video
                            </a>
                        </div>
                    <?php else: ?>
                        <div class="text-muted fst-italic">Chưa có video demo</div>
                    <?php endif; ?>
                </div>

                <!-- Audio demo -->
                <div class="mb-3">
                    <label class="form-label fw-bold"><i class="fa fa-music me-1"></i>Audio demo</label>
                    <small class="text-muted d-block mb-1">(Audio định dạng MP3, dung lượng tối đa 50MB)</small>
                    <?php if ($isOwner && $canEdit): ?>
                        <div class="input-group mb-2">
                            <input type="text" class="form-control talent-audio-input"
                                data-entry-id="<?php echo $entryId; ?>"
                                placeholder="Nhập link audio..."
                                value="<?php echo CHtml::encode($musicPath); ?>">
                            <button type="button" class="btn btn-outline-secondary btn-upload-talent-audio"
                                data-entry-id="<?php echo $entryId; ?>" title="Tải lên audio">
                                <i class="fa fa-upload"></i>
                            </button>
                            <?php if ($musicPath): ?>
                                <button type="button" class="btn btn-outline-info btn-preview-audio"
                                    data-url="<?php echo CHtml::encode($musicPath); ?>" title="Nghe thử">
                                    <i class="fa fa-play"></i>
                                </button>
                            <?php endif; ?>
                        </div>
                        <input type="file" class="d-none talent-audio-file"
                            data-entry-id="<?php echo $entryId; ?>"
                            accept="audio/mpeg,audio/mp3,.mp3">
                        <div class="progress talent-audio-progress mt-1" data-entry-id="<?php echo $entryId; ?>" style="display:none; height: 18px;">
                            <div class="progress-bar progress-bar-striped progress-bar-animated bg-success" role="progressbar" style="width: 0%">0%</div>
                        </div>
                        <div class="small talent-audio-upload-status text-muted mt-1" data-entry-id="<?php echo $entryId; ?>" style="display:none;"></div>
                    <?php elseif ($musicPath): ?>
                        <div class="d-flex align-items-center gap-2">
                            <audio controls class="w-100" style="max-height:40px;">
                                <source src="<?php echo CHtml::encode($musicPath); ?>" type="audio/mpeg">
                            </audio>
                        </div>
                    <?php else: ?>
                        <div class="text-muted fst-italic">Chưa có audio demo</div>
                    <?php endif; ?>
                </div>

                <?php if ($isOwner && $canEdit): ?>
                    <button type="button" class="btn btn-sm btn-primary btn-save-talent-media" data-entry-id="<?php echo $entryId; ?>">
                        <i class="fa fa-save me-1"></i>Lưu video/audio
                    </button>
                <?php endif; ?>
            </div>
        </div>

        <!-- Danh sách người tham gia -->
        <div class="mt-4">
            <h6 class="border-bottom pb-2 mb-3">
                <i class="fa fa-users me-1 text-primary"></i>Danh sách người tham gia
                <?php if (!empty($allAllianceMembers)): ?>
                    <span class="badge bg-secondary ms-2"><?php echo count($allAllianceMembers); ?> người</span>
                <?php endif; ?>
            </h6>

            <?php
            // Nhóm thành viên theo đơn vị
            $membersByProperty = array();
            foreach ($allAllianceMembers as $member) {
                $propId = isset($member['property_id']) ? $member['property_id'] : 0;
                $propName = isset($member['property_name']) ? $member['property_name'] : 'Không xác định';
                if (!isset($membersByProperty[$propId])) {
                    $membersByProperty[$propId] = array(
                        'name' => $propName,
                        'members' => array(),
                    );
                }
                $membersByProperty[$propId]['members'][] = $member;
            }

            // Đảm bảo đơn vị hiện tại luôn có card để thêm người (kể cả chưa có ai)
            $currentPropertyId = $model->property_id;
            $currentPropertyName = isset($model->property_name) ? $model->property_name : '';
            if (!isset($membersByProperty[$currentPropertyId]) && $canEdit) {
                $membersByProperty[$currentPropertyId] = array(
                    'name' => $currentPropertyName,
                    'members' => array(),
                );
            }
            ?>

            <?php if (empty($membersByProperty)): ?>
                <p class="text-muted fst-italic">Chưa có người tham gia.</p>
            <?php else: ?>
                <?php foreach ($membersByProperty as $propId => $propData):
                    $isCurrentProperty = ($propId == $model->property_id);
                ?>
                    <div class="card mb-2">
                        <div class="card-header py-2 bg-light d-flex justify-content-between align-items-center">
                            <span>
                                <i class="fa fa-building-o me-1"></i>
                                <strong><?php echo CHtml::encode($propData['name']); ?></strong>
                                <span class="badge bg-info ms-1"><?php echo count($propData['members']); ?> người</span>
                                <?php if ($isCurrentProperty): ?>
                                    <span class="badge bg-success ms-1">Đơn vị của bạn</span>
                                <?php endif; ?>
                            </span>
                            <?php if ($canEdit && $isCurrentProperty): ?>
                                <button type="button" class="btn btn-sm btn-outline-primary btn-add-talent-member"
                                    data-entry-id="<?php echo $entryId; ?>"
                                    data-property-id="<?php echo $propId; ?>">
                                    <i class="fa fa-user-plus me-1"></i>Thêm người
                                </button>
                            <?php endif; ?>
                        </div>
                        <div class="card-body py-2">
                            <?php if (empty($propData['members'])): ?>
                                <p class="text-muted fst-italic mb-0">Chưa có người tham gia từ đơn vị này.</p>
                            <?php else: ?>
                                <div class="table-responsive">
                                    <table class="table table-sm table-bordered mb-0">
                                        <thead class="table-light">
                                            <tr>
                                                <th style="width:50px;" class="text-center">STT</th>
                                                <th style="width:250px;">Họ tên</th>
                                                <th>Chức danh</th>
                                                <?php if ($canEdit): ?>
                                                    <th style="width:70px;" class="text-center">Thao tác</th>
                                                <?php endif; ?>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($propData['members'] as $idx => $member): ?>
                                                <tr>
                                                    <td class="text-center"><?php echo $idx + 1; ?></td>
                                                    <td><?php echo CHtml::encode($member['attendee_name'] ?? ''); ?></td>
                                                    <td><?php echo CHtml::encode($member['position_name'] ?? '-'); ?></td>
                                                    <?php if ($canEdit): ?>
                                                        <td class="text-center">
                                                            <?php if ($isCurrentProperty): ?>
                                                                <button type="button" class="btn btn-xs btn-outline-danger btn-remove-talent-member"
                                                                    data-member-id="<?php echo $member['id']; ?>"
                                                                    data-entry-id="<?php echo $entryId; ?>" title="Xóa">
                                                                    <i class="fa fa-trash"></i>
                                                                </button>
                                                            <?php endif; ?>
                                                        </td>
                                                    <?php endif; ?>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</div>
