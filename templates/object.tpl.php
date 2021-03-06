<?php if (!empty($content) || !empty($title)): ?>

<?php if (!empty($pre_object)) print $pre_object ?>

<div <?php if (!empty($attr)) print drupal_attributes($attr) ?>>
  <?php if ($layout && !empty($submitted) || !empty($links)): ?>
    <div class='column-side'><div class='column-wrapper'>
    <div class="developer">OBEJECT.TPL.PHP column-side</div>
  <?php endif; ?>

  <?php if (!empty($submitted)): ?>
    <div class='<?php print $hook ?>-submitted clear-block'><?php print $submitted ?></div>
  <?php endif; ?>
  <?php if (!empty($links)): ?>
    <div class='<?php print $hook ?>-links clear-block'><?php print $links ?></div>
  <?php endif; ?>

  <?php if ($layout && !empty($submitted) || !empty($links)): ?>
    </div></div>
  <?php endif; ?>

  <?php if ($layout): ?>
    <div class='column-main'><div class='column-wrapper'>
    <div class="developer">DROPLITCUBE OBEJECT.TPL.PHP<br />column-main</div>
  <?php endif; ?>

  <?php if (!empty($title)): ?>
    <h2 class='<?php print $hook ?>-title'>
      <?php if (!empty($new)): ?><a id='new' class='new'><?php print('New') ?></a><?php endif; ?>
      <?php print $title ?>
    </h2>
  <?php endif; ?>

  <?php if (!empty($content)): ?>
    <div class='<?php print $hook ?>-content clear-block <?php if (!empty($is_prose)) print 'prose' ?>'>
      <div class='<?php print $hook ?>-wrapper'>
      <?php print $content ?>
      </div>
    </div>
    <?php print $edit_links; ?>  
  <?php endif; ?>

  <?php if ($layout): ?>
    </div></div>
  <?php endif; ?>
</div>

<?php if (!empty($post_object)) print $post_object ?>

<?php endif; ?>
