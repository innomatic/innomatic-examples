<?php

use \Innomatic\Core\InnomaticContainer;
use \Innomatic\Wui\Widgets;
use \Innomatic\Wui\Dispatch;

/**
 * Actions for the Basicapp panel.
 */
class BasicappPanelActions extends \Innomatic\Desktop\Panel\PanelActions
{
    /**
     * Innomatic Container.
     *
     * @var \Innomatic\Core\InnomaticContainer
     * @access protected
     */
    protected $container;
    /**
     * Localized string catalog.
     *
     * @var \Innomatic\Locale\LocaleCatalog
     * @access protected
     */
    protected $catalog;

    public function __construct(\Innomatic\Desktop\Panel\PanelController $controller)
    {
        parent::__construct($controller);
    }

    /**
     * Action begin helper.
     *
     * This method is executed before calling the method for the requested action.
     */
     public function beginHelper()
    {
        $this->container = InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer');

        $this->catalog = new \Innomatic\Locale\LocaleCatalog(
            'example-basic-app::panel',
            $this->container->getCurrentUser()->getLanguage()
        );
    }

    /**
     * Action end helper.
     *
     * This method is executed after calling the method for the requested action.
     */
    public function endHelper()
    {
    }

    public function executeAdditem($eventData)
    {
        $country = new \Innomatic\Locale\LocaleCountry(
            $this->container->getCurrentUser()->getCountry()
        );
        $dateArray = $country->getDateArrayFromShortDatestamp($eventData['date']);

        $basicApp = new \Examples\Basic\BasicClass();
        $basicApp->addItem($eventData['description'], $dateArray);
    }
}
