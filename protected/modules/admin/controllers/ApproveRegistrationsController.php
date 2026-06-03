<?php

class ApproveRegistrationsController extends AdminController
{
    /**
     * Danh sách đăng ký chờ phê duyệt (status = submitted)
     */
    public function actionAdmin()
    {
        $model = new Registrations('search');
        $model->unsetAttributes();

        if (isset($_GET['Registrations'])) {
            $model->setAttributes($_GET['Registrations']);
        }

        // Mặc định chỉ hiện SUBMITTED nếu không có filter
        $params = array();
        if (!isset($_GET['Registrations']['status']) || $_GET['Registrations']['status'] === '') {
            $params['status'] = Registrations::STATUS_SUBMITTED;
        }
        foreach ($model->attributes as $key => $value) {
            if ($value !== null && $value !== '') {
                $params[$key] = $value;
            }
        }

        $dataProvider = Registrations::getApiDataProvider($params);
        $statusList = array('' => 'Tất cả') + Registrations::getStatusList();

        $this->render('admin', array(
            'model' => $model,
            'dataProvider' => $dataProvider,
            'statusList' => $statusList,
        ));
    }

    /**
     * Xem chi tiết đăng ký để phê duyệt
     */
    public function actionView($id)
    {
        $model = $this->loadModelById($id);

        // Load related names
        if (empty($model->event_name) && $model->event_id) {
            $event = Events::fetchFromApi($model->event_id);
            $model->event_name = $event ? $event->name : '';
        }
        if ($model->property_id) {
            $property = Properties::fetchFromApi($model->property_id);
            if ($property) {
                if (empty($model->property_name)) {
                    $model->property_name = $property->name;
                }
                $model->property_code = $property->prefix ? $property->prefix : $property->code;
            }
        }
        if (empty($model->period_name) && $model->period_id) {
            $period = RegistrationPeriods::fetchFromApi($model->period_id);
            $model->period_name = $period ? $period->name : '';
        }

        // Load attendees
        $attendees = Attendees::getByRegistrationId($id);
        $attendeesMap = array();
        foreach ($attendees as $att) {
            $attId = isset($att['id']) ? $att['id'] : null;
            if ($attId) {
                $attendeesMap[$attId] = $att;
            }
        }

        // Load roles
        $rolesData = Roles::getApiDataProvider(array(), 100)->getData();
        $roles = array();
        foreach ($rolesData as $r) {
            $rId = isset($r['id']) ? $r['id'] : (isset($r->id) ? $r->id : null);
            $rName = isset($r['name']) ? $r['name'] : (isset($r->name) ? $r->name : '');
            if ($rId) $roles[$rId] = $rName;
        }

        // Load transports
        $transportsData = Transports::getApiDataProvider(array(), 100)->getData();
        $transports = array();
        foreach ($transportsData as $t) {
            $tId = isset($t['id']) ? $t['id'] : (isset($t->id) ? $t->id : null);
            $tName = isset($t['name']) ? $t['name'] : (isset($t->name) ? $t->name : '');
            if ($tId) $transports[$tId] = $tName;
        }

        // Load competition registrations
        $competitionRegistrations = array();
        $compRegsData = CompetitionRegistrations::getApiDataProvider(array('registration_id' => $id), 200)->getData();
        foreach ($compRegsData as $reg) {
            $compId = isset($reg->competition_id) ? $reg->competition_id : (isset($reg['competition_id']) ? $reg['competition_id'] : null);
            if (!$compId) continue;

            if (!isset($competitionRegistrations[$compId])) {
                $competitionRegistrations[$compId] = array(
                    'competition_id' => $compId,
                    'competition_name' => isset($reg->competition_name) ? $reg->competition_name : (isset($reg['competition_name']) ? $reg['competition_name'] : ''),
                    'attendees' => array(),
                );
            }

            $attendeeId = isset($reg->attendee_id) ? $reg->attendee_id : (isset($reg['attendee_id']) ? $reg['attendee_id'] : null);
            $attendeeInfo = isset($attendeesMap[$attendeeId]) ? $attendeesMap[$attendeeId] : array();

            $competitionRegistrations[$compId]['attendees'][] = array(
                'id' => isset($reg->id) ? $reg->id : (isset($reg['id']) ? $reg['id'] : null),
                'attendee_id' => $attendeeId,
                'attendee_name' => isset($attendeeInfo['full_name']) ? $attendeeInfo['full_name'] : '',
                'position_name' => isset($attendeeInfo['position_name']) ? $attendeeInfo['position_name'] : '',
                'division_name' => isset($attendeeInfo['division_name']) ? $attendeeInfo['division_name'] : '',
            );
        }

        // Load competition names if missing
        foreach ($competitionRegistrations as $compId => &$compData) {
            if (empty($compData['competition_name'])) {
                $comp = Competitions::fetchFromApi($compId);
                $compData['competition_name'] = $comp ? $comp->name : '';
            }
        }
        unset($compData);

        // Load Sport Teams
        $sportTeams = array();
        $sportTeamMembers = array();
        if ($model->event_id && $model->property_id) {
            $teamsData = SportTeams::getApiDataProvider(array('event_id' => $model->event_id, 'property_id' => $model->property_id), 100)->getData();
            foreach ($teamsData as $team) {
                $teamId = isset($team->id) ? $team->id : (isset($team['id']) ? $team['id'] : null);
                if ($teamId) {
                    if (empty($team->sport_name) && $team->sport_id) {
                        $sport = Sports::fetchFromApi($team->sport_id);
                        $team->sport_name = $sport ? $sport->name : '';
                    }
                    $sportTeams[] = $team;
                    $membersData = SportTeamMembers::getApiDataProvider(array('sport_team_id' => $teamId), 100)->getData();

                    $enrichedMembers = array();
                    foreach ($membersData as $member) {
                        $attId = isset($member->attendee_id) ? $member->attendee_id : (isset($member['attendee_id']) ? $member['attendee_id'] : null);
                        $attInfo = isset($attendeesMap[$attId]) ? $attendeesMap[$attId] : array();

                        $memberArr = is_object($member) ? get_object_vars($member) : $member;
                        if (empty($memberArr['attendee_name']) && !empty($attInfo['full_name'])) {
                            $memberArr['attendee_name'] = $attInfo['full_name'];
                        }
                        if (empty($memberArr['position_name']) && !empty($attInfo['position_name'])) {
                            $memberArr['position_name'] = $attInfo['position_name'];
                        }
                        if (empty($memberArr['division_name']) && !empty($attInfo['division_name'])) {
                            $memberArr['division_name'] = $attInfo['division_name'];
                        }
                        $enrichedMembers[] = $memberArr;
                    }
                    $sportTeamMembers[$teamId] = $enrichedMembers;
                }
            }
        }

        // Load Beauty Contestants
        $beautyContestants = array();
        if ($model->event_id) {
            $attendeeIds = array_keys($attendeesMap);
            if (!empty($attendeeIds)) {
                $contests = BeautyContests::getApiDataProvider(array('event_id' => $model->event_id), 100)->getData();
                foreach ($contests as $contest) {
                    $contestId = isset($contest->id) ? $contest->id : (isset($contest['id']) ? $contest['id'] : null);
                    $contestName = isset($contest->name) ? $contest->name : (isset($contest['name']) ? $contest['name'] : '');
                    if (!$contestId) continue;

                    $contestants = BeautyContestants::getApiDataProvider(array('contest_id' => $contestId), 500)->getData();
                    foreach ($contestants as $c) {
                        $attId = isset($c->attendee_id) ? $c->attendee_id : (isset($c['attendee_id']) ? $c['attendee_id'] : null);
                        if ($attId && in_array($attId, $attendeeIds)) {
                            if (!isset($beautyContestants[$contestId])) {
                                $beautyContestants[$contestId] = array(
                                    'contest_id' => $contestId,
                                    'contest_name' => $contestName,
                                    'contestants' => array(),
                                );
                            }
                            $attInfo = isset($attendeesMap[$attId]) ? $attendeesMap[$attId] : array();
                            $beautyContestants[$contestId]['contestants'][] = array(
                                'id' => isset($c->id) ? $c->id : (isset($c['id']) ? $c['id'] : null),
                                'attendee_id' => $attId,
                                'attendee_name' => isset($attInfo['full_name']) ? $attInfo['full_name'] : '',
                                'position_name' => isset($attInfo['position_name']) ? $attInfo['position_name'] : '',
                                'division_name' => isset($attInfo['division_name']) ? $attInfo['division_name'] : '',
                                'candidate_number' => isset($c->candidate_number) ? $c->candidate_number : (isset($c['candidate_number']) ? $c['candidate_number'] : ''),
                                'height_cm' => isset($c->height_cm) ? $c->height_cm : (isset($c['height_cm']) ? $c['height_cm'] : null),
                                'weight_kg' => isset($c->weight_kg) ? $c->weight_kg : (isset($c['weight_kg']) ? $c['weight_kg'] : null),
                                'measurements' => isset($c->measurements) ? $c->measurements : (isset($c['measurements']) ? $c['measurements'] : ''),
                            );
                        }
                    }
                }
            }
        }

        // Load Talent Entries
        $talentEntries = array();
        $talentEntryMembers = array();
        if ($model->property_id) {
            // Lấy talent shows của event
            $showIds = array();
            if ($model->event_id) {
                $showsData = TalentShows::getApiDataProvider(array('event_id' => $model->event_id), 100)->getData();
                foreach ($showsData as $show) {
                    $showId = isset($show->id) ? $show->id : (isset($show['id']) ? $show['id'] : null);
                    if ($showId) $showIds[] = $showId;
                }
            }

            // Lấy talent entries của property cho các shows này
            $filterParams = array(
                'property_id' => $model->property_id,
                'registration_id' => $model->id,
            );
            if ($model->event_id) {
                $filterParams['event_id'] = $model->event_id;
            }
            $entriesData = TalentEntries::getApiDataProvider($filterParams, 100)->getData();

            foreach ($entriesData as $entry) {
                $entryId = isset($entry->id) ? $entry->id : (isset($entry['id']) ? $entry['id'] : null);
                $entryShowId = isset($entry->show_id) ? $entry->show_id : (isset($entry['show_id']) ? $entry['show_id'] : null);
                $entryRegId = isset($entry->registration_id) ? $entry->registration_id : (isset($entry['registration_id']) ? $entry['registration_id'] : null);

                // Chỉ lấy entries thuộc shows của event này và thuộc registration hiện tại
                if ($entryId && (empty($showIds) || in_array($entryShowId, $showIds))) {
                    if ($entryRegId && $entryRegId != $model->id) {
                        continue;
                    }
                    // Fetch category name if not available
                    if (empty($entry->category_name) && (isset($entry->category_id) || isset($entry['category_id']))) {
                        $catId = isset($entry->category_id) ? $entry->category_id : $entry['category_id'];
                        $cat = TalentCategories::fetchFromApi($catId);
                        if ($cat) {
                            if (is_object($entry)) {
                                $entry->category_name = $cat->name;
                            } else {
                                $entry['category_name'] = $cat->name;
                            }
                        }
                    }
                    
                    if (is_object($entry)) {
                        $entry->video_path = $this->cleanStorageUrl($entry->video_path);
                        $entry->music_path = $this->cleanStorageUrl($entry->music_path);
                    } else {
                        $entry['video_path'] = $this->cleanStorageUrl($entry['video_path']);
                        $entry['music_path'] = $this->cleanStorageUrl($entry['music_path']);
                    }
                    
                    $talentEntries[] = $entry;
                    $membersResult = ApiClient::get(ApiEndpoints::TALENT_ENTRY_MEMBER_LIST, array('entry_id' => $entryId));
                    $membersData = array();
                    if ($membersResult['success'] && isset($membersResult['data'])) {
                        $membersData = isset($membersResult['data']['data']) ? $membersResult['data']['data'] : $membersResult['data'];
                    }
                    $enrichedMembers = array();
                    foreach ($membersData as $member) {
                        $attId = isset($member['attendee_id']) ? $member['attendee_id'] : null;
                        $attInfo = isset($attendeesMap[$attId]) ? $attendeesMap[$attId] : array();
                        $memberArr = is_array($member) ? $member : get_object_vars($member);
                        if (empty($memberArr['attendee_name']) && !empty($attInfo['full_name'])) {
                            $memberArr['attendee_name'] = $attInfo['full_name'];
                        }
                        if (empty($memberArr['position_name']) && !empty($attInfo['position_name'])) {
                            $memberArr['position_name'] = $attInfo['position_name'];
                        }
                        if (empty($memberArr['division_name']) && !empty($attInfo['division_name'])) {
                            $memberArr['division_name'] = $attInfo['division_name'];
                        }
                        $enrichedMembers[] = $memberArr;
                    }
                    $talentEntryMembers[$entryId] = $enrichedMembers;
                }
            }
        }

        $this->render('view', array(
            'model' => $model,
            'attendees' => $attendees,
            'roles' => $roles,
            'transports' => $transports,
            'competitionRegistrations' => $competitionRegistrations,
            'sportTeams' => $sportTeams,
            'sportTeamMembers' => $sportTeamMembers,
            'beautyContestants' => $beautyContestants,
            'talentEntries' => $talentEntries,
            'talentEntryMembers' => $talentEntryMembers,
        ));
    }

