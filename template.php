<?php
// $Id$

/**
 * Implementation of hook_theme().
 */
function droplitcube_theme() {
  $items = array();

  // theme('blocks') targeted override for content region.
  $items['blocks_content'] = array();

  // Consolidate a variety of theme functions under a single template type.
  $items['block'] =
  $items['box'] =
  $items['comment'] =
  $items['fieldset'] =
  $items['node'] = array(
    'template' => 'object',
    'path' => drupal_get_path('theme', 'droplitcube') .'/templates',
  );
  $items['fieldset']['arguments'] = array('element' => array());
  $items['node']['template'] = 'node';

  // Print friendly page headers.
  $items['print_header'] = array(
    'arguments' => array(),
    'template' => 'print-header',
    'path' => drupal_get_path('theme', 'droplitcube') .'/templates',
  );

  $items['pager_list'] = array();

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
      'droplitcube_preprocess_form_buttons',
      'droplitcube_preprocess_form_legacy'
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
      'droplitcube_preprocess_form_buttons',
      'droplitcube_preprocess_form_legacy',
    ),
  );

  // These forms require additional massaging.
  $items['confirm_form'] = array(
    'arguments' => array('form' => array()),
    'path' => drupal_get_path('theme', 'droplitcube') .'/templates',
    'template' => 'form-simple',
    'preprocess functions' => array(
      'droplitcube_preprocess_form_confirm'
    ),
  );
  $items['node_form'] = array(
    'arguments' => array('form' => array()),
    'path' => drupal_get_path('theme', 'droplitcube') .'/templates',
    'template' => 'form-default',
    'preprocess functions' => array(
      'droplitcube_preprocess_form_buttons',
      'droplitcube_preprocess_form_node'
    ),
  );

  return $items;
}


/**
 * Strips CSS files from a Drupal CSS array whose filenames start with
 * prefixes provided in the $match argument.
 */
function droplitcube_css_stripped($match = array('modules/*','profiles/droplitinstallprofile/modules/contrib/vertical_tabs/vertical_tabs.css'), $exceptions = NULL) {
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
  // droplitcube has a more media handling.
  ksort($css);
  return $css;
}

/**
 * Print all child pages of a book.
 */
function droplitcube_print_book_children($node) {
  // We use a semaphore here since this function calls and is called by the
  // node_view() stack so that it may be called multiple times for a single book tree.
  static $semaphore;

  if (module_exists('book') && book_type_is_allowed($node->type)) {
    if (isset($_GET['print']) && isset($_GET['book_recurse']) && !isset($semaphore)) {
      $semaphore = TRUE;

      $child_pages = '';
      $zomglimit = 0;
      $tree = array_shift(book_menu_subtree_data($node->book));
      if (!empty($tree['below'])) {
        foreach ($tree['below'] as $link) {
          _droplitcube_print_book_children($link, $child_pages, $zomglimit);
        }
      }

      unset($semaphore);

      return $child_pages;
    }
  }

  return '';
}

/**
 * Book printing recursion.
 */
function _droplitcube_print_book_children($link, &$content, &$zomglimit, $limit = 500) {
  if ($zomglimit < $limit) {
    $zomglimit++;
    if (!empty($link['link']['nid'])) {
      $node = node_load($link['link']['nid']);
      if ($node) {
        $content .= node_view($node);
      }
      if (!empty($link['below'])) {
        foreach ($link['below'] as $child) {
          _droplitcube_print_book_children($child, $content);
        }
      }
    }
  }
}

/**
 * Preprocess functions ===============================================
 */

/**
 * Preprocessor for theme('page').
 */
