<?php

namespace Drupal\present\Element;

use Drupal\Component\Utility\Html;
use Drupal\Component\Utility\NestedArray;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Render\Attribute\FormElement;
use Drupal\Core\Render\Element\FormElementBase;
use Symfony\Component\Yaml\Yaml;

/**
 * Provides a form element for a slide.
 */
#[FormElement('present_slide')]
class Slide extends FormElementBase {
  const TYPE_RENDER_ARRAY = 'render_array';
  const TYPE_HTML_RAW = 'html';
  /**
   * {@inheritdoc}
   */
  public function getInfo() {
    $class = static::class;
    return [
      '#input' => TRUE,
      '#process' => [
        [$class, 'processSlide'],
      ],
      '#element_validate' => [
        [$class, 'validateSlide'],
      ],
      '#theme_wrappers' => ['fieldset'],
    ];
  }

  /**
   * Processes the slide form element.
   *
   * @param array $element
   *   The form element to process.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current state of the form.
   * @param array $complete_form
   *   The complete form structure.
   *
   * @return array
   *   The processed element.
   *
   * @throws \InvalidArgumentException
   *   Thrown when #field_overrides is malformed.
   */
  public static function processSlide(array &$element, FormStateInterface $form_state, array &$complete_form) {
    $value = $element['#value'];

    $id_prefix = implode('-', $element['#parents']);
    $wrapper_id = Html::getUniqueId($id_prefix . '-ajax-wrapper');

    $element = [
      '#tree' => TRUE,
      '#prefix' => '<div id="' . $wrapper_id . '">',
      '#suffix' => '</div>',
      // Pass the id along to other methods.
      '#wrapper_id' => $wrapper_id,
    ] + $element;
    $element['type'] = [
      '#type' => 'select',
      '#title' => t('Type'),
      '#options' => [
        static::TYPE_RENDER_ARRAY => t('Render Array'),
        static::TYPE_HTML_RAW => t('Raw HTML'),
      ],
      '#default_value' => $element['#default_value']['type'] ?? static::TYPE_RENDER_ARRAY,
      '#description' => t('Select type of content you are entering below. Render Array should be entered in YAML format.'),
      '#ajax' => [
        'callback' => [get_called_class(), 'ajaxRefresh'],
        'wrapper' => $wrapper_id,
      ],
    ];
    $element['content'] = [
      '#type' => 'textarea',
      '#title' => t('Content'),
      '#default_value' => $element['#default_value']['content'],
      '#limit_validation_errors' => [],
    ];
    $type = $value['type'] ?? $element['#default_value']['type'];
    if ($type == static::TYPE_RENDER_ARRAY) {
      // To get support from the https://www.drupal.org/project/yaml_editor module.
      $element['content']['#attributes']['data-yaml-editor'] = 'true';
    }

    $element['auto_animate'] = [
      '#type' => 'checkbox',
      '#title' => t('Auto-Animate'),
      '#default_value' => $element['#default_value']['auto_animate'] ?? FALSE,
      '#limit_validation_errors' => [],
      '#description' => t('Enable Auto-Animate in this slide. Read more about this feature in <a href="https://revealjs.com/auto-animate/">this page</a>.'),
    ];

    $element['auto_animate_id'] = [
      '#type' => 'textfield',
      '#title' => t('Auto-Animate ID'),
      '#default_value' => $element['#default_value']['auto_animate_id'] ?? FALSE,
      '#limit_validation_errors' => [],
      '#description' => t('Enter Auto-Animate ID of this slide. Read more about this feature in <a href="https://revealjs.com/auto-animate/">this page</a>.'),
    ];

    $element['auto_animate_restart'] = [
      '#type' => 'checkbox',
      '#title' => t('Auto-Animate Restart'),
      '#default_value' => $element['#default_value']['auto_animate_restart'] ?? FALSE,
      '#limit_validation_errors' => [],
      '#description' => t('Enable Auto-Animate Restart in this slide. Read more about this feature in <a href="https://revealjs.com/auto-animate/">this page</a>.'),
    ];

    $element['autoslide'] = [
      '#type' => 'textfield',
      '#title' => t('Auto-Slide Druation'),
      '#default_value' => $element['#default_value']['autoslide'] ?? '',
      '#limit_validation_errors' => [],
      '#description' => t('Number of milliseconds to run this slide in Auto-Slide mode. Read more about this feature in <a href="https://revealjs.com/auto-slide/">this page</a>.'),
    ];

    $element['ovrride_revealjs_config_options'] = [
      '#type' => 'details',
      '#title' => t('Override Configuration Options on Events'),
    ];

    $slide_events = static::slideEvents();
    foreach ($slide_events as $event_name => $event_label) {
      $element['ovrride_revealjs_config_options'][$event_name] = [
        '#type' => 'textarea',
        '#title' => t('Override Configuration Options on %event_label Event', ['%event_label' => $event_label]),
        '#default_value' => $element['#default_value']['ovrride_revealjs_config_options'][$event_name] ?? '',
        '#limit_validation_errors' => [],
        '#description' => t('Specify configuration options to be overriden for this slide on %event_label event.', ['%event_label' => $event_label]),
        '#attributes' => [
          'data-yaml-editor' => 'true',
        ],
      ];
      if (!empty($element['#default_value']['ovrride_revealjs_config_options'][$event_name])) {
        $element['ovrride_revealjs_config_options']['#open'] = TRUE;
      }
    }

    return $element;
  }

  /**
   * Form element validation handler for #type 'present_slide'.
   */
  public static function validateSlide(&$element, FormStateInterface $form_state, &$complete_form) {
    $value = $element['#value'];

    if ($value['type'] == static::TYPE_RENDER_ARRAY) {
      try {
        $test = Yaml::parse($value['content']);
      }
      catch (\Symfony\Component\Yaml\Exception\ParseException $e) {
          $form_state->setError(
          $element['content'],
          t(
            'Not in a valid YAML format: %message',
            [
              '%name' => empty($element['#title']) ? $element['#parents'][0] : $element['#title'],
              '%message' => $e->getMessage(),
            ]
          )
        );
      }
    }

    foreach ($value['ovrride_revealjs_config_options'] as $event_name => $config_options_string) {
      try {
        $revealjs_config_options = Yaml::parse($config_options_string);
      }
      catch (\Symfony\Component\Yaml\Exception\ParseException $e) {
        $form_state->setError(
          $element['ovrride_revealjs_config_options'][$event_name],
          t(
            'Not in a valid YAML format: %message',
            [
              '%message' => $e->getMessage(),
            ]
          )
        );
      }
    }
  }

  /**
   * Ajax callback.
   */
  public static function ajaxRefresh(array $form, FormStateInterface $form_state) {
    $triggering_element = $form_state->getTriggeringElement();
    $parents = $triggering_element['#array_parents'];
    array_pop($parents);
    $slide_element = NestedArray::getValue($form, $parents);

    return $slide_element;
  }

  public static function slideEvents() {
    return [
      'slidechanged' => t('Slide Changed'),
      'slidetransitionend' => t('Slide Transition End'),
    ];
  }

}
