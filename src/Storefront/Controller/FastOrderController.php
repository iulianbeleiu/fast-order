<?php declare(strict_types=1);

namespace FastOrder\Storefront\Controller;

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
		private readonly SuggestPageLoader $suggestPageLoader
	) {
	}

	#[Route(
        path: '/fast-order',
        name: 'frontend.fast.order',
        methods: ['GET']
    )]
    public function fastOrderPage(Request $request, SalesChannelContext $context): Response
    {
        return $this->renderStorefront('@FastOrder/storefront/page/fast-order.html.twig', [
            'example' => 'Hello world'
        ]);
    }

	#[Route(path: '/fast-order-article-search', name: 'frontend.fast.order.article.suggest', defaults: ['XmlHttpRequest' => true, '_httpCache' => true], methods: ['GET'])]
	public function suggest(SalesChannelContext $context, Request $request): Response
	{
		$page = $this->suggestPageLoader->load($request, $context);

		$this->hook(new SuggestPageLoadedHook($page, $context));

		return $this->renderStorefront('@FastOrder/storefront/component/fast-order/article-search-suggest.html.twig', ['page' => $page]);
	}
}
