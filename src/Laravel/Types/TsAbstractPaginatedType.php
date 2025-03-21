<?php

namespace djfhe\StanScript\PHPStan\Typescript\TypescriptTypes\Laravel;

use djfhe\StanScript\TsType;

class TsAbstractPaginatedType extends TsType
{
    public function __construct(private TsType $itemType) {}

    public function typeDefinition(): string
    {
      $itemTsType = $this->itemType->printTypeString();
      
      $paginationLink = '{ active: boolean; label: string; url: string | null; }';
      $paginated = "{ current_page: number; data: {$itemTsType}[]; first_page_url: string; from: number; last_page: number; last_page_url: string; links: {$paginationLink}[]; next_page_url: string | null; path: string; per_page: number; prev_page_url: string | null; to: number; total: number; }";
    
      
      return $paginated;
    }

    protected function getChildren(): array
    {
        return [$this->itemType];
    }
}