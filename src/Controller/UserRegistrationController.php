<?php

namespace App\Controller;

use App\Entity\User;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Doctrine\ORM\Exception\ORMException;
use Doctrine\Persistence\ManagerRegistry;
use Exception;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route('/api', name: 'api_')]
class UserRegistrationController extends AbstractController
{
    public function __construct(
        private readonly ValidatorInterface $validator,
        private readonly UserPasswordHasherInterface $passwordHasher,
        private readonly LoggerInterface $logger,
    ) {
    }

    #[Route('/registration', name: 'registration', methods: ['POST'])]
    public function index(ManagerRegistry $doctrine, Request $request): JsonResponse
    {
        $em = $doctrine->getManager();
        $data = json_decode($request->getContent(), true);

        // Validamos sin en la petición viene los valores requeridos
        if (!isset($data['email'], $data['password'])) {
            return $this->json([
                'message' => 'email and password are required',
            ], JsonResponse::HTTP_BAD_REQUEST);
        }

        // Validamos que el email sea valido
        $email = filter_var($data['email'], FILTER_VALIDATE_EMAIL);
        if (!$email) {
            return $this->json([
                'message' => 'invalid email address',
            ], JsonResponse::HTTP_BAD_REQUEST);
        }

        // Validamos que la contraseña tenga al menos 6 caracteres de longitud
        $password = $data['password'];
        if (strlen($password) < 6) {
            return $this->json([
                'message' => 'password must be at least 6 characters long',
            ], JsonResponse::HTTP_BAD_REQUEST);
        }

        $user = new User();
        $user->setUsername($email);
        $user->setEmail($email);
        $user->setPassword(
        // Encriptamos la contraseña del usuario mediante el paquete PasswordHasher
            $this->passwordHasher->hashPassword($user, $password)
        );

        // Validamos la entidad User con el paquete Validator
        $errors = $this->validator->validate($user);
        if (count($errors) > 0) {
            return $this->json([
                'message' => 'Validation user failed',
                'errors' => (string)$errors,
            ], JsonResponse::HTTP_BAD_REQUEST);
        }

        // Persistimos la entidad User y capturamos la excepción en caso de que se produzca
        try {
            $em->persist($user);
            $em->flush();
        } catch (UniqueConstraintViolationException $e) {
            $this->logger->error('User registration failed: '.$e->getMessage(), ['exception' => $e]);

            return $this->json([
                'message' => 'This email is already registered. Please use a different email address.',
            ], JsonResponse::HTTP_CONFLICT);

        } catch (ORMException $e) {
            $this->logger->error('User registration failed: '.$e->getMessage(), ['exception' => $e]);

            return $this->json([
                'message' => 'An error occurred while saving the user. Please try again later.',
            ]);
        } catch (Exception $e) {
            $this->logger->error('An unexpected error occurred: '.$e->getMessage(), ['exception' => $e]);

            return $this->json([
                'message' => 'An unexpected error occurred. Please try again later.',
                'exception' => $e->getMessage(),
            ], JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        }

        // Retornamos la respuesta con el mensaje de éxito
        return new JsonResponse(['message' => 'User created successfully'], JsonResponse::HTTP_CREATED);

    }
}
