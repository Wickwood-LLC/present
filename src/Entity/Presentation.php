<?php

namespace Drupal\present\Entity;

use Drupal\Core\Config\Entity\ConfigEntityBase;

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
 *     "collection" = "/admin/structure/presentation",
 *   },
 *   config_export = {
 *     "id",
 *     "label",
 *     "status",
 *     "path",
 *     "revealjs_theme",
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
   * The path of the presentation.
   */
  protected $path;

  /**
   * The theme to use for the presentation.
   */
  protected $revealjs_theme;

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
  public function getPath() {
    return $this->path;
  }

  /**
   * {@inheritdoc}
   */
  public function getTheme() {
    return $this->revealjs_theme;
  }

  /**
   * {@inheritdoc}
   */
  public function setPath($path) {
    $this->path = $path;
    return $this;
  }

  public function addSlide() {
    $key = time();
    $this->slides[$key] = [
      'content' => '',
    ];
  }

  public function getSlides() {
    return $this->slides;
  }
}
