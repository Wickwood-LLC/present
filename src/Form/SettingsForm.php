<?php

namespace Drupal\present\Form;

use Drupal\Core\Config\Config;
use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\present\Element\RevealJSPresentation;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * Defines a form that configures devel settings.
 */
class SettingsForm extends ConfigFormBase {


  /**
   * The 'present.settings' config object.
   */
  protected Config $config;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container): static {
    $instance = parent::create($container);
    $instance->config = $container->get('config.factory')->getEditable('present.settings');

    return $instance;
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId(): string {
    return 'present_settings_form';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames(): array {
    return [
      'present.settings',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, Request $request = NULL): array {

    $form['revealjs_theme'] = [
      '#type' => 'select',
      '#title' => $this->t('Default Reveal.js Theme'),
      '#default_value' => $this->config->get('revealjs_theme'),
      '#options' => RevealJSPresentation::revealThemes(),
      '#description' => $this->t('Select theme to be used by default. Theme previews can be <a href="https://revealjs.com/themes/">seen at</a>.'),
    ];

    $form['user_code_cookied_validity'] = [
      '#type' => 'number',
      '#title' => $this->t('Validity of Uer Code Cookie'),
      '#field_suffix' => $this->t('days'),
      '#size' => 4,
      '#min' => 0,
      '#max' => 1825, // 5 years.
      '#default_value' => $this->config->get('user_code_cookied_validity') ?? 30,
      '#description' => $this->t('Validity of the user code cookied in number of days.'),
    ];

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state): void {
    $values = $form_state->getValues();
    $this->config
      ->set('revealjs_theme', $values['revealjs_theme'])
      ->set('user_code_cookied_validity', $values['user_code_cookied_validity'])
      ->save();

    parent::submitForm($form, $form_state);
  }


}
