<?php
$user = AuthHandler::getUser();
$userPropertyCode = isset($user['property_code']) ? $user['property_code'] : '';
$isHO = ($userPropertyCode === '9999' || $userPropertyCode === 9999);

$eventList = array();
foreach ($events as $e) {
    $eId = isset($e['id']) ? $e['id'] : (isset($e->id) ? $e->id : null);
    $eName = isset($e['name']) ? $e['name'] : (isset($e->name) ? $e->name : '');
    if ($eId) {
        $eventList[$eId] = $eName;
    }
}

$propertyList = array();
foreach ($properties as $p) {
    $pId = isset($p['id']) ? $p['id'] : (isset($p->id) ? $p->id : null);
    $pCode = isset($p['code']) ? $p['code'] : (isset($p->code) ? $p->code : '');
    $pName = isset($p['name']) ? $p['name'] : (isset($p->name) ? $p->name : '');
    if ($pId) {
        $propertyList[$pId] = "{$pCode} - {$pName}";
    }
}
asort($propertyList);

$relationPropertyList = array();
if (isset($relationProperties)) {
    foreach ($relationProperties as $p) {
        $pId = isset($p['id']) ? $p['id'] : (isset($p->id) ? $p->id : null);
        $pCode = isset($p['code']) ? $p['code'] : (isset($p->code) ? $p->code : '');
        $pName = isset($p['name']) ? $p['name'] : (isset($p->name) ? $p->name : '');
        if ($pId && $pId != $model->property_id) {
            $relationPropertyList[$pId] = "{$pCode} - {$pName}";
        }
    }
    asort($relationPropertyList);
}
?>

<div class="form-wrap">
    <?php $form = $this->beginWidget('CActiveForm', array(
        'id' => 'registrations-form',
        'htmlOptions' => array('enctype' => 'multipart/form-data'),
        'enableClientValidation' => false,
    )); ?>

    <?php echo $form->errorSummary($model); ?>

    <div class="row">
        <div class="col-md-6">
            <div class="form-group mb-3">
                <?php echo $form->labelEx($model, 'event_id'); ?>
                <?php echo $form->dropDownList($model, 'event_id', $eventList, array(
                    'class' => 'form-select',
                    'prompt' => '-- Chọn sự kiện --',
                    'id' => 'event-select',
                )); ?>
                <?php echo $form->error($model, 'event_id'); ?>
            </div>
        </div>
        <div class="col-md-6">
            <div class="form-group mb-3">
                <?php echo $form->labelEx($model, 'period_id'); ?>
                <?php
                $periodPrompt = $model->event_id ? '-- Chọn đợt đăng ký --' : '-- Chọn sự kiện trước --';
                ?>
                <?php echo $form->dropDownList($model, 'period_id', $periods, array(
                    'class' => 'form-select',
                    'prompt' => $periodPrompt,
                    'id' => 'period-select',
                )); ?>
                <?php echo $form->error($model, 'period_id'); ?>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-6">
            <div class="form-group mb-3">
                <?php echo $form->labelEx($model, 'property_id'); ?>
                <?php echo $form->dropDownList($model, 'property_id', $propertyList, array(
                    'class' => 'form-select',
                    'prompt' => '-- Chọn đơn vị --',
                    'disabled' => !$isHO && count($propertyList) == 1,
                )); ?>
                <?php if (!$isHO && count($propertyList) == 1): ?>
                    <input type="hidden" name="Registrations[property_id]" value="<?php echo $model->property_id; ?>">
                <?php endif; ?>
                <?php echo $form->error($model, 'property_id'); ?>
            </div>
        </div>
        <div class="col-md-6">
            <div class="form-group mb-3">
                <?php echo $form->labelEx($model, 'Đơn vị liên quân (nếu có)'); ?>
                <?php echo $form->dropDownList($model, 'relation_property_id', $relationPropertyList, array(
                    'class' => 'form-select',
                    'prompt' => '-- Không có (liên quân) --',
                )); ?>
                <?php echo $form->error($model, 'relation_property_id'); ?>
                <small class="text-muted">Chọn đơn vị liên quân trong cùng khu vực. Cần xác nhận từ đơn vị được chọn mới được duyệt</small>
            </div>
        </div>
    </div>

    <div class="form-group mb-3">
        <?php echo $form->labelEx($model, 'document'); ?>
        <div class="upload-area border rounded p-3 text-center mb-2" id="uploadArea" style="border-style: dashed !important; cursor: pointer;">
            <i class="fa fa-cloud-upload fa-2x text-muted mb-2"></i>
            <p class="mb-1">Kéo thả file vào đây hoặc <span class="text-primary">chọn file</span></p>
            <small class="text-muted">PDF, DOC, DOCX, JPG, PNG (tối đa 5MB mỗi file)</small>
        </div>
        <input type="file" name="document_files[]" id="documentFiles" class="d-none" accept=".pdf,.doc,.docx,.jpg,.jpeg,.png" multiple>
        <div id="filePreview" class="row g-2 mt-2"></div>
        <?php echo $form->hiddenField($model, 'document', array('id' => 'documentJson')); ?>
        <?php echo $form->error($model, 'document'); ?>
    </div>

    <div class="form-group mb-3">
        <?php echo $form->labelEx($model, 'note'); ?>
        <?php echo $form->textArea($model, 'note', array(
            'class' => 'form-control',
            'rows' => 3,
            'placeholder' => 'Ghi chú thêm',
        )); ?>
        <?php echo $form->error($model, 'note'); ?>
    </div>

    <hr />
    <div class="footer-action">
        <button id="btn-submit" type="submit" class="btn btn-sm btn-primary">
            <i class="fa fa-save me-1"></i>Lưu lại
        </button>
        <a href="<?php echo $this->createUrl('admin'); ?>" class="btn btn-sm btn-secondary">
            <i class="fa fa-arrow-left me-1"></i>Quay lại
        </a>
    </div>

    <?php $this->endWidget(); ?>
