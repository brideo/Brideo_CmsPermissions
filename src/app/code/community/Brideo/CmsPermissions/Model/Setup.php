<?php

class Brideo_CmsPermissions_Model_Setup extends Mage_Eav_Model_Entity_Setup
{

    protected $coreBlockArray = array();
    protected $coreVariableArray = array();

    /**
     * Get the current blocks in the permissions
     * table.
     *
     * @return array
     */
    public function getCoreBlockArray()
    {
        if(!count($this->coreBlockArray)) {
            $sql = $this->getConnection()->select()
                ->from(
                    $this->getTable('admin/permission_block'),
                    'block_name'
                );

            foreach ($this->getConnection()->fetchAll($sql) as $item) {
                $this->coreBlockArray[] = $item['block_name'];
            }
        }

        return $this->coreBlockArray;
    }

    /**
     * Get the current variables in the permissions
     * table.
     *
     * @return array
     */
    public function getCoreVariableArray()
    {
        if(!count($this->coreVariableArray)) {
            $sql = $this->getConnection()->select()
                ->from(
                    $this->getTable('admin/permission_variable'),
                    'variable_name'
                );

            foreach ($this->getConnection()->fetchAll($sql) as $item) {
                $this->coreVariableArray[] = $item['variable_name'];
            }
        }

        return $this->coreVariableArray;
    }

    /**
     * Return a type block array for the passed in string.
     *
     * @param string $content
     *
     * @return array
     */
    public function getVariableBlockArray($content)
    {
        preg_match_all(Varien_Filter_Template::CONSTRUCTION_PATTERN, $content, $constructions, PREG_SET_ORDER);

        $typeBlockArray = array(
            'config' => array(),
            'block'  => array()
        );

        $insertArray = array(
            'config' => array(),
            'block'  => array()
        );

        $coreArrays = array(
            'block' => $this->getCoreBlockArray(),
            'config' => $this->getCoreVariableArray()

        );

        $keys = array(
            'block' => array(
                'directive' => 'type',
                'prefix' => 'block'
            ),
            'config' => array(
                'directive' => 'path',
                'prefix' => 'variable'
            )
        );

        foreach ($constructions as $construction) {

            foreach ($keys as $permission => $key) {
                if ($construction[1] == $permission) {
                    preg_match('/' . $key['directive'] . '=\"([^"]*)\"/', $construction[0], $permissions, PREG_OFFSET_CAPTURE, 3);

                    if ($this->isItemAvailable($permissions, $typeBlockArray, $coreArrays[$permission], $permission)) {
                        $typeBlockArray[$permission][] = $permissions[1][0];
                        $insertArray[$permission][] = array("{$key['prefix']}_name" => $permissions[1][0], 'is_allowed' => 1);
                    }
                }
            }
        }

        return $insertArray;
    }

    /**
     * Is the item set, make sure it has not been
     * set already and make sure it has not been set
     * by Magento core.
     *
     *
     * @param array  $array
     * @param array  $typeBlockArray
     * @param array  $coreBlockArray
     * @param string $key
     *
     * @return bool
     */
    protected function isItemAvailable($array, $typeBlockArray, $coreBlockArray, $key)
    {
        return $array[1][0] &&
        !in_array($array[1][0], $typeBlockArray[$key]) &&
        !in_array($array[1][0], $coreBlockArray)
        && $array[1][0] !== '';
    }
}
