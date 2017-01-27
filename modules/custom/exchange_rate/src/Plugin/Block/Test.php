<?php

namespace Drupal\exchange_rate\Plugin\Block;

use Drupal\Core\Block\BlockBase;

/**
 * @Block(
 *  id = "test_block",
 *  admin_label = @Translation("Test"),
 *  category = @Translation("Custom")
 * )
 */

class Test extends BlockBase {

    /**
     * {@inheritdoc}
     */

    public function build()
    {
        $date = new \DateTime();
        return [
            '#markup' => t('Happy @year'.'th, animals!', [
                '@year' => $date->format('Y'),
            ]),
        ];
    }
}