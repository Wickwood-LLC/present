<?php

namespace Drupal\present\EventSubscriber;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Routing\RouteSubscriberBase;
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
   * Resets the internal state of the route subscriber.
   */
  // public function reset() {
  //   $this->viewsDisplayPairs = NULL;
  // }

  /**
   * {@inheritdoc}
   */
  // public static function getSubscribedEvents(): array {
  //   $events = parent::getSubscribedEvents();
  //   $events[RoutingEvents::FINISHED] = ['routeRebuildFinished'];
  //   // Ensure to run after the entity resolver subscriber
  //   // @see \Drupal\Core\EventSubscriber\EntityRouteAlterSubscriber
  //   $events[RoutingEvents::ALTER] = ['onAlterRoutes', -175];

  //   return $events;
  // }

  // /**
  //  * Gets all the views and display IDs using a route.
  //  */
  // protected function getViewsDisplayIDsWithRoute() {
  //   if (!isset($this->viewsDisplayPairs)) {
  //     $this->viewsDisplayPairs = [];

  //     // @todo Convert this method to some service.
  //     $views = $this->getApplicableViews();
  //     foreach ($views as $data) {
  //       [$view_id, $display_id] = $data;
  //       $this->viewsDisplayPairs[] = $view_id . '.' . $display_id;
  //     }
  //     $this->viewsDisplayPairs = array_combine($this->viewsDisplayPairs, $this->viewsDisplayPairs);
  //   }
  //   return $this->viewsDisplayPairs;
  // }

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
      $route = new Route(
        rtrim($presentation->getPath(), '/') . '/' . '{user}',
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
    }

    // $this->state->set('views.view_route_names', $this->viewRouteNames);
    return $collection;
  }

  /**
   * {@inheritdoc}
   */
  // protected function alterRoutes(RouteCollection $collection) {
  //   foreach ($this->getViewsDisplayIDsWithRoute() as $pair) {
  //     [$view_id, $display_id] = explode('.', $pair);
  //     $view = $this->viewStorage->load($view_id);
  //     // @todo This should have an executable factory injected.
  //     if (($view = $view->getExecutable()) && $view instanceof ViewExecutable) {
  //       if ($view->setDisplay($display_id) && $display = $view->displayHandlers->get($display_id)) {
  //         if ($display instanceof DisplayRouterInterface) {
  //           // If the display returns TRUE a route item was found, so it does not
  //           // have to be added.
  //           $view_route_names = $display->alterRoutes($collection);
  //           $this->viewRouteNames = $view_route_names + $this->viewRouteNames;
  //           foreach ($view_route_names as $id_display => $route_name) {
  //             $view_route_name = $this->viewsDisplayPairs[$id_display];
  //             unset($this->viewsDisplayPairs[$id_display]);
  //             $collection->remove("views.$view_route_name");
  //           }
  //         }
  //       }
  //       $view->destroy();
  //     }
  //   }
  // }

  /**
   * Stores the new route names after they have been rebuilt.
   *
   * Callback for the RoutingEvents::FINISHED event.
   *
   * @see \Drupal\views\EventSubscriber::getSubscribedEvents()
   */
  // public function routeRebuildFinished() {
  //   $this->reset();
  //   $this->state->set('views.view_route_names', $this->viewRouteNames);
  // }

  /**
   * Returns all views/display combinations with routes.
   *
   * @see \Drupal\views\Views::getApplicableViews()
   */
  // protected function getApplicableViews() {
  //   return Views::getApplicableViews('uses_route');
  // }

}
