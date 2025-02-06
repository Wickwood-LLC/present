<?php

namespace Drupal\present\Plugin\ECA\Event;

use Drupal\eca\Attributes\Token;
use Drupal\eca\Event\Tag;
use Drupal\eca\Plugin\DataType\DataTransferObject;
use Drupal\eca\Plugin\ECA\Event\EventBase;
use Drupal\present\Event\PresentationEvent as Event;

/**
 * Plugin implementation of the ECA Events for presentation event.
 *
 * @EcaEvent(
 *   id = "presentation_event",
 *   deriver = "Drupal\present\Plugin\ECA\Event\PresentationEventDeriver",
 *   eca_version_introduced = "2.1.1"
 * )
 */
class PresentationEvent extends EventBase {

  /**
   * {@inheritdoc}
   */
  public static function definitions(): array {
    return [
      Event::READY => [
        'label' => 'Presentation Ready',
        'event_name' => Event::READY,
        'event_class' => Event::class,
        'tags' => Tag::CONTENT | Tag::VIEW,
        'description' => t('Fires when the presentation is loaded and ready. Make sure you enabled to track Ready event in the presentation edit page.'),
      ],
      Event::SLIDE_CHANGED => [
        'label' => 'Presentation Slide Changed',
        'event_name' => Event::SLIDE_CHANGED,
        'event_class' => Event::class,
        'tags' => Tag::CONTENT | Tag::VIEW,
        'description' => t('Fires when the presentation changes from one slide to another. Make sure you enabled to track Slide Changed event in the presentation edit page.'),
      ],
      Event::SLIDE_TRANSITION_END => [
        'label' => 'Presentation Slide Transition End',
        'event_name' => Event::SLIDE_TRANSITION_END,
        'event_class' => Event::class,
        'tags' => Tag::CONTENT | Tag::VIEW,
        'description' => t('Fires when the presentation slide change is finished. Make sure you enabled to track Slide Transition End event in the presentation edit page.'),
      ],
      Event::RESIZE => [
        'label' => 'Presentation Resize',
        'event_name' => Event::RESIZE,
        'event_class' => Event::class,
        'tags' => Tag::CONTENT | Tag::VIEW,
        'description' => t('Fires when the presentation is resized. Make sure you enabled to track Resize event in the presentation edit page.'),
      ],
    ];
  }

  /**
   * {@inheritdoc}
   */
  #[Token(
    name: 'presentation_event_name',
    description: 'The name of the presentation event.',
  )]
  #[Token(
    name: 'presentation_event_data',
    description: 'The presentation event data.',
  )]
  #[Token(
    name: 'presentation',
    description: 'The presentation config entity.',
  )]
  public function getData(string $key): mixed {
    /** @var \Drupal\present\Event\PresentationEvent */
    $event = $this->event;
    if ($key == 'presentation_event_name') {
      return $event->getName();
    }
    else if ($key == 'presentation_event_data') {
      return DataTransferObject::create($event->getData());
    }
    else if ($key == 'presentation') {
      return DataTransferObject::create($event->getPresentation());
    }
    return parent::getData($key);
  }

}
