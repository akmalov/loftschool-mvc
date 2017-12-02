<?php

namespace Final2\Controllers;

use Final2\Models\User;

class Files extends Controller
{
    private $model;

    public function index()
    {
        if ($this->access() === 'denied') {
            $data['message'] = 'Доступ только для авторизованных пользователей';
            $this->view->render('message', $data);
            return;
        }
        require_once __DIR__ . '/../models/User.php';
        $this->model = new User();
        $this->model->loadAllPhotos();
        $data = $this->model->photos;
        $this->view->render('photos', $data);
    }

    public function change(int $userId = 0)
    {
        if ($this->access() === 'denied') {
            $data['message'] = 'Доступ только для авторизованых пользователей';
            $this->view->render('message', $data);
            return;
        }
        if (!is_numeric($userId)) {
            $data['message'] = 'Ошибка';
            $this->view->render('message', $data);
            return;
        }
        require_once __DIR__ . '/../models/User.php';
        $this->model = new User();
        if ($_FILES['photo']['tmp_name'] != '') {
            $this->model->changePhoto($userId, $_FILES['photo']);
            header('Location: /files/');
            return;
        }
        $data = $this->model->getNameAndPhoto($userId);
        if ($data['photo'] === null) {
            $data['photo'] = 'default-photo.png';
        }
        $this->view->render('change-photo', $data);
    }
}
