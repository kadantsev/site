<?php
/**
 * Created by PhpStorm.
 * User: kadantsew
 * Date: 09.02.17
 * Time: 9:53
 */
namespace Drupal\testmodule\Plugin\Field\FieldType;

use Drupal\Core\Field\FieldItemBase;
use Drupal\Core\Field\FieldStorageDefinitionInterface;
use Drupal\Core\TypedData\DataDefinition;

/**
 * Plugin implementation of the 'testfield' field type.
 *
 * @FieldType (
 *  id = "testfield",
 *  label = @Translation("Test field"),
 *  description = @Translation("This field stores..."),
 *  category = @Translation("General"),
 *  default_widget = "testfield_default",
 *  default_formatter = "string"
 * )
 */

class TestField extends FieldItemBase {
    /**
     *   {@inheritdoc}
     */

    public static function schema(FieldStorageDefinitionInterface $field_definition)
    {
        return array(
          'columns' => array(
              'first_name' => array(
                  'description' => 'First name.',
                  'type' => 'varchar',
                  'length' => '255',
                  'not null' => TRUE,
                  'default' => '',
              ),
              'last_name' => array(
                  'description' => 'Last name.',
                  'type' => 'varchar',
                  'length' => '255',
                  'not null' => TRUE,
                  'default' => '',
              ),
          ),
            'indexes' => array(
                'first_name' => array('first_name'),
                'last_name' => array('last_name'),
            ),
        );
    }

    /**
     *   {@inheritdoc}
     */

    public static function propertyDefinitions(FieldStorageDefinitionInterface $field_definition)
    {
        $properties['first_name'] = DataDefinition::create('string')->setLabel(t('First name'));
        $properties['last_name'] = DataDefinition::create('string')->setLabel(t('Last name'));
        return $properties;
    }
}