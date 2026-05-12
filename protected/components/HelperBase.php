<?php

class HelperBase
{

  public static function getIdsFromCategoryData($categories)
  {
    $ids = array();
    foreach ($categories as $category) {
      if (!in_array($category->id, $ids))
        $ids[] = $category->id;
    }

    return $ids;
  }

  public static function run_in_background($command)
  {
    $PID = shell_exec(" php " . Yii::app()->basePath . "/../yiiCmd.php " . $command);
    return $PID;
  }

  public static function makeListNewsByCategoryId($categories, $news, $newsPerCat = 2)
  {
    $result = array();
    foreach ($news as $n) {
      foreach ($categories as $category) {
        if ($category->id == $n->category_id) {
          if ((isset($result[$category->id]) && count($result[$category->id]) < $newsPerCat) || !isset($result[$category->id]))
            $result[$category->id][] = $n;
        }
      }
    }

    return $result;
  }

  public static function getCompetitionCodeByName($trophieName)
  {
    $result = null;
    $result = MoCompetition::model()->findOne(array('trophie_name' => $trophieName));
    return $result;
  }

  public static function makeListJudgeByCompetitionId($competition, $judge, $newsPerCat = 2)
  {
    $result = array();
    foreach ($judge as $n) {
      foreach ($competition as $competitionValue) {
        if ($competitionValue->code == $n->competition_id) {
          if ((isset($result[$competitionValue->code]) && count($result[$competitionValue->code]) < $newsPerCat) || !isset($result[$competitionValue->code]))
            $result[$competitionValue->code][] = $n;
        }
      }
    }

    return $result;
  }

  public static function convertDatetimeByFormat($time, $format = 'Y-m-d')
  {
    return date($format, strtotime($time));
  }

  public static function createUrl($url, $id = null, $title = null, $baseUrl = true)
  {
    if ($baseUrl == true) {
      if (is_null($id) && is_null($title)) {
        //Yii::app()->getBaseUrl(true)
        return Yii::app()->getBaseUrl(true) . Yii::app()->createUrl($url);
      } else if (is_null($id) && !is_null($title)) {
        return Yii::app()->getBaseUrl(true) . Yii::app()->createUrl($url, array('title' => UrlTransliterate::cleanString($title)));
      } else {
        return Yii::app()->getBaseUrl(true) . Yii::app()->createUrl($url, array('id' => $id, 'title' => UrlTransliterate::cleanString($title)));
      }
    } else {
      if (is_null($id) && is_null($title)) {
        //Yii::app()->getBaseUrl(true)
        return Yii::app()->createUrl($url);
      } else if (is_null($id) && !is_null($title)) {
        return Yii::app()->createUrl($url, array('title' => UrlTransliterate::cleanString($title)));
      } else {
        return Yii::app()->createUrl($url, array('id' => $id, 'title' => UrlTransliterate::cleanString($title)));
      }
    }
  }

  public static function makeRelationUrl($news)
  {
    $newsUrl = '/admin/relationNews/admin/id/';
    return $newsUrl . $news->id;
  }

  public static function makeRelationVideoUrl($video)
  {
    $newsUrl = '/admin/relationVideo/admin/id/';
    return $newsUrl . $video->id;
  }

  public static function makeNewsUrl($news)
  {
    $newsUrl = '/frontend/news/detail';
    if ($news->type == Params::$nTypeJudge && $news->match_id > 0) {
      $newsUrl = '/frontend/judge/match';
    } else if ($news->is_predict == 1) {
      $newsUrl = '/frontend/news/predict';
    }
    return self::createUrl($newsUrl, $news->id, UrlTransliterate::cleanString($news->title));
  }

  public static function makeCategoryVideoUrl($category)
  {
    return self::createUrl('/frontend/video/category', $category->id, UrlTransliterate::cleanString($category->title));
  }

  public static function makeCategoryUrl($category)
  {
    return self::createUrl('/frontend/news/category', $category->id, UrlTransliterate::cleanString($category->title), false);
  }

  public static function makeCategoryJudgeUrl($category)
  {
    return self::createUrl('/frontend/judge/category', $category->id, UrlTransliterate::cleanString($category->title));
  }

  public static function makeJudgeUrl($news)
  {
    if ($news) {
      return self::createUrl('/frontend/judge/match', $news->id, UrlTransliterate::cleanString($news->title));
    }
  }

  public static function getVideoByTopicId($topic)
  {
    $video = array();
    $video = MNewsMedia::model()->findAll(' topic_id=' . $topic . ' order by created desc limit 4 ');
    return $video;
  }

  public static function getVideoById($id)
  {
    $video = array();
    $video = MNewsMedia::model()->findByPk($id);
    return $video;
  }

  public static function getJudgeNewsByTopicId($topic)
  {
    $judge = array();
    $judge = MNews::model()->findAll(' active=1 and category_id=' . $topic . ' and match_id > 0 and type = ' . Params::$nTypeJudge . ' order by created desc limit 4 ');
    return $judge;
  }

  public static function makeCategoryUrlById($categoryId)
  {
    $url = '';
    $category = MCategories::model()->findByPk($categoryId);
    if ($category != null) {
      $url = self::makeCategoryUrl($category);
    }
    return $url;
  }

  public static function makeTagCategoryUrl($tag)
  {
    $name = $tag->tag_name;
    if (strpos($tag->tag_name, " ") === false) {
      $name .= ' t';
    }
    return self::createUrl('/frontend/news/tag', $tag->id, UrlTransliterate::cleanString($name));
  }

