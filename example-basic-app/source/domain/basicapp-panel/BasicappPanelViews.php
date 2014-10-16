<?php
use \Innomatic\Core\InnomaticContainer;
use \Innomatic\Wui\Widgets;
use \Innomatic\Wui\Dispatch;
use \Shared\Wui;

/**
 * Views for Basicapp panel.
 */
class BasicappPanelViews extends \Innomatic\Desktop\Panel\PanelViews
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

    /**
     * WUI XML definition for main panel content.
     *
     * @var string
     */
    protected $pageXml;

    /**
     * Panel toolbars.
     *
     * @var array
     * @access protected
     */
    protected $toolbars;

    /**
     * Optional status bar string.
     *
     * @var string
     */
    protected $status;

    /**
     * Observer update method for the controller.
     *
     * @param object $observable
     * @param string $arg
     */
    public function update($observable, $arg = '')
    {
        switch ($arg) {
            case 'status':
                $this->status = $this->controller->getAction()->status;
                break;
        }
    }

    /**
     * View begin helper.
     *
     * This method is executed before calling the method for the requested view.
     */
    public function beginHelper()
    {
        // Set Innomatic Container.
        //
        $this->container = \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer');

        // Set localization catalog.
        //
        $this->catalog = new \Innomatic\Locale\LocaleCatalog(
            'example-basic-app::panel',
            $this->container->getCurrentUser()->getLanguage()
        );

        // Set page toolbar icons.
        // Here we have two buttons (items list and add item), both in the "items" group.
        // Toolbar may have more than a group of button, separated by a divider.
        //
        // We define the toolbar in the begin helper since a view may add its
        // own toolbar buttons after the default ones.
        //
        $this->toolbars['items'] = [
            'itemslist' => [
                'label' => $this->catalog->getStr('items_list'),
                'themeimage' => 'listbulletleft',
                'action' => WuiEventsCall::buildEventsCallString('', [
                    [
                        'view',
                        'default',
                        []
                    ]
                ]),
                'horiz' => 'true'
            ],
            'newitem' => [
                'label' => $this->catalog->getStr('add_item'),
                'themeimage' => 'mathadd',
                'action' => WuiEventsCall::buildEventsCallString('', [
                    [
                        'view',
                        'additem',
                        []
                    ]
                ]),
                'horiz' => 'true'
            ]
        ];
    }

    /**
     * View end helper.
     *
     * This method is executed after calling the method for the requested view.
     */
    public function endHelper()
    {
        // If a page title has not been set by a view, we pass it the default
        // one.
        //
        if (!strlen($this->pageTitle)) {
            $this->pageTitle = $this->catalog->getStr('panel_title');
        }

        // InnomaticToolbar is an helper WUI widget that eases the creation of
        // top toolbar buttons.
        //
        $toolbars = [
            new WuiInnomaticToolbar('view', [
                'toolbars' => $this->toolbars,
                'toolbar' => 'true'
            ])
        ];

        // InnomaticPage is an helper WUI widget that eases the creation of the
        // panel layout, by handling:
        // - the panel title
        // - the panel icon in the panel toolbar
        // - the panel content (here given as a WUI XML definition through the
        //   WuiXML widget)
        // - the panel status
        // - the panel toolbars, split in groups
        //
        $page = new WuiInnomaticPage('page', [
            'pagetitle' => $this->pageTitle,
            'icon' => 'documentcopy',
            'maincontent' => $this->wuiPanelContent,
            'status' => $this->status,
            'toolbars' => $toolbars
        ]);

        // The wuiContainer is the main WUI container widget.
        // Panel widgets must be added as children of the WUI container.
        // Here we add the InnomaticPage widget.
        //
        $this->wuiContainer->addChild($page);
    }

    /**
     * Method for default view.
     *
     * This it the default view, it must be always defined.
     *
     * @param array $eventData WUI event data.
     * @access public
     * @return void
     */
    public function viewDefault($eventData)
    {
        // Get all the items from the database.
        //
        $basicApp = new \Examples\Basic\BasicClass();
        $items = $basicApp->findAllItems();

        // Check if there are items in the database.
        //
        if ($items->getNumberRows() == 0 && strlen($this->status) == 0) {
            $this->status = $this->catalog->getStr('no_items_status');
            return;
        }

        $country = new \Innomatic\Locale\LocaleCountry(
            $this->container->getCurrentUser()->getCountry()
        );

        // Build the items table headers.
        //
        $headers[0]['label'] = $this->catalog->getStr('name_header');
        $headers[1]['label'] = $this->catalog->getStr('date_header');
        $this->tpl->set('headers', $headers);
        
        $row = 0;

        // Add a row in the table for each item result.
        //
        while (!$items->eof) {
            // Build the date array from the table date in safe timestamp format.
            //
            $dateArray = $this->container->getCurrentTenant()->getDataAccess()->getDateArrayFromTimestamp($items->getFields('itemdate'));

            // Prepare WUI events calls for panel actions.
            //
            $editAction  = WuiEventsCall::buildEventsCallString('', [ [ 'view', 'edititem', ['id' => $items->getFields('id')] ] ]);
            $deleteAction = WuiEventsCall::buildEventsCallString('', [ [ 'view', 'default', [] ], [ 'action', 'deleteitem', ['id' => $items->getFields('id')] ] ]);

            $itemsArray[$row]['name'] = $items->getFields('name');
            $itemsArray[$row]['dateArray'] = $country->formatShortArrayDate($dateArray);
            $itemsArray[$row]['toolbar'] =  [
                'view' => [
                    'edit' => [
                        'label' => $this->catalog->getStr('edit_item_button'),
                        'themeimage' => 'pencil',
                        'horiz' => 'true',
                        'action' => $editAction],
                    'delete' => [
                        'label' => $this->catalog->getStr('delete_item_button'),
                        'needconfirm' => 'true',
                        'confirmmessage' => $this->catalog->getStr('delete_confirm_message'),
                        'themeimage' => 'trash',
                        'horiz' => 'true',
                        'action' => $deleteAction]
                ]];

            // Move to the next item in the data access result.
            //
            $items->moveNext();
            $row++;
        }
        
        $this->tpl->setArray('itemsArray', $itemsArray);
    }

    /**
     * Method for add item view.
     *
     * @param array $eventData WUI event data.
     * @access public
     * @return void
     */
    public function viewAdditem($eventData)
    {
        // Build the current date in Innomatic DateArray format.
        //
        $country = new \Innomatic\Locale\LocaleCountry(
            $this->container->getCurrentUser()->getCountry()
        );
        $currentDate = $country->getDateArrayFromUnixTimestamp(time());
        $this->tpl->set('currentDate', $currentDate);

        // Prepare WUI events calls for panel actions.
        //
        $addAction   = WuiEventsCall::buildEventsCallString('', [ [ 'view', 'default', [] ], [ 'action', 'additem', [] ] ]);
        $abortAction = WuiEventsCall::buildEventsCallString('', [ [ 'view', 'default', [] ] ]);

        $this->tpl->set('addAction',    $addAction);
        $this->tpl->set('addItemLabel', $this->catalog->getStr('additem_button'));
        $this->tpl->set('abortAction',  $abortAction);
        $this->tpl->set('abortLabel',   $this->catalog->getStr('abort_button'));
        
        // Build the status list.
        //
        $item       = new \Examples\Basic\BasicClass();
        $statusList = $item->getStatusList();

        $statusArray = [];
        foreach ($statusList as $statusId => $statusName) {
            $statusArray[$statusId] = $this->catalog->getStr($statusName.'_status');
        }

        $this->tpl->set('statusArray', $statusArray);
        $this->tpl->set('statusLabel', $this->catalog->getStr('status_label'));

        $this->tpl->set('nameLabel',        $this->catalog->getStr('name_label'));
        $this->tpl->set('descriptionLabel', $this->catalog->getStr('description_label'));
        $this->tpl->set('dateLabel',        $this->catalog->getStr('date_label'));
        $this->tpl->set('doneLabel',        $this->catalog->getStr('done_label'));
    }

    /**
     * Method for edit item view.
     *
     * @param array $eventData WUI event data.
     * @access public
     * @return void
     */
    public function viewEdititem($eventData)
    {
        // Prepare WUI events calls for panel actions.
        //
        $editAction  = WuiEventsCall::buildEventsCallString('', [ [ 'view', 'default', [] ], [ 'action', 'edititem', ['id' => $eventData['id']] ] ]);
        $abortAction = WuiEventsCall::buildEventsCallString('', [ [ 'view', 'default', [] ] ]);

        $this->tpl->set('editAction',    $editAction);
        $this->tpl->set('editItemLabel', $this->catalog->getStr('edit_item_button'));
        $this->tpl->set('abortAction',   $abortAction);
        $this->tpl->set('abortLabel',    $this->catalog->getStr('abort_button'));

        // Get item data.
        //
        $item        = new \Examples\Basic\BasicClass($eventData['id']);
        $name        = $item->getName();
        $description = $item->getDescription();
        $date        = $item->getDate();
        $status      = $item->getStatusId();

        $this->tpl->set('nameValue',        $name);
        $this->tpl->set('descriptionValue', $description);
        $this->tpl->set('dateValue',        $date);
        $this->tpl->set('statusValue',      $status);

        // Convert PHP boolean to WUI checkbox widget boolean (that is a string).
        //
        $done        = $item->getDone() === TRUE ? 'true' : 'false';
        $this->tpl->set('doneValue',        $done);

        // Build the status list.
        //
        $item       = new \Examples\Basic\BasicClass();
        $statusList = $item->getStatusList();

        $statusArray = [];
        foreach ($statusList as $statusId => $statusName) {
            $statusArray[$statusId] = $this->catalog->getStr($statusName.'_status');
        }

        $this->tpl->set('statusArray', $statusArray);
        $this->tpl->set('statusLabel', $this->catalog->getStr('status_label'));

        $this->tpl->set('nameLabel',        $this->catalog->getStr('name_label'));
        $this->tpl->set('descriptionLabel', $this->catalog->getStr('description_label'));
        $this->tpl->set('dateLabel',        $this->catalog->getStr('date_label'));
        $this->tpl->set('doneLabel',        $this->catalog->getStr('done_label'));
    }
}
