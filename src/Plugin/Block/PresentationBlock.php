<?php

namespace Drupal\present\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\present\Entity\Presentation;
use Symfony\Component\DependencyInjection\ContainerInterface;

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

      return [
        'presentation' => [
          '#type' => 'revealjs_presentation',
          '#presentation' => $presentation,
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
