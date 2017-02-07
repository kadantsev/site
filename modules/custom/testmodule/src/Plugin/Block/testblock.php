<?php
/**
 * Created by PhpStorm.
 * User: kadantsew
 * Date: 07.02.17
 * Time: 12:01
 */

namespace Drupal\testmodule\Plugin\Block;

use Drupal\Core\Block\BlockBase;

/**
 * Provides a 'Test' Block.
 *
 * @Block(
 *   id = "test_block_block",
 *   admin_label = @Translation("Test block"),
 * )
 */
class Testblock extends BlockBase {

    /**
     * {@inheritdoc}
     */
    public function build() {
        $content = \Drupal::formBuilder()->getForm('Drupal\testmodule\Form\TestForm');
        $output = \Drupal::service('renderer')->render($content);
        return array(
            '#markup' => $output,
        );
    }

}