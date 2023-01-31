<?php

use Digitalize\SDK\Models\Order;

include __DIR__ . '/../autoload.php';

try {
    $client = new Digitalize\SDK\Client("https://0fffad7d6b888fc94a098e1ae02107c5fd45eda8:0dd1d259c0635ba3aaf48753db399ab770ceae78@localhost:8080/api/v1?customerId=1", false);

    $_COOKIE['dcb_user_id'] = '0d5c02cc-a150-4854-a457-a34f3a492252';
    $_COOKIE['dcb_state_id'] = '5d64e7f4bc59cd00b875c284';

    $order = Order::create(rand(2000, 20000));
    $nbProducts = rand(1, 3);
    while ($nbProducts--) {
        $order->addProduct(rand(1, 1000), rand(100, 23878) / 100, rand(1, 3), rand(0, 10), Order::DISCOUNT_TYPE_PERCENT);
    }

    var_dump($client->Orders->save($order));
} catch (Digitalize\SDK\Exceptions\ConnectionUriException $e) {
    var_dump("ConnectionUriException: " . $e->getMessage());
} catch (Digitalize\SDK\Exceptions\QueryException $e) {
    var_dump("QueryException: " . $e->getMessage());
} catch (Digitalize\SDK\Exceptions\QueryException $e) {
    var_dump("QueryException: " . $e->getMessage());
} catch (Exception $e) {
    var_dump("Exception: " . $e->getMessage());
}
