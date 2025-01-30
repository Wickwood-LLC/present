<?php

namespace Drupal\present\Plugin\ECA\Event;

use Drupal\eca\Attributes\Token;
use Drupal\eca\Event\Tag;
use Drupal\eca\Plugin\ECA\Event\EventBase;
use Drupal\present\Event\VimeoPlayerEvent as PlayerEvent;

/**
 * Plugin implementation of the ECA Events for vimeo player event.
 *
 * @EcaEvent(
 *   id = "present",
 *   deriver = "Drupal\present\Plugin\ECA\Event\VimeoPlayerEventDeriver",
 *   eca_version_introduced = "2.1.1"
 * )
 */
class VimeoPlayerEvent extends EventBase {

  /**
   * {@inheritdoc}
   */
  public static function definitions(): array {
    return [
      'vimeo_event' => [
        'label' => 'Vimeo event',
        'event_name' => PlayerEvent::VIMEO_PLAYER_EVENT,
        'event_class' => PlayerEvent::class,
        'tags' => Tag::CONTENT | Tag::VIEW,
      ],
    ];
  }

  /**
   * {@inheritdoc}
   */
  #[Token(
    name: 'vimeo_event_name',
    description: 'The name of the vimeo player event.',
    classes: [PlayerEvent::class],
  )]
  #[Token(
    name: 'vimeo_event_data',
    description: 'The Vimeo Player event data.',
    classes: [PlayerEvent::class],
    properties: [
      new Token(name: 'duration', description: 'Length of the video in seconds.'),
      new Token(name: 'percent', description: 'Current progress of the video as percentage value.'),
      new Token(name: 'seconds', description: 'Current progress of the video as number of sedond.'),
    ],
  )]
  #[Token(
    name: 'vimeo_embed_options',
    description: 'The Vimeo Player embed options used.',
    classes: [PlayerEvent::class],
    properties: [
      new Token(name: 'id', description: 'The video ID.'),
      new Token(name: 'url', description: 'The videp URL.'),
    ],
  )]
  public function getData(string $key): mixed {
    /** @var \Drupal\present\Event\VimeoPlayerEvent */
    $event = $this->event;
    if ($key == 'vimeo_event_name') {
      return $event->getName();
    }
    else if ($key == 'vimeo_event_data') {
      return $event->getData();
    }
    else if ($key == 'vimeo_embed_options') {
      return $event->getEmbedOptions();
    }
    return parent::getData($key);
  }

}
