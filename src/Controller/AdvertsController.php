<?php

namespace App\Controller;

use App\Entity\Adverts;
use App\Repository\AdvertsRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class AdvertsController extends AbstractController
{
    private EntityManagerInterface $entityManager;
    private Security $security;

    public function __construct(EntityManagerInterface $entityManager, Security $security)
    {
        $this->entityManager = $entityManager;
        $this->security = $security;
    }
    #[Route('/advert', name: 'create_advert', methods: ['POST'])]
    public function addAdvert(Request $request, ValidatorInterface $validator, SerializerInterface $serializer): JsonResponse
    {
        // Get user connected
        $user = $this->security->getUser();
        // Deserialize request content
        $advert = $serializer->deserialize($request->getContent(), Adverts::class, 'json');

        // Validate data and check error
        $errors = $validator->validate($advert);
        if (count($errors) > 0) {
            return new JsonResponse($serializer->serialize($errors, 'json'), JsonResponse::HTTP_BAD_REQUEST, [], true);
        }

        // Add user to advert
        $advert->setUser($user);
        // Save in database new Advert object
        $this->entityManager->persist($advert);
        $this->entityManager->flush();

        // Return new advert created with ID
        return new JsonResponse(['status' => 'Advert created', 'id' => $advert->getId()], Response::HTTP_CREATED);
    }

    #[Route('/advert/{id}', name: 'patch_advert', methods: ['PATCH'])]
    public function updateAdvert(int $id, Request $request, AdvertsRepository $advertsRepository, SerializerInterface $serializer): JsonResponse
    {
        // Find the Advert by ID
        $advert = $advertsRepository->find($id);

        // Check if Advert doesn't exist
        if (!$advert) {
            return new JsonResponse(['error' => 'Advert not found'], Response::HTTP_NOT_FOUND, [], true);
        }

        // Check if User connected is advert's owner
        if ($advert->getUser() !== $this->getUser()) {
            return new JsonResponse(['error' => 'Access denied, This advert not yours'], Response::HTTP_FORBIDDEN);
        }


        // Deserialize data into Advert
        $serializer->deserialize(
            $request->getContent(),
            Adverts::class,
            'json',
            ['object_to_populate' => $advert]
        );

        // Save in database
        $this->entityManager->flush();

        // Return response
        return new JsonResponse(['message' => 'Advert updated successfully'], Response::HTTP_OK);
    }
    #[Route('/advert', name: 'get_adverts', methods: ['GET'])]
    public function getAdvertList(AdvertsRepository $advertsRepository, SerializerInterface $serializer): JsonResponse
    {
        $advertsList = $advertsRepository->findAll();

        $jsonAdvertsList = $serializer->serialize($advertsList,'json', ['groups' => 'user']);
        return new JsonResponse($jsonAdvertsList, Response::HTTP_OK, [], true);
    }

    #[Route('/advert/{id}', name: 'get_advert', requirements: ['id' => '\d+'], methods: ['GET'])]
    public function getAdvert(int $id, AdvertsRepository $advertsRepository, SerializerInterface $serializer): JsonResponse
    {
        // Retrieve the advert from the database using the ID
        $advert = $advertsRepository->find($id);

        // If the advert exist return advert's information
        if ($advert) {
            $jsonAdvert = $serializer->serialize($advert,'json', ['groups' => 'user']);
            return new JsonResponse($jsonAdvert, Response::HTTP_OK, [], true);
        }

        // If the advert doesn't exist, return a 404 error
        return new JsonResponse(null, Response::HTTP_NOT_FOUND);
    }

    #[Route('/advert/search', name: 'advert_search', methods: ['GET'])]
    public function searchAdverts(Request $request, SerializerInterface $serializer): JsonResponse
    {
        // Get request's parameters
        $title = $request->query->get('title');
        $priceMin = $request->query->get('price_min');
        $priceMax = $request->query->get('price_max');

        // Create request to filter
        $queryBuilder = $this->entityManager->getRepository(Adverts::class)->createQueryBuilder('a');

        // Filter by title
        if ($title) {
            $queryBuilder->andWhere('a.title LIKE :title')
                ->setParameter('title', '%' . $title . '%');
        }

        // Filter by minimum price
        if ($priceMin) {
            $queryBuilder->andWhere('a.price >= :priceMin')
                ->setParameter('priceMin', $priceMin);
        }

        // Filter by maximum price
        if ($priceMax) {
            $queryBuilder->andWhere('a.price <= :priceMax')
                ->setParameter('priceMax', $priceMax);
        }

        // Get results from request
        $adverts = $queryBuilder->getQuery()->getResult();

        $jsonAdverts = $serializer->serialize($adverts,'json', ['groups' => 'user']);
        return new JsonResponse($jsonAdverts, Response::HTTP_OK, [], true);
    }

    #[Route('/advert/{id}', name: 'delete_advert', methods: ['DELETE'])]
    public function deleteAdvert(int $id, AdvertsRepository $advertsRepository): JsonResponse
    {

        // Find the Advert by ID
        $advert = $advertsRepository->find($id);

        // Check if Advert doesn't exist
        if (!$advert) {
            return new JsonResponse(['error' => 'Advert not found'], Response::HTTP_NOT_FOUND);
        }

        // Check if User connected is advert's owner
        if ($advert->getUser() !== $this->getUser()) {
            return new JsonResponse(['error' => 'Access denied, This advert not yours'], Response::HTTP_FORBIDDEN);
        }

        // Delete Advert
        $this->entityManager->remove($advert);
        $this->entityManager->flush();

        // Return Response
        return new JsonResponse(['message' => 'Advert deleted successfully'], Response::HTTP_NO_CONTENT);
    }
}
