<?php
namespace App\Http\Controllers;

use App\Http\Managers\AuthenticationManager;
use App\Http\Managers\CryptoManager;
use App\Http\Managers\ErrorManager;
use App\Http\Managers\Object\CommentManager;
use App\Http\Managers\ResponseManager;
use App\Http\Response\HTTPStatus;
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

    /**
     * Get a list of all the pastes a user owns
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function getPasteList(Request $request): JsonResponse
    {
        $response = new ResponseManager();

        if ($request->header("Authorization") === null) {
            $error = new ErrorManager();
            return $error->setHttpStatus(new HTTPStatus(401, "Unauthorized"))->build();
        }

        $bearerToken = explode(" ", $request->header("Authorization"))[1];
        $user = AuthenticationManager::getUserByOAuthToken($bearerToken);

        if ($user === null) {
            $error = new ErrorManager();
            return $error->setHttpStatus(new HTTPStatus(401, "Unauthorized"))->build();
        }

        $pasteList = Paste::where([
            "userId" => $user->id,
            "active" => 1
        ])->get();

        return $response->setHttpStatus(new HTTPStatus(200, "Success"))->setResult($pasteList)->build();
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
            $language = $request->has("language") ? Language::where('slug', '=', $request->get("language"))->first() : null;
            $languageId = $language === null ? null : $language->id;
            $userId = $user !== null ? $user->id : null;
            $password = $request->has("password") ? $request->get("password") : null;

            if ($code === null) {
                $error = new ErrorManager();
                return $error->setHttpStatus(new HTTPStatus(400, "Bad Request"))->build();
            }

            // Create Paste
            $paste = new Paste();

            $paste->token = $token;
            $paste->description = $description;
            $paste->code = $code;
            $paste->userId = $userId;
            $paste->languageId = $languageId;
            $paste->password = $password;


            $paste->save();
        } catch (\Exception $exception) {
            $error = new ErrorManager();
            return $error->setHttpStatus(new HTTPStatus(500, "Internal Server Error"))->build();
        }

        return $response->setHttpStatus(new HTTPStatus(200, "Success"))->setResult($paste)->build();
    }

    /**
     * Get a list of all the pastes a user owns
     *
     * @param $token
     * @return JsonResponse
     */
    public function getPaste($token): JsonResponse
    {
        $response = new ResponseManager();
        $paste = Paste::where("token", "=", $token)->first();
        $pasteDeleted = false;

        if ($paste->deleted_at !== null) {
            $carbonTime = Carbon::createFromFormat('Y-m-d H:i:s', $paste->deleted_at);

            if (!$paste->active || $carbonTime->getTimestamp() <= Carbon::now()->getTimestamp()) {
                $pasteDeleted = true;
            }
        } else {
            if (!$paste->active) {
                $pasteDeleted = true;
            }
        }

        if ($pasteDeleted) {
            // paste is due to be deleted -> set active state to false
            $paste->active = false;
            $paste->save();

            $error = new ErrorManager();
            return $error->setHttpStatus(new HTTPStatus(404, "Not Found"))->build();
        }

        $paste->user = $paste->userId === null ? null : User::where("id", "=", $paste->userId)->first();
        $paste->language = $paste->languageId === null ? null : Language::where("id", "=", $paste->languageId)->first();
        $paste->comments = CommentManager::getPasteComments($token);

        unset($paste->userId);

        return $response->setHttpStatus(new HTTPStatus(200, "Success"))->setResult($paste)->build();
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
            $error = new ErrorManager();
            return $error->setHttpStatus(new HTTPStatus(404, "Not Found"))->build();
        }

        if ($paste->userId !== null) {
            if ($request->header("Authorization") === null) {
                $error = new ErrorManager();
                return $error->setHttpStatus(new HTTPStatus(401, "Unauthorized"))->build();
            }

            $bearerToken = explode(" ", $request->header("Authorization"))[1];
            $user = AuthenticationManager::getUserByOAuthToken($bearerToken);

            if ($user === null || (string) $user->id !== (string) $paste->userId) {
                $error = new ErrorManager();
                return $error->setHttpStatus(new HTTPStatus(401, "Unauthorized"))->build();
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
            $error = new ErrorManager();
            return $error->setHttpStatus(new HTTPStatus(401, "Unauthorized"))->build();
        }

        $bearerToken = explode(" ", $request->header("Authorization"))[1];
        $user = AuthenticationManager::getUserByOAuthToken($bearerToken);

        $paste = Paste::where("token", "=", $token)->first();

        if ($paste === null) {
            $error = new ErrorManager();
            return $error->setHttpStatus(new HTTPStatus(404, "Not Found"))->build();
        }

        if ($user !== null && $user->id !== $paste->userId) {
            $error = new ErrorManager();
            return $error->setHttpStatus(new HTTPStatus(401, "Unauthorized"))->build();
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
                $error = new ErrorManager();
                return $error->setHttpStatus(new HTTPStatus(404, "Not Found"))->build();
            }

            $bearerToken = $request->header("Authorization") !== null ? explode(" ", $request->header("Authorization"))[1] : null;
            $user = $bearerToken === null ? null : AuthenticationManager::getUserByOAuthToken($bearerToken);

            // Comment Data Values
            $message = $request->has("message") ? CryptoManager::encrypt($request->get("message")) : null;
            $userId = $user !== null ? $user->id : null;

            if ($message === null) {
                $error = new ErrorManager();
                return $error->setHttpStatus(new HTTPStatus(400, "Bad Request"))->build();
            }

            // Create Comment
            $comment = new Comment();

            $comment->pasteId = $paste->id;
            $comment->userId = $userId;
            $comment->message = $message;

            $comment->save();

            return $response->setHttpStatus(new HTTPStatus(200, "Success"))->setResult($comment)->build();
        } catch (\Exception $exception) {
            $error = new ErrorManager();
            return $error->setHttpStatus(new HTTPStatus(500, "Internal Server Error"))->build();
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
            $error = new ErrorManager();
            return $error->setHttpStatus(new HTTPStatus(401, "Unauthorized"))->build();
        }

        $bearerToken = explode(" ", $request->header("Authorization"))[1];
        $user = AuthenticationManager::getUserByOAuthToken($bearerToken);

        $paste = Paste::where("token", "=", $token)->first();

        if ($paste === null) {
            $error = new ErrorManager();
            return $error->setHttpStatus(new HTTPStatus(404, "Not Found"))->build();
        }

        if ($user !== null && $user->id !== $paste->userId) {
            $error = new ErrorManager();
            return $error->setHttpStatus(new HTTPStatus(401, "Unauthorized"))->build();
        }

        $comment = CommentManager::getPasteComment($token, $comment);

        if ($comment === null) {
            $error = new ErrorManager();
            return $error->setHttpStatus(new HTTPStatus(404, "Not Found"))->build();
        }

        return $response->setHttpStatus(new HTTPStatus(200, "Success"))->setResult($comment)->build();
    }
}
