<?php

namespace djfhe\PHPStanTypescriptTransformer\Base\Types;

use djfhe\PHPStanTypescriptTransformer\IsSingleton;
use djfhe\PHPStanTypescriptTransformer\TsType;

class TsNeverType extends TsType
{
    use IsSingleton;

    protected function typeDefinition(): string
    {
        return 'never';
    }
}