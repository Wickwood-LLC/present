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

}
