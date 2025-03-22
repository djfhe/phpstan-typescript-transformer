<?php

namespace djfhe\StanScript\TsPrinter;

class TsRawTypePrinter implements TsTypePrinterContract
{
    public function __construct(
        private string $keyword,
        private string $namespace,
        private string $name,
        private string $typeDefinition,
    ) {}

    public function getTsNamespace(): ?string
    {
        return $this->namespace;
    }

    public function getTsName(): string
    {
        return $this->name;
    }

    public function printTypeString(): string
    {
        return TsPrinterUtil::createDeclaration($this->keyword, $this->name, $this->typeDefinition);
    }
}