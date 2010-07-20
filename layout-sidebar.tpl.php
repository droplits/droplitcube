<?php include('header.inc'); ?>

<div id='page' class='page-content clear-block'>
<div class="developer">DROPLITCUBE LAYOUT-SIDEBAR.TPL.PHP</div>  
  <?php if ($show_messages && $messages): ?>
    <div id='console' class='clear-block'><?php print $messages; ?></div>
  <?php endif; ?>  
  <div id='content'>
    <div class='content-wrapper clear-block'>
    <?php if (!empty($content) && (!$is_front)): ?>
      <?php print $content ?>
    <?php endif; ?>
    <?php print $content_region ?>
    </div>
  </div>
  <div id='right' class='clear-block'>
    <div class="developer">right</div>
    <?php print $right ?>
  </div>
</div>

<?php include('footer.inc'); ?>
