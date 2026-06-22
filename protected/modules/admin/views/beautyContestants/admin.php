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
    array(
        'label' => 'Gửi email hàng loạt',
        'url' => '#',
        'color' => 'warning',
        'icon' => 'fa-envelope',
        'id' => 'btn_send_bulk_email',
    ),
    array(
        'label' => 'Xuất Excel',
        'url' => '#',
        'color' => 'success',
        'icon' => 'fa-file-excel-o',
        'id' => 'btn_export_excel',
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
                    'header' => 'Sự kiện',
                    'type' => 'raw',
                    'filter' => false,
                    'value' => function ($data) {
                        if (!empty($data->event_name)) {
                            return CHtml::encode($data->event_name);
                        }
                        if (isset($data->contest) && isset($data->contest->event)) {
                            return CHtml::encode($data->contest->event->name);
                        }
                        return '';
                    }
                ),
                array(
                    'name' => 'contest_id',
                    'header' => 'Cuộc thi',
                    'type' => 'raw',
                    'filter' => $contests,
                    'value' => function ($data) {
                        if (isset($data->contest)) {
                            return CHtml::encode($data->contest->name);
                        }
                        return isset($data->contest_name) ? CHtml::encode($data->contest_name) : $data->contest_id;
                    }
                ),
                array(
                    'header' => 'Đơn vị',
                    'type' => 'raw',
                    'filter' => false,
                    'value' => function ($data) {
                        $unitName = '';
                        if (!empty($data->registration_id)) {
                            $unitName = BeautyContestants::getPropertyNameByRegistrationId($data->registration_id);
                        }
                        if (empty($unitName) && !empty($data->property_name)) {
                            $unitName = $data->property_name;
                        }
                        if (empty($unitName) && isset($data->attendee)) {
                            if (isset($data->attendee->property)) {
                                $unitName = $data->attendee->property->name;
                            } elseif (!empty($data->attendee->unit_label)) {
                                $unitName = $data->attendee->unit_label;
                            }
                        }
                        return CHtml::encode($unitName);
                    }
                ),
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
                array(
                    'header' => 'Hồ sơ',
                    'type' => 'raw',
                    'width' => '100px',
                    'filter' => false,
                    'value' => function ($data) {
                        if (!empty($data->submitted_at)) {
                            return '<span class="badge bg-success">Đã gửi</span>';
                        }
                        return '<span class="badge bg-secondary">Chưa gửi</span>';
                    }
                ),
                array(
                    'header' => 'Thao tác',
                    'type' => 'raw',
                    'width' => '150px',
                    'filter' => false,
                    'value' => function ($data) {
                        $buttons = '';
                        $buttons .= CHtml::link('<i class="fa fa-eye"></i>', array('view', 'id' => $data->id), array('class' => 'btn btn-sm btn-info me-1', 'title' => 'Xem'));
                        if (empty($data->submitted_at)) {
                            $buttons .= '<button type="button" class="btn btn-sm btn-warning btn-send-email" data-id="' . $data->id . '" data-name="' . CHtml::encode($data->attendee_name) . '" title="Gửi email"><i class="fa fa-envelope"></i></button>';
                        }
                        return $buttons;
                    }
                ),
            ),
            'options' => array(
                'pageLength' => 25,
                'responsive' => true,
                'scrollX' => true,
            ),
        ));
        ?>
    </div>
</div>

<?php
Yii::app()->clientScript->registerScript('beauty-contestants-scripts', "
    // Prevent filter clicks from triggering column sorting
    $('#beauty-contestants-grid thead').on('click', 'input, select', function(e) {
        e.stopPropagation();
    });

    $('#btn_export_excel').click(function(e) {
        e.preventDefault();
        var baseUrl = '" . $this->createUrl('export') . "';

        var filters = {};
        $('#beauty-contestants-grid thead input, #beauty-contestants-grid thead select').each(function() {
            var name = $(this).attr('name');
            var val = $(this).val();
            if (name && val !== '' && val !== null) {
                filters[name] = val;
            }
        });

        var queryString = $.param(filters);
        var exportUrl = baseUrl;
        if (queryString) {
            exportUrl += (baseUrl.indexOf('?') >= 0 ? '&' : '?') + queryString;
        }

        window.location.href = exportUrl;
    });

    // Gửi email cho từng thí sinh
    $(document).on('click', '.btn-send-email', function() {
        var id = $(this).data('id');
        var name = $(this).data('name');
        var btn = $(this);

        Swal.fire({
            title: 'Gửi email mời',
            text: 'Gửi email mời nộp hồ sơ đến thí sinh \"' + name + '\"?',
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Gửi email',
            cancelButtonText: 'Hủy'
        }).then(function(result) {
            if (result.isConfirmed) {
                btn.prop('disabled', true).html('<i class=\"fa fa-spinner fa-spin\"></i>');

                $.ajax({
                    url: '" . $this->createUrl('sendInviteEmail') . "',
                    type: 'POST',
                    data: { id: id },
                    dataType: 'json',
                    success: function(response) {
                        if (response.success) {
                            Toast.success('Đã gửi email thành công');
                            btn.removeClass('btn-warning').addClass('btn-secondary').html('<i class=\"fa fa-check\"></i>');
                        } else {
                            Toast.error(response.message || 'Có lỗi xảy ra');
                            btn.prop('disabled', false).html('<i class=\"fa fa-envelope\"></i>');
                        }
                    },
                    error: function() {
                        Toast.error('Lỗi kết nối server');
                        btn.prop('disabled', false).html('<i class=\"fa fa-envelope\"></i>');
                    }
                });
            }
        });
    });

    // Gửi email hàng loạt
    $('#btn_send_bulk_email').click(function(e) {
        e.preventDefault();

        Swal.fire({
            title: 'Gửi email hàng loạt',
            text: 'Gửi email mời nộp hồ sơ đến TẤT CẢ thí sinh chưa gửi hồ sơ?',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#f0ad4e',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Gửi tất cả',
            cancelButtonText: 'Hủy'
        }).then(function(result) {
            if (result.isConfirmed) {
                Swal.fire({
                    title: 'Đang gửi email...',
                    text: 'Vui lòng đợi...',
                    allowOutsideClick: false,
                    didOpen: function() {
                        Swal.showLoading();
                    }
                });

                $.ajax({
                    url: '" . $this->createUrl('sendBulkInviteEmail') . "',
                    type: 'POST',
                    dataType: 'json',
                    success: function(response) {
                        Swal.close();
                        if (response.success) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Hoàn tất',
                                html: 'Đã gửi: <strong>' + response.sent + '</strong> email<br>Lỗi: <strong>' + response.failed + '</strong>',
                            }).then(function() {
                                location.reload();
                            });
                        } else {
                            Toast.error(response.message || 'Có lỗi xảy ra');
                        }
                    },
                    error: function() {
                        Swal.close();
                        Toast.error('Lỗi kết nối server');
                    }
                });
            }
        });
    });
", CClientScript::POS_READY);
?>