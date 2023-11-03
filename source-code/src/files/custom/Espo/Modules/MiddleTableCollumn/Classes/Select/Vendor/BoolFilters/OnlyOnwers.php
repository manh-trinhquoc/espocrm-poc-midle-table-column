<?php

namespace Espo\Modules\MiddleTableCollumn\Classes\Select\Vendor\BoolFilters;

use Espo\Core\Select\Bool\Filter;

use Espo\ORM\Query\{
    SelectBuilder,
    Part\Where\OrGroupBuilder,
    Part\WhereClause,
};

class OnlyOwners implements Filter
{
    public function __construct()
    {
    }

    public function apply(SelectBuilder $queryBuilder, OrGroupBuilder $orGroupBuilder): void
    {
        $queryBuilder
          ->leftJoin('contactVendor', 'cv', ['cv.vendorId:' => 'vendor.id'])
          ->leftJoin('contact', 'c', ['cv.contactId:' => 'c.id']);

        $orGroupBuilder->add(
            WhereClause::fromRaw(array(
            'cv.role' => "Owner",
      ))
        );
    }
}