function droplitcube_preprocess_page(&$vars) {
  $attr = array();
  $attr['class'] = $vars['body_classes'];
  $attr['class'] .= ' droplitcube'; // Add the droplitcube class so that we can avoid using the 'body' selector

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
  
  // Split primary and secondary local tasks
  $vars['tabs'] = theme('menu_local_tasks', 'primary');
  $vars['tabs2'] = theme('menu_local_tasks', 'secondary');

  // Link site name to frontpage
  // $vars['site_name'] = l($vars['site_name'], '<front>');
  
  // Split page content & content blocks.
  $vars['content_region'] = theme('blocks_content', TRUE);

  // Set a page icon class.
  $vars['page_icon_class'] = ($item = menu_get_item()) ? _droplitcube_icon_classes($item['href']) : '';

  // Add body class for theme.
  $vars['attr']['class'] .= ' droplitcube';

  // Body class for admin module.
  $vars['attr']['class'] .= ' admin-static';

  // Help pages. They really do need help.
  if (strpos($_GET['q'], 'admin/help/') === 0) {
    $vars['content'] = theme('help_page', $vars['content']);
  }

  // Display user account links.
  $vars['user_links'] = _droplitcube_user_links();

  // Help text toggler link.
  $vars['help_toggler'] = l(t('Help'), $_GET['q'], array('attributes' => array('id' => 'help-toggler', 'class' => 'toggler'), 'fragment' => 'help-text=1'));

  // Clear out help text if empty.
  if (empty($vars['help']) || !(strip_tags($vars['help']))) {
    $vars['help'] = '';
  }
  
  // Don't render the attributes yet so subthemes can alter them
  $vars['attr'] = $attr;
  
  $vars['primary_menu'] = menu_tree(variable_get('menu_primary_links_source', 'primary-links'));
  $vars['secondary_menu'] = theme('links', $vars['secondary_links'], array('class' => 'links secondary-links'));
  
}

/**
 * Implementation of preprocess_block().
 */
function droplitcube_preprocess_block(&$vars) {
  // Hide blocks with no content.
  $vars['hide'] = empty($vars['block']->content);
  $attr = array();
  $attr['id'] = "block-{$vars['block']->module}-{$vars['block']->delta}";
  $attr['class'] = "block block-{$vars['block']->module} {$vars['skinr']} {$vars['block_classes']}";
  $vars['attr'] = $attr;
  $vars['hook'] = 'block';
  $vars['title'] = !empty($vars['block']->subject) ? $vars['block']->subject : '';
  $vars['content'] = $vars['block']->content;
  $vars['is_prose'] = ($vars['block']->module == 'block') ? TRUE : FALSE;
}

/**
 * Implementation of preprocess_box().
 */
function droplitcube_preprocess_box(&$vars) {
  $attr = array();
  $attr['class'] = "box";
  $vars['attr'] = $attr;
  $vars['hook'] = 'box';
}

/**
 * Implementation of preprocess_fieldset().
 */
function droplitcube_preprocess_fieldset(&$vars) {
  $element = $vars['element'];

  $attr = isset($element['#attributes']) ? $element['#attributes'] : array();
  $attr['class'] = !empty($attr['class']) ? $attr['class'] : '';
  $attr['class'] .= ' fieldset';
  $attr['class'] .= !empty($element['#collapsible']) ? ' collapsible' : '';
  $attr['class'] .= !empty($element['#collapsible']) && !empty($element['#collapsed']) ? ' collapsed' : '';
  $vars['attr'] = $attr;

  $description = !empty($element['#description']) ? "<div class='description'>{$element['#description']}</div>" : '';
  $children = !empty($element['#children']) ? $element['#children'] : '';
  $value = !empty($element['#value']) ? $element['#value'] : '';
  $vars['content'] = $description . $children . $value;
  $vars['title'] = !empty($element['#title']) ? $element['#title'] : '';
  if (!empty($element['#collapsible'])) {
    $vars['title'] = l($vars['title'], $_GET['q'], array('fragment' => 'fieldset'));
    $vars['title'] = "<span class='icon'></span>" . $vars['title'];
  }
  $vars['hook'] = 'fieldset';
}

/**
 * Attempts to render a non-template based form for template rendering.
 */
