<?php

class ApiEndpoints
{
    // Property
    const PROPERTY_LIST = '/api/properties';
    const PROPERTY_STORE = '/api/properties/store';
    const PROPERTY_DETAIL = '/api/properties/detail/{id}';
    const PROPERTY_UPDATE = '/api/properties/update/{id}';
    const PROPERTY_DESTROY = '/api/properties/destroy/{id}';
    const PROPERTY_LIST_AVAILABLE_UNITS = '/api/properties/list-available-units';

    // Department
    const DEPARTMENT_LIST = '/api/departments';
    const DEPARTMENT_STORE = '/api/departments/store';
    const DEPARTMENT_DETAIL = '/api/departments/detail/{id}';
    const DEPARTMENT_UPDATE = '/api/departments/update/{id}';
    const DEPARTMENT_DESTROY = '/api/departments/destroy/{id}';

    // Division
    const DIVISION_LIST = '/api/divisions';
    const DIVISION_STORE = '/api/divisions/store';
    const DIVISION_DETAIL = '/api/divisions/detail/{id}';
    const DIVISION_UPDATE = '/api/divisions/update/{id}';
    const DIVISION_DESTROY = '/api/divisions/destroy/{id}';

    // Position
    const POSITION_LIST = '/api/positions';
    const POSITION_STORE = '/api/positions/store';
    const POSITION_DETAIL = '/api/positions/detail/{id}';
    const POSITION_UPDATE = '/api/positions/update/{id}';
    const POSITION_DESTROY = '/api/positions/destroy/{id}';

    // Staff
    const STAFF_LIST = '/api/staffs';
    const STAFF_STORE = '/api/staffs/store';
    const STAFF_DETAIL = '/api/staffs/detail/{id}';
    const STAFF_UPDATE = '/api/staffs/update/{id}';
    const STAFF_DESTROY = '/api/staffs/destroy/{id}';
    const STAFF_BEFORE_JUNE_2026 = '/api/staffs/list-before-june-2026';

    // Banquet Event
    const BANQUET_EVENT_LIST = '/api/banquet-events';
    const BANQUET_EVENT_STORE = '/api/banquet-events/store';
    const BANQUET_EVENT_DETAIL = '/api/banquet-events/detail/{id}';
    const BANQUET_EVENT_UPDATE = '/api/banquet-events/update/{id}';
    const BANQUET_EVENT_DESTROY = '/api/banquet-events/destroy/{id}';

    // Banquet Table
    const BANQUET_TABLE_LIST = '/api/banquet-tables';
    const BANQUET_TABLE_STORE = '/api/banquet-tables/store';
    const BANQUET_TABLE_DETAIL = '/api/banquet-tables/detail/{id}';
    const BANQUET_TABLE_UPDATE = '/api/banquet-tables/update/{id}';
    const BANQUET_TABLE_DESTROY = '/api/banquet-tables/destroy/{id}';

    // Banquet Seat
    const BANQUET_SEAT_LIST = '/api/banquet-seats';
    const BANQUET_SEAT_STORE = '/api/banquet-seats/store';
    const BANQUET_SEAT_DETAIL = '/api/banquet-seats/detail/{id}';
    const BANQUET_SEAT_UPDATE = '/api/banquet-seats/update/{id}';
    const BANQUET_SEAT_DESTROY = '/api/banquet-seats/destroy/{id}';

    // Beauty Competition
    const BEAUTY_COMPETITION_LIST = '/api/beauty-competitions';
    const BEAUTY_COMPETITION_STORE = '/api/beauty-competitions/store';
    const BEAUTY_COMPETITION_DETAIL = '/api/beauty-competitions/detail/{id}';
    const BEAUTY_COMPETITION_UPDATE = '/api/beauty-competitions/update/{id}';
    const BEAUTY_COMPETITION_DESTROY = '/api/beauty-competitions/destroy/{id}';

    // Beauty Contestant
    const BEAUTY_CONTESTANT_LIST = '/api/beauty-contestants';
    const BEAUTY_CONTESTANT_STORE = '/api/beauty-contestants/store';
    const BEAUTY_CONTESTANT_DETAIL = '/api/beauty-contestants/detail/{id}';
    const BEAUTY_CONTESTANT_UPDATE = '/api/beauty-contestants/update/{id}';
    const BEAUTY_CONTESTANT_DESTROY = '/api/beauty-contestants/destroy/{id}';

