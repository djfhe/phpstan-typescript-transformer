<?php

namespace djfhe\ControllerTransformer\PHPStan\Typescript\TypescriptTypes;

use djfhe\ControllerTransformer\PHPStan\Typescript\_TsTypeParserContract;
use djfhe\ControllerTransformer\PHPStan\Typescript\TsTypeParser;
use PHPStan\Type\Type;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\ReflectionProvider;

class TsRecordType extends _TsType implements _TsTypeParserContract
{
    public function __construct(
      protected _TsType $keyType,
      protected _TsType $valueType,
    ) {}

    public function toTypeDefinition(bool $inline): string
    {
        return "Record<{$this->keyType->toTypeString($inline)}, {$this->valueType->toTypeString($inline)}>";
    }

    public static function canParse(Type $type, Scope $scope, ReflectionProvider $reflectionProvider): bool
    {
      if (! $type instanceof \PHPStan\Type\ArrayType) {
        return false;
      }

      if ($type instanceof \PHPStan\Type\Constant\ConstantArrayType) {
        return false;
      }

      $keyType = $type->getKeyType();

      return ! $keyType instanceof \PHPStan\Type\IntegerType;
    }

    public static function parse(Type $type, Scope $scope, ReflectionProvider $reflectionProvider): _TsType {
      /** @var \PHPStan\Type\ArrayType $type */

      $keyType = $type->getKeyType();
      $valueType = $type->getItemType();
      
      return new self(TsTypeParser::parse($keyType, $scope, $reflectionProvider), TsTypeParser::parse($valueType, $scope, $reflectionProvider));
    }

    public static function parsePriority(Type $type, Scope $scope, ReflectionProvider $reflectionProvider, array $candidates): int {
      return 0;
    }

    protected function _serialize(): array
    {
        return [
            'keyType' => $this->keyType->serialize(),
            'valueType' => $this->valueType->serialize(),
        ];
    }

    protected static function _deserialize(array $data): static
    {
        return new self(_TsType::deserialize($data['keyType']), _TsType::deserialize($data['valueType']));
    }

    protected function getChildren(): array
    {
        return [$this->keyType, $this->valueType];
    }
}