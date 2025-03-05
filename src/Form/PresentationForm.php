<?php

namespace Drupal\present\Form;

use Drupal\Component\Plugin\ConfigurableInterface;
use Drupal\Core\Entity\EntityForm;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Form\SubformState;
use Drupal\present\Element\RevealJSPresentation;
use Drupal\present\Element\Slide;
use Drupal\present\Entity\Presentation;
use Drupal\present\Plugin\RevealJSPlugin\RevealJSPluginManager;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Yaml\Yaml;

/**
 * Form for adding/editing Presentation entities.
 */
class PresentationForm extends EntityForm {

  /**
   * @var \Drupal\present\Plugin\RevealJSPlugin\RevealJSPluginManager
   */
  protected $pluginManager;

  /**
   * Constructs a PresentationForm instance.
   *
   * @param \Drupal\present\Plugin\RevealJSPlugin\RevealJSPluginManager $revealjs_plugin_manager
   *   The RevealJS plugin manager.
   */
  public function __construct(RevealJSPluginManager $revealjs_plugin_manager) {
    $this->pluginManager = $revealjs_plugin_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('plugin.manager.revealjs_plugins')
    );
  }

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

    /** @var \Drupal\present\Plugin\RevealJSPlugin\RevealJSPluginManager */
    $revealjs_plugin_manager = \Drupal::service('plugin.manager.revealjs_plugins');

    $plugin_options = $revealjs_plugin_manager->options();

    $form['revealjs_plugins'] = [
      '#type' => 'checkboxes',
      '#title' => $this->t('Reveal.js Plugins'),
      '#default_value' => $presentation->getPlugins(),
      '#options' => $plugin_options,
      '#description' => $this->t('Select additional Reveal.js plugins to load for this presentation.'),
    ];

    $form['revealjs_config_options'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Configuration Options'),
      '#default_value' => $presentation->getConfigOptions(),
      '#description' => $this->t('Specify default configuration options for all slides to be used for initializing the presentation. This should be entered in YAML format. Documentation about all possible options can be found at: <a href="https://revealjs.com/config/" target="_blank">https://revealjs.com/config/</a>.'),
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
    $num_slides = count($slides);
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

      $form['slides'][$key]['duplicate'] = [
        '#type' => 'submit',
        '#value' => $this->t('Duplicate'),
        '#name' => 'duplicate_' . $key,
        '#limit_validation_errors' => [],
        '#ajax' => [
          'callback' => '::duplicateSlideCallback', // AJAX callback method.
          'wrapper' => $slides_wrapper_id,
          'event' => 'click', // The event triggering the AJAX request.
        ],
        '#submit' => [[static::class, 'duplicateSlideSubmit']],
        '#weight' => 100,
      ];

      if ($slide_number != $num_slides) {
        $form['slides'][$key]['move_down'] = [
          '#type' => 'submit',
          '#value' => $this->t('Move Down ↓'),
          '#name' => 'move_down_' . $key,
          '#limit_validation_errors' => [],
          '#ajax' => [
            'callback' => '::moveSlideCallback', // AJAX callback method.
            'wrapper' => $slides_wrapper_id,
            'event' => 'click', // The event triggering the AJAX request.
          ],
          '#submit' => [[static::class, 'moveSlideSubmit']],
          '#weight' => 101,
        ];
      }
      if ($slide_number != 1) {
        $form['slides'][$key]['move_up'] = [
          '#type' => 'submit',
          '#value' => $this->t('Move Up ↑'),
          '#name' => 'move_up_' . $key,
          '#limit_validation_errors' => [],
          '#ajax' => [
            'callback' => '::moveSlideCallback', // AJAX callback method.
            'wrapper' => $slides_wrapper_id,
            'event' => 'click', // The event triggering the AJAX request.
          ],
          '#submit' => [[static::class, 'moveSlideSubmit']],
          '#weight' => 102,
        ];
      }
      $slide_number++;
    }

    $form['add_html_slide'] = [
      '#type' => 'submit',
      '#value' => $this->t('Add HTML Slide'),
      '#limit_validation_errors' => [],
      '#ajax' => [
        'callback' => '::addSlideCallback', // AJAX callback method.
        'wrapper' => $slides_wrapper_id,
        'event' => 'click', // The event triggering the AJAX request.
      ],
      '#submit' => [[static::class, 'addHtmlSlideSubmit']],
    ];

    $form['add_render_array_slide'] = [
      '#type' => 'submit',
      '#value' => $this->t('Add Render Array Slide'),
      '#limit_validation_errors' => [],
      '#ajax' => [
        'callback' => '::addSlideCallback', // AJAX callback method.
        'wrapper' => $slides_wrapper_id,
        'event' => 'click', // The event triggering the AJAX request.
      ],
      '#submit' => [[static::class, 'addRenderArraySlideSubmit']],
    ];

    $form['plugin_settings'] = [
      '#type' => 'vertical_tabs',
      '#title' => $this->t('Plugin settings'),
      '#attributes' => [
        'id' => 'reveal-plugin-settings',
      ],
    ];
    $this->injectPluginSettingsForm($form, $form_state, $presentation);

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
  public static function addHtmlSlideSubmit(array $form, FormStateInterface $form_state) {
    $button = $form_state->getTriggeringElement();

    /** @var \Drupal\present\Entity\Presentation */
    $presentation = $form_state->get('presentation');
    $presentation->addSlide(['type' => Slide::TYPE_HTML_RAW]);
    $form_state->set('presentation', $presentation);

    $form_state->setRebuild();
  }

  /**
   * Submission handler for the "Add Slide" button.
   */
  public static function addRenderArraySlideSubmit(array $form, FormStateInterface $form_state) {
    $button = $form_state->getTriggeringElement();

    /** @var \Drupal\present\Entity\Presentation */
    $presentation = $form_state->get('presentation');
    $presentation->addSlide(['type' => Slide::TYPE_RENDER_ARRAY]);
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
    $slide_to_remove = prev($button['#parents']);

    $presentation->removeSlide($slide_to_remove);
    $form_state->set('presentation', $presentation);

    $form_state->setRebuild();
  }

  /**
   * AJAX callback method.
   */
  public function duplicateSlideCallback(array &$form, FormStateInterface $form_state) {
    return $form['slides'];
  }

  /**
   * Submission handler for the "Add Slide" button.
   */
  public static function duplicateSlideSubmit(array $form, FormStateInterface $form_state) {
    $button = $form_state->getTriggeringElement();

    /** @var \Drupal\present\Entity\Presentation */
    $presentation = $form_state->get('presentation');

    end($button['#parents']);
    $slide_to_uplicate = prev($button['#parents']);

    $slides = $presentation->getSlides();
    $position = array_search($slide_to_uplicate, array_keys($presentation->getSlides())) + 1;

    $slide_data = $slides[$slide_to_uplicate];
    $new_slide_key = $presentation->addSlide($slide_data, $position);

    // Copy input values as well.
    $input = $form_state->getUserInput();
    $input['slides'][$new_slide_key] = $input['slides'][$slide_to_uplicate];
    $form_state->setUserInput($input);

    $form_state->set('presentation', $presentation);

    $form_state->setRebuild();
  }

  /**
   * AJAX callback method.
   */
  public function moveSlideCallback(array &$form, FormStateInterface $form_state) {
    return $form['slides'];
  }

  /**
   * Submission handler for the "Move Down" and "Move Up" buttons.
   */
  public static function moveSlideSubmit(array $form, FormStateInterface $form_state) {
    $button = $form_state->getTriggeringElement();

    /** @var \Drupal\present\Entity\Presentation */
    $presentation = $form_state->get('presentation');

    $button_name = end($button['#parents']);
    $slide_to_move = prev($button['#parents']);

    $existing_position = array_search($slide_to_move, array_keys($presentation->getSlides()));
    if ($button_name == 'move_up') {
      $new_postion = $existing_position - 1;
    }
    else {
      $new_postion = $existing_position + 1;
    }

    $presentation->moveSlide($slide_to_move, $new_postion);
    $form_state->set('presentation', $presentation);

    $form_state->setRebuild();
  }

  public function validateForm(array &$form, FormStateInterface $form_state) {
    parent::validateForm($form, $form_state);
    /** @var \Drupal\present\Entity\Presentation */
    $presentation = $form_state->get('presentation');

    $definitions = $this->pluginManager->getDefinitions();

    $revealjs_plugin_settings = [];
    foreach ($definitions as $plugin_id => $definition) {
      $plugin = $this->pluginManager->getPlugin($plugin_id, $presentation);
      if ($plugin instanceof ConfigurableInterface) {
        /** @var \Drupal\present\Plugin\RevealJSPlugin\ConfigurableRevealJSPluginBase $plugin */

        if ($form_state->hasValue(['revealjs_plugin_settings', $plugin_id])) {
          $subform = $form['revealjs_plugin_settings'][$plugin_id];
          $subform_state = SubformState::createForSubform($subform, $form, $form_state);
          $plugin->validateConfigurationForm($subform, $subform_state);
          $plugin->submitConfigurationForm($subform, $subform_state);

          $revealjs_plugin_settings[$plugin_id] = $plugin->getConfiguration();
        }
      }
    }
    $form_state->setValue('revealjs_plugin_settings', $revealjs_plugin_settings);

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
    $values['revealjs_plugins'] = array_values(array_filter($values['revealjs_plugins']));

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

    $triggering_button = $form_state->getTriggeringElement();
    if ($triggering_button['#name'] === 'save_and_continue') {
      $form_state->setRedirectUrl($entity->toUrl('edit-form'));
      $form_state->setIgnoreDestination();
    }
    else {
      $form_state->setRedirectUrl($entity->toUrl('collection'));
    }
  }

  /**
   * Injects the RevealJS plugins settings forms as a vertical tabs subform.
   *
   * @param array &$form
   *   A reference to an associative array containing the structure of the form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current state of the form.
   * @param \Drupal\editor\EditorInterface $editor
   *   A presentation object.
   */
  private function injectPluginSettingsForm(array &$form, FormStateInterface $form_state, Presentation $presentation): void {
    $form['revealjs_plugin_settings'] = [
      '#type' => 'container',
      '#tree' => TRUE,
    ];
    foreach ($presentation->getPlugins() as $plugin_id) {
      $plugin = $this->pluginManager->getPlugin($plugin_id, $presentation);
      $definition = $this->pluginManager->getDefinition($plugin_id);
      if ($plugin instanceof ConfigurableInterface) {
        /** @var \Drupal\present\Plugin\RevealJSPlugin\ConfigurableRevealJSPluginBase $plugin */

        $plugin_settings_form = [];
        $form['revealjs_plugin_settings'][$plugin_id] = [
          '#type' => 'details',
          '#title' => $definition['label'],
          '#open' => TRUE,
          '#group' => 'plugin_settings',
          '#attributes' => [
            'data-revealjs-plugin-id' => $plugin_id,
          ],
        ];
        $form['revealjs_plugin_settings'][$plugin_id] += $plugin->buildConfigurationForm($plugin_settings_form, $form_state);
      }
    }
  }

  public function actions(array $form, FormStateInterface $form_state) {
    $actions = parent::actions($form, $form_state);
    $actions['save_and_continue'] = $actions['submit'];
    $actions['save_and_continue']['#name'] = 'save_and_continue';
    $actions['save_and_continue']['#value'] = $this->t('Save and continue');
    return $actions;
  }

}