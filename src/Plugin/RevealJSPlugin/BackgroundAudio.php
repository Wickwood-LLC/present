<?php

namespace Drupal\present\Plugin\RevealJSPlugin;

use Drupal\present\Attribute\RevealJSPlugin;
use Drupal\present\Plugin\RevealJSPlugin\RevealJSPluginInterface;

#[RevealJSPlugin("background_audio")]
class BackgroundAudio implements RevealJSPluginInterface {

  /**
   * {@inheritdoc}
   */
  public function getLibraryName(): string {
    return 'present/reveal-bg-audio';
  }
}