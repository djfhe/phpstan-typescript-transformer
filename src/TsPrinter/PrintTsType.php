<?php

namespace djfhe\StanScript\TsPrinter;

use djfhe\StanScript\TsType;
use PHPStan\Analyser\Error;
use PHPStan\Rules\RuleError;
use PHPStan\Rules\RuleErrorBuilder;

final class PrintTsType
{

  public static string $error_identifier = 'djfhe.StanScript.printTsType';

    public function __construct(
      public string $namespace,
      public string $name,
      public TsType $type,
    ) { }

    public static function create(
      string $namespace,
      string $name,
      TsType $type,
    ): self {
      return new self($namespace, $name, $type);
    }

    public function toPHPStanError(): RuleError
    {
        return RuleErrorBuilder::message('')
          ->identifier(self::$error_identifier)
          ->metadata([
            'namespace' => $this->namespace,
            'name' => $this->name,
            'type' => serialize($this->type),
          ])
          ->build();
    }

    public static function fromPHPStanError(Error $error): self
    {
        $metadata = $error->getMetadata();

        return new self(
            $metadata['namespace'],
            $metadata['name'],
            unserialize($metadata['type']),
        );
    }

    /**
     * @return TsType[]
     */
    public function getRecursiveChildren(): array
    {
        return $this->type->getRecursiveChildren();
    }
}
