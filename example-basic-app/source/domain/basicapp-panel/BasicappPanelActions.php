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
        // Set the Innomatic Container inside this object for faster access
        // to the object.
        //
        $this->container = InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer');

        // Set localization catalog for this panel.
        //
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

    /**
     * Method for add item action.
     * 
     * This method is called when the user adds a new item.
     * 
     * @param array $eventData WUI event data.
     * @access public
     * @return void
     */
    public function executeAdditem($eventData)
    {
        $country = new \Innomatic\Locale\LocaleCountry(
            $this->container->getCurrentUser()->getCountry()
        );
        $dateArray = $country->getDateArrayFromShortDatestamp($eventData['date']);

        // Add the item to the database.
        //
        $basicApp = new \Examples\Basic\BasicClass();
        $basicApp->addItem($eventData['description'], $dateArray);

        // Update the panel status bar.
        //
        $this->status = $this->catalog->getStr('item_added_status');

        // Update the observers since we changed the status string.
        //
        $this->setChanged();
        $this->notifyObservers('status');
    }
    
    /**
     * Method for delete item action.
     *
     * This method is called when the user deletes an item.
     *
     * @param array $eventData WUI event data.
     * @access public
     * @return void
     */
    public function executeDeleteitem($eventData)
    {
        // Remove the item from the database.
        //
        $basicApp = new \Examples\Basic\BasicClass($eventData['id']);
        $basicApp->delete();
    
        // Update the panel status bar.
        //
        $this->status = $this->catalog->getStr('item_deleted_status');
    
        // Update the observers since we changed the status string.
        //
        $this->setChanged();
        $this->notifyObservers('status');
    }
}
