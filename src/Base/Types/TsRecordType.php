<?php

namespace djfhe\StanScript\Base\Types;

use djfhe\StanScript\TsType;

class TsRecordType extends TsType
{
    public function __construct(
      protected TsType $keyType,
      protected TsType $valueType,
    ) {}

    public function typeDefinition(): string
    {
        return "Record<{$this->keyType->printTypeString()}, {$this->valueType->printTypeString()}>";
    }

    protected function getChildren(): array
    {
        return [$this->keyType, $this->valueType];
    }
}