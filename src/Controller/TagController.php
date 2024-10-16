<?php

namespace App\Controller;

use App\Entity\Tag;
use App\Repository\TagRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Validator\Validator\ValidatorInterface;


#[Route('/api/tags')]
final class TagController extends AbstractController
{
    #[Route(name: 'app_tag_index', methods: ['GET'])]
    public function index(TagRepository $tagRepository): JsonResponse
    {
        $tags = $tagRepository->findAll();
        $data = [];

        foreach ($tags as $tag) {
            $data[] = ['id' => $tag->getId(), 'name' => $tag->getName()];
        }

        return new JsonResponse($data, Response::HTTP_OK);
    }

    #[Route('/create', name: 'app_tag_new', methods: ['POST'])]
    public function create(Request $request, EntityManagerInterface $entityManager, ValidatorInterface $validator, TagRepository $tagRepository): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        if (!isset($data['name'])) {
            return new JsonResponse(['message' => 'The name field is required'], Response::HTTP_BAD_REQUEST);
        }

        if ($tagRepository->findOneBy(['name' => $data['name']])) {
            return new JsonResponse(['message' => 'Tag with this name already exists'], Response::HTTP_CONFLICT);
        }

        $tag = new Tag();
        $tag->setName($data['name']);

        $errors = $validator->validate($tag);

        if (count($errors) > 0) {
            $errorMessages = [];
            foreach ($errors as $error) {
                $errorMessages[] = $error->getMessage();
            }

            return new JsonResponse(['errors' => $errorMessages], Response::HTTP_BAD_REQUEST);
        }

        $entityManager->persist($tag);
        $entityManager->flush();

        return new JsonResponse(['message' => 'Tag created', 'id' => $tag->getId()], Response::HTTP_CREATED);
    }

    #[Route('/{id}', name: 'app_tag_show', methods: ['GET'])]
    public function show($id, EntityManagerInterface $entityManager): JsonResponse
    {

        if (!is_numeric($id)) {
            return new JsonResponse(['message' => 'Invalid tag ID'], Response::HTTP_BAD_REQUEST);
        }

        $tag = $entityManager->getRepository(Tag::class)->find($id);

        if (!$tag) {
            return new JsonResponse(['message' => 'Tag not found'], Response::HTTP_NOT_FOUND);
        }

        $data = [
            'id' => $tag->getId(),
            'name' => $tag->getName(),
        ];

        return new JsonResponse($data, Response::HTTP_OK);
    }

    #[Route('/{id}', name: 'app_tag_edit', methods: ['PUT'])]
    public function edit($id, Request $request, EntityManagerInterface $entityManager, ValidatorInterface $validator): JsonResponse
    {
        if (!is_numeric($id)) {
            return new JsonResponse(['message' => 'Invalid tag ID'], Response::HTTP_BAD_REQUEST);
        }

        $tag = $entityManager->getRepository(Tag::class)->find($id);

        if (!$tag) {
            return new JsonResponse(['message' => 'Tag not found'], Response::HTTP_NOT_FOUND);
        }

        $data = json_decode($request->getContent(), true);

        if (!isset($data['name'])) {
            return new JsonResponse(['message' => 'The name field is required'], Response::HTTP_BAD_REQUEST);
        }

        $tag->setName($data['name']);
        $errors = $validator->validate($tag);

        if (count($errors) > 0) {
            $errorMessages = [];
            foreach ($errors as $error) {
                $errorMessages[] = $error->getMessage();
            }

            return new JsonResponse(['errors' => $errorMessages], Response::HTTP_BAD_REQUEST);
        }

        $entityManager->flush();

        return new JsonResponse(['message' => 'Tag updated', 'id' => $tag->getId()], Response::HTTP_OK);

    }

    #[Route('/{id}', name: 'app_tag_delete', methods: ['DELETE'])]
    public function delete($id, EntityManagerInterface $entityManager): JsonResponse
    {
        if (!is_numeric($id)) {
            return new JsonResponse(['message' => 'Invalid tag ID'], Response::HTTP_BAD_REQUEST);
        }

        $tag = $entityManager->getRepository(Tag::class)->find($id);

        if (!$tag) {
            return new JsonResponse(['message' => 'Tag not found'], Response::HTTP_NOT_FOUND);
        }

        $entityManager->remove($tag);
        $entityManager->flush();


        return new JsonResponse(['message' => 'Tag deleted'], Response::HTTP_OK);
    }
}
