<?php

/**
 * @file
 * Contains \Drupal\codemirror\PackagedModeDiscovery.
 */

namespace Drupal\codemirror\Discovery;

use Drupal;
use Drupal\codemirror\Plugin\CodeMirror\PackagedMode;
use Drupal\Component\Serialization\Json;
use Drupal\Core\Cache\CacheBackendInterface;

class PackagedModeDiscovery {

  /**
   * @var \Drupal\Core\Cache\CacheBackendInterface
   */
  protected $cacheBackend;

  /**
   * @var mixed
   */
  protected $bundledModes;

  /**
   * @param $cache \Drupal\Core\Cache\CacheBackendInterface
   *   The cache backend to use for discovered modes.
   */
  function __construct(CacheBackendInterface $cache) {
    $this->cacheBackend = $cache;
  }

  /**
   * Retrieve information about modes that come pre-packaged with codemirror.
   *
   * @return mixed
   *   Array of mode information. Keyed by mode id, containing the following
   *   fields:
   *     - label: a human readable label
   *     - mime: the mime-type of this mode
   *     - depedencies: array of modes this mode depends on
   */
  public function getBundledModes() {

    // Early return if bundledModes are already initialized.
    if (isset($this->bundledModes)) {
      return $this->bundledModes;
    }

    // Try to fetch data from the cache.
    $this->bundledModes = $this->cacheBackend->get('codemirror.mode.info');

    // Cache is empty, read from files.
    if (!$this->bundledModes) {
      /* @var \Drupal\summoner\LibraryManager $libraries */
      $libraries = Drupal::service('summoner.libraries');
      $path = $libraries->getLibrary('codemirror')->getPath();

      // Parse list of available modes from meta.js
      $meta = file_get_contents($path .'/mode/meta.js');
      $modes = array();
      preg_match('/CodeMirror\.modeInfo\s=\s(\[.*\])/us', $meta, $modes);

      // Clean up property identifiers and parse Json.
      $data = Json::decode(preg_replace('/([a-z]+)\:/', '"$1":', $modes[1]));


      foreach ($data as $mode) {
        $this->bundledModes[$mode['mode']] = array(
          'id' => $mode['mode'],
          'label' => $mode['name'],
          'mime' => $mode['mime'],
          'dependencies' => array(),
        );

        // Early abort, if mode is 'null'. Used for plaintext and has no actual
        // javascript file.
        if ($mode['mode'] == 'null') {
          continue;
        }

        // Search for require() calls to other modes.
        $file = file_get_contents($path . '/mode/' . $mode['mode'] . '/' . $mode['mode'] . '.js');
        $requires = array();
        preg_match_all('/require\(\"\.\.\/([a-z]+)\/([a-z]+)/', $file, $requires, PREG_OFFSET_CAPTURE);
        if (count($requires[1]) > 0) {
          foreach ($requires[1] as $require) {
            $this->bundledModes[$mode['mode']]['dependencies'][] = $require[0];
          }
        }
      }

      $this->cacheBackend->set('codemirror.bundled.mode', $this->bundledModes, CacheBackendInterface::CACHE_PERMANENT);
    }

    return $this->bundledModes;
  }

  /**
   * Get information about a specific mode.
   *
   * @param $plugin_id string
   *   The plugin id.
   * @return mixed
   *   The info array about this mode or FALSE if none is found.
   */
  public function getBundledMode($plugin_id) {
    if (isset($this->bundledModes)) {
      $this->getBundledModes();
    }
    list($prefix, $id) = explode(PackagedMode::DERIVATIVE_SEPARATOR, $plugin_id);
    return ($id && array_key_exists($id, $this->bundledModes)) ? $this->bundledModes[$id] : FALSE;
  }
}