<vertgroup>
  <children>

    <form>
      <name>item</name>
      <args>
        <action><?=$editAction?></action>
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
                <label><?=$nameLabel?></label>
              </args>
            </label>

            <string row="0" col="1">
              <name>name</name>
              <args>
                <disp>action</disp>
                <size>30</size>
                <value><?=$nameValue?></value>
              </args>
            </string>

            <label row="1" col="0" halign="right">
              <args>
                <label><?=$descriptionLabel?></label>
              </args>
            </label>

            <text row="1" col="1">
              <name>description</name>
              <args>
                <disp>action</disp>
                <rows>4</rows>
                <cols>80</cols>
                <value><?=$descriptionValue?></value>
              </args>
            </text>

            <label row="2" col="0" halign="right">
              <args>
                <label><?=$dateLabel?></label>
              </args>
            </label>

            <date row="2" col="1">
              <name>date</name>
              <args>
                <disp>action</disp>
                <!-- This is how we pass arrays in the WUI XML definition.
                     We set the XML tag "type" attribute to "array" and process
                     the array with \Shared\Wui\WuiXml::enocde().
                -->
                <value type="array"><?=$dateValue?></value>
              </args>
            </date>

            <label row="3" col="0" halign="right">
              <args>
                <label><?=$doneLabel?></label>
              </args>
            </label>

            <checkbox row="3" col="1">
              <name>done</name>
              <args>
                <disp>action</disp>
                <checked><?=$doneValue?></checked>
              </args>
            </checkbox>

            <label row="4" col="0" halign="right">
              <args>
                <label><?=$statusLabel?></label>
              </args>
            </label>

            <combobox row="4" col="1">
              <name>statusid</name>
              <args>
                <disp>action</disp>
                <elements type="array"><?=$statusArray?></elements>
                <default><?=$statusValue?></default>
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
                <label><?=$editItemLabel?></label>
                <horiz>true</horiz>
                <formsubmit>item</formsubmit>
                <action><?=$editAction?></action>
                <mainaction>true</mainaction>
              </args>
            </button>

            <button>
              <args>
                <themeimage>buttoncancel</themeimage>
                <label><?=$abortLabel?></label>
                <horiz>true</horiz>
                <action><?=$abortAction?></action>
              </args>
            </button>

          </children>
        </horizgroup>

      </children>
    </form>

  </children>
</vertgroup>