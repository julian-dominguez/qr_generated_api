<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\User;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Doctrine\ORM\Exception\ORMException;
use Doctrine\Persistence\ManagerRegistry;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route('/api', name: 'api_')]
class UserRegistrationController extends AbstractController
{
    public function __construct(
        private readonly ValidatorInterface $validator,
        private readonly UserPasswordHasherInterface $passwordHasher,
    ) {
    }

    /**
     * Gestiona el registro de un nuevo usuario.
     *
     * @param ManagerRegistry $doctrine La instancia de Doctrine ManagerRegistry.
     * @param Request $request El objeto de solicitud HTTP.
     * @return JsonResponse La respuesta JSON que contiene el mensaje de éxito.
     * @throws HttpException Si faltan los datos requeridos o no son válidos.
     */
    #[Route('/registration', name: 'registration', methods: ['POST'])]
    public function index(ManagerRegistry $doctrine, Request $request): JsonResponse
    {
        $em = $doctrine->getManager();
        $data = json_decode($request->getContent(), true);

        // Validamos sin en la petición viene los valores requeridos
        if (!isset($data['email'], $data['password'])) {
            throw new HttpException(
                statusCode: JsonResponse::HTTP_BAD_REQUEST,
                message: 'email and password are required',
                code: JsonResponse::HTTP_BAD_REQUEST

            );
        }

        // Validamos que el email sea valido
        $email = filter_var($data['email'], FILTER_VALIDATE_EMAIL);
        if (!$email) {
            throw new HttpException(
                statusCode: JsonResponse::HTTP_BAD_REQUEST,
                message: 'invalid email address',
                code: JsonResponse::HTTP_BAD_REQUEST
            );
        }

        // Validamos que la contraseña tenga al menos 6 caracteres de longitud
        $password = $data['password'];
        if (strlen($password) < 6) {
            throw new HttpException(
                statusCode: JsonResponse::HTTP_BAD_REQUEST,
                message: 'password must be at least 6 characters long',
                code: JsonResponse::HTTP_BAD_REQUEST
            );
        }

        // Creamos una nueva instancia de User
        $user = new User();
        $user->setUsername($email);
        $user->setEmail($email);
        $user->setRoles(['ROLE_USER']); // Asignamos el rol de user
        $user->setPassword(

        // Encriptamos la contraseña del usuario mediante el paquete PasswordHasher
            $this->passwordHasher->hashPassword($user, $password)
        );

        // Validamos la entidad User con el paquete Validator
        $errors = $this->validator->validate($user);
        if (count($errors) > 0) {
            throw new HttpException(
                statusCode: JsonResponse::HTTP_BAD_REQUEST,
                message: 'Validation user failed.',
                code: JsonResponse::HTTP_BAD_REQUEST,
            );
        }

        // Persistimos la entidad User y capturamos la excepción en caso de que se produzca
        try {
            $em->persist($user);
            $em->flush();
        } catch (UniqueConstraintViolationException $e) {
            throw new HttpException(
                statusCode: JsonResponse::HTTP_CONFLICT,
                message: 'This email is already registered. Please use a different email address.',
                code: JsonResponse::HTTP_CONFLICT
            );
        } catch (ORMException $e) {
            throw new HttpException(
                statusCode: JsonResponse::HTTP_INTERNAL_SERVER_ERROR,
                message: 'An error occurred while saving the user. Please try again later.',
                code: JsonResponse::HTTP_INTERNAL_SERVER_ERROR
            );

        } catch (Exception $e) {
            throw new HttpException(
                statusCode: JsonResponse::HTTP_INTERNAL_SERVER_ERROR,
                message: 'An unexpected error occurred. Please try again later.',
                code: JsonResponse::HTTP_INTERNAL_SERVER_ERROR
            );
        }

        // Retornamos la respuesta con el mensaje de éxito
        return new JsonResponse(
            data: ['message' => "the user {$data['email']} as created successfully"],
            status: JsonResponse::HTTP_CREATED,
        );

    }
}