    /**
     * Phê duyệt một hoặc tất cả người tham dự
     */
    public function actionApproveAttendee()
    {
        header('Content-Type: application/json');

        if (!Yii::app()->request->isPostRequest) {
            echo CJSON::encode(array('success' => false, 'error' => 'Yêu cầu không hợp lệ.'));
            Yii::app()->end();
        }

        $ssoUser = AuthHandler::getUser();
        $approvedBy = isset($ssoUser['email']) ? $ssoUser['email'] : null;
        $all = Yii::app()->request->getPost('all');
        $registrationId = Yii::app()->request->getPost('registration_id');

        // Bulk approve all pending attendees
        if ($all && $registrationId) {
            $attendees = Attendees::getByRegistrationId($registrationId);
            $count = 0;
            foreach ($attendees as $att) {
                $status = isset($att['approval_status']) ? (int)$att['approval_status'] : Attendees::APPROVAL_PENDING;
                if ($status == Attendees::APPROVAL_PENDING) {
                    $attendee = new Attendees();
                    $attendee->id = $att['id'];
                    $attendee->approval_status = Attendees::APPROVAL_APPROVED;
                    $attendee->approved_at = date('Y-m-d H:i:s');
                    $attendee->approved_by = $approvedBy;
                    $attendee->updateViaApi();
                    $count++;
                }
            }
            echo CJSON::encode(array('success' => true, 'message' => "Đã duyệt {$count} người tham dự."));
            Yii::app()->end();
        }

        // Single approve
        $attendeeId = Yii::app()->request->getPost('attendee_id');
        $attendee = Attendees::fetchFromApi($attendeeId);

        if (!$attendee) {
            echo CJSON::encode(array('success' => false, 'error' => 'Không tìm thấy người tham dự.'));
            Yii::app()->end();
        }

        $attendee->approval_status = Attendees::APPROVAL_APPROVED;
        $attendee->approved_at = date('Y-m-d H:i:s');
        $attendee->approved_by = $approvedBy;

        $result = $attendee->updateViaApi();

        if ($result['success']) {
            echo CJSON::encode(array('success' => true, 'message' => 'Đã phê duyệt người tham dự.'));
        } else {
            echo CJSON::encode(array('success' => false, 'error' => isset($result['error']) ? $result['error'] : 'Không thể phê duyệt.'));
        }
        Yii::app()->end();
    }

