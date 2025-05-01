<?php

namespace djfhe\PHPStanTypescriptTransformer\Laravel\Types;

use djfhe\PHPStanTypescriptTransformer\TsType;

class TsAbstractPaginatedType extends TsType
{
    public function __construct(private TsType $itemType) {}

    public function getName(): string
    {
        return 'Laravel\\Paginated';
    }

    protected function typeDefinition(): string
    {
      $paginationLink = TsPaginatedLinkType::instance();
      return "{ current_page: number; data: T[]; first_page_url: string; from: number; last_page: number; last_page_url: string; links: {$paginationLink->printTypeString()}[]; next_page_url: string | null; path: string; per_page: number; prev_page_url: string | null; to: number; total: number; }";
    }

    protected function genericParameters(): array
    {
      return [
        'T' => $this->itemType->printTypeString()
      ];
    }
}