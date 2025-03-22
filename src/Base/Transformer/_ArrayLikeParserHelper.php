<?php

namespace djfhe\PHPStanTypescriptTransformer\Base\Transformer;

use djfhe\PHPStanTypescriptTransformer\Base\Types\TsRecordType;
use djfhe\PHPStanTypescriptTransformer\Base\Types\TsSimpleArrayType;
use djfhe\PHPStanTypescriptTransformer\TsTransformer;
use PHPStan\Type\Type;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\ReflectionProvider;

class _ArrayLikeParserHelper
{
    public static function transform(Type $keyType, Type $valueType, Scope $scope, ReflectionProvider $reflectionProvider): TsSimpleArrayType|TsRecordType
    {
        if ($keyType->isInteger()->yes()) {
            return new TsSimpleArrayType(TsTransformer::transform($valueType, $scope, $reflectionProvider));
        }

        return new TsRecordType(TsTransformer::transform($keyType, $scope, $reflectionProvider), TsTransformer::transform($valueType, $scope, $reflectionProvider));
    }
}