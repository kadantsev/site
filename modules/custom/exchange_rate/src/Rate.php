<?php

namespace Drupal\exchange_rate;
use XPathSelector\Selector;




class Rate {
    protected $site;
    protected $html;
    protected $row;
    protected $flag;
    protected $currency;
    protected $buy;
    protected $sell;


    public function __construct($URL) {
        $this->site  = $URL;
    }

    public function setSelector ($row, $flag, $currency, $buy, $sell) {
        $this->row = $row;
        $this->flag = $flag;
        $this->currency = $currency;
        $this->buy = $buy;
        $this->sell = $sell;
    }

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
