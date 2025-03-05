<?php

namespace Drupal\present\Controller;

use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Controller\ControllerBase;
use Drupal\present\Entity\Presentation;
use Drupal\present\Event\PresentationEvent;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

/**
 * Controller for presentations.
 */
class PresentationController extends ControllerBase {

  public function __construct(
    #[Autowire(service: 'event_dispatcher')]
    protected EventDispatcherInterface $eventDispatcher
  ) {

  }

  /**
   * Responds to the AJAX request.
   *
   * @param \Symfony\Component\HttpFoundation\Request $request
   *   The incoming request object.
   *
   * @return \Symfony\Component\HttpFoundation\Response
   *   The AJAX response containing the rendered entity content.
   */
  public function event(Request $request): Response {
    // Get data from the POST request.
    $data = $request->request->all();

    $presentation_storage = \Drupal::entityTypeManager()->getStorage('presentation');

    $presentation = $presentation_storage->load($data['presentation_id']);
    if ($presentation) {
      $presentation_event = new PresentationEvent($data['name'], $data['data'], $presentation);
      $this->eventDispatcher->dispatch($presentation_event, 'presentation.' . $data['name']);
    }

    $response = new AjaxResponse();
    return $response;
  }

  /**
   * Preview a presentation.
   *
   * @param \Drupal\present\Entity\Presentation $presentation
   *   The presentation to preview.
   *
   * @return array
   *   A render array.
   */
  public function preview(Presentation $presentation) {
    return [
      '#type' => 'revealjs_presentation',
      '#presentation' => $presentation,
      '#attributes' => [
        // Allow the presentation to be resized.
        'style' => ['margin: auto; resize: both;'],
      ]
    ];
  }

  /**
   * Title for the preview page.
   *
   * @param \Drupal\present\Entity\Presentation $presentation
   *   The presentation to preview.
   *
   * @return \Drupal\Core\StringTranslation\TranslatableMarkup
   *   The title.
   */
  public function previewTitle(Presentation $presentation) {
    return $this->t('<em>Preview Presentation</em> @title', ['@title' => $presentation->label(),]);
  }

}
