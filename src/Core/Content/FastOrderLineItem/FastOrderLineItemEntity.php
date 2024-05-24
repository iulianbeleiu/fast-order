<?php declare(strict_types=1);

namespace FastOrder\Core\Content\FastOrderLineItem;

use Shopware\Core\Framework\DataAbstractionLayer\Entity;
use Shopware\Core\Framework\DataAbstractionLayer\EntityIdTrait;

class FastOrderLineItemEntity extends Entity
{
    use EntityIdTrait;

	protected string $sessionId;

	protected string $productNumber;

	protected int $quantity;

	protected ?string $comment;

	public function getSessionId(): string
	{
		return $this->sessionId;
	}

	public function setSessionId(string $sessionId): void
	{
		$this->sessionId = $sessionId;
	}

	public function getProductNumber(): string
	{
		return $this->productNumber;
	}

	public function setProductNumber(string $productNumber): void
	{
		$this->productNumber = $productNumber;
	}

	public function getQuantity(): int
	{
		return $this->quantity;
	}

	public function setQuantity(int $quantity): void
	{
		$this->quantity = $quantity;
	}

	public function getComment(): ?string
	{
		return $this->comment;
	}

	public function setComment(?string $comment): void
	{
		$this->comment = $comment;
	}
}
