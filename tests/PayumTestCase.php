<?php declare(strict_types=1);
/**
 * This file is part of the Adexos package.
 * (c) Adexos <contact@adexos.fr>
 */

namespace Tests\Adexos\SyliusUnitellerPlugin;

use PHPUnit\Framework\TestCase as BaseTestCase;
use Tests\Adexos\SyliusUnitellerPlugin\Traits\MockTrait;

class PayumTestCase extends BaseTestCase
{
    use MockTrait;

    public static function readAttribute(object $object, string $attributeName)
    {
        $ref = new \ReflectionClass(get_class($object));
        $propertyRef = $ref->getProperty($attributeName);
        if ($propertyRef->isPublic()) {
            return $propertyRef->getValue($object);
        }
        $propertyRef->setAccessible(true);
        $value =  $propertyRef->getValue($object);
        $propertyRef->setAccessible(false);
        return $value;
    }
}
