<?php

namespace Drupal\present\Element;

use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Render\Attribute\FormElement;
use Drupal\Core\Render\Element\FormElementBase;
use Symfony\Component\Yaml\Yaml;

/**
 * Provides a form element for a slide.
 */
#[FormElement('present_slide')]
class Slide extends FormElementBase {

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

    $element = [
      '#tree' => TRUE,
    ] + $element;
    $element['content'] = [
      '#type' => 'textarea',
      '#title' => t('Content'),
      '#default_value' => $element['#default_value']['content'],
      '#limit_validation_errors' => [],
      '#attributes' => ['data-yaml-editor' => 'true'],
    ];

    return $element;
  }

  /**
   * Form element validation handler for #type 'present_slide'.
   */
  public static function validateSlide(&$element, FormStateInterface $form_state, &$complete_form) {
    $value = $element['#value'];

    try {
      Yaml::parse($value['content']);
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
