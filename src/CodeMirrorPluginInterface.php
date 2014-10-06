<?php

/**
 * @file
 * Contains \Drupal\codemirror\CodeMirrorModePluginInterface.
 */

namespace Drupal\codemirror;

use Drupal\Component\Plugin\PluginInspectionInterface;

/**
 * Defines the interface for loading a CodeMirror language mode.
 */
interface CodeMirrorPluginInterface extends PluginInspectionInterface {
  /**
   * Build the library array for this codemirror plugin.
   *
   * @return mixed
   *   An array containing a library definition that can be used in #attached.
   */
  public function buildLibrary();
}