function droplitcube_preprocess_form_legacy(&$vars) {
  if (isset($vars['form']['#theme']) && function_exists("theme_{$vars['form']['#theme']}")) {
    $function = "theme_{$vars['form']['#theme']}";
    $vars['form'] = array(
      '#type' => 'markup',
      '#value' => $function($vars['form'])
    );
  }
}

/**
 * Preprocessor for handling form button for most forms.
 */
function droplitcube_preprocess_form_buttons(&$vars) {
  if (empty($vars['buttons'])) {
    if (isset($vars['form']['buttons'])) {
      $vars['buttons'] = $vars['form']['buttons'];
      unset($vars['form']['buttons']);
    }
    else {
      $vars['buttons'] = array();
      foreach (element_children($vars['form']) as $key) {
        if (isset($vars['form'][$key]['#type']) && in_array($vars['form'][$key]['#type'], array('submit', 'button'))) {
          $vars['buttons'][$key] = $vars['form'][$key];
          unset($vars['form'][$key]);
        }
      }
    }
  }
}

/**
 * Preprocessor for theme('confirm_form').
 */
function droplitcube_preprocess_form_confirm(&$vars) {
  // Move the title from the page title (usually too big and unwieldy)
  $title = filter_xss_admin(drupal_get_title());
  $vars['form']['description']['#type'] = 'item';
  $vars['form']['description']['#value'] = empty($vars['form']['description']['#value']) ?
    "<strong>{$title}</strong>" :
    "<strong>{$title}</strong><p>{$vars['form']['description']['#value']}</p>";
  drupal_set_title(t('Please confirm'));

  // Button setup
  $vars['buttons'] = $vars['form']['actions'];
  unset($vars['form']['actions']);
}

/**
 * Preprocessor for theme('node_form').
 */
function droplitcube_preprocess_form_node(&$vars) {
  // @TODO: Figure out a better way here. drupal_alter() is preferable.
  // Allow modules to insert form elements into the sidebar,
  // defaults to showing taxonomy in that location.
  if (empty($vars['sidebar'])) {
    $vars['sidebar'] = array();
    $sidebar_fields = module_invoke_all('node_form_sidebar', $vars['form'], $vars['form']['#node']) + array('taxonomy');
    foreach ($sidebar_fields as $field) {
      if (isset($vars['form'][$field])) {
        $vars['sidebar'][$field] = $vars['form'][$field];
        unset($vars['form'][$field]);
      }
    }
  }
}

/**
 * Preprocessor for theme('help').
 */
function droplitcube_preprocess_help(&$vars) {
  $vars['hook'] = 'help';
  $vars['attr']['id'] = 'help-text';
  $class = 'path-admin-help clear-block toggleable';
  $vars['attr']['class'] = isset($vars['attr']['class']) ? "{$vars['attr']['class']} $class" : $class;
  $help = menu_get_active_help();
  if (($test = strip_tags($help)) && !empty($help)) {
    // Thankfully this is static cached.
    $vars['attr']['class'] .= menu_secondary_local_tasks() ? ' with-tabs' : '';

    $vars['is_prose'] = TRUE;
    $vars['layout'] = TRUE;
    $vars['content'] = "<span class='icon'></span>" . $help;
    $vars['links'] = '<label class="breadcrumb-label">'. t('Help text for') .'</label>';
    $vars['links'] .= theme('breadcrumb', drupal_get_breadcrumb(), FALSE);
  }
}

/**
 * Preprocessor for theme('help_page').
 */
function droplitcube_preprocess_help_page(&$vars) {
  $vars['hook'] = 'help-page';
  $vars['is_prose'] = TRUE;
  $vars['layout'] = TRUE;
  $vars['attr'] = array('class' => 'help-page clear-block');

  // Truly hackish way to navigate help pages.
  $module_info = module_rebuild_cache();
  $modules = array();
  foreach (module_implements('help', TRUE) as $module) {
    if (module_invoke($module, 'help', "admin/help#$module", NULL)) {
      $modules[$module] = $module_info[$module]->info['name'];
    }
  }
  asort($modules);
  $links = array();
  foreach ($modules as $module => $name) {
    $links[] = array('title' => $name, 'href' => "admin/help/{$module}");
  }
  $vars['links'] = theme('links', $links);
}

