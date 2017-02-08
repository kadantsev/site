<?php
/**
 * Created by PhpStorm.
 * User: kadantsew
 * Date: 08.02.17
 * Time: 13:45
 */
namespace Drupal\exchange_rate;
use XPathSelector\Selector;

class GoverlaRate {
    protected $site = 'https://goverla.ua';
    protected $html;
    protected $row = "//div[contains(@id,'rates')]//div//div//div//div//div[contains(@class,'gvrl-table-body')]//div[contains(@class,'gvrl-table-row')]";
    protected $flag = "div/img/@src";
    protected $currency = "div/img/@alt";
    protected $buy = "div[2]";
    protected $sell = "div[3]";

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