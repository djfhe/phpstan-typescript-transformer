<?php

namespace djfhe\PHPStanTypescriptTransformer\LaravelData\Transformer;

use djfhe\PHPStanTypescriptTransformer\Laravel\Types\TsAbstractPaginatedType;
use djfhe\PHPStanTypescriptTransformer\TsTransformer;
use djfhe\PHPStanTypescriptTransformer\TsType;
use djfhe\PHPStanTypescriptTransformer\TsTypeTransformerContract;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\ReflectionProvider;
use PHPStan\Type\Type;
use PHPStan\Type\VerbosityLevel;
use function PHPStan\dumpType;

class LaravelDataPaginatedTransformer implements TsTypeTransformerContract
{
    public static function canTransform(Type $type, Scope $scope, ReflectionProvider $reflectionProvider): bool {
      if (!$type instanceof \PHPStan\Type\UnionType) {
        return false;
      }

      $unionTypes = $type->getTypes();

      // if (count($unionTypes) !== 2) {
      //   return false;
      // }

      foreach ($unionTypes as $unionType) {
        if (!$unionType instanceof \PHPStan\Type\ObjectType && !$unionType instanceof \PHPStan\Type\ArrayType) {
          return false;
        }
      }

      /** @var \PHPStan\Type\ObjectType[] $unionTypes */

      $paginator = self::getPaginator($unionTypes, $reflectionProvider);
      $paginatedType = self::getPaginatedType($unionTypes);

      return $paginator !== null && $paginatedType !== null;
    }

    /**
     * @param (\PHPStan\Type\ObjectType | \PHPStan\Type\ArrayType)[] $types
     */
    protected static function getPaginator(array $types, ReflectionProvider $reflectionProvider): ?Type {
      $paginator = array_filter($types, function ($type) use ($reflectionProvider) {
        if (!$type instanceof \PHPStan\Type\ObjectType) {
          return false;
        }

        if ($type->getClassName() === 'Illuminate\Pagination\AbstractPaginator') {
          return true;
        }

        $reflection = $type->getClassReflection();

        if ($reflection === null) {
          return false;
        }

        return $reflection->isSubclassOfClass($reflectionProvider->getClass('Illuminate\Pagination\AbstractPaginator'));
      });

      return array_pop($paginator);
    }

    /**
     * @param \PHPStan\Type\ObjectType[] $types
     */
    protected static function getPaginatedType(array $types): ?Type {


      $types = array_filter($types, function ($type) {
        return $type->isIterable()->yes();
      });

      return array_pop($types)?->getIterableValueType();
    }

    public static function transform(Type $type, Scope $scope, ReflectionProvider $reflectionProvider): TsType {

      /** @var \PHPStan\Type\UnionType $type */
      $unionTypes = $type->getTypes();

      /** @var \PHPStan\Type\ObjectType[] $unionTypes */

      $paginatedType = self::getPaginatedType($unionTypes);

      assert($paginatedType !== null);

      $tsType = TsTransformer::transform($paginatedType, $scope, $reflectionProvider);

      return new TsAbstractPaginatedType($tsType);
    }

    public static function transformPriority(Type $type, Scope $scope, ReflectionProvider $reflectionProvider, array $candidates): int {
        return 10;
    }
}