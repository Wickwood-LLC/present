<?php

namespace Drupal\present\Form;

use Drupal\user\RegisterForm;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;
use Symfony\Component\HttpFoundation\RedirectResponse;

/**
 * Custom form class for the "custom_register" form mode.
 */
class UserPresentationRegisterForm extends RegisterForm {

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    // Build the form using the parent class.
    $form = parent::buildForm($form, $form_state);

    // Presentation registration does not require password and username to set.
    $form['account']['pass']['#access'] = FALSE;
    $form['account']['pass']['#required'] = FALSE;
    $form['account']['name']['#access'] = FALSE;
    $form['account']['status']['#access'] = FALSE;
    $form['account']['roles']['#access'] = FALSE;
    $form['account']['notify']['#access'] = FALSE;

    return $form;
  }

  public function validateForm(array &$form, FormStateInterface $form_state) {
    $email = $form_state->getValue('mail');

    $form_state->setValue('name', $email);

    $user_storage = \Drupal::entityTypeManager()->getStorage('user');
    $user_ids = $user_storage->getQuery()->accessCheck(FALSE)
      ->condition('mail', $email)
      ->execute();

    parent::validateForm($form, $form_state);

    if (!empty($user_ids)) {
      $form_state->set('existing_user_id', reset($user_ids));
      $form_state->clearErrors();
    }
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $existing_user_id = $form_state->get('existing_user_id');
    if ($existing_user_id) {
      $this->setPresentationRedirection($existing_user_id, $form_state);
      \Drupal::messenger()->addMessage('You are already registered!');
    }
    else {
      // Perform custom submission logic for this form mode.
      \Drupal::messenger()->addMessage('Custom registration form submitted!');

      // Call the parent submission logic.
      parent::submitForm($form, $form_state);
    }
  }

  public function save(array $form, FormStateInterface $form_state) {
    $existing_user_id = $form_state->get('existing_user_id');
    if (!$existing_user_id) {
      parent::save($form, $form_state);
      $account = $form_state->get('user');
      $this->setPresentationRedirection($account->id(), $form_state);
    }
  }

  protected function setPresentationRedirection($user_id, FormStateInterface $form_state) {
    $config = \Drupal::config('present.settings');
    $presentation_id = $form_state->get('presentation_id');
    /** @var \Drupal\present\ParamConverter\UserCodeToEntityConverter */
    $user_code_service = \Drupal::service('present.user_code');
    $user_code = $user_code_service->getUserCode($user_id);
    setcookie('user_code', $user_code, time() + $config->get('user_code_cookied_validity'), '/');
    $presentation_redirect = Url::fromRoute('present.presentation.' . $presentation_id, ['user' => $user_code, 'presentation' => $presentation_id])
      ->toString();
    $form_state->set('presentation_redirect', $presentation_redirect);
    $form_state->setResponse(new RedirectResponse($presentation_redirect));
  }
}
