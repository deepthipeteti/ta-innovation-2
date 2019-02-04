<?php

namespace Drupal\face_registration\Form;

//use Aws\Rekognition\RekognitionClient;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
//use Drupal\user\UserStorageInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\user\Entity\User;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Drupal\file\FileInterface;

/**
 * Class FaceLoginForm.
 */
class FaceRegistrationForm extends FormBase {
 var  $file;
  /**
   * The user storage.
   *
   * @var \Drupal\user\UserStorageInterface
   */
  //protected $userStorage;

  /**
   * Constructs a new FaceLoginForm.
   * @param \Drupal\user\UserStorageInterface $user_storage
   *   The user storage.
   */
  /*public function __construct(UserStorageInterface $user_storage) {
    $this->userStorage = $user_storage;
  }*/

  /**
   * {@inheritdoc}
   */
  /*public static function create(ContainerInterface $container) {
    return new static(
      $container->get('entity.manager')->getStorage('user')
    );
  }*/

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'face_register';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    //drupal_set_message(t("No face detected.Please try again"),'error', TRUE);
    $form['webcam'] = [
      '#markup' => '<div id="webcam"></div>
      <div id="webcam_image"></div>',
    ];

    $form['target'] = [
      '#type' => 'hidden',
      //'#value' => '',
      '#attributes' => [
        'id' => 'file_target',
        'class' => ['target']
      ]
    ];
    $form['username'] = [
      '#type' => 'email',
      '#title' => $this
            ->t('Email id'),
      '#required' => TRUE,    
    ];
    $form['snap'] = [
      '#type' => 'button',
      '#value' => $this->t('Take snapshot'),
      '#attributes' => [
        'id' => 'snap',
      ]
    ];
    $form['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Submit'),
    ];

    $form['#attached']['library'][] = 'face_registration/webcamjs';
    $form['#attached']['library'][] = 'face_registration/face_registration';

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    $data = $form_state->getValues();
    $faces=0;
    if(!empty($data['target'])) {
    
    
 
  $result = \Drupal::service('google_vision.api')->faceDetection($data['target']);
  
  if(sizeof($result['responses']['0'])!=0) {
    $faces= sizeof($result['responses']['0']['faceAnnotations'],0);
  }

}

  if($faces!=1) {
   if($faces==0) {
    $form_state->setErrorByName('target', t('No face detected.Please try again'));

  }
  else {
    $form_state->setErrorByName('target', t('More than one face detected.Please try again'));
    
  }
}

  parent::validateForm($form, $form_state);
  }

 

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $data = $form_state->getValues();
    $uname= $data['username'];
    list($type, $d) = explode(';', $data['target']);
    list(, $d) = explode(',', $d);
    $picture = base64_decode($d);
   

    $filepath="saved_images/user".time().".png";
    file_put_contents("public://".$filepath, $picture);
    $picture = file_get_contents("public://".$filepath);
    $file = file_save_data($picture, "public://".$filepath, FILE_EXISTS_REPLACE);

    $user = User::create(
      array(
        'name' => 'test_user_'.time(),
        'mail' => $uname,
        'pass' => '123456',
        'status' => 1,
      )
    );
    $user->save();

    $user_id=$user->get('uid')->value;
    $user->user_picture->setValue(['target_id' => $file->id()]);
    $user->save();
    if(isset($user_id)) {
      $user = User::load($user_id);
      user_login_finalize($user);
      $user_destination = \Drupal::destination()->get();
      $response = new RedirectResponse($user_destination);
      $response->send();
    }
  }
}
