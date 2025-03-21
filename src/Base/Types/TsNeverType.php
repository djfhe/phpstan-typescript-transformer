<?php

namespace djfhe\StanScript\Base\Types;

use djfhe\StanScript\TsType;

class TsNeverType extends TsType
{
    public function toTypeDefinition(bool $inline): string
    {
        return 'never';
    }

    protected function getChildren(): array
    {
        return [];
    }
}