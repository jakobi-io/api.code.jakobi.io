<?php
namespace App\Http\Controllers;

use App\Http\Managers\AuthenticationManager;
use App\Http\Managers\CommentManager;
use App\Http\Managers\CryptoManager;
use App\Http\Managers\LanguageManager;
use App\Http\Managers\PasteManager;
use App\Http\Response\Response;
use App\Models\Paste;
use Carbon\Carbon;
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
    private CommentManager $commentManager;

    public function __construct()
    {
        $this->pasteManager = new PasteManager();
        $this->commentManager = new CommentManager();
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

        if ($request->header("Authorization") === null) {
            return $response->setStatusCode(401)->buildError("Unauthorized");
        }

        $bearerToken = explode(" ", $request->header("Authorization"))[1];
        $user = AuthenticationManager::getUserByOAuthToken($bearerToken);

        if ($user === null) {
            return $response->setStatusCode(401)->buildError("Unauthorized");
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
            $bearerToken = $request->header("Authorization") !== null ? explode(" ", $request->header("Authorization"))[1] : null;
            $user = $bearerToken === null ? null : AuthenticationManager::getUserByOAuthToken($bearerToken);

            $languageManager = new LanguageManager();

            // Paste Data Values
            $description = $request->get("description");
            $code = $request->get("code");
            $language = $request->has("language") ? $languageManager->getLanguageBySlug($request->get("language")) : null;
            $languageId = $language === null ? null : $language->id;
            $deleteAfter = $request->has("deleteAfter") ? $request->get("deleteAfter") : null;
            $password = null;
            $deletedAt = null;

            if ($code === null) {
                return $response->setStatusCode(400)->buildError("At least one parameter is missing");
            }

            if ($deleteAfter !== null) {
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
            }

            // Create Paste
            $paste = $this->pasteManager->createPaste($description, $code, $user, $languageId, $password, $deletedAt);
        } catch (\Exception $exception) {
            return $response->setStatusCode(500)->buildError("Internal Server Error");
        }

        return $response->setStatusCode(200)->setResult(["token" => $paste->token])->build();
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

    /**
     * Get a list of all the pastes a user owns
     *
     * @param $token
     * @param Request $request
     * @return JsonResponse
     */
    public function getPasteCommentList(Request $request, $token): JsonResponse
    {
        $response = new Response();

        if ($request->header("Authorization") === null) {
            return $response->setStatusCode(401)->buildError("Unauthorized");
        }

        $bearerToken = explode(" ", $request->header("Authorization"))[1];
        $user = AuthenticationManager::getUserByOAuthToken($bearerToken);

        $paste = $this->pasteManager->getPasteByToken($token);

        if ($paste === null) {
            return $response->setStatusCode(404)->buildError("Not Found");
        }

        if ($user !== null && $user->id !== $paste->userId) {
            return $response->setStatusCode(401)->buildError("Unauthorized");
        }

        $commentList = $this->commentManager->getPasteComments($token);

        return $response->setStatusCode(200)->setResult($commentList)->build();
    }

    public function createPasteComment(Request $request, $token): JsonResponse
    {
        $response = new Response();

        try {
            $paste = $this->pasteManager->getPasteByToken($token);

            if ($paste === null) {
                return $response->setStatusCode(404)->buildError("Not Found");
            }

            $bearerToken = $request->header("Authorization") !== null ? explode(" ", $request->header("Authorization"))[1] : null;
            $user = $bearerToken === null ? null : AuthenticationManager::getUserByOAuthToken($bearerToken);

            // Comment Data Values
            $message = $request->get("message");

            if ($message === null) {
                return $response->setStatusCode(400)->buildError("At least one parameter is missing");
            }

            // Create Comment
            $comment = $this->commentManager->createComment($paste, $user, $message);

            return $response->setStatusCode(200)->setResult($comment)->build();
        } catch (\Exception $exception) {
            return $response->setStatusCode(500)->buildError("Internal Server Error");
        }
    }

    /**
     * Get a list of all the pastes a user owns
     *
     * @param Request $request
     * @param $token
     * @param $comment
     * @return JsonResponse
     */
    public function getPasteComment(Request $request, $token, $comment): JsonResponse
    {
        $response = new Response();

        if ($request->header("Authorization") === null) {
            return $response->setStatusCode(401)->buildError("Unauthorized");
        }

        $bearerToken = explode(" ", $request->header("Authorization"))[1];
        $user = AuthenticationManager::getUserByOAuthToken($bearerToken);

        $paste = $this->pasteManager->getPasteByToken($token);

        if ($paste === null) {
            return $response->setStatusCode(404)->buildError("Not Found");
        }

        if ($user !== null && $user->id !== $paste->userId) {
            return $response->setStatusCode(401)->buildError("Unauthorized");
        }

        $comment = $this->commentManager->getPasteComment($token, $comment);

        if ($comment === null) {
            return $response->setStatusCode(404)->buildError("Not Found");
        }

        return $response->setStatusCode(200)->setResult($comment)->build();
    }

    /**
     * delete a paste comment
     * this is an admin only task
     *
     * @param Request $request
     * @param string $token Paste Token
     * @param string $comment Comment ID
     *
     * @return JsonResponse
     */
    public function deletePasteComment(Request $request, string $token, string $comment): JsonResponse
    {
        $response = new Response();

        if ($request->header("Authorization") === null) {
            return $response->setStatusCode(401)->buildError("Unauthorized");
        }

        $bearerToken = explode(" ", $request->header("Authorization"))[1];
        $user = AuthenticationManager::getUserByOAuthToken($bearerToken);

        $paste = $this->pasteManager->getPasteByToken($token);

        if ($paste === null) {
            return $response->setStatusCode(404)->buildError("Not Found");
        }

        if ($user !== null && $user->id !== $paste->userId) {
            return $response->setStatusCode(401)->buildError("Unauthorized");
        }

        $commentModel = $this->commentManager->getPasteComment($token, $comment);

        if ($commentModel === null) {
            return $response->setStatusCode(404)->buildError("Not Found");
        }

        $commentModel->forceDelete();
        return $response->setStatusCode(200)->setResult(null)->build();
    }
}
