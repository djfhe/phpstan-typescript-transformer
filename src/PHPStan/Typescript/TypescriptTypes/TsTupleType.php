<?php

namespace djfhe\ControllerTransformer\PHPStan\Typescript\TypescriptTypes;

use djfhe\ControllerTransformer\PHPStan\Typescript\_TsTypeParserContract;
use djfhe\ControllerTransformer\PHPStan\Typescript\TsTypeParser;
use PHPStan\Type\Type;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\ReflectionProvider;

class TsTupleType extends _TsType implements _TsTypeParserContract
{
    public function __construct(
      /** @var array<_TsType> */
      protected array $types
    ) {}

    public function toTypeDefinition(bool $inline): string
    {
        $types = [];
        foreach ($this->types as $type) {
            $types[] = $type->toTypeString($inline);
        }
        return "[" . implode(", ", $types) . "]";
    }

    public static function canParse(Type $type, Scope $scope, ReflectionProvider $reflectionProvider): bool {
      if (!$type instanceof \PHPStan\Type\Constant\ConstantArrayType) {
        return false;
      }

      $keyTypes = $type->getKeyTypes();

      if (count($keyTypes) === 0) {
        return false;
      }

      foreach ($keyTypes as $keyType) {
        if (!$keyType instanceof \PHPStan\Type\Constant\ConstantIntegerType) {
          return false;
        }
      }

      usort($keyTypes, fn($a, $b) => $a->getValue() - $b->getValue());

      for ($i = 0; $i < count($keyTypes); $i++) {
        if ($keyTypes[$i]->getValue() !== $i) {
          return false;
        }
      }

      return true;
    }

    public static function parse(Type $type, Scope $scope, ReflectionProvider $reflectionProvider): _TsType {
      $types = [];

      /** @var \PHPStan\Type\Constant\ConstantArrayType $type */

      $keyTypes = $type->getKeyTypes();
      $values = $type->getValueTypes();

      $items = array_map(fn($key, $value) => [$key, $value], $keyTypes, $values);

      usort($items, fn($a, $b) => $a[0]->getValue() - $b[0]->getValue());

      foreach ($items as $item) {
        $types[] = TsTypeParser::parse($item[1], $scope, $reflectionProvider);
      }

      return new self($types);
    }

    public static function parsePriority(Type $type, Scope $scope, ReflectionProvider $reflectionProvider, array $candidates): int {
      return 0;
    }

    protected function _serialize(): array
    {
        return [
            'types' => array_map(fn(_TsType $type) => $type->serialize(), $this->types)
        ];
    }

    protected static function _deserialize(array $data): static
    {
        return new self(array_map(fn($type) => _TsType::deserialize($type), $data['types']));
    }

    protected function getChildren(): array
    {
        return $this->types;
    }
}