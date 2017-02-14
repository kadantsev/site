<?php
/**
 * Created by PhpStorm.
 * User: kadantsew
 * Date: 09.02.17
 * Time: 9:53
 */
namespace Drupal\open_weather\Plugin\Field\FieldType;

use Drupal\Core\Field\FieldItemBase;
use Drupal\Core\Field\FieldStorageDefinitionInterface;
use Drupal\Core\TypedData\DataDefinition;

/**
 * Plugin implementation of the 'weather' field type.
 *
 * @FieldType (
 *  id = "weather",
 *  label = @Translation("Weather field"),
 *  description = @Translation("This field stores parameters for weather"),
 *  category = @Translation("General"),
 *  default_widget = "weather_widget",
 *  default_formatter = "weather_formatter"
 * )
 */

class WeatherField extends FieldItemBase {
    /**
     *   {@inheritdoc}
     */

    public static function schema(FieldStorageDefinitionInterface $field_definition)
    {
        return array(
          'columns' => array(
              'location' => array(
                  'description' => 'Location.',
                  'type' => 'varchar',
                  'length' => '255',
                  'not null' => TRUE,
                  'default' => '',
              ),
          ),
            'indexes' => array(
                'location' => array('location'),
            ),
        );
    }

    /**
     *   {@inheritdoc}
     */

    public static function propertyDefinitions(FieldStorageDefinitionInterface $field_definition)
    {
        $properties['location'] = DataDefinition::create('string')->setLabel(t('Location'));
        return $properties;
    }
}