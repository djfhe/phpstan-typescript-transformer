<?php

namespace djfhe\PHPStanTypescriptTransformer\Base\Types;

use djfhe\PHPStanTypescriptTransformer\TsType;

class TsObjectPropertyType extends TsType
{
    public function __construct(
        public string $key,
        public TsType $value,
        public bool $optional = false
    ) {}

    protected function typeDefinition(): string
    {
        return $this->key . ($this->optional ? '?' : '') . ': ' . $this->value->printTypeString();
    }
}