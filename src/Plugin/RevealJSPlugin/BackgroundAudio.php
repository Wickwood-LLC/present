<?php

namespace Drupal\present\Plugin\RevealJSPlugin;

use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\present\Attribute\RevealJSPlugin;
use Drupal\present\Plugin\RevealJSPlugin\RevealJSPluginInterface;

#[RevealJSPlugin(
  id: 'background_audio',
  label: new TranslatableMarkup('Background Auido'),
)]
class BackgroundAudio implements RevealJSPluginInterface {

  /**
   * {@inheritdoc}
   */
  public function getLibraryName(): string {
    return 'present/reveal-bg-audio';
  }
}