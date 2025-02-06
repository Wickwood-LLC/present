<?php

namespace Drupal\present\Event;

use Drupal\Component\EventDispatcher\Event;
use Drupal\present\Entity\Presentation;

/**
 * Defines the presentation event.
 */
class PresentationEvent extends Event {

  const READY = 'presentatin.ready';
  const SLIDE_CHANGED = 'presentatin.slidechanged';
  const SLIDE_TRANSITION_END = 'presentatin.slidetransitionend';
  const RESIZE = 'presentatin.resize';

  /**
   * Name of the event
   * 
   * This will be any of events listed at https://revealjs.com/events/
   * @var string
   */
  protected $name;

  /**
   * The event data
   *
   * @var array
   */
  protected $data;

  /**
   * The owner presentation of the event
   *
   * @var \Drupal\present\Entity\Presentation
   */
  protected $presentation;

  /**
   * Constructs a new AvailableCountriesEvent object.
   *
   * @param string $name
   *   The prsentaiton event
   * @param array $data
   *   The event data
   * @param \Drupal\present\Entity\Presentation $presentation
   *  the presentation object.
   */
  public function __construct(string $name, array $data, Presentation $presentation) {
    $this->name = $name;
    $this->data = $data;
    $this->presentation = $presentation;
  }

  /**
   * Gets the event name
   *
   * @return string
   *   The event name
   */
  public function getName(): string {
    return $this->name;
  }

  /**
   * Gets the event data
   *
   * @return array
   *   The event data
   */
  public function getData(): array {
    return $this->data;
  }

  /**
   * Get the presentation config entity.
   *
   * @return \Drupal\present\Entity\Presentation
   *   The presentation
   */
  public function getPresentation(): Presentation {
    return $this->presentation;
  }
}
