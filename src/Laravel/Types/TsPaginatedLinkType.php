<?php

namespace djfhe\StanScript\PHPStan\Typescript\TypescriptTypes\Laravel;

use djfhe\StanScript\IsSingleton;
use djfhe\StanScript\TsType;

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