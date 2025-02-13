<?php

namespace Drupal\present\Plugin\RevealJSPlugin;

use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\present\Attribute\RevealJSPlugin;

#[RevealJSPlugin(
  id: 'markdown',
  label: new TranslatableMarkup('Markdown'),
  revealjs_plugin_name: 'RevealMarkdown',
)]
class Markdown extends RevealJSPluginBase {

  /**
   * {@inheritdoc}
   */
  public function getLibraryName(): string {
    return 'present/reveal-markdown';
  }

  /**
     * {@inheritdoc}
     */
  public function prenderPresentation($element): array {
    foreach ($element['#slides'] as &$slide) {
      $slide['#attributes']['data-markdown'] = TRUE;
    }
    return $element;
  }
}