    // Event
    const EVENT_LIST = '/api/events';
    const EVENT_STORE = '/api/events/store';
    const EVENT_DETAIL = '/api/events/detail/{id}';
    const EVENT_UPDATE = '/api/events/update/{id}';
    const EVENT_DESTROY = '/api/events/destroy/{id}';

    // Transport
    const TRANSPORT_LIST = '/api/transports';
    const TRANSPORT_STORE = '/api/transports/store';
    const TRANSPORT_DETAIL = '/api/transports/detail/{id}';
    const TRANSPORT_UPDATE = '/api/transports/update/{id}';
    const TRANSPORT_DESTROY = '/api/transports/destroy/{id}';

    // Role
    const ROLE_LIST = '/api/roles';
    const ROLE_STORE = '/api/roles/store';
    const ROLE_DETAIL = '/api/roles/detail/{id}';
    const ROLE_UPDATE = '/api/roles/update/{id}';
    const ROLE_DESTROY = '/api/roles/destroy/{id}';

    // Content
    const CONTENT_LIST = '/api/contents';
    const CONTENT_STORE = '/api/contents/store';
    const CONTENT_DETAIL = '/api/contents/detail/{id}';
    const CONTENT_UPDATE = '/api/contents/update/{id}';
    const CONTENT_DESTROY = '/api/contents/destroy/{id}';

    // Event Content
    const EVENT_CONTENT_LIST = '/api/event-contents';
    const EVENT_CONTENT_STORE = '/api/event-contents/store';
    const EVENT_CONTENT_DETAIL = '/api/event-contents/detail/{id}';
    const EVENT_CONTENT_UPDATE = '/api/event-contents/update/{id}';
    const EVENT_CONTENT_DESTROY = '/api/event-contents/destroy/{id}';

    // Event Unit
    const EVENT_UNIT_LIST = '/api/event-units';
    const EVENT_UNIT_STORE = '/api/event-units/store';
    const EVENT_UNIT_DETAIL = '/api/event-units/detail/{id}';
    const EVENT_UNIT_UPDATE = '/api/event-units/update/{id}';
    const EVENT_UNIT_DESTROY = '/api/event-units/destroy/{id}';

    // Sport
    const SPORT_LIST = '/api/sports';
    const SPORT_STORE = '/api/sports/store';
    const SPORT_DETAIL = '/api/sports/detail/{id}';
    const SPORT_UPDATE = '/api/sports/update/{id}';
    const SPORT_DESTROY = '/api/sports/destroy/{id}';
    const SPORT_COUNT_ROOT = '/api/sports/count-root-sports/{id}';

    // Event Sport
    const EVENT_SPORT_LIST = '/api/event-sports';
    const EVENT_SPORT_STORE = '/api/event-sports/store';
    const EVENT_SPORT_DETAIL = '/api/event-sports/detail/{id}';
    const EVENT_SPORT_UPDATE = '/api/event-sports/update/{id}';
    const EVENT_SPORT_DESTROY = '/api/event-sports/destroy/{id}';

    // Registration Period
    const REGISTRATION_PERIOD_LIST = '/api/registration-periods';
    const REGISTRATION_PERIOD_LIST_ACTIVE = '/api/registration-periods/list-active';
    const REGISTRATION_PERIOD_STORE = '/api/registration-periods/store';
    const REGISTRATION_PERIOD_DETAIL = '/api/registration-periods/detail/{id}';
    const REGISTRATION_PERIOD_UPDATE = '/api/registration-periods/update/{id}';
    const REGISTRATION_PERIOD_DESTROY = '/api/registration-periods/destroy/{id}';

    // Registration
    const REGISTRATION_LIST = '/api/registrations';
    const REGISTRATION_STORE = '/api/registrations/store';
    const REGISTRATION_DETAIL = '/api/registrations/detail/{id}';
    const REGISTRATION_UPDATE = '/api/registrations/update/{id}';
    const REGISTRATION_DESTROY = '/api/registrations/destroy/{id}';

    // Registration Detail
    const REGISTRATION_DETAIL_LIST = '/api/registration-details';
    const REGISTRATION_DETAIL_STORE = '/api/registration-details/store';
    const REGISTRATION_DETAIL_ITEM = '/api/registration-details/detail/{id}';
    const REGISTRATION_DETAIL_UPDATE = '/api/registration-details/update/{id}';
    const REGISTRATION_DETAIL_DESTROY = '/api/registration-details/destroy/{id}';

