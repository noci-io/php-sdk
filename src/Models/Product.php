<?php

namespace Digitalize\SDK\Models;

use Digitalize\SDK\Model;

class Product extends Model
{
    public $id;
    public $qty;
    public $unit_price;
    public $discount;
    public $discount_type;
    public $amount_due;
    public $amount_payed;
    public $recommended;
    public $recommended_dates;
    public $recommended_occurences;
    public $recommended_states;

    protected $_types = [
        'id' => 'string',
        'qty' => 'int',
        'unit_price' => 'float',
        'discount' => 'float',
        'discount_type' => 'string',
        'amount_due' => 'float',
        'amount_payed' => 'float',
        'recommended' => 'bool',
        'recommended_dates' => ['type' => 'datetime', 'multiple' => true],
        'recommended_occurences' => 'int',
        'recommended_states' => ['type' => 'string', 'multiple' => true],
    ];

    /**
     * Callback executed right before export
     *
     * @return void
     */
    public function __beforeExport()
    {
        $this->refreshAmounts();
    }

    /**
     * Do not send recommended status
     *
     * @param array $ret
     * @return array
     */
    public function __afterExport($ret)
    {
        unset($ret['recommended']);
        unset($ret['recommended_dates']);
        unset($ret['recommended_occurences']);
        unset($ret['recommended_states']);
        return $ret;
    }

    /**
     * Recalculates amounts
     *
     * @return self
     */
    public function refreshAmounts()
    {
        $amount_due = $this->qty * $this->unit_price;
        $amount_payed = $amount_due;
        switch ($this->discount_type) {
            case Order::DISCOUNT_TYPE_AMOUNT:
                $amount_payed = $amount_due - $this->discount;
                break;
            case Order::DISCOUNT_TYPE_PERCENT:
                $amount_payed = $amount_due - round($amount_due * ($this->discount / 100), 2);
                break;
        }
        $this->amount_due = $amount_due;
        $this->amount_payed = $amount_payed;
        return $this;
    }
}
