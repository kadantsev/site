<?php
/**
 * Created by PhpStorm.
 * User: kadantsew
 * Date: 03.02.17
 * Time: 14:40
 */

namespace Drupal\exchange_rate\Controller;

use Drupal\Core\Controller\ControllerBase;

class MyTwigController extends ControllerBase {
    public function content() {

        return array(
            '#theme' => 'exchange_rate',
            '#row' => $this->t('Test Value'),
        );

    }
}