    // Regional
    const REGIONAL_LIST = '/api/properties/regionals';
    const REGIONAL_STORE = '/api/properties/regionals/store';
    const REGIONAL_DETAIL = '/api/properties/regionals/detail/{id}';
    const REGIONAL_UPDATE = '/api/properties/regionals/update/{id}';
    const REGIONAL_DESTROY = '/api/properties/regionals/destroy/{id}';

    // Alliance Request
    const ALLIANCE_REQUEST_LIST = '/api/alliance-requests';
    const ALLIANCE_REQUEST_STORE = '/api/alliance-requests/store';
    const ALLIANCE_REQUEST_DETAIL = '/api/alliance-requests/detail/{id}';
    const ALLIANCE_REQUEST_UPDATE = '/api/alliance-requests/update/{id}';
    const ALLIANCE_REQUEST_DESTROY = '/api/alliance-requests/destroy/{id}';

    // Competition
    const COMPETITION_LIST = '/api/competitions';
    const COMPETITION_STORE = '/api/competitions/store';
    const COMPETITION_DETAIL = '/api/competitions/detail/{id}';
    const COMPETITION_UPDATE = '/api/competitions/update/{id}';
    const COMPETITION_DESTROY = '/api/competitions/destroy/{id}';
    const COMPETITION_ASSIGN_NUMBERS = '/api/competitions/assign-numbers/{id}';

    // Event Competition
    const EVENT_COMPETITION_LIST = '/api/event-competitions';
    const EVENT_COMPETITION_STORE = '/api/event-competitions/store';
    const EVENT_COMPETITION_DETAIL = '/api/event-competitions/detail/{id}';
    const EVENT_COMPETITION_UPDATE = '/api/event-competitions/update/{id}';
    const EVENT_COMPETITION_DESTROY = '/api/event-competitions/destroy/{id}';

    // Competition Registration
    const COMPETITION_REGISTRATION_LIST = '/api/competition-registrations';
    const COMPETITION_REGISTRATION_STORE = '/api/competition-registrations/store';
    const COMPETITION_REGISTRATION_DETAIL = '/api/competition-registrations/detail/{id}';
    const COMPETITION_REGISTRATION_UPDATE = '/api/competition-registrations/update/{id}';
    const COMPETITION_REGISTRATION_DESTROY = '/api/competition-registrations/destroy/{id}';
    const COMPETITION_REGISTRATION_CONFIRM = '/api/competition-registrations/confirm/{id}';

    // Registration Detail Attendee
    const REGISTRATION_DETAIL_ATTENDEE_LIST = '/api/registration-detail-attendees';
    const REGISTRATION_DETAIL_ATTENDEE_STORE = '/api/registration-detail-attendees/store';
    const REGISTRATION_DETAIL_ATTENDEE_DETAIL = '/api/registration-detail-attendees/detail/{id}';
    const REGISTRATION_DETAIL_ATTENDEE_DESTROY = '/api/registration-detail-attendees/destroy/{id}';

    // Attendee
    const ATTENDEE_LIST = '/api/attendees';
    const ATTENDEE_STORE = '/api/attendees/store';
    const ATTENDEE_DETAIL = '/api/attendees/detail/{id}';
    const ATTENDEE_UPDATE = '/api/attendees/update/{id}';
    const ATTENDEE_DESTROY = '/api/attendees/destroy/{id}';
    const ATTENDEE_UPLOAD_DOCUMENTS = '/api/attendees/upload-documents/{id}';
    const ATTENDEE_APPROVE = '/api/attendees/approve/{id}';
    const ATTENDEE_REJECT = '/api/attendees/reject/{id}';
    const ATTENDEE_BULK_STORE = '/api/attendees/bulk-store';

    // Competition Department
    const COMPETITION_DEPARTMENT_LIST = '/api/competition-departments';
    const COMPETITION_DEPARTMENT_STORE = '/api/competition-departments/store';
    const COMPETITION_DEPARTMENT_DETAIL = '/api/competition-departments/detail/{id}';
    const COMPETITION_DEPARTMENT_DESTROY = '/api/competition-departments/destroy/{id}';
    const COMPETITION_DEPARTMENT_SYNC = '/api/competition-departments/sync/{competition_id}';

