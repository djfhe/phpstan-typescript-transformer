<?php

namespace djfhe\PHPStanTypescriptTransformer\Base\Types;

use djfhe\PHPStanTypescriptTransformer\TsType;

/**
 * A simple homogeneous array type. For example: `string[]`, `number[]`, `(string | number)[]`, `never[]`, etc.
 */
class TsSimpleArrayType extends TsType
{
    public function __construct(
      protected TsType $valueType
    ) {}

    protected function typeDefinition(): string
    {
        return "{$this->valueType->printTypeString()}[]";
    }
}