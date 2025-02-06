<?php

namespace Drupal\present\Form;

use Drupal\Core\Entity\EntityForm;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\present\Element\RevealJSPresentation;
use Drupal\present\Entity\Presentation;
use Symfony\Component\Yaml\Yaml;

/**
 * Form for adding/editing Presentation entities.
 */
class PresentationForm extends EntityForm {

  /**
   * {@inheritdoc}
   */
  public function setEntity(EntityInterface $entity) {
    /** @var \Drupal\present\Entity\Presentation $entity */
    if ($this->operation == 'duplicate') {
      $entity = $entity->createDuplicate();
      $entity->setLabel($this->t('Clone of @label', ['@label' => $entity->label()]));
    }
    $this->entity = $entity;
    return $this;
  }

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

    if ($this->operation == 'edit') {
      $form['#title'] = $this->t('<em>Edit Presentation</em> @title', [
        '@title' => $presentation->label(),
      ]);
    }

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

    $form['events_to_track'] = [
      '#type' => 'checkboxes',
      '#title' => $this->t('Events to Track'),
      '#default_value' => $presentation->getEventsToTrack(),
      '#options' => Presentation::events(),
      '#description' => $this->t('Select events to be tracked. These Reveal.js events will be passed to Drupal side where you can utilize for various purposes. Documentation about Reveal.js events can be <a href="https://revealjs.com/events/">seen at</a>.'),
    ];

    $slides = $presentation->getSlides();

    $slides_wrapper_id = $form['#attributes']['id'] . '-wrapper';
    $form['slides'] = [
      '#type' => 'fieldset',
      '#title' => $this->t('Slides'),
      '#tree' => TRUE,
      '#prefix' => '<div id="' . $slides_wrapper_id . '">',
      '#suffix' => '</div>',
    ];

    $slide_number = 1;
    foreach ($slides as $key => $slide) {
      $form['slides'][$key] = [
        '#type' => 'present_slide',
        '#title' => $this->t('Slide #%number', ['%number' => $slide_number]),
        '#default_value' => $slide,
      ];
      $form['slides'][$key]['remove'] = [
        '#type' => 'submit',
        '#value' => $this->t('Remove'),
        '#name' => 'remove_slide_' . $key,
        '#limit_validation_errors' => [],
        '#ajax' => [
          'callback' => '::removeSlideCallback', // AJAX callback method.
          'wrapper' => $slides_wrapper_id,
          'event' => 'click', // The event triggering the AJAX request.
        ],
        '#submit' => [[static::class, 'removeSlideSubmit']],
        '#weight' => 100,
      ];
      $slide_number++;
    }

    $form['add_slide'] = [
      '#type' => 'submit',
      '#value' => $this->t('Add Slide'),
      '#limit_validation_errors' => [],
      '#ajax' => [
        'callback' => '::addSlideCallback', // AJAX callback method.
        'wrapper' => $slides_wrapper_id,
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
    return $form['slides'];
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

  /**
   * AJAX callback method.
   */
  public function removeSlideCallback(array &$form, FormStateInterface $form_state) {
    return $form['slides'];
  }

  /**
   * Submission handler for the "Add Slide" button.
   */
  public static function removeSlideSubmit(array $form, FormStateInterface $form_state) {
    $button = $form_state->getTriggeringElement();

    /** @var \Drupal\present\Entity\Presentation */
    $presentation = $form_state->get('presentation');

    end($button['#parents']);
    $slode_to_remove = prev($button['#parents']);

    $presentation->removeSlide($slode_to_remove);
    $form_state->set('presentation', $presentation);

    $form_state->setRebuild();
  }

  public function validateForm(array &$form, FormStateInterface $form_state) {

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