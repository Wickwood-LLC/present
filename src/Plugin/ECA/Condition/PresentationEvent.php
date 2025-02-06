<?php

namespace Drupal\present\Plugin\ECA\Condition;

use Drupal\Core\Form\FormStateInterface;
use Drupal\eca\Plugin\ECA\PluginFormTrait;
use Drupal\eca\Plugin\ECA\Condition\ConditionBase;
use Drupal\present\Entity\Presentation;

/**
 * Plugin implementation of the ECA condition of the Presentation event.
 *
 * @EcaCondition(
 *   id = "presentation_event",
 *   label = @Translation("Presentation Event"),
 *   description = @Translation("Check which presentation event occurred."),
 *   eca_version_introduced = "1.0.0"
 * )
 */
class PresentationEvent extends ConditionBase {

  use PluginFormTrait;

  /**
   * {@inheritdoc}
   */
  public function evaluate(): bool {
    
    $current_event = $this->tokenService->replace($this->configuration['current_event']);
    $event = $this->tokenService->replace($this->configuration['event']);

    return $this->negationCheck($current_event === $event);
  }

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration(): array {
    return [
      'current_event' => '[presentation_event_name]',
      'event' => '',
    ] + parent::defaultConfiguration();
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state): array {
    $form['current_event'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Presentation Eevent Occured'),
      '#description' => $this->t('The presentation event you want to match.'),
      '#default_value' => $this->configuration['current_event'],
      '#weight' => -20,
    ];

    $form['event'] = [
      '#type' => 'select',
      '#title' => $this->t('Presentation Event'),
      '#description' => $this->t('The event to check.'),
      '#default_value' => $this->configuration['event'],
      '#required' => TRUE,
      '#options' => Presentation::events(),
    ];
    return parent::buildConfigurationForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitConfigurationForm(array &$form, FormStateInterface $form_state): void {
    $this->configuration['current_event'] = $form_state->getValue('current_event');
    $this->configuration['event'] = $form_state->getValue('event');
    parent::submitConfigurationForm($form, $form_state);
  }

}
