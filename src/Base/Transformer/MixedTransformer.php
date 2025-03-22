<?php

namespace djfhe\PHPStanTypescriptTransformer\Base\Transformer;

use djfhe\PHPStanTypescriptTransformer\TsTypeTransformerContract;
use djfhe\PHPStanTypescriptTransformer\Base\Types\TsScalarType;
use djfhe\PHPStanTypescriptTransformer\Base\Types\TsUnionType;
use djfhe\PHPStanTypescriptTransformer\TsTransformer;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\ReflectionProvider;
use PHPStan\Type\MixedType;
use PHPStan\Type\Type;

class MixedTransformer implements TsTypeTransformerContract
{
    public static function canTransform(Type $type, Scope $scope, ReflectionProvider $reflectionProvider): bool {

      if (!$type instanceof MixedType) {
        return false;
      }

      return true;
    }

    public static function transform(Type $type, Scope $scope, ReflectionProvider $reflectionProvider): TsScalarType {
    
      return new TsScalarType('unknown');
    }

    public static function transformPriority(Type $type, Scope $scope, ReflectionProvider $reflectionProvider, array $candidates): int {
        return 0;
    }
}