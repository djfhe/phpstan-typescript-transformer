<?php

namespace djfhe\PHPStanTypescriptTransformer\Base\Types;

use djfhe\PHPStanTypescriptTransformer\TsType;

class TsRecordType extends TsType
{
    public function __construct(
      protected TsType $keyType,
      protected TsType $valueType,
    ) {}

    protected function typeDefinition(): string
    {
        return "Record<{$this->keyType->printTypeString()}, {$this->valueType->printTypeString()}>";
    }
}