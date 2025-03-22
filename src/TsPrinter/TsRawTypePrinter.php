<?php

namespace djfhe\PHPStanTypescriptTransformer\TsPrinter;

class TsRawTypePrinter implements TsTypePrinterContract
{
    public function __construct(
        /** @var 'interface'|'type' */
        private string $keyword,
        private ?string $namespace,
        private string $name,
        /** @var string[] */
        private array $genericKeys,
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
        return TsPrinterUtil::createDeclaration(
            keyword: $this->keyword,
            name: $this->name,
            genericKeys: $this->genericKeys,
            definition: $this->typeDefinition
        );
    }
}