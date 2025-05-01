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
        $propertyName = $this->key;

        // Check if the property name needs to be quoted, e.g. if it contains spaces.
        if (preg_match('/^[$_a-zA-Z][$_a-zA-Z0-9]*$/', $propertyName) !== 1) {
            $propertyName = "'{$propertyName}'";
        }

        return $propertyName . ($this->optional ? '?' : '') . ': ' . $this->value->printTypeString();
    }
}