<?php
/**
 * Created by PhpStorm.
 * User: kadantsew
 * Date: 10.02.17
 * Time: 9:24
 */
namespace Drupal\open_weather\Plugin\Field\FieldFormatter;

use Drupal\Core\Field\FormatterBase;
use Drupal\Core\Field\FieldItemListInterface;

/**
 * Plugin implementation of the 'testfield_one_line' formatter.
 *
 * @FieldFormatter(
 *   id = "weather_formatter",
 *   label = @Translation("Text field (one line)"),
 *   field_types = {
 *     "weather"
 *   },
 * )
 */

class WeatherFormatter extends FormatterBase {

    /**
     * {@inheritdoc}
     */

    public function viewElements(FieldItemListInterface $items, $langcode)
    {
        $element = [];
        $key = \Drupal::config('open_weather.api')->get('api_key');
        foreach ($items as $delta => $item) {
            $res = file_get_contents("http://api.openweathermap.org/data/2.5/weather?q=". $item->location ."&units=metric&appid=" . $key);
            $json = json_decode($res, true);

            $element[$delta] = array(
                '#theme' => 'open_weather',
                '#content' => $json,
                '#attached' => array(
                    'library' => array(
                        'open_weather/main',
                    )
                )
            );
        }
        return $element;
    }
}