<?php

namespace djfhe\PHPStanTypescriptTransformer\Base\PhpstanTypes;

use djfhe\PHPStanTypescriptTransformer\TsType;
use PHPStan\PhpDocParser\Ast\Type\IdentifierTypeNode;
use PHPStan\PhpDocParser\Ast\Type\TypeNode;
use PHPStan\Type\VerbosityLevel;

class LiteralTypescriptType extends MetaType
{
	public function __construct(private TsType $tsType)
	{
	}

  public function tsType(): TsType
  {
    return $this->tsType;
  }

	public function describe(VerbosityLevel $level): string {
    return 'literal-typescript-type';
  }

	public function toPhpDocNode(): TypeNode {
    return new IdentifierTypeNode('literal-typescript-type');
  }
}