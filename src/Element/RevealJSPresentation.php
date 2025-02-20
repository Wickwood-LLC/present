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
        $override_config_options[$event_name] = Yaml::parse($slide_data['override_revealjs_config_options'][$event_name]);
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
      if (isset($slide_data['autoslide'])) {
        $slide['#attributes']['data-autoslide'] = $slide_data['autoslide'];
      }
      if (!empty($slide_data['transition']['in']) && !empty($slide_data['transition']['out'])) {
        $slide['#attributes']['data-transition'] = $slide_data['transition']['in'] . '-in ' . $slide_data['transition']['out'] . '-out';
      }
      if (!empty($slide_data['transition']['speed'])) {
        $slide['#attributes']['data-transition-speed'] = $slide_data['transition']['speed'];
      }

      $background = $slide_data['background'];
      if (!empty($background['color'])) {
        $slide['#attributes']['data-background-color'] = $background['color'];
      }
      if (!empty($background['gradient'])) {
        $slide['#attributes']['data-background-gradient'] = $background['gradient'];
      }
      if (!empty($background['image'])) {
        $slide['#attributes']['data-background-image'] = $background['image'];
      }
      if (!empty($background['size'])) {
        $slide['#attributes']['data-background-size'] = $background['size'];
      }
      if (!empty($background['position'])) {
        $slide['#attributes']['data-background-position'] = $background['position'];
      }
      if (!empty($background['repeat'])) {
        $slide['#attributes']['data-background-repeat'] = $background['repeat'];
      }
      if (!empty($background['opacity'])) {
        $slide['#attributes']['data-background-opacity'] = $background['opacity'];
      }
      if (!empty($background['interactive'])) {
        $slide['#attributes']['data-backgound-interactive'] = $background['interactive'];
      }
      if (!empty($background['video']['source'])) {
        $slide['#attributes']['data-background-video'] = $background['video']['source'];
      }
      if (!empty($background['video']['loop'])) {
        $slide['#attributes']['data-background-video-loop'] = $background['video']['loop'];
      }
      if (!empty($background['video']['muted'])) {
        $slide['#attributes']['data-background-video-muted'] = $background['video']['muted'];
      }
      if (!empty($background['iframe'])) {
        $slide['#attributes']['data-background-iframe'] = $background['iframe'];
      }
      if (!empty($background['transition'])) {
        $slide['#attributes']['data-background-transition'] = $background['transition'];
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

    $config_options = $presentation->getConfigOptionsArray();

    if (!isset($config_options['plugins'])) {
      $config_options['plugins'] = [];
    }

    /** @var \Drupal\present\Plugin\RevealJSPlugin\RevealJSPluginManager */
    $revealjs_plugin_manager = \Drupal::service('plugin.manager.revealjs_plugins');

    $plugin_libraries = [];
    $plugins = [];
    foreach ($presentation->getPlugins() as $plugin_id) {
      /** @var \Drupal\present\Plugin\RevealJSPlugin\RevealJSPluginInterface $plugin */
      // $plugin_def = $revealjs_plugin_manager->getDefinition($plugin_id);
      $plugin = $revealjs_plugin_manager->getPlugin($plugin_id, $presentation);
      $plugin_libraries[] = $plugin->getLibraryName();
      $config_options['plugins'][] = $plugin->getRevealJSPluginName();
      $plugins[$plugin_id] = $plugin;
    }

    foreach ($plugins as $plugin) {
      /** @var \Drupal\present\Plugin\RevealJSPlugin\RevealJSPluginInterface $plugin */
      $plugin->alterRevealJSConfig($config_options);
    }

    $element['#config_options'] = json_encode(['embedded' => TRUE] + $config_options);

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

    $element['#attached']['library'] = array_merge($plugin_libraries, $element['#attached']['library']);

    $element['#attributes']['data-config-options'] = $element['#config_options'];

    foreach ($plugins as $plugin) {
      /** @var \Drupal\present\Plugin\RevealJSPlugin\RevealJSPluginInterface $plugin */
      $element = $plugin->prenderPresentation($element);
    }
    return $element;
  }

}