    /**
     * Từ chối một hoặc tất cả người tham dự
     */
    public function actionRejectAttendee()
    {
        header('Content-Type: application/json');

        if (!Yii::app()->request->isPostRequest) {
            echo CJSON::encode(array('success' => false, 'error' => 'Yêu cầu không hợp lệ.'));
            Yii::app()->end();
        }

        $ssoUser = AuthHandler::getUser();
        $approvedBy = isset($ssoUser['email']) ? $ssoUser['email'] : null;
        $reason = Yii::app()->request->getPost('reason', '');
        $all = Yii::app()->request->getPost('all');
        $registrationId = Yii::app()->request->getPost('registration_id');

        // Bulk reject all pending attendees
        if ($all && $registrationId) {
            $attendees = Attendees::getByRegistrationId($registrationId);
            $count = 0;
            foreach ($attendees as $att) {
                $status = isset($att['approval_status']) ? (int)$att['approval_status'] : Attendees::APPROVAL_PENDING;
                if ($status == Attendees::APPROVAL_PENDING) {
                    $attendee = new Attendees();
                    $attendee->id = $att['id'];
                    $attendee->approval_status = Attendees::APPROVAL_REJECTED;
                    $attendee->note = $reason;
                    $attendee->approved_at = date('Y-m-d H:i:s');
                    $attendee->approved_by = $approvedBy;
                    $attendee->updateViaApi();
                    $count++;
                }
            }
            echo CJSON::encode(array('success' => true, 'message' => "Đã từ chối {$count} người tham dự."));
            Yii::app()->end();
        }

        // Single reject
        $attendeeId = Yii::app()->request->getPost('attendee_id');
        $attendee = Attendees::fetchFromApi($attendeeId);

        if (!$attendee) {
            echo CJSON::encode(array('success' => false, 'error' => 'Không tìm thấy người tham dự.'));
            Yii::app()->end();
        }

        $attendee->approval_status = Attendees::APPROVAL_REJECTED;
        $attendee->note = $reason;
        $attendee->approved_at = date('Y-m-d H:i:s');
        $attendee->approved_by = $approvedBy;

        $result = $attendee->updateViaApi();

        if ($result['success']) {
            echo CJSON::encode(array('success' => true, 'message' => 'Đã từ chối người tham dự.'));
        } else {
            echo CJSON::encode(array('success' => false, 'error' => isset($result['error']) ? $result['error'] : 'Không thể từ chối.'));
        }
        Yii::app()->end();
    }

