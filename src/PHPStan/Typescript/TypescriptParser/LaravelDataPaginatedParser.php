<?php

namespace djfhe\ControllerTransformer\PHPStan\Typescript\TypescriptParser;

use djfhe\ControllerTransformer\PHPStan\Typescript\_TsTypeParserContract;
use djfhe\ControllerTransformer\PHPStan\Typescript\TypescriptTypes\Laravel\TsAbstractPaginatedType;
use djfhe\ControllerTransformer\PHPStan\Typescript\TsTypeParser;
use djfhe\ControllerTransformer\PHPStan\Typescript\TypescriptTypes\_TsType;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\ReflectionProvider;
use PHPStan\Type\Type;
use PHPStan\Type\VerbosityLevel;

class LaravelDataPaginatedParser implements _TsTypeParserContract
{
    public static function canParse(Type $type, Scope $scope, ReflectionProvider $reflectionProvider): bool {
      if (!$type instanceof \PHPStan\Type\UnionType) {
        return false;
      }

      $types = $type->getTypes();

      if (count($types) !== 2) {
        return false;
      }

      foreach ($types as $type) {
        if (!$type->isObject()->yes()) {
          return false;
        }
      }

      /** @var \PHPStan\Type\ObjectType[] $types */

      $paginator = self::getPaginator($types, $reflectionProvider);
      $paginatedType = self::getPaginatedType($types);

      return $paginator !== null && $paginatedType !== null;
    }

    /**
     * @param \PHPStan\Type\ObjectType[] $type
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
     * @param \PHPStan\Type\ObjectType[] $type
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

    public static function parse(Type $type, Scope $scope, ReflectionProvider $reflectionProvider): _TsType {
      /** @var \PHPStan\Type\UnionType $type */

      $types = $type->getTypes();

      $paginatedType = self::getPaginatedType($types);

      assert($paginatedType !== null);

      $tsType = TsTypeParser::parse($paginatedType, $scope, $reflectionProvider);

      return new TsAbstractPaginatedType($tsType);
    }

    public static function parsePriority(Type $type, Scope $scope, ReflectionProvider $reflectionProvider, array $candidates): int {
        return 10;
    }
}