<?php

namespace djfhe\StanScript\PHPStan\Typescript\TypescriptTypes\Laravel;

use djfhe\StanScript\_TsType;

class TsAbstractPaginatedType extends _TsType
{
    public function __construct(private _TsType $itemType) {}

    public function toTypeDefinition(bool $inline): string
    {
      $itemTsType = $this->itemType->toTypeString($inline);
      
      $paginationLink = '{ active: boolean; label: string; url: string | null; }';
      $paginated = "{ current_page: number; data: {$itemTsType}[]; first_page_url: string; from: number; last_page: number; last_page_url: string; links: {$paginationLink}[]; next_page_url: string | null; path: string; per_page: number; prev_page_url: string | null; to: number; total: number; }";
    
      
      return $paginated;
    }

    protected function _serialize(): array
    {
        return [
            'itemType' => $this->itemType->serialize(),
        ];
    }

    protected static function _deserialize(array $data): static
    {
        return new self(_TsType::deserialize($data['itemType']));
    }

    protected function getChildren(): array
    {
        return [$this->itemType];
    }
}