<?php declare(strict_types=1);

namespace FastOrder\Storefront\Controller;

use Shopware\Core\Checkout\Cart\Cart;
use Shopware\Core\Checkout\Cart\Error\Error;
use Shopware\Core\Checkout\Cart\LineItemFactoryHandler\ProductLineItemFactory;
use Shopware\Core\Checkout\Cart\SalesChannel\CartService;
use Shopware\Core\Content\Product\ProductCollection;
use Shopware\Core\Content\Product\SalesChannel\ProductListRoute;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsAnyFilter;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\MultiFilter;
use Shopware\Core\Framework\Routing\RoutingException;
use Shopware\Core\Profiling\Profiler;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Shopware\Storefront\Controller\StorefrontController;
use Shopware\Storefront\Page\Suggest\SuggestPageLoadedHook;
use Shopware\Storefront\Page\Suggest\SuggestPageLoader;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route(defaults: ['_routeScope' => ['storefront']])]
class FastOrderController extends StorefrontController
{
	public function __construct(
		private readonly SuggestPageLoader $suggestPageLoader,
		private readonly ProductListRoute $productListRoute,
		private readonly ProductLineItemFactory $productLineItemFactory,
		private readonly CartService $cartService,
		private readonly EntityRepository $fastOrderLineItemRepository
	) {
	}

	#[Route(
        path: '/fast-order',
        name: 'frontend.fast.order',
        methods: ['GET']
    )]
    public function fastOrderPage(): Response
    {
        return $this->renderStorefront('@FastOrder/storefront/page/fast-order.html.twig');
    }

	#[Route(path: '/fast-order/article-search', name: 'frontend.fast.order.article.suggest', defaults: ['XmlHttpRequest' => true, '_httpCache' => true], methods: ['GET'])]
	public function suggest(SalesChannelContext $context, Request $request): Response
	{
		$page = $this->suggestPageLoader->load($request, $context);

		$this->hook(new SuggestPageLoadedHook($page, $context));

		return $this->renderStorefront('@FastOrder/storefront/component/fast-order/article-search-suggest.html.twig', ['page' => $page]);
	}

	#[Route(path: '/fast-order/product/add-to-cart', name: 'frontend.fast.order.add-to-cart', methods: ['POST'])]
	public function addProductByNumber(Request $request, SalesChannelContext $context): Response
	{
		return Profiler::trace('fast-order::add-to-cart', function () use ($request, $context) {
			$productNumbers = (array) $request->get('productNumbers');
			$quantities = (array) $request->get('quantities');

			$this->validateRequestParameters($productNumbers, $quantities);

			$productsWithQuantities = array_combine($productNumbers, $quantities);

			$products = $this->loadProducts($productNumbers, $context);

			if ($products->count() === 0) {
				$this->addFlash(self::DANGER, $this->trans('FastOrder.cart.noProductsFound'));
				return $this->createActionResponse($request);
			}

			$cart = $this->cartService->getCart($context->getToken(), $context);
			list($lineItems, $fastOrderLineItems) = $this->createLineItems($products, $productsWithQuantities, $context, $request);

			$cart = $this->cartService->add($cart, $lineItems, $context);

			if (!$this->traceErrors($cart)) {
				$this->fastOrderLineItemRepository->upsert($fastOrderLineItems, $context->getContext());
				$this->addFlash(self::SUCCESS, $this->trans('checkout.addToCartSuccess', ['%count%' => count($lineItems)]));
			}

			return $this->createActionResponse($request);
		});
	}

	private function validateRequestParameters($productNumbers, $quantities):void
	{
		if (empty($productNumbers)) {
			throw RoutingException::missingRequestParameter('productNumbers');
		}

		if (empty($quantities)) {
			throw RoutingException::missingRequestParameter('quantities');
		}
	}

	private function loadProducts($productNumbers, $context): ProductCollection
	{
		$criteria = new Criteria();
		$criteria->addFilter(new EqualsAnyFilter('productNumber', $productNumbers));
		$criteria->addFilter(new MultiFilter(MultiFilter::CONNECTION_OR, [
			new EqualsFilter('childCount', 0),
			new EqualsFilter('childCount', null),
		]));

		return $this->productListRoute->load($criteria, $context)->getProducts();
	}

	private function createLineItems($products, $productsWithQuantities, $context, $request): array
	{
		$lineItems = [];
		$fastOrderLineItems = [];

		foreach ($products as $product) {
			$quantity = (int) (!empty($productsWithQuantities[$product->getProductNumber()] && is_numeric($productsWithQuantities[$product->getProductNumber()]))
				? $productsWithQuantities[$product->getProductNumber()]
				: 1
			);
			$lineItems[] = $this->productLineItemFactory->create([
				'id' => $product->getId(),
				'referencedId' => $product->getId(),
				'quantity' => $quantity,
			], $context);

			$fastOrderLineItems[] = [
				'productNumber' => $product->getProductNumber(),
				'quantity' => $quantity,
				'sessionId' => $request->getSession()->getId(),
			];
		}

		return [$lineItems, $fastOrderLineItems];
	}

	private function traceErrors(Cart $cart): bool
	{
		if ($cart->getErrors()->count() <= 0) {
			return false;
		}

		$this->addCartErrors($cart, fn (Error $error) => $error->isPersistent());

		return true;
	}
}
