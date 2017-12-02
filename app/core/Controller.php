<?php

namespace Final2\Controllers;

use Final2\Views\View;

abstract class Controller
{
    protected $view;

    public function __construct()
    {
        $this->view = new View();
    }

    protected function access()
    {
        session_start();
        if ($_SESSION['access'] === 'granted') {
            return 'granted';
        } else {
            return 'denied';
        }
    }
}
