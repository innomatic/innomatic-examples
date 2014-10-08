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
     * Date is in Innomatic date array.
     *
     * @var array
     */
    protected $date;

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

    public function addItem($description, $date)
    {
        // Get a sequence number for the new item.
        //
        $id = $this->dataAccess->getNextSequenceValue('example_basic_table_id_seq');

        // Insert the new item in the database.
        // We use the parent class (DataAccessObject) update() method.
        //
        $result = $this->update(
            'INSERT INTO example_basic_table (id, description, itemdate) '.
            'VALUES ('.
            $id.','.
            $this->dataAccess->formatText($description).','.
            $this->dataAccess->formatText($this->dataAccess->getTimestampFromDateArray($date)).
            ')'
        );

        // If the query has been successful, set the object attributes.
        //
        if ($result) {
            $this->itemId      = $id;
            $this->description = $description;
            $this->date        = $date;
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
            $this->description = $item->getFields('description');
            $this->date = $this->dataAccess->getDateArrayFromTimestamp($item->getFields('itemdate'));
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
     * Sets the item description.
     *
     * This method checks if the item object is valid.
     *
     * @param string $description Item description.
     * @return \Examples\Basic\BasicClass The item object itself.
     */
    public function setDescription($description)
    {
        if ($this->itemId != 0) {
            // Update the database row.
            //
            $this->update(
                'UPDATE example_basic_table '.
                'SET description='.$this->dataAccess->formatText($description).' '.
                "WHERE id={$this->itemId}"
            );

            // Set the object attribute.
            //
            $this->description = $description;
        }

        return $this;
    }

    /**
     * Sets the item date.
     *
     * This method checks if the item object is valid.
     *
     * @param array $date Date in Innomatic array date format.
     * @return \Examples\Basic\BasicClass The item object itself.
     */
    public function setDate($date)
    {
        if ($this->itemId != 0) {
            // Update the database row.
            //
            $this->update(
                'UPDATE example_basic_table '.
                'SET itemdate='.$this->dataAccess->formatText(
                    $this->dataAccess->getTimestampFromDateArray($date)
                ).' '.
                "WHERE id={$this->itemId}"
            );

            // Set the object attribute.
            //
            $this->date = $date;
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
            $this->itemId = 0;
            $this->date = '';
            $this->description = '';
        }

    }
}