/**
 * Preprocessor for theme('node').
 */
function droplitcube_preprocess_node(&$vars) {
  $attr = array();
  $attr['id'] = "node-{$vars['node']->nid}";
  $attr['class'] = "node node-{$vars['node']->type} {$vars['node_classes']}";
  $attr['class'] .= $vars['node']->sticky ? ' sticky' : '';
  $vars['layout'] = TRUE;
  $vars['title'] = menu_get_object() === $vars['node'] ? '' : $vars['title'];

  $vars['attr']['class'] .= ' clear-block';
  $vars['attr'] = $attr;
  
  $vars['hook'] = 'node';
  $vars['is_prose'] = TRUE;

  // Add print customizations
  if (isset($_GET['print'])) {
    $vars['post_object'] = droplitcube_print_book_children($vars['node']);
  }
  
  // Clear out template file suggestions if we are the active theme.
  // Other subthemes will need to manage template suggestions on their own.
  global $theme_key;
  if (in_array($theme_key, array('droplitcube', 'cube'), TRUE)) {
    $vars['template_files'] = array();
  }
}

/**
 * Preprocessor for theme('comment').
 */
function droplitcube_preprocess_comment(&$vars) {
  $vars['layout'] = TRUE;
  $vars['attr']['class'] .= ' clear-block';
}

/**
 * Preprocessor for theme('comment_wrapper').
 */
function droplitcube_preprocess_comment_wrapper(&$vars) {
  $vars['hook'] = 'box';
  $vars['title'] = t('Comments');

  $vars['attr']['id'] = 'comments';
  $vars['attr']['class'] .= ' clear-block';
}

/**
 * Preprocessor for theme_print_header().
 */
function droplitcube_preprocess_print_header(&$vars) {
  $vars = array(
    'base_path' => base_path(),
    'theme_path' => base_path() .'/'. path_to_theme(),
    'site_name' => variable_get('site_name', 'Drupal'),
  );
  $count ++;
}





/**
 * Function overrides =================================================
 */

/**
 * Override of theme_menu_local_tasks().
 * Add argument to allow primary/secondary local tasks to be printed
 * separately. Use theme_links() markup to consolidate.
 */
function droplitcube_menu_local_tasks($type = '') {
  if ($primary = menu_primary_local_tasks()) {
    $primary = "<ul class='links primary-tabs'>{$primary}</ul>";
  }
  if ($secondary = menu_secondary_local_tasks()) {
    $secondary = "<ul class='links secondary-tabs'>$secondary</ul>";
  }
  switch ($type) {
    case 'primary':
      return $primary;
    case 'secondary':
      return $secondary;
    default:
      return $primary . $secondary;
  }
}

/**
 * Override of theme_form_element().
 * Take a more sensitive/delineative approach toward theming form elements.
 */
function droplitcube_form_element($element, $value) {
  $output = '';

  // This is also used in the installer, pre-database setup.
  $t = get_t();

  // Add a wrapper id
  $attr = array('class' => '');
  $attr['id'] = !empty($element['#id']) ? "{$element['#id']}-wrapper" : NULL;

  // Type logic
  $label_attr = array();
  $label_attr['for'] = !empty($element['#id']) ? $element['#id'] : '';

  if (!empty($element['#type']) && in_array($element['#type'], array('checkbox', 'radio'))) {
    $label_type = 'label';
    $attr['class'] .= ' form-item form-option';
  }
  else {
    $label_type = 'label';
    $attr['class'] .= ' form-item';
  }

  // Generate required markup
  $required_title = $t('This field is required.');
  $required = !empty($element['#required']) ? "<span class='form-required' title='{$required_title}'>*</span>" : '';

  // Generate label markup
  if (!empty($element['#title'])) {
    $title = $t('!title: !required', array('!title' => filter_xss_admin($element['#title']), '!required' => $required));
    $label_attr = drupal_attributes($label_attr);
    $output .= "<{$label_type} {$label_attr}>{$title}</{$label_type}>";
    $attr['class'] .= ' form-item-labeled';
  }

  // Add child values
  $output .= "$value";

  // Description markup
  $output .= !empty($element['#description']) ? "<div class='description'>{$element['#description']}</div>" : '';

  // Render the whole thing
  $attr = drupal_attributes($attr);
  $output = "<div {$attr}>{$output}</div>";

  return $output;

}

