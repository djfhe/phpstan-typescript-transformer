<?php

namespace djfhe\StanScript\Base\Types;

use djfhe\StanScript\TsType;

class TsObjectPropertyType extends TsType
{
    public function __construct(
        public string $key,
        public TsType $value,
        public bool $optional = false
    ) {}

    public function typeDefinition(): string
    {
        return $this->key . ($this->optional ? '?' : '') . ': ' . $this->value->printTypeString();
    }

    protected function getChildren(): array
    {
        return [$this->value];
    }
}