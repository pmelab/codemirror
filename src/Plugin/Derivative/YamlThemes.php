<?php

/**
 * @file
 * Contains \Drupal\codemirror\Plugin\Derivative\PackagedModes.
 */


namespace Drupal\codemirror\Plugin\Derivative;

use Drupal\codemirror\Discovery\YamlThemeDiscovery;
use Drupal\Component\Plugin\Derivative\DeriverBase;
use Drupal\Core\Plugin\Discovery\ContainerDeriverInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Dynamically defines derivatives for built in codemirror modes.
 */
class YamlThemes extends DeriverBase implements ContainerDeriverInterface {

  /**
   * @var \Drupal\codemirror\Discovery\YamlThemeDiscovery
   */
  protected $themeDiscovery;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, $base_plugin_id) {
    return new static($container->get('discovery.codemirror.themes.yaml'));
  }

  /**
   * {@inheritdoc}
   */
  function __construct(YamlThemeDiscovery $theme_discovery) {
    $this->themeDiscovery = $theme_discovery;
  }

  /**
   * {@inheritdoc}
   */
  public function getDerivativeDefinitions($base_plugin_definition) {
    $themes = $this->themeDiscovery->getThemes();
    foreach ($themes as $id => $theme) {
      $this->derivatives[$id] = array(
          'id' => $id,
          'label' => $theme['label'],
        ) + $base_plugin_definition;
    }
    return parent::getDerivativeDefinitions($base_plugin_definition);
  }

}
