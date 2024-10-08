<?php

declare(strict_types=1);

namespace Src\Controllers;

use Src\Interfaces\FollowServiceInterface;
use Swoole\Http\Request;
use Swoole\Http\Response;
use Src\Exceptions\ValidationException;

class FollowController extends BaseController
{
    public function __construct(
        private readonly FollowServiceInterface $followService
    ) {
    }

    public function follow(Request $request, Response $response, array $args): void
    {
        try {
            $followerId = $request->user->id; // Assuming user is set by middleware
            $followedId = (int) $args['id'];

            $result = $this->followService->follow($followerId, $followedId);

            if ($result) {
                $this->jsonResponse($response, ['success' => true, 'message' => 'Successfully followed user']);
            } else {
                $this->jsonResponse($response, ['success' => false, 'message' => 'Failed to follow user'], 400);
            }
        } catch (ValidationException $e) {
            $this->jsonResponse($response, ['success' => false, 'message' => $e->getMessage()], 400);
        } catch (\Exception $e) {
            $this->jsonResponse($response, ['success' => false, 'message' => 'An error occurred'], 500);
        }
    }

    public function unfollow(Request $request, Response $response, array $args): void
    {
        try {
            $followerId = $request->user->id; // Assuming user is set by middleware
            $followedId = (int) $args['id'];

            $result = $this->followService->unfollow($followerId, $followedId);

            if ($result) {
                $this->jsonResponse($response, ['success' => true, 'message' => 'Successfully unfollowed user']);
            } else {
                $this->jsonResponse($response, ['success' => false, 'message' => 'Failed to unfollow user'], 400);
            }
        } catch (\Exception $e) {
            $this->jsonResponse($response, ['success' => false, 'message' => 'An error occurred'], 500);
        }
    }

    public function getFollowers(Request $request, Response $response, array $args): void
    {
        try {
            $userId = (int) $args['id'];
            $followers = $this->followService->getFollowers($userId);
            $this->jsonResponse($response, ['success' => true, 'followers' => $followers]);
        } catch (\Exception $e) {
            $this->jsonResponse($response, ['success' => false, 'message' => 'An error occurred'], 500);
        }
    }

    public function getFollowing(Request $request, Response $response, array $args): void
    {
        try {
            $userId = (int) $args['id'];
            $following = $this->followService->getFollowing($userId);
            $this->jsonResponse($response, ['success' => true, 'following' => $following]);
        } catch (\Exception $e) {
            $this->jsonResponse($response, ['success' => false, 'message' => 'An error occurred'], 500);
        }
    }

    public function isFollowing(Request $request, Response $response, array $args): void
    {
        try {
            $followerId = $request->user->id; // Assuming user is set by middleware
            $followedId = (int) $args['id'];

            $isFollowing = $this->followService->isFollowing($followerId, $followedId);
            $this->jsonResponse($response, ['success' => true, 'is_following' => $isFollowing]);
        } catch (\Exception $e) {
            $this->jsonResponse($response, ['success' => false, 'message' => 'An error occurred'], 500);
        }
    }

    public function getFollowCounts(Request $request, Response $response, array $args): void
    {
        try {
            $userId = (int) $args['id'];
            $followersCount = $this->followService->getFollowersCount($userId);
            $followingCount = $this->followService->getFollowingCount($userId);

            $this->jsonResponse($response, [
                'success' => true,
                'followers_count' => $followersCount,
                'following_count' => $followingCount
            ]);
        } catch (\Exception $e) {
            $this->jsonResponse($response, ['success' => false, 'message' => 'An error occurred'], 500);
        }
    }
}
