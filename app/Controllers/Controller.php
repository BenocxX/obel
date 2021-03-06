<?php namespace Controllers;

use Zephyrus\Application\Localization;
use Zephyrus\Application\Session;
use Zephyrus\Exceptions\LocalizationException;
use Zephyrus\Network\Response;

/**
 * This class acts as an application middleware, all other controller classes should extends this Controller and thus
 * inherit every global behaviors your application may require. You can override methods like before() and after() to
 * make good use of the middleware feature, or simply override method like render() to define specific variables that
 * all views should have. You can have as much middleware as you want (through extends).
 *
 * Class Controller
 * @package Controllers
 */
abstract class Controller extends SecurityController
{
    /**
     * Override example of the render method to automatically include arguments to be sent to any views for any
     * Controller class extending this middleware. Useful for global data used in layout files.
     *
     * @param string $page
     * @param array $args
     * @return Response
     */
    public function render($page, $args = []): Response
    {
        $otherLang = 'en_US';
        $session = Session::getInstance();
        if (!empty($session->read('lang'))) {
            $currentLang = $session->read('lang');
            if ($currentLang == 'fr_CA') {
                $otherLang = 'en_US';
            } else if ($currentLang == 'en_US') {
                $otherLang = 'fr_CA';
            }
        }
        return parent::render($page, array_merge($args, [
            'system_date' => date(FORMAT_DATE_TIME),
            'other_lang' => $otherLang,
        ]));
    }

    /**
     * This method is called immediately before processing any route in your controller. To break the chain of
     * middleware, you can remove the call to parent::before() method, but it is highly discouraged. Instead, you should
     * always keep the parent call, but place it accordingly to your situation (should the parent's middleware
     * processing be done before or after mine?).
     *
     * If this method returns a Response, the whole execution chain is broken and the Response is directly returned. It
     * is useful for some security validations before any route processing. Should be removed if not used.
     *
     * @return Response | null
     */
    public function before(): ?Response
    {
        $form = $this->buildForm();
        if (!empty($form->getValue('lang'))) {
            Session::getInstance()->set('lang', $form->getValue('lang'));
        }
        $this->localize();
        return parent::before();
    }

    /**
     * This method is called after processing any route in your controller. It receives the processed response as
     * argument which you can modify and then return too to another middleware or the client response. Should be removed
     * if not used.
     *
     * @param Response $response
     * @return Response | null
     */
    public function after(?Response $response): ?Response
    {
        return parent::after($response);
    }

    private function localize()
    {
        try {
            $language = 'fr_CA';
            $session = Session::getInstance();
            if (!empty($session->read("lang"))) {
                $language = $session->read("lang");
            }
            Localization::getInstance()->start($language);
        } catch (LocalizationException $e) {
            die($e->getMessage());
        }
    }
}
