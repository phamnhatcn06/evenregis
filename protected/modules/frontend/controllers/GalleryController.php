<?php

/**
 * Thư viện ảnh đại hội (public). Người tham dự duyệt album → xem/tải ảnh.
 * Việc "đánh dấu ảnh của tôi" (qua quét QR) sẽ bổ sung ở Slice 3.
 *
 * Dữ liệu lấy qua Model (GalleryAlbums / GalleryPhotos) gọi External API.
 */
class GalleryController extends FrontEndController
{
    public function init()
    {
        parent::init();
        $this->layout = 'daihoi';
    }

    public function accessRules()
    {
        return array(
            array('allow', 'actions' => array('index', 'album', 'photos'), 'users' => array('*')),
            array('deny', 'users' => array('*')),
        );
    }

    /** Danh sách album. */
    public function actionIndex()
    {
        $this->frontTitle = 'Thư viện ảnh';
        $event = $this->safeEvent();
        $eventId = isset($event['id']) ? $event['id'] : null;

        $this->render('index', array(
            'event'  => $event,
            'albums' => GalleryAlbums::listPublic($eventId),
        ));
    }

    /** Xem 1 album (lưới ảnh + lightbox). Trang đầu render sẵn, còn lại lazy-load. */
    public function actionAlbum($slug)
    {
        $event = $this->safeEvent();
        $eventId = isset($event['id']) ? $event['id'] : null;

        $album = GalleryAlbums::findBySlug($slug, $eventId);
        if ($album === null) {
            throw new CHttpException(404, 'Không tìm thấy album ảnh.');
        }

        $this->frontTitle = $album['name'];
        $first = GalleryPhotos::listByAlbum($album['id'], 1, 24);

        $this->render('album', array(
            'event'    => $event,
            'album'    => $album,
            'photos'   => $this->mapPhotoUrls($first['items']),
            'lastPage' => $first['last_page'],
            'total'    => $first['total'],
        ));
    }

    /** AJAX: trả thêm 1 trang ảnh (JSON) cho infinite scroll. */
    public function actionPhotos()
    {
        header('Content-Type: application/json');
        $albumId = (int) Yii::app()->request->getParam('album_id');
        $page    = max(1, (int) Yii::app()->request->getParam('page', 1));

        if ($albumId <= 0) {
            echo CJSON::encode(array('success' => false, 'items' => array(), 'last_page' => 1));
            Yii::app()->end();
        }

        $data = GalleryPhotos::listByAlbum($albumId, $page, 24);
        echo CJSON::encode(array(
            'success'   => true,
            'items'     => $this->mapPhotoUrls($data['items']),
            'page'      => $data['page'],
            'last_page' => $data['last_page'],
        ));
        Yii::app()->end();
    }

    /** Gắn URL ảnh đầy đủ (thumb/web/original) vào từng bản ghi để view/JS dùng trực tiếp. */
    private function mapPhotoUrls($items)
    {
        $out = array();
        foreach ($items as $p) {
            $p['thumb_url']    = GalleryAlbums::mediaUrl(isset($p['thumb_path']) ? $p['thumb_path'] : '');
            $p['web_url']      = GalleryAlbums::mediaUrl(isset($p['file_path']) ? $p['file_path'] : '');
            $p['original_url'] = GalleryAlbums::mediaUrl(isset($p['original_path']) ? $p['original_path'] : '');
            $out[] = $p;
        }
        return $out;
    }

    /** Lấy sự kiện hiện tại an toàn (không để lỗi API làm hỏng trang). */
    private function safeEvent()
    {
        try {
            return Daihoi::getEvent();
        } catch (Exception $e) {
            return array();
        }
    }
}
