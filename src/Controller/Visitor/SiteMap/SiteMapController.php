<?php

namespace App\Controller\Visitor\SiteMap;

use App\Repository\RecipeRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class SiteMapController extends AbstractController
{
    public function __construct(
        private readonly RecipeRepository $recipeRepository,
    ) {
    }

    #[Route('/sitemap.xml', name: 'app_visitor_sitemap_index', methods: ['GET'])]
    public function index(Request $request): Response
    {
        $hostName = $request->getSchemeAndHttpHost();

        $urls = [];
        $urls[] = [
            'loc' => $this->generateUrl('app_visitor_welcome'),
        ];

        $recipes = $this->recipeRepository->findBy(['isPublished' => true], ['publishedAt' => 'DESC']);

        foreach ($recipes as $recipe) {
            $urls[] = [
                'loc' => $this->generateUrl('app_visitor_blog_recipe_show', ['id' => $recipe->getId(), 'slug' => $recipe->getSlug()]),
                'lastmod' => $recipe->getUpdatedAt()->format('Y-m-d'),
                'priority' => 0.9,
                'changefreq' => 'weekly',
            ];
        }

        $response = $this->render('pages/visitor/site_map/index.html.twig', [
            'host_name' => $hostName,
            'urls' => $urls,
        ]);

        $response->headers->set('Content-Type', 'text/xml');

        return $response;
    }
}
