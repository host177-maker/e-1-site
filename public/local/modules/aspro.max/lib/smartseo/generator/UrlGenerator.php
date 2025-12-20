<?php
namespace Aspro\Max\Smartseo\Generator;

use Aspro\Max\Smartseo\Generator\Handlers;

class UrlGenerator
{
    private $urlPageTemplate = '';
    private $urlSmartFilterTemplate = '';
    private $urlSection = '';

    private $errors = [];

    public function __construct()
    {

    }

    /**
     * @var Handlers\AbstractUrlHandler
     */
    private $handlers = [];


    public function setPageUrlTemplate($value)
    {
        $this->urlPageTemplate = $value;
    }

    public function getPageUrlTemplate()
    {
        return $this->urlPageTemplate;
    }

    public function setSmartFilterUrlTemplate($value)
    {
        $this->urlSmartFilterTemplate = $value;
    }

    public function getUrlSmartFilterTemplate()
    {
        return $this->urlSmartFilterTemplate;
    }

    public function setSectionUrlTemplate($value)
    {
        $this->urlSection = $value;
    }

    public function getSectionUrlTemplate($value)
    {
        return $this->urlSection;
    }

    public function addHandler(Handlers\AbstractUrlHandler $handler)
    {
        $this->handlers[] = clone $handler;
    }

    public function generate()
    {
        $result = [
            [
                'URL_PAGE' => $this->urlPageTemplate,
                'URL_SMART_FILTER' => $this->urlSmartFilterTemplate,
                'URL_SECTION' => $this->urlSection,
                'PARAMS' => [],
            ],
        ];


        foreach ($this->handlers as $handler) {
            if(!$handler->validateInitialParams()) {
                $this->errors[] = $handler->getErrors();
                continue;
            }

            $handler->generateResult($result);
        }

        return $result;
    }
}
