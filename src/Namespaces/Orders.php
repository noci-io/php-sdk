<?php

namespace Digitalize\SDK\Namespaces;

use Digitalize\SDK\Exceptions\QueryException;
use Digitalize\SDK\Models\Order;
use Digitalize\SDK\NS;

class Orders extends NS
{
    /**
     * Creates a new order.
     *
     * @param Order $order
     * @return Order
     */
    public function save(Order $order)
    {
        try {
            $cookieMap = [
                'dcb_user_id' => 'user_id',
                'dcb_state_id' => 'state_id'
            ];
            foreach ($cookieMap as $from => $to) {
                if (isset($_COOKIE[$from]))
                    $order->set($to, $_COOKIE[$from]);
            }
            $orderData = $order->export();
            unset($orderData['id']);
            $res = $this->client->post('customers/' . intval($this->client->params['customerId']) . '/orders', $orderData);
            if ($res)
                return new Order($res['data']);
            return null;
        } catch (QueryException $e) {
            return null;
        } catch (Exception $e) {
            return null;
        }
    }

    /**
     * Refunds a product in an order
     *
     * @param int $orderId
     * @param int $productId
     * @param int $qtyToRefund
     * @return Order
     */
    public function refundProduct($orderId, $productId, $qtyToRefund = 1)
    {
        try {
            $res = $this->client->put('customers/' . intval($this->client->params['customerId']) . '/orders/' . intval($orderId) . '/products/' . intval($productId) . '/refund', [
                'qty' => intval($qtyToRefund)
            ]);
            if ($res)
                return new Order($res['data']);
            return null;
        } catch (QueryException $e) {
            return null;
        } catch (Exception $e) {
            return null;
        }
    }

    /**
     * Refunds an order
     *
     * @param int $orderId
     * @return Order
     */
    public function refund($orderId)
    {
        try {
            $res = $this->client->put('customers/' . intval($this->client->params['customerId']) . '/orders/' . intval($orderId) . '/refund');
            if ($res)
                return new Order($res['data']);
            return null;
        } catch (QueryException $e) {
            return null;
        } catch (Exception $e) {
            return null;
        }
    }

    /**
     * Changes the status of an order
     *
     * @param int $orderId
     * @param string $newStatus
     * @return Order
     */
    public function updateStatus($orderId, $newStatus)
    {
        try {
            $res = $this->client->put('customers/' . intval($this->client->params['customerId']) . '/orders/' . intval($orderId) . '/status/' . $newStatus);
            if ($res)
                return new Order($res['data']);
            return null;
        } catch (QueryException $e) {
            return null;
        } catch (Exception $e) {
            return null;
        }
    }

    /**
     * Updates a product
     *
     * @param int $orderId
     * @param int $productId
     * @param float $newUnitPrice
     * @param integer $newQuantity
     * @param float $newDiscount
     * @param string $newDiscountType
     * @return Order
     */
    public function updateProduct($orderId, $productId, $newUnitPrice = null, $newQuantity = null, $newDiscount = null, $newDiscountType = null)
    {
        $data = [];
        $map = [
            'newUnitPrice' => 'unit_price',
            'newQuantity' => 'qty',
            'newDiscount' => 'discount',
            'newDiscountType' => 'discount_type'
        ];
        foreach ($map as $from => $to) {
            if ($$from !== null) {
                $data[$to] = $$from;
            }
        }
        try {
            $res = $this->client->put('customers/' . intval($this->client->params['customerId']) . '/orders/' . intval($orderId) . '/products/' . intval($productId), $data);
            if ($res)
                return new Order($res['data']);
            return null;
        } catch (QueryException $e) {
            return null;
        } catch (Exception $e) {
            return null;
        }
    }
}
