<?php

namespace djfhe\StanScript\LaravelData\Transformer;

use djfhe\StanScript\TsType;
use djfhe\StanScript\TsTypeTransformerContract;
use djfhe\StanScript\PHPStan\Typescript\TypescriptTypes\Laravel\TsAbstractPaginatedType;
use djfhe\StanScript\TsTransformer;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\ReflectionProvider;
use PHPStan\Type\Type;

class LaravelDataPaginatedParser implements TsTypeTransformerContract
{
    public static function canTransform(Type $type, Scope $scope, ReflectionProvider $reflectionProvider): bool {
      if (!$type instanceof \PHPStan\Type\UnionType) {
        return false;
      }

      $unionTypes = $type->getTypes();

      if (count($unionTypes) !== 2) {
        return false;
      }

      foreach ($unionTypes as $unionType) {
        if (!$unionType instanceof \PHPStan\Type\ObjectType) {
          return false;
        }
      }

      /** @var \PHPStan\Type\ObjectType[] $unionTypes */

      $paginator = self::getPaginator($unionTypes, $reflectionProvider);
      $paginatedType = self::getPaginatedType($unionTypes);

      return $paginator !== null && $paginatedType !== null;
    }

    /**
     * @param \PHPStan\Type\ObjectType[] $types
     */
    protected static function getPaginator(array $types, ReflectionProvider $reflectionProvider): ?Type {
      $paginator = array_filter($types, function ($type) use ($reflectionProvider) {
        if ($type->getClassName() === 'Illuminate\Pagination\AbstractPaginator') {
          return true;
        }

        $reflection = $type->getClassReflection();

        if ($reflection === null) {
          return false;
        }

        return $reflection->isSubclassOfClass($reflectionProvider->getClass('Illuminate\Pagination\AbstractPaginator'));
      });

      if (count($paginator) === 1) {
        return array_values($paginator)[0];
      }

      return null;
    }

    /**
     * @param \PHPStan\Type\ObjectType[] $types
     */
    protected static function getPaginatedType(array $types): ?Type {
      $types = array_filter($types, function ($type) {
        return $type->isIterable()->yes();
      });

      if (count($types) !== 1) {
        return null;
      }

      return array_values($types)[0]->getIterableValueType();
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