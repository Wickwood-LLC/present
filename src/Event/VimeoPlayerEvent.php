<?php

namespace Drupal\present\Event;

use Drupal\Component\EventDispatcher\Event;

/**
 * Defines the vimeo player event.
 */
class VimeoPlayerEvent extends Event {

  const VIMEO_PLAYER_EVENT = 'present.vimeo.present';

  /**
   * Name of the vimeo event
   * 
   * This will be any of events listed at https://github.com/vimeo/player.js?tab=readme-ov-file#events
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
   * The embed options used for the player
   *
   * @var array
   */
  protected $embed_options;

  /**
   * Constructs a new AvailableCountriesEvent object.
   *
   * @param string $name
   *   The vimeo player event
   * @param array $data
   *   The event data
   */
  public function __construct(string $name, array $data, array $embed_options) {
    $this->name = $name;
    $this->data = $data;
    $this->embed_options = $embed_options;
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
   * Gets the vimeo emebed options.
   *
   * @return array
   *   The emebed options
   */
  public function getEmbedOptions(): array {
    return $this->embed_options;
  }
}
