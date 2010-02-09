<?php

// Auto-rebuild the theme registry during theme development.
if (theme_get_setting('droplitcube_rebuild_registry')) {
  drupal_rebuild_theme_registry();
}

/**
 * Implementation of hook_theme().
 */
function droplitcube_theme(&$existing, $type, $theme, $path) {
  $items = array();

  // theme('blocks') targeted override for content region.
  $items['blocks_content'] = array();

  // Content theming.
  $items['help'] =
  $items['block'] =
  $items['node'] =
  $items['comment'] = array(
    'path' => drupal_get_path('theme', 'droplitcube') .'/templates',
    'template' => 'object',
  );
  $items['node']['template'] = 'node';

  // Help pages really need help. See preprocess_page().
  $items['help_page'] = array(
    'arguments' => array('content' => array()),
    'path' => drupal_get_path('theme', 'droplitcube') .'/templates',
    'template' => 'object',
  );

  // Form layout: simple.
  $items['filter_admin_overview'] =
  $items['user_admin_perm'] = array(
    'arguments' => array('form' => array()),
    'path' => drupal_get_path('theme', 'droplitcube') .'/templates',
    'template' => 'form-simple',
    'preprocess functions' => array(
      'rubik_preprocess_form_buttons',
      'rubik_preprocess_form_legacy'
    ),
  );

  // Form layout: default (2 column).
  $items['block_add_block_form'] =
  $items['block_admin_configure'] =
  $items['comment_form'] =
  $items['contact_admin_edit'] =
  $items['contact_mail_page'] =
  $items['contact_mail_user'] =
  $items['filter_admin_format_form'] =
  $items['forum_form'] =
  $items['locale_languages_edit_form'] =
  $items['locale_languages_configure_form'] =
  $items['menu_edit_menu'] =
  $items['menu_edit_item'] =
  $items['node_type_form'] =
  $items['path_admin_form'] =
  $items['system_settings_form'] =
  $items['system_themes_form'] =
  $items['system_modules'] =
  $items['system_actions_configure'] =
  $items['taxonomy_form_term'] =
  $items['taxonomy_form_vocabulary'] =
  $items['user_pass'] =
  $items['user_login'] =
  $items['user_register'] =
  $items['user_profile_form'] =
  $items['user_admin_access_add_form'] = array(
    'arguments' => array('form' => array()),
    'path' => drupal_get_path('theme', 'droplitcube') .'/templates',
    'template' => 'form-default',
    'preprocess functions' => array(
      'rubik_preprocess_form_buttons',
      'rubik_preprocess_form_legacy',
    ),
  );

  // These forms require additional massaging.
  $items['confirm_form'] = array(
    'arguments' => array('form' => array()),
    'path' => drupal_get_path('theme', 'droplitcube') .'/templates',
    'template' => 'form-simple',
    'preprocess functions' => array(
      'rubik_preprocess_form_confirm'
    ),
  );
  $items['node_form'] = array(
    'arguments' => array('form' => array()),
    'path' => drupal_get_path('theme', 'droplitcube') .'/templates',
    'template' => 'form-default',
    'preprocess functions' => array(
      'rubik_preprocess_form_buttons',
      'rubik_preprocess_form_node'
    ),
  );

  return $items;

  if (!db_is_active()) {
    return array();
  }
  include_once './' . drupal_get_path('theme', 'droplitcube') . '/template.theme-registry.inc';
  return _droplitcube_theme($existing, $type, $theme, $path);

}

/**
 * Strips CSS files from a Drupal CSS array whose filenames start with
 * prefixes provided in the $match argument.
 */
function droplitcube_css_stripped($match = array('modules/*','profiles/droplitinstallprofile/modules/contrib/calendar/*','profiles/droplitinstallprofile/modules/contrib/date/*'), $exceptions = NULL) {
  // Set default exceptions
  if (!is_array($exceptions)) {
    $exceptions = array(
      'modules/system/system.css',
      'modules/update/update.css',
      'modules/openid/openid.css',
      'modules/acquia/*',
    );
  }
  $css = drupal_add_css();
  $match = implode("\n", $match);
  $exceptions = implode("\n", $exceptions);
  foreach (array_keys($css['all']['module']) as $filename) {
    if (drupal_match_path($filename, $match) && !drupal_match_path($filename, $exceptions)) {
      unset($css['all']['module'][$filename]);
    }
  }

  // This servers to move the "all" CSS key to the front of the stack.
  // Mainly useful because modules register their CSS as 'all', while
  // Tao has a more media handling.
  ksort($css);
  return $css;
}

