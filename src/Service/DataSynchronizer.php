<?php

declare(strict_types=1);

namespace WEM\UtilsBundle\Service;

use Contao\CoreBundle\Framework\ContaoFramework;
use Contao\Database;
use Contao\Model;

class DataSynchronizer
{
	protected string $table;

	public function __construct(
	) {
    }

    public function __set($name, $value): void
    {
    	switch ($name) {
    		default:
    			$this->{$name} = $value;
    	}
    }

    public function __get($name): mixed
    {
    	switch ($name) {
    		default:
    			return $this->{$name};
    	}
    }

    /**
     * Sync basic data between pivot tables.
     *
     * @param array  $varValues       [Usually an array of IDs]
     * @param string $strTable        [Table where to sync]
     * @param int    $intParentId     [Parent ID]
     * @param string $strParentField  [Parent Field]
     * @param string $strForeignField [Foreign field where to sync values]
     */
    public function syncData(
        ?array $varValues,
        string $strTable,
        int|string $intParentId,
        string $strParentField,
        string $strForeignField,
        ?array $arrAdditionalIdsToKeep = [],
        ?array $arrAdditionalFieldsValues = [],
        ?array $arrAdditionalConfigValues = [],
    ): array {
        $arrIds = [];
        $stdModel = Model::getClassFromTable($strTable);
        $intParentId = (int) $intParentId;

        // step 1 - update existing recipients, add new ones
        foreach ($varValues as $id) {
        	$arrConfig = array_merge([
        		$strParentField => $intParentId,
        		$strForeignField => $id,
        	],  $arrAdditionalConfigValues);

        	$objModel = $this->getModel($stdModel, $arrConfig);

            if (!$objModel) {
                $objModel = new $stdModel();
                $objModel->$strParentField = $intParentId;
                $objModel->$strForeignField = $id;
            }

            if ($arrAdditionalFieldsValues) {
                foreach ($arrAdditionalFieldsValues as $field => $value) {
                    $objModel->$field = $value;
                }
            }

            $objModel->tstamp = time();
            $objModel->save();

            $arrIds[] = $objModel->id;
        }

        // step 2 - remove all ids not in $varValues
        $arrIdsToKeep = array_merge($varValues, $arrAdditionalIdsToKeep);

        $arrConfig = array_merge([
            $strParentField => $intParentId,
        ],  $arrAdditionalConfigValues);

        if (!empty($arrIdsToKeep)) {
            $arrConfig['where'][] = \sprintf(
                '%s.%s NOT IN (%s)',
                $strTable,
                $strForeignField,
                implode(',', array_map('intval', $arrIdsToKeep))
            );
        }

        if (method_exists($stdModel, 'findItems')) {
            $objItems = $stdModel::findItems($arrConfig);
        } else {
            $arrWheres = [];
            $arrValues = [];
            foreach ($arrConfig as $c => $v) {
                switch ($c) {
                    case 'where':
                        foreach ($v as $w) {
                            $arrWheres[] = $w;
                            $arrValues[] = "";
                        }
                    break;
                    default:
                        $arrWheres[] = $c.' = ?';
                        $arrValues[] = $v;
                }
            }

            $objItems = $stdModel::findBy($arrWheres, $arrValues);
        }

        if ($objItems && 0 < $objItems->count()) {
            while ($objItems->next()) {
                $objItems->delete();
            }
        }

        return $arrIds;
    }

    /**
     * Return model
     * 
     * @param string
     * @param array
     * 
     * @return Model|null
     */
    protected function getModel(string $stdModel, array $arrColumns): ?Model
    {
        if (method_exists($stdModel, 'findItems')) {
            $objModels = $stdModel::findItems($arrColumns, 1);
            $objModel = $objModels ? $objModels->current() : null;
        } else {
            $arrWheres = [];
            $arrValues = [];
            foreach ($arrColumns as $c => $v) {
                $arrWheres[] = $c.' = ?';
                $arrValues[] = $v;
            }

            $objModel = $stdModel::findOneBy($arrWheres, $arrValues);
        }

        return $objModel;
    }
}