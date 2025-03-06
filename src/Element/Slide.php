<?php

namespace Drupal\present\Element;

use Drupal\Component\Utility\Html;
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

    $config = \Drupal::config('present.settings');

    $id_prefix = implode('-', $element['#parents']);
    $wrapper_id = Html::getUniqueId($id_prefix . '-ajax-wrapper');
    $parents = $element['#parents'];
    $state_parent_name = array_shift($parents);
    $state_parent_name .= '[' . implode('][', $parents) . ']';

    $element = [
      '#tree' => TRUE,
      '#prefix' => '<div id="' . $wrapper_id . '">',
      '#suffix' => '</div>',
      // Pass the id along to other methods.
      '#wrapper_id' => $wrapper_id,
    ] + $element;
    $element['type'] = [
      '#type' => 'hidden',
      '#value' => $element['#default_value']['type'],
    ];

    $type = $value['type'] ?? $element['#default_value']['type'];
    if ($type == static::TYPE_HTML_RAW) {
      $element['content'] = [
        '#type' => 'text_format',
        '#title' => t('Content'),
        '#format' => $element['#default_value']['content']['format'],
        // '#allowed_formats' => [$config->get('slide_text_format')],
        '#default_value' => $element['#default_value']['content']['value'],
        '#limit_validation_errors' => [],
        '#description' => t('Enter HTML of slide content.'),
      ];
    }
    if ($type == static::TYPE_RENDER_ARRAY) {
      $element['content']['value'] = [
        '#type' => 'textarea',
        '#title' => t('Content'),
        '#default_value' => $element['#default_value']['content']['value'],
        '#limit_validation_errors' => [],
        '#description' => t('Enter Render array in YAML format of slide content.'),
      ];
      // To get support from the https://www.drupal.org/project/yaml_editor module.
      $element['content']['#attributes']['data-yaml-editor'] = 'true';
    }

    $element['auto_animate'] = [
      '#type' => 'details',
      '#title' => t('Auto-Animate'),
      '#open' => $element['#default_value']['auto_animate']['enabled'] ?? FALSE,
    ];

    $element['auto_animate']['help'] = [
      '#type' => 'html_tag',
      '#tag' => 'p',
      '#value' => t('Read more about this feature at: <a href="https://revealjs.com/auto-animate/" target="_blank">https://revealjs.com/auto-animate/</a>.'),
    ];

    $element['auto_animate']['enabled'] = [
      '#type' => 'checkbox',
      '#title' => t('Enabled'),
      '#default_value' => $element['#default_value']['auto_animate']['enabled'] ?? FALSE,
      '#limit_validation_errors' => [],
      '#description' => t('Enable Auto-Animate in this slide.'),
    ];

    $auto_animate_states = [
      'visible' => [
        ':input[name="' . $state_parent_name . '[auto_animate][enabled]"]' => ['checked' => TRUE],
      ],
    ];

    $element['auto_animate']['easing'] = [
      '#type' => 'textfield',
      '#title' => t('Easing'),
      '#default_value' => $element['#default_value']['auto_animate']['easing'] ?? '',
      '#limit_validation_errors' => [],
      '#description' => t('Enter Auto-Animate Easing of this slide.'),
      '#states' => $auto_animate_states,
    ];

    $element['auto_animate']['unmatched'] = [
      '#type' => 'checkbox',
      '#title' => t('Unmatched'),
      '#default_value' => $element['#default_value']['auto_animate']['unmatched'] ?? FALSE,
      '#limit_validation_errors' => [],
      '#description' => t('Enable or disable Auto-Animate Unmatched in this slide.'),
      '#states' => $auto_animate_states,
    ];

    $element['auto_animate']['duration'] = [
      '#type' => 'number',
      '#title' => t('Duration'),
      '#default_value' => $element['#default_value']['auto_animate']['duration'] ?? 1.0,
      '#min' => 0.1,
      '#step' => 0.1,
      '#limit_validation_errors' => [],
      '#description' => t('Enter Auto-Animate Duration of this slide.'),
      '#states' => $auto_animate_states,
    ];

    $element['auto_animate']['delay'] = [
      '#type' => 'number',
      '#title' => t('Delay'),
      '#default_value' => $element['#default_value']['auto_animate']['delay'] ?? 0.0,
      '#min' => 0.0,
      '#step' => 0.1,
      '#limit_validation_errors' => [],
      '#description' => t('Enter Auto-Animate Delay of this slide.'),
      '#states' => $auto_animate_states,
    ];

    $element['auto_animate']['id'] = [
      '#type' => 'textfield',
      '#title' => t('ID'),
      '#default_value' => $element['#default_value']['auto_animate']['id'] ?? '',
      '#limit_validation_errors' => [],
      '#description' => t('Enter Auto-Animate ID of this slide.'),
      '#states' => $auto_animate_states,
    ];

    $element['auto_animate']['restart'] = [
      '#type' => 'checkbox',
      '#title' => t('Restart'),
      '#default_value' => $element['#default_value']['auto_animate']['restart'] ?? FALSE,
      '#limit_validation_errors' => [],
      '#description' => t('Enable Auto-Animate Restart in this slide.'),
      '#states' => $auto_animate_states,
    ];

    $element['autoslide'] = [
      '#type' => 'textfield',
      '#title' => t('Auto-Slide Duration'),
      '#default_value' => $element['#default_value']['autoslide'] ?? '',
      '#limit_validation_errors' => [],
      '#description' => t('Number of milliseconds to run this slide in Auto-Slide mode. Read more about this feature in <a href="https://revealjs.com/auto-slide/">this page</a>.'),
    ];

    $element['transition'] = [
      '#type' => 'details',
      '#title' => t('Content Transition'),
      '#open' => TRUE,
    ];

    $element['transition']['help'] = [
      '#description' => t('Select transition effect and speed for this slide. Please read documentation for more <a href="https://revealjs.com/transitions/" target="_blank">options</a>.'),
    ];

    $transitions = [
      'none' => t('None'),
      'fade' => t('Fade'),
      'slide' => t('Slide'),
      'convex' => t('Convex'),
      'concave' => t('Concave'),
      'zoom' => t('Zoom'),
    ];

    $element['transition']['in'] = [
      '#type' => 'select',
      '#title' => t('Transition In'),
      '#default_value' => $element['#default_value']['transition']['in'] ?? 'fade',
      '#options' => $transitions,
      '#description' => t('Select transition in effect for this slide.'),
    ];

    $element['transition']['out'] = [
      '#type' => 'select',
      '#title' => t('Transition Out'),
      '#default_value' => $element['#default_value']['transition']['out'] ?? 'fade',
      '#options' => $transitions,
      '#description' => t('Select transition out effect for this slide.'),
    ];

    $element['transition']['speed'] = [
      '#type' => 'select',
      '#title' => t('Transition Speed'),
      '#default_value' => $element['#default_value']['transition']['speed'] ?? 'default',
      '#options' => [
        'default' => t('Default'),
        'fast' => t('Fast'),
        'slow' => t('Slow'),
      ],
      '#description' => t('Select transition speed for this slide.'),
    ];

    $element['background'] = [
      '#type' => 'details',
      '#title' => t('Background'),
    ];
    $element['background']['color_enabled'] = [
      '#type' => 'checkbox',
      '#title' => t('Enable Background Color'),
      '#default_value' => $element['#default_value']['background']['color_enabled'] ?? FALSE,
      '#description' => t('Check this box to enable background color for this slide.'),
    ];
    $element['background']['color'] = [
      '#type' => 'color',
      '#title' => t('Color'),
      '#default_value' => $element['#default_value']['background']['color'] ?? '#ffffff',
      '#description' => t('Select background color for this slide.'),
      '#states' => [
        'visible' => [
          ':input[name="' . $state_parent_name . '[background][color_enabled]"]' => ['checked' => TRUE],
        ],
      ],
    ];
    $element['background']['gradient'] = [
      '#type' => 'textfield',
      '#title' => t('Gradient'),
      '#default_value' => $element['#default_value']['background']['gradient'] ?? NULL,
      '#description' => t('Enter background gradient for this slide. You may use online tool like <a href="https://cssgradient.io/">https://cssgradient.io/</a> to generate the code to use here.'),
    ];
    $element['background']['image'] = [
      '#type' => 'textfield',
      '#title' => t('Image'),
      '#default_value' => $element['#default_value']['background']['image'] ?? '',
      '#description' => t('Enter background image URL for this slide.'),
    ];
    $element['background']['size'] = [
      '#type' => 'textfield',
      '#title' => t('Size'),
      '#default_value' => $element['#default_value']['background']['size'] ?? '',
      '#description' => t('Enter background size for this slide.'),
    ];
    $element['background']['position'] = [
      '#type' => 'textfield',
      '#title' => t('Position'),
      '#default_value' => $element['#default_value']['background']['position'] ?? '',
      '#description' => t('Enter background position for this slide.'),
    ];
    $element['background']['repeat'] = [
      '#type' => 'textfield',
      '#title' => t('Repeat'),
      '#default_value' => $element['#default_value']['background']['repeat'] ?? '',
      '#description' => t('Enter background repeat for this slide.'),
    ];
    $element['background']['opacity'] = [
      '#type' => 'number',
      '#title' => t('Opacity'),
      '#default_value' => $element['#default_value']['background']['opacity'] ?? '',
      '#description' => t('Enter background opacity for this slide.'),
      '#min' => 0,
      '#max' => 1,
      '#step' => 0.01,
    ];
    $element['background']['interactive'] = [
      '#type' => 'checkbox',
      '#title' => t('Interactive'),
      '#default_value' => $element['#default_value']['background']['interactive'] ?? false,
      '#description' => t('Make the background interactive. Usually usefor iframe backgrounds.'),
    ];

    $element['background']['video'] = [
      '#type' => 'details',
      '#title' => t('Video'),
      '#open' => TRUE,
    ];

    $element['background']['video']['source'] = [
      '#type' => 'textfield',
      '#title' => t('Video'),
      '#default_value' => $element['#default_value']['background']['video'] ?? '',
      '#description' => t('Enter background video URL for this slide.'),
    ];
    $element['background']['video']['loop'] = [
      '#type' => 'checkbox',
      '#title' => t('Loop'),
      '#default_value' => $element['#default_value']['background']['video']['loop'] ?? false,
      '#description' => t('Loop the video.'),
    ];
    $element['background']['video']['muted'] = [
      '#type' => 'checkbox',
      '#title' => t('Muted'),
      '#default_value' => $element['#default_value']['background']['video']['muted'] ?? false,
      '#description' => t('Mute the video.'),
    ];
    $element['background']['iframe'] = [
      '#type' => 'textfield',
      '#title' => t('Iframe'),
      '#default_value' => $element['#default_value']['background']['iframe'] ?? '',
      '#description' => t('Enter background iframe URL for this slide. Please enable the "Interactive" option above to allow users to interact with the iframe.'),
    ];
    $element['background']['transition'] = [
      '#type' => 'select',
      '#title' => t('Transition'),
      '#default_value' => $element['#default_value']['background']['transition'] ?? 'fade',
      '#options' => [
        'fade' => t('Fade'),
        'slide' => t('Slide'),
        'convex' => t('Convex'),
        'concave' => t('Concave'),
        'zoom' => t('Zoom'),
      ],
      '#description' => t('Select the background transition effect for this slide.'),
    ];

    $element['override_revealjs_config_options'] = [
      '#type' => 'details',
      '#title' => t('Override Configuration Options on Events'),
    ];
    $element['override_revealjs_config_options']['help'] = [
      '#markup' => '<p>' . t('You can override Reveal.js configuration options on specific events. For example, you can disbale controls on slidechanged event.') . '</p>'
        . '<p>' . t('You can override any of the <a href="https://revealjs.com/config/" target="_blank">global configuration</a> options here.') . '</p>'
        . '<p>' . t('The configuration options should be in YAML format.') . '</p>'
    ];

    $slide_events = static::slideEvents();
    foreach ($slide_events as $event_name => $event_label) {
      $element['override_revealjs_config_options'][$event_name] = [
        '#type' => 'textarea',
        '#title' => t('On %event_label Event', ['%event_label' => $event_label]),
        '#default_value' => $element['#default_value']['override_revealjs_config_options'][$event_name] ?? '',
        '#limit_validation_errors' => [],
        '#description' => t('Specify configuration options to be overriden for this slide on %event_label event.', ['%event_label' => $event_label]),
        '#attributes' => [
          'data-yaml-editor' => 'true',
        ],
      ];
      if (!empty($element['#default_value']['override_revealjs_config_options'][$event_name])) {
        $element['override_revealjs_config_options']['#open'] = TRUE;
      }
    }

    return $element;
  }

  /**
   * Form element validation handler for #type 'present_slide'.
   */
  public static function validateSlide(&$element, FormStateInterface $form_state, &$complete_form) {
    $value = $element['#value'];

    $form_state->setValueForElement($element, $value);

    if ($value['type'] == static::TYPE_RENDER_ARRAY) {
      try {
        $test = Yaml::parse($value['content']['value']);
      }
      catch (\Symfony\Component\Yaml\Exception\ParseException $e) {
          $form_state->setError(
          $element['content']['value'],
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

    foreach ($value['override_revealjs_config_options'] as $event_name => $config_options_string) {
      try {
        $revealjs_config_options = Yaml::parse($config_options_string);
      }
      catch (\Symfony\Component\Yaml\Exception\ParseException $e) {
        $form_state->setError(
          $element['override_revealjs_config_options'][$event_name],
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

  // /**
  //  * Ajax callback.
  //  */
  // public static function ajaxRefresh(array $form, FormStateInterface $form_state) {
  //   $triggering_element = $form_state->getTriggeringElement();
  //   $parents = $triggering_element['#array_parents'];
  //   array_pop($parents);
  //   $slide_element = NestedArray::getValue($form, $parents);

  //   return $slide_element;
  // }

  public static function slideEvents() {
    return [
      'slidechanged' => t('Slide Changed'),
      'slidetransitionend' => t('Slide Transition End'),
    ];
  }

  /**
   * {@inheritdoc}
   */
  public static function valueCallback(&$element, $input, FormStateInterface $form_state) {
    if (is_array($input)) {
      return $input;
    }
    return $element['#default_value'];
  }

}
