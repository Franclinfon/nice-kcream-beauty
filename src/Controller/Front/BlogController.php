<?php

namespace App\Controller\Front;

use App\Entity\BlogPost;
use App\Repository\BlogPostRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class BlogController extends AbstractController
{
    #[Route('/blog', name: 'app_front_blog_index')]
    public function index(BlogPostRepository $blogPostRepository): Response
    {
        $posts = $blogPostRepository->findBy(
            ['isPublished' => true],
            ['publishedAt' => 'DESC']
        );

        return $this->render('front/blog/index.html.twig', [
            'posts' => $posts,
        ]);
    }

    #[Route('/blog/{slug}', name: 'app_front_blog_show')]
    public function show(string $slug, BlogPostRepository $blogPostRepository): Response
    {
        $post = $blogPostRepository->findOneBy([
            'slug' => $slug,
            'isPublished' => true,
        ]);

        if (!$post) {
            throw $this->createNotFoundException('Article introuvable.');
        }

        $recentPosts = $blogPostRepository->findBy(
            ['isPublished' => true],
            ['publishedAt' => 'DESC'],
            3
        );

        return $this->render('front/blog/show.html.twig', [
            'post' => $post,
            'recentPosts' => $recentPosts,
        ]);
    }
}