    /**
     * Phê duyệt toàn bộ đăng ký
     */
    public function actionApproveAll()
    {
        header('Content-Type: application/json');

        if (!Yii::app()->request->isPostRequest) {
            echo CJSON::encode(array('success' => false, 'error' => 'Yêu cầu không hợp lệ.'));
            Yii::app()->end();
        }

        $registrationId = Yii::app()->request->getPost('registration_id');
        $model = Registrations::fetchFromApi($registrationId);

        if (!$model) {
            echo CJSON::encode(array('success' => false, 'error' => 'Không tìm thấy phiếu đăng ký.'));
            Yii::app()->end();
        }

        $ssoUser = AuthHandler::getUser();
        $approvedBy = isset($ssoUser['email']) ? $ssoUser['email'] : null;

        // Approve all attendees
        $attendees = Attendees::getByRegistrationId($registrationId);
        $successCount = 0;
        foreach ($attendees as $att) {
            $attId = isset($att['id']) ? $att['id'] : null;
            if ($attId) {
                $attendee = Attendees::fetchFromApi($attId);
                if ($attendee && $attendee->approval_status != Attendees::APPROVAL_APPROVED) {
                    $attendee->approval_status = Attendees::APPROVAL_APPROVED;
                    $attendee->approved_at = date('Y-m-d H:i:s');
                    $attendee->approved_by = $approvedBy;
                    $result = $attendee->updateViaApi();
                    if ($result['success']) {
                        $successCount++;
                    }
                }
            }
        }

        // Approve registration
        $model->status = Registrations::STATUS_APPROVED;
        $model->reviewed_at = date('Y-m-d H:i:s');
        $model->reviewed_by = $approvedBy;
        $result = $model->updateViaApi();

        if ($result['success']) {
            // Ghi vào registration_approvals
            $approval = RegistrationApprovals::getActiveByRegistrationId($registrationId);
            if ($approval) {
                $ssoId = isset($ssoUser['email']) ? $ssoUser['email'] : null;
                $fullName = isset($ssoUser['full_name']) ? $ssoUser['full_name'] : $approvedBy;
                RegistrationApprovals::approveViaApi($approval->id, $ssoId, $fullName);
            }

            echo CJSON::encode(array(
                'success' => true,
                'message' => "Đã phê duyệt phiếu đăng ký và {$successCount} người tham dự.",
            ));
        } else {
            echo CJSON::encode(array('success' => false, 'error' => 'Không thể phê duyệt phiếu đăng ký.'));
        }
        Yii::app()->end();
    }

