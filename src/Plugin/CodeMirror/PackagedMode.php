<?php

/**
 * @file
 * Contains \Drupal\codemirror\Plugin\CodeMirror\PackagedModes.
 */

namespace Drupal\codemirror\Plugin\CodeMirror;

use Drupal\codemirror\Annotation\CodeMirrorPlugin;
use Drupal\codemirror\CodeMirrorPluginInterface;
use Drupal\codemirror\Discovery\PackagedModeDiscovery;
use Drupal\Core\Annotation\Translation;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Plugin\PluginBase;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Generic mode for all packaged codemirror modes.
 *
 * @CodeMirrorPlugin(
 *   id = "codemirror_mode",
 *   label = @Translation("Mode"),
 *   type = "mode",
 *   deriver = "Drupal\codemirror\Plugin\Derivative\PackagedModes"
 * )
 */
class PackagedMode extends PluginBase implements CodeMirrorPluginInterface, ContainerFactoryPluginInterface {

  protected $id = 'null';
  protected $mime = 'text/plain';
  protected $dependencies = array();

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $container->get('discovery.codemirror.modes.packaged'),
      $configuration,
      $plugin_id,
      $plugin_definition
    );
  }

  /**
   * {@inheritdoc}
   */
  public function __construct(PackagedModeDiscovery $packaged_modes, array $configuration, $plugin_id, $plugin_definition) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    if ($info = $packaged_modes->getBundledMode($plugin_id)) {
      $this->id = $info['id'];
      $this->mime = $info['mime'];
      $this->dependencies = $info['dependencies'];
    }
  }

  /**
   * {@inheritdoc}
   */
  public function buildLibrary() {
    $library = array(
      'version' => 'VERSION',
      'js' => array(),
      'css' => array(),
      'dependencies' => array(),
    );

    if ($this->pluginDefinition['id'] != 'null') {
      $library['js'] = array(
        '/libraries/codemirror/mode/' . $this->id . '/' . $this->id . '.js' => array(),
      );
    }

    if (isset($this->dependencies)) {
      foreach ($this->dependencies as $dep) {
        $library['dependencies'][] = 'codemirror/codemirror.plugin.' . $dep;
      }
    }

    return $library;
  }
}