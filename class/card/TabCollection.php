<?php
/**
 *
 */

/**
 * TabCollection class
 *
 * @author Laurent De Coninck <lau.deconinck@gmail.com>
 */
class TabCollection implements \Countable
{

    /**
     * @var Tab[]
     */
    private $tabs;

    /**
     * TabCollection constructor.
     *
     * @param Tab[] $tabs
     */
    public function __construct(array $tabs = [])
    {
        $this->tabs = $tabs;
    }

    /**
     * Count elements of an object
     * @link  http://php.net/manual/en/countable.count.php
     * @return int The custom count as an integer.
     * </p>
     * <p>
     * The return value is cast to an integer.
     * @since 5.1.0
     */
    public function count()
    {
        return count($this->tabs);
    }

    /**
     * @param Tab $tab
     *
     * @return TabCollection
     */
    public function addTab(Tab $tab){
        $this->tabs[] = $tab;
    }

    /**
     * @return array
     */
    public function toArray(){
        $tabAsArray = [];

        foreach ($this->tabs as $tab){
            $tabAsArray[] = $tab->toArray();
        }

        return $tabAsArray;
    }
}