    /**
     * Từ chối toàn bộ đăng ký
     */
    public function actionRejectAll()
    {
        header('Content-Type: application/json');

        if (!Yii::app()->request->isPostRequest) {
            echo CJSON::encode(array('success' => false, 'error' => 'Yêu cầu không hợp lệ.'));
            Yii::app()->end();
        }

        $registrationId = Yii::app()->request->getPost('registration_id');
        $reason = Yii::app()->request->getPost('reason', '');
        $model = Registrations::fetchFromApi($registrationId);

        if (!$model) {
            echo CJSON::encode(array('success' => false, 'error' => 'Không tìm thấy phiếu đăng ký.'));
            Yii::app()->end();
        }

        $ssoUser = AuthHandler::getUser();
        $approvedBy = isset($ssoUser['email']) ? $ssoUser['email'] : null;

        // Reject all attendees
        $attendees = Attendees::getByRegistrationId($registrationId);
        $rejectCount = 0;
        foreach ($attendees as $att) {
            $attId = isset($att['id']) ? $att['id'] : null;
            if ($attId) {
                $attendee = Attendees::fetchFromApi($attId);
                if ($attendee && $attendee->approval_status != Attendees::APPROVAL_REJECTED) {
                    $attendee->approval_status = Attendees::APPROVAL_REJECTED;
                    $attendee->note = $reason;
                    $attendee->approved_at = date('Y-m-d H:i:s');
                    $attendee->approved_by = $approvedBy;
                    $result = $attendee->updateViaApi();
                    if ($result['success']) {
                        $rejectCount++;
                    }
                }
            }
        }

        // Reject registration
        $model->status = Registrations::STATUS_REJECTED;
        $model->reviewed_at = date('Y-m-d H:i:s');
        $model->reviewed_by = $approvedBy;
        $model->rejection_reason = $reason;
        $result = $model->updateViaApi();

        if ($result['success']) {
            // Ghi vào registration_approvals
            $approval = RegistrationApprovals::getActiveByRegistrationId($registrationId);
            if ($approval) {
                $ssoId = isset($ssoUser['email']) ? $ssoUser['email'] : null;
                $fullName = isset($ssoUser['full_name']) ? $ssoUser['full_name'] : $approvedBy;
                RegistrationApprovals::rejectViaApi($approval->id, $ssoId, $fullName, $reason);
            }

            echo CJSON::encode(array(
                'success' => true,
                'message' => "Đã từ chối phiếu đăng ký và {$rejectCount} người tham dự.",
            ));
        } else {
            echo CJSON::encode(array('success' => false, 'error' => 'Không thể từ chối phiếu đăng ký.'));
        }
        Yii::app()->end();
    }

