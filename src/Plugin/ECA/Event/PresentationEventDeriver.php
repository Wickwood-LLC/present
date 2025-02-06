<?php

namespace Drupal\present\Plugin\ECA\Event;

use Drupal\eca\Plugin\ECA\Event\EventDeriverBase;

/**
 * Deriver for ECA VimeoPlayer event plugins.
 */
class PresentationEventDeriver extends EventDeriverBase {

  /**
   * {@inheritdoc}
   */
  protected function definitions(): array {
    return PresentationEvent::definitions();
  }

}