  public static function makeTopicUrl($topic)
  {
    $name = $topic->topic;
    if (strpos($topic->topic, " ") === false) {
      $name .= ' t';
    }
    return self::createUrl('/frontend/news/topic', $topic->id, UrlTransliterate::cleanString($name), false);
  }

  public static function makeBannerUrl($banner)
  {
    return MyHelper::rewriteShortUrl($banner->id, $banner->title, 'b');
  }

  public static function getNotifyChannels($seasons, $matchs)
  {
    $channels = array();
    foreach ($seasons as $season) {
      if (isset($matchs[$season['code']])) {
        foreach ($matchs[$season['code']] as $match) {
          $channel = "'" . NotifyBase::$_prefixHomeChannel . $match->match_id . "'";
          if (!in_array($channel, $channels)) {
            $channels[] = $channel;
          }
        }
      }
    }
    return $channels;
  }

  public static function getCompetitionNotifyChannels($seasons, $matchs)
  {
    $channels = array();
    foreach ($seasons as $season) {
      if (isset($matchs[$season['code']])) {
        foreach ($matchs[$season['code']] as $match) {
          $channel = "'" . NotifyBase::$_prefixHomeChannel . $match->match_id . "'";
          if (!in_array($channel, $channels)) {
            $channels[] = $channel;
          }
        }
      }
    }
    return $channels;
  }

  public static function getVideoUpdatedTime($time, $format = "Y-m-d")
  {
    return date($format, strtotime($time));
  }

  public static function makeVideoUrl($video)
  {
    return self::createUrl('/frontend/video/detail', $video->id, UrlTransliterate::cleanString($video->title));
    // return self::createUrl('/frontend/video/index', $video->id, UrlTransliterate::cleanString($video->title));
  }

  public static function getNewGoals()
  {
    $criteria = array(
      '_sort' => array('created_at' => -1),
      '_limit' => Params::$totalNewGoals,
    );
    $goalModel = new EMongoCriteria($criteria);
    $newGoals = MoGoal::model()->findAll($goalModel);

    $content = '';
    foreach ($newGoals as $goal) {
      $content .= empty($content) ? '' : '--- ';
      $content .= $goal->player_name . ': ' . $goal->minute . '\' ';
      if ($goal->name == 'PG' || $goal->name == 'OG') {
        $content .= '(' . $goal->name . ') ';
      }
      if (!is_null($goal->home) && !is_null($goal->away)) {
        $content .= $goal->home . ' ' . $goal->score . ' ' . $goal->away . ' ';
      }
    }

    return $content;
  }

  public static function getLogoOfTeam($teamName, $teamId, $thumb = "25x25")
  {
    return Yii::app()->getBaseUrl(true) . '/images/' . $thumb . '/teams/' . UrlTransliterate::cleanString($teamName) . '-' . $teamId . '.png';
  }

  public static function getMiniLogoOfTeam($teamName, $teamId)
  {
    return Yii::app()->getBaseUrl(true) . '/images/16x16/teams/' . UrlTransliterate::cleanString($teamName) . '-' . $teamId . '.png';
  }

  public static function createNewsMediaTitle($title, $length = 250)
  {
    if (strlen($title) > $length) {
      $subLength = self::getLastChar($title, $length);
      $title = substr($title, 0, $subLength) . '...';
    }
    return $title;
  }

  public static function getLastChar($title, $lenght = 250)
  {
    $char = substr($title, $lenght - 1, 1);
    if ($char === ' ') {
      return $lenght - 1;
    } else {
      return self::getLastChar($title, $lenght - 1);
    }
  }

  public static function makeNewsImageUrl($news, $thumb = false, $thumbSizes = array())
  {
    $imageUrl = '';
    if (!$thumb) {
      $imageUrl = Yii::app()->getBaseUrl(true) . Params::NEWS_IMAGE_DIR . '/' . $news->image;
    } else {
      if (count($thumbSizes) > 0) {
        $imageUrl = Yii::app()->getBaseUrl(true) . Params::NEWS_IMAGE_DIR . '/' . $thumbSizes['width'] . 'x' . $thumbSizes['height'] . '/' . $news->image;
      } else {
        $imageUrl = Yii::app()->getBaseUrl(true) . Params::NEWS_IMAGE_DIR . '/' . Params::NEWS_IMATE_THUMB_WIDTH . 'x' . Params::NEWS_IMATE_THUMB_HEIGHT . '/' . $news->image;
      }
    }

    return $imageUrl;
  }

  public static function makeVideoPageUrl()
  {
    return Yii::app()->createUrl('/frontend/video/index');
  }

  public static function makeNewsMoreViewedUrl()
  {
    return Yii::app()->createUrl('/frontend/news/viewed');
  }

  public static function makePhotoMoreViewedUrl()
  {
    return Yii::app()->createUrl('frontend/news/photo');
  }

  public static function makeNewsTransferUrl()
  {
    return Yii::app()->createUrl('/frontend/news/transfer');
  }

  public static function makeNewsTriviaUrl()
  {
    return Yii::app()->createUrl('/frontend/news/trivia');
  }

  public static function makeJudgeMoreViewedUrl()
  {
    return Yii::app()->createUrl('/frontend/judge/list');
  }

  public static function converDataToArray($data, $key_field = 'id', $value_field = 'title')
  {
    $result = array();
    foreach ($data as $dt) {
      $result[$dt->{$key_field}] = $dt->{$value_field};
    }
    return $result;
  }

  public static function getUrlCateCompetition($title)
  {
    $competition = MoCompetition::model()->findOne(array('status' => 1, 'type' => 1, 'name' => $title));
    $url = '';
    if ($competition != null) {
      $url = CHtml::link($competition->name, $competition->code . '.html');
    }
    return $url;
  }