/**
 * Override of theme_file().
 * Reduces the size of upload fields which are by default far too long.
 */
function droplitcube_file($element) {
  _form_set_class($element, array('form-file'));
  $attr = $element['#attributes'] ? ' '. drupal_attributes($element['#attributes']) : '';
  return theme('form_element', $element, "<input type='file' name='{$element['#name']}' id='{$element['#id']}' size='15' {$attr} />");
}

/**
 * Override of theme_blocks().
 * Allows additional theme functions to be defined per region to
 * control block display on a per-region basis. Falls back to default
 * block region handling if no region-specific overrides are found.
 */
function droplitcube_blocks($region) {
  // Allow theme functions some additional control over regions.
  $registry = theme_get_registry();
  if (isset($registry['blocks_'. $region])) {
    return theme('blocks_'. $region);
  }
  return module_exists('context') && function_exists('context_blocks') ? context_blocks($region) : theme_blocks($region);
}

/**
 * Override of theme_username().
 */
function droplitcube_username($object) {
  if (!empty($object->name)) {
    // Shorten the name when it is too long or it will break many tables.
    $name = drupal_strlen($object->name) > 20 ? drupal_substr($object->name, 0, 15) .'...' : $object->name;
    $name = check_plain($name);

    // Default case -- we have a real Drupal user here.
    if ($object->uid && user_access('access user profiles')) {
      return l($name, 'user/'. $object->uid, array('attributes' => array('class' => 'username', 'title' => t('View user profile.'))));
    }
    // Handle cases where user is not registered but has a link or name available.
    else if (!empty($object->homepage)) {
      return l($name, $object->homepage, array('attributes' => array('class' => 'username', 'rel' => 'nofollow')));
    }
    // Produce an unlinked username.
    else {
      return "<span class='username'>{$name}</span>";
    }
  }
  return "<span class='username'>". variable_get('anonymous', t('Anonymous')) ."</span>";
}

/**
 * Override of theme_pager().
 * Easily one of the most obnoxious theming jobs in Drupal core.
 * Goals: consolidate functionality into less than 5 functions and
 * ensure the markup will not conflict with major other styles
 * (theme_item_list() in particular).
 */
function droplitcube_pager($tags = array(), $limit = 10, $element = 0, $parameters = array(), $quantity = 9) {
  $pager_list = theme('pager_list', $tags, $limit, $element, $parameters, $quantity);

  $links = array();
  $links['pager-first'] = theme('pager_first', ($tags[0] ? $tags[0] : t('First')), $limit, $element, $parameters);
  $links['pager-previous'] = theme('pager_previous', ($tags[1] ? $tags[1] : t('Prev')), $limit, $element, 1, $parameters);
  $links['pager-next'] = theme('pager_next', ($tags[3] ? $tags[3] : t('Next')), $limit, $element, 1, $parameters);
  $links['pager-last'] = theme('pager_last', ($tags[4] ? $tags[4] : t('Last')), $limit, $element, $parameters);
  $links = array_filter($links);
  $pager_links = theme('links', $links, array('class' => 'links pager pager-links'));

  if ($pager_list) {
    return "<div class='pager clear-block'>$pager_list $pager_links</div>";
  }
}

/**
 * Split out page list generation into its own function.
 */
