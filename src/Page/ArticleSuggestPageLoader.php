<?php declare(strict_types=1);

namespace FastOrder\Page;

use Shopware\Core\Content\Product\SalesChannel\Suggest\AbstractProductSuggestRoute;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\RangeFilter;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Shopware\Storefront\Page\GenericPageLoaderInterface;
use Shopware\Storefront\Page\Suggest\SuggestPage;
use Symfony\Component\HttpFoundation\Request;

class ArticleSuggestPageLoader
{
	public function __construct(
		private readonly AbstractProductSuggestRoute $productSuggestRoute,
		private readonly GenericPageLoaderInterface $genericLoader
	) {
	}

	public function load(Request $request, SalesChannelContext $salesChannelContext): SuggestPage
	{
		$page = $this->genericLoader->load($request, $salesChannelContext);

		$page = SuggestPage::createFrom($page);

		$criteria = new Criteria();
		$criteria->addFilter(new EqualsFilter('active', true));
		$criteria->addFilter(new RangeFilter('stock', ['gt' => 0]));

		$page->setSearchResult(
			$this->productSuggestRoute
				->load($request, $salesChannelContext, $criteria)
				->getListingResult()
		);

		return $page;
	}
}
