<?php

namespace djfhe\PHPStanTypescriptTransformer\Base\Types;

use djfhe\PHPStanTypescriptTransformer\TsType;

class TsCyclicType extends TsType
{
  public ?TsType $referencedType;

  public function getName(): ?string
  {
    if ($this->referencedType === null) {
      throw new \Exception('Cyclic type not resolved');
    }

    return $this->referencedType->getName();
  }

  protected function typeDefinition(): string
  {
    if ($this->referencedType === null) {
      throw new \Exception('Cyclic type not resolved');
    }

    return $this->referencedType->typeDefinition();
  }

  protected function genericParameters(): array
  {
    if ($this->referencedType === null) {
      throw new \Exception('Cyclic type not resolved');
    }

    return $this->referencedType->_genericParameters();
  }
}