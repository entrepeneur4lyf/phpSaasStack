<?php

declare(strict_types=1);

namespace Src\Controllers;

use Src\Interfaces\AssetServiceInterface;
use Swoole\Http\Request;
use Swoole\Http\Response;
use Src\Exceptions\AccessDeniedException;
use Src\Exceptions\AssetNotFoundException;
use Src\Exceptions\ValidationException;

class AssetController extends BaseController
{
    public function __construct(
        private readonly AssetServiceInterface $assetService
    ) {}

    public function upload(Request $request, Response $response): void
    {
        try {
            $userId = $request->user->id; // Assuming user is authenticated
            $fileData = $request->files['asset'];
            $assetId = $this->assetService->uploadAsset($fileData, $userId);
            $this->jsonResponse($response, ['success' => true, 'asset_id' => $assetId]);
        } catch (\Exception $e) {
            $this->jsonResponse($response, ['success' => false, 'message' => $e->getMessage()], 400);
        }
    }

    public function download(Request $request, Response $response, array $args): void
    {
        try {
            $assetId = (int) $args['id'];
            $userId = $request->user->id; // Assuming user is authenticated
            
            $downloadUrl = $this->assetService->getAssetDownloadUrl($assetId, $userId);
            $this->jsonResponse($response, ['success' => true, 'download_url' => $downloadUrl]);
        } catch (AccessDeniedException $e) {
            $this->jsonResponse($response, ['success' => false, 'message' => $e->getMessage()], 403);
        } catch (AssetNotFoundException $e) {
            $this->jsonResponse($response, ['success' => false, 'message' => $e->getMessage()], 404);
        } catch (\Exception $e) {
            $this->jsonResponse($response, ['success' => false, 'message' => 'An error occurred'], 500);
        }
    }

    public function delete(Request $request, Response $response, array $args): void
    {
        try {
            $assetId = (int) $args['id'];
            $userId = $request->user->id; // Assuming user is authenticated
            
            if (!$this->assetService->validateAssetAccess($assetId, $userId)) {
                throw new AccessDeniedException('Access denied');
            }

            $result = $this->assetService->deleteAsset($assetId);
            $this->jsonResponse($response, ['success' => $result]);
        } catch (AccessDeniedException $e) {
            $this->jsonResponse($response, ['success' => false, 'message' => $e->getMessage()], 403);
        } catch (\Exception $e) {
            $this->jsonResponse($response, ['success' => false, 'message' => 'An error occurred'], 500);
        }
    }

    public function getDownloadHistory(Request $request, Response $response, array $args): void
    {
        try {
            $assetId = (int) $args['id'];
            $userId = $request->user->id; // Assuming user is authenticated

            if (!$this->assetService->validateAssetAccess($assetId, $userId)) {
                throw new AccessDeniedException('Access denied');
            }

            $history = $this->assetService->getDownloadHistory($assetId);
            $this->jsonResponse($response, ['success' => true, 'history' => $history]);
        } catch (AccessDeniedException $e) {
            $this->jsonResponse($response, ['success' => false, 'message' => $e->getMessage()], 403);
        } catch (\Exception $e) {
            $this->jsonResponse($response, ['success' => false, 'message' => 'An error occurred'], 500);
        }
    }

    public function cropImage(Request $request, Response $response): void
    {
        try {
            $assetId = (int)$request->post['asset_id'];
            $cropData = [
                'x' => $request->post['x'],
                'y' => $request->post['y'],
                'width' => $request->post['width'],
                'height' => $request->post['height'],
            ];

            $result = $this->assetService->cropImage($assetId, $cropData);

            if ($result) {
                $this->jsonResponse($response, ['success' => true, 'message' => 'Image cropped successfully']);
            } else {
                $this->jsonResponse($response, ['success' => false, 'message' => 'Failed to crop image'], 400);
            }
        } catch (ValidationException $e) {
            $this->jsonResponse($response, ['success' => false, 'message' => $e->getMessage()], 400);
        } catch (\Exception $e) {
            $this->jsonResponse($response, ['success' => false, 'message' => 'An error occurred'], 500);
        }
    }
}