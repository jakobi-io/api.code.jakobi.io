<?php
namespace App\Http\Controllers;

use App\Http\Managers\AuthenticationManager;
use App\Http\Managers\LanguageManager;
use App\Http\Managers\PasteManager;
use App\Http\Response\Response;
use App\Models\Paste;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Class PasteController
 *
 * @licence Copyright &copy; 2020 jakobi.io
 * @package App\Http\Controllers
 * @author Lukas Jakobi <lukas@jakobi.io>
 * @since 29.10.2020
 */
class PasteController
{

    private PasteManager $pasteManager;

    public function __construct()
    {
        $this->pasteManager = new PasteManager();
    }

    /**
     * Get a list of all the pastes a user owns
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function getPasteList(Request $request): JsonResponse
    {
        $response = new Response();

        if (!$request->hasHeader("Authorization")) {
            return $response->setStatusCode(401)->error("Not Authenticated");
        }

        $bearerToken = explode(" ", $request->header("Authorization"))[1];
        $user = AuthenticationManager::getUserByOAuthToken($bearerToken);

        if ($user === null) {
            return $response->setStatusCode(401)->error("Not Authenticated");
        }

        $pasteList = $this->pasteManager->getPasteList($user);

        return $response->setResult($pasteList)->build();
    }

    /**
     * Create a new Paste
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function createPaste(Request $request): JsonResponse
    {
        $response = new Response();

        try {
            $languageManager = new LanguageManager();

            // Paste Data Values
            $description = $request->get("description");
            $code = $request->get("code");
            $language = $request->has("language") ? $languageManager->getLanguageBySlug($request->get("language")) : null;
            $deleteAfter = $request->has("deleteAfter") ? $request->get("deleteAfter") : null;
            $password = null;
            $deletedAt = null;

            if ($code === null) {
                return $response->setStatusCode(400)->buildError("At least one parameter is missing");
            }

            switch ($deleteAfter) {
                case "hour":
                    $deletedAt = Carbon::now()->addHour();
                    break;
                case "day":
                    $deletedAt = Carbon::now()->addDay();
                    break;
                case "week":
                    $deletedAt = Carbon::now()->addWeek();
                    break;
                case "month":
                    $deletedAt = Carbon::now()->addMonth();
                    break;
                case "year":
                    $deletedAt = Carbon::now()->addYear();
                    break;
                default:
                    $deletedAt = null;
                    break;
            }

            // Create Paste
            $paste = $this->pasteManager->createPaste(
                $description,
                $code,
                null,
                $language->id ?? null,
                $password,
                $deletedAt
            );
        } catch (Exception $exception) {
            return $response->setStatusCode(500)->buildError("Internal Server Error");
        }

        if ($paste === null) {
            return $response->setStatusCode(500)->buildError("Internal Server Error");
        }

        return $response->setStatusCode(200)->setResult($paste)->build();
    }

    /**
     * Get a list of all the pastes a user owns
     *
     * @param $token
     * @return JsonResponse
     */
    public function getPaste($token): JsonResponse
    {
        $response = new Response();
        $paste = $this->pasteManager->getPasteByToken($token);

        if ($paste === null || $paste->deleted) {
            return $response->setStatusCode(404)->buildError("Not Found");
        }

        return $response->setResult($paste)->build();
    }

    /**
     * Get a list of all the pastes a user owns
     *
     * @param $token
     * @param Request $request
     * @return JsonResponse
     */
    public function deletePaste($token, Request $request): JsonResponse
    {
        $response = new Response();
        $paste = Paste::where("token", "=", $token)->first();

        if ($paste === null) {
            return $response->setStatusCode(404)->buildError("Not Found");
        }

        if ($paste->userId !== null) {
            if ($request->header("Authorization") === null) {
                return $response->setStatusCode(401)->buildError("Unauthorized");
            }

            $bearerToken = explode(" ", $request->header("Authorization"))[1];
            $user = AuthenticationManager::getUserByOAuthToken($bearerToken);

            if ($user === null || (string) $user->id !== (string) $paste->userId) {
                return $response->setStatusCode(401)->buildError("Unauthorized");
            }
        }

        $this->pasteManager->deletePaste($token);

        return $response->setStatusCode(200)->setResult(null)->build();
    }
}