    // Sport Team
    const SPORT_TEAM_LIST = '/api/sport-teams';
    const SPORT_TEAM_STORE = '/api/sport-teams/store';
    const SPORT_TEAM_DETAIL = '/api/sport-teams/detail/{id}';
    const SPORT_TEAM_UPDATE = '/api/sport-teams/update/{id}';
    const SPORT_TEAM_DESTROY = '/api/sport-teams/destroy/{id}';

    // Sport Team Member
    const SPORT_TEAM_MEMBER_LIST = '/api/sport-team-members';
    const SPORT_TEAM_MEMBER_STORE = '/api/sport-team-members/store';
    const SPORT_TEAM_MEMBER_DETAIL = '/api/sport-team-members/detail/{id}';
    const SPORT_TEAM_MEMBER_UPDATE = '/api/sport-team-members/update/{id}';
    const SPORT_TEAM_MEMBER_DESTROY = '/api/sport-team-members/destroy/{id}';
    const SPORT_TEAM_MEMBER_COUNT_BY_ATTENDEE = '/api/sport-team-members/count-by-attendee/{attendee_id}';

    // Beauty Contest
    const BEAUTY_CONTEST_LIST = '/api/beauty-contests';
    const BEAUTY_CONTEST_STORE = '/api/beauty-contests/store';
    const BEAUTY_CONTEST_DETAIL = '/api/beauty-contests/detail/{id}';
    const BEAUTY_CONTEST_UPDATE = '/api/beauty-contests/update/{id}';
    const BEAUTY_CONTEST_DESTROY = '/api/beauty-contests/destroy/{id}';

    // Talent Show
    const TALENT_SHOW_LIST = '/api/talent-shows';
    const TALENT_SHOW_STORE = '/api/talent-shows/store';
    const TALENT_SHOW_DETAIL = '/api/talent-shows/detail/{id}';
    const TALENT_SHOW_UPDATE = '/api/talent-shows/update/{id}';
    const TALENT_SHOW_DESTROY = '/api/talent-shows/destroy/{id}';

    // Talent Category
    const TALENT_CATEGORY_LIST = '/api/talent-categories';
    const TALENT_CATEGORY_STORE = '/api/talent-categories/store';
    const TALENT_CATEGORY_DETAIL = '/api/talent-categories/detail/{id}';
    const TALENT_CATEGORY_UPDATE = '/api/talent-categories/update/{id}';
    const TALENT_CATEGORY_DESTROY = '/api/talent-categories/destroy/{id}';

    // Talent Entry
    const TALENT_ENTRY_LIST = '/api/talent-entries';
    const TALENT_ENTRY_STORE = '/api/talent-entries/store';
    const TALENT_ENTRY_DETAIL = '/api/talent-entries/detail/{id}';
    const TALENT_ENTRY_UPDATE = '/api/talent-entries/update/{id}';
    const TALENT_ENTRY_DESTROY = '/api/talent-entries/destroy/{id}';

    // Talent Entry Member
    const TALENT_ENTRY_MEMBER_LIST = '/api/talent-entry-members';
    const TALENT_ENTRY_MEMBER_STORE = '/api/talent-entry-members/store';
    const TALENT_ENTRY_MEMBER_DETAIL = '/api/talent-entry-members/detail/{id}';
    const TALENT_ENTRY_MEMBER_DESTROY = '/api/talent-entry-members/destroy/{id}';

    // Alliance Request - Additional
    const ALLIANCE_REQUEST_RESPOND = '/api/alliance-requests/respond/{id}';
    const ALLIANCE_REQUEST_BY_TARGET = '/api/alliance-requests/by-target/{org_id}';

    // Dashboard Statistics
    const DASHBOARD_STATS = '/api/dashboard/stats';

    // Event Agenda
    const EVENT_AGENDA_LIST = '/api/event-agendas';
    const EVENT_AGENDA_STORE = '/api/event-agendas/store';
    const EVENT_AGENDA_DETAIL = '/api/event-agendas/detail/{id}';
    const EVENT_AGENDA_UPDATE = '/api/event-agendas/update/{id}';
    const EVENT_AGENDA_DESTROY = '/api/event-agendas/destroy/{id}';

    // Content Round
    const CONTENT_ROUND_LIST = '/api/content-rounds';
    const CONTENT_ROUND_STORE = '/api/content-rounds/store';
    const CONTENT_ROUND_DETAIL = '/api/content-rounds/detail/{id}';
    const CONTENT_ROUND_UPDATE = '/api/content-rounds/update/{id}';
    const CONTENT_ROUND_DESTROY = '/api/content-rounds/destroy/{id}';

