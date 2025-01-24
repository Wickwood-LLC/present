<?php

namespace Drupal\present\Element;

use Drupal\Core\Render\Attribute\RenderElement;
use Drupal\Core\Render\Element\RenderElementBase;

/**
 * Provides a render element for a reveal.js presentation.
 */
#[RenderElement('revealjs_presentation')]
class RevealJSPresentation extends RenderElementBase {

  /**
   * {@inheritdoc}
   */
  public function getInfo() {
    $class = static::class;
    return [
      '#process' => [
        // [$class, 'processGroup'],
        // [$class, 'processAjaxForm'],
      ],
      '#pre_render' => [
        [$class, 'preRender'],
      ],
      '#value' => NULL,
      '#attributes' => [],
      // '#theme_wrappers' => ['fieldset'],
      '#theme' => 'revealjs_presentation',
      '#attached' => [
        'library' => ['present/reveal'],
      ],
    ];
  }

  public static function preRender($element) {
    if (!isset($element['#attributes']['class'])) {
      $element['#attributes']['class'] = [];
    }
    $element['#attributes']['class'][] = 'reveal';
    $element['#attributes']['style'] = "width: 100%; aspect-ratio: 4/3;";
    return $element;
  }

}
