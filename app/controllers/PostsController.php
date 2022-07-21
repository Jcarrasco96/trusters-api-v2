<?php

namespace app\controllers;

use app\core\Controller;
use app\core\Utils;
use app\core\Validators;
use app\models\Comment;
use app\models\Post;
use Exception;

class PostsController extends Controller {

    public function index() {
        $model = new Post();
        $token = Utils::token();

        Validators::isPoster($token['id'], $token['username'], $token['unique_hash']);

        $page_size = isset($_GET['page_size']) ? intval($_GET['page_size']) : 20;
        $offset = isset($_GET['page']) ? intval($_GET['page']) : 1;
        $offset = ($offset - 1) * $page_size;

        list($data, $count) = $model->index($page_size, $offset);

        return [
            'count' => (int)$count,
            'pages' => ceil($count / $page_size),
            'posts' => $data,
        ];
    }


    public function view($id) {
        $model = new Post();
        $token = Utils::token();

        Validators::isPoster($token['id'], $token['username'], $token['unique_hash']);

        return $model->view($id);
    }

    public function owner() {
        $model = new Post();
        $token = Utils::token();

        Validators::isPoster($token['id'], $token['username'], $token['unique_hash']);

        $page_size = isset($_GET['page_size']) ? (int)$_GET['page_size'] : 20;
        $offset = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $offset = ($offset - 1) * $page_size;

        list($data, $count) = $model->owner($page_size, $offset, $token['id']);

        return [
            'count' => (int)$count,
            'pages' => ceil($count / $page_size),
            'posts' => $data,
        ];
    }

    public function comments($id) {
        $commentModel = new Comment();
        $token = Utils::token();

        Validators::isPoster($token['id'], $token['username'], $token['unique_hash']);

        return $commentModel->index($id);
    }

    public function create() {
        $model = new Post();
        $token = Utils::token();

        Validators::isPoster($token['id'], $token['username'], $token['unique_hash']);
        Validators::validateIsSet("Verifique los datos del post tengan formato correcto", $this->dataJson, 'content');

        return $model->create($this->dataJson['content'], $token['id']);
    }

    public function comment($id) {
        $commentModel = new Comment();
        $token = Utils::token();

        Validators::isPoster($token['id'], $token['username'], $token['unique_hash']);
        Validators::validateIsSet("Verifique los datos enviados", $this->dataJson, 'content');
        Validators::validateIsNumeric($id);

        $rows = $commentModel->create($id, $token['id'], $this->dataJson['content']);

        if ($rows == 1) {
            return [
                'status'  => 201,
                'message' => 'Well done!'
            ];
        }

        throw new Exception("No se pudo comentar la publicación", 400);
    }

    public function delete($id) {
        $model = new Post();
        $token = Utils::token();

        Validators::isPoster($token['id'], $token['username'], $token['unique_hash']);
        Validators::validateIsNumeric($id);

        if ($model->delete($id, $token['id']) == 1) {
            return [
                'status' => 200,
                'message' => 'Registro eliminado'
            ];
        }

        throw new Exception("Registro no eliminado", 400);
    }

}