<?php

namespace Drupal\present\Plugin\RevealJSPlugin;

interface RevealJSPluginInterface {

  /**
   * Get library associated with the plugin.
   */
  public function getLibraryName(): string;

}