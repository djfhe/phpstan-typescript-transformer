<?php

namespace djfhe\PHPStanTypescriptTransformer\Laravel\PhpstanTypes;

use djfhe\PHPStanTypescriptTransformer\Base\PhpstanTypes\MetaType;
use PHPStan\PhpDocParser\Ast\Type\IdentifierTypeNode;
use PHPStan\PhpDocParser\Ast\Type\TypeNode;
use PHPStan\Type\Type;
use PHPStan\Type\VerbosityLevel;

class InertiaOptionalPropType extends MetaType
{
	public function __construct(private Type $propType)
	{
	}

  public function getPropType(): Type
  {
    return $this->propType;
  }

	public function describe(VerbosityLevel $level): string {
    return 'inertia-optional-prop';
  }

	public function toPhpDocNode(): TypeNode {
    return new IdentifierTypeNode('inertia-optional-prop');
  }
}