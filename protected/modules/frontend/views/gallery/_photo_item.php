<?php

/**
 * Một ô ảnh trong lưới album. Markup khớp với hàm dựng ở gallery.js (trang lazy-load).
 *
 * @var array $p  thumb_url, web_url, original_url, file_name, mine, claim_count, claimer_names
 */
$e = function ($s) { return CHtml::encode($s); };
$mine = !empty($p['mine']);
$count = isset($p['claim_count']) ? (int) $p['claim_count'] : 0;
$names = isset($p['claimer_names']) && is_array($p['claimer_names']) ? implode(', ', $p['claimer_names']) : '';
?>
<figure class="gallery-item<?php echo $mine ? ' is-mine' : ''; ?>"
        data-id="<?php echo (int) $p['id']; ?>"
        data-web="<?php echo $e(isset($p['web_url']) ? $p['web_url'] : ''); ?>"
        data-original="<?php echo $e(isset($p['original_url']) ? $p['original_url'] : ''); ?>"
        data-name="<?php echo $e(isset($p['file_name']) ? $p['file_name'] : ''); ?>"
        data-mine="<?php echo $mine ? '1' : '0'; ?>"
        data-count="<?php echo $count; ?>"
        data-names="<?php echo $e($names); ?>">
  <img src="<?php echo $e(isset($p['thumb_url']) ? $p['thumb_url'] : ''); ?>"
       alt="<?php echo $e(isset($p['file_name']) ? $p['file_name'] : ''); ?>"
       loading="lazy">
  <button type="button" class="gi-claim" title="Đánh dấu ảnh của tôi">
    <span class="material-symbols-outlined"><?php echo $mine ? 'how_to_reg' : 'person_add'; ?></span>
  </button>
  <span class="gi-count"<?php echo $count > 0 ? '' : ' style="display:none;"'; ?>>
    <span class="material-symbols-outlined">group</span><span class="gi-count-n"><?php echo $count; ?></span>
  </span>
</figure>
