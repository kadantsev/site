<?php
/**
 * Created by PhpStorm.
 * User: user
 * Date: 23.01.17
 * Time: 10:18
 */

namespace Drupal\mymodule\Controller;

use Drupal\Core\Controller\ControllerBase;

class MyPageController extends ControllerBase {
    public function customPage() {
        return [
            '#markup' => t('Welcome to my custom page!'),
        ];
    }
}

