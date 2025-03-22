<?php

use djfhe\StanScript\TsType;

class TsLiteralType extends TsType
{
    public function __construct(
        protected string $value
    ) {}

    protected function typeDefinition(): string
    {
        return $this->value;
    }
}