    // Competition Round
    const COMPETITION_ROUND_LIST = '/api/competition-rounds';
    const COMPETITION_ROUND_STORE = '/api/competition-rounds/store';
    const COMPETITION_ROUND_DETAIL = '/api/competition-rounds/detail/{id}';
    const COMPETITION_ROUND_UPDATE = '/api/competition-rounds/update/{id}';
    const COMPETITION_ROUND_DESTROY = '/api/competition-rounds/destroy/{id}';

    // Competition Round Result
    const COMPETITION_ROUND_RESULT_LIST = '/api/competition-round-results';
    const COMPETITION_ROUND_RESULT_STORE = '/api/competition-round-results/store';
    const COMPETITION_ROUND_RESULT_DETAIL = '/api/competition-round-results/detail/{id}';
    const COMPETITION_ROUND_RESULT_UPDATE = '/api/competition-round-results/update/{id}';
    const COMPETITION_ROUND_RESULT_DESTROY = '/api/competition-round-results/destroy/{id}';

    // Competition Department - Additional
    const COMPETITION_DEPARTMENT_CHECK_ELIGIBILITY = '/api/competition-departments/check-eligibility';

    // Beauty Registration
    const BEAUTY_REGISTRATION_LIST = '/api/beauty-registrations';
    const BEAUTY_REGISTRATION_STORE = '/api/beauty-registrations/store';
    const BEAUTY_REGISTRATION_DETAIL = '/api/beauty-registrations/detail/{id}';
    const BEAUTY_REGISTRATION_UPDATE = '/api/beauty-registrations/update/{id}';
    const BEAUTY_REGISTRATION_DESTROY = '/api/beauty-registrations/destroy/{id}';

    // Beauty Round
    const BEAUTY_ROUND_LIST = '/api/beauty-rounds';
    const BEAUTY_ROUND_STORE = '/api/beauty-rounds/store';
    const BEAUTY_ROUND_DETAIL = '/api/beauty-rounds/detail/{id}';
    const BEAUTY_ROUND_UPDATE = '/api/beauty-rounds/update/{id}';
    const BEAUTY_ROUND_DESTROY = '/api/beauty-rounds/destroy/{id}';

    // Beauty Round Result
    const BEAUTY_ROUND_RESULT_LIST = '/api/beauty-round-results';
    const BEAUTY_ROUND_RESULT_STORE = '/api/beauty-round-results/store';
    const BEAUTY_ROUND_RESULT_DETAIL = '/api/beauty-round-results/detail/{id}';
    const BEAUTY_ROUND_RESULT_UPDATE = '/api/beauty-round-results/update/{id}';
    const BEAUTY_ROUND_RESULT_DESTROY = '/api/beauty-round-results/destroy/{id}';

    // Beauty Score
    const BEAUTY_SCORE_LIST = '/api/beauty-scores';
    const BEAUTY_SCORE_STORE = '/api/beauty-scores/store';
    const BEAUTY_SCORE_DETAIL = '/api/beauty-scores/detail/{id}';
    const BEAUTY_SCORE_UPDATE = '/api/beauty-scores/update/{id}';
    const BEAUTY_SCORE_DESTROY = '/api/beauty-scores/destroy/{id}';

    // Meal
    const MEAL_LIST = '/api/meals';
    const MEAL_STORE = '/api/meals/store';
    const MEAL_DETAIL = '/api/meals/detail/{id}';
    const MEAL_UPDATE = '/api/meals/update/{id}';
    const MEAL_DESTROY = '/api/meals/destroy/{id}';

    // Meal Table
    const MEAL_TABLE_LIST = '/api/meal-tables';
    const MEAL_TABLE_STORE = '/api/meal-tables/store';
    const MEAL_TABLE_DETAIL = '/api/meal-tables/detail/{id}';
    const MEAL_TABLE_UPDATE = '/api/meal-tables/update/{id}';
    const MEAL_TABLE_DESTROY = '/api/meal-tables/destroy/{id}';

