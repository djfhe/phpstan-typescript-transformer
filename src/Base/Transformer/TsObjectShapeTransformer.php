<?php

namespace djfhe\StanScript\Base\Transformer;

use djfhe\StanScript\_TsTypeTransformerContract;
use djfhe\StanScript\Base\Types\TsObjectPropertyType;
use djfhe\StanScript\Base\Types\TsObjectType;
use djfhe\StanScript\TsTransformer;
use PHPStan\Type\Type;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\ReflectionProvider;

class TsObjectShapeTransformer implements _TsTypeTransformerContract
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
              $isOptional = in_array($name, $typeOptionalProperties);
              $parsed = TsTransformer::transform($property, $scope, $reflectionProvider);
              $properties[] = new TsObjectPropertyType($name, $parsed, $isOptional);
            }
    
            return new TsObjectType($properties);
        }

        if ($type instanceof \PHPStan\Type\Constant\ConstantArrayType) {
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

        throw new \Exception("Invalid type");
    }

    public static function transformPriority(Type $type, Scope $scope, ReflectionProvider $reflectionProvider, array $candidates): int {
        return 0;
    }
}