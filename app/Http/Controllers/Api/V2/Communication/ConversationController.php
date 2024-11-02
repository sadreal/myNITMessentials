<?php

namespace App\Http\Controllers\Api\V2\Communication;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Repositories\Interfaces\ConversationRepositoryInterface;

class ConversationController extends Controller
{
    private $converstionRepository;

    public function __construct(ConversationRepositoryInterface $converstionRepository)
    {
        $this->converstionRepository = $converstionRepository;
    }
    public function list(Request $request): object
    {
        return $this->converstionRepository->list($request);
    }

    public function contactList(Request $request): object
    {
        return $this->converstionRepository->contactList($request);
    }
    public function messages(Request $request): object
    {
        return $this->converstionRepository->messages($request);
    }

    public function storeMessage(Request $request): object
    {
        return $this->converstionRepository->storeMessage($request);
    }
}
