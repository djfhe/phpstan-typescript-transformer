<?php

namespace djfhe\PHPStanTypescriptTransformer\Laravel\Transformer;

use djfhe\PHPStanTypescriptTransformer\Laravel\Rules\ControllerInertiaReturnRule;
use Exception;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Relation;
use PHPStan\Analyser\OutOfClassScope;
use PHPStan\Reflection\ClassReflection;
use PHPStan\Type\Constant\ConstantArrayType;
use PHPStan\Type\Constant\ConstantStringType;
use PHPStan\Type\ObjectType;
use ReflectionException;
use ReflectionMethod;

class LaravelModelHelper
{
  /**
   * @param ClassReflection $classReflection
   * @return ?array<string,\PHPStan\Type\Type>
   */
  public static function getModelProperties(ClassReflection $classReflection): ?array
  {
    if (! $classReflection->is(Model::class)) {
      return null;
    }

    if ($classReflection->isAbstract()) {
        return null;
    }
    // trigger migration loading
    $classReflection->hasProperty('id');

    return self::getDatabaseProperties($classReflection);
  }

  /**
   * @param ClassReflection $classReflection
   * @return ?array<string,\PHPStan\Type\Type>
   */
  public static function getAccessorProperties(ClassReflection $classReflection): ?array
  {
    if (! $classReflection->is(Model::class)) {
      return null;
    }

    if ($classReflection->isAbstract()) {
        return null;
    }
    // trigger migration loading
    $classReflection->hasProperty('id');

    return self::getModelAccessors($classReflection);
  }

  /**
   * @param ClassReflection $classReflection
   * @return ?array<string,\PHPStan\Type\Type>
   */
  protected static function getDatabaseProperties(ClassReflection $classReflection): ?array
  {
    /** @var array<string,\Larastan\Larastan\Properties\SchemaTable> */
    $tables = ((fn () => $this->tables))->call(ControllerInertiaReturnRule::$propertyHelper);

    /** @var \Illuminate\Database\Eloquent\Model|null */
    $modelInstance = null;

    try {
        $modelInstance = $classReflection->getNativeReflection()->newInstanceWithoutConstructor();
    } catch (ReflectionException) {
        return null;
    }

    /** @var \Illuminate\Database\Eloquent\Model $modelInstance */

    $tableName = $modelInstance->getTable();

    if (! array_key_exists($tableName, $tables)) {
        return null;
    }

    $columns = $tables[$tableName]->columns;

    $properties = [];

    foreach($columns as $column) {
        $properties[$column->name] = $classReflection->getProperty($column->name, new OutOfClassScope())->getReadableType();
    }

    return $properties;
  }

  /**
   * @param ClassReflection $classReflection
   * @return ?array<string,\PHPStan\Type\Type>
   */
  protected static function getModelAccessors(ClassReflection $classReflection): ?array
  {
    /**
     * @var \ReflectionClass<Model&object>
     * @phpstan-ignore varTag.type
     */
    $nativeReflection = $classReflection->getNativeReflection();

    
    $methods = $nativeReflection->getMethods(ReflectionMethod::IS_PROTECTED);

    $attributeType = new ObjectType(Attribute::class);
    
    $methods = array_filter($methods, function (ReflectionMethod $method) use ($classReflection, $attributeType) {
        if ($method->isStatic()) {
            return false;
        }

        if ($method->getNumberOfParameters() !== 0) {
            return false;
        }

        if (preg_match('/^get[A-Za-z0-9_]+Attribute$/', $method->getName()) === 1) {
            return true;
        }

        $returnType = $classReflection->getMethod($method->getName(), new OutOfClassScope())->getVariants()[0]->getReturnType();

        return $attributeType->isSuperTypeOf($returnType)->yes();
    });

    $methodNames = array_map(fn(ReflectionMethod $method) => $method->getName(), $methods);

    $methodNameToPropertyMapper = function (string $methodName): string {
        if (preg_match('/^get[A-Za-z0-9_]+Attribute$/', $methodName) === 1) {
          $methodName = substr($methodName, 3, -9);
        }

        $replace = preg_replace('/(?<!^)[A-Z]/', '_$0', $methodName);

        if (! is_string($replace)) {
          return $methodName;
        }

        return strtolower($replace);
    };

    $propertyNames = array_map($methodNameToPropertyMapper, $methodNames);


    $properties = [];

    foreach ($propertyNames as $propertyName) {
        $properties[$propertyName] = $classReflection->hasProperty($propertyName) ? $classReflection->getProperty($propertyName, new OutOfClassScope())->getReadableType() : null;
    }

    return array_filter($properties, fn($property) => $property !== null);
  }