  public static function createSubMenus($data, $isNews = false)
  {
    $menus = array();
    foreach ($data as $item) {
      $m = array();
      $label = '<span class="ico-item"></span>';
      $label .= ($isNews) ? $item->title : $item['menu']->title;
      $link = ($isNews) ? self::makeCategoryUrl($item) : $item['menu']->link;

      $m['label'] = $label;
      $m['url'] = $link;
      if (isset($item['submenu']) && !$isNews) {
        $sub = array();
        foreach ($item['submenu'] as $submenu) {
          $s['label'] = '<span class="ico-item"></span>' . $submenu->title;
          $leagueName = explode('/', $submenu->link);
          $submenu->link = str_replace($leagueName[1], Yii::t('livescore', $leagueName[1]), $submenu->link);
          $s['url'] = $submenu->link;
          $sub[] = $s;
        }
        $m['items'] = $sub;
      }
      $menus[] = $m;
    }

    return $menus;
  }

  public static function getNewsViewed($news)
  {
    $viewed = empty($news->viewed) ? 1 : $news->viewed;
    return $viewed + rand(572, 1577);
  }

  public static function getSubTitleTab($title, $length = 100)
  {
    if (strlen($title) > $length) {
      $subLength = self::getLastChar($title, $length);
      $title = substr($title, 0, $subLength) . '...';
    }
    return $title;
  }

  public static function getUrlFromData($data)
  {
    $urls = array();
    foreach ($data as $row) {
      $urls[] = Yii::app()->getBaseUrl(true) . $row->link;
    }

    return $urls;
  }

  public static function getSeasonUrlByCode($code, $competitionid = false)
  {
    $criteria = array(
      '_condition' => array(
        'code' => (int) $code,
      ),
      '_sort' => array('sort' => 1),
      '_limit' => 1,
      '_skip' => 0,
    );
    $_criteria = new EMongoCriteria($criteria);
    $season = MoSeason::model()->findOne($_criteria);

    if ($competitionid) {
      return $season->competition_id;
    }

    $url = 'javascript:';
    if (isset($season->code))
      $url = Livecore_Helper::renderUrlTable(array('season_id' => $season->code, 'season_name' => $season->name, 'competition' => $season->competition_id));

    return $url;
  }

  public static function getMatchById($id)
  {
    $match = array();
    $criteria = array(
      '_condition' => array(
        'match_id' => (int) $id,
      ),
      '_sort' => array('start' => -1),
      '_limit' => 1,
      '_skip' => 0,
    );
    $_criteria = new EMongoCriteria($criteria);
    $match = MoMatch::model()->findOne($_criteria);
    return $match;
  }

  public static function getChanelById($id)
  {
    $result = array();
    $result = MChanel::model()->findByPk($id);
    return $result;
  }

  public static function getMatchUrlById($id)
  {
    $criteria = array(
      '_condition' => array(
        'match_id' => (int) $id,
      ),
      '_sort' => array('start' => -1),
      '_limit' => 1,
      '_skip' => 0,
    );
    $_criteria = new EMongoCriteria($criteria);
    $match = MoMatch::model()->findOne($_criteria);
    $competitionid = self::getSeasonUrlByCode($match->season_id, true);

    $url = 'javascript:';
    if (isset($match->match_id) && $competitionid)
      $url = Livecore_Helper::renderUrlMatch(array('match_id' => $match->match_id, 'season_id' => $match->season_id, 'home' => $match->home, 'away' => $match->away, 'competition' => $competitionid));

    return $url;
  }

  public static function getTeamUrlById($id)
  {
    $criteria = array(
      '_condition' => array(
        'team_id' => (int) $id,
      ),
      '_limit' => 1,
      '_skip' => 0,
    );
    $model = new EMongoCriteria($criteria);
    $team = MoTeam::model()->findOne($model);
    $url = 'javascript:';
    if (isset($team->team_id))
      $url = Livecore_Helper::renderUrlTeam(array('team_id' => $team->team_id, 'team_name' => $team->name));

    return $url;
  }

  public static function getPlayerUrlById($id)
  {
    $criteria = array(
      '_condition' => array(
        'player_id' => (int) $id,
      ),
      '_limit' => 1,
      '_skip' => 0,
    );
    $model = new EMongoCriteria($criteria);
    $player = MoPlayer::model()->findOne($model);

    $url = 'javascript:';
    if (isset($player->player_id))
      $url = Livecore_Helper::renderUrlPlayer(array('player_id' => $player->player_id, 'player_name' => $player->name));

    return $url;
  }

  public static function makeSlideShowUrl($slider)
  {
    $slideUrl = '#';
    $object = $slider['object_type'];
    $objectId = $slider['object_id'];

    switch ($object) {
      case Params::ADMIN_SLIDESHOW_OBJECT_TYPE_SEASON:
        $slideUrl = self::getSeasonUrlByCode($objectId);
        break;
      case Params::ADMIN_SLIDESHOW_OBJECT_TYPE_MATCH:
        $slideUrl = self::getMatchUrlById($objectId);
        break;
      case Params::ADMIN_SLIDESHOW_OBJECT_TYPE_TEAM:
        $slideUrl = self::getTeamUrlById($objectId);
        break;
      case Params::ADMIN_SLIDESHOW_OBJECT_TYPE_PLAYER:
        $slideUrl = self::getPlayerUrlById($objectId);
        break;
      case Params::ADMIN_SLIDESHOW_OBJECT_TYPE_NEWS:
        $news = MNews::model()->findByPk($objectId);
        $slideUrl = self::makeNewsUrl($news);
        break;
      case Params::ADMIN_SLIDESHOW_OBJECT_TYPE_VIDEO:
        $video = MNewsMedia::model()->findByPk($objectId);
        $slideUrl = self::makeVideoUrl($video);
        break;
      default:
        break;
    }

    return $slideUrl;
  }

