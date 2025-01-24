<?php

namespace Drupal\present\Element;

use Drupal\advancedqueue_test\Plugin\AdvancedQueue\JobType\Retry;
use Drupal\Core\Render\Attribute\RenderElement;
use Drupal\Core\Render\Element\RenderElementBase;

/**
 * Provides a render element for a Vimeo video player with integraton with the Player SDK.
 */
#[RenderElement('present_vimeo_player')]
class VimeoPlayer extends RenderElementBase {

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
      '#options' => [],
      '#events_to_fire' => [],
      '#attributes' => [],
      '#theme' => 'present_vimeo_player',
      '#attached' => [
        'library' => ['present/vimeo'],
      ],
    ];
  }

  public static function preRender($element) {
    if (!isset($element['#attributes']['class'])) {
      $element['#attributes']['class'] = [];
    }
    $element['#attributes']['class'][] = 'vimeo-player';
    // $element['#attributes']['style'] = "width: 100%; aspect-ratio: 4/3;";
    $element['#attributes']['data-vimeo-options'] = json_encode($element['#options']);
    if (isset($element['#events_to_fire'])) {
      $element['#attributes']['data-vimeo-events'] = json_encode($element['#events_to_fire']); 
    }
    return $element;
  }
}
