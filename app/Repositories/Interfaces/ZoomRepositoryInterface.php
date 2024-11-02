<?php

namespace App\Repositories\Interfaces;

interface ZoomRepositoryInterface
{
    public function configure(object $request): object;
    public function settings(object $request): object;
    public function getConfigSetting(): object;
    public function approvelTypes(): object;
    public function autoRecordings(): object;
    public function audios(): object;
    public function packages(): object;
}
