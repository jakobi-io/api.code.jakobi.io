<?php
namespace App\Http\Controllers;

use App\Http\Managers\AuthenticationManager;
use App\Http\Managers\CryptoManager;
use App\Http\Managers\PasteManager;
use App\Http\Managers\Response\Error;
use App\Http\Managers\Response\ErrorManager;
use App\Http\Managers\Object\CommentManager;
use App\Http\Managers\ResponseManager;
use App\Http\Response\HTTPStatus;
use App\Http\Response\Response;
use App\Models\Comment;
use App\Models\Language;
use App\Models\Paste;
use App\Models\User;
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

        if ($request->header("Authorization") === null) {
            return ErrorManager::buildError(Error::ERROR_UNAUTHORIZED);
        }

        $bearerToken = explode(" ", $request->header("Authorization"))[1];
        $user = AuthenticationManager::getUserByOAuthToken($bearerToken);

        if ($user === null) {
            return ErrorManager::buildError(Error::ERROR_UNAUTHORIZED);
        }

        $pasteList = Paste::where([
            "userId" => $user->id,
            "active" => 1
        ])->get();

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
        $response = new ResponseManager();

        try {
            $bearerToken = $request->header("Authorization") !== null ? explode(" ", $request->header("Authorization"))[1] : null;
            $user = $bearerToken === null ? null : AuthenticationManager::getUserByOAuthToken($bearerToken);

            // Paste Data Values
            $token = CryptoManager::generateToken(6);
            $description = $request->has("description") ? CryptoManager::encrypt($request->get("description")) : null;
            $code = $request->has("code") ? CryptoManager::encrypt($request->get("code")) : null;
            $language = $request->has("language") ? Language::where("slug", "=", $request->get("language"))->first() : null;
            $languageId = $language === null ? null : $language->id;
            $userId = $user !== null ? $user->id : null;
            $deleteAfter = $request->has("deleteAfter") ? $request->get("deleteAfter") : null;
            $password = null;
            $deletedAt = null;

            if ($code === null) {
                return ErrorManager::buildError(Error::ERROR_PARAMETER_MISSING);
            }

            if ($deleteAfter !== null) {
                if ($deleteAfter === "hour") {
                    $deletedAt = Carbon::now()->addHour();
                } else if ($deleteAfter === "day") {
                    $deletedAt = Carbon::now()->addDay();
                } else if ($deleteAfter === "week") {
                    $deletedAt = Carbon::now()->addWeek();
                } else if ($deleteAfter === "month") {
                    $deletedAt = Carbon::now()->addMonth();
                } else if ($deleteAfter === "year") {
                    $deletedAt = Carbon::now()->addYear();
                } else {
                    // fallback
                    $deletedAt = Carbon::now()->addDay();
                }
            }

            // Create Paste
            $paste = new Paste();

            $paste->token = $token;
            $paste->description = $description;
            $paste->code = $code;
            $paste->userId = $userId;
            $paste->languageId = $languageId;
            $paste->password = $password;
            $paste->deleted_at = $deletedAt;

            $paste->save();
        } catch (\Exception $exception) {
            return ErrorManager::buildError(Error::ERROR_INTERNAL_ERROR);
        }

        return $response->setHttpStatus(new HTTPStatus(200, "Success"))->setResult(["token" => $paste->token])->build();
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
        $response = new ResponseManager();
        $paste = Paste::where("token", "=", $token)->first();

        if ($paste === null) {
            return ErrorManager::buildError(Error::ERROR_NOT_FOUND);
        }

        if ($paste->userId !== null) {
            if ($request->header("Authorization") === null) {
                return ErrorManager::buildError(Error::ERROR_UNAUTHORIZED);
            }

            $bearerToken = explode(" ", $request->header("Authorization"))[1];
            $user = AuthenticationManager::getUserByOAuthToken($bearerToken);

            if ($user === null || (string) $user->id !== (string) $paste->userId) {
                return ErrorManager::buildError(Error::ERROR_UNAUTHORIZED);
            }
        }

        $paste->active = false;
        $paste->deleted_at = Carbon::now()->toDateTimeString();
        $paste->save();

        return $response->setHttpStatus(new HTTPStatus(200, "Success"))->setHasResult(false)->build();
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
        $response = new ResponseManager();

        if ($request->header("Authorization") === null) {
            return ErrorManager::buildError(Error::ERROR_UNAUTHORIZED);
        }

        $bearerToken = explode(" ", $request->header("Authorization"))[1];
        $user = AuthenticationManager::getUserByOAuthToken($bearerToken);

        $paste = Paste::where("token", "=", $token)->first();

        if ($paste === null) {
            return ErrorManager::buildError(Error::ERROR_NOT_FOUND);
        }

        if ($user !== null && $user->id !== $paste->userId) {
            return ErrorManager::buildError(Error::ERROR_UNAUTHORIZED);
        }

        $commentList = Comment::where("pasteId", "=", $paste->id)->get();

        foreach ($commentList as $comment) {
            $comment->user = $comment->userId === null ? null : User::where("id", "=", $comment->userId)->first();
            unset($comment->userId);
        }

        return $response->setHttpStatus(new HTTPStatus(200, "Success"))->setResult($commentList)->build();
    }

    public function createPasteComment(Request $request, $token): JsonResponse
    {
        $response = new ResponseManager();

        try {
            $paste = Paste::where("token", "=", $token)->first();

            if ($paste === null) {
                return ErrorManager::buildError(Error::ERROR_NOT_FOUND);
            }

            $bearerToken = $request->header("Authorization") !== null ? explode(" ", $request->header("Authorization"))[1] : null;
            $user = $bearerToken === null ? null : AuthenticationManager::getUserByOAuthToken($bearerToken);

            // Comment Data Values
            $message = $request->has("message") ? CryptoManager::encrypt($request->get("message")) : null;
            $userId = $user !== null ? $user->id : null;

            if ($message === null) {
                return ErrorManager::buildError(Error::ERROR_NOT_FOUND);
            }

            // Create Comment
            $comment = new Comment();

            $comment->pasteId = $paste->id;
            $comment->userId = $userId;
            $comment->message = $message;

            $comment->save();

            return $response->setHttpStatus(new HTTPStatus(200, "Success"))->setResult($comment)->build();
        } catch (\Exception $exception) {
            return ErrorManager::buildError(Error::ERROR_INTERNAL_ERROR);
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
        $response = new ResponseManager();

        if ($request->header("Authorization") === null) {
            return ErrorManager::buildError(Error::ERROR_UNAUTHORIZED);
        }

        $bearerToken = explode(" ", $request->header("Authorization"))[1];
        $user = AuthenticationManager::getUserByOAuthToken($bearerToken);

        $paste = Paste::where("token", "=", $token)->first();

        if ($paste === null) {
            return ErrorManager::buildError(Error::ERROR_NOT_FOUND);
        }

        if ($user !== null && $user->id !== $paste->userId) {
            return ErrorManager::buildError(Error::ERROR_UNAUTHORIZED);
        }

        $comment = CommentManager::getPasteComment($token, $comment);

        if ($comment === null) {
            return ErrorManager::buildError(Error::ERROR_NOT_FOUND);
        }

        return $response->setHttpStatus(new HTTPStatus(200, "Success"))->setResult($comment)->build();
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
        $response = new ResponseManager();

        if ($request->header("Authorization") === null) {
            return ErrorManager::buildError(Error::ERROR_UNAUTHORIZED);
        }

        $bearerToken = explode(" ", $request->header("Authorization"))[1];
        $user = AuthenticationManager::getUserByOAuthToken($bearerToken);

        $paste = Paste::where("token", "=", $token)->first();

        if ($paste === null) {
            return ErrorManager::buildError(Error::ERROR_NOT_FOUND);
        }

        if ($user !== null && $user->id !== $paste->userId) {
            return ErrorManager::buildError(Error::ERROR_UNAUTHORIZED);
        }

        $comment = CommentManager::getPasteComment($token, $comment);

        if ($comment === null) {
            return ErrorManager::buildError(Error::ERROR_NOT_FOUND);
        }

        $comment->forceDelete();
        return $response->setHttpStatus(new HTTPStatus(200, "Success"))->setHasResult(false)->build();
    }
}
