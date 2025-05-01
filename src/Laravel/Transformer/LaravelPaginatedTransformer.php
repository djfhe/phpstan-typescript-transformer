<?php

namespace djfhe\PHPStanTypescriptTransformer\Laravel\Transformer;

use djfhe\PHPStanTypescriptTransformer\Laravel\Types\TsAbstractPaginatedType;
use djfhe\PHPStanTypescriptTransformer\TsTransformer;
use djfhe\PHPStanTypescriptTransformer\TsType;
use djfhe\PHPStanTypescriptTransformer\TsTypeTransformerContract;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\ReflectionProvider;
use PHPStan\Type\Generic\GenericObjectType;
use PHPStan\Type\ObjectType;
use PHPStan\Type\Type;

class LaravelPaginatedTransformer implements TsTypeTransformerContract
{
    public static function canTransform(Type $type, Scope $scope, ReflectionProvider $reflectionProvider): bool {
        if (!$type instanceof GenericObjectType) {
            return false;
        }

        $reflection = $type->getClassReflection();

        if ($reflection === null) {
            return false;
        }

        return $reflection->isSubclassOfClass($reflectionProvider->getClass('Illuminate\Pagination\AbstractPaginator'));
    }

    public static function transform(Type $type, Scope $scope, ReflectionProvider $reflectionProvider): TsType {
      assert($type instanceof GenericObjectType);

      $unionTypes = $type->getTypes();

      if (count($unionTypes) !== 2) {
        throw new \Exception('Union types count is not 2 for Laravel paginated transformer');
      }

      $tsType = TsTransformer::transform($unionTypes[1], $scope, $reflectionProvider);

      return new TsAbstractPaginatedType($tsType);
    }

    public static function transformPriority(Type $type, Scope $scope, ReflectionProvider $reflectionProvider, array $candidates): int {
        return 10;
    }
}