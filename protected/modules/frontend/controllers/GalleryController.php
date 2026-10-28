<?php

/**
 * Thư viện ảnh đại hội (public). Người tham dự duyệt album → xem/tải ảnh, và
 * "đánh dấu ảnh của tôi" sau khi nhận diện bằng QR trên thẻ (?token=<qr_token>).
 *
 * Xem/tải ảnh KHÔNG cần đăng nhập. Chỉ việc đánh dấu mới cần nhận diện QR.
 * Phiên nhận diện giữ trong session 8 giờ để không phải quét lại từng ảnh.
 *
 * Dữ liệu lấy qua Model (GalleryAlbums / GalleryPhotos) gọi External API.
 */
class GalleryController extends FrontEndController
{
    /** Thời hạn giữ phiên nhận diện QR (giây). */
    const CLAIMER_TTL = 28800; // 8 giờ

    public function init()
    {
        parent::init();
        $this->layout = 'daihoi';
    }

    public function accessRules()
    {
        return array(
            array('allow', 'actions' => array('index', 'album', 'photos', 'claim'), 'users' => array('*')),
            array('deny', 'users' => array('*')),
        );
    }

    /** Danh sách album. */
    public function actionIndex()
    {
        $this->identifyFromToken('/thu-vien-su-kien');
        $this->frontTitle = 'Thư viện ảnh';
        $event = $this->safeEvent();
        $eventId = isset($event['id']) ? $event['id'] : null;

        $this->render('index', array(
            'event'   => $event,
            'albums'  => GalleryAlbums::listPublic($eventId),
            'claimer' => $this->currentClaimer(),
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

        $this->identifyFromToken('/thu-vien-su-kien/' . rawurlencode($slug));

        $claimer = $this->currentClaimer();
        $attendeeId = $claimer ? $claimer['id'] : null;

        $this->frontTitle = $album['name'];
        $first = GalleryPhotos::listByAlbum($album['id'], 1, 24, $attendeeId);

        $this->render('album', array(
            'event'    => $event,
            'album'    => $album,
            'photos'   => $this->mapPhotoUrls($first['items']),
            'lastPage' => $first['last_page'],
            'total'    => $first['total'],
            'claimer'  => $claimer,
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

        $claimer = $this->currentClaimer();
        $data = GalleryPhotos::listByAlbum($albumId, $page, 24, $claimer ? $claimer['id'] : null);
        echo CJSON::encode(array(
            'success'   => true,
            'items'     => $this->mapPhotoUrls($data['items']),
            'page'      => $data['page'],
            'last_page' => $data['last_page'],
        ));
        Yii::app()->end();
    }

    /** AJAX POST: đánh dấu / bỏ đánh dấu ảnh của tôi. Yêu cầu đã nhận diện QR. */
    public function actionClaim()
    {
        header('Content-Type: application/json');

        if (!Yii::app()->request->isPostRequest) {
            $this->jsonEnd(array('success' => false, 'message' => 'Yêu cầu không hợp lệ.'));
        }

        $claimer = $this->currentClaimer();
        if (!$claimer) {
            $this->jsonEnd(array('success' => false, 'message' => 'Vui lòng quét QR trên thẻ của bạn để đánh dấu ảnh.', 'need_identify' => true));
        }

        $photoId = (int) Yii::app()->request->getParam('photo_id');
        $action  = Yii::app()->request->getParam('action', 'claim');
        if ($photoId <= 0) {
            $this->jsonEnd(array('success' => false, 'message' => 'Thiếu thông tin ảnh.'));
        }

        $photo = GalleryPhotos::toggleClaim($photoId, $claimer['id'], $claimer['name'], $action !== 'unclaim');
        if ($photo === null) {
            $this->jsonEnd(array('success' => false, 'message' => 'Không thực hiện được. Vui lòng thử lại.'));
        }

        $this->jsonEnd(array(
            'success'       => true,
            'mine'          => !empty($photo['mine']),
            'claim_count'   => isset($photo['claim_count']) ? (int) $photo['claim_count'] : 0,
            'claimer_names' => isset($photo['claimer_names']) ? $photo['claimer_names'] : array(),
        ));
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

    /**
     * Nếu URL có ?token=<qr_token>, nhận diện người tham dự, lưu phiên rồi
     * redirect về URL sạch (bỏ token khỏi thanh địa chỉ).
     */
    private function identifyFromToken($cleanPath)
    {
        $token = trim((string) Yii::app()->request->getParam('token', ''));
        if ($token === '') {
            return;
        }
        $attendee = GalleryPhotos::resolveAttendee($token);
        if ($attendee) {
            Yii::app()->session['gallery_claimer'] = array(
                'id'   => (int) $attendee['attendee_id'],
                'name' => isset($attendee['full_name']) ? $attendee['full_name'] : '',
                'exp'  => time() + self::CLAIMER_TTL,
            );
        } else {
            Yii::app()->user->setFlash('error', 'Không nhận diện được thẻ của bạn. Vui lòng thử lại.');
        }
        $this->redirect(Yii::app()->request->baseUrl . $cleanPath);
    }

    /** Phiên nhận diện hiện tại (còn hạn) hoặc null. */
    private function currentClaimer()
    {
        $c = Yii::app()->session['gallery_claimer'];
        if (is_array($c) && isset($c['exp']) && $c['exp'] > time() && !empty($c['id'])) {
            return $c;
        }
        return null;
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

    private function jsonEnd($data)
    {
        echo CJSON::encode($data);
        Yii::app()->end();
    }
}
