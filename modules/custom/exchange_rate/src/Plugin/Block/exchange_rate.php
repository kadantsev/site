<?php

namespace Drupal\exchange_rate\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\exchange_rate\Rate;

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
                0 => 'kantor.com.ua',
                1 => 'goverla.ua',
                2 => 'express.lutsk.ua',
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
        switch ($this->configuration['services']) {
            case 0:
                $cantor = new Rate('http://kantor.com.ua');
                $cantor->setSelector('//tbody/tr', "td/img/@src", "td/span", "td[2]", "td[3]");
                $result = $cantor->getRate();
                break;
            case 1:
                $cantor = new Rate('https://goverla.ua');
                $cantor->setSelector("//div[contains(@id,'rates')]//div//div//div//div//div[contains(@class,'gvrl-table-body')]//div[contains(@class,'gvrl-table-row')]", "div/img/@src", "div/img/@alt", "div[2]", "div[3]");
                $result = $cantor->getRate();
                break;
            case 2:
                $express = new Rate('http://express.lutsk.ua');
                $express->setSelector('//table[1]/tbody/tr[position()>1]', "td/img/@src", "td[2]", "td[3]", "td[4]");
                $result = $express->getRate();
                break;
        }

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
