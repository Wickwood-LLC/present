<?php

namespace Drupal\present\Plugin\RevealJSPlugin;

use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Plugin\DefaultPluginManager;
use Drupal\present\Attribute\RevealJSPlugin;

/**
 * Provides a plugin manager for RevealJS plugins.
 *
 * @see \Drupal\present\Attribute\RevealJSPlugin
 * @see \Drupal\present\Plugin\RevealJSPlugin\RevealJSPlugin
 * @see plugin_api
 */
class RevealJSPluginManager extends DefaultPluginManager {

  /**
   * Constructs an RevealJSPluginManager object.
   *
   * @param \Traversable $namespaces
   *   An object that implements \Traversable which contains the root paths
   *   keyed by the corresponding namespace to look for plugin implementations.
   * @param \Drupal\Core\Cache\CacheBackendInterface $cache_backend
   *   Cache backend instance to use.
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $module_handler
   *   The module handler to invoke the alter hook with.
   */
  public function __construct(\Traversable $namespaces, CacheBackendInterface $cache_backend, ModuleHandlerInterface $module_handler) {
    parent::__construct('Plugin/RevealJSPlugin', $namespaces, $module_handler, 'Drupal\present\Plugin\RevealJSPlugin\RevealJSPluginInterface', RevealJSPlugin::class);
    $this->alterInfo('revealjs_plugin');
    $this->setCacheBackend($cache_backend, 'revealjs_plugin_info');
  }

  /**
   * Provide option array to use in checkboxes and select form elements.
   */
  public function options(): array {
    $plugins = $this->getDefinitions();
    $options = [];

    foreach ($plugins as $plugin) {
      $options[$plugin['id']] = $plugin['label'];
    }
    return $options;
  }
}
