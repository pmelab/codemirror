<?php

/**
 * @file
 * Contains \Drupal\codemirror\YamlThemeDiscovery.
 */

namespace Drupal\codemirror\Discovery;

use Drupal\codemirror\Plugin\CodeMirror\YamlTheme;
use Drupal\Component\Discovery\YamlDiscovery;
use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Extension\ModuleHandler;

class YamlThemeDiscovery {
  protected $cacheBackend;
  protected $moduleHandler;
  protected $themes;

  public function __construct(CacheBackendInterface $cache_backend, ModuleHandler $module_handler) {
    $this->cacheBackend = $cache_backend;
    $this->moduleHandler = $module_handler;
  }

  public function getThemes() {
    if (isset($this->themes)) {
      return $this->themes;
    }

    // Try to fetch data from the cache.
    $this->themes = $this->cacheBackend->get('codemirror.theme.info');

    if (!$this->themes) {
      if (!isset($this->yamlDiscovery)) {
        $this->yamlDiscovery = new YamlDiscovery('codemirror-themes', $this->moduleHandler->getModuleDirectories());
      }
      $data = $this->yamlDiscovery->findAll();
      $this->themes = array();
      foreach ($data as $provider => $themes) {
        foreach ($themes as $id => $theme)  {
          $this->themes[$id] = $theme;
          $this->themes[$id]['id'] = $id;
          $this->themes[$id]['label'] = $this->themes[$id]['name'];
          unset($this->themes[$id]['name']);
        }
      }
    }
    return $this->themes;
  }

  public function getTheme($plugin_id) {
    if (!isset($this->themes)) {
      $this->getThemes();
    }
    list($prefix, $id) = explode(YamlTheme::DERIVATIVE_SEPARATOR, $plugin_id);
    return ($id && array_key_exists($id, $this->themes)) ? $this->themes[$id] : FALSE;
  }

}