function droplitcube_pager_list($tags = array(), $limit = 10, $element = 0, $parameters = array(), $quantity = 9) {
  global $pager_page_array, $pager_total, $theme_key;
  if ($pager_total[$element] > 1) {
    // Calculate various markers within this pager piece:
    // Middle is used to "center" pages around the current page.
    $pager_middle = ceil($quantity / 2);
    // current is the page we are currently paged to
    $pager_current = $pager_page_array[$element] + 1;
    // first is the first page listed by this pager piece (re quantity)
    $pager_first = $pager_current - $pager_middle + 1;
    // last is the last page listed by this pager piece (re quantity)
    $pager_last = $pager_current + $quantity - $pager_middle;
    // max is the maximum page number
    $pager_max = $pager_total[$element];
    // End of marker calculations.

    // Prepare for generation loop.
    $i = $pager_first;
    if ($pager_last > $pager_max) {
      // Adjust "center" if at end of query.
      $i = $i + ($pager_max - $pager_last);
      $pager_last = $pager_max;
    }
    if ($i <= 0) {
      // Adjust "center" if at start of query.
      $pager_last = $pager_last + (1 - $i);
      $i = 1;
    }
    // End of generation loop preparation.

    $links = array();

    // When there is more than one page, create the pager list.
    if ($i != $pager_max) {
      // Now generate the actual pager piece.
      for ($i; $i <= $pager_last && $i <= $pager_max; $i++) {
        if ($i < $pager_current) {
          $links["$i pager-item"] = theme('pager_previous', $i, $limit, $element, ($pager_current - $i), $parameters);
        }
        if ($i == $pager_current) {
          $links["$i pager-current"] = array('title' => $i);
        }
        if ($i > $pager_current) {
          $links["$i pager-item"] = theme('pager_next', $i, $limit, $element, ($i - $pager_current), $parameters);
        }
      }
      return theme('links', $links, array('class' => 'links pager pager-list'));
    }
  }
  return '';
}

/**
 * Return an array suitable for theme_links() rather than marked up HTML link.
 */
function droplitcube_pager_link($text, $page_new, $element, $parameters = array(), $attributes = array()) {
  $page = isset($_GET['page']) ? $_GET['page'] : '';
  if ($new_page = implode(',', pager_load_array($page_new[$element], $element, explode(',', $page)))) {
    $parameters['page'] = $new_page;
  }

  $query = array();
  if (count($parameters)) {
    $query[] = drupal_query_string_encode($parameters, array());
  }
  $querystring = pager_get_querystring();
  if ($querystring != '') {
    $query[] = $querystring;
  }

  // Set each pager link title
  if (!isset($attributes['title'])) {
    static $titles = NULL;
    if (!isset($titles)) {
      $titles = array(
        t('« first') => t('Go to first page'),
        t('‹ previous') => t('Go to previous page'),
        t('next ›') => t('Go to next page'),
        t('last »') => t('Go to last page'),
      );
    }
    if (isset($titles[$text])) {
      $attributes['title'] = $titles[$text];
    }
    else if (is_numeric($text)) {
      $attributes['title'] = t('Go to page @number', array('@number' => $text));
    }
  }

  return array(
    'title' => $text,
    'href' => $_GET['q'],
    'attributes' => $attributes,
    'query' => count($query) ? implode('&', $query) : NULL,
  );
}

/**
 * Override of theme_views_mini_pager().
 */
function droplitcube_views_mini_pager($tags = array(), $limit = 10, $element = 0, $parameters = array(), $quantity = 9) {
  global $pager_page_array, $pager_total;

  // Calculate various markers within this pager piece:
  // Middle is used to "center" pages around the current page.
  $pager_middle = ceil($quantity / 2);
  // current is the page we are currently paged to
  $pager_current = $pager_page_array[$element] + 1;
  // max is the maximum page number
  $pager_max = $pager_total[$element];
  // End of marker calculations.


  $links = array();
  if ($pager_total[$element] > 1) {
    $links['pager-previous'] = theme('pager_previous', (isset($tags[1]) ? $tags[1] : t('‹‹')), $limit, $element, 1, $parameters);
    $links['pager-current'] = array('title' => t('@current of @max', array('@current' => $pager_current, '@max' => $pager_max)));
    $links['pager-next'] = theme('pager_next', (isset($tags[3]) ? $tags[3] : t('››')), $limit, $element, 1, $parameters);
    return theme('links', $links, array('class' => 'links pager views-mini-pager'));
  }
}




