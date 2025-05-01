<?php

namespace djfhe\PHPStanTypescriptTransformer\Base\PhpstanTypes;

use PHPStan\Php\PhpVersion;
use PHPStan\Reflection\ClassConstantReflection;
use PHPStan\Reflection\ClassMemberAccessAnswerer;
use PHPStan\Reflection\ExtendedMethodReflection;
use PHPStan\Reflection\ExtendedPropertyReflection;
use PHPStan\Reflection\Type\UnresolvedMethodPrototypeReflection;
use PHPStan\Reflection\Type\UnresolvedPropertyPrototypeReflection;
use PHPStan\ShouldNotHappenException;
use PHPStan\TrinaryLogic;
use PHPStan\Type\AcceptsResult;
use PHPStan\Type\BooleanType;
use PHPStan\Type\CompoundType;
use PHPStan\Type\Constant\ConstantBooleanType;
use PHPStan\Type\ErrorType;
use PHPStan\Type\GeneralizePrecision;
use PHPStan\Type\Generic\TemplateTypeMap;
use PHPStan\Type\Generic\TemplateTypeVariance;
use PHPStan\Type\IsSuperTypeOfResult;
use PHPStan\Type\NeverType;
use PHPStan\Type\Type;

// @phpstan-ignore phpstanApi.interface
abstract class MetaType implements Type
{
	public function toBoolean(): BooleanType {
    return new ConstantBooleanType(false);
  }

	public function inferTemplateTypes(Type $receivedType): TemplateTypeMap
	{
		return TemplateTypeMap::createEmpty();
	}

	public function getReferencedTemplateTypes(TemplateTypeVariance $positionVariance): array
	{
		return [];
	}

	public function isSmallerThan(Type $otherType, PhpVersion $phpVersion): TrinaryLogic
	{
		return TrinaryLogic::createNo();
	}

	public function isSmallerThanOrEqual(Type $otherType, PhpVersion $phpVersion): TrinaryLogic
	{
		return TrinaryLogic::createNo();
	}

	public function getSmallerType(PhpVersion $phpVersion): Type
	{
		return new NeverType();
	}

	public function getSmallerOrEqualType(PhpVersion $phpVersion): Type
	{
		return new NeverType();
	}

	public function getGreaterType(PhpVersion $phpVersion): Type
	{
		return new NeverType();
	}

	public function getGreaterOrEqualType(PhpVersion $phpVersion): Type
	{
		return new NeverType();
	}

	public function tryRemove(Type $typeToRemove): ?Type
	{
		return null;
	}

	public function isGreaterThan(Type $otherType, PhpVersion $phpVersion): TrinaryLogic
	{
		return TrinaryLogic::createNo();
	}

	public function isGreaterThanOrEqual(Type $otherType, PhpVersion $phpVersion): TrinaryLogic
	{
		return TrinaryLogic::createNo();
	}

	public function generalize(GeneralizePrecision $precision): Type
	{
		return $this->traverse(static fn (Type $type) => $type->generalize($precision));
	}

	public function getReferencedClasses(): array
	{
		return [];
	}

	public function getArrays(): array
	{
		return [];
	}

	public function getConstantArrays(): array
	{
		return [];
	}

	public function getObjectClassNames(): array
	{
		return [];
	}

	public function getObjectClassReflections(): array
	{
		return [];
	}

	public function getConstantStrings(): array
	{
		return [];
	}

	public function accepts(Type $type, bool $strictTypes): AcceptsResult
	{
		return AcceptsResult::createNo();
	}

	public function isSuperTypeOf(Type $type): IsSuperTypeOfResult
	{
		if ($type instanceof static) {
			return IsSuperTypeOfResult::createYes();
		}

		if ($type instanceof CompoundType) {
			return $type->isSubTypeOf($this);
		}

		return IsSuperTypeOfResult::createNo();
	}

	public function equals(Type $type): bool
	{
		return $type instanceof static;
	}

	public function isSubTypeOf(Type $otherType): IsSuperTypeOfResult
	{
		return $otherType->isSuperTypeOf($this);
	}

	public function isAcceptedBy(Type $acceptingType, bool $strictTypes): AcceptsResult
	{
		return $this->isSubTypeOf($acceptingType)->toAcceptsResult();
	}

