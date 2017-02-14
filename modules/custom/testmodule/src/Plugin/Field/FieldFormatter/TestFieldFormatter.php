<?php
/**
 * Created by PhpStorm.
 * User: kadantsew
 * Date: 10.02.17
 * Time: 9:24
 */
namespace Drupal\testmodule\Plugin\Field\FieldFormatter;

use Drupal\Core\Field\FormatterBase;
use Drupal\Core\Field\FieldItemListInterface;

/**
 * Plugin implementation of the 'testfield_one_line' formatter.
 *
 * @FieldFormatter(
 *   id = "testfield_one_line",
 *   label = @Translation("Text field (one line)"),
 *   field_types = {
 *     "testfield"
 *   },
 * )
 */

class TestFieldFormatter extends FormatterBase {

    /**
     * {@inheritdoc}
     */

    public function viewElements(FieldItemListInterface $items, $langcode)
    {
        $element = [];

        foreach ($items as $delta => $item) {
            $element[$delta] = array(
                '#markup' => $this->t('@first @last', array(
                        '@first' => $item->first_name,
                        '@last' => $item->last_name,
                    )
                )
            );
        }
        return $element;
    }
}