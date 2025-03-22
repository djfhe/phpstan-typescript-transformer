<?php

namespace djfhe\StanScript\Base\Transformer;

use djfhe\StanScript\TsTypeTransformerContract;
use djfhe\StanScript\Base\Types\TsRecordType;
use djfhe\StanScript\Base\Types\TsSimpleArrayType;
use PHPStan\Type\Type;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\ReflectionProvider;

class ArrayableParser implements TsTypeTransformerContract
{
    public static function canTransform(Type $type, Scope $scope, ReflectionProvider $reflectionProvider): bool {
        
        if (!$type instanceof \PHPStan\Type\Generic\GenericObjectType) {
            return false;
        }

        if ($type->getClassName() === 'Illuminate\Contracts\Support\Arrayable') {
            return true;
        }

        $reflection = $type->getClassReflection();

        if ($reflection === null) {
            return false;
        }

        if ($reflection->implementsInterface('Illuminate\Contracts\Support\Arrayable')) {
            return true;
        }

        return true;
    }

    public static function transform(Type $type, Scope $scope, ReflectionProvider $reflectionProvider): TsSimpleArrayType|TsRecordType
    {
      /** @var \PHPStan\Type\Generic\GenericObjectType $type */

      // @phpstan-ignore phpstanApi.varTagAssumption
      $types = $type->getTypes();

      assert(count($types) === 2);

      $keyType = $types[0];
      $valueType = $types[1];

      return _ArrayLikeParserHelper::transform($keyType, $valueType, $scope, $reflectionProvider);
    }

    public static function transformPriority(Type $type, Scope $scope, ReflectionProvider $reflectionProvider, array $candidates): int {
      return 1;
    }
  
}