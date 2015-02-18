<?php

/**
 * Add body classes if certain regions have content.
 */
function half_preprocess_html(&$variables) {
  if (!empty($variables['page']['featured'])) {
    $variables['classes_array'][] = 'featured';
  }

  if (!empty($variables['page']['triptych_first'])
    || !empty($variables['page']['triptych_middle'])
    || !empty($variables['page']['triptych_last'])) {
    $variables['classes_array'][] = 'triptych';
  }

  if (!empty($variables['page']['footer_firstcolumn'])
    || !empty($variables['page']['footer_secondcolumn'])
    || !empty($variables['page']['footer_thirdcolumn'])
    || !empty($variables['page']['footer_fourthcolumn'])) {
    $variables['classes_array'][] = 'footer-columns';
  }

  // Add conditional stylesheets for IE
  drupal_add_css(path_to_theme() . '/css/ie.css', array('group' => CSS_THEME, 'browsers' => array('IE' => 'lte IE 7', '!IE' => FALSE), 'preprocess' => FALSE));
  drupal_add_css(path_to_theme() . '/css/ie6.css', array('group' => CSS_THEME, 'browsers' => array('IE' => 'IE 6', '!IE' => FALSE), 'preprocess' => FALSE));

  $alias = drupal_get_path_alias($_GET['q']);
  $alias_parts = explode("/", $alias);

  // if (isset($alias_parts[1])) {
  //   foreach($alias_parts as $class) {
  //     $variables['classes_array'][] = $class;
  //   }
  // }
  // else if (!isset($alias_parts[1])) {
  //   $variables['classes_array'][] = $alias_parts[0] . '-landing';
  // }

}

/**
 * Override or insert variables into the page template for HTML output.
 */
function half_process_html(&$variables) {
  // Hook into color.module.
  if (module_exists('color')) {
    _color_html_alter($variables);
  }
}

/**
 * Override or insert variables into the page template.
 */
function half_process_page(&$variables) {
  // Hook into color.module.
  if (module_exists('color')) {
    _color_page_alter($variables);
  }
  // Always print the site name and slogan, but if they are toggled off, we'll
  // just hide them visually.
  $variables['hide_site_name']   = theme_get_setting('toggle_name') ? FALSE : TRUE;
  $variables['hide_site_slogan'] = theme_get_setting('toggle_slogan') ? FALSE : TRUE;
  if ($variables['hide_site_name']) {
    // If toggle_name is FALSE, the site_name will be empty, so we rebuild it.
    $variables['site_name'] = filter_xss_admin(variable_get('site_name', 'Drupal'));
  }
  if ($variables['hide_site_slogan']) {
    // If toggle_site_slogan is FALSE, the site_slogan will be empty, so we rebuild it.
    $variables['site_slogan'] = filter_xss_admin(variable_get('site_slogan', ''));
  }
  // Since the title and the shortcut link are both block level elements,
  // positioning them next to each other is much simpler with a wrapper div.
  if (!empty($variables['title_suffix']['add_or_remove_shortcut']) && $variables['title']) {
    // Add a wrapper div using the title_prefix and title_suffix render elements.
    $variables['title_prefix']['shortcut_wrapper'] = array(
      '#markup' => '<div class="shortcut-wrapper clearfix">',
      '#weight' => 100,
    );
    $variables['title_suffix']['shortcut_wrapper'] = array(
      '#markup' => '</div>',
      '#weight' => -99,
    );
    // Make sure the shortcut link is the first item in title_suffix.
    $variables['title_suffix']['add_or_remove_shortcut']['#weight'] = -100;
  }
}

/**
 * Implements hook_preprocess_maintenance_page().
 */
function half_preprocess_maintenance_page(&$variables) {
  // By default, site_name is set to Drupal if no db connection is available
  // or during site installation. Setting site_name to an empty string makes
  // the site and update pages look cleaner.
  // @see template_preprocess_maintenance_page
  if (!$variables['db_is_active']) {
    $variables['site_name'] = '';
  }
  drupal_add_css(drupal_get_path('theme', 'eci') . '/css/maintenance-page.css');
}

/**
 * Override or insert variables into the maintenance page template.
 */
function half_process_maintenance_page(&$variables) {
  // Always print the site name and slogan, but if they are toggled off, we'll
  // just hide them visually.
  $variables['hide_site_name']   = theme_get_setting('toggle_name') ? FALSE : TRUE;
  $variables['hide_site_slogan'] = theme_get_setting('toggle_slogan') ? FALSE : TRUE;
  if ($variables['hide_site_name']) {
    // If toggle_name is FALSE, the site_name will be empty, so we rebuild it.
    $variables['site_name'] = filter_xss_admin(variable_get('site_name', 'Drupal'));
  }
  if ($variables['hide_site_slogan']) {
    // If toggle_site_slogan is FALSE, the site_slogan will be empty, so we rebuild it.
    $variables['site_slogan'] = filter_xss_admin(variable_get('site_slogan', ''));
  }
}

