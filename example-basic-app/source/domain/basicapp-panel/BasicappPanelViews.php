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
            'maincontent' => new WuiXml('content', [
                'definition' => $this->pageXml
            ]),
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
        $headers[0]['label'] = $this->catalog->getStr('description_header');
        $headers[1]['label'] = $this->catalog->getStr('date_header');

        $this->pageXml = '
<table>
  <args>
    <headers type="array">'.WuiXml::encode($headers).'</headers>
  </args>
  <children>';

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

            $this->pageXml .= '
    <label row="'.$row.'" col="0">
      <args>
        <label>'.WuiXml::cdata($items->getFields('description')).'</label>
      </args>
    </label>
    <label row="'.$row.'" col="1">
      <args>
        <label>'.WuiXml::cdata($country->formatShortArrayDate($dateArray)).'</label>
      </args>
    </label>
    <innomatictoolbar row="'.$row.'" col="2">
      <args>
        <frame>false</frame>
        <toolbars type="array">'.WuiXml::encode([
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
            ]]).'</toolbars>
      </args>
    </innomatictoolbar>
';

            // Move to the next item in the data access result.
            //
            $items->moveNext();
            $row++;
        }

        $this->pageXml .= '
  </children>
</table>
';
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

        // Prepare WUI events calls for panel actions.
        //
        $addAction   = WuiEventsCall::buildEventsCallString('', [ [ 'view', 'default', [] ], [ 'action', 'additem', [] ] ]);
        $abortAction = WuiEventsCall::buildEventsCallString('', [ [ 'view', 'default', [] ] ]);

        // Build the status list.
        //
        $item       = new \Examples\Basic\BasicClass();
        $statusList = $item->getStatusList();

        $statusArray = [];
        foreach ($statusList as $statusId => $statusName) {
            $statusArray[$statusId] = $this->catalog->getStr($statusName.'_status');
        }

        $this->pageXml = '
<vertgroup>
  <children>

    <form>
      <name>item</name>
      <args>
        <action>'.WuiXml::cdata($addAction).'</action>
      </args>
      <children>

        <grid>
          <children>

            <!-- Grid children must declare their position (row and column) as
                 first and second argument. Argument names are not relevant, but
                 their order is.

                 Grid also supports horizontal and vertical alignment as third
                 and fourth optional arguments.
            -->
            <label row="0" col="0" halign="right">
              <args>
                <label>'.WuiXml::cdata($this->catalog->getStr('description_label')).'</label>
              </args>
            </label>

            <text row="0" col="1">
              <name>description</name>
              <args>
                <disp>action</disp>
                <rows>4</rows>
                <cols>80</cols>
              </args>
            </text>

            <label row="1" col="0" halign="right">
              <args>
                <label>'.WuiXml::cdata($this->catalog->getStr('date_label')).'</label>
              </args>
            </label>

            <date row="1" col="1">
              <name>date</name>
              <args>
                <disp>action</disp>
                <!-- This is how we pass arrays in the WUI XML definition.
                     We set the XML tag "type" attribute to "array" and process
                     the array with \Shared\Wui\WuiXml::enocde().
                -->
                <value type="array">'.WuiXml::encode($currentDate).'</value>
              </args>
            </date>

            <label row="2" col="0" halign="right">
              <args>
                <label>'.WuiXml::cdata($this->catalog->getStr('done_label')).'</label>
              </args>
            </label>

            <checkbox row="2" col="1">
              <name>done</name>
              <args>
                <disp>action</disp>
              </args>
            </checkbox>

            <label row="3" col="0" halign="right">
              <args>
                <label>'.WuiXml::cdata($this->catalog->getStr('status_label')).'</label>
              </args>
            </label>

            <combobox row="3" col="1">
              <name>statusid</name>
              <args>
                <disp>action</disp>
                <elements type="array">'.WuiXml::encode($statusArray).'</elements>
              </args>
            </combobox>

          </children>
        </grid>

        <horizbar />

        <horizgroup>
          <args>
            <width>0%</width>
          </args>
          <children>

            <button>
              <args>
                <themeimage>buttonok</themeimage>
                <label>'.WuiXml::cdata($this->catalog->getStr('additem_button')).'</label>
                <horiz>true</horiz>
                <formsubmit>item</formsubmit>
                <action>'.WuiXml::cdata($addAction).'</action>
                <mainaction>true</mainaction>
              </args>
            </button>

            <button>
              <args>
                <themeimage>buttoncancel</themeimage>
                <label>'.WuiXml::cdata($this->catalog->getStr('abort_button')).'</label>
                <horiz>true</horiz>
                <action>'.WuiXml::cdata($abortAction).'</action>
              </args>
            </button>

          </children>
        </horizgroup>

      </children>
    </form>

  </children>
