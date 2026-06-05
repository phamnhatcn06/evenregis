<?php
$this->breadcrumbs = array(
    'Thí sinh Miss',
    'Quản lý',
);

$this->menu = array(
    array(
        'label' => 'Thêm thí sinh',
        'url' => $this->createUrl('create'),
        'color' => 'primary',
        'icon' => 'fa-plus',
        'id' => 'btn_create',
    ),
);
$this->Tabletitle = 'Danh sách thí sinh thi Miss';
?>

<div class="card">
    <div class="card-body">
        <?php
        $this->widget('ext.edatatables.EDataTables', array(
            'id' => 'beauty-contestants-grid',
            'dataProvider' => $dataProvider,
            'language' => 'vi',
            'filter' => true,
            'columns' => array(
                array('name' => 'id', 'header' => 'ID', 'width' => '60px', 'filter' => false),
                array(
                    'name' => 'contest_id',
                    'header' => 'Cuộc thi',
                    'type' => 'raw',
                    'filter' => $contests,
                    'value' => function ($data) {
                        return isset($data->contest_name) ? CHtml::encode($data->contest_name) : $data->contest_id;
                    }
                ),
                array('name' => 'candidate_number', 'header' => 'SBD', 'width' => '80px'),
                array(
                    'name' => 'attendee_id',
                    'header' => 'Thí sinh',
                    'type' => 'raw',
                    'value' => function ($data) {
                        if (isset($data->members) && !empty($data->members)) {
                            return CHtml::encode($data->members[0]['attendee_name']);
                        }
                        return $data->attendee_id;
                    }
                ),
                array(
                    'name' => 'status',
                    'header' => 'Trạng thái',
                    'width' => '120px',
                    'type' => 'raw',
                    'filter' => BeautyContestants::getStatusOptions(),
                    'value' => function ($data) {
                        return BeautyContestants::getStatusLabel($data->status);
                    }
                ),
            ),
            'options' => array(
                'pageLength' => 25,
            ),
        ));
        ?>
    </div>
</div>