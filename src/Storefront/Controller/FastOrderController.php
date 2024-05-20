<?php declare(strict_types=1);

namespace FastOrder\Storefront\Controller;

use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Shopware\Storefront\Controller\StorefrontController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route(defaults: ['_routeScope' => ['storefront']])]
class FastOrderController extends StorefrontController
{
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
}
