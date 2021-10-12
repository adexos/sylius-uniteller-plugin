<?php declare(strict_types=1);
/**
 * This file is part of the Adexos package.
 * (c) Adexos <contact@adexos.fr>
 */

namespace Adexos\SyliusUnitellerPlugin;

use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\GatewayFactory;

final class UnitellerGatewayFactory extends GatewayFactory
{
    public const FACTORY_NAME = 'uniteller';

    protected function populateConfig(ArrayObject $config)
    {
        $config->defaults([
            'payum.factory_name' => self::FACTORY_NAME,
            'payum.factory_title'  => 'Uniteller Processing',
        ]);

        if ($config['payum.api']) {
            return;
        }

        $config['payum.default_options'] = [
            'shop_id'      => '',
            'login'        => '',
            'password'     => '',
        ];

        $config->defaults($config['payum.default_options']);
        $config['payum.required_options'] = ['shop_id', 'login', 'password'];

        $config['payum.api'] = function (ArrayObject $config) {
            $config->validateNotEmpty($config['payum.required_options']);
            $uniteller = new \Adexos\Uniteller\Client([
                'shopId' => $config['shop_id'],
                'login' => $config['login'],
                'password' => $config['password']
            ]);

            return $uniteller;
        };
    }


}
