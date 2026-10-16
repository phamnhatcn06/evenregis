<?php
/**
 * Màn quay số toàn màn hình (port từ tool Lucky Draw). KHÔNG header/footer.
 * Giữ nguyên DOM + hiệu ứng; chỉ đổi đường dẫn asset sang theme và __API về controller.
 *
 * @var array $prize        Giải hiện tại (hoặc null)
 * @var array $winnerList   Người đã trúng (confirm=1) của giải hiện tại
 * @var string $backgroundPath
 * @var string $eventTitle
 */
$asset = Yii::app()->theme->baseUrl . '/assets/luckydraw';
$bg = $backgroundPath ? $backgroundPath : ($asset . '/images/background.jpg');
$quantity = ($prize && isset($prize['quantity'])) ? (int) $prize['quantity'] : 0;
$hasWinners = count($winnerList) > 0;
?>
<!doctype html>
<html lang="vi">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width,initial-scale=1" />
    <title><?php echo CHtml::encode($eventTitle); ?></title>
    <link href="<?php echo $asset; ?>/css/lato.css" rel="stylesheet">
    <link rel="stylesheet" href="<?php echo $asset; ?>/css/lucky.css" />
</head>

<body>
    <style>
        body {
            background: url("<?php echo CHtml::encode($bg); ?>") no-repeat;
            background-size: cover;
            background-position: center;
        }
    </style>
    <canvas id="fxCanvas" class="fx"></canvas>
    <canvas id="petalCanvas" class="fx"></canvas>
    <canvas id="boomCanvas" class="fx" style="z-index: 2000;"></canvas>

    <div id="lightSweep" class="light-sweep"></div>
    <div class="screen">
        <div class="spin-area">
            <div id="specialMsg" class="special-msg hidden">Ai sẽ là người may mắn nhất hôm nay......</div>
            <div class="board hidden" id="board">
                <div id="prizeName" class="prize">—</div>
                <div class="dice-wrap">
                    <div id="reelsWrap" class="reels-wrap hidden">
                        <div class="reel" id="reel1">
                            <div class="reel-strip">0 1 2 3 4 5 6 7 8 9 0 1 2 3 4 5 6 7 8 9 0 1 2 3 4 5 6 7 8 9 0 1 2 3 4 5 6 7 8 9 0 1 2 3 4 5 6 7 8 9 0 1 2 3 4 5 6 7 8 9 0 1 2 3 4 5 6 7 8 9 0 1 2 3 4 5 6 7 8 9</div>
                            <div class="reel-cover hidden" data-index="0"><span>?</span></div>
                        </div>
                        <div class="reel" id="reel2">
                            <div class="reel-strip">0 1 2 3 4 5 6 7 8 9 0 1 2 3 4 5 6 7 8 9 0 1 2 3 4 5 6 7 8 9 0 1 2 3 4 5 6 7 8 9 0 1 2 3 4 5 6 7 8 9 0 1 2 3 4 5 6 7 8 9 0 1 2 3 4 5 6 7 8 9 0 1 2 3 4 5 6 7 8 9</div>
                            <div class="reel-cover hidden" data-index="1"><span>?</span></div>
                        </div>
                        <div class="reel" id="reel3">
                            <div class="reel-strip">0 1 2 3 4 5 6 7 8 9 0 1 2 3 4 5 6 7 8 9 0 1 2 3 4 5 6 7 8 9 0 1 2 3 4 5 6 7 8 9 0 1 2 3 4 5 6 7 8 9 0 1 2 3 4 5 6 7 8 9 0 1 2 3 4 5 6 7 8 9 0 1 2 3 4 5 6 7 8 9</div>
                            <div class="reel-cover hidden" data-index="2"><span>?</span></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Người trúng giải duy nhất (quantity=1) -->
    <div id="winnerCenter" class="winner-center <?php echo ($quantity == 1 && $hasWinners) ? '' : 'hidden'; ?>">
        <ul id="winnerListCenter">
            <?php if ($quantity == 1): ?>
                <?php foreach ($winnerList as $w): ?>
                    <li>
                        <span class="center-content">
                            <span class="numberBlock"><?php echo CHtml::encode($w['code']); ?></span>
                            <div class="info-block">
                                <span class="partname"><?php echo CHtml::encode($w['full_name']); ?></span>
                                <span class="job"><?php echo CHtml::encode($w['department']); ?></span>
                            </div>
                        </span>
                        <button class="btn-delete" data-id="<?php echo $w['id']; ?>" data-prize="<?php echo $w['prize_id']; ?>" title="Huỷ người này">🗑</button>
                    </li>
                <?php endforeach; ?>
            <?php endif; ?>
        </ul>
    </div>

    <!-- Cánh gà trái -->
    <div id="winnersLeft" class="winners-side left <?php echo ($quantity > 1 && $hasWinners) ? '' : 'hidden'; ?>">
        <ul id="winnerListLeft">
            <?php foreach ($winnerList as $i => $w): ?>
                <?php if ($i % 2 === 0): ?>
                    <li>
                        <span class="numberBlock"><b><?php echo CHtml::encode($w['code']); ?></b></span>
                        <span class="partInfo">
                            <span class="partname"><?php echo CHtml::encode($w['full_name']); ?></span>
                            <span class="job"><?php echo CHtml::encode($w['department']); ?></span>
                        </span>
                        <button class="btn-delete" data-id="<?php echo $w['id']; ?>" data-prize="<?php echo $w['prize_id']; ?>" title="Huỷ người này">❌</button>
                    </li>
                <?php endif; ?>
            <?php endforeach; ?>
        </ul>
    </div>

    <!-- Cánh gà phải -->
    <div id="winnersRight" class="winners-side right <?php echo ($quantity > 1 && $hasWinners) ? '' : 'hidden'; ?>">
        <ul id="winnerListRight">
            <?php foreach ($winnerList as $i => $w): ?>
                <?php if ($i % 2 === 1): ?>
                    <li>
                        <span class="numberBlock"><b><?php echo CHtml::encode($w['code']); ?></b></span>
                        <span class="partInfo">
                            <span class="partname"><?php echo CHtml::encode($w['full_name']); ?></span>
                            <span class="job"><?php echo CHtml::encode($w['department']); ?></span>
                        </span>
                        <button class="btn-delete" data-id="<?php echo $w['id']; ?>" data-prize="<?php echo $w['prize_id']; ?>" title="Huỷ người này">❌</button>
                    </li>
                <?php endif; ?>
            <?php endforeach; ?>
        </ul>
    </div>

    <!-- Consolation Board -->
    <div id="consolationBoard" class="consolation-board hidden">
        <div id="consolationGrid" class="consolation-grid"></div>
        <div id="consolationActions" class="consolation-actions hidden">
            <button class="btn cancel" onclick="cancelWinner()">❌ Hủy</button>
            <button class="btn confirm" onclick="confirmWinner()">✅ Xác nhận</button>
        </div>
    </div>

    <div id="winnerOverlay" class="overlay hidden"></div>

    <div id="winnerPopup" class="winner-popup hidden">
        <div class="popup-header">🎉 XIN CHÚC MỪNG</div>
        <div class="popup-body">
            <div id="singleWinnerWrapper">
                <div id="bigCode" class="code">----</div>
                <div id="fullName" class="line fullname">—</div>
                <div id="department" class="line sub position">—</div>
                <div id="company" class="line sub division">—</div>
            </div>
            <div id="multiWinnerContainer" class="multi-winner-container hidden"></div>
        </div>
        <div class="popup-actions">
            <button class="btn cancel" onclick="cancelWinner()">❌ Hủy</button>
            <button class="btn confirm" onclick="confirmWinner()">✅ Xác nhận</button>
        </div>
    </div>

    <div class="hint">
        Nhấn <b>SPACE</b> để quay
        <span id="remaining" class="pill">—</span>
    </div>
    <div class="next-area">
        <div id="prizeCounter" class="prize-counter">0 / 0</div>
        <button id="btnNextPrize" class="btn-next next-price">➡ Tiếp</button>
    </div>

    <script>
        window.__ASSET_BASE = "<?php echo $asset; ?>";
        window.__API = {
            spin:        "<?php echo $this->createUrl('apiSpin'); ?>",
            prize:       "<?php echo $this->createUrl('apiPrize'); ?>",
            status:      "<?php echo $this->createUrl('apiStatus'); ?>",
            confirm:     "<?php echo $this->createUrl('apiConfirmWinner'); ?>",
            cancel:      "<?php echo $this->createUrl('apiCancelWinner'); ?>",
            nextPrize:   "<?php echo $this->createUrl('apiNextPrize'); ?>",
            checkAuth:   "<?php echo $this->createUrl('apiCheckAuth'); ?>",
            remoteSpin:  "<?php echo $this->createUrl('apiRemoteSpin'); ?>",
            checkRemote: "<?php echo $this->createUrl('apiCheckRemote'); ?>"
        };
    </script>
    <script src="<?php echo $asset; ?>/js/jquery-3.6.0.min.js"></script>
    <script src="<?php echo $asset; ?>/js/fireworks-modern.js"></script>
    <script src="<?php echo $asset; ?>/js/sweetalert2.all.min.js"></script>
    <script src="<?php echo $asset; ?>/js/lucky-show.js"></script>
</body>

</html>