/**
 * Override of theme_blocks() for content region. Allows content blocks
 * to be split away from page content in page template. See droplitcube_blocks()
 * for how this function is called.
 */
function droplitcube_blocks_content($doit = FALSE) {
  static $blocks;
  if (!isset($blocks)) {
    $blocks = module_exists('context') && function_exists('context_blocks') ? context_blocks('content') : theme_blocks('content');
  }
  return $doit ? $blocks : '';
}

/**
 * Override of theme('breadcrumb').
 */
function droplitcube_breadcrumb($breadcrumb, $prepend = TRUE) {
  $output = '';

  // Add current page onto the end.
  if (!drupal_is_front_page()) {
    $item = menu_get_item();
    $end = end($breadcrumb);
    if ($end && strip_tags($end) !== $item['title']) {
      $breadcrumb[] = "<strong>". check_plain($item['title']) ."</strong>";
    }
  }

  // Remove the home link.
  foreach ($breadcrumb as $key => $link) {
    if (strip_tags($link) === t('Home')) {
      unset($breadcrumb[$key]);
      break;
    }
  }

  // Optional: Add the site name to the front of the stack.
  if ($prepend) {
    $site_name = empty($breadcrumb) ? "<strong>". check_plain(variable_get('site_name', '')) ."</strong>" : l(variable_get('site_name', ''), '<front>', array('purl' => array('disabled' => TRUE)));
    array_unshift($breadcrumb, $site_name);
  }

  foreach ($breadcrumb as $link) {
    $output .= "<span class='breadcrumb-link'>{$link}</span>";
  }
  return $output;
}

/**
 * Display the list of available node types for node creation.
 */
function droplitcube_node_add_list($content) {
  $output = "<ul class='admin-list'>";
  if ($content) {
    foreach ($content as $item) {
      $item['title'] = "<span class='icon'></span>" . filter_xss_admin($item['title']);
      if (isset($item['localized_options']['attributes']['class'])) {
        $item['localized_options']['attributes']['class'] .= ' '. _droplitcube_icon_classes($item['href']);
      }
      else {
        $item['localized_options']['attributes']['class'] = _droplitcube_icon_classes($item['href']);
      }
      $item['localized_options']['html'] = TRUE;
      $output .= "<li>";
      $output .= l($item['title'], $item['href'], $item['localized_options']);
      $output .= '<div class="description">'. filter_xss_admin($item['description']) .'</div>';
      $output .= "</li>";
    }
  }
  $output .= "</ul>";
  return $output;
}

/**
 * Override of theme_admin_block_content().
 */
function droplitcube_admin_block_content($content, $get_runstate = FALSE) {
  static $has_run = FALSE;
  if ($get_runstate) {
    return $has_run;
  }
  $has_run = TRUE;
  $output = '';
  if (!empty($content)) {
    foreach ($content as $k => $item) {
      $content[$k]['title'] = "<span class='icon'></span>{$item['title']}";
      $content[$k]['localized_options']['html'] = TRUE;
      if (!empty($content[$k]['localized_options']['attributes']['class'])) {
        $content[$k]['localized_options']['attributes']['class'] .= _droplitcube_icon_classes($item['href']);
      }
      else {
        $content[$k]['localized_options']['attributes']['class'] = _droplitcube_icon_classes($item['href']);
      }
    }
    $output = system_admin_compact_mode() ? '<ul class="admin-list admin-list-compact">' : '<ul class="admin-list">';
    foreach ($content as $item) {
      $output .= '<li class="leaf">';
      $output .= l($item['title'], $item['href'], $item['localized_options']);
      if (!system_admin_compact_mode()) {
        $output .= "<div class='description'>{$item['description']}</div>";
      }
      $output .= '</li>';
    }
    $output .= '</ul>';
  }
  return $output;
}

