<?php

namespace Final2\Controllers;

use Final2\Models\User;

class CreateUser extends Controller
{
    private $model;

    public function index()
    {
        if ($this->access() === 'denied') {
            $data['message'] = 'Доступ только для авторизованных пользователей';
            $this->view->render('message', $data);
            return;
        }

        $this->view->render('createuser');
    }

    public function new()
    {
        require_once __DIR__ . '/../models/User.php';
        $this->model = new User();
        $this->model->registerNewUser(
            $_POST['login'],
            $_POST['password1'],
            $_POST['password2'],
            $_POST['name'],
            $_POST['age'],
            $_POST['description'],
            $_FILES['photo']
        );
        $data['message'] = $this->model->message;
        if ($this->model->success) {
            $this->view->render('message', $data);
        } else {
            $this->view->render('createuser', $data);
        }
    }
}
