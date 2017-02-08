<?php

namespace Drupal\exchange_rate\Plugin\Block;

use Drupal\Core\Block\BlockBase;
//use Drupal\Core\Form\FormStateInterface;
//use Drupal\exchange_rate\Rate;

/**
 * @Block(
 *  id = "exchange_rate_form_block",
 *  admin_label = @Translation("Exchange_Rate_Form"),
 *  category = @Translation("Custom")
 * )
 */
class ExchangeRateForm extends BlockBase {

    /**
     * {@inheritdoc}
     */
    public function build()
    {
        $content = \Drupal::formBuilder()->getForm('Drupal\exchange_rate\Form\CurrencyForm');
        $output = \Drupal::service('renderer')->render($content);
        return array (
            '#markup' => $output,
        );
    }
}
