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
        // Here we add the InnomaticPage widget
        //
        $this->wuiContainer->addChild($page);
    }

    /**
     * Method for default view.
     *
     * This it the default view, it must be always defined.
     *
     * @param array $eventData WUI event data.
     */
    public function viewDefault($eventData)
    {
        $basicApp = new \Examples\Basic\BasicClass();
        $items = $basicApp->findAllItems();

        $this->pageXml = '';
    }

    public function viewAdditem($eventData)
    {
        $country = new \Innomatic\Locale\LocaleCountry(
            $this->container->getCurrentUser()->getCountry()
        );

        $addAction   = WuiEventsCall::buildEventsCallString('', [ [ 'view', 'default', [] ], [ 'action', 'additem', [] ] ]);
        $abortAction = WuiEventsCall::buildEventsCallString('', [ [ 'view', 'default', [] ] ]);
        $currentDate = $country->getDateArrayFromUnixTimestamp(time());

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
}