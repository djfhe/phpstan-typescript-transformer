<?php

namespace djfhe\PHPStanTypescriptTransformer\Base\Types;

use djfhe\PHPStanTypescriptTransformer\TsType;

class TsNeverType extends TsType
{
    protected function typeDefinition(): string
    {
        return 'never';
    }
}