  public static function getSeasonYear($string)
  {
    $year = '';
    $string = trim($string);
    if (strpos($string, '-') === false && strpos($string, ' ') === false) {
      $arr = str_split($string);
      for ($i = 0; $i < count($arr); $i++) {
        if ($i == 4)
          $year .= '-';
        $year .= $arr[$i];
      }
    }

    return empty($year) ? $string : $year;
  }

  public static function subPlayerName($name)
  {
    $fname = explode('.', $name);
    $sname = explode('-', $fname[count($fname) - 1]);
    return $fname[0] . '. ' . $sname[count($sname) - 1];
  }

  public static function getCoachByTeamId($id)
  {
    if ((int) $id) {
      $criteria = array(
        '_condition' => array(
          '$and' => array(array(
            'club_id' => (int) $id,
            'position' => "coach"
          ))
        ),
        '_sort' => array('start' => 1),
        '_limit' => 1,
        '_skip' => 0,
      );
      $_criteria = new EMongoCriteria($criteria);
      $data = MoPlayer::model()->findOne($_criteria);
    }
    return $data;
  }

  public static function getCurrentUrl()
  {
    return Yii::app()->getBaseUrl(true) . $_SERVER["REQUEST_URI"];
  }

  public static function mergeLineUp($data1, $data2)
  {
    $result = null;
    $keyExist = array();
    foreach ($data1 as $key => $value) {
      if (isset($data2[$key])) {
        $result[$key] = $value . '<br>' . $data2[$key];
        if (!in_array($key, $keyExist)) {
          $keyExist[] = $key;
        }
      } else {
        $result[$key] = $value;
      }
    }

    foreach ($data2 as $key => $value) {
      if (!in_array($key, $keyExist)) {
        $result[$key] = $value;
      }
    }

    return $result;
  }

  public static function limitString($string, $charlimit)
  {
    if (substr($string, $charlimit - 1, 1) != ' ') {
      $string = substr($string, '0', $charlimit);
      $array = explode(' ', $string);
      array_pop($array);
      $new_string = implode(' ', $array);

      return $new_string . '...';
    } else {
      return substr($string, '0', $charlimit - 1) . '...';
    }
  }

  public static function getListChanelByCategory($code)
  {
    $result = array();
    $listChanel = MoTelevision::model()->find(array('category_id' => $code));
    return $listChanel;
  }

  /* -------------------search----------------------- */

  public static function searchNews($key = '', $limit = 10)
  {
    $result = null;
    $criteria = new CDbCriteria();
    $criteria->addSearchCondition('title', $key, true, 'OR');
    $criteria->addSearchCondition('description', $key, true, 'OR');
    $criteria->addSearchCondition('content', $key, true, 'OR');
    $criteria->order = " created desc";
    $result = MNews::model()->findAll($criteria);
    return $result;
  }

  public static function searchPlayer($key = '', $limit = 6)
  {
    $result = null;
    $criteria = array(
      '_condition' => array(
        '$or' => array(
          array('name' => array('$regex' => $key, '$options' => 'im')),
          array('first_name' => array('$regex' => $key, '$options' => 'im')),
          array('last_name' => array('$regex' => $key, '$options' => 'im')),
          array('club_name' => array('$regex' => $key, '$options' => 'im')),
          array('normal_name' => array('$regex' => $key, '$options' => 'im')),
          array('player_id' => (int) $key),
        )
      ),
      '_sort' => array('start' => 1),
      '_limit' => $limit,
      '_skip' => 0,
    );

    $_criteria = new EMongoCriteria($criteria);
    $result = MoPlayer::model()->findAll($_criteria);
    return $result;
  }

  public static function searchTeam($key = '', $limit = 6)
  {
    $result = null;
    $criteria = array(
      '_condition' => array(
        '$or' => array(
          array('name' => array('$regex' => $key, '$options' => 'im')),
          array('bet_name' => array('$regex' => $key, '$options' => 'im')),
          array('tip_name' => array('$regex' => $key, '$options' => 'im')),
          array('address' => array('$regex' => $key, '$options' => 'im')),
          array('country' => array('$regex' => $key, '$options' => 'im')),
          array('venue' => array('$regex' => $key, '$options' => 'im')),
          array('team_id' => (int) $key),
        )
      ),
      '_sort' => array('start' => 1),
      '_limit' => $limit,
      '_skip' => 0,
    );
    $_criteria = new EMongoCriteria($criteria);
    $result = MoTeam::model()->findAll($_criteria);
    return $result;
  }

  public static function searchCompetition($key = '', $limit = 6)
  {
    $result = array();
    $criteria = array(
      '_condition' => array(
        '$or' => array(
          array('name' => array('$regex' => $key, '$options' => 'im')),
          array('code' => array('$regex' => $key, '$options' => 'im')),
          array('group_name' => array('$regex' => $key, '$options' => 'im')),
        )
      ),
      '_sort' => array('start' => 1),
      '_limit' => $limit,
      '_skip' => 0,
    );
    $_criteria = new EMongoCriteria($criteria);
    $result = MoCompetition::model()->findAll($_criteria);
    return $result;
  }

  /* ----------------------search---------------------- */


  /* ------------------Sitemap-------------------------------- */

