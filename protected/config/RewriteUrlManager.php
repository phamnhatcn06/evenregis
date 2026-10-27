<?php

$urlRewrite = array(
    // Cổng bình chọn Miss (public) — đặt TRƯỚC các rule generic
    'binh-chon' => 'frontend/binhchon/index',
    'binh-chon/<action:\w+>' => 'frontend/binhchon/<action>',

    // Cổng đăng ký hoạt động (Fun Run & Tham quan) — đặt TRƯỚC các rule generic
    'run' => 'frontend/run/index',
    'run/<action:\w+>' => 'frontend/run/<action>',

    // Màn quét chấm ăn (station) — public, đặt TRƯỚC các rule generic
    'cham-an' => 'frontend/mealScan/index',
    'cham-an/<action:\w+>' => 'frontend/mealScan/<action>',

    // Trang công bố người trúng giải (public) — đặt TRƯỚC rule generic
    'luckydraw' => 'frontend/luckyDraw/index',

    // Lucky Draw (admin) — không còn quay số: slug mặc định vào màn Import người trúng giải
    'quay-so' => 'admin/luckyDraw/import',
    'quay-so/trung-giai' => 'admin/luckyDraw/import',
    'quay-so/dieu-khien' => 'admin/luckyDraw/remote',
    'quay-so/cai-dat' => 'admin/luckyDraw/index',

    // Cổng cá nhân người tham dự — đặt TRƯỚC các rule generic để action 2 đoạn (vd: portal/saveProfile) không bị bắt nhầm
    'portal' => 'frontend/portal/login',
    'lich-trinh-cua-toi' => 'frontend/portal/schedule',
    'portal/<action:\w+>' => 'frontend/portal/<action>',

    'danh-muc-tin/<title>_<id:\d+>-<page:\d+>' => 'frontend/news/category',
    'danh-muc-tin/<title>_<id:\d+>' => 'frontend/news/category',
    'thu-vien-video/<title>_<id:\d+>-<page:\d+>' => 'frontend/video/index',
    'thu-vien-video/<title>_<id:\d+>' => 'frontend/video/index',
    'thu-vien-video' => 'frontend/video/index',
    'thu-vien-anh/<title>_<id:\d+>-<page:\d+>' => 'frontend/news/photo',
    'thu-vien-anh/<title>_<id:\d+>' => 'frontend/news/photo',
    'thu-vien-anh' => 'frontend/news/photo',
    'tho-bong-da/<title>_<id:\d+>-<page:\d+>' => 'frontend/news/poetics',
    'tho-bong-da/<title>_<id:\d+>' => 'frontend/news/poetics',
    'tho-bong-da' => 'frontend/news/poetics',
    '<controller:\w+>/<id:\d+>' => '<controller>/view',
    '<controller:\w+>/<action:\w+>/<id:\d+>' => '<controller>/<action>',
    '<controller:\w+>/<action:\w+>' => '<controller>/<action>',
    'tin-bong-da' => 'frontend/news/index',
    'tin-tuc/<title>_<id:\d+>' => 'frontend/news/detail',
    'chu-de/<title>_<id:\d+>-<page:\d+>' => 'frontend/news/topic',
    'chu-de/<title>_<id:\d+>' => 'frontend/news/topic',
    'tag/<title>_<id:\d+>-<page:\d+>' => 'frontend/news/tag',
    'tag/<title>_<id:\d+>' => 'frontend/news/tag',
    'post/<id:\d+>/<title:.*?>' => 'post/view',
    'posts/<tag:.*?>' => 'post/index',

    'worldcup' => 'frontend/worldcup',
    'du-lieu-bong-da' => 'frontend/livescore/index',
    'doi-bong/<team_name>-<team_id:\d+>' => 'frontend/teams/index',
    'cau-thu/<player_name>-<player_id:\d+>' => 'frontend/players/info',
    'huan-luyen-vien/<player_name>-<player_id:\d+>' => 'frontend/players/coach',
    '<competition>/<season_name>-<season_id:\d+>' => 'frontend/comparison/tables',
    '<competition>/<home>-<away>/<match_id:\d+>' => 'frontend/comparison/matchs',
    '<competition>/<home>-<away>/<match_id:\d+>' => 'frontend/match/index',
    '<competition>/<season_name>-<season_id:\d+>' => 'frontend/worldcup/index',
    'nhan-dinh' => 'frontend/judge/index',
    '/login' => 'frontend/users/login',
    // REST patterns
    array('api/list', 'pattern' => 'api/<model:\w+>', 'verb' => 'GET'),
    array('api/view', 'pattern' => 'api/<model:\w+>/<id:\d+>', 'verb' => 'GET'),
    array('api/update', 'pattern' => 'api/<model:\w+>/<id:\d+>', 'verb' => 'PUT'),
    array('api/delete', 'pattern' => 'api/<model:\w+>/<id:\d+>', 'verb' => 'DELETE'),
    array('api/create', 'pattern' => 'api/<model:\w+>', 'verb' => 'POST'),
    // Other controllers
    '<controller:\w+>/<action:\w+>' => '<controller>/<action>',
    // Trang Website công khai Đại hội
    '' => 'frontend/daihoi/index',
    'daihoi' => 'frontend/daihoi/index',

    // Slug thân thiện cho các link in trong Sổ tay Đại hội
    'agenda-tong-the' => 'frontend/daihoi/agenda',
    'tiec-welcome' => 'frontend/daihoi/tiecWelcome',
    'lich-thi-the-thao' => 'frontend/daihoi/schedule',
    'lich-tham-quan' => 'frontend/daihoi/lichThamQuan',
    'tiec-gala' => 'frontend/daihoi/tiecGala',
    'ho-tro' => 'frontend/daihoi/hoTro',
    'doan-thu-ky' => 'frontend/daihoi/doanThuKy',
    'so-do-dia-diem' => 'frontend/daihoi/soDoDiaDiem',
    'so-tay-dai-hoi' => 'frontend/daihoi/soTayDaiHoi',
    'fun-run' => 'frontend/daihoi/funRun',
    'khao-sat' => 'frontend/daihoi/khaoSat',

    // Tin tức Đại hội: danh sách + chi tiết (đặt rule chi tiết TRƯỚC rule danh sách)
    'tin-tuc/<id:\d+>' => 'frontend/daihoi/newsDetail',
    'tin-tuc' => 'frontend/daihoi/news',

    'daihoi/<action:\w+>' => 'frontend/daihoi/<action>',
);
