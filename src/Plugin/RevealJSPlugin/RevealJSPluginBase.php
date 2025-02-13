<?php

namespace Drupal\present\Plugin\RevealJSPlugin;

abstract class RevealJSPluginBase implements RevealJSPluginInterface {
  /**
   * {@inheritdoc}
   */
  public function prenderPresentation($element): array {
    return $element;
  }
}