<?php
$this->pageTitle = 'Đăng ký chạy - Kết quả';
?>
<div class="row justify-content-center mt-4">
    <div class="col-md-6 col-sm-9">
        <div class="card shadow-sm text-center">
            <div class="card-body p-4">
                <div class="mb-3">
                    <i class="fa fa-check-circle text-success" style="font-size:56px;"></i>
                </div>
                <h4 class="mb-1">Bạn đã đăng ký thành công!</h4>
                <p class="text-muted"><?php echo CHtml::encode($fullName); ?></p>

                <table class="table table-bordered mt-3">
                    <tr>
                        <th style="width:40%;background:#f8f9fa;">Nội dung</th>
                        <td><?php echo CHtml::encode(isset($mine['run_event_name']) ? $mine['run_event_name'] : ''); ?></td>
                    </tr>
                    <tr>
                        <th style="background:#f8f9fa;">Số BIB</th>
                        <td><span class="badge bg-primary fs-5"><?php echo CHtml::encode(isset($mine['bib_number']) ? $mine['bib_number'] : ''); ?></span></td>
                    </tr>
                </table>

                <div class="alert alert-info mt-3 mb-0">Mỗi người chỉ được đăng ký 1 nội dung. Vui lòng lưu lại số BIB của bạn.</div>

                <a href="<?php echo $this->createUrl('/frontend/run/logout'); ?>" class="btn btn-outline-secondary mt-3">
                    <i class="fa fa-sign-out"></i> Thoát
                </a>
            </div>
        </div>
    </div>
</div>
