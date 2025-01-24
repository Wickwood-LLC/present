<?php

namespace Drupal\present\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\user\UserInterface;

/**
 * Controller for building reveal.js presentations.
 */
class PresentationController extends ControllerBase {

  /**
   * Build the block instance add form.
   */
  public function present(UserInterface $user) {

    $slides = [];

    if ($user->isAnonymous()) {
      $user_storage = \Drupal::entityTypeManager()->getStorage('user');
      /** @var \Drupal\Core\Password\DefaultPasswordGenerator */
      $password_generator = \Drupal::service('password_generator');

      /** @var \Drupal\user\UserInterface */
      $new_user = $user_storage->create([]);
      $new_user->setPassword($password_generator->generate(12));

      $user_register_form = \Drupal::service('entity.form_builder')->getForm($new_user, 'presentation');

      $slides[] = [
        '#type' => 'revealjs_slide',
        '#content' => $user_register_form,
      ];
    }

    $media_storage = \Drupal::entityTypeManager()->getStorage('media');
    $media_view_builder = \Drupal::entityTypeManager()
          ->getViewBuilder('media');

    $media_1 = $media_storage->load(129);
    $media_2 = $media_storage->load(134);

    $slides[] = [
      '#type' => 'revealjs_slide',
      '#content' => $media_view_builder->view($media_1, 'player'),
    ];

    $slides[] = [
      '#type' => 'revealjs_slide',
      '#content' => [
        '#type' => 'inline_template',
        '#template' => '<div><a href="{{ link }}"><img src="{{ img_src }}" /></a></div><div><button>Call</button><button>Message</button></div>',
        '#context' => [
          'img_src' => '/sites/default/files/media/images/crop-duplicate-1-for-p-16723-44766-413540-fs.png',
          'link' => 'https://calendly.com/wickwood/book-a-call-for-a-capstone-review',
        ],
      ],
    ];
    return [
      '#type' => 'revealjs_presentation',
      '#slides' => $slides,
      '#cache' => [
        'max-age' => 0,
      ],
    ];
  }

}
