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
      '#pre_render' => [
        [$class, 'preRender'],
      ],
      '#options' => [],
      '#attributes' => [],
      '#theme' => 'revealjs_presentation',
      '#attached' => [
        'library' => ['present/reveal'],
      ],
    ];
  }

  public static function revealThemes() {
    return [
      'black' => t('Black'),
      'white' => t('White'),
      'league' => t('League'),
      'beige' => t('Beige'),
      'night' => t('Night'),
      'serif' => t('Serif'),
      'simple' => t('Simple'),
      'solarized' => t('Solarized'),
      'moon' => t('Moon'),
      'dracula' => t('Dracula'),
      'sky' => t('Sky'),
      'blood' => t('Blood'),
    ];
  }

  public static function preRender($element) {
    if (!isset($element['#attributes']['class'])) {
      $element['#attributes']['class'] = [];
    }
    $element['#attributes']['class'][] = 'reveal';
    $theme = $element['#options']['theme'] ?? \Drupal::config('present.settings')->get('revealjs_theme');
    if (!in_array($theme, array_keys(static::revealThemes()))) {
      $theme = 'black';
    }
    $element['#attached']['library'][] = 'present/reveal-theme-' . $theme;
    return $element;
  }

}
