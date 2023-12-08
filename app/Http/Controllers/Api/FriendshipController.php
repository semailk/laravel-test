<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\FriendshipCollection;
use App\Http\Resources\FriendshipMutualCollection;
use App\Http\Resources\FriendshipResource;
use App\Models\Friendship;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Tymon\JWTAuth\Facades\JWTAuth;

/**
 * @OA\Tag(
 *     name="Friendship",
 *     description="API Endpoints for managing friendships",
 * )
 */
class FriendshipController extends Controller
{
    /**
     * @OA\Post(
     *     path="/api/friendship/add/{user}",
     *     tags={"Friendship"},
     *     summary="Add a friend",
     *     description="Send a friend request to another user.",
     *     operationId="addFriend",
     *     @OA\Parameter(
     *         name="user",
     *         in="path",
     *         required=true,
     *         description="ID of the user to add as a friend",
     *         @OA\Schema(type="integer"),
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Friendship request sent successfully",
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Friendship already exists",
     *         @OA\JsonContent(
     *             @OA\Property(property="error", type="string", example="Friendship already exists"),
     *         ),
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized",
     *         @OA\JsonContent(
     *             @OA\Property(property="error", type="string", example="Unauthorized"),
     *         ),
     *     ),
     * )
     *
     * @param User $user
     * @return FriendshipResource|JsonResponse
     */
    public function addFriend(User $user): FriendshipResource|JsonResponse
    {
        $mainUser = JWTAuth::parseToken()->authenticate();
        $hasFriend = Friendship::query()
            ->where('friend_id', $user->id)
            ->where('user_id', '=', $mainUser->id)
            ->get()
            ->isNotEmpty();

        if (!$hasFriend) {
            $friendship = Friendship::create([
                'user_id' => $mainUser->id,
                'friend_id' => $user->id,
                'status' => 'pending',
            ]);

            return new FriendshipResource($friendship);
        } else {
            return response()->json(['error' => 'Friendship already exists'], 400);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/friendship/list",
     *     tags={"Friendship"},
     *     summary="Get friends list",
     *     description="Retrieve the list of friends for the authenticated user.",
     *     operationId="getFriendsList",
     *     @OA\Response(
     *         response=200,
     *         description="Friends list retrieved successfully",
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized",
     *         @OA\JsonContent(
     *             @OA\Property(property="error", type="string", example="Unauthorized"),
     *         ),
     *     ),
     * )
     *
     * @return JsonResponse
     */
    public function getFriendsList(): JsonResponse
    {
        $user = JWTAuth::parseToken()->authenticate();

        $friendsList = Friendship::query()
            ->where('user_id', '=', $user->id)
            ->get();

        return response()->json(
            FriendshipCollection::make($friendsList)
        );
    }

    /**
     * @OA\Get(
     *     path="/api/friendship/mutual",
     *     tags={"Friendship"},
     *     summary="Get mutual friends",
     *     description="Retrieve mutual friends for the authenticated user.",
     *     operationId="getMutualFriends",
     *     @OA\Response(
     *         response=200,
     *         description="Mutual friends retrieved successfully",
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized",
     *         @OA\JsonContent(
     *             @OA\Property(property="error", type="string", example="Unauthorized"),
     *         ),
     *     ),
     * )
     *
     * @return JsonResponse
     */
    public function getMutualFriends(): JsonResponse
    {
        $user = JWTAuth::parseToken()->authenticate();

        $friendsList = Friendship::query()
            ->where('user_id', '=', $user->id)
            ->select('friend_id')
            ->get()
            ->pluck('friend_id')
            ->toArray();

        $mutualFriends = User::query()->with('friends')->whereIn('id', $friendsList)->get();

        return response()->json(
            new FriendshipMutualCollection($mutualFriends)
        );
    }
}
