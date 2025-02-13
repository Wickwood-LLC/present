<?php

namespace Drupal\present\Plugin\RevealJSPlugin;

interface RevealJSPluginInterface {

  /**
   * Get library associated with the plugin.
   */
  public function getLibraryName(): string;

  /**
   * Manipulate the presentation element before the rendering process.
   */
  public function prenderPresentation($element): array;

}