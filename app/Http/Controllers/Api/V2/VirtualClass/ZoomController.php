<?php

namespace App\Http\Controllers\Api\V2\VirtualClass;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Repositories\Interfaces\ZoomRepositoryInterface;

class ZoomController extends Controller
{
    private $zoomRepository;

    public function __construct(ZoomRepositoryInterface $zoomRepository)
    {
        $this->zoomRepository = $zoomRepository;
    }

    public function configure(Request $request): object
    {
        return $this->zoomRepository->configure($request);
    }

    public function settings(Request $request): object
    {
        return $this->zoomRepository->settings($request);
    }

    public function getConfigSetting(): object
    {
        return $this->zoomRepository->getConfigSetting();
    }

    public function approvelTypes(): object
    {
        return $this->zoomRepository->approvelTypes();
    }

    public function autoRecordings(): object
    {
        return $this->zoomRepository->autoRecordings();
    }

    public function audios(): object
    {
        return $this->zoomRepository->audios();
    }

    public function packages(): object
    {
        return $this->zoomRepository->packages();
    }
}
