<?php
/**
 * Created by PhpStorm.
 * User: kadantsew
 * Date: 07.02.17
 * Time: 12:17
 */


namespace Drupal\testmodule\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Administration settings form.
 */
 class TestForm extends FormBase {

    /**
     * {@inheritdoc}
     */
    public function getFormID() {
        return 'testmodule_form';
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(array $form, FormStateInterface $form_state) {

        $form['services'] = array(
            '#type' => 'select',
            '#title' => t('Change the source'),
             '#options' => array(
                'kantor' => 'kantor.com.ua',
                'goverla' => 'goverla.ua',
                'express' => 'express.lutsk.ua',
            ),
        );

        $form['submit'] = array(
          '#type' => 'submit',
            '#value' => 'Submit',
        );

        return $form;
    }

    /**
     * {@inheritdoc}
     */
    public function submitForm(array &$form, FormStateInterface $form_state) {
        drupal_set_message($this->t('Your service is @services', array('@services' => $form['services']['#options'][$form_state->getValue('services')])));
    }

}