	public function getTemplateType(string $ancestorClassName, string $templateTypeName): Type
	{
		return new NeverType();
	}

	public function isObject(): TrinaryLogic
	{
		return TrinaryLogic::createNo();
	}

	public function isEnum(): TrinaryLogic
	{
		return TrinaryLogic::createNo();
	}

	public function canAccessProperties(): TrinaryLogic
	{
		return TrinaryLogic::createYes();
	}

	public function hasProperty(string $propertyName): TrinaryLogic
	{
		return TrinaryLogic::createNo();
	}

	public function getProperty(string $propertyName, ClassMemberAccessAnswerer $scope): ExtendedPropertyReflection
	{
		throw new ShouldNotHappenException();
	}

	public function getUnresolvedPropertyPrototype(string $propertyName, ClassMemberAccessAnswerer $scope): UnresolvedPropertyPrototypeReflection
	{
		throw new ShouldNotHappenException();
	}

	public function canCallMethods(): TrinaryLogic
	{
		return TrinaryLogic::createNo();
	}

	public function hasMethod(string $methodName): TrinaryLogic
	{
		return TrinaryLogic::createNo();
	}

	public function getMethod(string $methodName, ClassMemberAccessAnswerer $scope): ExtendedMethodReflection
	{
		throw new ShouldNotHappenException();
	}

	public function getUnresolvedMethodPrototype(string $methodName, ClassMemberAccessAnswerer $scope): UnresolvedMethodPrototypeReflection
	{
		throw new ShouldNotHappenException();
	}

	public function canAccessConstants(): TrinaryLogic
	{
		return TrinaryLogic::createNo();
	}

	public function hasConstant(string $constantName): TrinaryLogic
	{
		return TrinaryLogic::createNo();
	}

	public function getConstant(string $constantName): ClassConstantReflection
	{
		throw new ShouldNotHappenException();
	}

	public function isIterable(): TrinaryLogic
	{
		return TrinaryLogic::createNo();
	}

	public function isIterableAtLeastOnce(): TrinaryLogic
	{
		return TrinaryLogic::createMaybe();
	}

	public function getArraySize(): Type
	{
		return new NeverType();
	}

	public function getIterableKeyType(): Type
	{
		return new NeverType();
	}

	public function getFirstIterableKeyType(): Type
	{
		return new NeverType();
	}

	public function getLastIterableKeyType(): Type
	{
		return new NeverType();
	}

	public function getIterableValueType(): Type
	{
		return new NeverType();
	}

	public function getFirstIterableValueType(): Type
	{
		return new NeverType();
	}

	public function getLastIterableValueType(): Type
	{
		return new NeverType();
	}

	public function isArray(): TrinaryLogic
	{
		return TrinaryLogic::createNo();
	}

	public function isConstantArray(): TrinaryLogic
	{
		return TrinaryLogic::createNo();
	}

	public function isOversizedArray(): TrinaryLogic
	{
		return TrinaryLogic::createNo();
	}

	public function isList(): TrinaryLogic
	{
		return TrinaryLogic::createNo();
	}

	public function isOffsetAccessible(): TrinaryLogic
	{
		return TrinaryLogic::createNo();
	}

	public function isOffsetAccessLegal(): TrinaryLogic
	{
		return TrinaryLogic::createNo();
	}

	public function hasOffsetValueType(Type $offsetType): TrinaryLogic
	{
		return TrinaryLogic::createNo();
	}

	public function getOffsetValueType(Type $offsetType): Type
	{
		return new NeverType();
	}

	public function setOffsetValueType(?Type $offsetType, Type $valueType, bool $unionValues = true): Type
	{
		return new ErrorType();
	}

	public function setExistingOffsetValueType(Type $offsetType, Type $valueType): Type
	{
		return new ErrorType();
	}

	public function unsetOffset(Type $offsetType): Type
	{
		return new NeverType();
	}

	public function getKeysArray(): Type
	{
		return new NeverType();
	}

	public function getValuesArray(): Type
	{
		return new NeverType();
	}

	public function chunkArray(Type $lengthType, TrinaryLogic $preserveKeys): Type
	{
		return new NeverType();
	}

	public function fillKeysArray(Type $valueType): Type
	{
		return new NeverType();
	}

