<?php

namespace Examples\Basic;

/**
 * Example of a basic class with CRUD operations.
 *
 * In this example we use the DataAccessObject pattern.
 *
 * Innomatic provides a DAO abstraction with the
 * \Innomatic\Dataaccess\DataAccessObject class.
 */
class BasicClass extends \Innomatic\Dataaccess\DataAccessObject {
    /**
     * Innomatic container.
     *
     * @var \Innomatic\Core\InnomaticContainer
     */
    protected $container;

    /**
     * Item internal identifier number.
     *
     * @var integer
     */
    protected $itemId;

    /**
     * Item description.
     *
     * @var string
     */
    protected $description;

    /**
     * Item date.
     *
     * Date is in Innomatic date array format.
     *
     * @var array
     */
    protected $date;

    /**
     * Item done flag.
     *
     * @var boolean
     * @access protected
     */
    protected $done = FALSE;

    /**
     * Class constructor.
     *
     * @param number $id Optional item identifier number.
     */
    public function __construct($id = 0)
    {
        $this->container  = \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer');
        parent::__construct($this->container->getCurrentTenant()->getDataAccess());

        $this->itemId = $id;

        // If an item id has been given during object creation,
        // fetch the item data from the database.
        //
        if ($id != 0) {
            $this->getItem($id);
        }
    }

    /**
     * Adds a new item to the database and initializes the current object.
     *
     * @param string $description Item description.
     * @param array $date Item date in Innomatic date array format.
     * @access public
     * @return void
     */
    public function addItem($description, $date, $done)
    {
        // Get a sequence number for the new item.
        //
        $id = $this->dataAccess->getNextSequenceValue('example_basic_table_id_seq');

        // Format a string for SQL.
        //
        $descriptionValue = $this->dataAccess->formatText($description);

        // Convert an Innomatic date array to a database safe timestamp.
        //
        $dateValue = $this->dataAccess->formatText($this->dataAccess->getTimestampFromDateArray($date));

        // Convert a PHP boolean to a database safe boolean field and also
        // format it since Innomatic database booleans are strings.
        //
        $doneValue = $done === TRUE ? $this->dataAccess->fmttrue : $this->dataAccess->fmtfalse;
        $doneValue = $this->dataAccess->formatText($doneValue);

        // Insert the new item in the database.
        // We use the parent class (DataAccessObject) update() method.
        //
        $result = $this->update(
            'INSERT INTO example_basic_table '.
            '(id, description, itemdate, done) '.
            'VALUES ('.
            $id.','.
            $description.','.
            $dateValue.','.
            $doneValue.
            ')'
        );

        // If the query has been successful, initialize the object attributes.
        //
        if ($result) {
            $this->itemId      = $id;
            $this->description = $description;
            $this->date        = $date;
            $this->done        = $done;
        }

        return $result;
    }

    /**
     * Retrieves all the items from the database.
     *
     * @return \Innomatic\Dataaccess\DataAccessResult Items.
     */
    public function findAllItems()
    {
        return $this->retrieve('SELECT * FROM example_basic_table');
    }

    /**
     * Retrieves the content of an item and set the object attributes.
     *
     * @param integer $id
     */
    public function getItem($id)
    {
        $item = $this->retrieve("SELECT * FROM example_basic_table WHERE id=$id");

        if ($item->getNumberRows() == 1) {
            // This is a string, no need to process it.
            //
            $this->description = $item->getFields('description');

            // Convert the database safe timestamp field to an Innomatic date
            // array.
            //
            $this->date        = $this->dataAccess->getDateArrayFromTimestamp(
                $item->getFields('itemdate')
            );

            // Convert a database safe boolean field to a PHP boolean.
            // We use a switch in place of a ternary operator in order to show
            // both database formats.
            //
            switch ($item->getFields('done')) {
            case $this->dataAccess->fmttrue:
                $this->done = TRUE;
                break;

            case $this->dataAccess->fmtfalse:
                $this->done = FALSE;
                break;

            default:
                $this->done = FALSE;
            }
        }
    }

    /**
     * Returns item identifier number.
     *
     * @return integer
     */
    public function getId()
    {
        return $this->itemId;
    }

    /**
     * Returns item description.
     *
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Returns item date in Innomatic date array format.
     *
     * @return array
     */
    public function getDate()
    {
        return $this->date;
    }

    /**
     * Returns item done flag.
     *
     * @return boolean
     */
    public function getDone()
    {
        return $this->done;
    }

    /**
     * Sets the item description.
     *
     * @param string $description Item description.
     * @return \Examples\Basic\BasicClass The item object itself.
     */
    public function setDescription($description)
    {
        // Set the object attribute.
        //
        $this->description = $description;

        return $this;
    }

    /**
     * Sets the item date.
     *
     * @param array $date Date in Innomatic array date format.
     * @return \Examples\Basic\BasicClass The item object itself.
     */
    public function setDate($date)
    {
        // Set the object attribute.
        //
        $this->date = $date;

        return $this;
    }

    /**
     * Sets the item done flag.
     *
     * @param boolean $done Item done flag.
     * @return \Examples\Basic\BasicClass The item object itself.
     */
    public function setDone($done)
    {
        // Set the object attribute.
        //
        $this->done = $done;

        return $this;
    }

    /**
     * This method stores the object in the database.
     *
     * It must be called after changing one or more object attributes.
     *
     * This method checks if the item object is valid.
     *
     * @access public
     * @return \Examples\Basic\BasicClass The item object itself.
     */
    public function store()
    {
        if ($this->itemId != 0) {
            // Prepare the values.

            // Convert an Innomatic date array to a database safe timestamp.
            //
            $itemDate = $this->dataAccess->formatText(
                $this->dataAccess->getTimestampFromDateArray($this->date)
            );

            // Format a string for SQL.
            //
            $description = $this->dataAccess->formatText($this->description);

            // Convert a PHP boolean to a database safe boolean field and also
            // format it since Innomatic database booleans are strings.
            //
            $done = $this->done === TRUE ? $this->dataAccess->fmttrue : $this->dataAccess->fmtfalse;
            $done = $this->dataAccess->formatText($done);

            // Update the database row.
            //
            $this->update(
                'UPDATE example_basic_table '.
                'SET '.
                "itemdate    = $itemDate, ".
                "done        = $done, ".
                "description = $description ".
                "WHERE id={$this->itemId}"
            );
        }

        return $this;
    }

    /**
     * Deletes the current item from the database.
     *
     * @return void
     */
    public function delete()
    {
        if ($this->itemId != 0) {
            // Remove the item row from the database.
            //
            $this->update(
                'DELETE FROM example_basic_table '.
                'WHERE id='.$this->itemId
            );

            // Empty the item attributes.
            //
            $this->itemId      = 0;
            $this->date        = '';
            $this->description = '';
            $this->done        = FALSE;
        }

    }
}
