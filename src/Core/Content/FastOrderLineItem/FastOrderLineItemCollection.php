<?php declare(strict_types=1);

namespace FastOrder\Core\Content\FastOrderLineItem;

use Shopware\Core\Framework\DataAbstractionLayer\EntityCollection;

/**
 * @method void add(FastOrderLineItemEntity $entity)
 * @method void set(string $key, FastOrderLineItemEntity $entity)
 * @method FastOrderLineItemEntity[] getIterator()
 * @method FastOrderLineItemEntity[] getElements()
 * @method FastOrderLineItemEntity|null get(string $key)
 * @method FastOrderLineItemEntity|null first()
 * @method FastOrderLineItemEntity|null last()
 */
class FastOrderLineItemCollection extends EntityCollection
{
    protected function getExpectedClass(): string
    {
        return FastOrderLineItemEntity::class;
    }
}
