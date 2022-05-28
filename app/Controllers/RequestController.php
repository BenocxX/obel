<?php namespace Controllers;

use Exception;
use Models\Brokers\RequestBroker;
use SendGrid;
use SendGrid\Mail\Mail;
use Zephyrus\Application\Flash;
use Zephyrus\Application\Localization;
use Zephyrus\Application\Rule;
use Zephyrus\Network\Response;

class RequestController extends Controller
{
    public function initializeRoutes()
    {
        $this->get("/request", "request");
        $this->post("/request/submit", "submitRequest");
    }

    public function request(): Response
    {
        return $this->render("request", [
            "currentRoute" => "/request",
            "routes" => [
                "/home" => "home",
                "/request" => "request",
//                "/about" => "about"
            ]
        ]);
    }

    public function submitRequest(): Response
    {
        $localization = Localization::getInstance();
        $form = $this->buildForm();
        $form->field("name")->validate([
            Rule::notEmpty($localization->localize("error.name")),
        ]);
        $form->field("email")->validate([
            Rule::notEmpty($localization->localize("error.email")),
        ]);
        $form->field("description")->validate([
            Rule::notEmpty($localization->localize("error.description")),
        ]);
//        $form->field("file")->validate([
//            Rule::notEmpty("Please enter an file."),
//            Rule::fileSize("The submitted file is too big. Max limit is 100mo", 100)
//        ]);

        if (!$form->verify()) {
            Flash::error($form->getErrorMessages());
            return $this->redirect("/request");
        }

        $clientName = $form->getValue("name");
        $clientEmail = $form->getValue("email");
        $requestDescription = $form->getValue("description");
//        $file = $form->getValue("file");

        $broker = new RequestBroker();
        $result = $broker->getLatestId();
        if (!empty($result)) {
            $id = $result[0]->id + 1;
        } else {
            $id = 1;
        }

        try {
            $email = new Mail();
            $email->setFrom(getenv("SEND_GRID_SENDER"), "Obel Request");
            $email->setSubject("Request #$id");
            $email->addTo(getenv("REQUEST_EMAIL_TO_ADDRESS"), getenv("REQUEST_EMAIL_TO_NAME"));
            $email->setReplyTo("$clientEmail", "$clientName");
            $email->addContent(
                "text/html", "Client's name: $clientName </br>Client's email: $clientEmail</br></br>Request description: $requestDescription"
            );

            $sendgrid = new SendGrid(getenv("SEND_GRID_API_KEY"));
            $sendgrid->send($email);

            Flash::success($localization->localize("error.emailSuccess"));
            $broker->insert($requestDescription, $clientName, $clientEmail);
        } catch (Exception $e) {
            Flash::error($localization->localize("error.emailFail"));
        }
        return $this->redirect("/request");
    }
}
