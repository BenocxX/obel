<?php namespace Controllers;

use Zephyrus\Network\Response;

class RequestController extends Controller
{
    public function initializeRoutes()
    {
        $this->get("/request", "request");
    }

    public function request(): Response
    {
        return $this->render("request");
    }
}
