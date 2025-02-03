<?php

namespace Drupal\present\Form;

use Drupal\Component\Utility\UrlHelper;
use Drupal\Core\Entity\EntityForm;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\present\Element\RevealJSPresentation;
use Symfony\Component\Yaml\Yaml;

/**
 * Form for adding/editing Presentation entities.
 */
class PresentationForm extends EntityForm {

  /**
   * {@inheritdoc}
   */
  public function form(array $form, FormStateInterface $form_state) {
    $form = parent::form($form, $form_state);

    if (!$presentation = $form_state->get('presentation')) {
      $presentation = $this->entity;
      $form_state->set('presentation', $presentation);
    }

    /** @var \Drupal\present\Entity\Presentation $presentation */

    $form['#attributes']['id'] = 'presentation-' . $presentation->isNew() ? 'new' : $presentation->id();
    $form_state->set('presentation', $presentation);

    $form['label'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Title'),
      '#default_value' => $presentation->getLabel(),
      '#required' => TRUE,
    ];

    $form['id'] = [
      '#type' => 'machine_name',
      '#title' => $this->t('ID'),
      '#default_value' => $presentation->id(),
      '#machine_name' => [
        'exists' => '\Drupal\present\Entity\Presentation::load',
      ],
      '#disabled' => !$presentation->isNew(),
    ];

    $form['path'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Path'),
      '#default_value' => $presentation->getPath(),
      '#required' => TRUE,
      '#maxlength' => 255,
      '#description' => $this->t('Specify the path of this presentation.'),
    ];

    $form['revealjs_theme'] = [
      '#type' => 'select',
      '#title' => $this->t('Theme'),
      '#default_value' => $presentation->getTheme(),
      '#options' => ['__none' => $this->t('Global default')] + RevealJSPresentation::revealThemes(),
      '#description' => $this->t('Select theme to be used by default. Theme previews can be <a href="https://revealjs.com/themes/">seen at</a>.'),
    ];

    $form['revealjs_config_options'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Configuration Options'),
      '#default_value' => $presentation->getConfigOptions(),
      '#description' => $this->t('Specify configuration options to be used for initializing the slides. This should be entered in YAML format. Dcoumentation about all possible options can be <a href="https://revealjs.com/config/">found at</a>.'),
    ];

    $slides = $presentation->getSlides();

    $form['slides'] = [
      '#type' => 'fieldset',
      '#title' => $this->t('Slides'),
      '#tree' => TRUE,
    ];

    $slide_number = 1;
    foreach ($slides as $key => $slide) {
      $form['slides'][$key] = [
        '#type' => 'present_slide',
        '#title' => $this->t('Slide #%number', ['%number' => $slide_number]),
        '#default_value' => $slide,
      ];
      $slide_number++;
    }

    $form['add_slide'] = [
      '#type' => 'submit',
      '#value' => $this->t('Add Slide'),
      '#limit_validation_errors' => [],
      '#ajax' => [
        'callback' => '::addSlideCallback', // AJAX callback method.
        'wrapper' => $form['#attributes']['id'],
        'event' => 'click', // The event triggering the AJAX request.
      ],
      '#submit' => [[static::class, 'addSlideSubmit']],
    ];

    $form['status'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Enabled'),
      '#default_value' => $presentation->getStatus(),
    ];

    return $form;
  }

  /**
   * AJAX callback method.
   */
  public function addSlideCallback(array &$form, FormStateInterface $form_state) {
    return $form;
  }

  /**
   * Submission handler for the "Add Slide" button.
   */
  public static function addSlideSubmit(array $form, FormStateInterface $form_state) {
    $button = $form_state->getTriggeringElement();

    /** @var \Drupal\present\Entity\Presentation */
    $presentation = $form_state->get('presentation');
    $presentation->addSlide();
    $form_state->set('presentation', $presentation);

    $form_state->setRebuild();
  }

  public function validateForm(array &$form, FormStateInterface $form_state) {

    $path = $form_state->getValue('path');
    $errors = [];
    if (strpos($path, '%') !== FALSE) {
      $form_state->setErrorByName('path', $this->t('"%" may not be used in the path.'));
    }

    $parsed_url = UrlHelper::parse($path);
    if (empty($parsed_url['path'])) {
      $form_state->setErrorByName('path', $this->t('Path is empty.'));
    }

    if (!empty($parsed_url['query'])) {
      $form_state->setErrorByName('path', $this->t('No query allowed.'));
    }

    if (!parse_url('internal:/' . $path)) {
      $form_state->setErrorByName('path', $this->t('Invalid path. Valid characters are alphanumerics as well as "-", ".", "_" and "~".'));
    }

    $revealjs_config_options = $form_state->getValue('revealjs_config_options');
    try {
      $test = Yaml::parse($revealjs_config_options);
    }
    catch (\Symfony\Component\Yaml\Exception\ParseException $e) {
      $form_state->setErrorByName(
        'revealjs_config_options',
        t(
          'Not in a valid YAML format: %message',
          [
            '%message' => $e->getMessage(),
          ]
        )
      );
    }
  }

  /**
   * {@inheritdoc}
   */
  protected function copyFormValuesToEntity(EntityInterface $entity, array $form, FormStateInterface $form_state) {
    $values = $form_state->getValues();

    unset(
      $values['add_slide'],
    );

    $form_state->setValues($values);
    parent::copyFormValuesToEntity($entity, $form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $form_state) {
    $entity = $this->entity;
    $status = $entity->save();

    if ($status === SAVED_NEW) {
      $this->messenger()->addMessage($this->t('Created the %label presentaiton.', [
        '%label' => $entity->label(),
      ]));
    }
    else {
      $this->messenger()->addMessage($this->t('Updated the %label presentaiton.', [
        '%label' => $entity->label(),
      ]));
    }

    $form_state->setRedirectUrl($entity->toUrl('collection'));
  }

}