    // Meal Attendee
    const MEAL_ATTENDEE_LIST = '/api/meal-attendees';
    const MEAL_ATTENDEE_STORE = '/api/meal-attendees/store';
    const MEAL_ATTENDEE_DETAIL = '/api/meal-attendees/detail/{id}';
    const MEAL_ATTENDEE_UPDATE = '/api/meal-attendees/update/{id}';
    const MEAL_ATTENDEE_DESTROY = '/api/meal-attendees/destroy/{id}';

    // Meal Checkin
    const MEAL_CHECKIN_LIST = '/api/meal-checkins';
    const MEAL_CHECKIN_STORE = '/api/meal-checkins/store';
    const MEAL_CHECKIN_DETAIL = '/api/meal-checkins/detail/{id}';
    const MEAL_CHECKIN_UPDATE = '/api/meal-checkins/update/{id}';
    const MEAL_CHECKIN_DESTROY = '/api/meal-checkins/destroy/{id}';

    // Meal Cutoff
    const MEAL_CUTOFF_LIST = '/api/meal-cutoffs';
    const MEAL_CUTOFF_STORE = '/api/meal-cutoffs/store';
    const MEAL_CUTOFF_DETAIL = '/api/meal-cutoffs/detail/{id}';
    const MEAL_CUTOFF_UPDATE = '/api/meal-cutoffs/update/{id}';
    const MEAL_CUTOFF_DESTROY = '/api/meal-cutoffs/destroy/{id}';

    // Alliance
    const ALLIANCE_LIST = '/api/alliances';
    const ALLIANCE_STORE = '/api/alliances/store';
    const ALLIANCE_DETAIL = '/api/alliances/detail/{id}';
    const ALLIANCE_UPDATE = '/api/alliances/update/{id}';
    const ALLIANCE_DESTROY = '/api/alliances/destroy/{id}';

    // Attendee Role
    const ATTENDEE_ROLE_LIST = '/api/attendee-roles';
    const ATTENDEE_ROLE_STORE = '/api/attendee-roles/store';
    const ATTENDEE_ROLE_DETAIL = '/api/attendee-roles/detail/{id}';
    const ATTENDEE_ROLE_UPDATE = '/api/attendee-roles/update/{id}';
    const ATTENDEE_ROLE_DESTROY = '/api/attendee-roles/destroy/{id}';

    // Badge
    const BADGE_LIST = '/api/badges';
    const BADGE_STORE = '/api/badges/store';
    const BADGE_DETAIL = '/api/badges/detail/{id}';
    const BADGE_UPDATE = '/api/badges/update/{id}';
    const BADGE_DESTROY = '/api/badges/destroy/{id}';

    // Sport Match
    const SPORT_MATCH_LIST = '/api/sport-matches';
    const SPORT_MATCH_STORE = '/api/sport-matches/store';
    const SPORT_MATCH_DETAIL = '/api/sport-matches/detail/{id}';
    const SPORT_MATCH_UPDATE = '/api/sport-matches/update/{id}';
    const SPORT_MATCH_DESTROY = '/api/sport-matches/destroy/{id}';

    // Sport Match Result
    const SPORT_MATCH_RESULT_LIST = '/api/sport-match-results';
    const SPORT_MATCH_RESULT_STORE = '/api/sport-match-results/store';
    const SPORT_MATCH_RESULT_DETAIL = '/api/sport-match-results/detail/{id}';
    const SPORT_MATCH_RESULT_UPDATE = '/api/sport-match-results/update/{id}';
    const SPORT_MATCH_RESULT_DESTROY = '/api/sport-match-results/destroy/{id}';

    // Sport Stage
    const SPORT_STAGE_LIST = '/api/sport-stages';
    const SPORT_STAGE_STORE = '/api/sport-stages/store';
    const SPORT_STAGE_DETAIL = '/api/sport-stages/detail/{id}';
    const SPORT_STAGE_UPDATE = '/api/sport-stages/update/{id}';
    const SPORT_STAGE_DESTROY = '/api/sport-stages/destroy/{id}';

    // Sport Stage Team
    const SPORT_STAGE_TEAM_LIST = '/api/sport-stage-teams';
    const SPORT_STAGE_TEAM_STORE = '/api/sport-stage-teams/store';
    const SPORT_STAGE_TEAM_DETAIL = '/api/sport-stage-teams/detail/{id}';
    const SPORT_STAGE_TEAM_UPDATE = '/api/sport-stage-teams/update/{id}';
    const SPORT_STAGE_TEAM_DESTROY = '/api/sport-stage-teams/destroy/{id}';

