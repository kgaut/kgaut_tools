<?php

namespace Drupal\kgaut_tools\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Class PopoteConfigForm.
 */
class KgautToolsConfigForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [
      'kgaut_tools.config',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'kgaut_tools_config_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config('kgaut_tools.config');

    $form['medias'] = [
      '#type' => 'fieldset',
      '#title' => t("Gestion des médias"),
    ];

    $form['medias']['disable_image_derivate'] = [
      '#type' => 'checkbox',
      '#title' => 'Desactiver la génération automatique des dérivés d\'images',
      '#default_value' => $config->get('disable_image_derivate'),
    ];
    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    parent::submitForm($form, $form_state);

    $this->config('kgaut_tools.config')
      ->set('disable_image_derivate', $form_state->getValue('disable_image_derivate'))
      ->save();

  }

}
