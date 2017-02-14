<?php
/**
 * Created by PhpStorm.
 * User: kadantsew
 * Date: 09.02.17
 * Time: 12:10
 */

namespace Drupal\open_weather\Plugin\Field\FieldWidget;

use Drupal\Core\Field\WidgetBase;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Form\FormStateInterface;

/**
 * Plugin implementation of the 'test_default' widget.
 *
 * @FieldWidget(
 *   id = "weather_widget",
 *   label = @Translation("Text field"),
 *   field_types = {
 *     "weather"
 *   },
 * )
 */
class WeatherDefaultWidget extends WidgetBase {

    public static function isValid(&$element, FormStateInterface $form_state, $form) {

        if (!empty($element['#value'])) {
            $key = \Drupal::config('open_weather.api')->get('api_key');
            $request = new \GuzzleHttp\Client();
            $res = $request->request('GET', "http://api.openweathermap.org/data/2.5/weather?q=" . $element['#value'] . "&appid=" . $key, ['exceptions' => false]);

            if ($res->getStatusCode() != 200) {
                drupal_set_message(t('The name of city is wrong!'), 'error');
                $a = $element['location'];
                $form_state->setError($element, t('@name field is wrong.', array('@name' => $element['#title'])));
            }
        }

    }


    /**
     * {@inheritdoc}
     */

    public function formElement(FieldItemListInterface $items, $delta, array $element, array &$form, FormStateInterface $form_state)
    {
        $element['location'] = array(
            '#type' => 'textfield',
            '#title' => t('Location'),
            '#default_value' => '',
            '#element_validate' => array(array(get_called_class(), 'isValid')),
            '#size' => 25,
            '#required' => $element['#required'],
        );
        $b =1;
        return $element;
    }


}