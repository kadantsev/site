<?php
/**
 * Created by PhpStorm.
 * User: kadantsew
 * Date: 09.02.17
 * Time: 12:10
 */

namespace Drupal\testmodule\Plugin\Field\FieldWidget;

use Drupal\Core\Field\WidgetBase;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Form\FormStateInterface;

/**
 * Plugin implementation of the 'test_default' widget.
 *
 * @FieldWidget(
 *   id = "testfield_default",
 *   label = @Translation("Text field"),
 *   field_types = {
 *     "testfield"
 *   },
 * )
 */
class TestFieldDefaultWidget extends WidgetBase {

    /**
     * {@inheritdoc}
     */

    public function formElement(FieldItemListInterface $items, $delta, array $element, array &$form, FormStateInterface $form_state)
    {
        $element['first_name'] = array(
            '#type' => 'textfield',
            '#title' => t('First name'),
            '#default_value' => '',
            '#size' => 25,
            '#required' => $element['#required'],
        );
        $element['last_name'] = array(
            '#type' => 'textfield',
            '#title' => t('Last name'),
            '#default_value' => '',
            '#size' => 25,
            '#required' => $element['#required'],
        );
        return $element;
    }

}