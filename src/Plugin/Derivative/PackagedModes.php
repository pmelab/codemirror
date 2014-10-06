<?php

/**
 * @file
 * Contains \Drupal\codemirror\Plugin\Derivative\PackagedModes.
 */


namespace Drupal\codemirror\Plugin\Derivative;

use Drupal\codemirror\Discovery\PackagedModeDiscovery;
use Drupal\Component\Plugin\Derivative\DeriverBase;
use Drupal\Core\Plugin\Discovery\ContainerDeriverInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Dynamically defines derivatives for built in codemirror modes.
 */
class PackagedModes extends DeriverBase implements ContainerDeriverInterface {

  /**
   * @var \Drupal\codemirror\Discovery\PackagedModeDiscovery
   */
  protected $modeDiscovery;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, $base_plugin_id) {
    return new static($container->get('discovery.codemirror.modes.packaged'));
  }

  /**
   * {@inheritdoc}
   */
  function __construct(PackagedModeDiscovery $mode_discovery) {
    $this->modeDiscovery = $mode_discovery;
  }

  /**
   * {@inheritdoc}
   */
  public function getDerivativeDefinitions($base_plugin_definition) {
    $modes = $this->modeDiscovery->getBundledModes();
    foreach ($modes as $id => $mode) {
      $this->derivatives[$id] = array(
        'id' => $id,
        'label' => $mode['label'],
      ) + $base_plugin_definition;
    }
    return parent::getDerivativeDefinitions($base_plugin_definition);
  }

}
