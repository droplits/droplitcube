<?php
// $Id$

// Include the definition of zen_theme_get_default_settings().
include_once './' . drupal_get_path('theme', 'droplitcube') . '/template.theme-registry.inc';


/**
 * Implementation of THEMEHOOK_settings() function.
 *
 * @param $saved_settings
 *   An array of saved settings for this theme.
 * @param $subtheme_defaults
 *   Allow a subtheme to override the default values.
 * @return
 *   A form array.
 */
function droplitcube_settings($saved_settings, $subtheme_defaults = array()) {
  /*
   * The default values for the theme variables. Make sure $defaults exactly
   * matches the $defaults in the template.php file.
   */

  // Add CSS to adjust the layout on the settings page
  drupal_add_css(drupal_get_path('theme', 'droplitcube') . '/css/theme-settings.css', 'theme');

  // Get the default values from the .info file.
  $defaults = droplitcube_theme_get_default_settings('droplitcube');

  // Allow a subtheme to override the default values.
  $defaults = array_merge($defaults, $subtheme_defaults);

  // Merge the saved variables and their default values.
  $settings = array_merge($defaults, $saved_settings);

  // Setting for flush all caches
  $form['droplitcube_block_edit_links'] = array(
     '#type'          => 'checkbox',
     '#title'         => t('Display block editing links.'),
     '#default_value' => $settings['droplitcube_block_edit_links'],
     '#description'   => t('When hovering over blocks, display edit links for the proper users.'),
    );

  // Setting for flush all caches
  $form['droplitcube_rebuild_registry'] = array(
     '#type'          => 'checkbox',
     '#title'         => t('Rebuild the theme registry on every page.'),
     '#default_value' => $settings['droplitcube_rebuild_registry'],
     '#description'   => t('During theme development, it can be very useful to continuously <a href="!link">rebuild the theme registry</a>. WARNING: this is a huge performance penalty and must be turned off on production websites.', array('!link' => 'http://drupal.org/node/173880#theme-registry')),
    );

  // Setting to add the showgrid class
  $form['droplitcube_showgrid'] = array(
     '#type'          => 'checkbox',
     '#title'         => t('Show the droplitcube Grid'),
     '#default_value' => $settings['droplitcube_showgrid'],
     '#description'   => t('During theme development, it can be very useful to turn on the display of the grid.'),
    );

  // Setting to choose what displays in the header.
  $form['droplitcube_header_display'] = array(
     '#type' => 'select', 
     '#title' => t('Items Displayed in Header '), 
     '#default_value' => $settings['droplitcube_header_display'],
     '#options' => array(
       'logo' => t('Logo Only'), 
       'text' => t('Text Only'), 
       'full' => t('Logo and Text'),
     ),
     '#description' => t('Choose what elements to display in the header.'),
    );
  // Return the additional form widgets
  return $form;
}
