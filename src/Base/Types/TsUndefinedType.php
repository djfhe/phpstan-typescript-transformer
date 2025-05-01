<?php

namespace djfhe\PHPStanTypescriptTransformer\Base\Types;

use djfhe\PHPStanTypescriptTransformer\TsType;

class TsUndefinedType extends TsType
{
  protected function typeDefinition(): string
  {
      return 'undefined';
  }
}