<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\LoginRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Laravel\Sanctum\TransientToken;

class AuthController extends Controller
{
    /**
     * Авторизовать администратора и выдать Sanctum Bearer token.
     *
     * Принимает email и password, проверяет существование пользователя и наличие роли
     * `admin`, после чего возвращает bearer token и данные текущего пользователя.
     *
     * @throws ValidationException
     */
    public function login(LoginRequest $request): JsonResponse
    {
        $credentials = $request->validated();

        /** @var User|null $user */
        $user = User::query()->where('email', $credentials['email'])->first();

        if ($user === null || ! Hash::check($credentials['password'], $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        if (! $user->isAdmin()) {
            throw ValidationException::withMessages([
                'email' => ['The provided user is not allowed to access the admin API.'],
            ]);
        }

        $token = $user->createToken('admin-api')->plainTextToken;

        return response()->json([
            'token' => $token,
            'user' => UserResource::make($user),
        ]);
    }

    /**
     * Отозвать текущий Sanctum token для авторизованного администратора.
     *
     * Этот endpoint завершает только текущую API-сессию и удаляет токен, с которым был
     * выполнен запрос.
     */
    public function logout(Request $request): JsonResponse
    {
        $token = $request->user()?->currentAccessToken();

        if ($token !== null && ! $token instanceof TransientToken) {
            $token->delete();
        }

        return response()->json([
            'message' => 'Logged out successfully.',
        ]);
    }

    /**
     * Вернуть текущего авторизованного администратора.
     *
     * Удобно для восстановления admin-сессии на frontend после логина или перезагрузки
     * страницы.
     */
    public function me(Request $request): UserResource
    {
        return UserResource::make($request->user());
    }
}
