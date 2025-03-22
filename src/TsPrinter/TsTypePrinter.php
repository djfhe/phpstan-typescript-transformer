<?php

namespace djfhe\StanScript\TsPrinter;

use djfhe\StanScript\TsType;
use PHPStan\Analyser\Error;
use PHPStan\Rules\IdentifierRuleError;
use PHPStan\Rules\RuleError;
use PHPStan\Rules\RuleErrorBuilder;
use SplObjectStorage;

final class TsTypePrinter implements TsTypePrinterContract
{
  /**
   * @var SplObjectStorage<TsType,int>
   */
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

    public function toPHPStanError(): IdentifierRuleError
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

        $name = $metadata['name'];
        $serializedTypeData = $metadata['type'];

        if (!is_string($name) || !is_string($serializedTypeData)) {
            throw new \InvalidArgumentException('name or type metadata is not a string');
        }

        $unserializedTypeData = unserialize($serializedTypeData);

        if (!$unserializedTypeData instanceof TsType) {
            throw new \InvalidArgumentException('type metadata is not an instance of TsType');
        }

        return new self(
            $name,
            $unserializedTypeData
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

        $genericKeys = array_keys($this->type->_genericParameters());

        $typeDefinition = TsPrinterUtil::createDeclaration(
            keyword: $keyword,
            name: $typeName,
            genericKeys: $genericKeys,
            definition: $code
        );
        
        return $typeDefinition;
    }
}
