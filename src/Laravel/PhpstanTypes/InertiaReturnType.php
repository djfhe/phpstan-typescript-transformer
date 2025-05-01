<?php

namespace djfhe\PHPStanTypescriptTransformer\Laravel\PhpstanTypes;

use djfhe\PHPStanTypescriptTransformer\Base\PhpstanTypes\MetaType;
use PHPStan\PhpDocParser\Ast\Type\IdentifierTypeNode;
use PHPStan\PhpDocParser\Ast\Type\TypeNode;
use PHPStan\Type\StringType;
use PHPStan\Type\Type;
use PHPStan\Type\VerbosityLevel;

class InertiaReturnType extends MetaType
{
	public function __construct(
    private StringType $sitePath,
    private ?Type $props,
  ) {
	}

  public function getSitePath(): StringType
  {
    return $this->sitePath;
  }

  public function getProps(): ?Type
  {
    return $this->props;
  }

	public function describe(VerbosityLevel $level): string {
    return 'inertia-return';
  }

	public function toPhpDocNode(): TypeNode {
    return new IdentifierTypeNode('inertia-return');
  }
}