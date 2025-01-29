<?php

namespace Drupal\present\Controller;

use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Url;
use Drupal\present\Event\VimeoPlayerEvent;
use Drupal\user\UserInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

/**
 * Controller for building reveal.js presentations.
 */
class PresentationController extends ControllerBase {

  public function __construct(
    #[Autowire(service: 'event_dispatcher')]
    protected EventDispatcherInterface $eventDispatcher
  ) {
    
  }


  /**
   * Build the block instance add form.
   */
  public function present(Request $request, UserInterface $user) {

    $slides = [];

    if ($user->isAnonymous()) {
      $user_code = $request->cookies->get('user_code');
      if (!empty($user_code)) {
        $url = Url::fromRoute('present.presentation', ['user' => $user_code]);
      }
      else {
        $url = Url::fromRoute('present.presentation_registration');
      }
      return new RedirectResponse($url->toString());
    }


    $user_code = $request->attributes->get('_raw_variables')->get('user');
    setcookie('user_code', $user_code, time() + 7 * 24 * 60 * 60);

    $media_storage = $this->entityTypeManager()->getStorage('media');
    $media_view_builder = $this->entityTypeManager()->getViewBuilder('media');

    $media_1 = $media_storage->load(129);
    // $media_2 = $media_storage->load(134);

    // $slides[] = [
    //   '#type' => 'revealjs_slide',
    //   '#content' => [
    //     '#type' => 'present_vimeo_player',
    //     '#options' => [
    //       'url' => 'https://vimeo.com/1047002014/725dcc9318',
    //       'width' => 640,
    //     ],
    //   ],
    // ];

    // $slides[] = [
    //   '#type' => 'revealjs_slide',
    //   '#content' => $media_view_builder->view($media_1, 'player'),
    // ];

    $vimeo_video = [
      '#type' => 'present_vimeo_player',
      '#options' => [
        'url' => [
          'landscape' => 'https://player.vimeo.com/video/1047002014?h=725dcc9318&title=0&byline=0&portrait=0&badge=0&autopause=0&player_id=0&app_id=58479', // https://vimeo.com/1047002014/725dcc9318
          'portrait' => 'https://player.vimeo.com/video/1047002053?h=8d6fd16ee5&title=0&byline=0&portrait=0&badge=0&autopause=0&player_id=0&app_id=58479',
        ],
        // 'url' => 'https://vimeo.com/65226146',
        // 'width' => 640,
        'responsive' => true,
        // 'autoplay' => true,
        'play_button_position' => 'center',
        'title' => false,
        'portrait' => false,
        'byline' => false,
        'vimeo_logo' => false,
      ],
      '#events_to_fire' => ['ended'],
    ];

    $slides[] = [
      '#type' => 'revealjs_slide',
      '#content' => $vimeo_video,
      '#cache' => [
        'max-age' => 0,
      ],
    ];

    $slides[] = [
      '#type' => 'revealjs_slide',
      '#content' => [
        '#type' => 'inline_template',
        '#template' => '<div><a href="{{ link }}"><img src="{{ img_src }}" /></a></div>',
        '#context' => [
          'img_src' => '/sites/2025-01-28.a.2.dev.wickwood.biz/files/media/images/crop-duplicate-1-for-p-16723-44766-413540-fs.png',
          'link' => 'https://calendly.com/wickwood/book-a-call-for-a-capstone-review',
        ],
      ],
    ];
    $reveal_theme = $request->query->get('theme') ?? 'black';
    return [
      'presentation' => [
        '#type' => 'revealjs_presentation',
        '#slides' => $slides,
        '#options' => [
          'theme' => $reveal_theme,
        ],
        '#cache' => [
          'max-age' => 0,
          'contexts' => ['url.query_args:theme'],
        ],
      ],
      'footer' => [
        '#markup' => '<div><a href="tel:+15189510656">Call</a> <a href="mailto:withus@wickwood.net?subject=Please%20Contact%20Me%20About%20Capstone%20Plus">Message</a></div>',
      ],
      // 'vimeo' => [
      //   '#type' => 'revealjs_slide',
      //   '#content' => $vimeo_video,
      //   '#cache' => [
      //     'max-age' => 0,
      //   ],
      // ],
    ];
  }

  public function registration() {
    $user_storage = $this->entityTypeManager()->getStorage('user');
    /** @var \Drupal\Core\Password\DefaultPasswordGenerator */
    $password_generator = \Drupal::service('password_generator');

    /** @var \Drupal\user\UserInterface */
    $new_user = $user_storage->create([]);
    $new_user->setPassword($password_generator->generate(12));

    $form_state_additions = [
      'presentation_path' => '/capstone-health/plus/generic-presentation',
    ];
    $user_register_form = $this->entityFormBuilder()->getForm($new_user, 'presentation', $form_state_additions);

    return [
      'form' => $user_register_form,
    ];
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
  public function vimeoEvent(Request $request): Response {
    // Get data from the POST request.
    $data = $request->request->all();

    $vimeo_event = new VimeoPlayerEvent($data['name'], $data['data'], $data['embed_options']);
    $this->eventDispatcher->dispatch($vimeo_event, VimeoPlayerEvent::VIMEO_PLAYER_EVENT);

    $response = new AjaxResponse();
    return $response;
  }

}
