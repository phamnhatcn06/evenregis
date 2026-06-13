<?php
/**
 * Partial view hiển thị danh sách đội thể thao đã đăng ký
 * Được gọi từ AJAX refresh sau khi lưu đăng ký
 *
 * @var array $sportTeams Danh sách teams
 * @var array $sportTeamMembers Danh sách members theo teamId
 * @var Registrations $model
 * @var bool $canEdit
 */

// Nhóm theo từng đội (team), mỗi đội là 1 bảng riêng
$teamsData = array();
foreach ($sportTeams as $team) {
    $teamId = isset($team->id) ? $team->id : (isset($team['id']) ? $team['id'] : null);
    $sportName = isset($team->sport_name) ? $team->sport_name : (isset($team['sport_name']) ? $team['sport_name'] : '');
    $teamName = isset($team->team_name) ? $team->team_name : (isset($team->name) ? $team->name : (isset($team['name']) ? $team['name'] : ''));
    $teamPropertyId = isset($team->property_id) ? $team->property_id : (isset($team['property_id']) ? $team['property_id'] : null);
    $members = ($teamId && isset($sportTeamMembers[$teamId])) ? $sportTeamMembers[$teamId] : array();

    $allianceProperties = array();
    $membersList = array();
    $hasOwnMembers = false;
    foreach ($members as $member) {
        $memberPropertyName = isset($member['property_name']) ? $member['property_name'] : '';
        $memberId = isset($member['id']) ? $member['id'] : null;
        $membersList[] = array(
            'id' => $memberId,
            'attendee_name' => isset($member['attendee_name']) ? $member['attendee_name'] : (isset($member['name']) ? $member['name'] : ''),
            'gender' => isset($member['gender']) ? $member['gender'] : '',
            'property_name' => $memberPropertyName,
        );
        if ($memberPropertyName === $model->property_name) {
            $hasOwnMembers = true;
        }
        if (!empty($memberPropertyName) && $memberPropertyName !== $model->property_name && !in_array($memberPropertyName, $allianceProperties)) {
            $allianceProperties[] = $memberPropertyName;
        }
    }

    $isTeamOwner = ($teamPropertyId == $model->property_id);
    $teamsData[] = array(
        'team_id' => $teamId,
        'sport_name' => $sportName,
        'team_name' => $teamName,
        'members' => $membersList,
        'alliance_properties' => $allianceProperties,
        'is_alliance' => !empty($allianceProperties),
        'is_team_owner' => $isTeamOwner,
        'has_own_members' => $hasOwnMembers,
    );
}
// Sắp xếp theo tên môn
usort($teamsData, function ($a, $b) {
    return strcmp($a['sport_name'], $b['sport_name']);
});
?>
<?php if (empty($teamsData)): ?>
    <p class="text-muted mb-0 no-sport-message" id="no_sport_msg">Chưa đăng ký môn thể thao nào.</p>
<?php else: ?>
    <?php foreach ($teamsData as $teamData): ?>
        <div class="d-flex justify-content-between align-items-center mb-2 mt-3">
            <div>
                <h6 class="mb-0 d-inline">
                    <i class="fa fa-trophy text-warning me-1"></i><?php echo CHtml::encode($teamData['sport_name']); ?>
                    <span class="text-muted">-</span>
                    <span class="badge bg-primary"><?php echo CHtml::encode($teamData['team_name']); ?></span>
                    (<?php echo count($teamData['members']); ?> VĐV)
                </h6>
                <?php if ($teamData['is_alliance']): ?>
                    <span class="badge bg-info ms-2"><i class="fa fa-handshake-o me-1"></i>Liên quân: <?php echo CHtml::encode(implode(', ', $teamData['alliance_properties'])); ?></span>
                <?php endif; ?>
            </div>
            <?php if ($canEdit && ($teamData['is_team_owner'] || $teamData['has_own_members'])): ?>
                <div class="btn-group">
                    <button type="button" class="btn btn-sm btn-outline-primary" onclick="RegistrationView.editSportTeam(<?php echo $teamData['team_id']; ?>)" title="Sửa danh sách VĐV">
                        <i class="fa fa-pencil me-1"></i>Sửa
                    </button>
                    <?php if (!$teamData['is_alliance'] && $teamData['is_team_owner']): ?>
                        <button type="button" class="btn btn-sm btn-outline-danger" onclick="confirmDeleteTeam(<?php echo $teamData['team_id']; ?>)" title="Xóa đội">
                            <i class="fa fa-trash me-1"></i>Xóa
                        </button>
                    <?php endif; ?>
                </div>
                <?php if (!$teamData['is_alliance'] && $teamData['is_team_owner']): ?>
                    <form method="post" action="<?php echo $this->createUrl('deleteSportTeam', array('id' => $teamData['team_id'], 'registration_id' => $model->id)); ?>" id="delete-team-form-<?php echo $teamData['team_id']; ?>" style="display:none;"></form>
                <?php endif; ?>
            <?php endif; ?>
        </div>
        <div class="table-responsive">
            <table class="table table-bordered table-striped table-sm mb-3">
                <thead class="table-light">
                    <tr>
                        <th style="width:50px;" class="text-center">STT</th>
                        <th style="width:180px;">Họ tên</th>
                        <th style="width:80px;" class="text-center">Giới tính</th>
                        <th>Đơn vị</th>
                        <?php if ($canEdit): ?>
                            <th style="width:70px;" class="text-center">Thao tác</th>
                        <?php endif; ?>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($teamData['members'] as $idx => $member):
                        $memberId = isset($member['id']) ? $member['id'] : null;
                        $memberPropertyName = isset($member['property_name']) ? $member['property_name'] : '';
                        $isOwnMember = ($memberPropertyName === $model->property_name);
                    ?>
                        <tr>
                            <td class="text-center"><?php echo $idx + 1; ?></td>
                            <td><?php echo CHtml::encode(isset($member['attendee_name']) ? $member['attendee_name'] : ''); ?></td>
                            <td class="text-center">
                                <?php
                                $genderVal = isset($member['gender']) ? $member['gender'] : null;
                                if ($genderVal === 1 || $genderVal === '1' || $genderVal === 'male' || $genderVal === 'nam') {
                                    echo '<span class="badge bg-primary">Nam</span>';
                                } elseif ($genderVal === 0 || $genderVal === '0' || $genderVal === 'female' || $genderVal === 'nữ' || $genderVal === 'nu') {
                                    echo '<span class="badge bg-danger">Nữ</span>';
                                } else {
                                    echo '-';
                                }
                                ?>
                            </td>
                            <td><?php echo CHtml::encode($memberPropertyName ?: '-'); ?></td>
                            <?php if ($canEdit): ?>
                                <td class="text-center">
                                    <?php if ($isOwnMember && $memberId): ?>
                                        <button type="button" class="btn btn-sm btn-outline-danger" onclick="confirmDeleteTeamMember(<?php echo $memberId; ?>, <?php echo $teamData['team_id']; ?>)" title="Xóa khỏi đội">
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
    <?php endforeach; ?>
<?php endif; ?>
