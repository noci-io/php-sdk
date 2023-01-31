<?php

namespace Digitalize\SDK\Models;

use Digitalize\SDK\Model;

class ProviderIdentifier extends Model
{
    public $type;
    public $identifier;

    protected $_types = [
        'type' => 'string',
        'identifier' => 'string'
    ];
}
