<?php

namespace Drupal\present\Plugin\RevealJSPlugin;

use Drupal\Component\Plugin\PluginBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Form\SubformStateInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;

abstract class RevealJSPluginBase extends PluginBase implements RevealJSPluginInterface {
  use StringTranslationTrait;

  /**
   * {@inheritdoc}
   */
  public function prenderPresentation($element): array {
    return $element;
  }

  public function getRevealJSPluginName(): string {
    // $reflection = new \ReflectionClass($this);
    // $attributes = $reflection->getAttributes('RevealJSPlugin');
    // return $attributes['revealjs_plugin_name'];
    return $this->pluginDefinition['revealjs_plugin_name'];
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    // $class = new \ReflectionClass(self::class);
    // $attributes = $class->getAttributes('RevealJSPlugin');
    return $this->pluginDefinition['id'] . '_plugin_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state) {
    if ($form_state instanceof SubformStateInterface) {
      $form_state = $form_state->getCompleteFormState();
    }
    // $contexts = $form_state->getTemporaryValue('gathered_contexts') ?: [];
    // $form['context_mapping'] = $this->addContextAssignmentElement($this, $contexts);
    // $form['negate'] = [
    //   '#type' => 'checkbox',
    //   '#title' => $this->t('Negate the condition'),
    //   '#default_value' => $this->configuration['negate'],
    // ];
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateConfigurationForm(array &$form, FormStateInterface $form_state) {
  }

  /**
   * {@inheritdoc}
   */
  public function submitConfigurationForm(array &$form, FormStateInterface $form_state) {
    // $this->configuration['negate'] = $form_state->getValue('negate');
    // if ($form_state->hasValue('context_mapping')) {
    //   $this->setContextMapping($form_state->getValue('context_mapping'));
    // }
  }

  /**
   * {@inheritdoc}
   */
  public function alterRevealJSConfig(&$config) { }
}