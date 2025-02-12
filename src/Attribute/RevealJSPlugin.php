<?php

declare(strict_types = 1);

namespace Drupal\present\Attribute;

use Drupal\Component\Plugin\Attribute\Plugin;

/**
 * Defines a RevealJS plugin attribute object.
 *
 * Plugin Namespace: Plugin\RevealJS\Plugin
 *
 * For a working example, see \Drupal\present\Plugin\RevealJS\Plugin\FullAudio.
 *
 * @see \Drupal\Core\Render\ElementInfoManager
 * @see \Drupal\Core\Render\Element\ElementInterface
 * @see \Drupal\Core\Render\Element\RenderElementBase
 * @see \Drupal\Core\Render\Attribute\FormElement
 * @see plugin_api
 *
 * @ingroup theme_render
 */
#[\Attribute(\Attribute::TARGET_CLASS)]
class RevealJSPlugin extends Plugin {
}
