<?php

namespace App\Controller;

use App\Entity\Article;
use App\Entity\ArticleTag;
use App\Entity\Tag;
use App\Repository\ArticleRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Validator\Validator\ValidatorInterface;


#[Route('/api/articles')]
final class ArticleController extends AbstractController
{
    #[Route(name: 'app_article_index', methods: ['GET'])]
    public function index(ArticleRepository $articleRepository): JsonResponse
    {
        $articles = $articleRepository->findAll();
        $data = [];

        foreach ($articles as $article) {
            $data[] = ['id' => $article->getId(), 'title' => $article->getTitle()];
        }

        return new JsonResponse($data, Response::HTTP_OK);
    }

    #[Route('/create', name: 'app_article_new', methods: ['POST'])]
    public function create(Request $request, EntityManagerInterface $entityManager, ValidatorInterface $validator): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        if (!isset($data['title'])) {
            return new JsonResponse(['message' => 'The title field is required'], Response::HTTP_BAD_REQUEST);
        }

        $article = new Article();
        $article->setTitle($data['title']);

        if (isset($data['tags'])) {
            if (!is_array($data['tags'])) {
                return new JsonResponse(['message' => 'The tags field is not array'], Response::HTTP_BAD_REQUEST);
            }

            foreach ($data['tags'] as $tagName) {
                $tag = $entityManager->getRepository(Tag::class)->findOneBy(['name' => $tagName]);

                if (!$tag) {
                    $tag = new Tag();
                    $tag->setName($tagName);
                    $entityManager->persist($tag);
                }

                $articleTag = new ArticleTag();
                $articleTag->setArticle($article);
                $articleTag->setTag($tag);

                $article->addArticleTag($articleTag);
            }
        }

        $errors = $validator->validate($article);

        if (count($errors) > 0) {
            $errorMessages = [];
            foreach ($errors as $error) {
                $errorMessages[] = $error->getMessage();
            }

            return new JsonResponse(['errors' => $errorMessages], Response::HTTP_BAD_REQUEST);
        }

        $entityManager->persist($article);
        $entityManager->flush();

        return new JsonResponse(['message' => 'Article created', 'id' => $article->getId()], Response::HTTP_CREATED);
    }

    #[Route('/{id}', name: 'app_article_show', methods: ['GET'])]
    public function show($id, EntityManagerInterface $entityManager): JsonResponse
    {

        if (!is_numeric($id)) {
            return new JsonResponse(['message' => 'Invalid article ID'], Response::HTTP_BAD_REQUEST);
        }

        $article = $entityManager->getRepository(Article::class)->find($id);

        if (!$article) {
            return new JsonResponse(['message' => 'Article not found'], Response::HTTP_NOT_FOUND);
        }

        $data = [
            'id' => $article->getId(),
            'title' => $article->getTitle(),
        ];

        return new JsonResponse($data, Response::HTTP_OK);
    }

    #[Route('/{id}', name: 'app_article_edit', methods: ['PUT'])]
    public function edit($id, Request $request, EntityManagerInterface $entityManager, ValidatorInterface $validator): JsonResponse
    {
        if (!is_numeric($id)) {
            return new JsonResponse(['message' => 'Invalid article ID'], Response::HTTP_BAD_REQUEST);
        }

        $article = $entityManager->getRepository(Article::class)->find($id);

        if (!$article) {
            return new JsonResponse(['message' => 'Article not found'], Response::HTTP_NOT_FOUND);
        }

        $data = json_decode($request->getContent(), true);

        if (!isset($data['title'])) {
            return new JsonResponse(['message' => 'The title field is required'], Response::HTTP_BAD_REQUEST);
        }

        $article->setTitle($data['title']);
        $errors = $validator->validate($article);

        if (count($errors) > 0) {
            $errorMessages = [];
            foreach ($errors as $error) {
                $errorMessages[] = $error->getMessage();
            }

            return new JsonResponse(['errors' => $errorMessages], Response::HTTP_BAD_REQUEST);
        }

        $entityManager->flush();

        return new JsonResponse(['message' => 'Article updated', 'id' => $article->getId()], Response::HTTP_OK);

    }

    #[Route('/{id}', name: 'app_article_delete', methods: ['DELETE'])]
    public function delete($id, EntityManagerInterface $entityManager): JsonResponse
    {
        if (!is_numeric($id)) {
            return new JsonResponse(['message' => 'Invalid article ID'], Response::HTTP_BAD_REQUEST);
        }

        $article = $entityManager->getRepository(Article::class)->find($id);

        if (!$article) {
            return new JsonResponse(['message' => 'Article not found'], Response::HTTP_NOT_FOUND);
        }

        $entityManager->remove($article);
        $entityManager->flush();


        return new JsonResponse(['message' => 'Article deleted'], Response::HTTP_OK);
    }
}
