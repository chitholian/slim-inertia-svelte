<?php

namespace App\Controller;

use App\Middleware\Inertia;
use App\Middleware\Session;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class DeviceController extends BaseController
{
    public function report(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        $this->resolveQueryOffset($request);
        $did = Session::getCookie('did');
        $q = "SELECT t1.n, d.id, u.username, ip, last_seen, user_agent, trusted, IF(did = ?,'y','n') as this_device FROM devices d LEFT JOIN (SELECT COUNT(id) as n FROM devices) t1 ON 1 LEFT JOIN users u on u.id = d.user_id ORDER BY last_seen DESC LIMIT $this->offset, $this->dpp";
        $q = $this->db->execute($q, [$did]);
        $items = $q->fetchAll();
        $data = [
            'items' => $items,
            'page' => $this->page,
            'dpp' => $this->dpp,
            'total' => intval($items[0]['n'] ?? 0),
        ];

        return Inertia::render($request, $response, 'Devices', ['data' => $data, 'query' => $request->getQueryParams()]);
    }

    public function takeAction(ServerRequestInterface $request): ResponseInterface
    {
        $data = $request->getParsedBody();
        if (!isset($data['action'], $data['ids'])) {
            return back($request, ['danger' => 'Required field missing']);
        }
        if (!is_array($data['ids'])) return back($request, ['danger' => 'Field ids must be an array']);

        $ids = [];
        foreach ($data['ids'] as $id) {
            $ids[] = intval($id);
        }
        $ids = "('" . implode("','", $ids) . "')";

        switch ($data['action']) {
            case 'delete':
                $stmt = $this->db->execute("DELETE FROM devices WHERE id IN $ids AND did <> :did", ['did' => Session::getCookie('did')]);
                $count = $stmt->rowCount();
                break;
            case 'trust':
                $stmt = $this->db->execute("UPDATE devices SET trusted = 'y' WHERE id IN $ids");
                $count = $stmt->rowCount();
                break;
            case 'distrust':
                $stmt = $this->db->execute("UPDATE devices SET trusted = 'n' WHERE id IN $ids");
                $count = $stmt->rowCount();
                break;
            case 'clean':
                $this->db->execute("DELETE FROM devices WHERE did <> :did", ['did' => Session::getCookie('did')]);
                return back($request, ['info' => "Device list cleaned up"]);
            default:
                return back($request, ['danger' => 'Unknown action specified']);
        }
        return back($request, ['info' => "$count devices affected by the action \"$data[action]\""]);
    }
}
