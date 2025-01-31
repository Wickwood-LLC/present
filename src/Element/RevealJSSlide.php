<?php

namespace Drupal\present\Element;

use Drupal\Core\Render\Attribute\RenderElement;
use Drupal\Core\Render\Element\RenderElementBase;

/**
 * Provides a render element for a reveal.js slide.
 */
#[RenderElement('revealjs_slide')]
class RevealJSSlide extends RenderElementBase {

  /**
   * {@inheritdoc}
   */
  public function getInfo() {
    $class = static::class;
    return [
      '#content' => '',
      '#theme' => 'revealjs_slide',
    ];
  }

}
