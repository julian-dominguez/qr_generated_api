<?php

namespace App\Controller;

use App\Entity\User;
use Doctrine\Persistence\ManagerRegistry;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasher;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route('/api', name: 'api_')]
class UserRegistrationController extends AbstractController
{
    public function __construct(
        protected ValidatorInterface $validator,
        protected userPasswordHasher $passwordHasher
    ) {
    }

    #[Route('/v1/user/registration', name: 'v1_user_registration', methods: ['POST'])]
    public function index(ManagerRegistry $doctrine, Request $request): JsonResponse
    {
        $em = $doctrine->getManager();
        $data = json_decode($request->getContent(), true);

        // Validamos sin en la petición viene los valores requeridos
        if (!isset($data['email'], $data['password'])) {
            return $this->json([
                'message' => 'email and password are required',
            ], Response::HTTP_BAD_REQUEST);
        }

        // Validamos que el email sea valido
        $email = filter_var($data['email'], FILTER_VALIDATE_EMAIL);
        if (!$email) {
            return $this->json([
                'message' => 'invalid email address',
            ], Response::HTTP_BAD_REQUEST);
        }

        // Validamos que la contraseña tenga al menos 6 caracteres de longitud
        $password = $data['password'];
        if (strlen($password) < 6) {
            return $this->json([
                'message' => 'password must be at least 6 characters long',
            ], Response::HTTP_BAD_REQUEST);
        }

        $user = new User();
        $user->setUsername(strstr($email, '@', true));
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
            ], Response::HTTP_BAD_REQUEST);
        }

        // Persistimos la entidad User y capturamos la excepción en caso de que se produzca
        try {
            $em->persist($user);
            $em->flush();
        } catch (Exception $e) {
            return $this->json([
                'message' => 'An error occurred while creating the user',
                'exception' => $e->getMessage(),
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        // Retornamos la respuesta con el mensaje de éxito
        return $this->json([
            'message' => "the user {$user->getUsername()} as created successfully",
        ], Response::HTTP_CREATED);
    }
}
