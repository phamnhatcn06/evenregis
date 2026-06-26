<?php

class RegistrationsController extends AdminController
{
	public function actionView($id)
	{
		$this->checkRegistrationAccess($id);
		$model = $this->loadModelById($id);
		$registrationDetails = RegistrationDetails::getByRegistrationId($id);

		// Load related names nếu API không trả về
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
		if (empty($model->relation_property_name) && $model->relation_property_id) {
			$relationProperty = Properties::fetchFromApi($model->relation_property_id);
			$model->relation_property_name = $relationProperty ? $relationProperty->name : '';
		}
		if (empty($model->period_name) && $model->period_id) {
			$period = RegistrationPeriods::fetchFromApi($model->period_id);
			$model->period_name = $period ? $period->name : '';
		}

		// Load allowed contents from registration period
		$allowedContentCodes = array();
		if ($model->period_id) {
			$periodContents = RegistrationPeriodContents::getContentsByPeriod($model->period_id);
			foreach ($periodContents as $pc) {
				$code = '';
				if (is_array($pc)) {
					$code = isset($pc['content_code']) ? $pc['content_code'] : (isset($pc['content']['code']) ? $pc['content']['code'] : '');
				} else {
					$code = isset($pc->content_code) ? $pc->content_code : (isset($pc->content) && isset($pc->content->code) ? $pc->content->code : '');
				}
				if ($code) {
					if ($code === 'sport') $code = 'sports';
					if ($code === 'competitions') $code = 'competition';
					if ($code === 'talents') $code = 'talent';
					if ($code === 'beauty_contests') $code = 'miss';
					$allowedContentCodes[] = $code;
				}
			}
		}

		// Load alliance request nếu có liên quân (hỗ trợ tìm kiếm 2 chiều)
		$allianceRequest = null;
		if ($model->event_id && $model->property_id) {
			if ($model->relation_property_id) {
				$allianceRequest = AllianceRequests::findByRegistration(
					$model->event_id,
					$model->property_id,
					$model->relation_property_id
				);
				if (!$allianceRequest) {
					$allianceRequest = AllianceRequests::findByRegistration(
						$model->event_id,
						$model->relation_property_id,
						$model->property_id
					);
				}
			} else {
				// Nếu relation_property_id chưa được set (ví dụ ở đơn vị yêu cầu liên quân),
				// thực hiện tìm kiếm xem có yêu cầu liên quân nào được duyệt liên quan đến đơn vị này hay không.
				// 1. Tìm với vai trò đơn vị yêu cầu
				$allianceRequests = AllianceRequests::getApiDataProvider(array(
					'event_id' => $model->event_id,
					'requester_org_id' => $model->property_id,
					'status' => AllianceRequests::STATUS_APPROVED,
				), 1)->getData();

				if (!empty($allianceRequests)) {
					$allianceRequest = $allianceRequests[0];
					$model->relation_property_id = $allianceRequest->target_org_id;
					if (empty($model->relation_property_name)) {
						$relProp = Properties::fetchFromApi($model->relation_property_id);
						$model->relation_property_name = $relProp ? $relProp->name : '';
					}
				} else {
					// 2. Tìm với vai trò đơn vị nhận
					$allianceRequests = AllianceRequests::getApiDataProvider(array(
						'event_id' => $model->event_id,
						'target_org_id' => $model->property_id,
						'status' => AllianceRequests::STATUS_APPROVED,
					), 1)->getData();

					if (!empty($allianceRequests)) {
						$allianceRequest = $allianceRequests[0];
						$model->relation_property_id = $allianceRequest->requester_org_id;
						if (empty($model->relation_property_name)) {
							$relProp = Properties::fetchFromApi($model->relation_property_id);
							$model->relation_property_name = $relProp ? $relProp->name : '';
						}
					}
				}
			}
		}
		$isAllianceApproved = $allianceRequest && $allianceRequest->status == AllianceRequests::STATUS_APPROVED;

		// Load competition registrations từ bảng competition_registrations
		$competitionRegistrations = array();
		$compRegsData = CompetitionRegistrations::getApiDataProvider(array('registration_id' => $id), 200)->getData();

		// Load tất cả attendees của registration để lấy thông tin chi tiết
		$attendeesMap = array();
		$attendeesData = Attendees::getByRegistrationId($id);
		foreach ($attendeesData as $att) {
			$attId = isset($att['id']) ? $att['id'] : null;
			if ($attId) {
				$att['property_id'] = $model->property_id;
				$att['property_name'] = $model->property_name;
				$att['personal_email'] = isset($att['personal_email']) ? $att['personal_email'] : '';
				$attendeesMap[$attId] = $att;
			}
		}

		// Nếu liên quân đã được duyệt, tải thêm attendees của đối tác liên quân để phục vụ hiển thị VĐV trong đội liên quân
		if ($isAllianceApproved && $model->relation_property_id) {
			$partnerRegs = Registrations::getApiDataProvider(array(
				'event_id' => $model->event_id,
				'property_id' => $model->relation_property_id,
			), 1)->getData();
			if (!empty($partnerRegs)) {
				$partnerReg = $partnerRegs[0];
				$partnerRegId = isset($partnerReg['id']) ? $partnerReg['id'] : (isset($partnerReg->id) ? $partnerReg->id : null);
				if ($partnerRegId) {
					$partnerAttendees = Attendees::getByRegistrationId($partnerRegId);
					foreach ($partnerAttendees as $att) {
						$attId = isset($att['id']) ? $att['id'] : null;
						if ($attId) {
							$att['property_id'] = $model->relation_property_id;
							$att['property_name'] = $model->relation_property_name;
							$att['personal_email'] = isset($att['personal_email']) ? $att['personal_email'] : '';
							$attendeesMap[$attId] = $att;
						}
					}
				}
			}
		}

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

			// Lấy thông tin attendee từ map
			$attendeeId = isset($reg->attendee_id) ? $reg->attendee_id : (isset($reg['attendee_id']) ? $reg['attendee_id'] : null);
			$attendeeInfo = isset($attendeesMap[$attendeeId]) ? $attendeesMap[$attendeeId] : array();

			$attendeeName = isset($attendeeInfo['full_name']) ? $attendeeInfo['full_name'] : '';
			$positionName = isset($attendeeInfo['position_name']) ? $attendeeInfo['position_name'] : '';
			$divisionName = isset($attendeeInfo['division_name']) ? $attendeeInfo['division_name'] : '';

			$competitionRegistrations[$compId]['attendees'][] = array(
				'id' => isset($reg->id) ? $reg->id : (isset($reg['id']) ? $reg['id'] : null),
				'attendee_id' => $attendeeId,
				'attendee_name' => $attendeeName,
				'position_name' => $positionName,
				'division_name' => $divisionName,
				'personal_email' => isset($attendeeInfo['personal_email']) ? $attendeeInfo['personal_email'] : '',
				'status' => isset($reg->status) ? $reg->status : (isset($reg['status']) ? $reg['status'] : 0),
			);
		}

		// Load tên cuộc thi nếu chưa có
		foreach ($competitionRegistrations as $compId => &$compData) {
			if (empty($compData['competition_name'])) {
				$comp = Competitions::fetchFromApi($compId);
				$compData['competition_name'] = $comp ? $comp->name : '';
			}
		}
		unset($compData);

		// Load Sport Teams cho đơn vị (bao gồm cả đội liên quân)
		$sportTeams = array();
		$sportTeamMembers = array();
		if ($model->event_id && $model->property_id) {
			// Gọi API list-by-property để lấy tất cả đội của đơn vị (kể cả đội liên quân)
			$apiResult = ApiClient::get(ApiEndpoints::SPORT_TEAM_LIST_BY_PROPERTY, array(
				'property_id' => $model->property_id,
				'event_id' => $model->event_id,
			));
			$teamsData = array();
			if ($apiResult['success'] && isset($apiResult['data']['data'])) {
				$teamsData = $apiResult['data']['data'];
			} elseif ($apiResult['success'] && isset($apiResult['data']) && is_array($apiResult['data'])) {
				$teamsData = $apiResult['data'];
			}

			foreach ($teamsData as $team) {
				// Hỗ trợ cả object và array
				$isObject = is_object($team);
				$teamId = $isObject ? (isset($team->id) ? $team->id : null) : (isset($team['id']) ? $team['id'] : null);
				if ($teamId) {
					$sportName = $isObject ? (isset($team->sport_name) ? $team->sport_name : '') : (isset($team['sport_name']) ? $team['sport_name'] : '');
					$sportId = $isObject ? (isset($team->sport_id) ? $team->sport_id : null) : (isset($team['sport_id']) ? $team['sport_id'] : null);

					// Fetch sport name if not available
					if (empty($sportName) && $sportId) {
						$sport = Sports::fetchFromApi($sportId);
						$sportName = $sport ? $sport->name : '';
						if ($isObject) {
							$team->sport_name = $sportName;
						} else {
							$team['sport_name'] = $sportName;
						}
					}

					// Chỉ hiển thị danh sách tham gia của đối tác ở các nội dung có số người min >= 3
					$teamPropertyId = $isObject ? (isset($team->property_id) ? $team->property_id : null) : (isset($team['property_id']) ? $team['property_id'] : null);
					if ($teamPropertyId != $model->property_id) {
						$minPlayers = self::getSportMinPlayers($sportName);
						if ($minPlayers < 3) {
							continue; // Bỏ qua đội của đối tác nếu số lượng người tối thiểu của môn < 3
						}
					}

					// Convert to object for consistency
					if (!$isObject) {
						$teamObj = new stdClass();
						foreach ($team as $key => $value) {
							$teamObj->$key = $value;
						}
						$team = $teamObj;
					}
					$sportTeams[] = $team;

					// Kiểm tra xem API đã trả về members chưa
					$membersData = array();
					if (isset($team->members) && is_array($team->members)) {
						$membersData = $team->members;
					} else {
						// Fallback: gọi API riêng để lấy members
						$membersData = SportTeamMembers::getApiDataProvider(array('sport_team_id' => $teamId), 100)->getData();
					}

					// Lấy property_name của team để fallback
					$teamPropertyName = isset($team->property_name) ? $team->property_name : '';
					if (empty($teamPropertyName) && $teamPropertyId == $model->property_id) {
						$teamPropertyName = $model->property_name;
					} elseif (empty($teamPropertyName) && $teamPropertyId == $model->relation_property_id) {
						$teamPropertyName = $model->relation_property_name;
					}

					// Enrich member info from attendees map
					$enrichedMembers = array();
					foreach ($membersData as $member) {
						$memberIsObj = is_object($member);
						$attId = $memberIsObj ? (isset($member->attendee_id) ? $member->attendee_id : null) : (isset($member['attendee_id']) ? $member['attendee_id'] : null);
						$attInfo = isset($attendeesMap[$attId]) ? $attendeesMap[$attId] : array();

						$memberArr = $memberIsObj ? (method_exists($member, 'getAttributes') ? array_merge($member->getAttributes(), get_object_vars($member)) : get_object_vars($member)) : $member;
						if (empty($memberArr['attendee_name']) && !empty($attInfo['full_name'])) {
							$memberArr['attendee_name'] = $attInfo['full_name'];
						}
						if (empty($memberArr['position_name']) && !empty($attInfo['position_name'])) {
							$memberArr['position_name'] = $attInfo['position_name'];
						}
						if (empty($memberArr['division_name']) && !empty($attInfo['division_name'])) {
							$memberArr['division_name'] = $attInfo['division_name'];
						}
						if (empty($memberArr['property_name']) && !empty($attInfo['property_name'])) {
							$memberArr['property_name'] = $attInfo['property_name'];
						}
						// Fallback: lấy property_name từ team nếu vẫn chưa có
						if (empty($memberArr['property_name']) && !empty($teamPropertyName)) {
							$memberArr['property_name'] = $teamPropertyName;
						}
						$enrichedMembers[] = $memberArr;
					}
					$sportTeamMembers[$teamId] = $enrichedMembers;
				}
			}
		}

		// Load incoming pending alliance requests if any
		$incomingRequestsData = array();
		if ($model->event_id && $model->property_id) {
			$incomingAllianceRequests = AllianceRequests::getApiDataProvider(array(
				'event_id' => $model->event_id,
				'target_org_id' => $model->property_id,
				'status' => AllianceRequests::STATUS_PENDING,
			), 100)->getData();

			foreach ($incomingAllianceRequests as $req) {
				// Kiểm tra target_registration_id có bằng id của registration hiện tại không
				$targetRegId = isset($req->target_registration_id) ? $req->target_registration_id : null;
				if ($targetRegId && $targetRegId != $id) {
					continue;
				}

				$requesterRegId = null;
				$requesterRegs = Registrations::getApiDataProvider(array(
					'event_id' => $model->event_id,
					'property_id' => $req->requester_org_id,
				), 1)->getData();
				if (!empty($requesterRegs)) {
					$requesterRegsList = $requesterRegs;
					$requesterRegId = isset($requesterRegsList[0]['id']) ? $requesterRegsList[0]['id'] : (isset($requesterRegsList[0]->id) ? $requesterRegsList[0]->id : null);
				}

				$requesterName = $req->requester_org_name;
				if (empty($requesterName)) {
					$prop = Properties::fetchFromApi($req->requester_org_id);
					$requesterName = $prop ? $prop->name : 'Đơn vị khác';
				}

				// Lấy tên nội dung liên quân từ content_name
				$contentName = isset($req->content_name) ? $req->content_name : (isset($req['content_name']) ? $req['content_name'] : '');

				$incomingRequestsData[] = array(
					'request' => $req,
					'requester_registration_id' => $requesterRegId,
					'requester_name' => $requesterName,
					'content_name' => $contentName,
				);
			}
		}

