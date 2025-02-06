<?php

namespace Drupal\present\Element;

use Drupal\Core\Render\Attribute\RenderElement;
use Drupal\Core\Render\Element\RenderElementBase;
use Drupal\Core\Render\Markup;
use Drupal\present\Entity\Presentation;
use Exception;
use Symfony\Component\Yaml\Yaml;

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
      '#presentation' => NULL,
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
    $presentation = $element['#presentation'];
    if (is_string($presentation)) {
      $presentation = Presentation::load($presentation);
    }

    if (!$presentation) {
      throw new Exception(t('A valid presentation object is required'));
    }

    /** @var \Drupal\present\Entity\Presentation $presentation */

    $slides = [];
    $slide_number = 0;
    foreach ($presentation->getSlides() as  $slide_data) {
      // Slide number to be starting from 1.
      $slide_number++;

      $override_config_options = [];
      foreach (Slide::slideEvents() as $event_name => $event_label) {
        $override_config_options[$event_name] = Yaml::parse($slide_data['ovrride_revealjs_config_options'][$event_name]);
      }

      $slide = [
        '#type' => 'revealjs_slide',
        '#attributes' => [
          'data-config-options' => json_encode($override_config_options),
          'data-slide-number' => $slide_number,
        ],
      ];
      if ($slide_data['auto_animate']) {
        $slide['#attributes']['data-auto-animate'] = TRUE;
      }
      if (!empty($slide_data['auto_animate_id'])) {
        $slide['#attributes']['data-auto-animate-id'] = $slide_data['auto_animate_id'];
      }
      if ($slide_data['auto_animate_restart']) {
        $slide['#attributes']['data-auto-animate-restart'] = TRUE;
      }
      if ($slide_data['type'] == Slide::TYPE_RENDER_ARRAY) {
        $slide['#content'] = Yaml::parse($slide_data['content']);
      }
      else {
        $slide['#content'] = [
          '#markup' => Markup::create($slide_data['content']),
        ];
      }
      $slides[] = $slide;
    }

    $element['#slides'] = $slides;

    $reveal_theme = \Drupal::request()->query->get('theme');

    if (!$reveal_theme) {
      $reveal_theme = $presentation->getTheme();
      if ($reveal_theme == '__none') {
        $reveal_theme = NULL;
      }
    }

    $theme = $element['#options']['theme'] = $reveal_theme;

    $element['#cache']['contexts'][] = 'url.query_args:theme';
    $element['#cache']['tags'][] = $presentation->getEntityTypeId() . ':' . $presentation->id();

    $element['#config_options'] = json_encode(['embedded' => TRUE] + $presentation->getConfigOptionsArray());

    if (!isset($element['#attributes']['class'])) {
      $element['#attributes']['class'] = [];
    }
    $element['#attributes']['class'][] = 'reveal';
    $theme = $element['#options']['theme'] ?? \Drupal::config('present.settings')->get('revealjs_theme');
    if (!in_array($theme, array_keys(static::revealThemes()))) {
      $theme = 'black';
    }

    $element['#attributes']['data-events-to-track'] = json_encode(array_values(array_filter($presentation->getEventsToTrack())));
    $element['#attributes']['data-presentation-id'] = $presentation->id();

    $element['#attached']['library'][] = 'present/reveal-theme-' . $theme;

    $element['#attributes']['data-config-options'] = $element['#config_options'];
    return $element;
  }

}
