<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\back\tests\EndToEnd\Connection;

use Akeneo\Connectivity\Connection\Domain\Settings\Model\ValueObject\FlowType;
use Akeneo\Test\Integration\Configuration;
use Akeneo\Tool\Bundle\ApiBundle\tests\integration\ApiTestCase;
use PHPUnit\Framework\Assert;
use Symfony\Component\HttpFoundation\Response;

/**
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class CollectApiErrorsEndToEnd extends ApiTestCase
{
    public function test_it_collects_a_violation_http_exception(): void
    {
        $connection = $this->createConnection('erp', 'ERP', FlowType::DATA_SOURCE, true);

        $client = $this->createAuthenticatedClient(
            [],
            [],
            $connection->clientId(),
            $connection->secret(),
            $connection->username(),
            $connection->password()
        );

        $content = <<<JSON
{
    "identifier": "ziggy",
    "family": "familyA",
    "categories": ["master"],
    "values": {
        "a_text": [{
            "locale": null,
            "scope": null,
            "data": "A name"
        }]
    }
}
JSON;

        $content2 = <<<JSON
{
    "identifier": "ziggy",
    "values": {
        "a_text": [{
            "locale": null,
            "scope": null,
            "data": "A name"
        }]
    }
}
JSON;

        $client->request('POST', '/api/rest/v1/products', [], [], [], $content);
        Assert::assertSame(Response::HTTP_CREATED, $client->getResponse()->getStatusCode());

        $client->request('PATCH', '/api/rest/v1/products', [], [], [
            'HTTP_Content_Type' => 'application/vnd.akeneo.collection+json'
        ], $content2);
        Assert::assertSame(Response::HTTP_UNPROCESSABLE_ENTITY, $client->getResponse()->getStatusCode());

        dd($client->getResponse()->getContent());
    }

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useTechnicalCatalog();
    }
}
