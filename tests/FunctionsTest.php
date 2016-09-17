<?php declare(strict_types=1);

namespace ApiClients\Tests\Foundation\Resource;

use ApiClients\Tests\Foundation\Resource\Resources\Async\SubResource;
use ApiClients\Tests\Foundation\Resource\Resources\Sync\Resource;
use function ApiClients\Foundation\get_properties;
use function ApiClients\Foundation\get_property;
use function ApiClients\Foundation\resource_pretty_print;
use League\Tactician\Setup\QuickStart;
use PHPUnit_Framework_TestCase;

class FunctionsTest extends PHPUnit_Framework_TestCase
{
    public function testGetProperties()
    {
        $properties = [];

        foreach (get_properties(new Resource(QuickStart::create([]))) as $property) {
            $properties[] = $property->getName();
        }

        $this->assertSame([
            'id',
            'slug',
            'sub',
            'subs',
        ], $properties);
    }

    public function testGetProperty()
    {
        $resource = new Resource(QuickStart::create([]));
        get_property($resource, 'id')->setValue($resource, $this->getJson()['id']);

        $this->assertSame(
            $this->getJson()['id'],
            get_property($resource, 'id')->getValue($resource)
        );
    }

    public function testResourcePrettyPrint()
    {
        $resource = new Resource(QuickStart::create([]));
        get_property($resource, 'id')->setValue($resource, $this->getJson()['id']);
        get_property($resource, 'slug')->setValue($resource, $this->getJson()['slug']);

        $sub = new SubResource(QuickStart::create([]));
        get_property($sub, 'id')->setValue($sub, $this->getJson()['id']);
        get_property($sub, 'slug')->setValue($sub, $this->getJson()['slug']);
        get_property($resource, 'sub')->setValue($resource, $sub);

        $subs = [];
        foreach ($this->getJson()['subs'] as $index => $row) {
            $subZero = new SubResource(QuickStart::create([]));
            get_property($subZero, 'id')->setValue($subZero, $row['id']);
            get_property($subZero, 'slug')->setValue($subZero, $row['slug']);
            $subs[] = $subZero;
        }
        get_property($resource, 'subs')->setValue($resource, $subs);

        $expected = "ApiClients\Tests\Foundation\Resource\Resources\Sync\Resource
	id: 1
	slug: Wyrihaximus/php-travis-client
	sub: ApiClients\Tests\Foundation\Resource\Resources\Async\SubResource
		id: 1
		slug: Wyrihaximus/php-travis-client
	subs: [
		ApiClients\Tests\Foundation\Resource\Resources\Async\SubResource
			id: 1
			slug: Wyrihaximus/php-travis-client
		ApiClients\Tests\Foundation\Resource\Resources\Async\SubResource
			id: 2
			slug: Wyrihaximus/php-travis-client
		ApiClients\Tests\Foundation\Resource\Resources\Async\SubResource
			id: 3
			slug: Wyrihaximus/php-travis-client
	]
";
        ob_start();
        resource_pretty_print($resource);
        $actual = ob_get_clean();

        if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
            $expected = str_replace(
                [
                    "\r",
                    "\n",
                ],
                '',
                $expected
            );
            $actual = str_replace(
                [
                    "\r",
                    "\n",
                ],
                '',
                $actual
            );
        }

        $this->assertSame($expected, $actual);
    }

    protected function getJson()
    {
        return [
            'id' => 1,
            'slug' => 'Wyrihaximus/php-travis-client',
            'sub' => [
                'id' => 1,
                'slug' => 'Wyrihaximus/php-travis-client',
            ],
            'subs' => [
                [
                    'id' => 1,
                    'slug' => 'Wyrihaximus/php-travis-client',
                ],
                [
                    'id' => 2,
                    'slug' => 'Wyrihaximus/php-travis-client',
                ],
                [
                    'id' => 3,
                    'slug' => 'Wyrihaximus/php-travis-client',
                ],
            ],
        ];
    }
}
