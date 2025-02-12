<?php

namespace Drupal\present\Plugin\RevealJSPlugin;

use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\present\Attribute\RevealJSPlugin;

#[RevealJSPlugin(
  id: 'background_audio',
  label: new TranslatableMarkup('Background Auido'),
)]
class BackgroundAudio extends RevealJSPlugin {

  /**
   * {@inheritdoc}
   */
  public function getLibraryName(): string {
    return 'present/reveal-bg-audio';
  }
}