<?php

namespace Digitalize\SDK\Models;

use Digitalize\SDK\Model;

class ProviderActivity extends Model
{
    public $provider;
    public $type;
    public $date;
    public $payload;
    public $user;

    protected $_types = [
        'provider' => ['type' => ProviderIdentifier::class],
        'type' => 'string',
        'date' => 'datetime',
        'user' => ['type' => ProviderUser::class]
    ];
}
