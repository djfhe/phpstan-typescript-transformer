<?php

namespace djfhe\PHPStanTypescriptTransformer\PHPStan\Typescript\TypescriptTypes\Laravel;

use djfhe\PHPStanTypescriptTransformer\IsSingleton;
use djfhe\PHPStanTypescriptTransformer\TsType;

class TsPaginatedLinkType extends TsType
{
  use IsSingleton;

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