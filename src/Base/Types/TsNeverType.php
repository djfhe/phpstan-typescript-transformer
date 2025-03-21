<?php

namespace djfhe\StanScript\Base\Types;

use djfhe\StanScript\_TsType;

class TsNeverType extends _TsType
{
    public function toTypeDefinition(bool $inline): string
    {
        return 'never';
    }

    protected function _serialize(): array
    {
        return [];
    }

    protected static function _deserialize(array $data): static
    {
        return new self();
    }

    protected function getChildren(): array
    {
        return [];
    }
}