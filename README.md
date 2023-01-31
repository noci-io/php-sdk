# Digital'ize PHP SDK

This packages aims to help developers to interact with Digital'ize services.

## Setup

### Pre-requisites

- PHP >= 5.6
- cURL extension installed

### Using Composer

```bash
composer require digital-ize/php-sdk
```

### Manual installation

Clone this repository to the desired destination, and include `./autoload.php` file.

## Usage

### Client instanciation

```php
<?php

use Digitalize\SDK\Client as DigitalizeClient;

// Use the connection URI available in your Digital'ize dashboard > Settings > API
$connectionUri = 'https://apikey:apisecret@api.digital-ize.com/api/v1?customerId=customer_id';

// Tells if we must verify SSL certificates when contacting Digital'ize API, must be true for most cases.
$verifySsl = true;

$client = new DigitalizeClient($connectionUri, $verifySsl);
```

## Namespaces

The API is divided in many namespaces, grouping related requests.

### Orders

Contains all methods about order management.

#### Constants

##### Order status

- Order::STATUS_CREATED : The order is created
- Order::STATUS_CANCELED : The order is canceled
- Order::STATUS_PAYED : The order has been payed
- Order::STATUS_FINISHED : The order is delivered and closed
- Order::STATUS_REFUNDED : The order is fully refunded

##### Discount types

- Order::DISCOUNT_TYPE_AMOUNT : The discount is a fixed amount
- Order::DISCOUNT_TYPE_PERCENT : The discount is a percentage of the full price

#### Create an order

```php
<?php

use Digitalize\SDK\Models\Order;

/**
 * Create the order
 */
$order_id = 12;
$order_status = Order::STATUS_CREATED;
$order_date = date('Y-m-d H:i:s');

$order = Order::create($order_id, $order_status, $order_date);

/**
 * Add one or many products to the order
 */
$product_id = 1287;
$unit_price = 162.90;
$qty = 1;
$discount = 10;
$discount_type = ORDER::DISCOUNT_TYPE_PERCENT;

$order->addProduct($product_id, $unit_price, $qty, $discount, $discount_type);

/**
 * Save the order
 */
$client->Orders->save($order);
```

#### Refund a product

```php
<?php

$orderId = 4289;
$productId = 126;
$qtyToRefund = 1;

$client->Orders->refundProduct($orderId, $productId, $qtyToRefund);
```

#### Refund an order completely

```php
<?php

$orderId = 4289;

$client->Orders->refund($orderId);
```

#### Update an order status

```php
<?php

use Digitalize\SDK\Models\Order;

$orderId = 4289;
$newStatus = Order::STATUS_PAYED;

$client->Orders->updateStatus($orderId, $newStatus);
```

#### Update an order product

```php
<?php

use Digitalize\SDK\Models\Order;

$orderId = 4289;
$productId = 165;
$newUnitPrice = 18.00;
$newQuantity = 3;
$newDiscount = 12;
$newDiscountType = Order::DISCOUNT_TYPE_PERCENT;

$client->Orders->updateProduct($orderId, $productId, $newUnitPrice, $newQuantity, $newDiscount, $newDiscountType);
```

### Providers

Contains all methods related to the SDK integration, like tracking events in your application, etc.

#### Configure the current provider

```php
<?php

$type = 'prestashop'; // the base system, like Prestashop, Magento, etc
$identifier = 'myexample.com'; // an unique identifier to call your installation, like your URL, etc
$auth = [
    'id' => 1762,
    'name' => 'John Doe'
]; // Or can be NULL if no user is logged in

$client->Providers->configure($type, $identifier, $auth);
```

#### Emit an event

Before emit an event, you must call Providers::configure at least one time.

```php
<?php

$type = 'user-clicked-on-important-button';
$details = [
    'buttonName' => 'TheBigButton',
    'x' => 12,
    'y' => 287
]; // The details can contain everything you want
$date = 'now';

$client->Providers->emitEvent($type, $details, $date);
```
