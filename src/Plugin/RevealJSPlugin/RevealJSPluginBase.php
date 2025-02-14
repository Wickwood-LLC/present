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
    return $this->pluginDefinition['revealjs_plugin_name'];
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return $this->pluginDefinition['id'] . '_plugin_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state) {
    if ($form_state instanceof SubformStateInterface) {
      $form_state = $form_state->getCompleteFormState();
    }
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
  public function submitConfigurationForm(array &$form, FormStateInterface $form_state) {}

  /**
   * {@inheritdoc}
   */
  public function alterRevealJSConfig(&$config) { }
}