/**
 * Override of theme('admin_menu_item_link').
 */
function droplitcube_admin_menu_item_link($link) {
  $link['localized_options'] = empty($link['localized_options']) ? array() : $link['localized_options'];
  $link['localized_options']['html'] = TRUE;
  if (!isset($link['localized_options']['attributes']['class'])) {
    $link['localized_options']['attributes']['class'] = _droplitcube_icon_classes($link['href']);
  }
  else {
    $link['localized_options']['attributes']['class'] .= ' '. _droplitcube_icon_classes($link['href']);
  }
  $link['description'] = check_plain(truncate_utf8(strip_tags($link['description']), 150, TRUE, TRUE));
  $link['description'] = "<span class='icon'></span>" . $link['description'];
  $link['title'] .= !empty($link['description']) ? "<span class='menu-description'>{$link['description']}</span>" : '';
  return l($link['title'], $link['href'], $link['localized_options']);
}

/**
 * Override of theme('textfield').
 */
function droplitcube_textfield($element) {
  if ($element['#size'] >= 30) {
    $element['#size'] = '';
    $element['#attributes']['class'] = isset($element['#attributes']['class']) ? "{$element['#attributes']['class']} fluid" : "fluid";
  }
  return theme_textfield($element);
}

/**
 * Override of theme('password').
 */
function droplitcube_password($element) {
  if ($element['#size'] >= 30 || $element['#maxlength'] >= 30) {
    $element['#size'] = '';
    $element['#attributes']['class'] = isset($element['#attributes']['class']) ? "{$element['#attributes']['class']} fluid" : "fluid";
  }
  return theme_password($element);
}

/**
 * Override of theme('node_submitted').
 */
function droplitcube_node_submitted($node) {
  return _droplitcube_submitted($node);
}

/**
 * Override of theme('comment_submitted').
 */
function droplitcube_comment_submitted($comment) {
  $vars = $comment;
  $vars->created = $comment->timestamp;
  return _droplitcube_submitted($comment);
}

/**
 * Helper function for cloning and drupal_render()'ing elements.
 */
function droplitcube_render_clone($elements) {
  static $instance;
  if (!isset($instance)) {
    $instance = 1;
  }
  foreach (element_children($elements) as $key) {
    if (isset($elements[$key]['#id'])) {
      $elements[$key]['#id'] = "{$elements[$key]['#id']}-{$instance}";
    }
  }
  $instance++;
  return drupal_render($elements);
}

/**
 * Helper function to submitted info theming functions.
 */
function _droplitcube_submitted($node) {
  $byline = t('Posted by !username', array('!username' => theme('username', $node)));
  $date = format_date($node->created, 'small');
  return "<div class='byline'>{$byline}</div><div class='date'>$date</div>";
}

/**
 * User/account related links.
 */
function _droplitcube_user_links() {
  // Add user-specific links
  global $user;
  $user_links = array();
  if (empty($user->uid)) {
    $user_links['login'] = array('title' => t('Login'), 'href' => 'user');
    $user_links['register'] = array('title' => t('Register'), 'href' => 'user/register');
  }
  else {
    $user_links['account'] = array('title' => t('Hello !username', array('!username' => $user->name)), 'href' => 'user', 'html' => TRUE);
    $user_links['logout'] = array('title' => t('Logout'), 'href' => "logout");
  }
  return $user_links;
}

/**
 * Generate an icon class from a path.
 */
function _droplitcube_icon_classes($path) {
  $classes = array();
  $args = explode('/', $path);
  if ($args[0] === 'admin' || (count($args) > 1 && $args[0] === 'node' && $args[1] === 'add')) {
    while (count($args)) {
      $classes[] = 'path-'. str_replace('/', '-', implode('/', $args));
      array_pop($args);
    }
    return implode(' ', $classes);
  }
  return '';
}
