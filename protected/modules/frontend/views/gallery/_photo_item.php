<?php

/**
 * Một ô ảnh trong lưới album. Markup khớp với hàm dựng ở gallery.js (trang lazy-load).
 *
 * @var array $p  thumb_url, web_url, original_url, file_name, width, height
 */
$e = function ($s) { return CHtml::encode($s); };
?>
<figure class="gallery-item"
        data-web="<?php echo $e(isset($p['web_url']) ? $p['web_url'] : ''); ?>"
        data-original="<?php echo $e(isset($p['original_url']) ? $p['original_url'] : ''); ?>"
        data-name="<?php echo $e(isset($p['file_name']) ? $p['file_name'] : ''); ?>">
  <img src="<?php echo $e(isset($p['thumb_url']) ? $p['thumb_url'] : ''); ?>"
       alt="<?php echo $e(isset($p['file_name']) ? $p['file_name'] : ''); ?>"
       loading="lazy">
</figure>
