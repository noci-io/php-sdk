<?php

namespace Digitalize\SDK\Namespaces;

use Digitalize\SDK\Exceptions\QueryException;
use Digitalize\SDK\Models\ProviderActivity;
use Digitalize\SDK\NS;

class Providers extends NS
{
    /**
     * Contains the provider type
     *
     * @var string
     */
    private $type = '';

    /**
     * Contains the provider unique identifier
     *
     * @var string
     */
    private $identifier = '';

    /**
     * Contains auth info : ['id' => 123, 'name' => 'A name']
     *
     * @var array
     */
    private $auth = ['id' => null, 'name' => null];

    /**
     * Configures the provider globally
     *
     * @param string $type
     * @param string $identifier
     * @param array|null $auth
     * @return void
     */
    public function configure($type, $identifier, $auth = null)
    {
        $this->type = $type;
        $this->identifier = $identifier;
        if ($auth === null) {
            $this->auth = ['id' => null, 'name' => null];
        } else {
            if (isset($auth['id'], $auth['name'])) {
                $this->auth = [
                    'id' => $auth['id'],
                    'name' => $auth['name']
                ];
            }
        }
    }

    /**
     * Emits a new activity
     *
     * @param string $eventType
     * @param array $details
     * @param string $date
     * @return ProviderActivity
     */
    public function emitEvent($eventType, $details = [], $date = 'now')
    {
        try {
            $res = $this->client->post('providers/activity', [
                'provider' => [
                    'type' => $this->type,
                    'identifier' => $this->identifier
                ],
                'type' => $eventType,
                'date' => date('Y-m-d H:i:s', strtotime($date)),
                'payload' => $details,
                'user' => $this->auth
            ]);
            if ($res)
                return new ProviderActivity($res['data']);
            return null;
        } catch (QueryException $e) {
            return null;
        } catch (Exception $e) {
            return null;
        }
    }
}
