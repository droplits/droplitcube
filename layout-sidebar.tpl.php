<?php include('header.inc'); ?>

<div id='page' class='clear-block limiter page-content'>
<div class="developer">DROPLITCUBE LAYOUT-SIDEBAR.TPL.PHP</div>  
  <?php if ($show_messages && $messages): ?>
    <div id='console' class='clear-block'><?php print $messages; ?></div>
  <?php endif; ?>  
  <div id='content'>
    <?php if (!empty($content) && (!$is_front)): ?>
      <div class='content-wrapper clear-block'><?php print $content ?></div>
    <?php endif; ?>
    <?php print $content_region ?>
  </div>
  <div id='right' class='clear-block'>
    <div class="developer">right</div>
    <?php print $right ?>
  </div>
</div>

<?php include('footer.inc'); ?>