  //delete sitemap
  public static function deleteUrlSitemap($urlsitemap, $sitemap)
  {

    if (file_exists($sitemap)) {
      $sitemapOld = file_get_contents($sitemap);
      if (isset($sitemapOld)) {
        $url = '<url>';
        $url .= '<loc>' . $urlsitemap . '</loc>';
        $url .= '<changefreq>daily</changefreq>';
        $url .= '<priority>0.5</priority>';
        $url .= '</url>';
        $sitemapNew = str_replace($url, '', $sitemapOld);
        $fp = fopen($sitemap, "w") or exit("Không Tìm Thấy File Cần Mở! ");
        fwrite($fp, $sitemapNew);
        fclose($fp);
      }
    }
  }

  //update sitemap-match.xml by time
  public static function updateSitemapMatch()
  {
    set_time_limit(0);
    $listLinkMatch = MoMatch::model()->findAll(array('is_sitemap_update' => 0));

    $dirfile = Yii::app()->basePath . '/../' . Params::$sitemapMatch;
    if (file_exists($dirfile) && file_get_contents($dirfile) != false) {
      $sitemapOld = file_get_contents($dirfile);
      $sitemapHeader = '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:schemaLocation="http://www.sitemaps.org/schemas/sitemap/0.9 http://www.sitemaps.org/schemas/sitemap/0.9/sitemap.xsd">';
      $sitemap = $sitemapHeader;
      foreach ($listLinkMatch as $value) {
        $value->is_sitemap_update = 1;
        $value->save(false);
        $sitemap .= "<url>";
        $sitemap .= '<loc>' . Params::$urlDefault . Livecore_Helper::renderUrlMatch(array('match_id' => $value['match_id'], 'home' => $value['home'], 'away' => $value['away'], 'competition' => $value['competition_id'])) . '</loc>';
        $sitemap .= '<changefreq>daily</changefreq>';
        $sitemap .= '<priority>0.5</priority>';
        $sitemap .= '</url>';
      }
      $sitemapNew = str_replace($sitemapHeader, $sitemap, $sitemapOld);
      $fp = fopen(Params::$sitemapMatch, "w") or exit("Không Tìm Thấy File Cần Mở! ");
      fwrite($fp, $sitemapNew);
      fclose($fp);
      echo 'Update sitemap-match successfully!';
    }
  }

  //update sitemap-team.xml by time
  public static function updateSitemapTeam()
  {
    set_time_limit(0);
    $listLinkTeam = MoTeam::model()->findAll(array('is_sitemap_update' => 0));
    $dirfile = Yii::app()->basePath . '/../' . Params::$sitemapTeam;
    if (file_exists($dirfile) && file_get_contents($dirfile) != false) {
      $sitemapOld = file_get_contents($dirfile);
      $sitemapHeader = '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:schemaLocation="http://www.sitemaps.org/schemas/sitemap/0.9 http://www.sitemaps.org/schemas/sitemap/0.9/sitemap.xsd">';
      $sitemap = $sitemapHeader;
      foreach ($listLinkTeam as $value) {
        $value->is_sitemap_update = 1;
        $value->save(false);
        $sitemap .= "<url>";
        $sitemap .= '<loc>' . Params::$urlDefault . Livecore_Helper::renderUrlTeam(array('team_id' => $value->team_id, 'team_name' => $value->name)) . '</loc>';
        $sitemap .= '<changefreq>daily</changefreq>';
        $sitemap .= '<priority>0.5</priority>';
        $sitemap .= '</url>';
      }
      $sitemapNew = str_replace($sitemapHeader, $sitemap, $sitemapOld);
      $fp = fopen(Params::$sitemapTeam, "w") or exit("Không Tìm Thấy File Cần Mở! ");
      fwrite($fp, $sitemapNew);
      fclose($fp);
      echo 'Update sitemap-team successfully!';
    }
  }

  //update sitemap-news.xml
  public static function updateSitemapNews($newsLink)
  {
    set_time_limit(0);
    $dirfile = Yii::app()->basePath . '/../' . Params::$sitemapNews;
    if (file_exists($dirfile)) {
      if (file_get_contents($dirfile) != false) {  // file_get_Contents: đọc 1 file nhập vào
        $sitemapOld = file_get_contents($dirfile);
        $sitemapHeader = '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:schemaLocation="http://www.sitemaps.org/schemas/sitemap/0.9 http://www.sitemaps.org/schemas/sitemap/0.9/sitemap.xsd">';
        $url = $sitemapHeader . '<url>';
        $url .= '<loc>' . $newsLink . '</loc>';
        $url .= '<changefreq>daily</changefreq>';
        $url .= '<priority>0.5</priority>';
        $url .= '</url>';
        $sitemapNew = str_replace($sitemapHeader, $url, $sitemapOld);
        $fp = fopen(Params::$sitemapNews, "w") or exit("Không Tìm Thấy File Cần Mở! ");
        fwrite($fp, $sitemapNew);
        fclose($fp);
      }
    }
  }

  //update sitemap-player.xml by time schedule
  public static function updateSitemapPlayer()
  {
    set_time_limit(0);
    $listLinkPlayer = MoPlayer::model()->findAll(array('is_sitemap_update' => 0));
    $dirfile = Yii::app()->basePath . '/../' . Params::$sitemapPlayer;
    if (file_exists($dirfile)) {
      if (file_get_contents($dirfile) != false) {
        $sitemapOld = file_get_contents($dirfile);
        $sitemapHeader = '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:schemaLocation="http://www.sitemaps.org/schemas/sitemap/0.9 http://www.sitemaps.org/schemas/sitemap/0.9/sitemap.xsd">';
        $sitemap = $sitemapHeader;
        foreach ($listLinkPlayer as $value) {
          $value->is_sitemap_update = 1;
          $value->save(false);
          $sitemap .= "<url>";
          $sitemap .= '<loc>' . Params::$urlDefault . Livecore_Helper::renderUrlPlayer(array('player_id' => $value['player_id'], 'player_name' => isset($value['first_name']) ? $value['first_name'] . ' ' . $value['last_name'] : $value['player'])) . '</loc>';
          $sitemap .= '<changefreq>daily</changefreq>';
          $sitemap .= '<priority>0.5</priority>';
          $sitemap .= '</url>';
        }
        $sitemapNew = str_replace($sitemapHeader, $sitemap, $sitemapOld);
        $fp = fopen(Params::$sitemapPlayer, "w") or exit("Không Tìm Thấy File Cần Mở! ");
        fwrite($fp, $sitemapNew);
        fclose($fp);
        echo 'Update sitemap-player successfully!';
      }
    }
  }

