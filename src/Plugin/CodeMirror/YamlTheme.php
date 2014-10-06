<?php

/**
 * @file
 * Contains \Drupal\codemirror\Plugin\CodeMirror\YamlTheme.
 */

namespace Drupal\codemirror\Plugin\CodeMirror;

use Drupal\codemirror\Annotation\CodeMirrorPlugin;
use Drupal\codemirror\CodeMirrorPluginInterface;
use Drupal\codemirror\Discovery\YamlThemeDiscovery;
use Drupal\Component\Plugin\PluginBase;
use Drupal\Core\Annotation\Translation;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Generic theme for all Yaml declared codemirror themes.
 *
 * @CodeMirrorPlugin(
 *   id = "codemirror_theme",
 *   label = @Translation("Theme"),
 *   type = "theme",
 *   deriver = "Drupal\codemirror\Plugin\Derivative\YamlThemes"
 * )
 */
class YamlTheme extends PluginBase implements CodeMirrorPluginInterface, ContainerFactoryPluginInterface {
  protected $id;
  protected $files;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $container->get('discovery.codemirror.themes.yaml'),
      $configuration,
      $plugin_id,
      $plugin_definition
    );
  }

  public function __construct(YamlThemeDiscovery $themes, $configuration, $plugin_id, $plugin_definition) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    if ($info = $themes->getTheme($plugin_id)) {
      $this->id = $info['id'];
      $this->files = $info['files'];
    }
  }


  /**
   * Build the library array for this codemirror plugin.
   *
   * @return mixed
   *   An array containing a library definition that can be used in #attached.
   */
  public function buildLibrary() {
    $library = array(
      'version' => 'VERSION',
      'js' => array(),
      'css' => array(),
      'dependencies' => array(),
    );

    foreach ($this->files as $file) {
      $library['css']['base'][$file] = array();
    }

    return $library;
  }
}
