<?php

namespace Digitalize\SDK\Models;

use Digitalize\SDK\Model;

class Order extends Model
{
    const STATUS_CANCELED = 'canceled';
    const STATUS_CREATED = 'created';
    const STATUS_PAYED = 'payed';
    const STATUS_FINISHED = 'finished';
    const STATUS_REFUNDED = 'refunded';

    const DISCOUNT_TYPE_AMOUNT = 'amount';
    const DISCOUNT_TYPE_PERCENT = 'percent';

    public $id;
    public $order_id;
    public $date;
    public $status;
    public $state_id;
    public $user_id;
    public $final_customer_id;
    public $products = [];
    public $amount_due;
    public $amount_payed;
    public $discount;
    public $events = [];
    public $created_at;
    public $updated_at;

    protected $_types = [
        'id' => 'string',
        'order_id' => 'int',
        'date' => 'datetime',
        'status' => 'string',
        'state_id' => 'string',
        'products' => ['type' => Product::class, 'multiple' => true],
        'amount_due' => 'float',
        'amount_payed' => 'float',
        'discount' => 'float',
        'events' => ['type' => OrderEvent::class, 'multiple' => true],
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    /**
     * Creates a new order
     *
     * @param string $id
     * @param string $status
     * @param string $date
     * @return self
     */
    public static function create($id, $status = self::STATUS_CREATED, $date = null)
    {
        $order = new self();
        $order->set('order_id', $id);
        $order->set('status', $status);
        $order->set('date', date('Y-m-d H:i:s', $date === null ? time() : strtotime($date)));
        return $order;
    }

    /**
     * Adds a new product to the order
     *
     * @param string $id
     * @param float $unit_price
     * @param integer $qty
     * @param integer $discount
     * @param string $discount_type
     * @return self
     */
    public function addProduct($id, $unit_price, $qty = 1, $discount = 0, $discount_type = self::DISCOUNT_TYPE_AMOUNT)
    {
        $this->products[] = new Product([
            'id' => $id,
            'unit_price' => $unit_price,
            'qty' => $qty,
            'discount' => $discount,
            'discount_type' => $discount_type
        ]);
        return $this->refreshAmounts();
    }

    /**
     * Recalculates amounts
     *
     * @return self
     */
    public function refreshAmounts()
    {
        $amount_due = 0;
        $amount_payed = 0;
        $discount = 0;

        /**
         * For each product, calculate the amounts
         */
        foreach ($this->products as $product) {
            $e = $product->export();

            $amount_due += $e['amount_due'];
            $amount_payed += $e['amount_payed'];
            $discount += $e['amount_due'] - $e['amount_payed'];
        }

        $this->amount_due = $amount_due;
        $this->amount_payed = $amount_payed;
        $this->discount = $discount;

        return $this;
    }

    /**
     * Do not send creation and update dates
     *
     * @param array $ret
     * @return array
     */
    public function __afterExport($ret)
    {
        unset($ret['created_at']);
        unset($ret['updated_at']);
        return $ret;
    }
}
