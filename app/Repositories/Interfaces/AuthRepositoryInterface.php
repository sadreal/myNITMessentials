<?php

namespace App\Repositories\Interfaces;

interface AuthRepositoryInterface
{
    public function signup(object $request): array;
    public function verifyEmail(object $request): array;
    public function login(object $request): array;
    public function socialLogin(object $request): object;
    public function logout(object $request): bool;
    public function user(object $request): object;
    public function changePassword(object $request): array;
    // public function accountDelete(object $request): object;
    public function setFcmToken(object $request): object;
    // public function getLang(object $request): object;
    // public function setLang(object $request): object;
    public function sendOtp(object $request): void;
    public function resetPasswordWithOtp(object $request): void;
    public function logOutDevice(object $request): void;
    public function userDetail(): object;
    public function deleteSelfAccount(object $request): array;
}
