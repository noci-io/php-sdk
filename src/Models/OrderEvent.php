<?php

namespace Digitalize\SDK\Models;

use Digitalize\SDK\Model;

class OrderEvent extends Model
{
    public $type;
    public $date;
    public $product_id;
    public $qty;
    public $amount;
    public $from;
    public $to;
    public $old;
    public $new;

    protected $_types = [
        'type' => 'string',
        'date' => 'datetime',
        'product_id' => 'string',
        'qty' => 'int',
        'amount' => 'float',
        'from' => 'string',
        'to' => 'string'
    ];
}
