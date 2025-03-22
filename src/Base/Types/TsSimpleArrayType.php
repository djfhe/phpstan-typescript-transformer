<?php

namespace djfhe\StanScript\Base\Types;

use djfhe\StanScript\TsType;

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