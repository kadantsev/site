<?php

namespace Drupal\exchange_rate\Plugin\Block;

use Drupal\Core\Block\BlockBase;
//use Drupal\exchange_rate\Plugin\Rate;
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

    public function build()
    {
        return array (
            '#theme' => 'exchange_rate',
            '#row' => 'hello',
        );
    }
}
