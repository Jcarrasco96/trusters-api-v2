<?php

namespace app\controllers;

use app\core\Controller;
use app\core\Utils;
use app\core\Validators;
use app\models\Download;
use Exception;

class DownloadController extends Controller {

    /**
     * @throws Exception
     */
    public function index(): array
    {
        $model = new Download();
        $token = Utils::token();

        Validators::isDownloader($token['id'], $token['username']);

        $page_size = isset($_GET['page_size']) ? intval($_GET['page_size']) : 20;
        $offset = isset($_GET['page']) ? intval($_GET['page']) : 1;
        $offset = ($offset - 1) * $page_size;

        list($data, $count) = $model->index($page_size, $offset);

        return [
            'count' => (int)$count,
            'pages' => ceil($count / $page_size),
            'downloads' => $data,
        ];
    }

    /**
     * @throws Exception
     */
    public function view($id): array
    {
        $model = new Download();
        $token = Utils::token();

        Validators::isDownloader($token['id'], $token['username']);

        return $model->view($id);
    }

    /**
     * @throws Exception
     */
    public function owner(): array
    {
        $model = new Download();
        $token = Utils::token();

        Validators::isDownloader($token['id'], $token['username']);

        $page_size = isset($_GET['page_size']) ? (int)$_GET['page_size'] : 20;
        $offset = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $offset = ($offset - 1) * $page_size;

        list($data, $count) = $model->owner($page_size, $offset, $token['id']);

        return [
            'count' => (int)$count,
            'pages' => ceil($count / $page_size),
            'downloads' => $data,
        ];
    }

    /**
     * @throws Exception
     */
    public function create(): array
    {
        $model = new Download();
        $token = Utils::token();

        Validators::isDownloader($token['id'], $token['username']);
        Validators::validateSet("Verifique los datos del post tengan formato correcto", $this->dataJson, 'name', 'url');

        return $model->create($this->dataJson['name'], $this->dataJson['url'], $token['id']);
    }

    /**
     * @throws Exception
     */
    public function update($id): array
    {
        $model = new Download();
        $token = Utils::token();

        Validators::validateSet('Verifique los datos del afiliado tengan formato correcto', $this->dataJson, 'name');

        $rows = $model->update($this->dataJson['name'], $id, $token['id']);

        if ($rows == 1) {
            return ['status_code' => 200, 'message' => 'Download modificada correctamente'];
        }

        if ($rows == 0) {
            return ['status_code' => 200, 'message' => 'No hay cambios de datos en la descarga'];
        }

        throw new Exception('Error en la base de datos', 500);
    }

    /**
     * @throws Exception
     */
    public function delete($id): array
    {
        $model = new Download();
        $token = Utils::token();

        Validators::isDownloader($token['id'], $token['username']);
        Validators::validateIsNumeric($id);

        if ($model->delete($id, $token['id']) == 1) {
            return ['status_code' => 200, 'message' => 'Registro eliminado'];
        }

        throw new Exception("Registro no eliminado", 400);
    }

}