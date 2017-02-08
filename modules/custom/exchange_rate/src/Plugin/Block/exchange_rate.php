<?php

namespace Drupal\exchange_rate\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Form\FormStateInterface;
//use Drupal\exchange_rate\Rate;
use Drupal\exchange_rate\GoverlaRate;
use Drupal\exchange_rate\KantorRate;
use Drupal\exchange_rate\ExpresRate;

/**
 * @Block(
 *  id = "exchange_rate_block",
 *  admin_label = @Translation("Exchange_Rate"),
 *  category = @Translation("Custom")
 * )
 */



class Exchange_Rate extends BlockBase {

    /**
     * {@inheritdoc}
     */
    public function defaultConfiguration()
    {
        return [
            'services' => '',
        ];
    }
    /**
     * {@inheritdoc}
     */
    public function blockForm($form, FormStateInterface $form_state)
    {
        $form['services'] = [
            '#type' => 'select',
            '#title' => t('Service'),
            '#options' => array(
                'Kantor' => 'kantor.com.ua',
                'Goverla' => 'goverla.ua',
                'Expres' => 'express.lutsk.ua',
            ),
            '#default_value' => $this->configuration['services'],
        ];
        return $form;
    }
    /**
     * {@inheritdoc}
     */
    public function blockSubmit($form, FormStateInterface $form_state)
    {
        $this->configuration['services'] = $form_state->getValue('services');

    }
    /**
     * {@inheritdoc}
     */
    public function build()
    {
        $classname = '\Drupal\exchange_rate\\'. $this->configuration['services'] .'Rate';
        $cantor = new $classname;
        $result = $cantor->getRate();

        return array (
            '#theme' => 'exchange_rate',
            '#head' => ['Currency', 'Buy', 'Sell'],
            '#content' => $result,
            '#attached' => array(
                'library' => array(
                    'exchange_rate/main',
                ),
            ),
        );
    }
}