</vertgroup>';
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
        // Build the current date in Innomatic DateArray format.
        //
        $country = new \Innomatic\Locale\LocaleCountry(
            $this->container->getCurrentUser()->getCountry()
        );
        $currentDate = $country->getDateArrayFromUnixTimestamp(time());

        // Prepare WUI events calls for panel actions.
        //
        $editAction  = WuiEventsCall::buildEventsCallString('', [ [ 'view', 'default', [] ], [ 'action', 'edititem', ['id' => $eventData['id']] ] ]);
        $abortAction = WuiEventsCall::buildEventsCallString('', [ [ 'view', 'default', [] ] ]);

        // Get item data.
        //
        $item        = new \Examples\Basic\BasicClass($eventData['id']);
        $description = $item->getDescription();
        $date        = $item->getDate();
        $status      = $item->getStatusId();

        // Convert PHP boolean to WUI checkbox widget boolean (that is a string).
        //
        $done        = $item->getDone() === TRUE ? 'true' : 'false';

        // Build the status list.
        //
        $item       = new \Examples\Basic\BasicClass();
        $statusList = $item->getStatusList();

        $statusArray = [];
        foreach ($statusList as $statusId => $statusName) {
            $statusArray[$statusId] = $this->catalog->getStr($statusName.'_status');
        }

        $this->pageXml = '
<vertgroup>
  <children>

    <form>
      <name>item</name>
      <args>
        <action>'.WuiXml::cdata($editAction).'</action>
      </args>
      <children>

        <grid>
          <children>

            <!-- Grid children must declare their position (row and column) as
                 first and second argument. Argument names are not relevant, but
                 their order is.

                 Grid also supports horizontal and vertical alignment as third
                 and fourth optional arguments.
            -->
            <label row="0" col="0" halign="right">
              <args>
                <label>'.WuiXml::cdata($this->catalog->getStr('description_label')).'</label>
              </args>
            </label>

            <text row="0" col="1">
              <name>description</name>
              <args>
                <disp>action</disp>
                <rows>4</rows>
                <cols>80</cols>
                <value>'.WuiXml::cdata($description).'</value>
              </args>
            </text>

            <label row="1" col="0" halign="right">
              <args>
                <label>'.WuiXml::cdata($this->catalog->getStr('date_label')).'</label>
              </args>
            </label>

            <date row="1" col="1">
              <name>date</name>
              <args>
                <disp>action</disp>
                <!-- This is how we pass arrays in the WUI XML definition.
                     We set the XML tag "type" attribute to "array" and process
                     the array with \Shared\Wui\WuiXml::enocde().
                -->
                <value type="array">'.WuiXml::encode($date).'</value>
              </args>
            </date>

            <label row="2" col="0" halign="right">
              <args>
                <label>'.WuiXml::cdata($this->catalog->getStr('done_label')).'</label>
              </args>
            </label>

            <checkbox row="2" col="1">
              <name>done</name>
              <args>
                <disp>action</disp>
                <checked>'.$done.'</checked>
              </args>
            </checkbox>

            <label row="3" col="0" halign="right">
              <args>
                <label>'.WuiXml::cdata($this->catalog->getStr('status_label')).'</label>
              </args>
            </label>

            <combobox row="3" col="1">
              <name>statusid</name>
              <args>
                <disp>action</disp>
                <elements type="array">'.WuiXml::encode($statusArray).'</elements>
                <default>'.$status.'</default>
              </args>
            </combobox>

          </children>
        </grid>

        <horizbar />

        <horizgroup>
          <args>
            <width>0%</width>
          </args>
          <children>

            <button>
              <args>
                <themeimage>buttonok</themeimage>
                <label>'.WuiXml::cdata($this->catalog->getStr('edit_item_button')).'</label>
                <horiz>true</horiz>
                <formsubmit>item</formsubmit>
                <action>'.WuiXml::cdata($editAction).'</action>
                <mainaction>true</mainaction>
              </args>
            </button>

            <button>
              <args>
                <themeimage>buttoncancel</themeimage>
                <label>'.WuiXml::cdata($this->catalog->getStr('abort_button')).'</label>
                <horiz>true</horiz>
                <action>'.WuiXml::cdata($abortAction).'</action>
              </args>
            </button>

          </children>
        </horizgroup>

      </children>
    </form>

  </children>
</vertgroup>';
    }

}
