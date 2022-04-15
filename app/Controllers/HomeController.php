<?php namespace Controllers;

use Zephyrus\Network\Response;

class HomeController extends Controller
{
    public function initializeRoutes()
    {
        $this->get("/", "home");
        $this->get("/home", "home");
    }

    public function home(): Response
    {
        return $this->render("home", [
            "currentRoute" => "/home",
            "routes" => [
                "/home" => "Home",
                "/request" => "Request",
                "/about" => "About"
            ]
        ]);
    }
}