    public function actionReturn()
    {
        header('Content-Type: application/json');

        if (!Yii::app()->request->isPostRequest) {
            echo CJSON::encode(array('success' => false, 'error' => 'Yêu cầu không hợp lệ.'));
            Yii::app()->end();
        }

        $registrationId = Yii::app()->request->getPost('registration_id');
        $reason = Yii::app()->request->getPost('reason', '');
        $model = Registrations::fetchFromApi($registrationId);

        if (!$model) {
            echo CJSON::encode(array('success' => false, 'error' => 'Không tìm thấy phiếu đăng ký.'));
            Yii::app()->end();
        }

        $ssoUser = AuthHandler::getUser();
        $reviewedBy = isset($ssoUser['email']) ? $ssoUser['email'] : null;

        $model->status = Registrations::STATUS_RETURNED;
        $model->reviewed_at = date('Y-m-d H:i:s');
        $model->reviewed_by = $reviewedBy;
        $model->rejection_reason = $reason;
        $result = $model->updateViaApi();

        if ($result['success']) {
            // Ghi vào registration_approvals
            $approval = RegistrationApprovals::getActiveByRegistrationId($registrationId);
            if ($approval) {
                $ssoId = isset($ssoUser['email']) ? $ssoUser['email'] : null;
                $fullName = isset($ssoUser['full_name']) ? $ssoUser['full_name'] : $reviewedBy;
                RegistrationApprovals::revisionViaApi($approval->id, $ssoId, $fullName, 0, $reason);
            }

            echo CJSON::encode(array(
                'success' => true,
                'message' => 'Đã trả phiếu đăng ký về đơn vị để chỉnh sửa.',
            ));
        } else {
            $errorMsg = isset($result['message']) ? $result['message'] : (isset($result['error']) ? $result['error'] : 'Không thể trả phiếu đăng ký về.');
            Yii::log('Return registration failed: ' . print_r($result, true), 'error', 'application.controllers.ApproveRegistrationsController');
            echo CJSON::encode(array('success' => false, 'error' => $errorMsg, 'debug' => $result));
        }
        Yii::app()->end();
    }

    protected function loadModelById($id)
    {
        $model = Registrations::fetchFromApi($id);
        if ($model === null) {
            throw new CHttpException(404, 'Không tìm thấy phiếu đăng ký.');
        }
        return $model;
    }

    private function cleanStorageUrl($url)
    {
        if (empty($url)) {
            return $url;
        }
        $prefix = 'https://portal-registration.muongthanh.vn/storage/';
        if (strpos($url, $prefix) === 0) {
            $remaining = substr($url, strlen($prefix));
            if (preg_match('/^https?:\/\//i', $remaining)) {
                return $remaining;
            }
        }
        $prefixHttp = 'http://portal-registration.muongthanh.vn/storage/';
        if (strpos($url, $prefixHttp) === 0) {
            $remaining = substr($url, strlen($prefixHttp));
            if (preg_match('/^https?:\/\//i', $remaining)) {
                return $remaining;
            }
        }
        return $url;
    }
}
