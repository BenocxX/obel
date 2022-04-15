<?php namespace Controllers;

use Zephyrus\Network\Response;

class AboutController extends Controller
{
    public function initializeRoutes()
    {
        $this->get("/about", "about");
    }

    public function about(): Response
    {
        return $this->render("about");
    }
}
