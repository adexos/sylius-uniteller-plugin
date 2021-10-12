<?php declare(strict_types=1);
/**
 * This file is part of the Adexos package.
 * (c) Adexos <contact@adexos.fr>
 */

namespace Tests\Adexos\SyliusUnitellerPlugin\Traits;

use Payum\Core\ApiAwareInterface;
use Payum\Core\Exception\UnsupportedApiException;

trait ApiAwareTestTrait
{
    public function testShouldImplementApiAwareInterface(): void
    {
        $rc = new \ReflectionClass($this->actionClass);
        $this->assertTrue($rc->implementsInterface(ApiAwareInterface::class));
    }

    public function testCanActionThrowIfUnsupportedApiGiven(): void
    {
        $this->expectException(UnsupportedApiException::class);
        $action = new $this->actionClass();
        $action->setApi(new \stdClass());
    }
}
