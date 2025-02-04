<?php

namespace Drupal\present\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Render\Markup;
use Drupal\present\Element\Slide;
use Drupal\present\Entity\Presentation;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Yaml\Yaml;

/**
 * Defines an inline block plugin type.
 *
 * @Block(
 *  id = "presentation_block",
 *  admin_label = @Translation("Presentation block"),
 *  category = @Translation("Presentation blocks"),
 * )
 *
 * @internal
 *   Plugin classes are internal.
 */
class PresentationBlock extends BlockBase implements ContainerFactoryPluginInterface {

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * Presentation entity associated with this block
   *
   * @var \Drupal\present\Entity\Presentation
   */
  protected $presentation;


  /**
   * Constructs a new InlineBlock.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin ID for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager service.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, EntityTypeManagerInterface $entity_type_manager) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);

    $this->entityTypeManager = $entity_type_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('entity_type.manager')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return [
      'presentation' => NULL,
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function blockForm($form, FormStateInterface $form_state) {

    $presentation_storage = $this->entityTypeManager->getStorage('presentation');
    $presentation_options = [];
    foreach ($presentation_storage->loadMultiple() as $presentation) {
      $presentation_options[$presentation->id()] = $presentation->label();
    }

    $form['presentation'] = [
      '#type' => 'select',
      '#options' => $presentation_options,
      '#title' => $this->t('Presentation'),
      '#description' => $this->t('The Presentation to display.'),
      '#default_value' => $this->configuration['presentation'],
    ];
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function blockSubmit($form, FormStateInterface $form_state) {
    $this->configuration['presentation'] = $form_state->getValue('presentation');
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    if ($presentation = $this->getPresentation()) {
      if (!$presentation->getStatus()) {
        // Not to display if the presentation is disabled.
        return [];
      }

      $config = \Drupal::config('present.settings');

      foreach ($presentation->getSlides() as $slide_data) {

        $override_config_options = [];
        foreach (Slide::slideEvents() as $event_name => $event_label) {
          $override_config_options[$event_name] = Yaml::parse($slide_data['ovrride_revealjs_config_options'][$event_name]);
        }

        $slide = [
          '#type' => 'revealjs_slide',
          '#attributes' => [
            'data-config-options' => json_encode($override_config_options),
          ],
        ];
        if ($slide_data['auto_animate']) {
          $slide['#attributes']['data-auto-animate'] = TRUE;
        }
        if (!empty($slide_data['auto_animate_id'])) {
          $slide['#attributes']['data-auto-animate-id'] = $slide_data['auto_animate_id'];
        }
        if ($slide_data['auto_animate_restart']) {
          $slide['#attributes']['data-auto-animate-restart'] = TRUE;
        }
        if ($slide_data['type'] == Slide::TYPE_RENDER_ARRAY) {
          $slide['#content'] = Yaml::parse($slide_data['content']);
        }
        else {
          $slide['#content'] = [
            '#markup' => Markup::create($slide_data['content']),
          ];
        }
        $slides[] = $slide;
      }
      $reveal_theme = \Drupal::request()->query->get('theme');

      if (!$reveal_theme) {
        $reveal_theme = $presentation->getTheme();
        if ($reveal_theme == '__none') {
          $reveal_theme = NULL;
        }
      }
      return [
        'presentation' => [
          '#type' => 'revealjs_presentation',
          '#slides' => $slides,
          '#options' => [
            'theme' => $reveal_theme,
          ],
          // Make emebdded by default but allow overriding.
          '#config_options' => json_encode(['embedded' => TRUE] + $presentation->getConfigOptionsArray()),
          '#cache' => [
            'max-age' => 0,
            'contexts' => ['url.query_args:theme'],
            'tags' => [$presentation->getEntityTypeId() . ':' . $presentation->id()],
          ],
        ],
      ];
    }
    return [];
  }

  /**
   * Get presentation config entity associated with this block.
   */
  public function getPresentation(): Presentation {
    if (!$this->presentation) {
    $this->presentation = $this->entityTypeManager->getStorage('presentation')->load($this->configuration['presentation']);
    }
    return $this->presentation;
  }

  /**
   * @inheritdoc
   */
  public function getCacheTags()  {
    $tags = parent::getCacheTags();
    if ($presentation = $this->getPresentation()) {
      $tags += $presentation->getCacheTags();
    }

    return $tags;
  }

  /**
   * @inheritdoc
   */
  public function getCacheMaxAge() {
    return CacheBackendInterface::CACHE_PERMANENT;
  }
}