    // Alliance Team Orgs
    const ALLIANCE_TEAM_ORG_LIST = '/api/alliance-team-orgs';
    const ALLIANCE_TEAM_ORG_STORE = '/api/alliance-team-orgs/store';
    const ALLIANCE_TEAM_ORG_DETAIL = '/api/alliance-team-orgs/detail/{id}';
    const ALLIANCE_TEAM_ORG_UPDATE = '/api/alliance-team-orgs/update/{id}';
    const ALLIANCE_TEAM_ORG_DESTROY = '/api/alliance-team-orgs/destroy/{id}';

    // Event Sport Alliance Configs
    const EVENT_SPORT_ALLIANCE_CONFIG_LIST = '/api/event-sport-alliance-configs';
    const EVENT_SPORT_ALLIANCE_CONFIG_STORE = '/api/event-sport-alliance-configs/store';
    const EVENT_SPORT_ALLIANCE_CONFIG_DETAIL = '/api/event-sport-alliance-configs/detail/{id}';
    const EVENT_SPORT_ALLIANCE_CONFIG_UPDATE = '/api/event-sport-alliance-configs/update/{id}';
    const EVENT_SPORT_ALLIANCE_CONFIG_DESTROY = '/api/event-sport-alliance-configs/destroy/{id}';

    // Talent Score
    const TALENT_SCORE_LIST = '/api/talent-scores';
    const TALENT_SCORE_STORE = '/api/talent-scores/store';
    const TALENT_SCORE_DETAIL = '/api/talent-scores/detail/{id}';
    const TALENT_SCORE_UPDATE = '/api/talent-scores/update/{id}';
    const TALENT_SCORE_DESTROY = '/api/talent-scores/destroy/{id}';

    // Approval Workflow
    const APPROVAL_WORKFLOW_LIST = '/api/approval-workflows';
    const APPROVAL_WORKFLOW_STORE = '/api/approval-workflows/store';
    const APPROVAL_WORKFLOW_DETAIL = '/api/approval-workflows/detail/{id}';
    const APPROVAL_WORKFLOW_UPDATE = '/api/approval-workflows/update/{id}';
    const APPROVAL_WORKFLOW_DESTROY = '/api/approval-workflows/destroy/{id}';

    // Approval Workflow Approver
    const APPROVAL_WORKFLOW_APPROVER_LIST = '/api/approval-workflow-approvers';
    const APPROVAL_WORKFLOW_APPROVER_STORE = '/api/approval-workflow-approvers/store';
    const APPROVAL_WORKFLOW_APPROVER_DETAIL = '/api/approval-workflow-approvers/detail/{id}';
    const APPROVAL_WORKFLOW_APPROVER_UPDATE = '/api/approval-workflow-approvers/update/{id}';
    const APPROVAL_WORKFLOW_APPROVER_DESTROY = '/api/approval-workflow-approvers/destroy/{id}';
    const APPROVAL_WORKFLOW_APPROVER_BY_USER = '/api/approval-workflow-approvers/by-user/{portal_user_id}';

    // Registration Approval
    const REGISTRATION_APPROVAL_LIST = '/api/registration-approvals';
    const REGISTRATION_APPROVAL_STORE = '/api/registration-approvals/store';
    const REGISTRATION_APPROVAL_DETAIL = '/api/registration-approvals/detail/{id}';
    const REGISTRATION_APPROVAL_PENDING = '/api/registration-approvals/pending/{portal_user_id}';
    const REGISTRATION_APPROVAL_APPROVE = '/api/registration-approvals/approve/{id}';
    const REGISTRATION_APPROVAL_REJECT = '/api/registration-approvals/reject/{id}';
    const REGISTRATION_APPROVAL_REVISION = '/api/registration-approvals/revision/{id}';
    const REGISTRATION_APPROVAL_RESUBMIT = '/api/registration-approvals/resubmit/{id}';

    // Registration Approval Log
    const REGISTRATION_APPROVAL_LOG_LIST = '/api/registration-approval-logs';
    const REGISTRATION_APPROVAL_LOG_BY_REGISTRATION = '/api/registration-approval-logs/by-registration/{registration_id}';

    public static function url($endpoint, $params = array())
    {
        $url = $endpoint;
        foreach ($params as $key => $value) {
            $url = str_replace('{' . $key . '}', $value, $url);
        }
        return $url;
    }
}
