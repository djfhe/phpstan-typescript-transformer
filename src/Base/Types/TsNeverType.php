<?php

namespace djfhe\StanScript\Base\Types;

use djfhe\StanScript\TsType;

class TsNeverType extends TsType
{
    public function typeDefinition(): string
    {
        return 'never';
    }

    protected function getChildren(): array
    {
        return [];
    }
}