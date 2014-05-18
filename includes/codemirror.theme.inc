<?php
/**
 * @file
 * CodeMirror theming functions.
 */

/**
 * Theme the settings forms into tables.
 */
function theme_codemirror_settings_table($form) {
  $header = array();
  $table = $form['form'];
  foreach ($table['header'] as $key => $value) {
    if ($key[0] == '#') {
      continue;
    }
    if (!(array_key_exists('#access', $value) && $value['#access'] == FALSE)) {
      $header[$key] = drupal_render($value);
    }
  }

  $rows = array();
  foreach ($table as $lang => $language) {
    if ($lang[0] == '#' || $lang == 'header') {
      continue;
    }
    $rows[$lang] = array();
    foreach ($language as $key => $elem) {
      if ($key[0] == '#') {
        continue;
      }
      if (!(array_key_exists('#access', $elem) && $elem['#access'] == FALSE)) {
        $rows[$lang][$key] = drupal_render($elem);
      }
    }
  }
  return theme('table', array(
    'header' => $header,
    'rows' => $rows,
  ));
}