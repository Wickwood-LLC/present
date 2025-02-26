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
 *     "add-form" = "/admin/content/presentation/add",
 *     "edit-form" = "/admin/content/presentation/{presentation}/edit",
 *     "delete-form" = "/admin/content/presentation/{presentation}/delete",
 *     "duplicate-form" = "/admin/content/presentation/{presentation}/duplicate",
 *     "collection" = "/admin/content/presentation",
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

  protected $plugin_instances = [];

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
   * Get the theme set for this presentation.
   *
   * @return string | null
   *   The theme set for this presentation.
   * @see https://revealjs.com/themes/
   */
  public function getTheme(): string | null {
    return $this->revealjs_theme;
  }

  /**
   * Get the plugins set for this presentation.
   *
   * @return array
   *   The plugins set for this presentation.
   */
  public function getPlugins(): array {
    return $this->revealjs_plugins;
  }

  /**
   * Get the plugin settings set for this presentation.
   *
   * @return array
   *   The plugin settings set for this presentation.
   */
  public function getPluginSettings(): array {
    return $this->revealjs_plugin_settings;
  }

  /**
   * Get the config options set for this presentation.
   * @see https://revealjs.com/config/
   *
   * @return string
   *  The config options set for this presentation in YAML format.
   */
  public function getConfigOptions() {
    return $this->revealjs_config_options;
  }

  /**
   * Get the config options set for this presentation as an array.
   *
   * @return array
   *   The config options set for this presentation as an array.
   */
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

  /**
   * Add a new slide to the presentation.
   *
   * @param array $slide_data
   *   The slide data.
   * @param int $position
   *   The position to insert the slide at.
   *  If not set, the slide will be added to the end.
   * @return string
   *   The UUID key of the slide.
   */
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

  /**
   * Remove a slide from the presentation.
   *
   * @param string $key
   *  The key of the slide to remove.
   */
  public function removeSlide($key) {
    unset($this->slides[$key]);
  }

  /**
   * Get all slides.
   *
   * @return array
   *  The slides array.
   */
  public function getSlides() {
    return $this->slides;
  }

  /**
   * Get a slide by key.
   *
   * @param string $key
   *  Key of the slide
   * @return array
   *  The slide data array.
   */
  public function getSlide($key) {
    return $this->slides[$key];
  }

  /**
   * Set a slide by key.
   *
   * @param string $key
   *  Key of the slide
   * @param array $slide
   *  The slide data array.
   */
  public function setSlide(string $key, array $slide) {
    $this->slides[$key] = $slide;
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

  /**
   * Get the list of events supported revealjs.
   *
   * @return array
   *  List of events.
   */
  public static function events(): array {
    return [
      'ready' => t('Ready'),
      'slidechanged' => t('Slide Changed'),
      'slidetransitionend' => t('Slide Transition End'),
      'resize' => t('Resize'),
    ];
  }

  /**
   * Get plugin instances enabled on this presentation.
   *
   * @return array
   *  List of plugin instances. Keyed by plugin id.
   */
  public function getPluginInstances() {
    /** @var \Drupal\present\Plugin\RevealJSPlugin\RevealJSPluginManager */
    $revealjs_plugin_manager = \Drupal::service('plugin.manager.revealjs_plugins');

    foreach ($this->revealjs_plugins as $plugin_id) {
      if (!isset($this->plugin_instances[$plugin_id])) {
        /** @var \Drupal\present\Plugin\RevealJSPlugin\RevealJSPlugin $plugin */
        $plugin = $revealjs_plugin_manager->getPlugin($plugin_id, $this);
        $this->plugin_instances[$plugin_id] = $plugin;
      }
    }
    return $this->plugin_instances;
  }
}
