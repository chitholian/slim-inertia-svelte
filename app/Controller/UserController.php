<?php

namespace App\Controller;

use App\Middleware\Inertia;
use App\Middleware\Session;
use App\Middleware\UserIdentifier;
use App\Repo\UserRepo;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Slim\Routing\RouteContext;

class UserController extends BaseController
{
    public function loginForm(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        if (UserIdentifier::getUser()) return intended(['info' => 'You are already logged in']);
        return Inertia::render($request, $response, 'Forms/LoginForm');
    }

    public function login(ServerRequestInterface $request): ResponseInterface
    {
        if (UserIdentifier::getUser()) return back($request, ['info' => 'You are already logged in']);
        $data = $request->getParsedBody();
        $errors = [];
        if (empty($data['username'])) {
            $errors['username'] = 'Username is required';
        }
        if (empty($data['password'])) {
            $errors['password'] = 'Password is required';
        }

        if (empty($data['captcha'])) {
            $errors['captcha'] = 'Captcha is required';
        } elseif (!ExtraController::verifyCaptcha($data['captcha'])) {
            $errors['captcha'] = 'Invalid captcha';
        }
        if ($errors) {
            return back($request, ['errors' => $errors]);
        }
        // Do Login.
        $q = $this->db->execute("SELECT * FROM users WHERE BINARY username = ?", [$data['username']]);
        if ($u = $q->fetch()) {
            // Check password.
            if (!password_verify($data['password'], $u['password'])) {
                $errors['password'] = 'Incorrect password';
                return back($request, ['errors' => $errors]);
            }

            $params = $request->getServerParams();
            $device = [
                'did' => UserIdentifier::getDeviceID(),
                'ip' => $params['REMOTE_ADDR'] ?? 'Unknown',
                'port' => $params['REMOTE_PORT'] ?? 0,
                'user_agent' => $params['HTTP_USER_AGENT'] ?? null,
            ];

            /** @var UserRepo $repo */
            $repo = $this->container->get(UserRepo::class);
            $loginID = $repo->insertNewLogin($device, $u);
            Session::regenerate($u['id'] . '--', true);
            Session::put('user_id', $u['id']);
            Session::put('login_id', $loginID);
            Session::put('username', $u['username']);
            Session::put('role', $u['role']);
            return intended(['success' => 'Login Successful']);
        }
        $errors['username'] = 'User not found';
        return back($request, ['errors' => $errors]);
    }

    public function logout(ServerRequestInterface $request): ResponseInterface
    {
        $loginID = Session::get('login_id');
        Session::cleanAll();
        Session::regenerate();
        /** @var UserRepo $repo */
        $repo = $this->container->get(UserRepo::class);
        $repo->updateLoginReport($loginID);
        $r = RouteContext::fromRequest($request);
        return redirect($r->getRouteParser()->urlFor('login'), 303, ['info' => 'You have logged out successfully']);
    }

    public function loginReport(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        $this->resolveQueryOffset($request);
        $q = $this->db->execute("SELECT l.*, t1.n, u.username, d.trusted FROM login_reports l LEFT JOIN users u on u.id = l.user_id LEFT JOIN devices d on l.did = d.did LEFT JOIN (SELECT COUNT(id) as n FROM login_reports) t1 ON 1 ORDER BY l.created_at DESC LIMIT $this->offset, $this->dpp");
        $items = $q->fetchAll();
        $data = [
            'items' => $items,
            'page' => $this->page,
            'dpp' => $this->dpp,
            'total' => intval($items[0]['n'] ?? 0),
        ];

        return Inertia::render($request, $response, 'LoginReport', ['data' => $data, 'query' => $request->getQueryParams()]);
    }

    public function modifyLoginReport(ServerRequestInterface $request): ResponseInterface
    {
        $data = $request->getParsedBody();
        if (!isset($data['action'], $data['ids'])) return back($request, ['danger' => 'Required field missing']);
        elseif (!is_array($data['ids'])) return back($request, ['danger' => 'Field ids must be an array']);

        $ids = [];
        foreach ($data['ids'] as $id) {
            $ids[] = intval($id);
        }
        $ids = "('" . implode("','", $ids) . "')";
        switch ($data['action']) {
            case 'delete':
                $stmt = $this->db->execute("DELETE FROM login_reports WHERE id IN $ids AND id <> ?", [Session::get('login_id')]);
                $count = $stmt->rowCount();
                return back($request, ['info' => "$count items deleted"]);
            case 'clean':
                $this->db->execute("DELETE FROM login_reports WHERE id <> ?", [Session::get('login_id')]);
                return back($request, ['info' => "Login report cleaned up"]);
            default:
                return back($request, ['danger' => 'Unknown action specified']);
        }
    }

    public function changePassForm(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        $user = UserIdentifier::getUser();
        $info = ['username' => $user['username']];
        return Inertia::render($request, $response, 'Forms/ChangePassword', ['userInfo' => $info]);
    }

    public function changePass(ServerRequestInterface $request): ResponseInterface
    {
        $data = $request->getParsedBody();
        $errors = [];
        if (empty($data['username'])) $errors['username'] = 'Username is required';
        if (empty($data['password'])) $errors['password'] = 'Password is required';
        if (empty($data['password_old'])) $errors['password_old'] = 'Current password is required';
        if ($errors) return back($request, ['errors' => $errors]);
        $user = UserIdentifier::getUser();

        if (!password_verify($data['password_old'], $user['password'])) {
            $errors['password_old'] = 'Incorrect current password';
        }
        $q = $this->db->execute("SELECT id FROM users WHERE BINARY username = ? AND id <> ?", [$data['username'], $user['id']]);
        if ($q->fetch()) {
            $errors['username'] = 'Username already exists.';
        }
        if ($errors) return back($request, ['errors' => $errors]);

        $this->db->execute("UPDATE users SET username = ?, password = ?, updated_at = NOW() WHERE id = ?", [$data['username'], password_hash($data['password'], PASSWORD_DEFAULT), $user['id']]);
        if ($data['logout_all'] ?? false) {
            Session::regenerate('', true);
            Session::deleteByPrefix($user['id'] . '--');
            $this->db->execute("UPDATE login_reports SET logout_time = NOW() WHERE user_id = ? AND logout_time IS NULL", [$user['id']]);
            $r = RouteContext::fromRequest($request);
            return redirect($r->getRouteParser()->urlFor('login'), 303, ['success' => 'Credentials changed successfully']);
        }
        return back($request, ['success' => 'Credentials changed successfully']);
    }
}
