<?php
/**
 * Partial view hiển thị danh sách đội thể thao đã đăng ký (read-only)
 * Dùng cho màn hình phê duyệt
 *
 * @var array $sportTeams Danh sách teams
 * @var array $sportTeamMembers Danh sách members theo teamId
 * @var Registrations $model
 */

$teamsData = array();
foreach ($sportTeams as $team) {
    $teamId = isset($team->id) ? $team->id : (isset($team['id']) ? $team['id'] : null);
    $sportName = isset($team->sport_name) ? $team->sport_name : (isset($team['sport_name']) ? $team['sport_name'] : '');
    $teamName = isset($team->team_name) ? $team->team_name : (isset($team->name) ? $team->name : (isset($team['name']) ? $team['name'] : ''));
    $teamPropertyId = isset($team->property_id) ? $team->property_id : (isset($team['property_id']) ? $team['property_id'] : null);
    $members = ($teamId && isset($sportTeamMembers[$teamId])) ? $sportTeamMembers[$teamId] : array();

    $allianceProperties = array();
    $membersList = array();
    foreach ($members as $member) {
        $memberPropertyName = isset($member['property_name']) ? $member['property_name'] : '';
        $membersList[] = array(
            'attendee_name' => isset($member['attendee_name']) ? $member['attendee_name'] : (isset($member['name']) ? $member['name'] : ''),
            'gender' => isset($member['gender']) ? $member['gender'] : '',
            'property_name' => $memberPropertyName,
        );
        if (!empty($memberPropertyName) && $memberPropertyName !== $model->property_name && !in_array($memberPropertyName, $allianceProperties)) {
            $allianceProperties[] = $memberPropertyName;
        }
    }

    $teamsData[] = array(
        'sport_name' => $sportName,
        'team_name' => $teamName,
        'members' => $membersList,
        'alliance_properties' => $allianceProperties,
        'is_alliance' => !empty($allianceProperties),
    );
}
usort($teamsData, function ($a, $b) {
    return strcmp($a['sport_name'], $b['sport_name']);
});
?>
<?php if (empty($teamsData)): ?>
    <p class="text-muted mb-0">Chưa đăng ký môn thể thao nào.</p>
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
        </div>
        <div class="table-responsive">
            <table class="table table-bordered table-striped table-sm mb-3">
                <thead class="table-light">
                    <tr>
                        <th style="width:50px;" class="text-center">STT</th>
                        <th style="width:180px;">Họ tên</th>
                        <th style="width:80px;" class="text-center">Giới tính</th>
                        <th>Đơn vị</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($teamData['members'] as $idx => $member): ?>
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
                            <td><?php echo CHtml::encode(isset($member['property_name']) ? $member['property_name'] : '-'); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endforeach; ?>
<?php endif; ?>
