<?php
/**
 * Created by PhpStorm.
 * User: kadantsew
 * Date: 08.02.17
 * Time: 12:21
 */
namespace Drupal\exchange_rate;
use XPathSelector\Selector;

class KantorRate {
    protected $site = 'http://kantor.com.ua';
    protected $html;
    protected $row = '//tbody/tr';
    protected $flag = "td/img/@src";
    protected $currency = "td/span";
    protected $buy = "td[2]";
    protected $sell = "td[3]";

    public function getRate () {

        $this->html = Selector::loadHTML(file_get_contents($this->site));
        $array = $this->html->findAll($this->row)->map(function ($node) {
            return [
                'flag'=>$this->site .($node->find($this->flag)->extract()),
                'currency'=>$node->find($this->currency)->extract(),
                'buy'=>$node->find($this->buy)->extract(),
                'sell'=>$node->find($this->sell)->extract(),
            ];
        });

        return $array;
    }
}

