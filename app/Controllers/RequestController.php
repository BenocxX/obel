<?php namespace Controllers;

use Exception;
use Models\Brokers\RequestBroker;
use SendGrid;
use SendGrid\Mail\Mail;
use Zephyrus\Application\Flash;
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
                "/home" => "Home",
                "/request" => "Request",
                "/about" => "About"
            ]
        ]);
    }

    public function submitRequest(): Response
    {
        $form = $this->buildForm();
        $form->field("name")->validate([
            Rule::notEmpty("Please enter a name."),
        ]);
        $form->field("email")->validate([
            Rule::notEmpty("Please enter an email."),
        ]);
        $form->field("description")->validate([
            Rule::notEmpty("Please enter a description."),
        ]);
//        $form->field("file")->validate([
//            Rule::notEmpty("Please enter an file."),
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
        $broker->insert($requestDescription, $clientName, $clientEmail);
        $result = $broker->getLatestId();
        $id = $result[0]->id;

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

            Flash::success("Request submitted successfully!");
            $broker->insert($requestDescription, $clientName, $clientEmail);
        } catch (Exception $e) {
            Flash::error("Failed to send email.");
        }
        return $this->redirect("/request");
    }
}
