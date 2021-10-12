<?php declare(strict_types=1);
/**
 * This file is part of the Adexos package.
 * (c) Adexos <contact@adexos.fr>
 */

namespace Tests\Adexos\SyliusUnitellerPlugin\Traits;

use Payum\Core\GatewayInterface;
use Tmconsulting\Uniteller\ClientInterface;

trait MockTrait
{
    /**
     * Uniteller ClientInterface.
     *
     * @return \PHPUnit_Framework_MockObject_MockObject|ClientInterface
     */
    protected function createClientMock()
    {
        return $this->createMock(ClientInterface::class);
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|GatewayInterface
     */
    protected function createGatewayMock()
    {
        return $this->createMock(GatewayInterface::class);
    }
}
