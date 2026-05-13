<?php

class ApiEndpoints
{
    // Property
    const PROPERTY_LIST = '/api/properties';
    const PROPERTY_STORE = '/api/properties/store';
    const PROPERTY_DETAIL = '/api/properties/detail/{id}';
    const PROPERTY_UPDATE = '/api/properties/update/{id}';
    const PROPERTY_DESTROY = '/api/properties/destroy/{id}';

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

    // Event Sport
    const EVENT_SPORT_LIST = '/api/event-sports';
    const EVENT_SPORT_STORE = '/api/event-sports/store';
    const EVENT_SPORT_DETAIL = '/api/event-sports/detail/{id}';
    const EVENT_SPORT_UPDATE = '/api/event-sports/update/{id}';
    const EVENT_SPORT_DESTROY = '/api/event-sports/destroy/{id}';

    // Registration Period
    const REGISTRATION_PERIOD_LIST = '/api/registration-periods';
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

    public static function url($endpoint, $params = array())
    {
        $url = $endpoint;
        foreach ($params as $key => $value) {
            $url = str_replace('{' . $key . '}', $value, $url);
        }
        return $url;
    }
}