  /**
   * @param ClassReflection $classReflection
   * @return ?array<string,\PHPStan\Type\Type>
   */
  public static function getModelRelations(ClassReflection $classReflection): ?array
  {
    /**
     * @var \ReflectionClass<Model&object>
     * @phpstan-ignore varTag.type
     */
    $nativeReflection = $classReflection->getNativeReflection();

    $methods = $nativeReflection->getMethods();
    $relationNames = array_map(fn(ReflectionMethod $method) => $method->getName(), $methods);

    $relationType = new ObjectType(Relation::class);

    $methods = array_filter($methods, function (ReflectionMethod $method) use ($classReflection, $relationType) {
        if ($method->isStatic()) {
            return false;
        }

        if ($method->getNumberOfParameters() !== 0) {
            return false;
        }

        $returnType = $classReflection->getMethod($method->getName(), new OutOfClassScope())->getVariants()[0]->getReturnType();

        return $relationType->isSuperTypeOf($returnType)->yes();
    });

    $relationNames = array_map(fn(ReflectionMethod $method) => $method->getName(), $methods);

    $relations = [];

    foreach ($relationNames as $relationName) {
        $relations[$relationName] = $classReflection->hasProperty($relationName) ? $classReflection->getProperty($relationName, new OutOfClassScope())->getReadableType() : null;
    }

    return array_filter($relations, fn($relation) => $relation !== null);
  }

  /**
   * @return list<string>
   */
  public static function hiddenAttributes(ClassReflection $classReflection): array
  {
    if (! $classReflection->is(Model::class)) {
      return [];
    }

    if ($classReflection->isAbstract()) {
        return [];
    }

    if (!$classReflection->hasProperty('hidden')) {
        return [];
    }


    try {
      /** @var Model */
      $modelInstance = $classReflection->getNativeReflection()->newInstanceWithoutConstructor();
      return array_values($modelInstance->getHidden());
    } catch (Exception) {}

    $hidden = $classReflection->getProperty('hidden', new OutOfClassScope())->getReadableType();

    if (!$hidden instanceof ConstantArrayType) {
        return [];
    }

    $hidden = $hidden->getValueTypes();

    $hiddenProperties = [];

    foreach ($hidden as $value) {
        if ($value instanceof ConstantStringType) {
            $hiddenProperties[] = $value->getValue();
        }
    }

    return $hiddenProperties;
  }

  /**
   * @return list<string>
   */
  public static function visibleAttributes(ClassReflection $classReflection): array
  {
    if (! $classReflection->is(Model::class)) {
      return [];
    }

    if ($classReflection->isAbstract()) {
        return [];
    }

    if (!$classReflection->hasProperty('visible')) {
        return [];
    }

    try {
      /** @var Model */
      $modelInstance = $classReflection->getNativeReflection()->newInstanceWithoutConstructor();
      return array_values($modelInstance->getVisible());
    } catch (Exception) {}

    $visible = $classReflection->getProperty('visible', new OutOfClassScope())->getReadableType();

    if (!$visible instanceof ConstantArrayType) {
        return [];
    }

    $visible = $visible->getValueTypes();

    $visibleProperties = [];

    foreach ($visible as $value) {
        if ($value instanceof ConstantStringType) {
            $visibleProperties[] = $value->getValue();
        }
    }

    return $visibleProperties;
  }

  /**
   * @return list<string>
   */
  public static function appendedAttributes(ClassReflection $classReflection): array
  {
    if (! $classReflection->is(Model::class)) {
      return [];
    }

    if ($classReflection->isAbstract()) {
        return [];
    }

    if (!$classReflection->hasProperty('appends')) {
        return [];
    }
    
    try {
      /** @var Model */
      $modelInstance = $classReflection->getNativeReflection()->newInstanceWithoutConstructor();
      // @phpstan-ignore return.type
      return array_values($modelInstance->getAppends());
    } catch (Exception) {}

    $appends = $classReflection->getProperty('appends', new OutOfClassScope())->getReadableType();

    if (!$appends instanceof ConstantArrayType) {
        return [];
    }

    $appends = $appends->getValueTypes();

    $appendedProperties = [];

    foreach ($appends as $value) {
        if ($value instanceof ConstantStringType) {
            $appendedProperties[] = $value->getValue();
        }
    }

    return $appendedProperties;
  }
}