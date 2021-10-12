<?php declare(strict_types=1);
/**
 * This file is part of the Adexos package.
 * (c) Adexos <contact@adexos.fr>
 */

namespace Tests\Adexos\SyliusUnitellerPlugin\Traits;

use Payum\Core\GatewayAwareInterface;

trait GatewayAwareTestTrait
{
    public function testShouldImplementGatewayAwareInterface(): void
    {
        $rc = new \ReflectionClass($this->actionClass);
        $this->assertTrue($rc->implementsInterface(GatewayAwareInterface::class));
    }
}
