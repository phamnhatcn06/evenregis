<?php
/** @var array $contests */
/** @var int $contestId */
/** @var array $ranking */
/** @var array $settings */

$this->breadcrumbs = array(
    'Bình chọn Miss',
    'Kết quả',
);
$this->Tabletitle = 'Kết quả bình chọn Miss';

$rows = isset($ranking['ranking']) ? $ranking['ranking'] : array();
$totalVotes = (int) (isset($ranking['total_votes']) ? $ranking['total_votes'] : 0);
$startAt = isset($settings['vote_start_at']) ? $settings['vote_start_at'] : '';
$endAt = isset($settings['vote_end_at']) ? $settings['vote_end_at'] : '';
?>

<!-- Chọn cuộc thi -->
<div class="card mb-3">
    <div class="card-body">
        <form method="get" action="<?php echo $this->createUrl('index'); ?>" class="row g-2 align-items-end">
            <div class="col-md-5">
                <label class="form-label">Cuộc thi</label>
                <select name="contest_id" class="form-select">
                    <?php foreach ($contests as $id => $name): ?>
                        <option value="<?php echo (int) $id; ?>" <?php echo ($id == $contestId) ? 'selected' : ''; ?>>
                            <?php echo CHtml::encode($name); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-3">
                <button type="submit" class="btn btn-primary"><i class="fa fa-search me-1"></i>Xem</button>
                <?php if ($contestId): ?>
                    <a href="<?php echo $this->createUrl('exportExcel', array('contest_id' => $contestId)); ?>" class="btn btn-success">
                        <i class="fa fa-file-excel-o me-1"></i>Xuất Excel
                    </a>
                <?php endif; ?>
            </div>
        </form>
    </div>
</div>

<?php if ($contestId): ?>
<div class="row g-3 mb-3">
    <div class="col-md-6">
        <div class="card h-100">
            <div class="card-body text-center">
                <div class="text-muted">Tổng lượt bình chọn</div>
                <div class="h3 fw-bold text-primary mb-0"><?php echo (int) (isset($ranking['total_votes']) ? $ranking['total_votes'] : 0); ?></div>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card h-100">
            <div class="card-body text-center">
                <div class="text-muted">Số người đã bình chọn</div>
                <div class="h3 fw-bold text-info mb-0"><?php echo (int) (isset($ranking['voter_count']) ? $ranking['voter_count'] : 0); ?></div>
            </div>
        </div>
    </div>
</div>

<!-- Khung giờ mở/đóng cổng -->
<div class="card mb-3">
    <div class="card-header"><strong>Khung giờ bình chọn</strong> <span class="text-muted">(để trống = không giới hạn)</span></div>
    <div class="card-body">
        <form method="post" action="<?php echo $this->createUrl('saveWindow'); ?>" class="row g-2 align-items-end">
            <input type="hidden" name="contest_id" value="<?php echo (int) $contestId; ?>">
            <div class="col-md-4">
                <label class="form-label">Thời gian mở</label>
                <input type="datetime-local" name="vote_start_at" class="form-control"
                       value="<?php echo CHtml::encode($startAt ? str_replace(' ', 'T', substr($startAt, 0, 16)) : ''); ?>">
            </div>
            <div class="col-md-4">
                <label class="form-label">Thời gian đóng</label>
                <input type="datetime-local" name="vote_end_at" class="form-control"
                       value="<?php echo CHtml::encode($endAt ? str_replace(' ', 'T', substr($endAt, 0, 16)) : ''); ?>">
            </div>
            <div class="col-md-4">
                <button type="submit" class="btn btn-primary"><i class="fa fa-save me-1"></i>Lưu khung giờ</button>
            </div>
        </form>
    </div>
</div>

<!-- Bảng xếp hạng -->
<div class="card">
    <div class="card-header"><strong><?php echo $this->Tabletitle; ?></strong></div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered table-striped align-middle">
                <thead>
                    <tr>
                        <th style="width:70px;">Hạng</th>
                        <th style="width:90px;">Ảnh</th>
                        <th style="width:130px;">Số báo danh</th>
                        <th>Thí sinh</th>
                        <th>Đơn vị</th>
                        <th style="width:110px;" class="text-end">Số phiếu</th>
                        <th style="width:110px;" class="text-end">Tỷ lệ</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($rows)): ?>
                        <tr><td colspan="7" class="text-center text-muted">Chưa có dữ liệu bình chọn.</td></tr>
                    <?php else: ?>
                        <?php foreach ($rows as $r): ?>
                            <?php
                            // Ảnh bình chọn là URL tuyệt đối (storage backend); ảnh đại diện
                            // dự phòng là đường dẫn tương đối nằm trên host Yii.
                            $photo = isset($r['photo_portrait']) && $r['photo_portrait'] ? $r['photo_portrait'] : '';
                            if ($photo && strpos($photo, 'http') !== 0) {
                                $photo = Yii::app()->request->baseUrl . '/' . ltrim($photo, '/');
                            }
                            $voteCount = (int) (isset($r['vote_count']) ? $r['vote_count'] : 0);
                            $percent = $totalVotes > 0 ? round($voteCount * 100 / $totalVotes, 2) : 0;
                            ?>
                            <tr>
                                <td class="text-center fw-bold"><?php echo (int) (isset($r['rank']) ? $r['rank'] : 0); ?></td>
                                <td>
                                    <?php if ($photo): ?>
                                        <img src="<?php echo CHtml::encode($photo); ?>" alt="" style="width:50px;height:60px;object-fit:cover;border-radius:4px;">
                                    <?php endif; ?>
                                </td>
                                <td><?php echo CHtml::encode(isset($r['candidate_number']) ? $r['candidate_number'] : ''); ?></td>
                                <td><?php echo CHtml::encode(isset($r['name']) ? $r['name'] : ''); ?></td>
                                <td><?php echo CHtml::encode(isset($r['unit']) ? $r['unit'] : ''); ?></td>
                                <td class="text-end fw-bold text-primary"><?php echo (int) (isset($r['vote_count']) ? $r['vote_count'] : 0); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php endif; ?>
