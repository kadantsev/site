<?php

namespace Drupal\open_weather\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

class ConfigForm extends ConfigFormBase {

    /**
     * {@inheritdoc}
     */

    public function getFormId() {
        return 'open_weather_admin_settings';
    }

    /**
     * {@inheritdoc}
     */

    protected  function getEditableConfigNames()
    {
        return ['open_weather.api'];
    }
    /**
     * {@inheritdoc}
     */

    public function buildForm(array $form, FormStateInterface $form_state)
    {
        $form['api_key'] = array(
          '#type' => 'textfield',
            '#title' => t('API Key'),
            '#default_value' => $this->config('open_weather.api')->get('api_key'),
        );
        return parent::buildForm($form, $form_state);
    }

    /**
     * {@inheritdoc}
     */

    public function submitForm(array &$form, FormStateInterface $form_state)
    {
        parent::submitForm($form, $form_state);
        $this->config('open_weather.api')->set('api_key', $form_state->getValue('api_key'))->save();
    }
}