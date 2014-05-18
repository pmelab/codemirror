<?php
/**
 * @file
 * Form element to map codemirror modes with according settings.
 */

/**
 * Process the mode form element.
 */
function codemirror_config_element_process($element, $form_state, $complete_form) {
  $id = $element['#id'];
  $override = array_key_exists('#override', $element) && ($element['#override'] != FALSE);

  $themes = codemirror_themes();
  $theme_options = array();
  foreach ($themes as $name => $theme) {
    $theme_options[$name] = $theme['name'];
  }

  $languages = codemirror_languages();
  $language_options = array();
  foreach ($languages as $key => $language) {
    $language_options[$key] = $language['name'];
  }

  $keymaps = codemirror_keymaps();
  $keymap_options = array();
  foreach ($keymaps as $key => $map) {
    $keymap_options[$key] = $map['name'];
  }

  $element = array(
    '#tree' => TRUE,
    '#prefix' => '<div id="' . $id . '-ajax-wrapper">',
    '#suffix' => '</div>',
    '#theme' => 'codemirror_settings_table',
  ) + $element;

  $table = &$element;
  $table['header'] = array(
    // Index language element.
    'language' => array('#markup' => t('Language')),

    // Display settings.
    'theme' => array('#markup' => t('Theme')),
    'font' => array('#markup' => t('Font')),
    'fontsize' => array('#markup' => t('Font size')),
    'lineheight' => array('#markup' => t('Line height')),

    // Functional settings.
    'smartIndent' => array('#markup' => t('Smart indent')),
    'lineWrapping' => array('#markup' => t('Wrap lines')),
    'lineNumbers' => array('#markup' => t('Line numbers')),
    'indentUnit' => array('#markup' => t('Indent size')),
    'tabSize' => array('#markup' => t('Tab size')),
    'keyMap' => array('#markup' => t('Key bindings')),

    // Action buttons.
    'actions' => array(
      '#markup' => t('Actions'),
      '#access' => !$override,
    ),
  );
  $available = array_keys($table['header']);
  if (array_key_exists('#available_settings', $element)) {
    $available = $element['#available_settings'];
  }
  foreach (array_keys($table['header']) as $key) {
    if (in_array($key, array('language', 'actions'))) {
      continue;
    }
    $table['header'][$key]['#access'] = in_array($key, $available);
  }

  $settings = codemirror_config_element_values($element, $form_state);

  if (!$override) {
    $settings['_new_'] = $settings['_default_'];
  }

  foreach ($settings as $key => $set) {
    if (array_key_exists($key, $language_options)) {
      $label = $language_options[$key];
    }
    else {
      $label = t('Default');
    }
    $table[$key] = array(
      'language' => array(
        'fixed' => array(
          '#markup' => $label,
          '#access' => $key != '_new_' || $override,
        ),
        'choose' => array(
          '#type' => 'select',
          '#options' => $language_options,
          '#access' => $key == '_new_' && !$override,
        ),
      ),
      'theme' => array(
        '#type' => 'select',
        '#options' => $theme_options,
      ),
      'font' => array(
        '#type' => 'select',
        '#options' => codemirror_fonts(),
      ),
      'fontsize' => array(
        '#type' => 'textfield',
        '#size' => 2,
        '#field_suffix' => 'px',
        '#maxlength' => 2,
      ),
      'lineheight' => array(
        '#type' => 'textfield',
        '#size' => 2,
        '#field_suffix' => 'px',
        '#maxlength' => 2,
      ),
      'smartIndent' => array(
        '#type' => 'checkbox',
      ),
      'lineWrapping' => array(
        '#type' => 'checkbox',
      ),
      'lineNumbers' => array(
        '#type' => 'checkbox',
      ),
      'indentUnit' => array(
        '#type' => 'textfield',
        '#size' => 2,
        '#maxsize' => 2,
      ),
      'tabSize' => array(
        '#type' => 'textfield',
        '#size' => 2,
        '#maxsize' => 2,
      ),
      'keyMap' => array(
        '#type' => 'select',
        '#options' => $keymap_options,
      ),
      'actions' => array(
        '#access' => !$override,
        'remove' => array(
          '#type' => 'submit',
          '#value' => t('Remove'),
          '#access' => !in_array($key, array('_default_', '_new_')) && !$override,
          '#name' => "{$id}--remove",
          '#remove_language' => $key,
          '#ajax' => array(
            'wrapper' => "{$id}-ajax-wrapper",
            'method' => 'replace',
            'callback' => 'codemirror_theme_element_ajax',
            'effect' => 'none',
            'progress' => array(
              'type' => 'throbber',
              'message' => FALSE,
            ),
          ),
        ),
        'add' => array(
          '#type' => 'submit',
          '#value' => t('Add'),
          '#access' => ($key == '_new_') && !$override,
          '#name' => "{$id}--add",
          '#add_language' => $key,
          '#ajax' => array(
            'wrapper' => "{$id}-ajax-wrapper",
            'method' => 'replace',
            'callback' => 'codemirror_theme_element_ajax',
            'effect' => 'none',
            'progress' => array(
              'type' => 'throbber',
              'message' => FALSE,
            ),
          ),
        ),
      ),
    );
    if ($override) {
      unset($table[$key]['actions']['add']['#ajax']);
      unset($table[$key]['actions']['remove']['#ajax']);
    }
    foreach (array_keys($table[$key]) as $field) {
      if (in_array($field, array('language', 'actions'))) {
        continue;
      }
      if (!in_array($field, $available)) {
        unset($table[$key][$field]);
      }
      else {
        $table[$key][$field]['#default_value'] = @$set[$field];
      }
    }
  }
  return $element;
}

/**
 * Retrieve the form elements values.
 */
function codemirror_config_element_values($element, $form_state) {
  $values = $form_state['values'];
  $parents = $element['#parents'];
  while ($parent = array_shift($parents)) {
    $values = $values[$parent];
  }
  return $values;
}

/**
 * Ajax callback for add/remove buttons.
 */
function codemirror_theme_element_ajax($form, &$form_state) {
  $parents = $form_state['triggering_element']['#array_parents'];
  $parents = array_splice($parents, 0, count($parents) - 3);
  $element = $form;
  drupal_set_message(t('Changes will not be permanent until you save the form.'), 'warning');
  while ($parent = array_shift($parents)) {
    $element = $element[$parent];
  }
  return $element;
}

/**
 * Set form elements values.
 */
function codemirror_config_element_validate(&$element, &$form_state) {
  $values = codemirror_config_element_values($element, $form_state);
  if (array_key_exists('#remove_language', $form_state['triggering_element'])) {
    $remove = $form_state['triggering_element']['#remove_language'];
    unset($values[$remove]);
    $form_state['rebuild'] = TRUE;
  }
  if (array_key_exists('#add_language', $form_state['triggering_element'])) {
    $lang = $values['_new_']['language']['choose'];
    $values[$lang] = array_replace_recursive($element['#default_value']['_default_'], $values['_new_']);
    unset($values[$lang]['language']);
    $form_state['rebuild'] = TRUE;
  }
  unset($values['_new_']);
  foreach (array_keys($values) as $lang) {
    unset($values[$lang]['language']);
    unset($values[$lang]['actions']);
    $default = $element['#default_value']['_default_'];
    if (array_key_exists($lang, $element['#default_value'])) {
      $default = $element['#default_value'][$lang];
    }
    foreach ($default as $key => $value) {
      if (!array_key_exists($key, $values[$lang]) && in_array($key, $element['#available_settings'])) {
        $values[$lang][$key] = $value;
      }
    }
  }
  form_set_value($element, $values, $form_state);
}
