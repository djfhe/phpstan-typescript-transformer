<?php

namespace djfhe\StanScript\Base\Types;

use djfhe\StanScript\TsType;

class TsRecordType extends TsType
{
    public function __construct(
      protected TsType $keyType,
      protected TsType $valueType,
    ) {}

    public function toTypeDefinition(bool $inline): string
    {
        return "Record<{$this->keyType->toTypeString($inline)}, {$this->valueType->toTypeString($inline)}>";
    }

    protected function getChildren(): array
    {
        return [$this->keyType, $this->valueType];
    }
}