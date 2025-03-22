<?php

namespace djfhe\StanScript\Base\Types;

use djfhe\StanScript\IsSingleton;
use djfhe\StanScript\TsType;

class TsNeverType extends TsType
{
    use IsSingleton;

    protected function typeDefinition(): string
    {
        return 'never';
    }
}