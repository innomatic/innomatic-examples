<?php

namespace Examples\Basic;

class BasicClass extends \Innomatic\Dataaccess\DataAccessObject {
    protected $container;
    protected $itemId;
    protected $description;
    protected $date;

    public function __construct($id = 0)
    {
        $this->container  = \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer');
        parent::__construct($this->container->getCurrentTenant()->getDataAccess());

        $this->itemId = $id;
    }

    public function addItem($description, $date)
    {
        $id = $this->dataAccess->getNextSequenceValue('example_basic_table_id_seq');

        $result = $this->update(
            'INSERT INTO example_basic_table (id, description, itemdate) '.
            'VALUES ('.
            $id.','.
            $this->dataAccess->formatText($description).','.
            $this->dataAccess->formatText($this->dataAccess->getTimestampFromDateArray($date)).
            ')'
        );

        if ($result) {
            $this->itemId      = $id;
            $this->description = $description;
            $this->date        = $date;
        }

        return $result;
    }

    public function findAllItems()
    {
        return $this->retrieve('SELECT * FROM example_basic_table');
    }

    public function getItem($id)
    {
        $item = $this->retrieve('SELECT * FROM example_basic_table WHERE id=$id');
    }

    public function getId()
    {
        return $this->itemId;
    }

    public function getDescription()
    {
        return $this->description;
    }

    public function getDate()
    {
        return $this->date;
    }

    public function setDescription($description)
    {
        if ($this->itemId != 0) {
            $this->update(
                'UPDATE example_basic_table '.
                'SET description='.$this->dataAccess->formatText($description).' '.
                "WHERE id={$this->itemId}"
            );

            $this->description = $description;
        }

        return $this;
    }

    public function setDate($date)
    {
        if ($this->itemId != 0) {
            $this->update(
                'UPDATE example_basic_table '.
                'SET itemdate='.$this->dataAccess->formatText(
                    $this->dataAccess->getTimestampFromDateArray($date)
                ).' '.
                "WHERE id={$this->itemId}"
            );

            $this->date = $date;
        }

        return $this;
    }

    public function delete()
    {
        if ($this->itemId != 0) {
            $this->update(
                'DELETE FROM example_basic_table '.
                'WHERE id='.$this->itemId
            );

            $this->itemId = 0;
            $this->date = '';
            $this->description = '';
        }

    }
}
