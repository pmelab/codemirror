<?php
/**
 * @file
 * Wysiwyg integration of codemirror.
 */

/**
 * Implements hook_editor().
 */
function codemirror_codemirror_editor() {
  $editor = array();
  $editor['codemirror'] = array(
    'title' => 'Codemirror',
    'vendor url' => 'http://www.codemirror.net',
    'download url' => 'http://www.codemirror.net/codemirror.zip',
    'library path' => wysiwyg_get_path('codemirror'),
    'libraries' => array(
      '' => array(
        'title' => 'Source',
        'files' => array(),
      ),
    ),
    'version callback' => 'codemirror_version',
    'settings callback' => 'codemirror_settings',
    'settings form callback' => 'codemirror_wysiwyg_settings_form',
    'plugin callback' => 'codemirror_plugins',
    'versions' => array(
      '3' => array(
        'js files' => array('codemirror-3.js'),
      ),
    ),
  );
  return $editor;
}

/**
 * Determine version of installed codemirror library.
 */
function codemirror_version($editor) {
  $package = json_decode(file_get_contents($editor['editor path'] . '/package.json'));
  return $package->version;
}

/**
 * Prepare settings for the codemirror wysiwyg plugin.
 */
function codemirror_settings($editor, $config, $theme) {
  $settings['language'] = $config['codemirror_language'];
  return $settings;
}

/**
 * Alter the editor settings form.
 */
function codemirror_wysiwyg_settings_form(&$form, &$form_state) {
  $settings = $form_state['wysiwyg_profile']->settings;
  $languages = codemirror_languages();

  unset($form['buttons'], $form['appearance'], $form['output'], $form['css']);
  $form['basic']['#access'] = FALSE;

  $form['basic']['default']['#default_value'] = TRUE;
  $form['basic']['show_toggle']['#default_value'] = FALSE;

  $form['codemirror'] = array(
    '#type' => 'fieldset',
    '#title' => t('CodeMirror'),
  );
  $form['codemirror']['codemirror_language'] = array(
    '#type' => 'select',
    '#title' => t('CodeMirror Language'),
    '#options' => array(),
    '#default_value' => array_key_exists('codemirror_language', $settings) ? $settings['codemirror_language'] : 'text/plain',
  );

  foreach ($languages as $mime => $lang) {
    $form['codemirror']['codemirror_language']['#options'][$mime] = $lang['name'];
  }
}