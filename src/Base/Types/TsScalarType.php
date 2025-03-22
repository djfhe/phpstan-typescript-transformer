<?php

namespace djfhe\PHPStanTypescriptTransformer\Base\Types;

use djfhe\PHPStanTypescriptTransformer\TsType;

class TsScalarType extends TsType
{
    
    public function __construct(
        public string $value
    ) {}

    protected function typeDefinition(): string
    {
        return $this->value;
    }
}