/**
 * Preprocessor for theme('page').
 */
function droplitcube_preprocess_page(&$vars) {
  // Automatically adjust layout for page with right sidebar content if no
  // explicit layout has been set.
  $layout = module_exists('context_layouts') ? context_layouts_get_active_layout() : NULL;
  if (arg(0) != 'admin' && !empty($vars['right']) && !$layout) {
    $vars['template_files'][] = 'layout-sidebar';
    $css = array('screen' => array('theme' => array(drupal_get_path('theme', 'droplitcube') . '/layout-sidebar.css' => TRUE)));
    $vars['styles'] .= drupal_get_css($css);
  }
  

  // Replace screen/all stylesheets with print
  // We want a minimal print representation here for full control.
  if (isset($_GET['print'])) {
    $css = droplitcube_css_stripped();
    unset($css['all']);
    unset($css['screen']);
    $css['all'] = $css['print'];
    $vars['styles'] = drupal_get_css($css);

    // Add print header
    $vars['print_header'] = theme('print_header');

    // Replace all body classes
    $attr['class'] = 'print';

    // Use print template
    $vars['template_file'] = 'print-page';

    // Suppress devel output
    $GLOBALS['devel_shutdown'] = FALSE;
  }
  // Get minimalized CSS. Add designkit styles back in if needed.
  else {
    $vars['styles'] = drupal_get_css(droplitcube_css_stripped());
    $vars['styles'] .= isset($vars['designkit']) ? $vars['designkit'] : '';
  }  


  //  if (!drupal_is_front_page()) {
  //    $vars['head_title'] = $title .' | '. $vars['site_name'];
  //    if ($vars['site_slogan'] != '') {
  //      $vars['head_title'] .= ' &ndash; '. $vars['site_slogan'];
  //    }
  //  }


  $header_switch = theme_get_setting("droplitcube_header_display");
  if (!$vars['logo'] && ($header_switch == 'full' || $header_switch == 'logo')) {
    $header_switch = 'text';
  }
  switch ($header_switch) {
    case 'full':
      $vars['logo_block'] = "<a title=". $vars['site_name'] ." href=". url() ."><img src=". $vars['logo'] ." alt=". $vars['site_name'] ." border='0' />".  $vars['site_name'] ."</a>";
      break;
    case 'logo':
      $vars['logo_block'] = "<a title=". $vars['site_name'] ." href=". url() ."><img src=". $vars['logo'] ." alt=". $vars['site_name'] ." border='0' /></a>";
      break;
    default:
      $vars['logo_block'] = "<a title=". $vars['site_name'] ." href=". url() .">".  $vars['site_name'] ."</a>";
      break;
  }

}



/**
 * Override or insert variables into the block templates.
 *
 * @param $vars
 *   An array of variables to pass to the theme template.
 * @param $hook
 *   The name of the template being rendered ("block" in this case.)
 */
function droplitcube_preprocess_block(&$vars, $hook) {
  $block = $vars['block'];
  
  $attr = array();
  $attr['id'] = "block-{$vars['block']->module}-{$vars['block']->delta}";
  $attr['class'] = "block block-{$vars['block']->module} count-{$vars['id']} region-{$vars['block_zebra']} region-count-{$vars['block_id']}";

  $vars['edit_links_array'] = array();
  $vars['edit_links'] = '';

  if (theme_get_setting('droplitcube_block_edit_links') && user_access('administer blocks')) {
    include_once './' . drupal_get_path('theme', 'droplitcube') . '/template.block-editing.inc';
    droplitcube_preprocess_block_editing($vars, $hook);
    $attr['class'] = "block block-{$vars['block']->module} count-{$vars['id']} region-{$vars['block_zebra']} region-count-{$vars['block_id']} with-block-editing";
  }

  $vars['attr'] = $attr;

  $vars['hook'] = 'block';
  $vars['title'] = !empty($vars['block']->subject) ? $vars['block']->subject : '';
  $vars['content'] = $vars['block']->content;
  $vars['is_prose'] = ($vars['block']->module == 'block') ? TRUE : FALSE;

}

/**
 * Override of theme_blocks() for content region. Allows content blocks
 * to be split away from page content in page template. See tao_blocks()
 * for how this function is called.
 */
function droplitcube_blocks_content($doit = FALSE) {
  static $blocks;
  if (!isset($blocks)) {
    $blocks = module_exists('context') && function_exists('context_blocks') ? context_blocks('content') : theme_blocks('content');
  }
  return $doit ? $blocks : '';
}