<?php

namespace djfhe\PHPStanTypescriptTransformer\Laravel\Types;

use djfhe\PHPStanTypescriptTransformer\TsType;

class TsPaginatedLinkType extends TsType
{
    public function __construct() {}

    protected function typeDefinition(): string
    { 
      return '{ active: boolean; label: string; url: string | null; }';
    }

    public function getName(): string
    {
        return 'Laravel\\PaginatedLink';
    }
}