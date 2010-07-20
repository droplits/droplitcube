<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="<?php print $language->language ?>" lang="<?php print $language->language ?>" dir="<?php print $language->dir ?>">
  <head>
    <?php print $head ?>
    <?php print $styles ?>
    <title><?php print $head_title ?></title>
  </head>
  <body <?php print drupal_attributes($attr) ?>>
  <div class="container_24 <?php print $skinr; ?>">
  <?php if ($help): ?>
  <div id='page-help'><div class='limiter clear-block'>
    <?php if ($help_toggler) print $help_toggler ?>
    <?php if ($help) print $help ?>
  </div></div>
  <?php endif; ?>

  <div id='page-header' class='clear-block'><div class='page-header-wrapper'>
    <?php if ($user_links): ?>
    <div id='user-links' class='limiter-right clear-block'>
      <?php if ($user_links) print theme('links', $user_links) ?>
    </div>
    <?php endif; ?>
    <?php if ($logo || $site_name || $site_slogan): ?>
      <div id="header-site-info" class="header-site-info block">
      <div id="header-site-info-inner" class="header-site-info-inner inner">
      <?php if ($logo): ?>
        <div id="logo">
          <a href="<?php print check_url($front_page); ?>" title="<?php print t('Home'); ?>"><img src="<?php print $logo; ?>" alt="<?php print t('Home'); ?>" /></a>
        </div>
      <?php endif; ?>
      <?php if ($site_name): ?>
        <span id="site-name" class="developer"><a href="<?php print check_url($front_page); ?>" title="<?php print t('Home'); ?>"><?php print $site_name; ?></a></span>
      <?php endif; ?>
      <?php if ($site_slogan): ?>
        <span id="slogan"><?php print $site_slogan; ?></span>
      <?php endif; ?>
      </div><!-- /header-site-info-inner -->
      </div><!-- /header-site-info -->
    <?php endif; ?>
  </div></div>

    <?php /* if ($help_toggler && $primary_links): */ ?>
    <!-- <div id='primary-links' class='limiter clear-block'> -->
      <?php /* if ($help_toggler) print $help_toggler */ ?>
      <?php /* if ($primary_links) print theme('links', $primary_links) */ ?>
    <!-- </div> -->
    <?php /* endif; */ ?>

    
    <?php if (isset($primary_links)) : ?>
      <div id="superfish">
      <?php print $primary_menu; ?>
      </div>
    <?php endif; ?>


    <?php if ($breadcrumb): ?>
    <div id='branding' class='limiter-left clear-block'>
      <div class='breadcrumb clear-block'><?php print $breadcrumb ?></div>
    </div>
    <?php endif; ?>
    
    <?php if ($tabs): ?>
    <div id='tabs' class='clear-block'>
      <div class='page-tabs clear-block'>
        <?php print $tabs ?>
      </div>
      <?php if ($tabs2): ?>
      <div class='page-tabs clear-block'>
        <?php print $tabs2 ?>
      </div>
      <?php endif; ?>
    </div>
    <?php endif; ?>

  <!-- 
    Adds ability for title to appear on non-node pages
  -->
  <?php if (arg(0) != 'node'): ?>
    <div id='page-title' class='limiter clear-block'>
      <h2 class='page-title <?php print $page_icon_class ?>'>
        <?php if (!empty($page_icon_class)): ?><span class='icon'></span><?php endif; ?>
        <?php if ($title) print $title ?>
      </h2>
    </div>
  <?php endif; ?>




