<?php

namespace Drupal\present\Plugin\ECA\Event;

use Drupal\eca\Plugin\ECA\Event\EventDeriverBase;

/**
 * Deriver for ECA VimeoPlayer event plugins.
 */
class VimeoPlayerEventDeriver extends EventDeriverBase {

  /**
   * {@inheritdoc}
   */
  protected function definitions(): array {
    return VimeoPlayerEvent::definitions();
  }

}