  //update sitemap-competition.xml by time schedule
  public static function updateSitemapCompetition()
  {
    set_time_limit(0);
    $listLinkCompetition = MoCompetition::model()->findAll(array('is_sitemap_update' => 0));
    $dirfile = Yii::app()->basePath . '/../' . Params::$sitemapCompetition;
    if (file_exists($dirfile)) {
      if (file_get_contents($dirfile) != false) {
        $sitemapOld = file_get_contents($dirfile);
        $sitemapHeader = '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:schemaLocation="http://www.sitemaps.org/schemas/sitemap/0.9 http://www.sitemaps.org/schemas/sitemap/0.9/sitemap.xsd">';
        $sitemap = $sitemapHeader;
        foreach ($listLinkCompetition as $value) {
          $value->is_sitemap_update = 1;
          $value->save(false);
          $sitemap .= "<url>";
          $sitemap .= '<loc>' . Params::$urlDefault . '/' . $competition->code . '.html' . '</loc>';
          $sitemap .= '<changefreq>daily</changefreq>';
          $sitemap .= '<priority>0.5</priority>';
          $sitemap .= '</url>';
        }
        $sitemapNew = str_replace($sitemapHeader, $sitemap, $sitemapOld);
        $fp = fopen(Params::$sitemapCompetition, "w") or exit("Không Tìm Thấy File Cần Mở! ");
        fwrite($fp, $sitemapNew);
        fclose($fp);
        echo 'Update sitemap-competition successfully!';
      }
    }
  }

  //update sitemap-season.xml by time schedule
  public static function updateSitemapSeason()
  {
    set_time_limit(0);
    $listLinkSeason = MoSeason::model()->findAll(array('is_sitemap_update' => 0));
    $dirfile = Yii::app()->basePath . '/../' . Params::$sitemapSeason;
    if (file_exists($dirfile)) {
      if (file_get_contents($dirfile) != false) {
        $sitemapOld = file_get_contents($dirfile);
        $sitemapHeader = '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:schemaLocation="http://www.sitemaps.org/schemas/sitemap/0.9 http://www.sitemaps.org/schemas/sitemap/0.9/sitemap.xsd">';
        $sitemap = $sitemapHeader;
        foreach ($listLinkSeason as $value) {
          $value->is_sitemap_update = 1;
          $value->save(false);
          $sitemap .= "<url>";
          $sitemap .= '<loc>' . Params::$urlDefault . Livecore_Helper::renderUrlTable(array('season_id' => $season->code, 'season_name' => $season->name, 'competition' => $season->competition_id)) . '</loc>';
          $sitemap .= '<changefreq>daily</changefreq>';
          $sitemap .= '<priority>0.5</priority>';
          $sitemap .= '</url>';
        }
        $sitemapNew = str_replace($sitemapHeader, $sitemap, $sitemapOld);
        $fp = fopen(Params::$sitemapSeason, "w") or exit("Không Tìm Thấy File Cần Mở! ");
        fwrite($fp, $sitemapNew);
        fclose($fp);
        echo 'Update sitemap-season successfully!';
      }
    }
  }

  // get all news
  public static function getAllUrlNews()
  {
    set_time_limit(0);
    $listLinkNews = MNews::model()->findAll(' active=1 order by created desc');
    $sitemap = '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:schemaLocation="http://www.sitemaps.org/schemas/sitemap/0.9 http://www.sitemaps.org/schemas/sitemap/0.9/sitemap.xsd">';
    foreach ($listLinkNews as $value) {
      $sitemap .= "<url>";
      $sitemap .= '<loc>' . HelperBase::makeNewsUrl($value) . '</loc>';
      $sitemap .= '<changefreq>daily</changefreq>';
      $sitemap .= '<priority>0.5</priority>';
      $sitemap .= '</url>';
    }
    $sitemap .= "</urlset>";
    $fp = fopen(Params::$sitemapNews, "w") or exit("Không Tìm Thấy File Cần Mở! ");
    fwrite($fp, $sitemap);
    fclose($fp);
    echo 'Successfully!';
  }

  // get all tags
  public static function getAllUrlTags()
  {
    set_time_limit(0);
    $listLinkTags = MTags::model()->findAll();
    $sitemapOld = file_get_contents(Params::$sitemapNews);
    $sitemap = str_replace('</urlset>', ' ', $sitemapOld);
    foreach ($listLinkTags as $value) {
      $sitemap .= '<url>';
      $sitemap .= '<loc>' . HelperBase::makeTagCategoryUrl($value) . '</loc>';
      $sitemap .= '<changefreq>daily</changefreq>';
      $sitemap .= '<priority>0.5</priority>';
      $sitemap .= '</url>';
    }
    $sitemap .= '</urlset>';
    $fp = fopen(Params::$sitemapNews, "w") or exit("Không Tìm Thấy File Cần Mở! ");
    fwrite($fp, $sitemap);
    fclose($fp);
    echo 'Successfully!';
  }

