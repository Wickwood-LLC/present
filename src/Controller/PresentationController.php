<?php

namespace Drupal\present\Controller;

use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Render\Markup;
use Drupal\Core\Url;
use Drupal\present\Element\Slide;
use Drupal\present\Entity\Presentation;
use Drupal\present\Event\VimeoPlayerEvent;
use Drupal\user\UserInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Yaml\Yaml;

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
  public function present(Request $request, UserInterface $user, Presentation $presentation) {

    $slides = [];

    if ($user->isAnonymous()) {
      $user_code = $request->cookies->get('user_code');
      if (!empty($user_code)) {
        $url = Url::fromRoute('present.presentation.' . $presentation->id(), ['user' => $user_code, 'presentation' => $presentation->id()]);
      }
      else {
        $url = Url::fromRoute('present.presentation_registration.' . $presentation->id(), ['presentation' => $presentation]);
      }
      return new RedirectResponse($url->toString());
    }

    $config = \Drupal::config('present.settings');

    $user_code = $request->attributes->get('_raw_variables')->get('user');
    setcookie('user_code', $user_code, time() + $config->get('user_code_cookied_validity'), '/');

    foreach ($presentation->getSlides() as $slide_data) {

      $slide = [
        '#type' => 'revealjs_slide',
      ];
      if ($slide_data['auto_animate']) {
        $slide['#attributes']['data-auto-animate'] = TRUE;
      }
      if (!empty($slide_data['auto_animate_id'])) {
        $slide['#attributes']['data-auto-animate-id'] = $slide_data['auto_animate_id'];
      }
      if ($slide_data['auto_animate_restart']) {
        $slide['#attributes']['data-auto-animate-restart'] = TRUE;
      }
      if ($slide_data['type'] == Slide::TYPE_RENDER_ARRAY) {
        $slide['#content'] = Yaml::parse($slide_data['content']);
      }
      else {
        $slide['#content'] = [
          '#markup' => Markup::create($slide_data['content']),
        ];
      }
      $slides[] = $slide;
    }
    $reveal_theme = $request->query->get('theme');

    if (!$reveal_theme) {
      $reveal_theme = $presentation->getTheme();
      if ($reveal_theme == '__none') {
        $reveal_theme = NULL;
      }
    }
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
          'tags' => [$presentation->getEntityTypeId() . ':' . $presentation->id()],
        ],
      ],
      'footer' => [
        '#markup' => '<div><a href="tel:+15189510656">Call</a> <a href="mailto:withus@wickwood.net?subject=Please%20Contact%20Me%20About%20Capstone%20Plus">Message</a></div>',
      ],
    ];
  }

  /**
   * The _title_callback for the presentaiton page
   *
   * @param \Drupal\present\Entity\Presentation $presentation
   *   The presentation.
   *
   * @return string
   *   The presentation title.
   */
  public function presentationTitle(Presentation $presentation) {
    return $presentation->label();
  }

  /**
   * The _title_callback for the presentaiton registration page
   *
   * @param \Drupal\present\Entity\Presentation $presentation
   *   The presentation.
   *
   * @return string
   *   The presentation registration title.
   */
  public function registrationTitle(Presentation $presentation) {
    return $this->t('Register the %presentation presentation', ['%presentation' => $presentation->label()]);
  }

  public function registration(Presentation $presentation) {
    $user_storage = $this->entityTypeManager()->getStorage('user');
    /** @var \Drupal\Core\Password\DefaultPasswordGenerator */
    $password_generator = \Drupal::service('password_generator');

    /** @var \Drupal\user\UserInterface */
    $new_user = $user_storage->create([]);
    $new_user->setPassword($password_generator->generate(12));

    $form_state_additions = [
      'presentation_id' => $presentation->id(),
    ];

    $config = \Drupal::config('present.settings');
    $form_mode = $config->get('registration_form_mode');
    if (empty($form_mode)) {
      $form_mode = 'default';
    }
    $user_register_form = $this->entityFormBuilder()->getForm($new_user, $form_mode, $form_state_additions);

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
