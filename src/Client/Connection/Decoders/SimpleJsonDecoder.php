<?php declare(strict_types=1);

namespace Somnambulist\Components\ApiClient\Client\Connection\Decoders;

use Symfony\Contracts\HttpClient\ResponseInterface;
use function in_array;
use function is_string;
use function json_decode;
use const JSON_THROW_ON_ERROR;

/**
 * Class SimpleJsonDecoder
 *
 * @package    Somnambulist\Components\ApiClient\Client\Connection\Decoders
 * @subpackage Somnambulist\Components\ApiClient\Client\Connection\Decoders\SimpleJsonDecoder
 */
class SimpleJsonDecoder
{

    public function decode(ResponseInterface $response, array $ok = [200]): array
    {
        if (!in_array($response->getStatusCode(), $ok)) {
            return [];
        }

        return json_decode((string)$response->getContent(), true, $depth = 512, JSON_THROW_ON_ERROR);
    }

    /**
     * Ensures that the array data is a set of arrays
     *
     * i.e.: is: [[], [], [], []]  not [data => [[],[]]] or [key->value, key->value]
     *
     * @param array $data
     *
     * @return array|array[]
     */
    public function ensureFlatArrayOfArrays(array $data): array
    {
        if (empty($data)) {
            return $data;
        }
        if (isset($data['data'])) {
            // external response could contain a data element
            $data = $data['data'];
        }
        if (is_string(array_key_first($data))) {
            // treat single object responses like collections
            $data = [$data];
        }

        return $data;
    }
}
