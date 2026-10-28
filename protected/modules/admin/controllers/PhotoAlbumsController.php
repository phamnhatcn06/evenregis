<?php

/**
 * Quản trị Thư viện ảnh đại hội: quản lý album (đổi tên, ẩn/hiện, thứ tự, xoá),
 * quản lý ảnh trong album (đặt ảnh bìa, xoá ảnh, xem ai đã đánh dấu).
 *
 * Dữ liệu qua Model Gallery* gọi External API. Admin HO truy cập (quyền '*').
 */
class PhotoAlbumsController extends AdminController
{
    /** Danh sách album. */
    public function actionIndex()
    {
        $eventId = Yii::app()->request->getParam('event_id');
        $albums = GalleryAlbums::adminList($eventId !== '' ? $eventId : null);
        $this->render('index', array('albums' => $albums));
    }

    /** Cập nhật album (AJAX từ modal). */
    public function actionUpdate($id)
    {
        header('Content-Type: application/json');
        if (!Yii::app()->request->isPostRequest) {
            $this->jsonEnd(array('success' => false, 'message' => 'Yêu cầu không hợp lệ.'));
        }

        $data = array(
            'name'       => trim((string) Yii::app()->request->getParam('name')),
            'is_public'  => (int) Yii::app()->request->getParam('is_public', 1),
            'sort_order' => (int) Yii::app()->request->getParam('sort_order', 0),
        );
        if ($data['name'] === '') {
            $this->jsonEnd(array('success' => false, 'message' => 'Vui lòng nhập tên album.'));
        }

        $result = GalleryAlbums::updateViaApi($id, $data);
        $this->jsonEnd(array(
            'success' => !empty($result['success']),
            'message' => !empty($result['success']) ? 'Đã cập nhật album.' : 'Cập nhật thất bại.',
        ));
    }

    /** Xoá mềm album (POST từ SweetAlert). */
    public function actionDelete($id)
    {
        if (!Yii::app()->request->isPostRequest) {
            throw new CHttpException(400, 'Yêu cầu không hợp lệ.');
        }
        $result = GalleryAlbums::deleteViaApi($id);
        if (!empty($result['success'])) {
            Yii::app()->user->setFlash('success', 'Đã xoá album.');
        } else {
            Yii::app()->user->setFlash('error', 'Xoá album thất bại.');
        }
        $this->redirect(array('index'));
    }

    /** Trang quản lý ảnh trong 1 album. */
    public function actionPhotos($id)
    {
        $albums = GalleryAlbums::adminList();
        $album = null;
        foreach ($albums as $a) {
            if ((int) $a['id'] === (int) $id) {
                $album = $a;
                break;
            }
        }
        if ($album === null) {
            throw new CHttpException(404, 'Không tìm thấy album.');
        }

        $data = GalleryPhotos::adminListByAlbum($id, 1, 200);
        $photos = array();
        foreach ($data['items'] as $p) {
            $p['thumb_url']    = GalleryAlbums::mediaUrl(isset($p['thumb_path']) ? $p['thumb_path'] : '');
            $p['web_url']      = GalleryAlbums::mediaUrl(isset($p['file_path']) ? $p['file_path'] : '');
            $p['original_url'] = GalleryAlbums::mediaUrl(isset($p['original_path']) ? $p['original_path'] : '');
            $photos[] = $p;
        }

        $this->render('photos', array('album' => $album, 'photos' => $photos));
    }

    /** Đặt ảnh làm ảnh bìa (AJAX). */
    public function actionSetCover($id)
    {
        header('Content-Type: application/json');
        $result = GalleryPhotos::setCover($id);
        $this->jsonEnd(array('success' => !empty($result['success'])));
    }

    /** Xoá mềm 1 ảnh (AJAX). */
    public function actionDeletePhoto($id)
    {
        header('Content-Type: application/json');
        if (!Yii::app()->request->isPostRequest) {
            $this->jsonEnd(array('success' => false, 'message' => 'Yêu cầu không hợp lệ.'));
        }
        $result = GalleryPhotos::deleteViaApi($id);
        $this->jsonEnd(array(
            'success' => !empty($result['success']),
            'message' => !empty($result['success']) ? 'Đã xoá ảnh.' : 'Xoá ảnh thất bại.',
        ));
    }

    private function jsonEnd($data)
    {
        echo CJSON::encode($data);
        Yii::app()->end();
    }
}
