<?php

namespace Drupal\present\EventSubscriber;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;

/**
 * Builds up the routes of all presnetations.
 *
 * @see \Drupal\views\Plugin\views\display\PathPluginBase
 */
class RouteSubscriber {

  /**
   * The presentation storage.
   *
   * @var \Drupal\Core\Entity\EntityStorageInterface
   */
  protected $presentationStorage;

  /**
   * Constructs a \Drupal\present\EventSubscriber\RouteSubscriber instance.
   * 
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager service.
   */
  public function __construct(EntityTypeManagerInterface $entity_type_manager) {
    $this->presentationStorage = $entity_type_manager->getStorage('presentation');
  }

  /**
   * Returns a set of route objects.
   *
   * @return \Symfony\Component\Routing\RouteCollection
   *   A route collection.
   */
  public function routes() {
    $collection = new RouteCollection();
    
    $presentations = $this->presentationStorage->loadByProperties(['status' => TRUE]);
    foreach ($presentations  as $presentation) {
      /** @var \Drupal\present\Entity\Presentation $presentation */
      $route_name = 'present.presentation.' . $presentation->id();
      $base_path = rtrim($presentation->getPath(), '/') . '/' ;
      $base_path = '/' . ltrim($base_path, '/');
      $route = new Route(
        $base_path . '{user}',
        [
          '_controller' => '\Drupal\present\Controller\PresentationController::present',
          '_title_callback' => '\Drupal\present\Controller\PresentationController::presentationTitle',
          'user' => 0,
          'presentation' => $presentation->id(),
        ]
      );
      $route->addRequirements(['_permission' => 'access content']);
      $route->addOptions([
        'parameters' => [
          'user' => ['converter' => 'present.user_code'],
          'presentation' => ['type' => 'entity:presentation'],
        ],
      ]);
      $collection->add($route_name, $route);

      $registration_route = new Route(
        $base_path . 'registration',
        [
          '_controller' => '\Drupal\present\Controller\PresentationController::registration',
          '_title_callback' => '\Drupal\present\Controller\PresentationController::registrationTitle',
          'presentation' => $presentation->id(),
        ]
      );
      $registration_route->addRequirements(['_permission' => 'access content']);
      $registration_route->addOptions([
        'parameters' => [
          'presentation' => ['type' => 'entity:presentation'],
        ],
      ]);

      $collection->add('present.presentation_registration.' . $presentation->id(), $registration_route);
    }
    return $collection;
  }
}
