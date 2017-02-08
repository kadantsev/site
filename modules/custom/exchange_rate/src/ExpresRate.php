<?php

 namespace Drupal\exchange_rate;
 use XPathSelector\Selector;

 class ExpresRate {
     protected $site = 'http://express.lutsk.ua';
     protected $html;
     protected $row = '//table[1]/tbody/tr[position()>1]';
     protected $flag = "td/img/@src";
     protected $currency = "td[2]";
     protected $buy = "td[3]";
     protected $sell = "td[4]";

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