  // get all player
  public static function getAllUrlPlayer()
  {
    set_time_limit(0);
    $listLinkPlayer = MoPlayer::model()->findAll();
    $sitemap = '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:schemaLocation="http://www.sitemaps.org/schemas/sitemap/0.9 http://www.sitemaps.org/schemas/sitemap/0.9/sitemap.xsd">';
    foreach ($listLinkPlayer as $value) {
      $value->is_sitemap_update = 1;
      $value->save(false);
      $sitemap .= "<url>";
      $sitemap .= '<loc>' . Params::$urlDefault . Livecore_Helper::renderUrlPlayer(array('player_id' => $value['player_id'], 'player_name' => isset($value['first_name']) ? $value['first_name'] . ' ' . $value['last_name'] : $value['player'])) . '</loc>';
      $sitemap .= '<changefreq>daily</changefreq>';
      $sitemap .= '<priority>0.5</priority>';
      $sitemap .= '</url>';
    }
    $sitemap .= "</urlset>";
    $fp = fopen(Params::$sitemapPlayer, "w") or exit("Không Tìm Thấy File Cần Mở! ");
    fwrite($fp, $sitemap);
    fclose($fp);
    echo 'Successfully!';
  }

  // get all season
  public static function getAllUrlSeason()
  {
    set_time_limit(0);
    $listLinkSeason = MoSeason::model()->findAll();
    $sitemap = '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:schemaLocation="http://www.sitemaps.org/schemas/sitemap/0.9 http://www.sitemaps.org/schemas/sitemap/0.9/sitemap.xsd">';
    foreach ($listLinkSeason as $season) {
      $season->is_sitemap_update = 1;
      $season->save(false);
      $sitemap .= "<url>";
      $sitemap .= '<loc>' . Params::$urlDefault . Livecore_Helper::renderUrlTable(array('season_id' => $season->code, 'season_name' => $season->name, 'competition' => $season->competition_id)) . '</loc>';
      $sitemap .= '<changefreq>daily</changefreq>';
      $sitemap .= '<priority>0.5</priority>';
      $sitemap .= '</url>';
    }
    $sitemap .= "</urlset>";
    $fp = fopen(Params::$sitemapSeason, "w") or exit("Không Tìm Thấy File Cần Mở! ");
    fwrite($fp, $sitemap);
    fclose($fp);
    echo 'Successfully!';
  }

  // get all competition
  public static function getAllUrlCompetition()
  {
    set_time_limit(0);
    $listLinkCompetition = MoCompetition::model()->findAll();
    $sitemap = '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:schemaLocation="http://www.sitemaps.org/schemas/sitemap/0.9 http://www.sitemaps.org/schemas/sitemap/0.9/sitemap.xsd">';
    foreach ($listLinkCompetition as $competition) {
      $competition->is_sitemap_update = 1;
      $competition->save(false);
      $sitemap .= "<url>";
      $sitemap .= '<loc>' . Params::$urlDefault . '/' . $competition->code . '.html' . '</loc>';
      $sitemap .= '<changefreq>daily</changefreq>';
      $sitemap .= '<priority>0.5</priority>';
      $sitemap .= '</url>';
    }
    $sitemap .= "</urlset>";
    $fp = fopen(Params::$sitemapCompetition, "w") or exit("Không Tìm Thấy File Cần Mở! ");
    fwrite($fp, $sitemap);
    fclose($fp);
    echo 'Successfully!';
  }

  // get all team
  public static function getAllUrlTeam()
  {
    set_time_limit(0);
    $listLinkTeam = MoTeam::model()->findAll();
    $sitemap = '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:schemaLocation="http://www.sitemaps.org/schemas/sitemap/0.9 http://www.sitemaps.org/schemas/sitemap/0.9/sitemap.xsd">';
    foreach ($listLinkTeam as $value) {
      //update is_sitemap_update => 1
      $value->is_sitemap_update = 1;
      $value->save(false);
      $sitemap .= "<url>";
      $sitemap .= '<loc>' . Params::$urlDefault . Livecore_Helper::renderUrlTeam(array('team_id' => $value->team_id, 'team_name' => $value->name)) . '</loc>';
      $sitemap .= '<changefreq>daily</changefreq>';
      $sitemap .= '<priority>0.5</priority>';
      $sitemap .= '</url>';
    }
    $sitemap .= "</urlset>";
    $fp = fopen(Params::$sitemapTeam, "w") or exit("Không Tìm Thấy File Cần Mở! ");
    fwrite($fp, $sitemap);
    fclose($fp);
    echo 'Successfully!';
  }

  // get all match
  public static function getAllUrlMatch()
  {
    set_time_limit(0);
    $listLinkMatch = MoMatch::model()->findAll();
    $sitemap = '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:schemaLocation="http://www.sitemaps.org/schemas/sitemap/0.9 http://www.sitemaps.org/schemas/sitemap/0.9/sitemap.xsd">';
    foreach ($listLinkMatch as $value) {
      $value->is_sitemap_update = 1;
      $value->save(false);
      $sitemap .= "<url>";
      $sitemap .= '<loc>' . Params::$urlDefault . Livecore_Helper::renderUrlMatch(array('match_id' => $value['match_id'], 'home' => $value['home'], 'away' => $value['away'], 'competition' => $value['competition_id'])) . '</loc>';
      $sitemap .= '<changefreq>daily</changefreq>';
      $sitemap .= '<priority>0.5</priority>';
      $sitemap .= '</url>';
    }
    $sitemap .= "</urlset>";
    $fp = fopen(Params::$sitemapMatch, "w") or exit("Không Tìm Thấy File Cần Mở! ");
    fwrite($fp, $sitemap);
    fclose($fp);
    echo 'Successfully!';
  }

