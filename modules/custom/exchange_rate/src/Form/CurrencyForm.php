<?php
/**
 * Created by PhpStorm.
 * User: kadantsew
 * Date: 07.02.17
 * Time: 12:17
 */


namespace Drupal\exchange_rate\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormState;
use Drupal\Core\Form\FormStateInterface;

use Drupal\Core\Render\Element\Form;
use Drupal\exchange_rate\GoverlaRate;
use Drupal\exchange_rate\KantorRate;
use Drupal\exchange_rate\ExpresRate;
use Drupal\Core\Url;

/**
 * Administration settings form.
 */
 class CurrencyForm extends FormBase {

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
        if  (\Drupal::currentUser()->hasPermission('change currency service')) {
            $form['services'] = array(
                '#type' => 'select',
                '#title' => t('Change the source'),
                '#options' => array(
                    'Kantor' => 'kantor.com.ua',
                    'Goverla' => 'goverla.ua',
                    'Expres' => 'express.lutsk.ua',
                ),
                '#default_value' => $form_state->getValue('services'),
            );

            $form['submit'] = array(
                '#type' => 'submit',
                '#value' => 'Submit',
            );
            $param = \Drupal::request()->query->all();
            $a =1;
            if ($param!=null && array_key_exists($param['services'], $form['services']['#options'])) {

                $classname = '\Drupal\exchange_rate\\' . $param['services'] . 'Rate';
                $cantor = new $classname;
                $result = $cantor->getRate();
                $form['services']['#default_value'] = $param;
            } else {
                $cantor = new KantorRate;
                $result = $cantor->getRate();
            }

        }
        else {
            $cantor = new KantorRate;
            $result = $cantor->getRate();
        }

        $content = [
            '#theme' => 'exchange_rate',
            '#head' => ['Currency', 'Buy', 'Sell'],
            '#content' => $result,
            '#attached' => array(
                'library' => array(
                    'exchange_rate/main',
                ),
            ),
        ];

        $form['content'] = array(
            '#markup' => \Drupal::service('renderer')->render($content),
        );

        return $form;
    }

    /**
     * {@inheritdoc}
     */
    public function submitForm(array &$form, FormStateInterface $form_state) {
        $url = Url::fromRoute('<current>');
        $route_name = $url->getRouteName();
        $form_state->setRedirect($route_name, ['services' => $form_state->getValue('services')]);

        drupal_set_message($this->t('Your service is @services', array('@services' => $form['services']['#options'][$form_state->getValue('services')])));

    }

}
//\Drupal::service('current_route_match')->getRouteName()
//\Drupal::routeMatch()->getRouteName()