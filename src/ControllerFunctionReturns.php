<?php

namespace djfhe\ControllerTransformer;

use djfhe\ControllerTransformer\PHPStan\Typescript\TypescriptTypes\_TsType;

final class ControllerFunctionReturns
{

  public static string $error_identifier = 'djfhe.controllerTransformer.controllerMethodReturnType';

    public function __construct(
      public string $class,
      public string $methodName,
      /** @var _TsType[] */
      public array $returns = []
    ) { }

    public function add(_TsType $inertiaPropsContainer): void
    {
        $this->returns[] = $inertiaPropsContainer;
    }

    public function count(): int
    {
        return count($this->returns);
    }

    public function serialize(): array
    {
        return [
            'class' => $this->class,
            'methodName' => $this->methodName,
            'returns' => array_map(fn(_TsType $type) => $type->serialize(), $this->returns)
        ];
    }

    public static function deserialize(array $data): self
    {
        return new self(
            $data['class'],
            $data['methodName'],
            array_map(fn($type) => _TsType::deserialize($type), $data['returns'])
        );
    }

    /**
     * @return _TsType[]
     */
    public function getRecursiveChildren(): array
    {
        $children = $this->returns;
        $result = $children;
        
        foreach ($children as $child) {
          $result = array_merge($result, $child->getRecursiveChildren());
        }

        return $result;
    }
}
