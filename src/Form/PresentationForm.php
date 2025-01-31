<?php

namespace Drupal\present\Form;

use Drupal\Core\Entity\EntityForm;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Form\FormStateInterface;

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

    /** @var \Drupal\present\Entity\Presentation */

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

  /**
   * {@inheritdoc}
   */
  protected function copyFormValuesToEntity(EntityInterface $entity, array $form, FormStateInterface $form_state) {
    $values = $form_state->getValues();

    // foreach ($values['tab_order'] ?? [] as $key => $order_data) {
    //   $values['tabs'][$key]['weight'] = $order_data['weight'];
    // }

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