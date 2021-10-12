<?php declare(strict_types=1);
/**
 * This file is part of the Adexos package.
 * (c) Adexos <contact@adexos.fr>
 */

namespace Tests\Adexos\SyliusUnitellerPlugin;

use Adexos\SyliusUnitellerPlugin\UnitellerGatewayFactory;
use Payum\Core\Gateway;
use Tmconsulting\Uniteller\ClientInterface;

class UnitellerGatewayFactoryTest extends PayumTestCase
{
    public function testConstructWithoutArgs(): void
    {
        $this->expectNotToPerformAssertions();
        new UnitellerGatewayFactory();
    }

    public function testIfConfigurationContainDefaultOptions(): void
    {
        $factory = new UnitellerGatewayFactory();
        $config  = $factory->createConfig();

        $this->assertIsArray($config);
        $this->assertArrayHasKey('payum.default_options', $config);

        $defaults = [
            'shop_id'      => '',
            'login'        => '',
            'password'     => '',
            'base_uri'     => 'https://wpay.uniteller.ru',
        ];

        $this->assertEquals($defaults, $config['payum.default_options']);
    }

    public function testPayumConfigurationContainFactoryNameAndTitle(): void
    {
        $factory = new UnitellerGatewayFactory();
        $config  = $factory->createConfig();

        $this->assertIsArray($config);

        $this->assertArrayHasKey('payum.factory_name', $config);
        $this->assertEquals('uniteller', $config['payum.factory_name']);

        $this->assertArrayHasKey('payum.factory_title', $config);
        $this->assertEquals('Uniteller Processing', $config['payum.factory_title']);
    }

    public function testAllowCreateGatewayWithCustomApi(): void
    {
        $factory = new UnitellerGatewayFactory();
        $gateway = $factory->create(['payum.api' => new \stdClass()]);

        $this->assertInstanceOf(Gateway::class, $gateway);
        $this->assertNotEmpty(static::readAttribute($gateway, 'apis'));
        $this->assertNotEmpty(static::readAttribute($gateway, 'actions'));
        $extensions = static::readAttribute($gateway, 'extensions');
        $this->assertNotEmpty(static::readAttribute($extensions, 'extensions'));
    }

    public function testGatewayCreating(): void
    {
        $expected = [
            'shop_id'      => 'boo',
            'login'        => 'bar',
            'password'     => 'foo',
            'base_uri'     => 'https://wpay.uniteller.ru',
        ];
        $factory = new UnitellerGatewayFactory();
        $gateway = $factory->create($expected);

        $apis = static::readAttribute($gateway, 'apis');
        $this->assertInstanceOf(ClientInterface::class, $apis[1]);
        $options = static::readAttribute($apis[1], 'options');

        $this->assertEquals($expected, $options);
    }
}