	public function flipArray(): Type
	{
		return new NeverType();
	}

	public function intersectKeyArray(Type $otherArraysType): Type
	{
		return new NeverType();
	}

	public function popArray(): Type
	{
		return new NeverType();
	}

	public function reverseArray(TrinaryLogic $preserveKeys): Type
	{
		return new NeverType();
	}

	public function searchArray(Type $needleType): Type
	{
		return new NeverType();
	}

	public function shiftArray(): Type
	{
		return new NeverType();
	}

	public function shuffleArray(): Type
	{
		return new NeverType();
	}

	public function sliceArray(Type $offsetType, Type $lengthType, TrinaryLogic $preserveKeys): Type
	{
		return new NeverType();
	}

	public function isCallable(): TrinaryLogic
	{
		return TrinaryLogic::createNo();
	}

	public function getCallableParametersAcceptors(ClassMemberAccessAnswerer $scope): array
	{
		throw new ShouldNotHappenException();
	}

	public function isCloneable(): TrinaryLogic
	{
		return TrinaryLogic::createYes();
	}

	public function toNumber(): Type
	{
		return $this;
	}

	public function toAbsoluteNumber(): Type
	{
		return $this;
	}

	public function toString(): Type
	{
		return $this;
	}

	public function toInteger(): Type
	{
		return $this;
	}

	public function toFloat(): Type
	{
		return $this;
	}

	public function toArray(): Type
	{
		return $this;
	}

	public function toArrayKey(): Type
	{
		return $this;
	}

	public function toCoercedArgumentType(bool $strictTypes): Type
	{
		return $this;
	}

	public function traverse(callable $cb): Type
	{
		return $this;
	}

	public function traverseSimultaneously(Type $right, callable $cb): Type
	{
		return $this;
	}

	public function isNull(): TrinaryLogic
	{
		return TrinaryLogic::createNo();
	}

	public function isConstantValue(): TrinaryLogic
	{
		return TrinaryLogic::createNo();
	}

	public function isConstantScalarValue(): TrinaryLogic
	{
		return TrinaryLogic::createNo();
	}

	public function getConstantScalarTypes(): array
	{
		return [];
	}

	public function getConstantScalarValues(): array
	{
		return [];
	}

	public function isTrue(): TrinaryLogic
	{
		return TrinaryLogic::createNo();
	}

	public function isFalse(): TrinaryLogic
	{
		return TrinaryLogic::createNo();
	}

	public function isBoolean(): TrinaryLogic
	{
		return TrinaryLogic::createNo();
	}

	public function isFloat(): TrinaryLogic
	{
		return TrinaryLogic::createNo();
	}

	public function isInteger(): TrinaryLogic
	{
		return TrinaryLogic::createNo();
	}

	public function isString(): TrinaryLogic
	{
		return TrinaryLogic::createNo();
	}

	public function isNumericString(): TrinaryLogic
	{
		return TrinaryLogic::createNo();
	}

	public function isNonEmptyString(): TrinaryLogic
	{
		return TrinaryLogic::createNo();
	}

	public function isNonFalsyString(): TrinaryLogic
	{
		return TrinaryLogic::createNo();
	}

	public function isLiteralString(): TrinaryLogic
	{
		return TrinaryLogic::createNo();
	}

	public function isLowercaseString(): TrinaryLogic
	{
		return TrinaryLogic::createNo();
	}

	public function isUppercaseString(): TrinaryLogic
	{
		return TrinaryLogic::createNo();
	}

	public function isClassString(): TrinaryLogic
	{
		return TrinaryLogic::createNo();
	}

	public function getClassStringObjectType(): Type
	{
		return new ErrorType();
	}

	public function getObjectTypeOrClassStringObjectType(): Type
	{
		return new ErrorType();
	}

	public function isVoid(): TrinaryLogic
	{
		return TrinaryLogic::createNo();
	}

	public function isScalar(): TrinaryLogic
	{
		return TrinaryLogic::createNo();
	}

	public function looseCompare(Type $type, PhpVersion $phpVersion): BooleanType
	{
		return new BooleanType();
	}

	public function getEnumCases(): array
	{
		return [];
	}

	public function exponentiate(Type $exponent): Type
	{
		return $this;
	}

	public function getFiniteTypes(): array
	{
		return [];
	}

}