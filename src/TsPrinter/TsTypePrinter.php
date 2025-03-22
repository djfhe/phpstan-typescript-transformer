<?php

namespace djfhe\StanScript\TsPrinter;

use djfhe\StanScript\TsType;
use PHPStan\Analyser\Error;
use PHPStan\Rules\RuleError;
use PHPStan\Rules\RuleErrorBuilder;
use SplObjectStorage;

final class TsTypePrinter implements TsTypePrinterContract
{
  public static SplObjectStorage $printingTypesStack;

  protected static function resetPrintingTypesStack(): void
  {
      self::$printingTypesStack = new SplObjectStorage();
  }

  public static string $error_identifier = 'djfhe.StanScript.printTsType';

    public function __construct(
      public string $name,
      public TsType $type,
    ) { }

    public static function create(
      ?string $namespace,
      string $name,
      TsType $type,
    ): self {
      if ($namespace === null || $namespace === '') {
        return new self($name, $type);
      }

      return new self($namespace . '\\' . $name, $type);
    }

    public function toPHPStanError(): RuleError
    {
        return RuleErrorBuilder::message('')
          ->identifier(self::$error_identifier)
          ->metadata([
            'name' => $this->name,
            'type' => serialize($this->type),
          ])
          ->build();
    }

    public static function fromPHPStanError(Error $error): self
    {
        $metadata = $error->getMetadata();

        return new self(
            $metadata['name'],
            unserialize($metadata['type']),
        );
    }

    public function getTsNamespace(): ?string
    {
      return TsPrinterUtil::getNamespace($this->name);
    }

    public function getTsName(): string
    {
      return TsPrinterUtil::getName($this->name);
    }

    public function printTypeString(): string
    {

        $keyword = $this->type->definitionKeyword();
        $typeName = $this->getTsName();

        self::resetPrintingTypesStack();

        $code = $this->type->printTypeString();

        $typeDefinition = TsPrinterUtil::createDeclaration($keyword, $typeName, $code);
        
        return $typeDefinition;
    }
}
