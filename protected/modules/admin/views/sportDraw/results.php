<?php
/**
 * Màn hình ghi kết quả từng trận. Sau khi lưu, backend tự đẩy đội thắng vòng sau.
 *
 * @var SportDrawController $this
 * @var SportStages $model
 * @var array|null $preview
 */

$this->breadcrumbs = array(
    'Bốc thăm thi đấu' => $this->createUrl('index'),
    $model->name => $this->createUrl('view', array('id' => $model->id)),
    'Ghi kết quả',
);
$this->Tabletitle = 'Ghi kết quả: ' . $model->name;

$this->menu = array(
    array(
        'label' => 'Quay lại',
        'url' => $this->createUrl('index', array('event_id' => $model->event_id, 'sport_id' => $model->sport_id)),
        'color' => 'secondary',
        'icon' => 'fa-arrow-left',
        'id' => 'btn_back',
    ),
    array(
        'label' => 'Xem sơ đồ cây',
        'url' => $this->createUrl('view', array('id' => $model->id)),
        'color' => 'info',
        'icon' => 'fa-sitemap',
        'id' => 'btn_view_tree',
    ),
);

Yii::app()->clientScript->registerCssFile(
    Yii::app()->theme->baseUrl . '/assets/css/pages/sportdraw-bracket.css?v=1.2'
);
Yii::app()->clientScript->registerScriptFile(
    Yii::app()->theme->baseUrl . '/assets/js/plugins/toast.js',
    CClientScript::POS_END
);
Yii::app()->clientScript->registerScriptFile(
    Yii::app()->theme->baseUrl . '/assets/js/pages/sportdraw-results.js?v=1.1',
    CClientScript::POS_END
);

?>

<div id="results-board"
     data-save-url="<?php echo $this->createUrl('saveResult'); ?>"
     data-reload-url="<?php echo $this->createUrl('results', array('id' => $model->id)); ?>">
    <?php $this->renderPartial('_board', array('model' => $model, 'preview' => $preview)); ?>
</div>
