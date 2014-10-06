<?php

/**
 * @file
 * Contains Drupal\codemirror\Annotation\CodeMirrorPlugin.
 */

namespace Drupal\codemirror\Annotation;

use Drupal\Component\Annotation\Plugin;

/**
 * Defines a CodeMirrorPlugin annotation object.
 *
 * Plugin Namespace: Plugin\CodeMirror
 *
 * @Annotation
 */
class CodeMirrorPlugin extends Plugin {
  /**
   * The plugin ID.
   *
   * @var string
   */
  public $id = '';

  /**
   * The human-readable name of the CodeMirror plugin.
   *
   * @ingroup plugin_translatable
   *
   * @var \Drupal\Core\Annotation\Translation
   */
  public $label ='';

  /**
   * The plugin type. Either 'mode', 'theme', 'keymap' or 'plugin'.
   * @var string
   */
  public $type = '';

  /**
   * Class used to retrieve derivative definitions of this plugin.
   *
   * @var string
   */
  public $deriver = '';
}