</div>

<?php
Yii::app()->clientScript->registerScriptFile(
    Yii::app()->theme->baseUrl . '/assets/js/pages/registrations-form.js',
    CClientScript::POS_END
);
?>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        var eventSelect = document.getElementById('event-select');
        var periodSelect = document.getElementById('period-select');
        var periodApiUrl = '<?php echo Yii::app()->params['externalApiUrl']; ?>/api/registration-periods/list-active';
        var apiKey = '<?php echo Yii::app()->params['externalApiKey']; ?>';

        eventSelect.addEventListener('change', function() {
            var eventId = this.value;
            periodSelect.innerHTML = '<option value="">-- Đang tải... --</option>';

            if (!eventId) {
                periodSelect.innerHTML = '<option value="">-- Chọn sự kiện trước --</option>';
                return;
            }

            fetch(periodApiUrl + '?event_id=' + eventId, {
                headers: {
                    'Authorization': 'Bearer ' + apiKey,
                    'Accept': 'application/json'
                }
            })
            .then(function(response) { return response.json(); })
            .then(function(data) {
                periodSelect.innerHTML = '<option value="">-- Chọn đợt đăng ký --</option>';
                var items = data.data || data;
                if (Array.isArray(items) && items.length > 0) {
                    items.forEach(function(p) {
                        var option = document.createElement('option');
                        option.value = p.id;
                        option.textContent = p.name;
                        periodSelect.appendChild(option);
                    });
                }
            })
            .catch(function() {
                periodSelect.innerHTML = '<option value="">-- Lỗi tải dữ liệu --</option>';
            });
        });
    });
</script>

<?php if ($isHO): ?>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            var propertySelect = document.getElementById('Registrations_property_id');
            var relationSelect = document.getElementById('Registrations_relation_property_id');
            var ajaxUrl = '<?php echo $this->createUrl("getRelationProperties"); ?>';

            propertySelect.addEventListener('change', function() {
                var propertyId = this.value;
                relationSelect.innerHTML = '<option value="">-- Đang tải... --</option>';

                if (!propertyId) {
                    relationSelect.innerHTML = '<option value="">-- Chọn đơn vị trước --</option>';
                    return;
                }

                var separator = ajaxUrl.indexOf('?') > -1 ? '&' : '?';
                fetch(ajaxUrl + separator + 'property_id=' + propertyId)
                    .then(function(response) {
                        return response.json();
                    })
                    .then(function(data) {
                        relationSelect.innerHTML = '<option value="">-- Không có (liên quân) --</option>';
                        if (data.success && data.data && data.data.length > 0) {
                            data.data.forEach(function(p) {
                                var option = document.createElement('option');
                                option.value = p.id;
                                option.textContent = p.code + ' - ' + p.name;
                                relationSelect.appendChild(option);
                            });
                        }
                    })
                    .catch(function() {
                        relationSelect.innerHTML = '<option value="">-- Lỗi tải dữ liệu --</option>';
                    });
            });

            if (!propertySelect.value && relationSelect.options.length <= 1) {
                relationSelect.innerHTML = '<option value="">-- Chọn đơn vị trước --</option>';
            }
        });
    </script>
<?php endif; ?>