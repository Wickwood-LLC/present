<?php

namespace Drupal\present\Plugin\RevealJSPlugin;

use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\present\Attribute\RevealJSPlugin;

#[RevealJSPlugin(
  id: 'background_audio',
  label: new TranslatableMarkup('Background Auido'),
  revealjs_plugin_name: 'BackgroundAudio',
)]
class BackgroundAudio extends RevealJSPluginBase {

  /**
   * {@inheritdoc}
   */
  public function getLibraryName(): string {
    return 'present/reveal-bg-audio';
  }

  /**
     * {@inheritdoc}
     */
  public function prenderPresentation($element): array {
    return $element;
  }
}