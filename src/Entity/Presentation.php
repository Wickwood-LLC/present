<?php

namespace Drupal\present\Entity;

use Drupal\Core\Config\Entity\ConfigEntityBase;
use Symfony\Component\Yaml\Yaml;

/**
 * Defines the Presentation configuration entity.
 *
 * @ConfigEntityType(
 *   id = "presentation",
 *   label = @Translation("Presentation"),
 *   label_collection = @Translation("Presentations"),
 *   label_singular = @Translation("Presentation"),
 *   label_plural = @Translation("presentations"),
 *   label_count = @PluralTranslation(
 *     singular = "@count presentation",
 *     plural = "@count presentations",
 *   ),
 *   handlers = {
 *     "list_builder" = "Drupal\present\PresentationListBuilder",
 *     "form" = {
 *       "add" = "Drupal\present\Form\PresentationForm",
 *       "edit" = "Drupal\present\Form\PresentationForm",
 *       "delete" = "Drupal\Core\Entity\EntityDeleteForm",
 *       "duplicate" = "Drupal\present\Form\PresentationForm"
 *     },
 *     "route_provider" = {
 *       "html" = "Drupal\Core\Entity\Routing\AdminHtmlRouteProvider",
 *     },
 *   },
 *   config_prefix = "presentation",
 *   admin_permission = "administer presentations",
 *   entity_keys = {
 *     "id" = "id",
 *     "label" = "label",
 *     "status" = "status",
 *   },
 *   links = {
 *     "add-form" = "/admin/structure/presentation/add",
 *     "edit-form" = "/admin/structure/presentation/{presentation}/edit",
 *     "delete-form" = "/admin/structure/presentation/{presentation}/delete",
 *     "duplicate-form" = "/admin/structure/presentation/{presentation}/duplicate",
 *     "collection" = "/admin/structure/presentation",
 *   },
 *   config_export = {
 *     "id",
 *     "label",
 *     "status",
 *     "revealjs_theme",
 *     "revealjs_plugins",
 *     "revealjs_plugin_settings",
 *     "revealjs_config_options",
 *     "events_to_track",
 *     "slides",
 *   },
 *   cache = {
 *     "tags" = {"presentation_list", "presentation:{id}"}
 *   }
 * )
 */
class Presentation extends ConfigEntityBase {

  /**
   * The ID of the presentation.
   *
   * @var string
   */
  protected $id;

  /**
   * The label of the presentation.
   *
   * @var string
   */
  protected $label;

  /**
   * The status of the presentation.
   *
   * @var bool
   */
  protected $status = TRUE;

  /**
   * The theme to use for the presentation.
   */
  protected $revealjs_theme;

  /**
   * Reveal.js plugins to load for this presentaiton.
   */
  protected $revealjs_plugins = [];

  /**
   * Reveal.js plugin settings.
   */
  protected $revealjs_plugin_settings = [];

  /**
   * The configuration options in YAML format.
   *
   * @var string
   */
  protected $revealjs_config_options;

  /**
   * Events to track
   *
   * @var array
   */
  protected $events_to_track = [];

  /**
   * The slides of the presentation.
   */
  protected $slides = [];

  /**
   * {@inheritdoc}
   */
  public function getLabel() {
    return $this->label;
  }

  /**
   * {@inheritdoc}
   */
  public function setLabel($label) {
    $this->label = $label;
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getStatus() {
    return $this->status;
  }

  /**
   * {@inheritdoc}
   */
  public function setStatus($status) {
    $this->status = $status;
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getTheme() {
    return $this->revealjs_theme;
  }

  public function getPlugins(): array {
    return $this->revealjs_plugins;
  }

  public function getPluginSettings(): array {
    return $this->revealjs_plugin_settings;
  }

  public function getConfigOptions() {
    return $this->revealjs_config_options;
  }

  public function getConfigOptionsArray(): array {
    return Yaml::parse($this->revealjs_config_options) ?? [];
  }

  /**
   * {@inheritdoc}
   */
  public function setPath($path) {
    $this->path = $path;
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getEventsToTrack() {
    return $this->events_to_track;
  }

  public function addSlide($slide_data = [], int $position = NULL) {
    $uuid_service = \Drupal::service('uuid');
    $key = $uuid_service->generate();
    $slide = $slide_data + [
      'content' => '',
      'type' => 'html',
    ] ;
    if (isset($position)) {
      $existing_position = array_search($key, array_keys($this->slides));
      // $part_1 = array_splice($this->slides, $existing_position, 1);
      $part_1 = array_splice($this->slides, 0, $position);
      $part_2 = [$key => $slide];
      $this->slides = array_merge($part_1, $part_2, $this->slides);
    }
    else {
      $this->slides[$key] = $slide;
    }
    return $key;
  }

  public function removeSlide($key) {
    unset($this->slides[$key]);
  }

  public function getSlides() {
    return $this->slides;
  }

  /**
   * Move a slide to new positon.
   *
   * @param sttring $key
   *  Key of the slide
   * @param int $new_position
   *  New position to move slide to
   */
  public function moveSlide(string $key, int $new_position) {
    $existing_position = array_search($key, array_keys($this->slides));
    $part_1 = array_splice($this->slides, $existing_position, 1);
    $part_2 = array_splice($this->slides, 0, $new_position);
    $this->slides = array_merge($part_2, $part_1, $this->slides);
  }

  public static function events(): array {
    return [
      'ready' => t('Ready'),
      'slidechanged' => t('Slide Changed'),
      'slidetransitionend' => t('Slide Transition End'),
      'resize' => t('Resize'),
    ];
  }

  public function getPluginInstances() {
    /** @var \Drupal\present\Plugin\RevealJSPlugin\RevealJSPluginManager */
    $revealjs_plugin_manager = \Drupal::service('plugin.manager.revealjs_plugins');

    $plugins = [];
    foreach ($this->revealjs_plugins as $plugin_id) {
      // $plugin_def = $revealjs_plugin_manager->getDefinition($plugin_id);
      /** @var \Drupal\present\Plugin\RevealJSPlugin\RevealJSPlugin $plugin */
      $plugin = $revealjs_plugin_manager->createInstance($plugin_id);
      $plugins[$plugin_id] = $plugin;
    }
    return $plugins;
  }
}
