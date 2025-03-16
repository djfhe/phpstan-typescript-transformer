<?php

namespace djfhe\ControllerTransformer\TypescriptTransformer;

use ReflectionClass;
use Spatie\TypeScriptTransformer\Collectors\Collector;
use Spatie\TypeScriptTransformer\Structures\MissingSymbolsCollection;
use Spatie\TypeScriptTransformer\Structures\TransformedType;
use Spatie\TypeScriptTransformer\TypeScriptTransformerConfig;

class ControllerCollector extends Collector
{ 
  /**
   * @var array<string, array{class: string, method: string, returns: string}>
   */
    private array $parsedPhpstan = [];

    public function __construct(TypeScriptTransformerConfig $config)
    {
        parent::__construct($config);

        $filePath = './controller_schema.json';
        $file = file_get_contents($filePath);

        if ($file === false) {
          return;
        }
        /** @var array{class: string, method: string, returns: string}[] */
        $parsed = json_decode($file, true);

        if (!is_array($parsed)) {
          return;
        }

        foreach ($parsed as $item) {
          $class = $item['class'];

          if (!array_key_exists($class, $this->parsedPhpstan)) {
            $this->parsedPhpstan[$class] = [];
          }

          $this->parsedPhpstan[$class][] = $item;
        }

    }

    /**
     * @param  ReflectionClass<object>  $class
     */
    public function getTransformedType(ReflectionClass $class): ?TransformedType
    {
        $namespace = $class->getNamespaceName();
        if (! str_starts_with($namespace, 'App\\Http\\Controllers')) {
            return null;
        }

        $name = $class->getName();

        if (!array_key_exists($name, $this->parsedPhpstan)) {
          return null;
        }

        $methods = $this->parsedPhpstan[$name];

        $transformed = implode(PHP_EOL, array_map(function ($method) {
          return $method['method'] . ': ' . $method['returns'] . ';';
        }, $methods));

        $missingSymbols = new MissingSymbolsCollection();

        $transformed = preg_replace_callback('({%([^%]+)%})', function ($matches) use ($missingSymbols) {
          return $missingSymbols->add($matches[1]);
        }, $transformed);

        if (!is_string($transformed)) {
          return null;
        }

        return new TransformedType(
          class: $class,
          name: $class->getShortName(),
          transformed: '{' . PHP_EOL . $transformed . PHP_EOL . '}',
          missingSymbols: $missingSymbols,
          isInline: false,
          keyword: 'interface'
        );
    }
}
