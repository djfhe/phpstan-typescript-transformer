<?php

namespace djfhe\PHPStanTypescriptTransformer\Base\PhpstanTypes;

use PHPStan\PhpDocParser\Ast\Type\IdentifierTypeNode;
use PHPStan\PhpDocParser\Ast\Type\TypeNode;
use PHPStan\Type\VerbosityLevel;

class UndefinedType extends MetaType
{
	public function __construct(private bool $markKeyAsOptional = false)
	{
	}

  public function markKeyAsOptional(): bool
  {
    return $this->markKeyAsOptional;
  }

	public function describe(VerbosityLevel $level): string {
    return 'undefined';
  }

	public function toPhpDocNode(): TypeNode {
    return new IdentifierTypeNode('undefined');
  }
}