  /* ----------------end sitemap ---------------- */

  public static function getListCompetitionHot($hot = 1)
  {
    $returnData = array();
    $criteria = array(
      '_condition' => array(
        'hot' => $hot
      ),
      '_sort' => array('sort' => 1),
      '_skip' => 0,
    );
    $_criteria = new EMongoCriteria($criteria);
    $returnData = MoCompetition::model()->findAll($_criteria);
    return $returnData;
  }

  //detail
  public static function getInfo($id = 0, $type = 0)
  {
    $returnData = array();
    //type=0: player,type=1 :team,type=3: competition
    if ($id != 0 && $type == 0) {

      $returnData = MoPlayer::model()->findOne(array('player_id' => (int) $id));
    }
    if ($id != 0 && $type == 1) {
      $returnData = MoTeam::model()->findOne(array('team_id' => (int) $id));
    }
    if ($id != '' && $type == 2) {

      $returnData = MoCompetition::model()->findOne(array('code' => $id));
    }
    return $returnData;
  }

  /* ----------------get meta title and meta description ---------------- */

  public static function getCategoryTitlebyNewsId($id)
  {
    $criteria = new CDbCriteria();
    $criteria->select = '*';
    $criteria->join = 'join m_news n on n.category_id =t.id';
    $criteria->condition = 'n.id =:id';
    $criteria->params = array(':id' => $id);
    $category = MCategories::model()->findAll($criteria);
    return $category;
  }

  /*------------------- SEND MAIL ----------------------------*/

  public static function SendMail($mailTo, $mailCC = '', $mailTitle, $mailMessage)
  {
    $error = array();
    try {
      $mail = new PHPMailer();
      $mail->isSMTP();
      $mail->Host = 'mail.muongthanh.vn';
      $mail->SMTPAuth = true;
      $mail->Username = Params::$usernameRootMail;
      $mail->Password = Params::$passwordRootMail;
      $mail->SMTPSecure = 'SSL';
      $mail->Port = 587;
      if ($mail->Password == '') {
        $error[] = array(
          'type' => 1,
          'message' => 'Bạn chưa cấu hình mật khẩu để gửi Mail. Xin vui lòng tiếp hành cấu hình mật khẩu gửi mail để tiếp tục thực hiện gửi Mail thông báo lịch công tác !'
        );
        MyHelper::resultJson($error);
      }
      $mail->setFrom(Params::$usernameRootMail, $mailTitle);

      $aryMailTo = explode(',', $mailTo);
      foreach ($aryMailTo as $_mail_to) {
        $mail->addAddress($_mail_to);
      }

      $aryMailCc = explode(',', $mailCC);
      foreach ($aryMailCc as $_mail_cc) {
        $mail->addCC($_mail_cc);
      }
      $mail->CharSet = 'utf-8';
      $mail->isHTML(true);
      $mail->Subject = $mailTitle;
      $mail->Body = $mailMessage;
      $mail->AltBody = $mailTitle;

      if (!$mail->send()) {
        $error[] = array(
          'type' => 1,
          'message' => 'Thực hiện gửi Mail thất bại. Xin vui lòng kiểm tra lại : ' . $mail->ErrorInfo
        );
        MyHelper::resultJson($error);
      } else {
        return 0;
      }
    } catch (Exception $e) {
      $error[] = array(
        'type' => 1,
        'message' => 'Thực hiện gửi Mail thất bại. Xin vui lòng kiểm tra lại !'
      );
      MyHelper::resultJson($error);
    }
  }

  //    Data Tree:
  public static function generateTree($data, $option)
  {
    $arrayOpt = array();
    foreach (array('class') as $name) {
      if (isset($option[$name]) && $option[$name] !== null) {
        $arrayOpt[$name] = $option[$name];
      }
    }
    $mainClass = (isset($arrayOpt['class']) && $arrayOpt['class'] != '') ? $arrayOpt['class'] : '';
    echo '<ul class="' . $mainClass . '">';
    foreach ($data as $node) {
      echo '<li class="menu-title">
                ' . $node['text'] . '
                <i class="zmdi zmdi-more"></i>
            </li>';
      if (count($node['children']) > 0) {
        foreach ($node['children'] as $childNode) {
          echo '<li>' . $childNode['text'];
          if (isset($childNode['children']) && count($childNode['children']) > 0) {
            echo '<ul class="nav-second-level" aria-expanded="false">';
            foreach ($childNode['children'] as $subChild) {
              echo '<li>' . $subChild['text'] . '</li>';
            }
            echo '</ul>';
          }
          echo '</li>';
        }
      }
    }
    echo '</ul>';
  }

  public static function generatButton($data = array())
  {
    if (count($data) > 0) : ?>
      <?php foreach ($data as $key => $value) : ?>
        <?php if ($value['id'] == 'btn_delete') : ?>
        <?php else : ?>
          <?php $html = '';

          if (isset($value['htmlOptions']) && $value['htmlOptions'] != null) {
            foreach ($value['htmlOptions'] as $option => $val) {

              $html .= ' ' . $option . '="' . $val . '" ';
            }
          }
          ?>
          <a class="label label-<?= $value['color'] ?> btn btn-sm waves-effect waves-light  btn-<?= $value['color'] ?> txt-light" title="" data-toggle="tooltip" href="<?= $value['url'] ?>" data-original-title="<?= $value['label'] ?>" <?= $html ?>><i class="fa <?= $value['icon'] ?>"></i>&nbsp;<?= $value['label'] ?></a>
        <?php endif; ?>

      <?php endforeach; ?>
<?php endif;
  }
}
