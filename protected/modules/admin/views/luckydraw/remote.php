<?php
/**
 * Bảng điều khiển quay số từ xa (toàn màn hình). Gửi lệnh 'spin' qua apiRemoteSpin;
 * màn quay số (actionShow) poll checkRemote mỗi giây và tự quay.
 *
 * @var LuckyDrawController $this
 * @var array $prize Giải hiện tại (hoặc null)
 */
$asset = Yii::app()->theme->baseUrl . '/assets/luckydraw';
$prizeName = ($prize && isset($prize['prize_name'])) ? $prize['prize_name'] : '—';
?>
<!doctype html>
<html lang="vi">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Điều khiển quay số</title>
    <link rel="stylesheet" href="<?php echo $asset; ?>/css/lato.css">
    <style>
        body { margin:0; min-height:100vh; display:flex; flex-direction:column; align-items:center; justify-content:center;
               font-family:'Lato',sans-serif; background:#0b1020; color:#fff; text-align:center; padding:24px; }
        .prize { font-size:28px; font-weight:700; margin-bottom:8px; }
        .label { opacity:.7; margin-bottom:28px; }
        .btn-spin { width:240px; height:240px; border-radius:50%; border:none; cursor:pointer;
                    font-size:36px; font-weight:800; color:#1b1b1b;
                    background:radial-gradient(circle at 30% 30%, #ffe14d, #f5a623);
                    box-shadow:0 12px 40px rgba(245,166,35,.5); transition:transform .1s; }
        .btn-spin:active { transform:scale(.96); }
        .btn-spin:disabled { opacity:.5; cursor:not-allowed; }
        .msg { margin-top:24px; min-height:24px; font-size:18px; }
        .msg.err { color:#ff8080; } .msg.ok { color:#7CFC00; }
    </style>
</head>
<body>
    <div class="label">Giải đang quay</div>
    <div class="prize" id="prizeName"><?php echo CHtml::encode($prizeName); ?></div>
    <button class="btn-spin" id="btnSpin">QUAY</button>
    <div class="msg" id="msg"></div>

    <script>
        window.__REMOTE = {
            spin:  "<?php echo $this->createUrl('apiRemoteSpin'); ?>",
            prize: "<?php echo $this->createUrl('apiPrize'); ?>"
        };
    </script>
    <script src="<?php echo $asset; ?>/js/jquery-3.6.0.min.js"></script>
    <script src="<?php echo Yii::app()->theme->baseUrl; ?>/assets/js/pages/luckydraw-remote.js?v=1.0"></script>
</body>
</html>
