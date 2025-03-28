<?php

namespace djfhe\PHPStanTypescriptTransformer\Base\Transformer;

use djfhe\PHPStanTypescriptTransformer\TsTypeTransformerContract;
use djfhe\PHPStanTypescriptTransformer\Base\Types\TsObjectPropertyType;
use djfhe\PHPStanTypescriptTransformer\Base\Types\TsObjectType;
use djfhe\PHPStanTypescriptTransformer\TsTransformer;
use PHPStan\Type\Type;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\ReflectionProvider;

class TsObjectShapeTransformer implements TsTypeTransformerContract
{

    public static function canTransform(Type $type, Scope $scope, ReflectionProvider $reflectionProvider): bool {
        if ($type instanceof \PHPStan\Type\ObjectShapeType) {
            return true;
        }

        if ($type instanceof \PHPStan\Type\Constant\ConstantArrayType) {
            if (count($type->getKeyTypes()) === 0) {
                return false;
            }

            return !TsTupleTransformer::canTransform($type, $scope, $reflectionProvider);
        }

        return false;
    }

    public static function transform(Type $type, Scope $scope, ReflectionProvider $reflectionProvider): TsObjectType {
        /** @var \PHPStan\Type\ObjectShapeType|\PHPStan\Type\Constant\ConstantArrayType $type */

        if ($type instanceof \PHPStan\Type\ObjectShapeType) {
            $typeOptionalProperties = $type->getOptionalProperties();

            $properties = [];
    
            foreach ($type->getProperties() as $name => $property) {
              $isOptional = in_array($name, $typeOptionalProperties, true);
              $parsed = TsTransformer::transform($property, $scope, $reflectionProvider);
              $properties[] = new TsObjectPropertyType($name, $parsed, $isOptional);
            }
    
            return new TsObjectType($properties);
        }
        
        $properties = [];

        $keyTypes = $type->getKeyTypes();
        $valueTypes = $type->getValueTypes();

        foreach ($keyTypes as $i => $key) {
            $isOptional = $type->isOptionalKey($i);
            $parsedValue = TsTransformer::transform($valueTypes[$i], $scope, $reflectionProvider);

            $properties[] = new TsObjectPropertyType((string) $key->getValue(), $parsedValue, $isOptional);
        }

        return new TsObjectType($properties);
    }

    public static function transformPriority(Type $type, Scope $scope, ReflectionProvider $reflectionProvider, array $candidates): int {
        return 0;
    }
}