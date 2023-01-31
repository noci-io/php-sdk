<?php

namespace Digitalize\SDK\Models;

use Digitalize\SDK\Model;

class ProviderUser extends Model
{
    public $id;
    public $name;

    protected $_types = [
        'id' => 'string',
        'name' => 'string'
    ];
}
