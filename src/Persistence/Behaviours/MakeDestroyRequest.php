<?php declare(strict_types=1);

namespace Somnambulist\Components\ApiClient\Persistence\Behaviours;

use Psr\Log\LogLevel;
use Somnambulist\Components\ApiClient\Client\Contracts\ConnectionInterface;
use Somnambulist\Components\ApiClient\Persistence\Contracts\ApiActionInterface;
use Somnambulist\Components\ApiClient\Persistence\Exceptions\EntityPersisterException;
use Symfony\Component\HttpClient\Exception\ClientException;
use function array_values;
use function implode;

/**
 * Trait MakeDestroyRequest
 *
 * @package    Somnambulist\Components\ApiClient\Persistence\Behaviours
 * @subpackage Somnambulist\Components\ApiClient\Persistence\Behaviours\MakeDestroyRequest
 *
 * @property-read \Somnambulist\Components\ApiClient\Client\Contracts\ConnectionInterface $client
 */
trait MakeDestroyRequest
{

    public function destroy(ApiActionInterface $action): bool
    {
        $action->isValid();

        $id = implode(':', array_values($action->getRouteParams()));

        try {
            $response = $this->client->delete($action->getRoute(), $action->getRouteParams());

            if (204 !== $response->getStatusCode()) {
                throw EntityPersisterException::entityNotDestroyed($action->getClass(), $id, new ClientException($response));
            }

            return true;

        } catch (ClientException $e) {
            $this->log(LogLevel::ERROR, $e->getMessage(), [
                'class' => $action->getClass(),
                'route' => $this->client->route($action->getRoute(), $action->getRouteParams()),
            ]);

            throw EntityPersisterException::serverError($e->getMessage(), $e);
        }
    }
}