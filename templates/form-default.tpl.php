<div class='form form-layout-default clear-block'>
  <div class='column-main'><div class='column-wrapper clear-block'>
  <div class="developer">DROPLITCUBE FORM-DEFAULT.TPL.PHP<br />column-main</div>
    <?php print drupal_render($form); ?>
    <div class='buttons'><div class="developer">buttons</div><?php print rubik_render_clone($buttons); ?></div>
  </div></div>
  <div class='column-side'><div class='column-wrapper clear-block'>
  <div class="developer">column-side</div>
    <div class='buttons'><div class="developer">buttons</div><?php print drupal_render($buttons); ?></div>
    <?php print drupal_render($sidebar); ?>
  </div></div>
</div>