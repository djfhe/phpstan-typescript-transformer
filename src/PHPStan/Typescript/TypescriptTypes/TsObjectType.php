<?php

namespace djfhe\ControllerTransformer\PHPStan\Typescript\TypescriptTypes;

use djfhe\ControllerTransformer\PHPStan\Typescript\_TsTypeParserContract;
use djfhe\ControllerTransformer\PHPStan\Typescript\TsTypeParser;
use PHPStan\Type\Type;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\ReflectionProvider;

class TsObjectType extends _TsType implements _TsTypeParserContract
{
    public function __construct(
      /** @var array<TsObjectPropertyType> */
      protected array $properties
    ) {}

    public function definitionKeyword(): string
    {
        return "interface";
    }

    public function toTypeDefinition(bool $inline): string
    {
        $properties = [];
        foreach ($this->properties as $value) {
            $properties[] = $value->toTypeString($inline);
        }

        return "{ " . implode("; ", $properties) . " }";
    }

    public static function canParse(Type $type, Scope $scope, ReflectionProvider $reflectionProvider): bool {
        if ($type instanceof \PHPStan\Type\ObjectShapeType) {
            return true;
        }

        if ($type instanceof \PHPStan\Type\Constant\ConstantArrayType) {
            if (count($type->getKeyTypes()) === 0) {
                return false;
            }

            return !TsTupleType::canParse($type, $scope, $reflectionProvider);
        }

        return false;
    }

    public static function parse(Type $type, Scope $scope, ReflectionProvider $reflectionProvider): _TsType {
        /** @var \PHPStan\Type\ObjectShapeType|\PHPStan\Type\Constant\ConstantArrayType $type */

        if ($type instanceof \PHPStan\Type\ObjectShapeType) {
            $typeOptionalProperties = $type->getOptionalProperties();

            $properties = [];
    
            foreach ($type->getProperties() as $name => $property) {
              $isOptional = in_array($name, $typeOptionalProperties);
              $parsed = TsTypeParser::parse($property, $scope, $reflectionProvider);
              $properties[] = new TsObjectPropertyType($name, $parsed, $isOptional);
            }
    
            return new self($properties);
        }

        if ($type instanceof \PHPStan\Type\Constant\ConstantArrayType) {
            $properties = [];

            $keyTypes = $type->getKeyTypes();
            $valueTypes = $type->getValueTypes();

            foreach ($keyTypes as $i => $key) {
                $isOptional = $type->isOptionalKey($i);
                $parsedValue = TsTypeParser::parse($valueTypes[$i], $scope, $reflectionProvider);

                $properties[] = new TsObjectPropertyType((string) $key->getValue(), $parsedValue, $isOptional);
            }

            return new self($properties);
        }

        throw new \Exception("Invalid type");
    }

    public static function parsePriority(Type $type, Scope $scope, ReflectionProvider $reflectionProvider, array $candidates): int {
        return 0;
    }

    protected function _serialize(): array
    {
        return [
            'properties' => array_map(fn(TsObjectPropertyType $property) => $property->serialize(), $this->properties)
        ];
    }

    protected static function _deserialize(array $data): static
    {
        return new self(array_map(fn($property) => TsObjectPropertyType::deserialize($property), $data['properties']));
    }

    protected function getChildren(): array
    {
        return $this->properties;
    }
}