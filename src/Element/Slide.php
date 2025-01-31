<?php

namespace Drupal\present\Element;

use Drupal\Component\Utility\Html;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Render\Attribute\FormElement;
use Drupal\Core\Render\Element;
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
      // '#multiple' => FALSE,
      // '#sort_options' => FALSE,
      // '#sort_start' => NULL,
      '#process' => [
        [$class, 'processSlide'],
        // [$class, 'processGroup'],
      ],
      '#element_validate' => [
        [$class, 'validateSlide'],
      ],
      // '#pre_render' => [
      //   [$class, 'preRenderSelect'],
      // ],
      // '#theme' => 'present_slide',
      // '#theme' => 'fieldset',
      '#theme_wrappers' => ['fieldset'],
      // '#options' => [],
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
    // Validate and parse #field_overrides.
    // if (!is_array($element['#field_overrides'])) {
    //   throw new \InvalidArgumentException('The #field_overrides property must be an array.');
    // }
    // $element['#parsed_field_overrides'] = new FieldOverrides($element['#field_overrides']);

    // $id_prefix = implode('-', $element['#parents']);
    // $wrapper_id = Html::getUniqueId($id_prefix . '-ajax-wrapper');
    // The #value has the new values on #ajax, the #default_value otherwise.
    $value = $element['#value'];

    $element = [
      '#tree' => TRUE,
      // '#prefix' => '<div id="' . $wrapper_id . '">',
      // '#suffix' => '</div>',
      // Pass the id along to other methods.
      // '#wrapper_id' => $wrapper_id,
      // '#collapsible' => TRUE,
    ] + $element;
    $element['content'] = [
      '#type' => 'textarea',
      '#title' => t('Content'),
      '#default_value' => $element['#default_value']['content'],
      // '#required' => $element['#required'],
      '#limit_validation_errors' => [],
      '#attributes' => ['data-yaml-editor' => 'true'],
      // '#ajax' => [
      //   'callback' => [get_called_class(), 'ajaxRefresh'],
      //   'wrapper' => $wrapper_id,
      // ],
    ];
    // if (!empty($value['country_code'])) {
    //   $element = static::addressElements($element, $value);
    // }

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
