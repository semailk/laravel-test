<?php

namespace App\Http\Resources;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;

class FriendshipMutualCollection extends ResourceCollection
{
    /**
     * Transform the resource collection into an array.
     *
     * @return array<int|string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'data' => $this->collection
                ->map(function (User $friend) {
                    if ($friend->friends->isNotEmpty()) {
                        return $friend->friends;
                    }
                })
                ->filter()
        ];
    }
}