		// Load Beauty Contestants (Miss) cho registration
		$beautyContestants = array();
		if ($model->event_id) {
			$ownAttendeeIds = array();
			foreach ($attendeesData as $att) {
				$attId = isset($att['id']) ? $att['id'] : null;
				if ($attId) {
					$ownAttendeeIds[] = $attId;
				}
			}
			if (!empty($ownAttendeeIds)) {
				$contests = BeautyContests::getApiDataProvider(array('event_id' => $model->event_id), 100)->getData();
				foreach ($contests as $contest) {
					$contestId = isset($contest->id) ? $contest->id : (isset($contest['id']) ? $contest['id'] : null);
					$contestName = isset($contest->name) ? $contest->name : (isset($contest['name']) ? $contest['name'] : '');
					if (!$contestId) continue;

					$contestants = BeautyContestants::getApiDataProvider(array('contest_id' => $contestId), 500)->getData();
					foreach ($contestants as $c) {
						$attId = isset($c->attendee_id) ? $c->attendee_id : (isset($c['attendee_id']) ? $c['attendee_id'] : null);
						if ($attId && in_array($attId, $ownAttendeeIds)) {
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
								'personal_email' => isset($c->personal_email) ? $c->personal_email : (isset($c['personal_email']) ? $c['personal_email'] : ''),
								'candidate_number' => isset($c->candidate_number) ? $c->candidate_number : (isset($c['candidate_number']) ? $c['candidate_number'] : ''),
								'height_cm' => isset($c->height_cm) ? $c->height_cm : (isset($c['height_cm']) ? $c['height_cm'] : null),
								'weight_kg' => isset($c->weight_kg) ? $c->weight_kg : (isset($c['weight_kg']) ? $c['weight_kg'] : null),
								'measurements' => isset($c->measurements) ? $c->measurements : (isset($c['measurements']) ? $c['measurements'] : ''),
								'talent' => isset($c->talent) ? $c->talent : (isset($c['talent']) ? $c['talent'] : ''),
								'bio' => isset($c->bio) ? $c->bio : (isset($c['bio']) ? $c['bio'] : ''),
								'status' => isset($c->status) ? $c->status : (isset($c['status']) ? $c['status'] : 0),
							);
						}
					}
				}
			}
		}

		// Load Talent Entries cho registration
		$talentEntries = array();
		$talentEntryMembers = array();
		$loadedEntryIds = array();
		if ($model->property_id && $model->event_id) {
			// Lấy talent shows của event
			$showsData = TalentShows::getApiDataProvider(array('event_id' => $model->event_id), 100)->getData();
			$showIds = array();
			foreach ($showsData as $show) {
				$showId = isset($show->id) ? $show->id : (isset($show['id']) ? $show['id'] : null);
				if ($showId) $showIds[] = $showId;
			}

			// Helper function để load và enrich entry
			$loadTalentEntry = function ($entry) use (&$talentEntries, &$talentEntryMembers, &$loadedEntryIds, $attendeesMap, $showIds) {
				$entryId = isset($entry->id) ? $entry->id : (isset($entry['id']) ? $entry['id'] : null);
				if (!$entryId || in_array($entryId, $loadedEntryIds)) {
					return;
				}
				$entryShowId = isset($entry->show_id) ? $entry->show_id : (isset($entry['show_id']) ? $entry['show_id'] : null);

				if ($entryId && (empty($showIds) || in_array($entryShowId, $showIds))) {
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
					$loadedEntryIds[] = $entryId;

					$membersResult = ApiClient::get(ApiEndpoints::TALENT_ENTRY_MEMBER_LIST, array('entry_id' => $entryId));
					$membersData = array();
					if ($membersResult['success'] && isset($membersResult['data'])) {
						$membersData = isset($membersResult['data']['data']) ? $membersResult['data']['data'] : $membersResult['data'];
					}
					$enrichedMembers = array();
					foreach ($membersData as $member) {
						$attId = isset($member['attendee_id']) ? $member['attendee_id'] : null;
						$attInfo = isset($attendeesMap[$attId]) ? $attendeesMap[$attId] : array();
						$memberArr = is_array($member) ? $member : array_merge($member->attributes, get_object_vars($member));
						if (empty($memberArr['attendee_name']) && !empty($attInfo['full_name'])) {
							$memberArr['attendee_name'] = $attInfo['full_name'];
						}
						if (empty($memberArr['position_name']) && !empty($attInfo['position_name'])) {
							$memberArr['position_name'] = $attInfo['position_name'];
						}
						if (empty($memberArr['division_name']) && !empty($attInfo['division_name'])) {
							$memberArr['division_name'] = $attInfo['division_name'];
						}
						if (empty($memberArr['property_id']) && !empty($attInfo['property_id'])) {
							$memberArr['property_id'] = $attInfo['property_id'];
						}
						if (empty($memberArr['property_name']) && !empty($attInfo['property_name'])) {
							$memberArr['property_name'] = $attInfo['property_name'];
						}
						$enrichedMembers[] = $memberArr;
					}
					$talentEntryMembers[$entryId] = $enrichedMembers;
				}
			};

			// Lấy tất cả talent entries của event, sau đó filter:
			// - property_id = đơn vị hiện tại (owner)
			// - HOẶC đơn vị hiện tại nằm trong alliance_property_ids
			$allEntriesData = TalentEntries::getApiDataProvider(array(
				'event_id' => $model->event_id,
			), 200)->getData();

			$currentPropertyId = (string)$model->property_id;

			// Debug log
			Yii::log('Talent Debug - currentPropertyId: ' . $currentPropertyId . ', entries count: ' . count($allEntriesData), 'info');

			foreach ($allEntriesData as $entry) {
				$entryPropertyId = isset($entry->property_id) ? (string)$entry->property_id : (isset($entry['property_id']) ? (string)$entry['property_id'] : '');
				$allianceIds = isset($entry->alliance_property_ids) ? $entry->alliance_property_ids : (isset($entry['alliance_property_ids']) ? $entry['alliance_property_ids'] : '');

				// Parse alliance_property_ids (có thể là string "1,2,3" hoặc array hoặc JSON)
				$allianceIdArray = array();
				if (!empty($allianceIds)) {
					if (is_array($allianceIds)) {
						$allianceIdArray = array_map('strval', $allianceIds);
					} elseif (is_string($allianceIds)) {
						// Thử parse JSON trước
						$decoded = json_decode($allianceIds, true);
						if (is_array($decoded)) {
							$allianceIdArray = array_map('strval', $decoded);
						} else {
							$allianceIdArray = array_map('trim', explode(',', $allianceIds));
						}
					}
				}

				// Check nếu đơn vị hiện tại là owner hoặc trong alliance
				$isOwner = ($entryPropertyId === $currentPropertyId);
				$isAlliance = in_array($currentPropertyId, $allianceIdArray, true);

				if ($isOwner || $isAlliance) {
					$loadTalentEntry($entry);
				}
			}
		}

		// Load alliance history (all requests related to this registration)
		$allianceHistory = array();
		if ($model->event_id && $model->property_id) {
			// Requests sent by this registration
			$sentRequests = AllianceRequests::getApiDataProvider(array(
				'event_id' => $model->event_id,
				'requester_org_id' => $model->property_id,
			), 50)->getData();
			foreach ($sentRequests as $req) {
				// Chỉ hiển thị requests của registration hiện tại
				$reqRegistrationId = isset($req->registration_id) ? $req->registration_id : null;
				if ($reqRegistrationId && $reqRegistrationId != $id) {
					continue;
				}

				$targetName = isset($req->target_org_name) ? $req->target_org_name : '';
				if (empty($targetName) && $req->target_org_id) {
					$prop = Properties::fetchFromApi($req->target_org_id);
					$targetName = $prop ? $prop->name : '';
				}
				$allianceHistory[] = array(
					'request' => $req,
					'type' => 'sent',
					'partner_name' => $targetName,
					'content_name' => isset($req->content_name) ? $req->content_name : '',
				);
			}
			// Requests received by this registration (exclude pending - already shown in incomingRequestsData)
			$receivedRequests = AllianceRequests::getApiDataProvider(array(
				'event_id' => $model->event_id,
				'target_org_id' => $model->property_id,
			), 50)->getData();
			foreach ($receivedRequests as $req) {
				if ($req->status == AllianceRequests::STATUS_PENDING) continue;

				// Chỉ hiển thị requests gửi đến registration hiện tại
				$targetRegId = isset($req->target_registration_id) ? $req->target_registration_id : null;
				if ($targetRegId && $targetRegId != $id) {
					continue;
				}

				$requesterName = isset($req->requester_org_name) ? $req->requester_org_name : '';
				if (empty($requesterName) && $req->requester_org_id) {
					$prop = Properties::fetchFromApi($req->requester_org_id);
					$requesterName = $prop ? $prop->name : '';
				}
				$allianceHistory[] = array(
					'request' => $req,
					'type' => 'received',
					'partner_name' => $requesterName,
					'content_name' => isset($req->content_name) ? $req->content_name : '',
				);
			}
			// Sort by requested_at desc
			usort($allianceHistory, function ($a, $b) {
				$aTime = isset($a['request']->requested_at) ? strtotime($a['request']->requested_at) : 0;
				$bTime = isset($b['request']->requested_at) ? strtotime($b['request']->requested_at) : 0;
				return $bTime - $aTime;
			});
		}

		// Load approval logs for registration
		$approvalLogs = RegistrationApprovalLogs::getHistory($id);

		$this->render('view', array(
			'model' => $model,
			'registrationDetails' => $registrationDetails,
			'competitionRegistrations' => $competitionRegistrations,
			'allianceRequest' => $allianceRequest,
			'sportTeams' => $sportTeams,
			'sportTeamMembers' => $sportTeamMembers,
			'beautyContestants' => $beautyContestants,
			'talentEntries' => $talentEntries,
			'talentEntryMembers' => $talentEntryMembers,
			'incomingRequestsData' => $incomingRequestsData,
			'allianceHistory' => $allianceHistory,
			'approvalLogs' => $approvalLogs,
			'allowedContentCodes' => $allowedContentCodes,
		));
	}

	public function actionCreate()
	{
		$model = new Registrations;

		$user = AuthHandler::getUser();
		$userPropertyCode = isset($user['property_code']) ? $user['property_code'] : null;
		$isAdmin = ($userPropertyCode === '9999');

		// Lấy property của user từ API dựa vào property_code
		$userProperty = null;
		$userPropertyId = null;
		$userRegionalId = null;
		if ($userPropertyCode) {
			$userProperty = Properties::fetchByCode($userPropertyCode);
			if ($userProperty) {
				$userPropertyId = $userProperty->id;
				$userRegionalId = $userProperty->region_id;
			}
		}

		$events = Events::getApiDataProvider(array('status' => 1), 100)->getData();

		$properties = $userProperty ? array($userProperty) : array();

		if ($isAdmin) {
			$relationProperties = Properties::getApiDataProvider(array(), 500)->getData();
		} else {
			$relationProperties = $userRegionalId ? Properties::getApiDataProvider(array('region_id' => $userRegionalId), 500)->getData() : array();
		}

		if ($userPropertyId && !$model->property_id) {
			$model->property_id = $userPropertyId;
		}

		if (isset($_POST['Registrations'])) {
			$model->setAttributes($_POST['Registrations']);
			$model->status = Registrations::STATUS_DRAFT;
			$ssoUser = AuthHandler::getUser();
			$model->submitted_by = isset($ssoUser['email']) ? $ssoUser['email'] : null;
			$existingDoc = isset($_POST['Registrations']['document']) ? $_POST['Registrations']['document'] : null;
			$uploadedFiles = $this->handleDocumentUpload($existingDoc);
			if ($uploadedFiles) {
				$model->document = $uploadedFiles;
			}

			if ($model->validate()) {

				$result = $model->storeViaApi();

				if ($result['success']) {
					$newId = isset($result['data']['id']) ? $result['data']['id'] : null;

					if ($model->relation_property_id && $model->event_id && $model->property_id) {
						$this->createAllianceRequest($model->event_id, $model->property_id, $model->relation_property_id, null, $newId);
					}

					Yii::app()->user->setFlash('success', 'Tạo phiếu đăng ký thành công.');
					$this->redirect($newId ? array('view', 'id' => $newId) : array('admin'));
				} else {
					$errorMsg = isset($result['error']) ? $result['error'] : 'Không thể tạo phiếu đăng ký.';
					$model->addError('property_id', $errorMsg);
				}
			}
		}
		// Load periods theo event_id hiện có
		$periods = array();
		if ($model->event_id) {
			$periodsData = RegistrationPeriods::getApiDataProvider(array(
				'event_id' => $model->event_id,
				'is_active' => 1,
			), 100)->getData();
			foreach ($periodsData as $p) {
				$pId = isset($p->id) ? $p->id : (isset($p['id']) ? $p['id'] : null);
				$pName = isset($p->name) ? $p->name : (isset($p['name']) ? $p['name'] : '');
				if ($pId) {
					$periods[$pId] = $pName;
				}
			}
		}

		$this->render('create', array(
			'model' => $model,
			'events' => $events,
			'periods' => $periods,
			'properties' => $properties,
			'relationProperties' => $relationProperties,
			'isAdmin' => $isAdmin,
		));
	}

	public function actionUpdate($id)
	{
		$this->checkRegistrationAccess($id);
		$model = $this->loadModelById($id);

		// Lưu lại relation_property_id cũ để so sánh
		$oldRelationPropertyId = $model->relation_property_id;

		$user = AuthHandler::getUser();
		$userPropertyId = isset($user['property_id']) ? $user['property_id'] : null;
		$userPropertyCode = isset($user['property_code']) ? $user['property_code'] : null;
		$userRegionalId = isset($user['regional_id']) ? $user['regional_id'] : null;
		$isAdmin = ($userPropertyCode === '9999');

		$events = Events::getApiDataProvider(array('status' => 1), 100)->getData();

		// Load periods theo event_id hiện có
		$periods = array();
		if ($model->event_id) {
			$periodsData = RegistrationPeriods::getApiDataProvider(array(
				'event_id' => $model->event_id,
				'is_active' => 1,
			), 100)->getData();
			foreach ($periodsData as $p) {
				$pId = isset($p->id) ? $p->id : (isset($p['id']) ? $p['id'] : null);
				$pName = isset($p->name) ? $p->name : (isset($p['name']) ? $p['name'] : '');
				if ($pId) {
					$periods[$pId] = $pName;
				}
			}
		}

		// Load properties và relationProperties
		if ($isAdmin) {
			$property = Properties::fetchFromApi($model->property_id);
			$properties = $property ? array($property) : array();
			$relationProperties = Properties::getApiDataProvider(array(), 500)->getData();
		} else {
			$properties = $userPropertyId ? Properties::getApiDataProvider(array('id' => $userPropertyId), 100)->getData() : array();
			$relationProperties = $userRegionalId ? Properties::getApiDataProvider(array('region_id' => $userRegionalId), 500)->getData() : array();
		}

		if (isset($_POST['Registrations'])) {
			$model->setAttributes($_POST['Registrations']);

			$existingDoc = isset($_POST['Registrations']['document']) ? $_POST['Registrations']['document'] : null;
			$uploadedFiles = $this->handleDocumentUpload($existingDoc);
			if ($uploadedFiles) {
				$model->document = $uploadedFiles;
			}

			if ($model->validate()) {
				$result = $model->updateViaApi();

				if ($result['success']) {
					// Xử lý alliance request khi relation_property_id thay đổi
					$newRelationPropertyId = $model->relation_property_id;
					if ($oldRelationPropertyId != $newRelationPropertyId) {
						// Xóa alliance request cũ nếu có
						if ($oldRelationPropertyId && $model->event_id && $model->property_id) {
							$this->deleteAllianceRequest($model->event_id, $model->property_id, $oldRelationPropertyId);
						}
						// Tạo alliance request mới nếu có chọn đơn vị liên quân mới
						if ($newRelationPropertyId && $model->event_id && $model->property_id) {
							$this->createAllianceRequest($model->event_id, $model->property_id, $newRelationPropertyId, null, $id);
						}
					}

					Yii::app()->user->setFlash('success', 'Cập nhật phiếu đăng ký thành công.');
					$this->redirect(array('view', 'id' => $id));
				} else {
					$model->addError('property_id', isset($result['error']) ? $result['error'] : 'Không thể cập nhật.');
				}
			}
		}

		$this->render('update', array(
			'model' => $model,
			'events' => $events,
			'periods' => $periods,
			'properties' => $properties,
			'relationProperties' => $relationProperties,
			'isAdmin' => $isAdmin,
		));
	}

	public function actionDelete($id)
	{
		$this->checkRegistrationAccess($id);
		if (Yii::app()->getRequest()->getIsPostRequest()) {
			$result = Registrations::deleteViaApi($id);

			if ($result['success']) {
				Yii::app()->user->setFlash('success', 'Xóa phiếu đăng ký thành công.');
			} else {
				Yii::app()->user->setFlash('error', isset($result['error']) ? $result['error'] : 'Không thể xóa.');
			}

			if (!Yii::app()->getRequest()->getIsAjaxRequest()) {
				$this->redirect(array('admin'));
			}
		} else {
			throw new CHttpException(400, 'Yêu cầu không hợp lệ.');
		}
	}

	public function actionSubmit($id)
	{
		$this->checkRegistrationAccess($id);
		if (Yii::app()->getRequest()->getIsPostRequest()) {
			$model = $this->loadModelById($id);
			$submittedAt = date('Y-m-d H:i:s');
			$ssoUser = AuthHandler::getUser();
			$submittedBy = isset($ssoUser['email']) ? $ssoUser['email'] : null;

			// Validate thi nghiệp vụ (content_id = 2) phải đủ max_per_org
			$validationError = $this->validateCompetitionRequirements($model);
			if ($validationError) {
				Yii::app()->user->setFlash('error', $validationError);
				$this->redirect(array('view', 'id' => $id));
				return;
			}

			$updateData = array(
				'event_id' => $model->event_id,
				'property_id' => $model->property_id,
				'relation_property_id' => $model->relation_property_id,
				'period_id' => $model->period_id,
				'document' => $model->document,
				'reviewed_by' => $model->reviewed_by,
				'reviewed_at' => $model->reviewed_at,
				'rejection_reason' => $model->rejection_reason,
				'note' => $model->note,
				'status' => Registrations::STATUS_SUBMITTED,
				'submitted_at' => $submittedAt,
				'submitted_by' => $submittedBy,
			);

			$updateData = array_filter($updateData, function ($value) {
				return $value !== null && $value !== '';
			});

			$result = $model->updateViaApi($updateData);

			if ($result['success']) {
				// Tạo registration_approval record để tracking workflow
				$approvalResult = RegistrationApprovals::createForRegistration($id);
				if (!$approvalResult['success']) {
					Yii::app()->user->setFlash('error', 'Đã nộp phiếu nhưng không tạo được luồng duyệt: ' . (isset($approvalResult['message']) ? $approvalResult['message'] : 'Lỗi không xác định'));
					$this->redirect(array('view', 'id' => $id));
					return;
				}

				// Ghi log nộp đăng ký
				$logResult = RegistrationApprovalLogs::createLog(
					$id,
					RegistrationApprovalLogs::ACTION_SUBMITTED,
					0,
					'Nộp đăng ký',
					isset($ssoUser['id']) ? $ssoUser['id'] : null,
					$submittedBy
				);
				if (!$logResult['success']) {
					Yii::app()->user->setFlash('warning', 'Không ghi được log: ' . json_encode($logResult));
				}

				$resetResult = Attendees::resetRejectedToPending($id);
				$msg = 'Đã nộp phiếu đăng ký.';
				if ($resetResult['count'] > 0) {
					$msg .= ' Đã chuyển ' . $resetResult['count'] . ' người bị từ chối về trạng thái chờ duyệt.';
				}
				Yii::app()->user->setFlash('success', $msg);
			} else {
				Yii::app()->user->setFlash('error', 'Không thể nộp phiếu đăng ký.');
			}
			$this->redirect(array('view', 'id' => $id));
		}
	}

	public function actionResubmit($id)
	{
		$this->checkRegistrationAccess($id);
		if (Yii::app()->getRequest()->getIsPostRequest()) {
			$model = $this->loadModelById($id);

			if ($model->status != Registrations::STATUS_RETURNED) {
				Yii::app()->user->setFlash('error', 'Phiếu không ở trạng thái có thể gửi lại.');
				$this->redirect(array('view', 'id' => $id));
				return;
			}

			// Validate thi nghiệp vụ (content_id = 2) phải đủ max_per_org
			$validationError = $this->validateCompetitionRequirements($model);
			if ($validationError) {
				Yii::app()->user->setFlash('error', $validationError);
				$this->redirect(array('view', 'id' => $id));
				return;
			}

			$ssoUser = AuthHandler::getUser();
			$submittedBy = isset($ssoUser['email']) ? $ssoUser['email'] : null;

			$updateData = array(
				'status'       => Registrations::STATUS_SUBMITTED,
				'submitted_at' => date('Y-m-d H:i:s'),
				'submitted_by' => $submittedBy,
			);

			$result = $model->updateViaApi($updateData);

			if ($result['success']) {
				// Ghi resubmit vào registration_approvals
				$approval = RegistrationApprovals::getActiveByRegistrationId($id);
				if ($approval) {
					RegistrationApprovals::resubmitViaApi($approval->id);
				}

				$resetResult = Attendees::resetRejectedToPending($id);
				$msg = 'Đã gửi lại phiếu đăng ký.';
				if ($resetResult['count'] > 0) {
					$msg .= ' Đã chuyển ' . $resetResult['count'] . ' người bị từ chối về trạng thái chờ duyệt.';
				}
				Yii::app()->user->setFlash('success', $msg);
			} else {
				Yii::app()->user->setFlash('error', 'Không thể gửi lại phiếu đăng ký.');
			}
			$this->redirect(array('view', 'id' => $id));
		}
	}

	public function actionApprove($id)
	{
		if (Yii::app()->getRequest()->getIsPostRequest()) {
			$model = $this->loadModelById($id);
			$model->status = Registrations::STATUS_APPROVED;
			$reviewedAt = time();
			$model->reviewed_at = $reviewedAt;
			$ssoUser = AuthHandler::getUser();
			$reviewedBy = isset($ssoUser['email']) ? $ssoUser['email'] : null;
			if ($reviewedBy) {
				$model->reviewed_by = $reviewedBy;
			}
			$result = $model->updateViaApi(array(
				'reviewed_at' => $reviewedAt,
				'reviewed_by' => $reviewedBy,
			));

			if ($result['success']) {
				Yii::app()->user->setFlash('success', 'Đã phê duyệt phiếu đăng ký.');
			} else {
				Yii::app()->user->setFlash('error', 'Không thể phê duyệt.');
			}
			$this->redirect(array('view', 'id' => $id));
		}
	}

	public function actionReject($id)
	{
		if (Yii::app()->getRequest()->getIsPostRequest()) {
			$model = $this->loadModelById($id);
			$model->status = Registrations::STATUS_REJECTED;
			$reviewedAt = time();
			$model->reviewed_at = $reviewedAt;
			$ssoUser = AuthHandler::getUser();
			$reviewedBy = isset($ssoUser['email']) ? $ssoUser['email'] : null;
			if ($reviewedBy) {
				$model->reviewed_by = $reviewedBy;
			}
			$rejectionReason = Yii::app()->getRequest()->getPost('rejection_reason', '');
			$model->rejection_reason = $rejectionReason;
			$result = $model->updateViaApi(array(
				'reviewed_at' => $reviewedAt,
				'reviewed_by' => $reviewedBy,
				'rejection_reason' => $rejectionReason,
			));

			if ($result['success']) {
				Yii::app()->user->setFlash('success', 'Đã từ chối phiếu đăng ký.');
			} else {
				Yii::app()->user->setFlash('error', 'Không thể từ chối.');
			}
			$this->redirect(array('view', 'id' => $id));
		}
	}

	public function actionApproveAlliance($request_id, $registration_id)
	{
		$this->checkRegistrationAccess($registration_id);
		$model = AllianceRequests::fetchFromApi($request_id);
		if ($model) {
			$ssoUser = AuthHandler::getUser();
			$model->status = AllianceRequests::STATUS_APPROVED;
			$model->reviewed_by = isset($ssoUser['email']) ? $ssoUser['email'] : null;
			$model->reviewed_at = date('Y-m-d H:i:s');

			$result = $model->updateViaApi();

			if ($result['success']) {
				$regModel = $this->loadModelById($registration_id);
				if ($regModel) {
					$regModel->relation_property_id = $model->requester_org_id;
					$regResult = $regModel->updateViaApi();
				}

				// Tìm các sport team member thuộc về 2 nội dung bóng đá + kéo co của đơn vị nhận yêu cầu, sau đó cập nhật sang đơn vị gửi
				try {
					if ($model->target_registration_id && $model->registration_id) {
						$targetTeams = SportTeams::getApiDataProvider(array('registration_id' => $model->target_registration_id), 1000)->getData();
						$senderTeams = SportTeams::getApiDataProvider(array('registration_id' => $model->registration_id), 1000)->getData();

						$senderTeamsMap = array();
						foreach ($senderTeams as $st) {
							$senderTeamsMap[$st->sport_id] = $st;
						}

						foreach ($targetTeams as $targetTeam) {
							if (self::isTeamSportRequiringAlliance($targetTeam->sport_name)) {
								$senderTeam = isset($senderTeamsMap[$targetTeam->sport_id]) ? $senderTeamsMap[$targetTeam->sport_id] : null;

								if (!$senderTeam) {
									// Tạo mới team cho đơn vị gửi yêu cầu
									$newTeam = new SportTeams();
									$newTeam->event_id = $model->event_id;
									$newTeam->sport_id = $targetTeam->sport_id;
									$newTeam->property_id = $model->requester_org_id;
									$newTeam->registration_id = $model->registration_id;
									$newTeam->team_name = $targetTeam->team_name;
									$newTeam->is_alliance = 1;
									$newTeam->alliance_property_ids = array($model->target_org_id);
									$newTeam->status = SportTeams::STATUS_CONFIRMED;

									$createResult = $newTeam->storeViaApi();
									if ($createResult['success']) {
										$createdTeamId = isset($createResult['data']['data']['id']) ? $createResult['data']['data']['id'] : (isset($createResult['data']['id']) ? $createResult['data']['id'] : null);
										if ($createdTeamId) {
											$senderTeam = SportTeams::fetchFromApi($createdTeamId);
											if ($senderTeam) {
												$senderTeamsMap[$targetTeam->sport_id] = $senderTeam;
											}
										}
									} else {
										Yii::log("Failed to create alliance sport team for requester org={$model->requester_org_id}: " . json_encode($createResult), 'error', 'application.alliance');
									}
								} else {
									// Cập nhật existing team
									$changed = false;
									if (!$senderTeam->is_alliance) {
										$senderTeam->is_alliance = 1;
										$changed = true;
									}
									$propIds = $senderTeam->alliance_property_ids;
									if (is_string($propIds)) {
										$propIds = array_filter(explode(',', $propIds));
									} elseif (!is_array($propIds)) {
										$propIds = array();
									}
									if (!in_array($model->target_org_id, $propIds)) {
										$propIds[] = $model->target_org_id;
										$senderTeam->alliance_property_ids = $propIds;
										$changed = true;
									}
									if ($changed) {
										$updateRes = $senderTeam->updateViaApi();
										if (!$updateRes['success']) {
											Yii::log("Failed to update alliance sport team id={$senderTeam->id} for requester: " . json_encode($updateRes), 'error', 'application.alliance');
										}
									}
								}

								if ($senderTeam && isset($senderTeam->id)) {
									// Chuyển toàn bộ thành viên sang team của đơn vị gửi
									$members = SportTeamMembers::getApiDataProvider(array('sport_team_id' => $targetTeam->id), 1000)->getData();
									foreach ($members as $member) {
										$member->sport_team_id = $senderTeam->id;
										$memberRes = $member->updateViaApi();
										if (!$memberRes['success']) {
											Yii::log("Failed to update sport team member id={$member->id} to team id={$senderTeam->id}: " . json_encode($memberRes), 'error', 'application.alliance');
										}
									}
									// Xóa team trống của đơn vị nhận
									$deleteRes = SportTeams::deleteViaApi($targetTeam->id);
									if (!$deleteRes['success']) {
										Yii::log("Failed to delete empty target sport team id={$targetTeam->id}: " . json_encode($deleteRes), 'error', 'application.alliance');
									}
								}
							}
						}
					}
				} catch (Exception $e) {
					Yii::log("Error transferring alliance sport team members: " . $e->getMessage(), 'error', 'application.alliance');
				}

				Yii::app()->user->setFlash('success', 'Đã chấp nhận yêu cầu liên quân.');
			} else {
				Yii::app()->user->setFlash('error', isset($result['error']) ? $result['error'] : 'Không thể chấp nhận yêu cầu.');
			}
		} else {
			Yii::app()->user->setFlash('error', 'Không tìm thấy yêu cầu liên quân.');
		}
		$this->redirect(array('view', 'id' => $registration_id));
	}

	public function actionRejectAlliance($request_id, $registration_id)
	{
		$this->checkRegistrationAccess($registration_id);
		Yii::log("actionRejectAlliance - request_id={$request_id}, registration_id={$registration_id}", 'info', 'application.alliance');
		$model = AllianceRequests::fetchFromApi($request_id);
		if ($model) {
			$ssoUser = AuthHandler::getUser();
			$model->status = AllianceRequests::STATUS_REJECTED;
			$model->reviewed_by = isset($ssoUser['email']) ? $ssoUser['email'] : null;
			$model->reviewed_at = date('Y-m-d H:i:s');
			$model->rejection_reason = Yii::app()->getRequest()->getParam('rejection_reason', '');

			Yii::log("actionRejectAlliance - Updating AllianceRequest model attributes: " . CJSON::encode($model->attributes), 'info', 'application.alliance');
			$result = $model->updateViaApi();
			Yii::log("actionRejectAlliance - AllianceRequest update result: " . CJSON::encode($result), 'info', 'application.alliance');

			if ($result['success']) {
				Yii::app()->user->setFlash('success', 'Đã từ chối yêu cầu liên quân.');
			} else {
				Yii::app()->user->setFlash('error', isset($result['error']) ? $result['error'] : 'Không thể từ chối yêu cầu.');
			}
		} else {
			Yii::app()->user->setFlash('error', 'Không tìm thấy yêu cầu liên quân.');
		}
		$this->redirect(array('view', 'id' => $registration_id));
	}

	protected function loadModelById($id)
	{
		$model = Registrations::fetchFromApi($id);
		if ($model === null) {
			throw new CHttpException(404, 'Không tìm thấy phiếu đăng ký.');
		}
		return $model;
	}

	/**
	 * Kiểm tra user có quyền truy cập registration không
	 * @param int $registrationId
	 * @throws CHttpException nếu không có quyền
	 */
	protected function checkRegistrationAccess($registrationId)
	{
		$ssoUser = AuthHandler::getUser();
		$userPropertyCode = isset($ssoUser['property_code']) ? $ssoUser['property_code'] : null;

		if ($userPropertyCode === '9999') {
			return; // Admin HO (code 9999) - có quyền truy cập tất cả
		}

		$userProperty = $userPropertyCode ? Properties::fetchByCode($userPropertyCode) : null;
		$userPropertyId = $userProperty ? $userProperty->id : null;

		$model = Registrations::fetchFromApi($registrationId);
		if ($model && $model->property_id != $userPropertyId) {
			throw new CHttpException(403, 'Bạn không có quyền thực hiện thao tác này.');
		}
	}

	protected function handleDocumentUpload($existingDocument = null)
	{
		$uploadedFiles = array();

		if ($existingDocument) {
			$existing = json_decode($existingDocument, true);
			if (is_array($existing)) {
				$uploadedFiles = $existing;
			} elseif ($existingDocument) {
				$uploadedFiles[] = $existingDocument;
			}
		}

		if (!isset($_FILES['document_files']) || !is_array($_FILES['document_files']['name'])) {
			return $uploadedFiles ? json_encode($uploadedFiles) : null;
		}

		$allowedTypes = array('pdf', 'doc', 'docx', 'jpg', 'jpeg', 'png');
		$maxSize = 5 * 1024 * 1024;

		$uploadDir = Yii::getPathOfAlias('webroot') . '/uploads/registrations/';
		if (!is_dir($uploadDir)) {
			mkdir($uploadDir, 0755, true);
		}

		$fileCount = count($_FILES['document_files']['name']);
		for ($i = 0; $i < $fileCount; $i++) {
			if ($_FILES['document_files']['error'][$i] !== UPLOAD_ERR_OK) {
				continue;
			}

			$ext = strtolower(pathinfo($_FILES['document_files']['name'][$i], PATHINFO_EXTENSION));
			if (!in_array($ext, $allowedTypes)) {
				continue;
			}

			if ($_FILES['document_files']['size'][$i] > $maxSize) {
				continue;
			}

			$filename = date('Ymd_His') . '_' . uniqid() . '.' . $ext;
			$filepath = $uploadDir . $filename;

			if (move_uploaded_file($_FILES['document_files']['tmp_name'][$i], $filepath)) {
				$uploadedFiles[] = Yii::app()->baseUrl . '/uploads/registrations/' . $filename;
			}
		}

		return $uploadedFiles ? json_encode($uploadedFiles) : null;
	}

	/**
	 * Validate yêu cầu thi nghiệp vụ (content_id = 2)
	 * Kiểm tra mỗi cuộc thi trong event_competitions phải đủ max_per_org người đăng ký
	 * Ngoại trừ đơn vị có has_golf = 1
	 * @param Registrations $model
	 * @return string|null Lỗi nếu không hợp lệ, null nếu OK
	 */
	protected function validateCompetitionRequirements($model)
	{
		if (!$model->period_id || !$model->event_id) {
			return null;
		}

		// Kiểm tra đơn vị có has_golf = 1 thì không validate
		if ($model->property_id) {
			$property = Properties::fetchFromApi($model->property_id);
			if ($property && isset($property->has_golf) && (int)$property->has_golf == 1) {
				return null;
			}
		}

		// Lấy content_ids của period
		$contentIds = RegistrationPeriodContents::getContentIdsByPeriod($model->period_id);

		// Chỉ validate nếu period có content_id = 2 (thi nghiệp vụ)
		if (!in_array(2, $contentIds)) {
			return null;
		}

		// Lấy danh sách cuộc thi của event từ event_competitions
		$eventCompetitions = EventCompetitions::getByEventId($model->event_id);
		if (empty($eventCompetitions)) {
			return null;
		}

		// Lấy số người đã đăng ký thi nghiệp vụ của registration này
		$compRegsData = CompetitionRegistrations::getApiDataProvider(array('registration_id' => $model->id), 500)->getData();

		// Đếm số người đăng ký theo từng competition_id
		$regCountByComp = array();
		foreach ($compRegsData as $reg) {
			$compId = isset($reg->competition_id) ? $reg->competition_id : (isset($reg['competition_id']) ? $reg['competition_id'] : null);
			if ($compId) {
				if (!isset($regCountByComp[$compId])) {
					$regCountByComp[$compId] = 0;
				}
				$regCountByComp[$compId]++;
			}
		}

		// Validate từng cuộc thi
		$errors = array();
		foreach ($eventCompetitions as $ec) {
			$compId = isset($ec['competition_id']) ? $ec['competition_id'] : (isset($ec->competition_id) ? $ec->competition_id : null);
			if (!$compId) continue;

			// Lấy thông tin cuộc thi để biết max_per_org và tên
			$competition = Competitions::fetchFromApi($compId);
			if (!$competition) continue;

			$maxPerOrg = $competition->max_per_org ? (int)$competition->max_per_org : 0;

			// Nếu max_per_org = 0 hoặc null thì không giới hạn, bỏ qua
			if ($maxPerOrg <= 0) continue;

			$currentCount = isset($regCountByComp[$compId]) ? $regCountByComp[$compId] : 0;

			if ($currentCount < $maxPerOrg) {
				$errors[] = "Cuộc thi \"{$competition->name}\" yêu cầu đủ {$maxPerOrg} người, hiện có {$currentCount} người";
			}
		}

		if (!empty($errors)) {
			return 'Không thể nộp đăng ký. ' . implode('. ', $errors) . '.';
		}

		return null;
	}

	/**
	 * Tính số lượng người tham dự cho competition
	 * Đối với đơn vị có has_golf = 1: nhân đôi số lượng cho competition id = 3 và id = 4
	 * @param int $competitionId
	 * @param int $count Số lượng gốc
	 * @param object $property Thông tin property
	 * @return int
	 */
	protected function getCompetitionAttendeeCount($competitionId, $count, $property)
	{
		$hasGolf = isset($property->has_golf) ? (int)$property->has_golf : 0;

		// Nếu đơn vị có has_golf = 1 và competition_id là 3 hoặc 4 thì nhân đôi
		if ($hasGolf == 1 && in_array($competitionId, array(3, 4))) {
			return $count * 2;
		}

		return $count;
	}

	protected function deleteAllianceRequest($eventId, $requesterOrgId, $targetOrgId)
	{
		$existing = AllianceRequests::findByRegistration($eventId, $requesterOrgId, $targetOrgId);
		if ($existing && $existing->id) {
			$result = AllianceRequests::deleteViaApi($existing->id);
			if ($result['success']) {
				Yii::log("Deleted alliance request id={$existing->id} for event=$eventId, requester=$requesterOrgId, target=$targetOrgId", 'info', 'application.alliance');
			} else {
				Yii::log("Failed to delete alliance request: " . json_encode($result), 'error', 'application.alliance');
			}
		}
	}

	protected function createAllianceRequest($eventId, $requesterOrgId, $targetOrgId, $eventContentId = null, $registrationId = null, $targetRegistrationId = null)
	{
		// Lấy period_id từ registration hiện tại để validate
		$periodId = null;
		if ($registrationId) {
			$currentReg = Registrations::fetchFromApi($registrationId);
			if ($currentReg) {
				$periodId = $currentReg->period_id;
			}
		}

		// Nếu chưa có targetRegistrationId, tự động tìm registration của đơn vị nhận
		// Điều kiện: cùng event_id, cùng period_id, deleted_at IS NULL
		if (!$targetRegistrationId && $targetOrgId && $eventId) {
			$params = array(
				'event_id' => $eventId,
				'property_id' => $targetOrgId,
				'deleted_at' => 'null',
			);
			if ($periodId) {
				$params['period_id'] = $periodId;
			}
			$targetRegs = Registrations::getApiDataProvider($params, 1)->getData();
			if (!empty($targetRegs)) {
				$targetRegistrationId = isset($targetRegs[0]['id']) ? $targetRegs[0]['id'] : (isset($targetRegs[0]->id) ? $targetRegs[0]->id : null);
			}
		}

		// Kiểm tra đơn vị nhận đã có phiếu đăng ký chưa
		if (!$targetRegistrationId) {
			return array(
				'success' => false,
				'message' => 'Đơn vị đối tác chưa khởi tạo phiếu đăng ký cho đợt đăng ký này. Vui lòng chờ đối tác khởi tạo đăng ký trước.',
			);
		}

		$ssoUser = AuthHandler::getUser();
		$alliance = new AllianceRequests;
		$alliance->event_id = $eventId;
		$alliance->requester_org_id = $requesterOrgId;
		$alliance->target_org_id = $targetOrgId;
		$alliance->requested_by = isset($ssoUser['email']) ? $ssoUser['email'] : null;
		$alliance->event_content_id = $eventContentId ? $eventContentId : null;
		$alliance->registration_id = $registrationId ? $registrationId : null;
		$alliance->target_registration_id = $targetRegistrationId ? $targetRegistrationId : null;
		$result = $alliance->storeViaApi();
		Yii::log("Alliance storeViaApi - event_content_id=$eventContentId, registration_id=$registrationId, target_registration_id=$targetRegistrationId, result: " . json_encode($result), 'info', 'application.alliance');

		return $result;
	}

	public function actionGetRelationProperties($property_id)
	{
		$property = Properties::fetchFromApi($property_id);
		$result = array();

		if ($property && $property->region_id) {
			$properties = Properties::getApiDataProvider(array('region_id' => $property->region_id), 500)->getData();
			foreach ($properties as $p) {
				$pId = isset($p['id']) ? $p['id'] : (isset($p->id) ? $p->id : null);
				if ($pId && $pId != $property_id) {
					$prefix = isset($p['prefix']) ? $p['prefix'] : (isset($p->prefix) ? $p->prefix : '');
					$result[] = array(
						'id' => $pId,
						'code' => $prefix ? $prefix : (isset($p['code']) ? $p['code'] : ''),
						'name' => isset($p['name']) ? $p['name'] : '',
					);
				}
			}
			usort($result, function ($a, $b) {
				return strcmp($a['code'], $b['code']);
			});
		}

		header('Content-Type: application/json');
		echo CJSON::encode(array('success' => true, 'data' => $result));
		Yii::app()->end();
	}

	public function actionGetAllianceProperties($registration_id)
	{
		$this->checkRegistrationAccess($registration_id);
		$eventContentId = isset($_GET['event_content_id']) ? $_GET['event_content_id'] : null;
		$registration = Registrations::fetchFromApi($registration_id);
		$result = array();

		if ($registration && $registration->property_id) {
			$property = Properties::fetchFromApi($registration->property_id);
			if ($property && $property->region_id) {
				// Get existing alliance requests filtered by event_content_id
				$params = array(
					'event_id' => $registration->event_id,
					'requester_org_id' => $registration->property_id,
				);
				if ($eventContentId) {
					$params['event_content_id'] = $eventContentId;
				}
				$existingRequests = AllianceRequests::getApiDataProvider($params, 100)->getData();
				$existingTargetIds = array();
				foreach ($existingRequests as $req) {
					$targetId = isset($req['target_org_id']) ? $req['target_org_id'] : (isset($req->target_org_id) ? $req->target_org_id : null);
					if ($targetId) {
						$existingTargetIds[] = $targetId;
					}
				}

				$properties = Properties::getApiDataProvider(array('region_id' => $property->region_id), 500)->getData();
				foreach ($properties as $p) {
					$pId = isset($p['id']) ? $p['id'] : (isset($p->id) ? $p->id : null);
					// Loại bỏ đơn vị hiện tại
					if ($pId == $registration->property_id) {
						continue;
					}
					$prefix = isset($p['prefix']) ? $p['prefix'] : (isset($p->prefix) ? $p->prefix : '');
					$result[] = array(
						'id' => $pId,
						'code' => $prefix ? $prefix : (isset($p['code']) ? $p['code'] : ''),
						'name' => isset($p['name']) ? $p['name'] : '',
						'is_selected' => in_array($pId, $existingTargetIds) ? 1 : 0,
					);
				}
				usort($result, function ($a, $b) {
					return strcmp($a['code'], $b['code']);
				});
			}
		}

		header('Content-Type: application/json');
		echo CJSON::encode(array('success' => true, 'data' => $result));
		Yii::app()->end();
	}

	public function actionSaveAllianceProperties()
	{
		if (!Yii::app()->request->isPostRequest) {
			throw new CHttpException(400, 'Yêu cầu không hợp lệ.');
		}

		$registrationId = Yii::app()->request->getPost('registration_id');
		$targetOrgIds = Yii::app()->request->getPost('target_org_ids', array());
		$eventContentId = Yii::app()->request->getPost('event_content_id');

		// Convert empty string to null
		if ($eventContentId === '' || $eventContentId === 'null') {
			$eventContentId = null;
		}

		$model = Registrations::fetchFromApi($registrationId);
		if (!$model || !$model->event_id || !$model->property_id) {
			header('Content-Type: application/json');
			echo CJSON::encode(array('success' => false, 'error' => 'Phiếu đăng ký không hợp lệ.'));
			Yii::app()->end();
		}

		$eventId = $model->event_id;
		$requesterOrgId = $model->property_id;

		// Get existing alliance requests filtered by event_content_id
		$params = array(
			'event_id' => $eventId,
			'requester_org_id' => $requesterOrgId,
		);
		if ($eventContentId) {
			$params['event_content_id'] = $eventContentId;
		}
		$existingRequests = AllianceRequests::getApiDataProvider($params, 100)->getData();

		$existingTargetIds = array();
		foreach ($existingRequests as $req) {
			$reqId = isset($req['id']) ? $req['id'] : (isset($req->id) ? $req->id : null);
			$targetId = isset($req['target_org_id']) ? $req['target_org_id'] : (isset($req->target_org_id) ? $req->target_org_id : null);

			if ($targetId) {
				$existingTargetIds[] = $targetId;
				// If it's unchecked, we delete the alliance request
				if (!in_array($targetId, $targetOrgIds)) {
					AllianceRequests::deleteViaApi($reqId);
				}
			}
		}
		// Add new ones
		$errors = array();
		if (!empty($targetOrgIds)) {
			foreach ($targetOrgIds as $targetId) {
				if (!in_array($targetId, $existingTargetIds)) {
					$result = $this->createAllianceRequest($eventId, $requesterOrgId, $targetId, $eventContentId, $registrationId);
					if ($result && !$result['success']) {
						$errors[] = $result;
					}
				}
			}
		}

		header('Content-Type: application/json');
		if (!empty($errors)) {
			$firstError = $errors[0];
			$errorMessage = isset($firstError['message']) ? $firstError['message'] : 'Có lỗi xảy ra khi tạo yêu cầu liên kết.';
			echo CJSON::encode(array('success' => false, 'message' => $errorMessage, 'errors' => $errors));
		} else {
			echo CJSON::encode(array('success' => true));
		}
		Yii::app()->end();
	}

	public function actionGetSportAttendees($registration_id)
	{
		$this->checkRegistrationAccess($registration_id);
		$sportId = Yii::app()->request->getQuery('sport_id', null);
		$result = array();

		// Lấy attendees từ registration hiện tại có role "Thi đấu thể thao"
		$attendees = Attendees::getByRegistrationId($registration_id);
		foreach ($attendees as $att) {
			$roleName = Attendees::resolveRoleNames(isset($att['role_id']) ? $att['role_id'] : '');
			// Kiểm tra role có chứa "thể thao" hoặc "thi đấu"
			if (stripos($roleName, 'thể thao') !== false || stripos($roleName, 'thi đấu') !== false) {
				$attId = $att['id'];
				$canRegister = true;
				$reason = '';

				// Kiểm tra xem người này có thể đăng ký môn sportId không
				if ($sportId) {
					$checkResult = SportTeamMembers::canRegisterSport($attId, $sportId);
					$canRegister = $checkResult['can_register'];
					$reason = $checkResult['error'];
				}

				$result[] = array(
					'id' => $attId,
					'full_name' => isset($att['full_name']) ? $att['full_name'] : '',
					'position' => isset($att['position']) ? $att['position'] : '',
					'department_name' => isset($att['department_name']) ? $att['department_name'] : '',
					'can_register' => $canRegister,
					'reason' => $reason,
				);
			}
		}

		header('Content-Type: application/json');
		echo CJSON::encode(array('success' => true, 'data' => $result));
		Yii::app()->end();
	}

	/**
	 * Lấy danh sách liên quân đã được xác nhận (approved) cho đơn vị hiện tại
	 * Dùng cho form đăng ký thể thao - chọn liên quân đã có sẵn
	 */
	public function actionGetApprovedAlliances($registration_id)
	{
		$this->checkRegistrationAccess($registration_id);
		$registration = Registrations::fetchFromApi($registration_id);
		$result = array();

		if ($registration && $registration->property_id && $registration->event_id) {
			// Lấy các alliance request đã được approved mà đơn vị này là requester hoặc target
			$params = array(
				'event_id' => $registration->event_id,
				'status' => AllianceRequests::STATUS_APPROVED,
			);
			$allRequests = AllianceRequests::getApiDataProvider($params, 500)->getData();

			$propertyId = $registration->property_id;
			$addedOrgIds = array($propertyId); // Tránh trùng lặp

			foreach ($allRequests as $req) {
				$requesterId = isset($req['requester_org_id']) ? $req['requester_org_id'] : (isset($req->requester_org_id) ? $req->requester_org_id : null);
				$targetId = isset($req['target_org_id']) ? $req['target_org_id'] : (isset($req->target_org_id) ? $req->target_org_id : null);
				$requesterName = isset($req['requester_org_name']) ? $req['requester_org_name'] : (isset($req->requester_org_name) ? $req->requester_org_name : '');
				$targetName = isset($req['target_org_name']) ? $req['target_org_name'] : (isset($req->target_org_name) ? $req->target_org_name : '');

				// Nếu đơn vị hiện tại là requester -> lấy target làm đối tác liên quân
				if ($requesterId == $propertyId && $targetId && !in_array($targetId, $addedOrgIds)) {
					$targetProperty = Properties::fetchFromApi($targetId);
					$code = $targetProperty ? ($targetProperty->prefix ?: $targetProperty->code) : '';
					$name = $targetProperty ? $targetProperty->name : $targetName;
					$result[] = array(
						'id' => $targetId,
						'code' => $code,
						'name' => $name,
					);
					$addedOrgIds[] = $targetId;
				}

				// Nếu đơn vị hiện tại là target -> lấy requester làm đối tác liên quân
				if ($targetId == $propertyId && $requesterId && !in_array($requesterId, $addedOrgIds)) {
					$requesterProperty = Properties::fetchFromApi($requesterId);
					$code = $requesterProperty ? ($requesterProperty->prefix ?: $requesterProperty->code) : '';
					$name = $requesterProperty ? $requesterProperty->name : $requesterName;
					$result[] = array(
						'id' => $requesterId,
						'code' => $code,
						'name' => $name,
					);
					$addedOrgIds[] = $requesterId;
				}
			}

			usort($result, function ($a, $b) {
				return strcmp($a['code'], $b['code']);
			});
		}

		header('Content-Type: application/json');
		echo CJSON::encode(array('success' => true, 'data' => $result));
		Yii::app()->end();
	}

	/**
	 * Kiểm tra xem đơn vị liên quân đã có team cho môn này chưa
	 * Trả về thông tin team nếu có để hiển thị cho user biết sẽ ghép vào team đó
	 */
	public function actionCheckAllianceTeam()
	{
		$registrationId = Yii::app()->request->getQuery('registration_id');
		$sportId = Yii::app()->request->getQuery('sport_id');
		$alliancePropertyIds = Yii::app()->request->getQuery('alliance_property_ids', array());

		if (!$registrationId || !$sportId) {
			header('Content-Type: application/json');
			echo CJSON::encode(array('success' => false, 'error' => 'Thiếu thông tin.'));
			Yii::app()->end();
		}

		$registration = Registrations::fetchFromApi($registrationId);
		if (!$registration) {
			header('Content-Type: application/json');
			echo CJSON::encode(array('success' => false, 'error' => 'Không tìm thấy phiếu đăng ký.'));
			Yii::app()->end();
		}

		$existingTeam = null;
		$allianceIds = is_array($alliancePropertyIds) ? $alliancePropertyIds : explode(',', $alliancePropertyIds);

		foreach ($allianceIds as $alliancePropertyId) {
			if (empty($alliancePropertyId)) continue;

			$partnerTeams = SportTeams::getApiDataProvider(array(
				'event_id' => $registration->event_id,
				'property_id' => $alliancePropertyId,
				'sport_id' => $sportId,
			), 10)->getData();

			if (!empty($partnerTeams)) {
				$partnerTeam = $partnerTeams[0];
				$teamId = isset($partnerTeam->id) ? $partnerTeam->id : (isset($partnerTeam['id']) ? $partnerTeam['id'] : null);
				$teamName = isset($partnerTeam->team_name) ? $partnerTeam->team_name : (isset($partnerTeam['team_name']) ? $partnerTeam['team_name'] : '');
				$memberCount = isset($partnerTeam->member_count) ? $partnerTeam->member_count : 0;

				// Lấy số thành viên hiện tại
				if ($teamId && !$memberCount) {
					$members = SportTeamMembers::getApiDataProvider(array('sport_team_id' => $teamId), 500)->getData();
					$memberCount = count($members);
				}

				// Lấy tên đơn vị
				$property = Properties::fetchFromApi($alliancePropertyId);
				$propertyName = $property ? ($property->prefix ?: $property->code) . ' - ' . $property->name : '';

				$existingTeam = array(
					'team_id' => $teamId,
					'team_name' => $teamName,
					'property_id' => $alliancePropertyId,
					'property_name' => $propertyName,
					'member_count' => $memberCount,
				);
				break;
			}
		}

		header('Content-Type: application/json');
		if ($existingTeam) {
			echo CJSON::encode(array(
				'success' => true,
				'has_existing_team' => true,
				'team' => $existingTeam,
				'message' => "Đơn vị {$existingTeam['property_name']} đã có đội \"{$existingTeam['team_name']}\" ({$existingTeam['member_count']} VĐV). Khi lưu, VĐV của bạn sẽ được ghép vào đội này."
			));
		} else {
			echo CJSON::encode(array(
				'success' => true,
				'has_existing_team' => false,
				'message' => 'Chưa có đội nào. Sẽ tạo đội mới khi lưu.'
			));
		}
		Yii::app()->end();
	}

	public static function getSportMaxPlayers($sportName)
	{
		if (empty($sportName)) return 9999;
		$sportNameLower = mb_strtolower($sportName, 'UTF-8');

		if (strpos($sportNameLower, 'bóng đá') !== false || strpos($sportNameLower, 'football') !== false || strpos($sportNameLower, 'soccer') !== false) {
			return 11;
		}
		if (strpos($sportNameLower, 'kéo co') !== false) {
			return 10;
		}
		if (strpos($sportNameLower, 'bơi tiếp sức') !== false || strpos($sportNameLower, 'bơi đồng đội') !== false) {
			return 4;
		}
		if (strpos($sportNameLower, 'đôi') !== false || strpos($sportNameLower, 'doubles') !== false) {
			return 2;
		}
		if (strpos($sportNameLower, 'đơn') !== false || strpos($sportNameLower, 'singles') !== false || strpos($sportNameLower, 'cờ vua') !== false || strpos($sportNameLower, 'cờ tướng') !== false || strpos($sportNameLower, 'bản đồ') !== false) {
			return 1;
		}

		if (strpos($sportNameLower, 'bóng bàn') !== false || strpos($sportNameLower, 'cầu lông') !== false || strpos($sportNameLower, 'tennis') !== false || strpos($sportNameLower, 'quần vợt') !== false || strpos($sportNameLower, 'pickleball') !== false || strpos($sportNameLower, 'pickerball') !== false) {
			return 2;
		}

		return 9999;
	}

	/**
	 * Kiểm tra môn thể thao có phải là môn đội (bóng đá, kéo co) cần liên quân không
	 */
	public static function isTeamSportRequiringAlliance($sportName)
	{
		if (empty($sportName)) return false;
		$sportNameLower = mb_strtolower($sportName, 'UTF-8');

		if (strpos($sportNameLower, 'bóng đá') !== false || strpos($sportNameLower, 'football') !== false || strpos($sportNameLower, 'soccer') !== false) {
			return true;
		}
		if (strpos($sportNameLower, 'kéo co') !== false) {
			return true;
		}
		return false;
	}

	/**
	 * Kiểm tra đơn vị có pending alliance request cho nội dung thể thao không
	 * Trả về array('has_pending' => bool, 'message' => string)
	 */
	protected function checkPendingSportAllianceRequest($eventId, $propertyId, $sportName)
	{
		if (!self::isTeamSportRequiringAlliance($sportName)) {
			return array('has_pending' => false, 'message' => '');
		}

		// Kiểm tra với vai trò đơn vị gửi yêu cầu
		$sentRequests = AllianceRequests::getApiDataProvider(array(
			'event_id' => $eventId,
			'requester_org_id' => $propertyId,
			'status' => AllianceRequests::STATUS_PENDING,
		), 10)->getData();

		if (!empty($sentRequests)) {
			return array(
				'has_pending' => true,
				'message' => 'Đơn vị đang có yêu cầu liên quân chờ xác nhận. Vui lòng chờ đối tác xác nhận hoặc hủy yêu cầu trước khi thêm thành viên đội ' . $sportName . '.'
			);
		}

		// Kiểm tra với vai trò đơn vị nhận yêu cầu
		$receivedRequests = AllianceRequests::getApiDataProvider(array(
			'event_id' => $eventId,
			'target_org_id' => $propertyId,
			'status' => AllianceRequests::STATUS_PENDING,
		), 10)->getData();

		if (!empty($receivedRequests)) {
			return array(
				'has_pending' => true,
				'message' => 'Đơn vị đang có yêu cầu liên quân chờ xác nhận. Vui lòng xác nhận hoặc từ chối yêu cầu trước khi thêm thành viên đội ' . $sportName . '.'
			);
		}

		return array('has_pending' => false, 'message' => '');
	}

	public static function getSportMinPlayers($sportName)
	{
		if (empty($sportName)) return 1;
		$sportNameLower = mb_strtolower($sportName, 'UTF-8');

		if (strpos($sportNameLower, 'bóng đá') !== false || strpos($sportNameLower, 'football') !== false || strpos($sportNameLower, 'soccer') !== false) {
			return 11;
		}
		if (strpos($sportNameLower, 'kéo co') !== false) {
			return 8;
		}
		if (strpos($sportNameLower, 'bóng chuyền') !== false || strpos($sportNameLower, 'volleyball') !== false) {
			return 6;
		}
		if (strpos($sportNameLower, 'bơi tiếp sức') !== false || strpos($sportNameLower, 'bơi đồng đội') !== false) {
			return 4;
		}
		if (strpos($sportNameLower, 'đôi') !== false || strpos($sportNameLower, 'doubles') !== false) {
			return 2;
		}
		if (strpos($sportNameLower, 'đơn') !== false || strpos($sportNameLower, 'singles') !== false || strpos($sportNameLower, 'cờ vua') !== false || strpos($sportNameLower, 'cờ tướng') !== false || strpos($sportNameLower, 'bản đồ') !== false) {
			return 1;
		}

		if (strpos($sportNameLower, 'bóng bàn') !== false || strpos($sportNameLower, 'cầu lông') !== false || strpos($sportNameLower, 'tennis') !== false || strpos($sportNameLower, 'quần vợt') !== false || strpos($sportNameLower, 'pickleball') !== false || strpos($sportNameLower, 'pickerball') !== false) {
			return 1;
		}

		return 1;
	}

	public function actionAddSportRegistration()
	{
		if (!Yii::app()->request->isPostRequest) {
			throw new CHttpException(400, 'Bad Request');
		}
		$isAjax = Yii::app()->request->isAjaxRequest;
		$registrationId = Yii::app()->request->getPost('registration_id');
		$this->checkRegistrationAccess($registrationId);
		$sportId = Yii::app()->request->getPost('sport_id');
		$alliancePropertyIds = Yii::app()->request->getPost('alliance_property_ids', array());
		$isAlliance = Yii::app()->request->getPost('is_alliance', 0);
		$teamName = Yii::app()->request->getPost('team_name');
		$note = Yii::app()->request->getPost('note');
		$attendeeIds = Yii::app()->request->getPost('attendee_ids', array());
		$attendeeNames = Yii::app()->request->getPost('attendee_names', array());
		$contentId = Yii::app()->request->getPost('content_id');

		if (!$registrationId || !$sportId || empty($attendeeIds)) {
			if ($isAjax) {
				echo CJSON::encode(array('success' => false, 'error' => 'Thiếu thông tin bắt buộc.'));
				Yii::app()->end();
			}
			Yii::app()->user->setFlash('error', 'Thiếu thông tin bắt buộc.');
			$this->redirect(array('view', 'id' => $registrationId));
			return;
		}

		$ssoUser = AuthHandler::getUser();
		$createdBy = isset($ssoUser['email']) ? $ssoUser['email'] : null;

		$registration = Registrations::fetchFromApi($registrationId);
		if (!$registration) {
			if ($isAjax) {
				echo CJSON::encode(array('success' => false, 'error' => 'Không tìm thấy phiếu đăng ký.'));
				Yii::app()->end();
			}
			Yii::app()->user->setFlash('error', 'Không tìm thấy phiếu đăng ký.');
			$this->redirect(array('admin'));
			return;
		}

		// Kiểm tra số lượng vận động viên tối đa của môn
		$sport = Sports::fetchFromApi($sportId);
		$sportName = $sport ? $sport->name : '';
		$maxPlayers = ($sport && $sport->max_per_team_member) ? (int)$sport->max_per_team_member : self::getSportMaxPlayers($sportName);
		if (count($attendeeIds) > $maxPlayers) {
			$msg = "Môn {$sportName} tối đa chỉ cho phép chọn {$maxPlayers} người.";
			if ($isAjax) {
				echo CJSON::encode(array('success' => false, 'error' => $msg));
				Yii::app()->end();
			}
			Yii::app()->user->setFlash('error', $msg);
			$this->redirect(array('view', 'id' => $registrationId));
			return;
		}

		// Kiểm tra pending alliance request cho môn bóng đá/kéo co
		$allianceCheck = $this->checkPendingSportAllianceRequest($registration->event_id, $registration->property_id, $sportName);
		if ($allianceCheck['has_pending']) {
			if ($isAjax) {
				echo CJSON::encode(array('success' => false, 'error' => $allianceCheck['message']));
				Yii::app()->end();
			}
			Yii::app()->user->setFlash('error', $allianceCheck['message']);
			$this->redirect(array('view', 'id' => $registrationId));
			return;
		}

		// Kiểm tra giới hạn: tối đa 3 bộ môn cha + không được tham gia cùng nội dung con ở nhiều team
		$errors = array();
		foreach ($attendeeIds as $idx => $attId) {
			$name = isset($attendeeNames[$idx]) ? $attendeeNames[$idx] : "ID: $attId";
			$checkResult = SportTeamMembers::canRegisterSport($attId, $sportId);
			if (!$checkResult['can_register']) {
				$errors[] = "{$name}: {$checkResult['error']}";
			}
		}
		if (!empty($errors)) {
			$msg = 'Không thể lưu. ' . implode('; ', $errors);
			if ($isAjax) {
				echo CJSON::encode(array('success' => false, 'error' => $msg));
				Yii::app()->end();
			}
			Yii::app()->user->setFlash('error', $msg);
			$this->redirect(array('view', 'id' => $registrationId));
			return;
		}

		// Nếu chọn liên quân, tìm team đã có của đơn vị liên quân để ghép vào
		$existingAllianceTeamId = null;
		$existingTeamName = null;

		if ($isAlliance && !empty($alliancePropertyIds)) {
			$allianceIds = is_array($alliancePropertyIds) ? $alliancePropertyIds : explode(',', $alliancePropertyIds);

			foreach ($allianceIds as $alliancePropertyId) {
				if (empty($alliancePropertyId)) continue;

				// Tìm team đã có của đơn vị liên quân cho môn này
				$partnerTeams = SportTeams::getApiDataProvider(array(
					'event_id' => $registration->event_id,
					'property_id' => $alliancePropertyId,
					'sport_id' => $sportId,
				), 10)->getData();

				if (!empty($partnerTeams)) {
					$partnerTeam = $partnerTeams[0];
					$existingAllianceTeamId = isset($partnerTeam->id) ? $partnerTeam->id : (isset($partnerTeam['id']) ? $partnerTeam['id'] : null);
					$existingTeamName = isset($partnerTeam->team_name) ? $partnerTeam->team_name : (isset($partnerTeam['team_name']) ? $partnerTeam['team_name'] : null);

					if ($existingAllianceTeamId) {
						// Cập nhật team để đánh dấu là liên quân và thêm property_id hiện tại vào alliance_org_ids
						$existingOrgIds = isset($partnerTeam->alliance_org_ids) ? $partnerTeam->alliance_org_ids : (isset($partnerTeam['alliance_org_ids']) ? $partnerTeam['alliance_org_ids'] : '');
						$orgIdsList = $existingOrgIds ? explode(',', $existingOrgIds) : array();
						if (!in_array($registration->property_id, $orgIdsList)) {
							$orgIdsList[] = $registration->property_id;
						}

						// Cập nhật team với is_alliance=1 và alliance_org_ids mới
						$updateTeam = new SportTeams();
						$updateTeam->id = $existingAllianceTeamId;
						$updateTeam->is_alliance = 1;
						$updateTeam->alliance_property_ids = implode(',', array_filter($orgIdsList));
						// Cập nhật tên đội nếu chưa có prefix "Liên quân"
						if ($existingTeamName && strpos($existingTeamName, 'Liên quân') === false) {
							$updateTeam->team_name = $teamName; // Dùng tên đội mới từ form
							$updateTeam->name = $teamName;
						}
						$updateTeam->updateViaApi();

						break;
					}
				}
			}
		}

		if ($existingAllianceTeamId) {
			// Kiểm tra tổng số thành viên của team (bao gồm liên quân) không vượt quá max_per_team_member
			$existingMembers = SportTeamMembers::getApiDataProvider(array('sport_team_id' => $existingAllianceTeamId), 500)->getData();
			$currentMemberCount = count($existingMembers);
			$totalAfterAdd = $currentMemberCount + count($attendeeIds);

			if ($totalAfterAdd > $maxPlayers) {
				$msg = "Môn {$sportName} tối đa chỉ cho phép {$maxPlayers} người/đội. Đội liên quân hiện có {$currentMemberCount} người, không thể thêm " . count($attendeeIds) . " người nữa.";
				if ($isAjax) {
					echo CJSON::encode(array('success' => false, 'error' => $msg));
					Yii::app()->end();
				}
				Yii::app()->user->setFlash('error', $msg);
				$this->redirect(array('view', 'id' => $registrationId));
				return;
			}

			// Thêm thành viên vào đội liên quân có sẵn của đối tác
			foreach ($attendeeIds as $idx => $attId) {
				$member = new SportTeamMembers();
				$member->sport_team_id = $existingAllianceTeamId;
				$member->attendee_id = $attId;
				$member->name = isset($attendeeNames[$idx]) ? $attendeeNames[$idx] : '';
				$member->storeViaApi();
			}
			if ($isAjax) {
				echo CJSON::encode(array('success' => true, 'message' => 'Ghép đội liên quân thành công.', 'team_id' => $existingAllianceTeamId));
				Yii::app()->end();
			}
			Yii::app()->user->setFlash('success', 'Ghép đội liên quân thành công.');
		} else {
			// Tạo SportTeam mới
			$teamModel = new SportTeams();
			$teamModel->event_id = $registration->event_id;
			$teamModel->sport_id = $sportId;
			$teamModel->property_id = $registration->property_id;
			$teamModel->name = $teamName ? $teamName : 'Team';
			$teamModel->code = $teamName ? $teamName : 'TEAM';
			$teamModel->team_name = $teamName;
			// Xác định is_alliance từ checkbox hoặc từ alliance_property_ids
			$teamModel->is_alliance = $isAlliance ? 1 : (!empty($alliancePropertyIds) ? 1 : 0);
			// Lưu alliance_property_ids dưới dạng chuỗi id1,id2,id3 vào alliance_org_ids
			$teamModel->alliance_property_ids = is_array($alliancePropertyIds) ? implode(',', $alliancePropertyIds) : $alliancePropertyIds;
			$teamModel->status = SportTeams::STATUS_CONFIRMED;
			$teamModel->registration_id = $registrationId;

			$teamResult = $teamModel->storeViaApi();
			if ($teamResult['success']) {
				$teamId = isset($teamResult['data']['data']['id']) ? $teamResult['data']['data']['id'] : (isset($teamResult['data']['id']) ? $teamResult['data']['id'] : null);
				if ($teamId) {
					// Tạo SportTeamMembers
					foreach ($attendeeIds as $idx => $attId) {
						$member = new SportTeamMembers();
						$member->sport_team_id = $teamId;
						$member->attendee_id = $attId;
						$member->name = isset($attendeeNames[$idx]) ? $attendeeNames[$idx] : '';
						$member->storeViaApi();
					}
				}
				if ($isAjax) {
					echo CJSON::encode(array('success' => true, 'message' => 'Đăng ký thể thao thành công.', 'team_id' => $teamId));
					Yii::app()->end();
				}
				Yii::app()->user->setFlash('success', 'Đăng ký thể thao thành công.');
			} else {
				if ($isAjax) {
					echo CJSON::encode(array('success' => false, 'error' => isset($teamResult['error']) ? $teamResult['error'] : 'Không thể tạo đội thi đấu.'));
					Yii::app()->end();
				}
				Yii::app()->user->setFlash('error', isset($teamResult['error']) ? $teamResult['error'] : 'Không thể tạo đội thi đấu.');
				$this->redirect(array('view', 'id' => $registrationId));
				return;
			}
		}

		// Không tạo RegistrationDetails nữa, vì môn thể thao sẽ được quản lý bởi SportTeams
		if (!$isAjax) {
			$this->redirect(array('view', 'id' => $registrationId));
		}
	}

	public function actionDeleteSportTeam($id, $registration_id)
	{
		$this->checkRegistrationAccess($registration_id);
		if (Yii::app()->getRequest()->getIsPostRequest()) {
			$team = SportTeams::fetchFromApi($id);
			$registration = Registrations::fetchFromApi($registration_id);
			if ($team && $registration && $team->property_id != $registration->property_id) {
				Yii::app()->user->setFlash('error', 'Bạn không có quyền xóa đội liên quân này.');
				$this->redirect(array('view', 'id' => $registration_id));
				return;
			}
			$result = SportTeams::deleteViaApi($id);

			if ($result['success']) {
				Yii::app()->user->setFlash('success', 'Xóa đội thể thao thành công.');
			} else {
				Yii::app()->user->setFlash('error', isset($result['error']) ? $result['error'] : 'Không thể xóa đội.');
			}

			$this->redirect(array('view', 'id' => $registration_id));
		} else {
			throw new CHttpException(400, 'Yêu cầu không hợp lệ.');
		}
	}

	public function actionDeleteTeamMember()
	{
		if (!Yii::app()->getRequest()->getIsPostRequest()) {
			throw new CHttpException(400, 'Yêu cầu không hợp lệ.');
		}

		$memberId = Yii::app()->getRequest()->getPost('member_id');
		$teamId = Yii::app()->getRequest()->getPost('team_id');
		$registrationId = Yii::app()->getRequest()->getPost('registration_id');

		if (!$memberId || !$teamId || !$registrationId) {
			echo CJSON::encode(array('success' => false, 'error' => 'Thiếu thông tin.'));
			Yii::app()->end();
		}

		$this->checkRegistrationAccess($registrationId);
		$registration = Registrations::fetchFromApi($registrationId);
		$team = SportTeams::fetchFromApi($teamId);

		if (!$team || !$registration) {
			echo CJSON::encode(array('success' => false, 'error' => 'Không tìm thấy đội hoặc phiếu đăng ký.'));
			Yii::app()->end();
		}

		// Chỉ cho phép xóa member thuộc đơn vị của mình
		$member = SportTeamMembers::fetchFromApi($memberId);
		if (!$member) {
			echo CJSON::encode(array('success' => false, 'error' => 'Không tìm thấy thành viên.'));
			Yii::app()->end();
		}

		// Kiểm tra attendee thuộc đơn vị nào
		$attendee = Attendees::fetchFromApi($member->attendee_id);
		if ($attendee && $attendee->property_id != $registration->property_id) {
			echo CJSON::encode(array('success' => false, 'error' => 'Bạn không có quyền xóa VĐV của đơn vị khác.'));
			Yii::app()->end();
		}

		$result = SportTeamMembers::deleteViaApi($memberId);
		if ($result['success']) {
			echo CJSON::encode(array('success' => true, 'message' => 'Đã xóa VĐV khỏi đội.'));
		} else {
			echo CJSON::encode(array('success' => false, 'error' => isset($result['error']) ? $result['error'] : 'Không thể xóa VĐV.'));
		}
		Yii::app()->end();
	}

	public function actionGetSportTeamDetail($id)
	{
		$team = SportTeams::fetchFromApi($id);
		if (!$team) {
			echo CJSON::encode(array('success' => false, 'error' => 'Không tìm thấy đội.'));
			Yii::app()->end();
		}

		// Fetch sport info
		$sportName = $team->sport_name;
		$maxPerTeamMember = null;
		if ($team->sport_id) {
			$sport = Sports::fetchFromApi($team->sport_id);
			if ($sport) {
				if (empty($sportName)) {
					$sportName = $sport->name;
				}
				$maxPerTeamMember = $sport->max_per_team_member ? (int)$sport->max_per_team_member : self::getSportMaxPlayers($sportName);
			}
		}

		// Lấy attendees của đơn vị hiện tại để đếm số thành viên liên quân từ đơn vị khác
		$registrationId = Yii::app()->request->getQuery('registration_id');
		$ownAttendeeIds = array();
		$ownRegId = $registrationId ? $registrationId : $team->registration_id;
		if ($ownRegId) {
			$attendees = Attendees::getByRegistrationId($ownRegId);
			foreach ($attendees as $att) {
				if (isset($att['id'])) {
					$ownAttendeeIds[] = (int)$att['id'];
				}
			}
		}

		$members = SportTeamMembers::getApiDataProvider(array('sport_team_id' => $id), 100)->getData();
		$membersArr = array();
		$allianceMemberCount = 0;
		foreach ($members as $m) {
			$isOwnMember = in_array((int)$m->attendee_id, $ownAttendeeIds);
			$membersArr[] = array(
				'id' => $m->id,
				'attendee_id' => $m->attendee_id,
				'name' => $m->name,
				'attendee_name' => $m->attendee_name,
				'is_own_member' => $isOwnMember,
			);
			if (!$isOwnMember) {
				$allianceMemberCount++;
			}
		}

		// Lấy tên các đơn vị liên quân từ alliance_org_ids
		$allianceProperties = array();
		$allianceOrgIds = isset($team->alliance_org_ids) ? $team->alliance_org_ids : '';
		if (!empty($allianceOrgIds)) {
			$orgIds = array_filter(array_map('trim', explode(',', $allianceOrgIds)));
			foreach ($orgIds as $orgId) {
				$org = Properties::fetchFromApi($orgId);
				if ($org && !empty($org->name)) {
					$allianceProperties[] = $org->name;
				}
			}
		}

		echo CJSON::encode(array(
			'success' => true,
			'data' => array(
				'team' => array(
					'id' => $team->id,
					'sport_id' => $team->sport_id,
					'sport_name' => $sportName,
					'team_name' => $team->team_name,
					'name' => $team->name,
					'is_alliance' => $team->is_alliance || !empty($allianceProperties),
					'alliance_properties' => $allianceProperties,
					'max_per_team_member' => $maxPerTeamMember,
					'alliance_member_count' => $allianceMemberCount,
				),
				'members' => $membersArr,
			),
		));
		Yii::app()->end();
	}

	public function actionUpdateSportTeam()
	{
		if (!Yii::app()->request->isPostRequest) {
			throw new CHttpException(400, 'Bad Request');
		}

		$teamId = Yii::app()->request->getPost('team_id');
		$teamName = Yii::app()->request->getPost('team_name');
		$attendeeIds = Yii::app()->request->getPost('attendee_ids', array());
		$attendeeNames = Yii::app()->request->getPost('attendee_names', array());
		$registrationId = Yii::app()->request->getPost('registration_id');

		if (!$teamId) {
			echo CJSON::encode(array('success' => false, 'error' => 'Thiếu team_id.'));
			Yii::app()->end();
		}

		// Update team name
		$team = SportTeams::fetchFromApi($teamId);
		if ($team) {
			$ssoUser = AuthHandler::getUser();
			$userPropertyCode = isset($ssoUser['property_code']) ? $ssoUser['property_code'] : null;
			if ($userPropertyCode !== '9999') {
				$userProperty = Properties::fetchByCode($userPropertyCode);
				if ($userProperty && $team->property_id != $userProperty->id) {
					$isAllianceApproved = false;
					if ($team->event_id) {
						$allianceRequest = AllianceRequests::findByRegistration(
							$team->event_id,
							$team->property_id,
							$userProperty->id
						);
						if (!$allianceRequest) {
							$allianceRequest = AllianceRequests::findByRegistration(
								$team->event_id,
								$userProperty->id,
								$team->property_id
							);
						}
						if ($allianceRequest && $allianceRequest->status == AllianceRequests::STATUS_APPROVED) {
							$isAllianceApproved = true;
						}
					}
					if (!$isAllianceApproved) {
						echo CJSON::encode(array('success' => false, 'error' => 'Bạn không có quyền sửa đội liên quân này.'));
						Yii::app()->end();
					}
				}
			}

			// Kiểm tra số lượng vận động viên tối đa của môn
			$sport = Sports::fetchFromApi($team->sport_id);
			$sportName = $sport ? $sport->name : '';
			$maxPlayers = ($sport && $sport->max_per_team_member) ? (int)$sport->max_per_team_member : self::getSportMaxPlayers($sportName);

			// Lấy attendees của đơn vị hiện tại để xác định thành viên liên quân từ đơn vị khác
			$ownAttendeeIdsForCheck = array();
			if ($registrationId) {
				$ownAttendeesCheck = Attendees::getByRegistrationId($registrationId);
				foreach ($ownAttendeesCheck as $att) {
					if (isset($att['id'])) {
						$ownAttendeeIdsForCheck[] = (int)$att['id'];
					}
				}
			}

			// Đếm số thành viên liên quân từ đơn vị khác (sẽ được giữ lại)
			$oldMembers = SportTeamMembers::getApiDataProvider(array('sport_team_id' => $teamId), 500)->getData();
			$allianceMemberCount = 0;
			$oldAttendeeIds = array();
			foreach ($oldMembers as $m) {
				$oldAttendeeIds[] = (int)$m->attendee_id;
				if (!in_array((int)$m->attendee_id, $ownAttendeeIdsForCheck)) {
					$allianceMemberCount++;
				}
			}

			// Tổng số thành viên sau khi update = liên quân giữ lại + số người submit mới
			$totalAfterUpdate = $allianceMemberCount + count($attendeeIds);
			if ($totalAfterUpdate > $maxPlayers) {
				$msg = "Môn {$sportName} tối đa chỉ cho phép {$maxPlayers} người/đội.";
				if ($allianceMemberCount > 0) {
					$msg .= " Đội liên quân hiện có {$allianceMemberCount} thành viên từ đơn vị khác, bạn chỉ có thể chọn tối đa " . ($maxPlayers - $allianceMemberCount) . " người.";
				}
				echo CJSON::encode(array('success' => false, 'error' => $msg));
				Yii::app()->end();
			}

			// Kiểm tra pending alliance request cho môn bóng đá/kéo co
			$allianceCheck = $this->checkPendingSportAllianceRequest($team->event_id, $team->property_id, $sportName);
			if ($allianceCheck['has_pending']) {
				echo CJSON::encode(array('success' => false, 'error' => $allianceCheck['message']));
				Yii::app()->end();
			}

			// Kiểm tra giới hạn: tối đa 3 bộ môn cha + không được tham gia cùng nội dung con ở nhiều team

			$errors = array();
			foreach ($attendeeIds as $idx => $attId) {
				$isAlreadyInTeam = in_array((int)$attId, $oldAttendeeIds);
				if ($isAlreadyInTeam) {
					continue; // Người đã có trong team này rồi, không cần kiểm tra
				}

				$name = isset($attendeeNames[$idx]) ? $attendeeNames[$idx] : "ID: $attId";
				$checkResult = SportTeamMembers::canRegisterSport($attId, $team->sport_id);
				if (!$checkResult['can_register']) {
					$errors[] = "{$name}: {$checkResult['error']}";
				}
			}

			if (!empty($errors)) {
				echo CJSON::encode(array(
					'success' => false,
					'error' => 'Không thể cập nhật. ' . implode('; ', $errors)
				));
				Yii::app()->end();
			}

			$team->team_name = $teamName;
			$team->name = $teamName;
			if ($registrationId) {
				$reg = Registrations::fetchFromApi($registrationId);
				if ($reg && $team->property_id == $reg->property_id) {
					$team->registration_id = $registrationId;
				}
			}
			$team->updateViaApi();
		}

		if (!$registrationId && $team) {
			$registrationId = $team->registration_id;
		}

		// Fetch own attendee IDs to identify members belonging to our unit
		$ownAttendeeIds = array();
		if ($registrationId) {
			$attendees = Attendees::getByRegistrationId($registrationId);
			foreach ($attendees as $att) {
				if (isset($att['id'])) {
					$ownAttendeeIds[] = (int)$att['id'];
				}
			}
		}

		// Delete old members belonging to the current unit only
		$oldMembers = SportTeamMembers::getApiDataProvider(array('sport_team_id' => $teamId), 500)->getData();
		foreach ($oldMembers as $m) {
			if (in_array((int)$m->attendee_id, $ownAttendeeIds)) {
				SportTeamMembers::deleteViaApi($m->id);
			}
		}

		// Create new members (only for own unit's athletes as a security safeguard)
		foreach ($attendeeIds as $idx => $attId) {
			if (!in_array((int)$attId, $ownAttendeeIds)) {
				continue;
			}
			$member = new SportTeamMembers();
			$member->sport_team_id = $teamId;
			$member->attendee_id = $attId;
			$member->code = 'T' . $teamId . '-A' . $attId;
			$member->name = isset($attendeeNames[$idx]) ? $attendeeNames[$idx] : '';
			$member->storeViaApi();
		}

		echo CJSON::encode(array('success' => true, 'message' => 'Cập nhật đội thành công.'));
		Yii::app()->end();
	}

	public function actionAdmin()
	{
		$model = new Registrations('search');
		$model->unsetAttributes();

		if (isset($_GET['Registrations'])) {
			$model->setAttributes($_GET['Registrations']);
		}

		$params = array();
		foreach ($model->attributes as $key => $value) {
			if ($value !== null && $value !== '') {
				$params[$key] = $value;
			}
		}

		// Filter theo property_id của user (nếu không phải admin HO code 9999)
		$ssoUser = AuthHandler::getUser();
		$userPropertyCode = isset($ssoUser['property_code']) ? $ssoUser['property_code'] : null;
		if ($userPropertyCode) {
			$userProperty = Properties::fetchByCode($userPropertyCode);
			if ($userProperty && $userProperty->id) {
				$params['property_id'] = $userProperty->id;
			}
		}
		$dataProvider = Registrations::getApiDataProvider($params);
		$this->render('admin', array(
			'model' => $model,
			'dataProvider' => $dataProvider,
		));
	}

	public function actionGetEventContents($event_id)
	{
		$contents = EventContents::getByEventId($event_id);
		$result = array();
		foreach ($contents as $item) {
			$result[] = array(
				'id' => isset($item['content_id']) ? $item['content_id'] : $item['id'],
				'name' => isset($item['content_name']) ? $item['content_name'] : (isset($item['name']) ? $item['name'] : ''),
				'code' => isset($item['content_code']) ? $item['content_code'] : (isset($item['code']) ? $item['code'] : ''),
			);
		}

		header('Content-Type: application/json');
		echo CJSON::encode(array('success' => true, 'data' => $result));
		Yii::app()->end();
	}

	public function actionGetContentItems($event_id, $content_type)
	{
		$result = array();
		$registrationId = Yii::app()->request->getParam('registration_id');

		if ($content_type === 'sports') {
			$sports = EventSports::getByEventId($event_id);

			// Pre-fetch all sports once to avoid N+1 API calls in the loop
			$allSportsData = Sports::getApiDataProvider(array('is_active' => 1), 1000)->getData();
			$sportsMap = array();
			foreach ($allSportsData as $sp) {
				$spId = isset($sp->id) ? $sp->id : (isset($sp['id']) ? $sp['id'] : null);
				if ($spId) {
					$sportsMap[$spId] = $sp;
				}
			}

			foreach ($sports as $item) {
				$sportId = isset($item['sport_id']) ? $item['sport_id'] : $item['id'];
				$sportName = isset($item['sport_name']) ? $item['sport_name'] : (isset($item['name']) ? $item['name'] : '');

				$sport = isset($sportsMap[$sportId]) ? $sportsMap[$sportId] : null;

				// Lấy min_members từ API response, hoặc từ Sports model, hoặc fallback về hardcode
				$minMembers = 1;
				if (isset($item['min_members']) && $item['min_members'] !== null && $item['min_members'] !== '') {
					$minMembers = (int)$item['min_members'];
				} elseif (isset($item['min_per_team_member']) && $item['min_per_team_member'] !== null && $item['min_per_team_member'] !== '') {
					$minMembers = (int)$item['min_per_team_member'];
				} elseif ($sport && isset($sport->min_per_team_member) && $sport->min_per_team_member !== null && $sport->min_per_team_member !== '') {
					$minMembers = (int)$sport->min_per_team_member;
				} else {
					$minMembers = self::getSportMinPlayers($sportName);
				}

				// Lấy max_members từ API response, hoặc từ Sports model, hoặc fallback về hardcode
				$maxMembers = null;
				if (isset($item['max_members']) && $item['max_members'] !== null && $item['max_members'] !== '') {
					$maxMembers = (int)$item['max_members'];
				} elseif (isset($item['max_per_team_member']) && $item['max_per_team_member'] !== null && $item['max_per_team_member'] !== '') {
					$maxMembers = (int)$item['max_per_team_member'];
				} elseif ($sport && isset($sport->max_per_team_member) && $sport->max_per_team_member !== null && $sport->max_per_team_member !== '') {
					$maxMembers = (int)$sport->max_per_team_member;
				} else {
					$maxMembers = self::getSportMaxPlayers($sportName);
				}

				$result[] = array(
					'id' => $sportId,
					'name' => $sportName,
					'parent_id' => isset($item['parent_id']) ? $item['parent_id'] : (($sport && isset($sport->parent_id)) ? $sport->parent_id : 0),
					'parent_name' => isset($item['parent_name']) ? $item['parent_name'] : '',
					'min_members' => $minMembers,
					'min_per_team_member' => $minMembers,
					'max_members' => $maxMembers,
					'max_per_team_member' => $maxMembers,
				);
			}
		} elseif ($content_type === 'competition') {
			$competitions = EventCompetitions::getByEventId($event_id);

			// Lấy has_golf từ registration property
			$hasGolf = 0;
			if ($registrationId) {
				$registration = Registrations::fetchFromApi($registrationId);
				if ($registration && $registration->property_id) {
					$property = Properties::fetchFromApi($registration->property_id);
					if ($property && isset($property->has_golf)) {
						$hasGolf = (int)$property->has_golf;
					}
				}
			}
			// Lấy số lượng đã đăng ký cho từng cuộc thi của registration này
			$registeredCounts = array();
			if ($registrationId) {
				$compRegs = CompetitionRegistrations::getByRegistrationId($registrationId);
				foreach ($compRegs as $reg) {
					$compId = isset($reg['competition_id']) ? $reg['competition_id'] : (isset($reg->competition_id) ? $reg->competition_id : null);
					if ($compId) {
						if (!isset($registeredCounts[$compId])) {
							$registeredCounts[$compId] = 0;
						}
						$registeredCounts[$compId]++;
					}
				}
			}

			foreach ($competitions as $item) {
				$compId = isset($item['competition_id']) ? $item['competition_id'] : $item['id'];
				$currentCount = isset($registeredCounts[$compId]) ? $registeredCounts[$compId] : 0;

				// Lấy max_per_org từ Competitions model
				$maxPerOrg = 0;
				if (isset($item['max_per_org'])) {
					$maxPerOrg = (int)$item['max_per_org'];
				} else {
					$competition = Competitions::fetchFromApi($compId);
					if ($competition) {
						$maxPerOrg = $competition->max_per_org ? (int)$competition->max_per_org : 0;
					}
				}

				// Nếu đơn vị có has_golf = 1 và competition_id là 3 hoặc 4 thì nhân đôi max_per_org
				if ($hasGolf == 1 && in_array($compId, array(3, 4))) {
					$maxPerOrg = $maxPerOrg * 2;
				}

				// Bỏ qua nếu đã đăng ký đủ số lượng (max_per_org > 0 và đã đạt giới hạn)
				if ($maxPerOrg > 0 && $currentCount >= $maxPerOrg) {
					continue;
				}

				$result[] = array(
					'id' => $compId,
					'name' => isset($item['competition_name']) ? $item['competition_name'] : (isset($item['name']) ? $item['name'] : ''),
				);
			}
		} elseif ($content_type === 'miss') {
			$contests = BeautyContests::getApiDataProvider(array('event_id' => $event_id), 100)->getData();
			foreach ($contests as $item) {
				$id = isset($item['id']) ? $item['id'] : (isset($item->id) ? $item->id : null);
				$name = isset($item['name']) ? $item['name'] : (isset($item->name) ? $item->name : '');
				if ($id) {
					$result[] = array('id' => $id, 'name' => $name);
				}
			}
		}

		header('Content-Type: application/json');
		echo CJSON::encode(array('success' => true, 'data' => $result));
		Yii::app()->end();
	}

	public function actionAddDetail()
	{
		if (!Yii::app()->getRequest()->getIsPostRequest()) {
			throw new CHttpException(400, 'Yêu cầu không hợp lệ.');
		}

		$registrationId = Yii::app()->getRequest()->getPost('registration_id');
		$contentId = Yii::app()->getRequest()->getPost('content_id');
		$contentType = Yii::app()->getRequest()->getPost('content_type');
		$itemId = Yii::app()->getRequest()->getPost('item_id');
		$quantity = Yii::app()->getRequest()->getPost('quantity', 1);
		$note = Yii::app()->getRequest()->getPost('note', '');

		$data = array(
			'registration_id' => $registrationId,
			'content_id' => $contentId,
			'quantity' => $quantity,
			'note' => $note,
		);

		if ($contentType === 'sports' && $itemId) {
			$data['sport_id'] = $itemId;
		} elseif ($contentType === 'competition' && $itemId) {
			$data['competition_id'] = $itemId;
		}

		$result = RegistrationDetails::storeViaApi($data);

		if ($result['success']) {
			Yii::app()->user->setFlash('success', 'Thêm nội dung đăng ký thành công.');
		} else {
			Yii::app()->user->setFlash('error', isset($result['error']) ? $result['error'] : 'Không thể thêm nội dung.');
		}

		$this->redirect(array('view', 'id' => $registrationId));
	}

	public function actionDeleteDetail($id, $registration_id)
	{
		$this->checkRegistrationAccess($registration_id);
		if (Yii::app()->getRequest()->getIsPostRequest()) {
			$result = RegistrationDetails::deleteViaApi($id);

			if ($result['success']) {
				Yii::app()->user->setFlash('success', 'Xóa nội dung đăng ký thành công.');
			} else {
				Yii::app()->user->setFlash('error', isset($result['error']) ? $result['error'] : 'Không thể xóa.');
			}

			$this->redirect(array('view', 'id' => $registration_id));
		} else {
			throw new CHttpException(400, 'Yêu cầu không hợp lệ.');
		}
	}

	public function actionDeleteTalentEntry($id, $registration_id)
	{
		$this->checkRegistrationAccess($registration_id);
		if (Yii::app()->getRequest()->getIsPostRequest()) {
			$result = TalentEntries::deleteViaApi($id);

			if ($result['success']) {
				Yii::app()->user->setFlash('success', 'Xóa tiết mục văn nghệ thành công.');
			} else {
				Yii::app()->user->setFlash('error', isset($result['error']) ? $result['error'] : 'Không thể xóa.');
			}

			$this->redirect(array('view', 'id' => $registration_id));
		} else {
			throw new CHttpException(400, 'Yêu cầu không hợp lệ.');
		}
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

	public function actionGetTalentEntry($id)
	{
		header('Content-Type: application/json');

		$entry = TalentEntries::fetchFromApi($id);
		if ($entry) {
			if (empty($entry->category_name) && $entry->category_id) {
				$cat = TalentCategories::fetchFromApi($entry->category_id);
				if ($cat) {
					$entry->category_name = $cat->name;
				}
			}
			$members = TalentEntryMembers::getApiDataProvider(array('entry_id' => $id), 100)->getData();
			$membersArr = array();
			foreach ($members as $m) {
				$membersArr[] = array(
					'id' => $m->id,
					'attendee_id' => $m->attendee_id,
					'name' => isset($m->attendee_name) ? $m->attendee_name : '',
				);
			}
			echo CJSON::encode(array(
				'success' => true,
				'data' => array(
					'id' => $entry->id,
					'title' => $entry->title,
					'category_id' => $entry->category_id,
					'category_name' => $entry->category_name,
					'description' => $entry->description,
					'content' => $entry->content,
					'duration_seconds' => $entry->duration_seconds,
					'music_path' => $this->cleanStorageUrl($entry->music_path),
					'video_path' => $this->cleanStorageUrl($entry->video_path),
					'director' => $entry->director,
					'director_phone' => $entry->director_phone,
					'origin' => $entry->origin,
					'participant_count' => $entry->participant_count,
					'note' => $entry->note,
				),
				'members' => $membersArr
			));
		} else {
			echo CJSON::encode(array('success' => false, 'message' => 'Không tìm thấy tiết mục.'));
		}
		Yii::app()->end();
	}

	public function actionUpdateTalentEntry($id)
	{
		header('Content-Type: application/json');

		if (!Yii::app()->getRequest()->getIsPostRequest()) {
			echo CJSON::encode(array('success' => false, 'message' => 'Yêu cầu không hợp lệ.'));
			Yii::app()->end();
		}

		$entry = TalentEntries::fetchFromApi($id);
		if (!$entry) {
			echo CJSON::encode(array('success' => false, 'message' => 'Không tìm thấy tiết mục.'));
			Yii::app()->end();
		}

		$attendeeIds = Yii::app()->request->getPost('attendee_ids', array());

		$entry->title = Yii::app()->request->getPost('title', $entry->title);

		$categoryId = Yii::app()->request->getPost('category_id');
		$entry->category_id = ($categoryId !== null && $categoryId !== '') ? (int)$categoryId : $entry->category_id;

		$entry->description = Yii::app()->request->getPost('description', $entry->description);
		$entry->content = Yii::app()->request->getPost('content', $entry->content);

		$durationSeconds = Yii::app()->request->getPost('duration_seconds');
		$entry->duration_seconds = ($durationSeconds !== null && $durationSeconds !== '') ? (int)$durationSeconds : null;

		$entry->music_path = Yii::app()->request->getPost('music_path', $entry->music_path);
		$entry->video_path = Yii::app()->request->getPost('video_path', $entry->video_path);
		$entry->director = Yii::app()->request->getPost('director', $entry->director);
		$entry->director_phone = Yii::app()->request->getPost('director_phone', $entry->director_phone);
		$entry->origin = Yii::app()->request->getPost('origin', $entry->origin);

		$participantCount = Yii::app()->request->getPost('participant_count');
		$entry->participant_count = ($participantCount !== null && $participantCount !== '') ? (int)$participantCount : null;

		$entry->note = Yii::app()->request->getPost('note', $entry->note);

		// Log request details to find the exact reason for the failure
		$result = $entry->updateViaApi();
		if ($result['success']) {
			// Xóa các thành viên cũ
			$oldMembers = TalentEntryMembers::getApiDataProvider(array('entry_id' => $id), 100)->getData();
			if (!empty($oldMembers)) {
				foreach ($oldMembers as $m) {
					TalentEntryMembers::deleteViaApi($m->id);
				}
			}

			// Thêm các thành viên mới
			foreach ($attendeeIds as $attendeeId) {
				$member = new TalentEntryMembers;
				$member->entry_id = $id;
				$member->attendee_id = $attendeeId;
				$member->storeViaApi();
			}

			echo CJSON::encode(array('success' => true, 'message' => 'Cập nhật thành công.'));
		} else {
			$error = isset($result['error']) ? $result['error'] : 'Cập nhật thất bại.';
			echo CJSON::encode(array('success' => false, 'message' => $error));
		}
		Yii::app()->end();
	}

	/**
	 * Cập nhật mô tả và nội dung tiết mục văn nghệ
	 * Chỉ đơn vị chủ quản mới được sửa
	 */
	public function actionUpdateTalentInfo($id)
	{
		header('Content-Type: application/json');

		if (!Yii::app()->getRequest()->getIsPostRequest()) {
			echo CJSON::encode(array('success' => false, 'message' => 'Yêu cầu không hợp lệ.'));
			Yii::app()->end();
		}

		$entry = TalentEntries::fetchFromApi($id);
		if (!$entry) {
			echo CJSON::encode(array('success' => false, 'message' => 'Không tìm thấy tiết mục.'));
			Yii::app()->end();
		}

		// Kiểm tra quyền: chỉ đơn vị chủ quản mới được sửa
		$user = AuthHandler::getUser();
		$userPropertyId = isset($user['property_id']) ? $user['property_id'] : null;
		if ($entry->property_id != $userPropertyId) {
			echo CJSON::encode(array('success' => false, 'message' => 'Bạn không có quyền chỉnh sửa tiết mục này.'));
			Yii::app()->end();
		}

		$entry->description = Yii::app()->request->getPost('description', $entry->description);
		$entry->content = Yii::app()->request->getPost('content', $entry->content);

		$result = $entry->updateViaApi();
		if ($result['success']) {
			echo CJSON::encode(array('success' => true, 'message' => 'Cập nhật thành công.'));
		} else {
			$error = isset($result['error']) ? $result['error'] : 'Cập nhật thất bại.';
			echo CJSON::encode(array('success' => false, 'message' => $error));
		}
		Yii::app()->end();
	}

	/**
	 * Cập nhật video và audio tiết mục văn nghệ
	 * Chỉ đơn vị chủ quản mới được sửa
	 */
	public function actionUpdateTalentMedia($id)
	{
		header('Content-Type: application/json');

		if (!Yii::app()->getRequest()->getIsPostRequest()) {
			echo CJSON::encode(array('success' => false, 'message' => 'Yêu cầu không hợp lệ.'));
			Yii::app()->end();
		}

		$entry = TalentEntries::fetchFromApi($id);
		if (!$entry) {
			echo CJSON::encode(array('success' => false, 'message' => 'Không tìm thấy tiết mục.'));
			Yii::app()->end();
		}

		// Kiểm tra quyền: chỉ đơn vị chủ quản mới được sửa
		$user = AuthHandler::getUser();
		$userPropertyId = isset($user['property_id']) ? $user['property_id'] : null;
		if ($entry->property_id != $userPropertyId) {
			echo CJSON::encode(array('success' => false, 'message' => 'Bạn không có quyền chỉnh sửa tiết mục này.'));
			Yii::app()->end();
		}

		$entry->video_path = Yii::app()->request->getPost('video_path', $entry->video_path);
		$entry->music_path = Yii::app()->request->getPost('music_path', $entry->music_path);

		$result = $entry->updateViaApi();
		if ($result['success']) {
			echo CJSON::encode(array('success' => true, 'message' => 'Cập nhật thành công.'));
		} else {
			$error = isset($result['error']) ? $result['error'] : 'Cập nhật thất bại.';
			echo CJSON::encode(array('success' => false, 'message' => $error));
		}
		Yii::app()->end();
	}

	/**
	 * Thêm thành viên vào tiết mục văn nghệ
	 * Mỗi đơn vị chỉ được thêm người của đơn vị mình
	 */
	public function actionAddTalentMember()
	{
		header('Content-Type: application/json');

		if (!Yii::app()->getRequest()->getIsPostRequest()) {
			echo CJSON::encode(array('success' => false, 'message' => 'Yêu cầu không hợp lệ.'));
			Yii::app()->end();
		}

		$entryId = Yii::app()->request->getPost('entry_id');
		$attendeeId = Yii::app()->request->getPost('attendee_id');

		if (!$entryId || !$attendeeId) {
			echo CJSON::encode(array('success' => false, 'message' => 'Thiếu thông tin bắt buộc.'));
			Yii::app()->end();
		}

		// Kiểm tra attendee có thuộc đơn vị của user hiện tại không
		$user = AuthHandler::getUser();
		$userPropertyId = isset($user['property_id']) ? $user['property_id'] : null;

		$attendee = Attendees::fetchFromApi($attendeeId);
		if (!$attendee) {
			echo CJSON::encode(array('success' => false, 'message' => 'Không tìm thấy người tham dự.'));
			Yii::app()->end();
		}

		// Kiểm tra người tham dự có thuộc registration của đơn vị user không
		$registration = Registrations::fetchFromApi($attendee->registration_id);
		if (!$registration || $registration->property_id != $userPropertyId) {
			echo CJSON::encode(array('success' => false, 'message' => 'Bạn chỉ được thêm người của đơn vị mình.'));
			Yii::app()->end();
		}

		// Kiểm tra đã có trong danh sách chưa
		$existingMembers = TalentEntryMembers::getApiDataProvider(array('entry_id' => $entryId), 100)->getData();
		foreach ($existingMembers as $m) {
			if ($m->attendee_id == $attendeeId) {
				echo CJSON::encode(array('success' => false, 'message' => 'Người này đã có trong danh sách.'));
				Yii::app()->end();
			}
		}

		$member = new TalentEntryMembers;
		$member->entry_id = $entryId;
		$member->attendee_id = $attendeeId;
		$result = $member->storeViaApi();

		if ($result['success']) {
			echo CJSON::encode(array('success' => true, 'message' => 'Thêm thành viên thành công.'));
		} else {
			$error = isset($result['error']) ? $result['error'] : 'Thêm thất bại.';
			echo CJSON::encode(array('success' => false, 'message' => $error));
		}
		Yii::app()->end();
	}

	/**
	 * Xóa thành viên khỏi tiết mục văn nghệ
	 * Mỗi đơn vị chỉ được xóa người của đơn vị mình
	 */
	public function actionRemoveTalentMember()
	{
		header('Content-Type: application/json');

		if (!Yii::app()->getRequest()->getIsPostRequest()) {
			echo CJSON::encode(array('success' => false, 'message' => 'Yêu cầu không hợp lệ.'));
			Yii::app()->end();
		}

		$memberId = Yii::app()->request->getPost('member_id');

		if (!$memberId) {
			echo CJSON::encode(array('success' => false, 'message' => 'Thiếu thông tin bắt buộc.'));
			Yii::app()->end();
		}

		// Lấy thông tin member
		$member = TalentEntryMembers::fetchFromApi($memberId);
		if (!$member) {
			echo CJSON::encode(array('success' => false, 'message' => 'Không tìm thấy thành viên.'));
			Yii::app()->end();
		}

		// Kiểm tra attendee có thuộc đơn vị của user hiện tại không
		$user = AuthHandler::getUser();
		$userPropertyId = isset($user['property_id']) ? $user['property_id'] : null;

		$attendee = Attendees::fetchFromApi($member->attendee_id);
		if ($attendee) {
			$registration = Registrations::fetchFromApi($attendee->registration_id);
			if (!$registration || $registration->property_id != $userPropertyId) {
				echo CJSON::encode(array('success' => false, 'message' => 'Bạn chỉ được xóa người của đơn vị mình.'));
				Yii::app()->end();
			}
		}

		$result = TalentEntryMembers::deleteViaApi($memberId);

		if ($result['success']) {
			echo CJSON::encode(array('success' => true, 'message' => 'Xóa thành viên thành công.'));
		} else {
			$error = isset($result['error']) ? $result['error'] : 'Xóa thất bại.';
			echo CJSON::encode(array('success' => false, 'message' => $error));
		}
		Yii::app()->end();
	}

	/**
	 * Lấy danh sách attendees của đơn vị hiện tại để thêm vào tiết mục
	 */
	public function actionGetAttendeesForTalent($registration_id)
	{
		header('Content-Type: application/json');

		$user = AuthHandler::getUser();
		$userPropertyId = isset($user['property_id']) ? $user['property_id'] : null;

		$registration = Registrations::fetchFromApi($registration_id);
		if (!$registration || $registration->property_id != $userPropertyId) {
			echo CJSON::encode(array('success' => false, 'message' => 'Không có quyền truy cập.'));
			Yii::app()->end();
		}

		$attendees = Attendees::getByRegistrationId($registration_id);
		$result = array();
		foreach ($attendees as $att) {
			$result[] = array(
				'id' => isset($att['id']) ? $att['id'] : null,
				'full_name' => isset($att['full_name']) ? $att['full_name'] : '',
				'position' => isset($att['position']) ? $att['position'] : '',
			);
		}

		echo CJSON::encode(array('success' => true, 'data' => $result));
		Yii::app()->end();
	}

	public function actionGetOrganizations()
	{
		$user = AuthHandler::getUser();
		$userPropertyCode = isset($user['property_code']) ? $user['property_code'] : null;
		$userPropertyId = isset($user['property_id']) ? $user['property_id'] : null;
		$isAdmin = ($userPropertyCode === '9999');

		$result = array();

		if ($isAdmin) {
			$properties = Properties::getApiDataProvider(array(), 500)->getData();
			foreach ($properties as $p) {
				$result[] = array(
					'id' => isset($p['id']) ? $p['id'] : (isset($p->id) ? $p->id : null),
					'code' => isset($p['code']) ? $p['code'] : (isset($p->code) ? $p->code : ''),
					'name' => isset($p['name']) ? $p['name'] : (isset($p->name) ? $p->name : ''),
				);
			}
			usort($result, function ($a, $b) {
				return strcmp($a['code'], $b['code']);
			});
		} else {
			if ($userPropertyId) {
				$property = Properties::fetchFromApi($userPropertyId);
				if ($property) {
					$result[] = array(
						'id' => $property->id,
						'code' => $property->code,
						'name' => $property->name,
					);
				}
			}
		}

		header('Content-Type: application/json');
		echo CJSON::encode(array('success' => true, 'data' => $result));
		Yii::app()->end();
	}

	public function actionGetStaffByProperty($property_id)
	{
		$result = array();
		$competitionId = isset($_GET['competition_id']) ? $_GET['competition_id'] : null;

		$allowedDepartments = array();
		if ($competitionId) {
			$competition = Competitions::fetchFromApi($competitionId);
			if ($competition) {
				$allowedDepartments = $competition->getAllowedDepartments();
			}
		}

		$property = Properties::fetchFromApi($property_id);
		if ($property && $property->code) {
			$staffs = Staffs::getListBeforeJune2026($property->code);
			foreach ($staffs as $staff) {
				$id = isset($staff['id']) ? $staff['id'] : (isset($staff->id) ? $staff->id : null);
				$fullName = isset($staff['full_name']) ? $staff['full_name'] : (isset($staff->full_name) ? $staff->full_name : '');
				$positionName = isset($staff['position_name']) ? $staff['position_name'] : (isset($staff->position_name) ? $staff->position_name : '');
				$divisionName = isset($staff['division_name']) ? $staff['division_name'] : (isset($staff->division_name) ? $staff->division_name : '');
				$code = isset($staff['code']) ? $staff['code'] : (isset($staff->code) ? $staff->code : '');
				$startDate = isset($staff['start_date']) ? $staff['start_date'] : (isset($staff->start_date) ? $staff->start_date : '');
				$departmentCode = isset($staff['division_code']) ? $staff['division_code'] : (isset($staff->division_code) ? $staff->division_code : '');

				// if (!$id) continue;

				// if (!empty($allowedDepartments) && !in_array($departmentCode, $allowedDepartments)) {
				// 	continue;
				// }

				$result[] = array(
					'id' => $id,
					'name' => $fullName,
					'position' => $positionName,
					'department_name' => $divisionName,
					'code' => $code,
					'display' => $code ? ($code . ' - ' . $fullName) : $fullName,
					'start_date' => $startDate,
				);
			}
		}

		header('Content-Type: application/json');
		echo CJSON::encode(array('success' => true, 'data' => $result));
		Yii::app()->end();
	}

	public function actionGetCompetitionInfo($competition_id)
	{
		$competition = Competitions::fetchFromApi($competition_id);
		$result = array();

		if ($competition) {
			$result = array(
				'id' => $competition->id,
				'name' => $competition->name,
				'max_per_org' => $competition->max_per_org ? (int)$competition->max_per_org : 0,
			);
		}

		header('Content-Type: application/json');
		echo CJSON::encode(array('success' => true, 'data' => $result));
		Yii::app()->end();
	}

	public function actionGetAttendeesForCompetition($registration_id)
	{
		$this->checkRegistrationAccess($registration_id);
		$result = array();
		$competitionId = isset($_GET['competition_id']) ? $_GET['competition_id'] : null;

		$attendees = Attendees::getByRegistrationId($registration_id);

		// Lấy has_golf từ registration property
		$hasGolf = 0;
		$registration = Registrations::fetchFromApi($registration_id);
		if ($registration && $registration->property_id) {
			$property = Properties::fetchFromApi($registration->property_id);
			if ($property && isset($property->has_golf)) {
				$hasGolf = (int)$property->has_golf;
			}
		}

		// Lọc theo phòng ban được phép thi nếu có competition_id
		$allowedDepartments = array();
		if ($competitionId) {
			$competition = Competitions::fetchFromApi($competitionId);
			if ($competition) {
				$allowedDepartments = $competition->getAllowedDepartments();
			}
		}

		foreach ($attendees as $att) {
			$id = isset($att['id']) ? $att['id'] : null;
			$fullName = isset($att['full_name']) ? $att['full_name'] : '';
			$staffCode = isset($att['staff_code']) ? $att['staff_code'] : '';
			$positionName = isset($att['position_name']) ? $att['position_name'] : '';
			$divisionCode = isset($att['division_code']) ? $att['division_code'] : '';

			if (!$id) continue;

			// Nếu đơn vị có has_golf = 1: cho chọn cả người có phòng ban phù hợp và người không có phòng ban
			// Ngược lại: lọc theo division_code nằm trong danh sách phòng ban được phép thi
			if (!empty($allowedDepartments)) {
				if ($hasGolf == 1) {
					// Đơn vị có golf: cho chọn người có phòng ban phù hợp HOẶC người không có phòng ban
					if (!empty($divisionCode) && !in_array($divisionCode, $allowedDepartments)) {
						continue;
					}
				} else {
					// Đơn vị thường: chỉ cho chọn người có phòng ban phù hợp
					if (!in_array($divisionCode, $allowedDepartments)) {
						continue;
					}
				}
			}

			$result[] = array(
				'id' => $id,
				'name' => $fullName,
				'code' => $staffCode,
				'position' => isset($att['position']) ? $att['position'] : (isset($att['position_name']) ? $att['position_name'] : ''),
				'division_code' => $divisionCode,
				'department_name' => isset($att['department_name']) ? $att['department_name'] : (isset($att['division_name']) ? $att['division_name'] : ''),
				'display' => $staffCode ? ($staffCode . ' - ' . $fullName) : $fullName,
			);
		}

		header('Content-Type: application/json');
		echo CJSON::encode(array('success' => true, 'data' => $result));
		Yii::app()->end();
	}

	public function actionAddCompetitionRegistration()
	{
		header('Content-Type: application/json');

		if (!Yii::app()->getRequest()->getIsPostRequest()) {
			echo CJSON::encode(array('success' => false, 'error' => 'Yêu cầu không hợp lệ.'));
			Yii::app()->end();
		}

		$registrationId = Yii::app()->getRequest()->getPost('registration_id');
		$this->checkRegistrationAccess($registrationId);
		$competitionId = Yii::app()->getRequest()->getPost('competition_id');
		$propertyId = Yii::app()->getRequest()->getPost('property_id');
		$staffIds = Yii::app()->getRequest()->getPost('staff_ids', array());
		$note = Yii::app()->getRequest()->getPost('note', '');

		if (empty($staffIds) || !is_array($staffIds)) {
			echo CJSON::encode(array('success' => false, 'error' => 'Vui lòng chọn ít nhất một nhân viên.'));
			Yii::app()->end();
		}

		$successCount = 0;
		$errorCount   = 0;
		$debugErrors  = array();
		$createdIds   = array();

		foreach ($staffIds as $staffId) {
			$regData = array(
				'registration_id' => $registrationId,
				'competition_id'  => $competitionId,
				'property_id'     => $propertyId,
				'attendee_id'     => $staffId,
				'status'          => CompetitionRegistrations::STATUS_PENDING,
				'note'            => $note,
			);
			$result = ApiClient::post(ApiEndpoints::COMPETITION_REGISTRATION_STORE, $regData);
			if ($result['success']) {
				$successCount++;
				if (isset($result['data']['data']['id'])) {
					$createdIds[] = $result['data']['data']['id'];
				} elseif (isset($result['data']['id'])) {
					$createdIds[] = $result['data']['id'];
				}
			} else {
				$errorCount++;
				$debugErrors[] = array('attendee_id' => $staffId, 'error' => $result);
			}
		}
		$message = "Đã đăng ký thành công {$successCount} người tham dự thi nghiệp vụ.";
		if ($errorCount > 0) {
			$message .= " Có {$errorCount} người không đăng ký được.";
		}

		// Load thông tin để render
		$competition = Competitions::fetchFromApi($competitionId);
		$competitionName = $competition ? $competition->name : '';

		// Load danh sách vừa đăng ký từ competition_registrations
		$registrations = CompetitionRegistrations::getApiDataProvider(array(
			'registration_id' => $registrationId,
			'competition_id'  => $competitionId,
		), 10000)->getData();

		// Load attendees map để lấy thông tin chi tiết
		$attendeesMap = array();
		$attendeesData = Attendees::getByRegistrationId($registrationId);
		foreach ($attendeesData as $att) {
			$attId = isset($att['id']) ? $att['id'] : null;
			if ($attId) {
				$attendeesMap[$attId] = $att;
			}
		}

		$attendeeList = array();
		foreach ($registrations as $reg) {
			$attendeeId = isset($reg->attendee_id) ? $reg->attendee_id : (isset($reg['attendee_id']) ? $reg['attendee_id'] : null);
			$attendeeInfo = isset($attendeesMap[$attendeeId]) ? $attendeesMap[$attendeeId] : array();

			$attendeeList[] = array(
				'id' => isset($reg->id) ? $reg->id : (isset($reg['id']) ? $reg['id'] : null),
				'attendee_id' => $attendeeId,
				'attendee_name' => isset($attendeeInfo['full_name']) ? $attendeeInfo['full_name'] : '',
				'position_name' => isset($attendeeInfo['position_name']) ? $attendeeInfo['position_name'] : '',
				'division_name' => isset($attendeeInfo['division_name']) ? $attendeeInfo['division_name'] : '',
			);
		}

		echo CJSON::encode(array(
			'success'         => true,
			'message'         => $message,
			'successCount'    => $successCount,
			'errorCount'      => $errorCount,
			'competitionId'   => $competitionId,
			'competitionName' => $competitionName,
			'attendees'       => $attendeeList,
		));
		Yii::app()->end();
	}

	public function actionGetCompetitionRegisteredAttendees($registration_id, $competition_id)
	{
		header('Content-Type: application/json');

		$registrations = CompetitionRegistrations::getApiDataProvider(array(
			'registration_id' => $registration_id,
			'competition_id'  => $competition_id,
		), 100)->getData();

		$result = array();
		foreach ($registrations as $reg) {
			$result[] = array(
				'id' => isset($reg->id) ? $reg->id : (isset($reg['id']) ? $reg['id'] : null),
				'attendee_id' => isset($reg->attendee_id) ? $reg->attendee_id : (isset($reg['attendee_id']) ? $reg['attendee_id'] : null),
			);
		}

		echo CJSON::encode(array('success' => true, 'data' => $result));
		Yii::app()->end();
	}

	public function actionUpdateCompetitionRegistration()
	{
		header('Content-Type: application/json');

		if (!Yii::app()->getRequest()->getIsPostRequest()) {
			echo CJSON::encode(array('success' => false, 'error' => 'Yêu cầu không hợp lệ.'));
			Yii::app()->end();
		}

		$registrationId = Yii::app()->getRequest()->getPost('registration_id');
		$this->checkRegistrationAccess($registrationId);
		$competitionId = Yii::app()->getRequest()->getPost('competition_id');
		$propertyId = Yii::app()->getRequest()->getPost('property_id');
		$staffIds = Yii::app()->getRequest()->getPost('staff_ids', array());
		$note = Yii::app()->getRequest()->getPost('note', '');

		// Lấy danh sách đã đăng ký hiện tại
		$existingRegs = CompetitionRegistrations::getApiDataProvider(array(
			'registration_id' => $registrationId,
			'competition_id'  => $competitionId,
		), 100)->getData();

		$existingAttendeeIds = array();
		$existingRegMap = array();
		foreach ($existingRegs as $reg) {
			$attId = isset($reg->attendee_id) ? $reg->attendee_id : (isset($reg['attendee_id']) ? $reg['attendee_id'] : null);
			$regId = isset($reg->id) ? $reg->id : (isset($reg['id']) ? $reg['id'] : null);
			if ($attId && $regId) {
				$existingAttendeeIds[] = $attId;
				$existingRegMap[$attId] = $regId;
			}
		}

		$newStaffIds = is_array($staffIds) ? $staffIds : array();

		// Xóa những người không còn trong danh sách mới
		$toDelete = array_diff($existingAttendeeIds, $newStaffIds);
		foreach ($toDelete as $attId) {
			if (isset($existingRegMap[$attId])) {
				CompetitionRegistrations::deleteViaApi($existingRegMap[$attId]);
			}
		}

		// Thêm những người mới
		$toAdd = array_diff($newStaffIds, $existingAttendeeIds);
		$successCount = 0;
		foreach ($toAdd as $attId) {
			$regData = array(
				'registration_id' => $registrationId,
				'competition_id'  => $competitionId,
				'property_id'     => $propertyId,
				'attendee_id'     => $attId,
				'status'          => CompetitionRegistrations::STATUS_PENDING,
				'note'            => $note,
			);
			$result = ApiClient::post(ApiEndpoints::COMPETITION_REGISTRATION_STORE, $regData);
			if ($result['success']) {
				$successCount++;
			}
		}

		echo CJSON::encode(array(
			'success' => true,
			'message' => 'Đã cập nhật danh sách đăng ký thi nghiệp vụ.',
			'deleted' => count($toDelete),
			'added'   => $successCount,
		));
		Yii::app()->end();
	}

	public function actionDeleteCompetitionRegistration()
	{
		header('Content-Type: application/json');

		if (!Yii::app()->getRequest()->getIsPostRequest()) {
			echo CJSON::encode(array('success' => false, 'error' => 'Yêu cầu không hợp lệ.'));
			Yii::app()->end();
		}

		$registrationId = Yii::app()->getRequest()->getPost('registration_id');
		$this->checkRegistrationAccess($registrationId);
		$competitionId = Yii::app()->getRequest()->getPost('competition_id');

		$registrations = CompetitionRegistrations::getApiDataProvider(array(
			'registration_id' => $registrationId,
			'competition_id'  => $competitionId,
		), 100)->getData();

		$deletedCount = 0;
		foreach ($registrations as $reg) {
			$regId = isset($reg->id) ? $reg->id : (isset($reg['id']) ? $reg['id'] : null);
			if ($regId) {
				$result = CompetitionRegistrations::deleteViaApi($regId);
				if ($result['success']) {
					$deletedCount++;
				}
			}
		}

		echo CJSON::encode(array(
			'success' => true,
			'message' => "Đã xóa {$deletedCount} đăng ký.",
			'deleted' => $deletedCount,
		));
		Yii::app()->end();
	}

	public function actionAddAttendeesFromStaff()
	{
		if (!Yii::app()->getRequest()->getIsPostRequest()) {
			echo CJSON::encode(array('success' => false, 'error' => 'Yêu cầu không hợp lệ.'));
			Yii::app()->end();
		}

		$registrationId = Yii::app()->getRequest()->getPost('registration_id');
		$this->checkRegistrationAccess($registrationId);
		$eventId = Yii::app()->getRequest()->getPost('event_id');
		$propertyId = Yii::app()->getRequest()->getPost('property_id');
		$roleId = Yii::app()->getRequest()->getPost('role_id');
		if (is_array($roleId)) {
			$roleId = implode(', ', $roleId);
		}
		$staffIds = Yii::app()->getRequest()->getPost('staff_ids', array());
		$checkInDate = Yii::app()->getRequest()->getPost('check_in_date');
		$checkOutDate = Yii::app()->getRequest()->getPost('check_out_date');
		$transportId = Yii::app()->getRequest()->getPost('transport_id');

		if (empty($staffIds) || !is_array($staffIds)) {
			echo CJSON::encode(array('success' => false, 'error' => 'Vui lòng chọn ít nhất một nhân viên.'));
			Yii::app()->end();
		}

		$successCount = 0;
		$errorCount = 0;

		foreach ($staffIds as $staffId) {
			$staff = Staffs::fetchFromApi($staffId);
			if (!$staff) {
				Yii::log("AddAttendeesFromStaff - Staff not found: {$staffId}", 'error', 'application.registration');
				$errorCount++;
				continue;
			}

			Yii::log("AddAttendeesFromStaff - Staff data: " . json_encode($staff->attributes), 'info', 'application.registration');

			$attendee = new Attendees;
			$attendee->event_id = $eventId;
			$attendee->registration_id = $registrationId;
			$attendee->property_id = $propertyId;
			$attendee->staff_id = $staffId;
			$attendee->role_id = $roleId;
			$attendee->full_name = $staff->full_name;
			$attendee->position = isset($staff->position_name) ? $staff->position_name : '';
			$attendee->approval_status = Attendees::APPROVAL_PENDING;
			$attendee->join_hotel_date = isset($staff->join_hotel_date) ? $staff->join_hotel_date : null;
			$attendee->gender = isset($staff->gender) ? $staff->gender : null;
			$attendee->id_card = isset($staff->id_card) ? $staff->id_card : null;
			$attendee->check_in_date = $checkInDate;
			$attendee->check_out_date = $checkOutDate;
			$attendee->transport_id = $transportId;
			$attendee->staff_code = isset($staff->code) ? $staff->code : null;
			$attendee->position_code = isset($staff->position_code) ? $staff->position_code : null;
			$attendee->position_name = isset($staff->position_name) ? $staff->position_name : null;
			$attendee->department_code = isset($staff->department_code) ? $staff->department_code : null;
			$attendee->department_name = isset($staff->department_name) ? $staff->department_name : null;
			$attendee->end_starting_date = isset($staff->end_testing_date) ? $staff->end_testing_date : null;

			$uploadedFiles = $this->handleAttendeeDocumentUpload();

			if (isset($uploadedFiles['errors']) && !empty($uploadedFiles['errors'])) {
				Yii::app()->user->setFlash('error', implode("\n", $uploadedFiles['errors']));
				$this->redirect(array('view', 'id' => $registrationId));
			}

			if (isset($uploadedFiles['portrait_path'])) {
				$attendee->portrait_path = $uploadedFiles['portrait_path'];
			}
			if (isset($uploadedFiles['cccd_front_path'])) {
				$attendee->cccd_front_path = $uploadedFiles['cccd_front_path'];
			}
			if (isset($uploadedFiles['cccd_back_path'])) {
				$attendee->cccd_back_path = $uploadedFiles['cccd_back_path'];
			}
			if (isset($uploadedFiles['contract_path'])) {
				$attendee->contract_path = $uploadedFiles['contract_path'];
			}
			$result = $attendee->storeViaApi();
			if ($result['success']) {
				$successCount++;
			} else {
				Yii::log("AddAttendeesFromStaff - Store failed: " . json_encode($result), 'error', 'application.registration');
				$errorCount++;
			}
		}

		$message = '';
		if ($successCount > 0) {
			$message = "Đã thêm thành công {$successCount} người tham dự.";
		}
		if ($errorCount > 0) {
			$message .= ($message ? ' ' : '') . "Có {$errorCount} người không thêm được.";
		}

		echo CJSON::encode(array(
			'success' => $successCount > 0,
			'message' => $message,
			'added' => $successCount,
			'failed' => $errorCount,
		));
		Yii::app()->end();
	}

	public function actionDownloadImportTemplate()
	{
		try {
			$phpExcelPath = Yii::getPathOfAlias('ext.phpexcel.Classes');
			spl_autoload_unregister(array('YiiBase', 'autoload'));
			require_once($phpExcelPath . DIRECTORY_SEPARATOR . 'PHPExcel.php');

			$objPHPExcel = new PHPExcel();

			spl_autoload_register(array('YiiBase', 'autoload'));

			$objPHPExcel->getProperties()->setCreator("System")
				->setLastModifiedBy("System")
				->setTitle("Mau import nguoi tham du")
				->setSubject("Mau import nguoi tham du");

			// Fetch roles for validation
			$rolesData = Roles::getApiDataProvider(array(), 100)->getData();
			$roleNames = array();
			foreach ($rolesData as $r) {
				$rName = isset($r['name']) ? $r['name'] : (isset($r->name) ? $r->name : '');
				if ($rName) {
					$roleNames[] = trim($rName);
				}
			}

			// Create a hidden sheet for categories
			$sheet2 = $objPHPExcel->createSheet(1);
			$sheet2->setTitle('Danh_muc');
			$row = 1;
			foreach ($roleNames as $role) {
				$sheet2->setCellValue('A' . $row, $role);
				$row++;
			}
			$sheet2->setSheetState(PHPExcel_Worksheet::SHEETSTATE_HIDDEN);
			$lastRoleRow = $row - 1;

			// Set active sheet to 1st sheet
			$sheet = $objPHPExcel->setActiveSheetIndex(0);

			// Header
			$headers = array('Họ và tên (*)', 'Số CCCD/CMND (*)', 'Phòng ban', 'Chức danh (*)', 'Vai trò 1 (*)', 'Vai trò 2', 'Vai trò 3', 'Ngày vào làm (dd/mm/yyyy) (*)', 'Ghi chú');
			$col = 'A';
			foreach ($headers as $header) {
				$sheet->setCellValue($col . '1', $header);
				$sheet->getStyle($col . '1')->getFont()->setBold(true);
				$col++;
			}

			// Add Data Validation for column E, F, G (Vai trò)
			if ($lastRoleRow >= 1) {
				for ($c = 'E'; $c <= 'G'; $c++) {
					$objValidation = $sheet->getDataValidation($c . "2:" . $c . "1000");
					$objValidation->setType(PHPExcel_Cell_DataValidation::TYPE_LIST);
					$objValidation->setErrorStyle(PHPExcel_Cell_DataValidation::STYLE_INFORMATION);
					$objValidation->setAllowBlank(true);
					$objValidation->setShowInputMessage(true);
					$objValidation->setShowErrorMessage(true);
					$objValidation->setShowDropDown(true);
					$objValidation->setPromptTitle('Chọn vai trò');
					$objValidation->setPrompt('Vui lòng chọn từ danh sách thả xuống.');
					$objValidation->setFormula1('Danh_muc!$A$1:$A$' . $lastRoleRow);
				}
			}

			// Sample data
			$sheet->setCellValue('A2', 'Nguyễn Văn A');
			$sheet->setCellValue('B2', '012345678901');
			$sheet->setCellValue('C2', 'Kinh doanh');
			$sheet->setCellValue('D2', 'Nhân viên');
			$sheet->setCellValue('E2', 'Vận động viên');
			$sheet->setCellValue('F2', 'Khách');
			$sheet->setCellValue('G2', '');
			$sheet->setCellValue('H2', '01/01/2023');
			$sheet->setCellValue('I2', 'Ghi chú mẫu');

			foreach (range('A', 'I') as $columnID) {
				$sheet->getColumnDimension($columnID)->setAutoSize(true);
			}

			header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
			header('Content-Disposition: attachment;filename="mau_import_nguoi_tham_du.xlsx"');
			header('Cache-Control: max-age=0');

			$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
			$objWriter->save('php://output');
			Yii::app()->end();
		} catch (Throwable $e) {
			die("Error: " . $e->getMessage() . " at " . $e->getFile() . ":" . $e->getLine());
		}
	}

	public function actionImportExcelAttendees()
	{
		try {
			if (!Yii::app()->getRequest()->getIsPostRequest()) {
				throw new CHttpException(400, 'Yêu cầu không hợp lệ.');
			}

			$registrationId = Yii::app()->getRequest()->getPost('registration_id');
			$this->checkRegistrationAccess($registrationId);
			$eventId = Yii::app()->getRequest()->getPost('event_id');
			$propertyId = Yii::app()->getRequest()->getPost('property_id');

			if (!isset($_FILES['excel_file']) || $_FILES['excel_file']['error'] != UPLOAD_ERR_OK) {
				Yii::app()->user->setFlash('error', 'Vui lòng chọn file Excel hợp lệ.');
				$this->redirect(array('view', 'id' => $registrationId));
			}

			$phpExcelPath = Yii::getPathOfAlias('ext.phpexcel.Classes');
			spl_autoload_unregister(array('YiiBase', 'autoload'));
			require_once($phpExcelPath . DIRECTORY_SEPARATOR . 'PHPExcel.php');

			$filePath = $_FILES['excel_file']['tmp_name'];

			try {
				$objPHPExcel = PHPExcel_IOFactory::load($filePath);
				spl_autoload_register(array('YiiBase', 'autoload'));
			} catch (Exception $e) {
				spl_autoload_register(array('YiiBase', 'autoload'));
				Yii::app()->user->setFlash('error', 'Lỗi đọc file Excel: ' . $e->getMessage());
				$this->redirect(array('view', 'id' => $registrationId));
			}

			$sheet = $objPHPExcel->getActiveSheet();
			$highestRow = $sheet->getHighestRow();
			$highestColumn = $sheet->getHighestColumn();

			if ($highestRow < 2) {
				Yii::app()->user->setFlash('error', 'File Excel không có dữ liệu.');
				$this->redirect(array('view', 'id' => $registrationId));
			}

			// Cache role names to IDs
			$rolesData = Roles::getApiDataProvider(array(), 100)->getData();
			$rolesMap = array();
			foreach ($rolesData as $r) {
				$rId = isset($r['id']) ? $r['id'] : (isset($r->id) ? $r->id : null);
				$rName = isset($r['name']) ? $r['name'] : (isset($r->name) ? $r->name : '');
				if ($rId && $rName) {
					$rolesMap[mb_strtolower(trim($rName), 'UTF-8')] = $rId;
				}
			}

			$successCount = 0;
			$errorCount = 0;

			for ($row = 2; $row <= $highestRow; $row++) {
				$fullName = trim($sheet->getCell('A' . $row)->getValue() ?? '');
				$idCard = trim($sheet->getCell('B' . $row)->getValue() ?? '');
				$department = trim($sheet->getCell('C' . $row)->getValue() ?? '');
				$position = trim($sheet->getCell('D' . $row)->getValue() ?? '');
				$role1 = trim($sheet->getCell('E' . $row)->getValue() ?? '');
				$role2 = trim($sheet->getCell('F' . $row)->getValue() ?? '');
				$role3 = trim($sheet->getCell('G' . $row)->getValue() ?? '');
				$startDateVal = $sheet->getCell('H' . $row)->getValue();
				$note = trim($sheet->getCell('I' . $row)->getValue() ?? '');

				if (empty($fullName) || empty($idCard) || empty($position) || empty($startDateVal)) {
					if (!empty($fullName)) $errorCount++;
					continue;
				}

				// Format start_date
				$startDate = null;
				if (PHPExcel_Shared_Date::isDateTime($sheet->getCell('H' . $row))) {
					$startDate = date('Y-m-d', PHPExcel_Shared_Date::ExcelToPHP($startDateVal));
				} else {
					// Cố gắng parse dạng text dd/mm/yyyy
					$dateParts = explode('/', $startDateVal);
					if (count($dateParts) == 3) {
						$startDate = $dateParts[2] . '-' . $dateParts[1] . '-' . $dateParts[0];
					}
				}

				// Parse roles
				$roleIds = array();
				$rNames = array_filter(array($role1, $role2, $role3));
				foreach ($rNames as $rName) {
					$key = mb_strtolower(trim($rName), 'UTF-8');
					if (isset($rolesMap[$key])) {
						$roleIds[] = $rolesMap[$key];
					}
				}

				$attendee = new Attendees;
				$attendee->event_id = $eventId;
				$attendee->registration_id = $registrationId;
				$attendee->property_id = $propertyId;
				$attendee->full_name = $fullName;
				$attendee->id_card = $idCard;
				$attendee->position = $position;
				$attendee->unit_label = $department;
				if (!empty($roleIds)) {
					$attendee->role_id = implode(', ', $roleIds);
				}
				$attendee->join_hotel_date = $startDate;
				$attendee->note = $note;
				$attendee->approval_status = Attendees::APPROVAL_PENDING;

				$result = $attendee->storeViaApi();
				if ($result['success']) {
					$successCount++;
				} else {
					$errorCount++;
					Yii::log("ImportExcel - Store failed for row $row: " . json_encode($result), 'error', 'application.registration');
				}
			}

			$msg = '';
			if ($successCount > 0) {
				$msg .= "Đã import thành công {$successCount} người tham dự. ";
				Yii::app()->user->setFlash('success', $msg);
			}
			if ($errorCount > 0) {
				$msgError = "Có {$errorCount} dòng dữ liệu bị lỗi hoặc thiếu thông tin.";
				Yii::app()->user->setFlash($successCount > 0 ? 'warning' : 'error', $msgError);
			}
			if ($successCount == 0 && $errorCount == 0) {
				Yii::app()->user->setFlash('warning', 'Không có dữ liệu hợp lệ để import.');
			}

			$this->redirect(array('view', 'id' => $registrationId));
		} catch (Throwable $e) {
			die("Error: " . $e->getMessage() . " at " . $e->getFile() . ":" . $e->getLine());
		}
	}

	public function actionAddAttendeeManual()
	{
		if (!Yii::app()->getRequest()->getIsPostRequest()) {
			throw new CHttpException(400, 'Yêu cầu không hợp lệ.');
		}

		$registrationId = Yii::app()->getRequest()->getPost('registration_id');
		$this->checkRegistrationAccess($registrationId);
		$eventId = Yii::app()->getRequest()->getPost('event_id');
		$propertyId = Yii::app()->getRequest()->getPost('property_id');
		$checkInDate = Yii::app()->getRequest()->getPost('check_in_date');
		$checkOutDate = Yii::app()->getRequest()->getPost('check_out_date');
		$transportId = Yii::app()->getRequest()->getPost('transport_id');
		$join_hotel_date = Yii::app()->getRequest()->getPost('join_hotel_date');
		if ($join_hotel_date === null) {
			$join_hotel_date = Yii::app()->getRequest()->getPost('start_date');
		}

		$attendee = new Attendees;
		$attendee->event_id = $eventId;
		$attendee->registration_id = $registrationId;
		$attendee->property_id = $propertyId;
		$attendee->full_name = Yii::app()->getRequest()->getPost('full_name');
		$attendee->position = Yii::app()->getRequest()->getPost('position');
		$attendee->gender = Yii::app()->getRequest()->getPost('gender');
		$roleId = Yii::app()->getRequest()->getPost('role_id');
		if (is_array($roleId)) {
			$roleId = implode(', ', $roleId);
		}
		$attendee->role_id = $roleId;
		$attendee->note = Yii::app()->getRequest()->getPost('note');
		$attendee->id_card = Yii::app()->getRequest()->getPost('id_card');
		$attendee->approval_status = Attendees::APPROVAL_PENDING;
		$attendee->join_hotel_date = $join_hotel_date;
		$attendee->check_in_date = $checkInDate;
		$attendee->check_out_date = $checkOutDate;
		$attendee->transport_id = $transportId;

		$uploadedFiles = $this->handleAttendeeDocumentUpload();
		if (isset($uploadedFiles['portrait_path'])) {
			$attendee->portrait_path = $uploadedFiles['portrait_path'];
		}
		if (isset($uploadedFiles['cccd_front_path'])) {
			$attendee->cccd_front_path = $uploadedFiles['cccd_front_path'];
		}
		if (isset($uploadedFiles['cccd_back_path'])) {
			$attendee->cccd_back_path = $uploadedFiles['cccd_back_path'];
		}
		if (isset($uploadedFiles['contract_path'])) {
			$attendee->contract_path = $uploadedFiles['contract_path'];
		}

		$result = $attendee->storeViaApi();

		// AJAX request - trả về JSON
		if (Yii::app()->request->isAjaxRequest) {
			header('Content-Type: application/json');
			if ($result['success']) {
				echo CJSON::encode(array('success' => true, 'message' => 'Đã thêm người tham dự thành công.'));
			} else {
				echo CJSON::encode(array('success' => false, 'error' => isset($result['error']) ? $result['error'] : 'Không thể thêm người tham dự.'));
			}
			Yii::app()->end();
		}

		// Normal request - redirect
		if ($result['success']) {
			Yii::app()->user->setFlash('success', 'Đã thêm người tham dự thành công.');
		} else {
			Yii::app()->user->setFlash('error', isset($result['error']) ? $result['error'] : 'Không thể thêm người tham dự.');
		}

		$this->redirect(array('view', 'id' => $registrationId));
	}

	protected function handleAttendeeDocumentUpload()
	{
		$result = array();
		$uploadDir = Yii::getPathOfAlias('webroot') . '/uploads/attendees/';
		if (!is_dir($uploadDir)) {
			mkdir($uploadDir, 0755, true);
		}

		$fileFields = array(
			'portrait_file' => 'portrait_path',
			'cccd_front_file' => 'cccd_front_path',
			'cccd_back_file' => 'cccd_back_path',
			'contract_file' => 'contract_path',
		);

		$allowedTypes = array('jpg', 'jpeg', 'png', 'gif', 'pdf');
		$maxSize = 50 * 1024 * 1024; // Increase to 10MB to support PDF contracts

		foreach ($fileFields as $fieldName => $attrName) {
			if (!isset($_FILES[$fieldName]) || $_FILES[$fieldName]['error'] === UPLOAD_ERR_NO_FILE) {
				continue;
			}

			if ($_FILES[$fieldName]['error'] !== UPLOAD_ERR_OK) {
				$result['errors'][] = "Lỗi khi tải lên {$fieldName}: Mã lỗi " . $_FILES[$fieldName]['error'];
				continue;
			}

			$ext = strtolower(pathinfo($_FILES[$fieldName]['name'], PATHINFO_EXTENSION));
			if (!in_array($ext, $allowedTypes)) {
				continue;
			}

			if ($_FILES[$fieldName]['size'] > $maxSize) {
				continue;
			}

			$filename = date('Ymd_His') . '_' . uniqid() . '.' . $ext;
			$filepath = $uploadDir . $filename;

			if (move_uploaded_file($_FILES[$fieldName]['tmp_name'], $filepath)) {
				$result[$attrName] = Yii::app()->baseUrl . '/uploads/attendees/' . $filename;
			}
		}

		return $result;
	}

	public function actionDeleteAttendee($id, $registration_id)
	{
		$this->checkRegistrationAccess($registration_id);
		if (Yii::app()->getRequest()->getIsPostRequest()) {

			// 1. Xóa đăng ký thi đấu thể thao (SportTeamMembers)
			// Theo dõi xem những SportTeam nào bị ảnh hưởng để kiểm tra dọn dẹp sau đó
			$affectedTeamIds = array();

			$resSport = ApiClient::get(ApiEndpoints::SPORT_TEAM_MEMBER_LIST, array(
				'attendee_id' => $id,
				'per_page' => 500,
			));
			if ($resSport['success'] && isset($resSport['data'])) {
				$items = isset($resSport['data']['data']) ? $resSport['data']['data'] : $resSport['data'];
				if (is_array($items)) {
					foreach ($items as $item) {
						// KIỂM TRA CHẶT CHẼ ATTENDEE_ID TRƯỚC KHI XÓA
						if (isset($item['attendee_id']) && $item['attendee_id'] == $id) {
							$itemId = isset($item['id']) ? $item['id'] : null;
							$teamId = isset($item['sport_team_id']) ? $item['sport_team_id'] : (isset($item['team_id']) ? $item['team_id'] : null);
							if ($itemId) {
								SportTeamMembers::deleteViaApi($itemId);
								if ($teamId && !in_array($teamId, $affectedTeamIds)) {
									$affectedTeamIds[] = $teamId;
								}
							}
						}
					}
				}
			}

			// 2. Xóa đăng ký thi nghiệp vụ (CompetitionRegistrations)
			// Theo dõi xem những cuộc thi nghiệp vụ nào bị ảnh hưởng
			$affectedCompetitionIds = array();

			$resComp = ApiClient::get(ApiEndpoints::COMPETITION_REGISTRATION_LIST, array(
				'attendee_id' => $id,
				'per_page' => 500,
			));
			if ($resComp['success'] && isset($resComp['data'])) {
				$items = isset($resComp['data']['data']) ? $resComp['data']['data'] : $resComp['data'];
				if (is_array($items)) {
					foreach ($items as $item) {
						// KIỂM TRA CHẶT CHẼ ATTENDEE_ID TRƯỚC KHI XÓA
						if (isset($item['attendee_id']) && $item['attendee_id'] == $id) {
							$itemId = isset($item['id']) ? $item['id'] : null;
							$compId = isset($item['competition_id']) ? $item['competition_id'] : null;
							if ($itemId) {
								CompetitionRegistrations::deleteViaApi($itemId);
								if ($compId && !in_array($compId, $affectedCompetitionIds)) {
									$affectedCompetitionIds[] = $compId;
								}
							}
						}
					}
				}
			}

			// 3. Xóa đăng ký thi Miss (BeautyContestants)
			$resMiss = ApiClient::get(ApiEndpoints::BEAUTY_CONTESTANT_LIST, array(
				'attendee_id' => $id,
				'per_page' => 500,
			));
			if ($resMiss['success'] && isset($resMiss['data'])) {
				$items = isset($resMiss['data']['data']) ? $resMiss['data']['data'] : $resMiss['data'];
				if (is_array($items)) {
					foreach ($items as $item) {
						// KIỂM TRA CHẶT CHẼ ATTENDEE_ID TRƯỚC KHI XÓA
						if (isset($item['attendee_id']) && $item['attendee_id'] == $id) {
							$itemId = isset($item['id']) ? $item['id'] : null;
							if ($itemId) {
								BeautyContestants::deleteViaApi($itemId);
							}
						}
					}
				}
			}

			// 4. Xóa đăng ký văn nghệ (TalentEntryMembers)
			// Theo dõi xem những tiết mục văn nghệ nào bị ảnh hưởng
			$affectedEntryIds = array();

			$resTalent = ApiClient::get(ApiEndpoints::TALENT_ENTRY_MEMBER_LIST, array(
				'attendee_id' => $id,
				'per_page' => 500,
			));
			if ($resTalent['success'] && isset($resTalent['data'])) {
				$items = isset($resTalent['data']['data']) ? $resTalent['data']['data'] : $resTalent['data'];
				if (is_array($items)) {
					foreach ($items as $item) {
						// KIỂM TRA CHẶT CHẼ ATTENDEE_ID TRƯỚC KHI XÓA
						if (isset($item['attendee_id']) && $item['attendee_id'] == $id) {
							$itemId = isset($item['id']) ? $item['id'] : null;
							$entryId = isset($item['entry_id']) ? $item['entry_id'] : null;
							if ($itemId) {
								TalentEntryMembers::deleteViaApi($itemId);
								if ($entryId && !in_array($entryId, $affectedEntryIds)) {
									$affectedEntryIds[] = $entryId;
								}
							}
						}
					}
				}
			}

			// 5. Tiến hành xóa Attendee gốc
			$result = Attendees::deleteViaApi($id);

			// Lấy danh sách ID của những người tham dự còn lại trong phiếu đăng ký
			$currentAttendeeIds = array();
			$attendees = Attendees::getByRegistrationId($registration_id);
			if (is_array($attendees)) {
				foreach ($attendees as $att) {
					if (isset($att['id'])) {
						$currentAttendeeIds[] = $att['id'];
					}
				}
			}

			// ==================== CASCADE CLEAN UP NỘI DUNG RỖNG ====================

			// A. Dọn dẹp đội thể thao rỗng (SportTeams)
			foreach ($affectedTeamIds as $teamId) {
				$resMembers = ApiClient::get(ApiEndpoints::SPORT_TEAM_MEMBER_LIST, array(
					'per_page' => 1000,
				));
				$hasMembers = false;
				if ($resMembers['success'] && isset($resMembers['data'])) {
					$mItems = isset($resMembers['data']['data']) ? $resMembers['data']['data'] : $resMembers['data'];
					if (is_array($mItems)) {
						foreach ($mItems as $mItem) {
							$mTeamId = isset($mItem['sport_team_id']) ? $mItem['sport_team_id'] : (isset($mItem['team_id']) ? $mItem['team_id'] : null);
							if ($mTeamId == $teamId) {
								$hasMembers = true;
								break;
							}
						}
					}
				}
				if (!$hasMembers) {
					// Nếu đội không còn thành viên nào, xóa đội
					$teamObj = SportTeams::fetchFromApi($teamId);
					$sportId = ($teamObj && isset($teamObj->sport_id)) ? $teamObj->sport_id : null;

					SportTeams::deleteViaApi($teamId);

					// Đồng thời dọn dẹp dòng RegistrationDetails môn thể thao rỗng
					if ($sportId) {
						$resDetails = ApiClient::get(ApiEndpoints::REGISTRATION_DETAIL_LIST, array(
							'per_page' => 1000,
						));
						if ($resDetails['success'] && isset($resDetails['data'])) {
							$dItems = isset($resDetails['data']['data']) ? $resDetails['data']['data'] : $resDetails['data'];
							if (is_array($dItems)) {
								foreach ($dItems as $dItem) {
									$dRegId = isset($dItem['registration_id']) ? $dItem['registration_id'] : null;
									$dSportId = isset($dItem['sport_id']) ? $dItem['sport_id'] : null;
									if ($dRegId == $registration_id && $dSportId == $sportId) {
										$detailId = isset($dItem['id']) ? $dItem['id'] : null;
										if ($detailId) {
											RegistrationDetails::deleteViaApi($detailId);
										}
									}
								}
							}
						}
					}
				}
			}

			// B. Dọn dẹp đăng ký nghiệp vụ rỗng (RegistrationDetails)
			foreach ($affectedCompetitionIds as $compId) {
				$resCompRegs = ApiClient::get(ApiEndpoints::COMPETITION_REGISTRATION_LIST, array(
					'per_page' => 1000,
				));
				$hasRegs = false;
				if ($resCompRegs['success'] && isset($resCompRegs['data'])) {
					$rItems = isset($resCompRegs['data']['data']) ? $resCompRegs['data']['data'] : $resCompRegs['data'];
					if (is_array($rItems)) {
						foreach ($rItems as $rItem) {
							$rCompId = isset($rItem['competition_id']) ? $rItem['competition_id'] : null;
							$rAttendeeId = isset($rItem['attendee_id']) ? $rItem['attendee_id'] : null;
							if ($rCompId == $compId && in_array($rAttendeeId, $currentAttendeeIds)) {
								$hasRegs = true;
								break;
							}
						}
					}
				}
				if (!$hasRegs) {
					// Nếu không còn ai đăng ký cuộc thi này, xóa dòng RegistrationDetails tương ứng
					$resDetails = ApiClient::get(ApiEndpoints::REGISTRATION_DETAIL_LIST, array(
						'per_page' => 1000,
					));
					if ($resDetails['success'] && isset($resDetails['data'])) {
						$dItems = isset($resDetails['data']['data']) ? $resDetails['data']['data'] : $resDetails['data'];
						if (is_array($dItems)) {
							foreach ($dItems as $dItem) {
								$dRegId = isset($dItem['registration_id']) ? $dItem['registration_id'] : null;
								$dCompId = isset($dItem['competition_id']) ? $dItem['competition_id'] : null;
								if ($dRegId == $registration_id && $dCompId == $compId) {
									$detailId = isset($dItem['id']) ? $dItem['id'] : null;
									if ($detailId) {
										RegistrationDetails::deleteViaApi($detailId);
									}
								}
							}
						}
					}
				}
			}

			// C. Dọn dẹp tiết mục văn nghệ rỗng (TalentEntries)
			foreach ($affectedEntryIds as $entryId) {
				$resMembers = ApiClient::get(ApiEndpoints::TALENT_ENTRY_MEMBER_LIST, array(
					'per_page' => 1000,
				));
				$hasMembers = false;
				if ($resMembers['success'] && isset($resMembers['data'])) {
					$mItems = isset($resMembers['data']['data']) ? $resMembers['data']['data'] : $resMembers['data'];
					if (is_array($mItems)) {
						foreach ($mItems as $mItem) {
							$mEntryId = isset($mItem['entry_id']) ? $mItem['entry_id'] : null;
							if ($mEntryId == $entryId) {
								$hasMembers = true;
								break;
							}
						}
					}
				}
				if (!$hasMembers) {
					// Nếu tiết mục văn nghệ không còn ai biểu diễn, xóa tiết mục
					TalentEntries::deleteViaApi($entryId);
				}
			}

			// ==================== END CASCADE CLEAN UP ====================

			if ($result['success']) {
				Yii::app()->user->setFlash('success', 'Xóa người tham dự thành công.');
			} else {
				Yii::app()->user->setFlash('error', isset($result['error']) ? $result['error'] : 'Không thể xóa.');
			}

			$this->redirect(array('view', 'id' => $registration_id));
		} else {
			throw new CHttpException(400, 'Yêu cầu không hợp lệ.');
		}
	}

	public function actionGetAttendeesList($registration_id)
	{
		$this->checkRegistrationAccess($registration_id);
		$attendees = Attendees::getByRegistrationId($registration_id);
		$result = array();

		foreach ($attendees as $att) {
			$attId = isset($att['id']) ? $att['id'] : '';
			$staffId = isset($att['staff_id']) ? $att['staff_id'] : null;
			$positionName = isset($att['position']) ? $att['position'] : '';
			$departmentName = '';

			if ($staffId) {
				$staff = Staffs::fetchFromApi($staffId);
				if ($staff) {
					$positionName = isset($staff->position_name) ? $staff->position_name : $positionName;
					$departmentName = isset($staff->division_name) ? $staff->division_name : '';
				}
			}

			$roleName = Attendees::resolveRoleNames(isset($att['role_id']) ? $att['role_id'] : '');

			$result[] = array(
				'id' => $attId,
				'full_name' => isset($att['full_name']) ? $att['full_name'] : '',
				'position' => $positionName,
				'department_name' => $departmentName,
				'role_name' => $roleName,
				'portrait_path' => isset($att['portrait_path']) ? $att['portrait_path'] : (isset($att['photo_path']) ? $att['photo_path'] : ''),
				'approval_status' => isset($att['approval_status']) ? (int)$att['approval_status'] : 0,
				'start_date' => isset($att['join_hotel_date']) ? $att['join_hotel_date'] : (isset($att['start_date']) ? $att['start_date'] : ''),
				'check_in_date' => isset($att['check_in_date']) ? $att['check_in_date'] : '',
				'check_out_date' => isset($att['check_out_date']) ? $att['check_out_date'] : '',
				'transport_name' => isset($att['transport_name']) ? $att['transport_name'] : '',
				'contract_path' => isset($att['contract_path']) ? $att['contract_path'] : '',
			);
		}

		echo CJSON::encode(array('success' => true, 'data' => $result));
		Yii::app()->end();
	}

	public function actionGetAttendeeDetail($id)
	{
		$attendee = Attendees::fetchFromApi($id);
		if (!$attendee) {
			echo CJSON::encode(array('success' => false, 'error' => 'Không tìm thấy người tham dự.'));
			Yii::app()->end();
		}

		$positionName = $attendee->position;
		$departmentName = '';
		if ($attendee->staff_id) {
			$staff = Staffs::fetchFromApi($attendee->staff_id);
			if ($staff) {
				$positionName = isset($staff->position_name) ? $staff->position_name : $attendee->position;
				$departmentName = isset($staff->division_name) ? $staff->division_name : '';
			}
		}

		$data = array(
			'id' => $attendee->id,
			'staff_id' => $attendee->staff_id,
			'full_name' => $attendee->full_name,
			'position' => $positionName,
			'department_name' => $departmentName,
			'role_id' => $attendee->role_id,
			'note' => $attendee->note,
			'portrait_path' => $attendee->portrait_path,
			'cccd_front_path' => $attendee->cccd_front_path,
			'cccd_back_path' => $attendee->cccd_back_path,
			'contract_path' => $attendee->contract_path,
			'join_hotel_date' => $attendee->join_hotel_date,
			'start_date' => $attendee->join_hotel_date,
			'check_in_date' => $attendee->check_in_date,
			'check_out_date' => $attendee->check_out_date,
			'transport_id' => $attendee->transport_id,
		);

		echo CJSON::encode(array('success' => true, 'data' => $data));
		Yii::app()->end();
	}

	public function actionUpdateAttendeeAjax()
	{
		if (!Yii::app()->getRequest()->getIsPostRequest()) {
			echo CJSON::encode(array('success' => false, 'error' => 'Yêu cầu không hợp lệ.'));
			Yii::app()->end();
		}

		$id = Yii::app()->getRequest()->getPost('attendee_id');
		$registrationId = Yii::app()->getRequest()->getPost('registration_id');

		$attendee = Attendees::fetchFromApi($id);
		if (!$attendee) {
			echo CJSON::encode(array('success' => false, 'error' => 'Không tìm thấy người tham dự.'));
			Yii::app()->end();
		}

		$attendee->full_name = Yii::app()->getRequest()->getPost('full_name');
		$attendee->position = Yii::app()->getRequest()->getPost('position');
		$roleId = Yii::app()->getRequest()->getPost('role_id');
		if (is_array($roleId)) {
			$roleId = implode(', ', $roleId);
		}
		$attendee->role_id = $roleId;
		$attendee->note = Yii::app()->getRequest()->getPost('note');

		$joinHotelDate = Yii::app()->getRequest()->getPost('join_hotel_date');
		if ($joinHotelDate === null) {
			$joinHotelDate = Yii::app()->getRequest()->getPost('start_date');
		}
		if ($joinHotelDate !== null) {
			$attendee->join_hotel_date = $joinHotelDate;
		}

		$attendee->check_in_date = Yii::app()->getRequest()->getPost('check_in_date');
		$attendee->check_out_date = Yii::app()->getRequest()->getPost('check_out_date');
		$attendee->transport_id = Yii::app()->getRequest()->getPost('transport_id');

		$uploadedFiles = $this->handleAttendeeDocumentUpload();

		if (isset($uploadedFiles['errors']) && !empty($uploadedFiles['errors'])) {
			echo CJSON::encode(array('success' => false, 'error' => implode("\n", $uploadedFiles['errors'])));
			Yii::app()->end();
		}

		if (isset($uploadedFiles['portrait_path'])) {
			$attendee->portrait_path = $uploadedFiles['portrait_path'];
		}
		if (isset($uploadedFiles['cccd_front_path'])) {
			$attendee->cccd_front_path = $uploadedFiles['cccd_front_path'];
		}
		if (isset($uploadedFiles['cccd_back_path'])) {
			$attendee->cccd_back_path = $uploadedFiles['cccd_back_path'];
		}
		if (isset($uploadedFiles['contract_path'])) {
			$attendee->contract_path = $uploadedFiles['contract_path'];
		}

		$result = $attendee->updateViaApi();

		if ($result['success']) {
			echo CJSON::encode(array('success' => true, 'message' => 'Cập nhật thành công.'));
		} else {
			echo CJSON::encode(array('success' => false, 'error' => isset($result['error']) ? $result['error'] : 'Không thể cập nhật.'));
		}
		Yii::app()->end();
	}

	public function actionGetAttendeesAjax($registration_id)
	{
		header('Content-Type: application/json');
		$attendees = Attendees::getByRegistrationId($registration_id);
		echo CJSON::encode(array('success' => true, 'data' => $attendees));
		Yii::app()->end();
	}

	public function actionUpdateAttendeeEmail()
	{
		header('Content-Type: application/json');
		if (!Yii::app()->getRequest()->getIsPostRequest()) {
			echo CJSON::encode(array('success' => false, 'error' => 'Yêu cầu không hợp lệ.'));
			Yii::app()->end();
		}

		$attendeeId = Yii::app()->getRequest()->getPost('attendee_id');
		$email = Yii::app()->getRequest()->getPost('personal_email');

		if (!$attendeeId) {
			echo CJSON::encode(array('success' => false, 'error' => 'Thiếu ID người tham dự.'));
			Yii::app()->end();
		}

		$attendee = Attendees::fetchFromApi($attendeeId);
		if (!$attendee) {
			echo CJSON::encode(array('success' => false, 'error' => 'Không tìm thấy người tham dự.'));
			Yii::app()->end();
		}

		$attendee->personal_email = $email;
		$result = $attendee->updateViaApi();

		if ($result['success']) {
			echo CJSON::encode(array('success' => true, 'message' => 'Cập nhật email thành công.'));
		} else {
			echo CJSON::encode(array('success' => false, 'error' => isset($result['error']) ? $result['error'] : 'Không thể cập nhật email.'));
		}
		Yii::app()->end();
	}

	public function actionUpdateContestantEmail()
	{
		header('Content-Type: application/json');
		if (!Yii::app()->getRequest()->getIsPostRequest()) {
			echo CJSON::encode(array('success' => false, 'error' => 'Yêu cầu không hợp lệ.'));
			Yii::app()->end();
		}

		$contestantId = Yii::app()->getRequest()->getPost('contestant_id');
		$email = Yii::app()->getRequest()->getPost('personal_email');

		if (!$contestantId) {
			echo CJSON::encode(array('success' => false, 'error' => 'Thiếu ID thí sinh.'));
			Yii::app()->end();
		}

		$contestant = BeautyContestants::fetchFromApi($contestantId);
		if (!$contestant) {
			echo CJSON::encode(array('success' => false, 'error' => 'Không tìm thấy thí sinh.'));
			Yii::app()->end();
		}

		$contestant->personal_email = $email;
		$result = $contestant->updateViaApi();

		if ($result['success']) {
			echo CJSON::encode(array('success' => true, 'message' => 'Cập nhật email thành công.'));
		} else {
			echo CJSON::encode(array('success' => false, 'error' => isset($result['error']) ? $result['error'] : 'Không thể cập nhật email.'));
		}
		Yii::app()->end();
	}

	// ==================== MISS REGISTRATION ====================

	public function actionGetMissContestInfo($contest_id)
	{
		$contest = BeautyContests::fetchFromApi($contest_id);
		$result = array();

		if ($contest) {
			$result = array(
				'id' => $contest->id,
				'name' => $contest->name,
				'max_per_org' => isset($contest->max_per_org) ? (int)$contest->max_per_org : 0,
				'gender' => isset($contest->gender) ? $contest->gender : null,
			);
		}

		header('Content-Type: application/json');
		echo CJSON::encode(array('success' => true, 'data' => $result));
		Yii::app()->end();
	}

	public function actionGetAttendeesForMiss($registration_id)
	{
		$this->checkRegistrationAccess($registration_id);
		$result = array();
		$registered = array();
		$contestId = isset($_GET['contest_id']) ? $_GET['contest_id'] : null;

		$contest = $contestId ? BeautyContests::fetchFromApi($contestId) : null;
		$contestGenderRaw = $contest && isset($contest->gender) ? $contest->gender : null;
		$contestGender = null;
		if ($contestGenderRaw === 'female') {
			$contestGender = 0;
		} elseif ($contestGenderRaw === 'male') {
			$contestGender = 1;
		}

		$existingContestants = $contestId ? BeautyContestants::getApiDataProvider(array('contest_id' => $contestId), 1000)->getData() : array();
		$registeredAttendeeIds = array();
		foreach ($existingContestants as $c) {
			$attId = isset($c->attendee_id) ? $c->attendee_id : (isset($c['attendee_id']) ? $c['attendee_id'] : null);
			if ($attId) $registeredAttendeeIds[] = $attId;
			$registered[] = array(
				'id' => isset($c->id) ? $c->id : $c['id'],
				'attendee_id' => $attId,
				'name' => isset($c->attendee_name) ? $c->attendee_name : (isset($c['attendee_name']) ? $c['attendee_name'] : ''),
				'candidate_number' => isset($c->candidate_number) ? $c->candidate_number : (isset($c['candidate_number']) ? $c['candidate_number'] : ''),
			);
		}

		$attendees = Attendees::getByRegistrationId($registration_id);

		foreach ($attendees as $att) {
			$id = isset($att['id']) ? $att['id'] : null;
			$fullName = isset($att['full_name']) ? $att['full_name'] : '';
			$positionName = isset($att['position']) ? $att['position'] : '';
			$gender = isset($att['gender']) ? $att['gender'] : null;

			if (!$id) continue;

			if (in_array($id, $registeredAttendeeIds)) continue;

			if ($contestGender !== null && (int)$gender !== $contestGender) continue;

			$result[] = array(
				'id' => $id,
				'name' => $fullName,
				'position' => $positionName,
				'department_name' => isset($att['department_name']) ? $att['department_name'] : (isset($att['division_name']) ? $att['division_name'] : ''),
				'display' => $fullName . ($positionName ? ' (' . $positionName . ')' : ''),
			);
		}

		header('Content-Type: application/json');
		echo CJSON::encode(array('success' => true, 'data' => $result, 'registered' => $registered));
		Yii::app()->end();
	}

	public function actionAddMissRegistration()
	{
		if (!Yii::app()->getRequest()->getIsPostRequest()) {
			echo CJSON::encode(array('success' => false, 'error' => 'Yêu cầu không hợp lệ.'));
			Yii::app()->end();
		}

		$registrationId = Yii::app()->getRequest()->getPost('registration_id');
		$this->checkRegistrationAccess($registrationId);
		$contestId = Yii::app()->getRequest()->getPost('contest_id');
		$attendeeIds = Yii::app()->getRequest()->getPost('attendee_ids', array());
		$note = Yii::app()->getRequest()->getPost('note', '');

		if (empty($attendeeIds)) {
			echo CJSON::encode(array('success' => false, 'error' => 'Vui lòng chọn ít nhất một thí sinh.'));
			Yii::app()->end();
		}

		$contest = BeautyContests::fetchFromApi($contestId);
		if (!$contest) {
			echo CJSON::encode(array('success' => false, 'error' => 'Không tìm thấy cuộc thi.'));
			Yii::app()->end();
		}

		$prefix = $contest->candidate_prefix ?: 'MS';
		$startNum = $contest->candidate_start ?: 1;

		$existingContestants = BeautyContestants::getApiDataProvider(array('contest_id' => $contestId), 1000)->getData();

		$successCount = 0;
		$errors = array();
		foreach ($attendeeIds as $attendeeId) {
			$suffix = chr(mt_rand(65, 90)); // A-Z để tránh trùng khi delete rồi add lại
			$candidateNumber = $prefix . $attendeeId . $suffix;
			$model = new BeautyContestants;
			$model->contest_id = $contestId;
			$model->attendee_id = $attendeeId;
			$model->candidate_number = $candidateNumber;
			$model->registration_id = $registrationId;
			$model->note = $note;
			$model->status = BeautyContestants::STATUS_REGISTERED;

			$result = $model->storeViaApi();
			if ($result['success']) {
				$successCount++;
			} else {
				$errors[] = isset($result['error']) ? $result['error'] : 'Lỗi không xác định';
			}
		}

		if ($successCount > 0) {
			echo CJSON::encode(array('success' => true, 'message' => "Đăng ký thành công {$successCount} thí sinh."));
		} else {
			$errorMsg = !empty($errors) ? implode('; ', array_unique($errors)) : 'Không thể đăng ký.';
			echo CJSON::encode(array('success' => false, 'error' => $errorMsg));
		}
		Yii::app()->end();
	}

	public function actionGetMissContestant($id)
	{
		$model = BeautyContestants::fetchFromApi($id);
		if (!$model) {
			echo CJSON::encode(array('success' => false, 'error' => 'Không tìm thấy thí sinh.'));
			Yii::app()->end();
		}

		$attendeeName = $model->attendee_name;
		if (empty($attendeeName) && $model->attendee_id) {
			$attendee = Attendees::fetchFromApi($model->attendee_id);
			if ($attendee) {
				$attendeeName = $attendee->full_name;
			}
		}

		header('Content-Type: application/json');
		echo CJSON::encode(array('success' => true, 'data' => array(
			'id' => $model->id,
			'attendee_name' => $attendeeName,
			'candidate_number' => $model->candidate_number,
			'height_cm' => $model->height_cm,
			'weight_kg' => $model->weight_kg,
			'measurements' => $model->measurements,
			'talent' => $model->talent,
			'bio' => $model->bio,
		)));
		Yii::app()->end();
	}

	public function actionUpdateMissContestant()
	{
		if (!Yii::app()->getRequest()->getIsPostRequest()) {
			echo CJSON::encode(array('success' => false, 'error' => 'Yêu cầu không hợp lệ.'));
			Yii::app()->end();
		}

		$id = Yii::app()->getRequest()->getPost('id');
		$model = BeautyContestants::fetchFromApi($id);
		if (!$model) {
			echo CJSON::encode(array('success' => false, 'error' => 'Không tìm thấy thí sinh.'));
			Yii::app()->end();
		}

		$model->height_cm = Yii::app()->getRequest()->getPost('height_cm');
		$model->weight_kg = Yii::app()->getRequest()->getPost('weight_kg');
		$model->measurements = Yii::app()->getRequest()->getPost('measurements');
		$model->talent = Yii::app()->getRequest()->getPost('talent');
		$model->bio = Yii::app()->getRequest()->getPost('bio');

		$result = $model->updateViaApi();
		if ($result['success']) {
			echo CJSON::encode(array('success' => true, 'message' => 'Cập nhật thành công.'));
		} else {
			echo CJSON::encode(array('success' => false, 'error' => $result['error'] ?: 'Không thể cập nhật.'));
		}
		Yii::app()->end();
	}

	public function actionDeleteMissContestant()
	{
		if (!Yii::app()->getRequest()->getIsPostRequest()) {
			echo CJSON::encode(array('success' => false, 'error' => 'Yêu cầu không hợp lệ.'));
			Yii::app()->end();
		}

		$id = Yii::app()->getRequest()->getPost('id');
		$result = BeautyContestants::deleteViaApi($id);

		if ($result['success']) {
			echo CJSON::encode(array('success' => true, 'message' => 'Xóa thành công.'));
		} else {
			echo CJSON::encode(array('success' => false, 'error' => $result['error'] ?: 'Không thể xóa.'));
		}
		Yii::app()->end();
	}

	// ==================== TALENT REGISTRATION ====================

	public function actionGetTalentCategories($event_id)
	{
		$result = array();

		$categories = TalentCategories::getApiDataProvider(array(), 100)->getData();
		foreach ($categories as $cat) {
			$id = isset($cat['id']) ? $cat['id'] : (isset($cat->id) ? $cat->id : null);
			$name = isset($cat['name']) ? $cat['name'] : (isset($cat->name) ? $cat->name : '');
			if ($id) {
				$result[] = array('id' => $id, 'name' => $name);
			}
		}

		header('Content-Type: application/json');
		echo CJSON::encode(array('success' => true, 'data' => $result));
		Yii::app()->end();
	}

	public function actionAddTalentRegistration()
	{
		if (!Yii::app()->getRequest()->getIsPostRequest()) {
			echo CJSON::encode(array('success' => false, 'error' => 'Yêu cầu không hợp lệ.'));
			Yii::app()->end();
		}

		$registrationId = Yii::app()->getRequest()->getPost('registration_id');
		$this->checkRegistrationAccess($registrationId);
		$propertyId = Yii::app()->getRequest()->getPost('property_id');
		$categoryId = Yii::app()->getRequest()->getPost('category_id');
		$title = Yii::app()->getRequest()->getPost('title');
		$duration = Yii::app()->getRequest()->getPost('duration');
		$attendeeIds = Yii::app()->getRequest()->getPost('attendee_ids', array());
		$alliancePropertyIds = Yii::app()->getRequest()->getPost('alliance_property_ids', array());
		$note = Yii::app()->getRequest()->getPost('note', '');
		$description = Yii::app()->getRequest()->getPost('description', '');
		$content = Yii::app()->getRequest()->getPost('content', '');
		$director = Yii::app()->getRequest()->getPost('director', '');
		$directorPhone = Yii::app()->getRequest()->getPost('director_phone', '');
		$origin = Yii::app()->getRequest()->getPost('origin', '');
		$musicPath = Yii::app()->getRequest()->getPost('music_path', '');
		$videoPath = Yii::app()->getRequest()->getPost('video_path', '');

		// Tạo talent entry
		$entry = new TalentEntries;
		$entry->registration_id = $registrationId;
		$entry->property_id = $propertyId;
		$entry->category_id = $categoryId;
		$entry->title = $title;
		$entry->duration_seconds = $duration ? ($duration * 60) : null;
		$entry->status = TalentEntries::STATUS_DRAFT;
		$entry->note = $note;
		$entry->description = $description;
		$entry->content = $content;
		$entry->director = $director;
		$entry->director_phone = $directorPhone;
		$entry->origin = $origin;
		$entry->music_path = $musicPath;
		$entry->video_path = $videoPath;
		$entry->alliance_property_ids = !empty($alliancePropertyIds) ? implode(',', $alliancePropertyIds) : null;

		$result = $entry->storeViaApi();
		if (!$result['success']) {
			echo CJSON::encode(array('success' => false, 'error' => isset($result['error']) ? $result['error'] : 'Không thể tạo tiết mục.'));
			Yii::app()->end();
		}

		$entryId = null;
		if (isset($result['data']['data']['id'])) {
			$entryId = $result['data']['data']['id'];
		} elseif (isset($result['data']['id'])) {
			$entryId = $result['data']['id'];
		}

		// Thêm thành viên
		if ($entryId && !empty($attendeeIds)) {
			foreach ($attendeeIds as $attendeeId) {
				$member = new TalentEntryMembers;
				$member->entry_id = $entryId;
				$member->attendee_id = $attendeeId;
				$memberResult = $member->storeViaApi();
				if (!$memberResult['success']) {
					Yii::log('Lỗi thêm thành viên văn nghệ: entry_id=' . $entryId . ', attendee_id=' . $attendeeId . ', error=' . json_encode($memberResult), 'error');
				}
			}
		}

		echo CJSON::encode(array('success' => true, 'message' => 'Đăng ký tiết mục thành công.', 'id' => $entryId));
		Yii::app()->end();
	}

	public function actionUploadDocument($id)
	{
		$this->checkRegistrationAccess($id);
		$model = $this->loadModelById($id);

		if (!in_array($model->status, array(Registrations::STATUS_DRAFT, Registrations::STATUS_REJECTED))) {
			Yii::app()->user->setFlash('error', 'Không thể tải lên tệp khi phiếu đã nộp hoặc đã duyệt.');
			$this->redirect(array('view', 'id' => $id));
		}

		if (!isset($_FILES['documents']) || empty($_FILES['documents']['name'][0])) {
			Yii::app()->user->setFlash('error', 'Vui lòng chọn tệp để tải lên.');
			$this->redirect(array('view', 'id' => $id));
		}

		$documents = array();
		if (!empty($model->document)) {
			$parsed = json_decode($model->document, true);
			if (is_array($parsed)) {
				$documents = $parsed;
			}
		}

		$uploadPath = Yii::getPathOfAlias('webroot') . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . 'registrations' . DIRECTORY_SEPARATOR . $id;
		if (!is_dir($uploadPath)) {
			if (!@mkdir($uploadPath, 0755, true)) {
				Yii::app()->user->setFlash('error', 'Không thể tạo thư mục upload.');
				$this->redirect(array('view', 'id' => $id));
			}
		}

		$allowedTypes = array('jpg', 'jpeg', 'png', 'gif', 'pdf', 'doc', 'docx', 'xls', 'xlsx');
		$maxSize = 10 * 1024 * 1024;

		$files = $_FILES['documents'];
		$uploadedCount = 0;
		$errors = array();

		for ($i = 0; $i < count($files['name']); $i++) {
			$filename = $files['name'][$i];
			if (empty($filename)) continue;

			if ($files['error'][$i] !== UPLOAD_ERR_OK) {
				$errors[] = $filename . ': Lỗi upload (code ' . $files['error'][$i] . ')';
				continue;
			}

			$ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
			if (!in_array($ext, $allowedTypes)) {
				$errors[] = $filename . ': Định dạng không hỗ trợ';
				continue;
			}

			if ($files['size'][$i] > $maxSize) {
				$errors[] = $filename . ': Vượt quá 10MB';
				continue;
			}

			$safeFilename = preg_replace('/[^a-zA-Z0-9._-]/', '_', $filename);
			$newFilename = time() . '_' . $i . '_' . $safeFilename;
			$targetPath = $uploadPath . DIRECTORY_SEPARATOR . $newFilename;

			if (move_uploaded_file($files['tmp_name'][$i], $targetPath)) {
				$documents[] = '/uploads/registrations/' . $id . '/' . $newFilename;
				$uploadedCount++;
			} else {
				$errors[] = $filename . ': Không thể lưu tệp';
			}
		}

		if ($uploadedCount > 0) {
			$model->document = CJSON::encode($documents);
			$result = $model->updateViaApi();
			if ($result['success']) {
				$msg = 'Tải lên ' . $uploadedCount . ' tệp thành công.';
				if (!empty($errors)) {
					$msg .= ' Lỗi: ' . implode(', ', $errors);
				}
				Yii::app()->user->setFlash('success', $msg);
			} else {
				Yii::app()->user->setFlash('error', 'Lỗi lưu thông tin: ' . (isset($result['message']) ? $result['message'] : ''));
			}
		} else {
			$errorMsg = 'Không có tệp nào được tải lên.';
			if (!empty($errors)) {
				$errorMsg .= ' Chi tiết: ' . implode(', ', $errors);
			}
			Yii::app()->user->setFlash('error', $errorMsg);
		}

		$this->redirect(array('view', 'id' => $id));
	}

	public function actionDeleteDocument($id)
	{
		$this->checkRegistrationAccess($id);
		$model = $this->loadModelById($id);

		if (!in_array($model->status, array(Registrations::STATUS_DRAFT, Registrations::STATUS_REJECTED))) {
			Yii::app()->user->setFlash('error', 'Không thể xóa tệp khi phiếu đã nộp hoặc đã duyệt.');
			$this->redirect(array('view', 'id' => $id));
		}

		$index = isset($_POST['document_index']) ? (int)$_POST['document_index'] : -1;

		if ($index >= 0 && !empty($model->document)) {
			$documents = json_decode($model->document, true);
			if (is_array($documents) && isset($documents[$index])) {
				$docUrl = $documents[$index];
				$filePath = Yii::getPathOfAlias('webroot') . parse_url($docUrl, PHP_URL_PATH);
				if (file_exists($filePath)) {
					@unlink($filePath);
				}

				array_splice($documents, $index, 1);
				$model->document = !empty($documents) ? CJSON::encode($documents) : null;
				$result = $model->updateViaApi();

				if ($result['success']) {
					Yii::app()->user->setFlash('success', 'Đã xóa tệp đính kèm.');
				} else {
					Yii::app()->user->setFlash('error', 'Lỗi khi xóa tệp.');
				}
			}
		}

		$this->redirect(array('view', 'id' => $id));
	}

	public function actionCheckSportAttendeesLimit()
	{
		if (!Yii::app()->request->isAjaxRequest) {
			throw new CHttpException(400, 'Yêu cầu không hợp lệ.');
		}

		$attendeeIds = Yii::app()->request->getParam('attendee_ids', array());
		$sportId = Yii::app()->request->getParam('sport_id');
		$teamId = Yii::app()->request->getParam('team_id');

		// Lấy danh sách attendee_id cũ của team nếu có teamId
		$oldAttendeeIds = array();
		if ($teamId) {
			$oldMembers = SportTeamMembers::getApiDataProvider(array('sport_team_id' => $teamId), 500)->getData();
			foreach ($oldMembers as $m) {
				$oldAttendeeIds[] = (int)$m->attendee_id;
			}
		}

		$errors = array();
		foreach ($attendeeIds as $attId) {
			$isAlreadyInTeam = in_array((int)$attId, $oldAttendeeIds);
			if ($isAlreadyInTeam) {
				continue; // Người đã có trong team này, không cần kiểm tra
			}

			if ($sportId) {
				$checkResult = SportTeamMembers::canRegisterSport($attId, $sportId);
				if (!$checkResult['can_register']) {
					$attendee = Attendees::fetchFromApi($attId);
					$name = $attendee ? $attendee->full_name : "ID: $attId";
					$errors[] = "{$name}: {$checkResult['error']}";
				}
			}
		}

		header('Content-Type: application/json');
		if (!empty($errors)) {
			echo CJSON::encode(array(
				'success' => false,
				'error' => 'Không thể thêm. ' . implode('; ', $errors)
			));
		} else {
			echo CJSON::encode(array('success' => true));
		}
		Yii::app()->end();
	}

	/**
	 * API kiểm tra pending alliance khi chọn môn thể thao có max_per_team_member > 3
	 * Trả về lỗi ngay nếu có yêu cầu liên quân chưa duyệt
	 */
	public function actionCheckSportPendingAlliance()
	{
		if (!Yii::app()->request->isAjaxRequest) {
			throw new CHttpException(400, 'Yêu cầu không hợp lệ.');
		}

		$registrationId = Yii::app()->request->getParam('registration_id');
		$sportId = Yii::app()->request->getParam('sport_id');

		header('Content-Type: application/json');

		if (!$registrationId || !$sportId) {
			echo CJSON::encode(array('success' => false, 'error' => 'Thiếu thông tin.'));
			Yii::app()->end();
		}

		$registration = Registrations::fetchFromApi($registrationId);
		if (!$registration) {
			echo CJSON::encode(array('success' => false, 'error' => 'Không tìm thấy phiếu đăng ký.'));
			Yii::app()->end();
		}

		$sport = Sports::fetchFromApi($sportId);
		if (!$sport) {
			echo CJSON::encode(array('success' => false, 'error' => 'Không tìm thấy môn thể thao.'));
			Yii::app()->end();
		}

		$maxPerTeam = $sport->max_per_team_member ? (int)$sport->max_per_team_member : self::getSportMaxPlayers($sport->name);

		// Chỉ check pending alliance nếu max_per_team_member > 3
		if ($maxPerTeam <= 3) {
			echo CJSON::encode(array('success' => true, 'requires_alliance_check' => false));
			Yii::app()->end();
		}

		$allianceCheck = $this->checkPendingSportAllianceRequest($registration->event_id, $registration->property_id, $sport->name);
		if ($allianceCheck['has_pending']) {
			echo CJSON::encode(array(
				'success' => false,
				'error' => $allianceCheck['message'],
				'requires_alliance_check' => true
			));
		} else {
			echo CJSON::encode(array('success' => true, 'requires_alliance_check' => true));
		}
		Yii::app()->end();
	}

	public function actionCheckSubmitValid($id)
	{
		header('Content-Type: application/json');

		try {
			$this->checkRegistrationAccess($id);

			$model = $this->loadModelById($id);
			$errors = array();

			// 1. Kiểm tra yêu cầu liên quân gửi đi (nếu có relation_property_id)
			if ($model->relation_property_id) {
				$allianceRequest = AllianceRequests::findByRegistration(
					$model->event_id,
					$model->property_id,
					$model->relation_property_id
				);
				if (!$allianceRequest) {
					$allianceRequest = AllianceRequests::findByRegistration(
						$model->event_id,
						$model->relation_property_id,
						$model->property_id
					);
				}

				if (!$allianceRequest) {
					$errors[] = 'Chưa gửi yêu cầu liên quân với đơn vị đối tác.';
				} else if ($allianceRequest->status != AllianceRequests::STATUS_APPROVED) {
					$statusLabel = 'chưa được duyệt';
					if ($allianceRequest->status == AllianceRequests::STATUS_PENDING) {
						$statusLabel = 'đang chờ xác nhận';
					} else if ($allianceRequest->status == AllianceRequests::STATUS_REJECTED) {
						$statusLabel = 'đã bị từ chối';
					} else if ($allianceRequest->status == AllianceRequests::STATUS_CANCELLED) {
						$statusLabel = 'đã bị hủy';
					}
					$errors[] = "Yêu cầu liên quân giữa đơn vị của bạn và đơn vị đối tác {$statusLabel}.";
				}
			}

			// 2. Kiểm tra các yêu cầu liên quân gửi đến đang chờ duyệt (STATUS_PENDING)
			$incomingAllianceRequests = AllianceRequests::getApiDataProvider(array(
				'event_id' => $model->event_id,
				'target_org_id' => $model->property_id,
				'status' => AllianceRequests::STATUS_PENDING,
			), 100)->getData();

			if (!empty($incomingAllianceRequests)) {
				$errors[] = 'Đơn vị của bạn đang có yêu cầu liên quân gửi đến chưa xử lý. Vui lòng duyệt hoặc từ chối yêu cầu liên quân trước khi gửi duyệt phiếu.';
			}

			// 3. Kiểm tra số lượng thành viên đội thi đấu thể thao
			$sportTeams = SportTeams::getApiDataProvider(array('registration_id' => $id), 100)->getData();
			foreach ($sportTeams as $team) {
				$teamId = isset($team->id) ? $team->id : (isset($team['id']) ? $team['id'] : null);
				$teamName = isset($team->name) ? $team->name : (isset($team['name']) ? $team['name'] : 'Không rõ tên');
				$sportId = isset($team->sport_id) ? $team->sport_id : (isset($team['sport_id']) ? $team['sport_id'] : null);
				$sportName = isset($team->sport_name) ? $team->sport_name : (isset($team['sport_name']) ? $team['sport_name'] : '');

				if (!$teamId) continue;

				// Lấy thông tin môn thể thao để lấy max_per_team_member
				$sport = $sportId ? Sports::fetchFromApi($sportId) : null;
				$maxPerTeam = 0;
				if ($sport && isset($sport->max_per_team_member) && $sport->max_per_team_member !== null && $sport->max_per_team_member !== '') {
					$maxPerTeam = (int)$sport->max_per_team_member;
					if (empty($sportName)) {
						$sportName = $sport->name;
					}
				} else {
					$maxPerTeam = self::getSportMaxPlayers($sportName);
				}

				// Đếm số thành viên trong đội
				$membersData = SportTeamMembers::getApiDataProvider(array('sport_team_id' => $teamId), 500)->getData();
				$memberCount = count($membersData);

				if ($maxPerTeam > 0 && $memberCount > $maxPerTeam) {
					$errors[] = "Đội \"{$teamName}\" (môn {$sportName}) có {$memberCount} thành viên, vượt quá giới hạn {$maxPerTeam} người.";
				}
			}

			// 4. Kiểm tra thông tin người tham dự (Attendees)
			$attendees = Attendees::getByRegistrationId($id);
			if (empty($attendees)) {
				$errors[] = 'Phiếu đăng ký chưa có người tham dự nào.';
			} else {
				// Lấy danh sách ID thí sinh thi Miss trong event này để kiểm tra email cá nhân
				$missAttendeeEmails = array();
				$contests = BeautyContests::getApiDataProvider(array('event_id' => $model->event_id), 100)->getData();
				foreach ($contests as $contest) {
					$contestId = isset($contest->id) ? $contest->id : (isset($contest['id']) ? $contest['id'] : null);
					if ($contestId) {
						$contestants = BeautyContestants::getApiDataProvider(array('contest_id' => $contestId), 500)->getData();
						foreach ($contestants as $c) {
							$attId = isset($c->attendee_id) ? $c->attendee_id : (isset($c['attendee_id']) ? $c['attendee_id'] : null);
							$cEmail = isset($c->personal_email) ? $c->personal_email : (isset($c['personal_email']) ? $c['personal_email'] : null);
							if ($attId) {
								$missAttendeeEmails[$attId] = $cEmail;
							}
						}
					}
				}

				foreach ($attendees as $att) {
					$name = isset($att['full_name']) ? $att['full_name'] : 'Không rõ tên';
					$attId = isset($att['id']) ? $att['id'] : null;

					// Kiểm tra 4 tệp tài liệu bắt buộc
					$missingDocs = array();
					if (empty($att['cccd_front_path'])) {
						$missingDocs[] = 'CCCD mặt trước';
					}
					if (empty($att['cccd_back_path'])) {
						$missingDocs[] = 'CCCD mặt sau';
					}
					if (empty($att['portrait_path'])) {
						$missingDocs[] = 'Ảnh chân dung';
					}
					if (empty($att['contract_path'])) {
						$missingDocs[] = 'Hợp đồng lao động';
					}

					if (!empty($missingDocs)) {
						$errors[] = "Người tham dự \"{$name}\" chưa tải lên đủ tệp đính kèm bắt buộc: " . implode(', ', $missingDocs) . '.';
					}

					// Kiểm tra email cá nhân cho thí sinh thi Miss
					if ($attId && array_key_exists($attId, $missAttendeeEmails)) {
						$emailToCheck = !empty($att['personal_email']) ? $att['personal_email'] : $missAttendeeEmails[$attId];
						if (empty($emailToCheck)) {
							$errors[] = "Thí sinh thi Miss \"{$name}\" chưa điền email cá nhân.";
						}
					}
				}
			}

			if (!empty($errors)) {
				echo CJSON::encode(array(
					'success' => false,
					'errors' => $errors,
				));
			} else {
				echo CJSON::encode(array('success' => true));
			}
		} catch (CHttpException $e) {
			echo CJSON::encode(array(
				'success' => false,
				'errors' => array($e->getMessage()),
			));
		} catch (Exception $e) {
			Yii::log('checkSubmitValid error: ' . $e->getMessage(), CLogger::LEVEL_ERROR);
			echo CJSON::encode(array(
				'success' => false,
				'errors' => array('Lỗi hệ thống: ' . $e->getMessage()),
			));
		}
		Yii::app()->end();
	}

	public function actionGetSportTeamsHtml($registration_id)
	{
		if (!Yii::app()->request->isAjaxRequest) {
			throw new CHttpException(400, 'Bad Request');
		}

		$registration = Registrations::fetchFromApi($registration_id);
		if (!$registration) {
			echo CJSON::encode(array('success' => false, 'error' => 'Không tìm thấy phiếu đăng ký.'));
			Yii::app()->end();
		}

		// Load Sport Teams cho đơn vị (bao gồm cả đội liên quân)
		$sportTeams = array();
		$sportTeamMembers = array();
		if ($registration->event_id && $registration->property_id) {
			$apiResult = ApiClient::get(ApiEndpoints::SPORT_TEAM_LIST_BY_PROPERTY, array(
				'property_id' => $registration->property_id,
				'event_id' => $registration->event_id,
			));
			$teamsData = array();
			if ($apiResult['success'] && isset($apiResult['data']['data'])) {
				$teamsData = $apiResult['data']['data'];
			} elseif ($apiResult['success'] && isset($apiResult['data']) && is_array($apiResult['data'])) {
				$teamsData = $apiResult['data'];
			}

			foreach ($teamsData as $team) {
				$isObject = is_object($team);
				$teamId = $isObject ? (isset($team->id) ? $team->id : null) : (isset($team['id']) ? $team['id'] : null);
				if ($teamId) {
					$sportName = $isObject ? (isset($team->sport_name) ? $team->sport_name : '') : (isset($team['sport_name']) ? $team['sport_name'] : '');
					$sportId = $isObject ? (isset($team->sport_id) ? $team->sport_id : null) : (isset($team['sport_id']) ? $team['sport_id'] : null);

					if (empty($sportName) && $sportId) {
						$sport = Sports::fetchFromApi($sportId);
						$sportName = $sport ? $sport->name : '';
					}

					$teamObj = new stdClass();
					$teamObj->id = $teamId;
					$teamObj->sport_id = $sportId;
					$teamObj->sport_name = $sportName;
					$teamObj->team_name = $isObject ? (isset($team->team_name) ? $team->team_name : (isset($team->name) ? $team->name : '')) : (isset($team['team_name']) ? $team['team_name'] : (isset($team['name']) ? $team['name'] : ''));
					$teamObj->property_id = $isObject ? (isset($team->property_id) ? $team->property_id : null) : (isset($team['property_id']) ? $team['property_id'] : null);
					$teamObj->is_alliance = $isObject ? (isset($team->is_alliance) ? $team->is_alliance : 0) : (isset($team['is_alliance']) ? $team['is_alliance'] : 0);
					$teamObj->alliance_org_ids = $isObject ? (isset($team->alliance_org_ids) ? $team->alliance_org_ids : '') : (isset($team['alliance_org_ids']) ? $team['alliance_org_ids'] : '');

					$sportTeams[] = $teamObj;

					$members = SportTeamMembers::getApiDataProvider(array('sport_team_id' => $teamId), 50)->getData();
					$sportTeamMembers[$teamId] = $members;
				}
			}
		}

		$html = $this->renderPartial('_sport_teams_content', array(
			'sportTeams' => $sportTeams,
			'sportTeamMembers' => $sportTeamMembers,
			'model' => $registration,
			'canEdit' => true,
		), true);

		echo CJSON::encode(array('success' => true, 'html' => $html));
		Yii::app()->end();
	}
}
