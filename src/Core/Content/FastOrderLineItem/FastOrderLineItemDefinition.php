<?php declare(strict_types=1);

namespace FastOrder\Core\Content\FastOrderLineItem;

use Shopware\Core\Framework\DataAbstractionLayer\EntityDefinition;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\ApiAware;
use Shopware\Core\Framework\DataAbstractionLayer\Field\IntField;
use Shopware\Core\Framework\DataAbstractionLayer\FieldCollection;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\PrimaryKey;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\Required;
use Shopware\Core\Framework\DataAbstractionLayer\Field\IdField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\StringField;
use Shopware\Core\System\NumberRange\DataAbstractionLayer\NumberRangeField;

class FastOrderLineItemDefinition extends EntityDefinition
{
    public const ENTITY_NAME = 'fast_order_line_item';

    public function getEntityName(): string
    {
        return self::ENTITY_NAME;
    }

    public function getEntityClass(): string
    {
        return FastOrderLineItemEntity::class;
    }

    public function getCollectionClass(): string
    {
        return FastOrderLineItemCollection::class;
    }

    protected function defineFields(): FieldCollection
    {
        return new FieldCollection([
            (new IdField('id', 'id'))->addFlags(new Required(), new PrimaryKey()),
            (new StringField('session_id', 'sessionId'))->addFlags(new ApiAware(), new Required()),
            (new NumberRangeField('product_number', 'productNumber'))->addFlags(new ApiAware(), new Required()),
	        (new IntField('quantity', 'quantity'))->addFlags(new ApiAware(), new Required()),
            (new StringField('comment', 'comment'))->addFlags(new ApiAware()),
        ]);
    }
}
