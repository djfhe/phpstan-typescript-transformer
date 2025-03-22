<?php

namespace djfhe\StanScript\Base\Types;

use djfhe\StanScript\TsType;

class TsNeverType extends TsType
{
    protected function typeDefinition(): string
    {
        return 'never';
    }
}