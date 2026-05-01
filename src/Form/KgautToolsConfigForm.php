<?php

declare(strict_types=1);

namespace Drupal\kgaut_tools\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Configuration form for the kgaut_tools module.
 */
final class KgautToolsConfigForm extends ConfigFormBase {

  /**
   * The configuration object name.
   */
  private const CONFIG_NAME = 'kgaut_tools.config';

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames(): array {
    return [self::CONFIG_NAME];
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId(): string {
    return 'kgaut_tools_config_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state): array {
    $config = $this->config(self::CONFIG_NAME);

    $form['medias'] = [
      '#type' => 'fieldset',
      '#title' => $this->t('Media management'),
    ];
    $form['medias']['disable_image_derivate'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Disable automatic image derivative generation.'),
      '#default_value' => (bool) $config->get('disable_image_derivate'),
    ];

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state): void {
    $this->config(self::CONFIG_NAME)
      ->set('disable_image_derivate', (bool) $form_state->getValue('disable_image_derivate'))
      ->save();
    parent::submitForm($form, $form_state);
  }

}