/**
 * Override or insert variables into the node template.
 */
function half_preprocess_node(&$variables) {
  if ($variables['view_mode'] == 'full' && node_is_page($variables['node'])) {
    $variables['classes_array'][] = 'node-full';
  }
}

/**
 * Override or insert variables into the block template.
 */
function half_preprocess_block(&$variables) {
  // In the header region visually hide block titles.
  if ($variables['block']->region == 'header') {
    $variables['title_attributes_array']['class'][] = 'element-invisible';
  }
}

/**
 * Implements theme_menu_tree().
 */
function half_menu_tree($variables) {
  return '<ul class="menu clearfix">' . $variables['tree'] . '</ul>';
}

/**
 * Preprocess function.
 */
function half_preprocess_taxonomy_term(&$variables) {
  if($variables['vocabulary_machine_name'] == 'products' || $variables['vocabulary_machine_name'] == 'services_solutions') {
    $contextual_links = array(
      '#type' => 'contextual_links',
      '#contextual_links' => array(
        'taxonomy' => array('taxonomy/term', array($variables['tid'])),
      ),
    );
    $variables['title_suffix']['contextual_links'] = $contextual_links;
  }
}

/**
 * Implements theme_field__field_type().
 */
function half_field__taxonomy_term_reference($variables) {
  $output = '';

  // Render the label, if it's not hidden.
  if (!$variables['label_hidden']) {
    $output .= '<h3 class="field-label">' . $variables['label'] . ': </h3>';
  }

  // Render the items.
  $output .= ($variables['element']['#label_display'] == 'inline') ? '<ul class="links inline">' : '<ul class="links">';
  foreach ($variables['items'] as $delta => $item) {
    $output .= '<li class="taxonomy-term-reference-' . $delta . '"' . $variables['item_attributes'][$delta] . '>' . drupal_render($item) . '</li>';
  }
  $output .= '</ul>';

  // Render the top-level DIV.
  $output = '<div class="' . $variables['classes'] . (!in_array('clearfix', $variables['classes_array']) ? ' clearfix' : '') . '"' . $variables['attributes'] .'>' . $output . '</div>';

  return $output;
}

/**
 * Override or insert variables into the page template.
 */
function half_preprocess_page(&$variables) {

  if (isset($variables['node'])) {
    $node = $variables['node'];

    //setting normal page image for nodes
    if (!empty($node->field_page_image)) {
      $imageCount = 0;
      foreach ($node->field_page_image[$node->language] as $file) {
        if (drupal_is_front_page()) {
          $page_image = array(
            'style_name' => 'homepage_image',
            'path' => $file['uri'],
          );
        } else {
          $page_image = array(
            'style_name' => 'page_image',
            'path' => $file['uri'],
          );
        }
        $imageCount++;

        $temp_array['image'] = theme('image_style', $page_image);
        $variables['page_image'][] = $temp_array;
        $variables['count'] = $imageCount;
      }
    }
  }

  // //setting normal page image for taxonomy terms
  // if (isset($variables['page']['content']['system_main']['term_heading']['term']['#term']) && !empty($variables['page']['content']['system_main']['term_heading']['term']['#term']->field_page_image)) {
  //       $imageCount = 0;
  //       foreach ($variables['page']['content']['system_main']['term_heading']['term']['#term']->field_page_image['und'] as $file){
  //       $taxonomy_image = array(
  //         'style_name' => 'page_image',
  //         'path' => $file['uri'],
  //       );
  //       $imageCount++;
        
  //       $temp_array['taxImage'] = theme('image_style', $taxonomy_image);
  //       $variables['taxonomy_image'][] = $temp_array;
  //       $variables['count'] = $imageCount;
  //     }
  // }

  // if (isset($node) && $node->type == 'project') {
  //   $variables['theme_hook_suggestions'][] = 'page__project';
  // }
  
  // if (isset($variables['node'])) {
  //   if ($variables['node']->type == 'homepage') {

  //     $node = node_load($variables['node']->nid);
  //     foreach($node->field_homepage_slider['und'] as $idx => $value){
  //       $field_collection = entity_load('field_collection_item', array($value['value']));
  //       foreach($field_collection as $key => $item){
  //         $image_link = image_style_url('homepage_slider', $item->field_slider_image['und'][0]['uri']);
  //         $temp_array['image_link'] = $image_link;
  //         $temp_array['heading'] = $item->field_slider_title['und'][0]['value'];
  //           $temp_array['description'] = $item->field_slider_text['und'][0]['value'];
  //           $variables['feature'][] = $temp_array;
  //       }
  //     }

  //   }
  // }
}
