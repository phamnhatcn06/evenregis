<?php
/**
 * Partial dùng chung: phần khung đầu (head + header) cho tất cả email hệ thống.
 * Mở các thẻ <table> khung ngoài & card; view chính echo tiếp các dòng nội dung;
 * partial `_email_footer` sẽ đóng lại các thẻ này.
 *
 * @var string $emailTitle     Tiêu đề tab trình duyệt (thẻ <title>)
 * @var string $headerTitle    Tiêu đề lớn hiển thị trong header
 * @var string $headerSubtitle Dòng phụ dưới tiêu đề (tuỳ chọn)
 * @var string $accentFrom     Màu gradient bắt đầu của header (tuỳ chọn)
 * @var string $accentTo       Màu gradient kết thúc của header (tuỳ chọn)
 * @var int    $containerWidth Chiều rộng card email, px (mặc định 600)
 */
$emailTitle     = isset($emailTitle) ? $emailTitle : 'Đại hội Mường Thanh 2026';
$headerTitle    = isset($headerTitle) ? $headerTitle : 'ĐẠI HỘI MƯỜNG THANH 2026';
$headerSubtitle = isset($headerSubtitle) ? $headerSubtitle : '';
$accentFrom     = isset($accentFrom) ? $accentFrom : '#0d6efd';
$accentTo       = isset($accentTo) ? $accentTo : '#0a58ca';
$containerWidth = isset($containerWidth) ? (int)$containerWidth : 600;
?>
<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo CHtml::encode($emailTitle); ?></title>
</head>

<body style="margin:0; padding:0; font-family: Arial, Helvetica, sans-serif; background-color:#f4f6f9; color:#333333; line-height:1.6;">
    <table width="100%" cellpadding="0" cellspacing="0" style="background-color:#f4f6f9; padding:30px 10px;">
        <tr>
            <td align="center">
                <table width="<?php echo $containerWidth; ?>" cellpadding="0" cellspacing="0" style="max-width:<?php echo $containerWidth; ?>px; width:100%; background-color:#ffffff; border-radius:10px; overflow:hidden; box-shadow:0 4px 15px rgba(0,0,0,0.08); border:1px solid #e2e8f0;">

                    <!-- Header -->
                    <tr>
                        <td style="background: linear-gradient(135deg, <?php echo $accentFrom; ?> 0%, <?php echo $accentTo; ?> 100%); padding:32px 25px; text-align:center;">
                            <h1 style="color:#ffffff; margin:0; font-size:24px; font-weight:bold; text-transform:uppercase; letter-spacing:0.5px; text-shadow:1px 1px 2px rgba(0,0,0,0.15);">
                                <?php echo CHtml::encode($headerTitle); ?>
                            </h1>
                            <?php if (!empty($headerSubtitle)): ?>
                            <p style="color:#ffffff; margin:10px 0 0 0; font-size:15px; opacity:0.95;">
                                <?php echo CHtml::encode($headerSubtitle); ?>
                            </p>
                            <?php endif; ?>
                        </td>
                    </tr>
