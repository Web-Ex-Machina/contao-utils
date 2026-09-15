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
		protected readonly ContaoFramework $framework
	) {
        $this->framework->initialize();
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
    public function syncData(?array $varValues, $strTable, $intParentId, $strParentField, $strForeignField, ?array $arrAdditionalIdsToKeep = [], ?array $arrAdditionalFieldsValues = [], ?array $arrAdditionalConfigValues = []): array
    {
        $arrIds = [];
        $stdModel = Model::getClassFromTable($strTable);

        // step 1 - update existing recipients, add new ones
        foreach ($varValues as $id) {
        	$arrConfig = array_merge([
        		$strParentField => $intParentId,
        		$strForeignField => $id,
        	],  $arrAdditionalConfigValues);

        	$objModel = $this->getModel($stdModel, $arrConfig);

            if (!$objModel) {
                $objModel = new $stdModel();
                $objModel->createdAt = time();
                $objModel->created_at = time();
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

        if (!empty($arrIdsToKeep)) {
            $sql = \sprintf(
                'DELETE FROM %s WHERE %s.%s = %s AND %s.%s NOT IN (%s)',
                $strTable,
                $strTable,
                $strParentField,
                $intParentId,
                $strTable,
                $strForeignField,
                implode(',', array_map('intval', $arrIdsToKeep))
            );
        } else {
        	$sql = \sprintf(
                'DELETE FROM %s WHERE %s.%s = %s',
                $strTable,
                $strTable,
                $strParentField,
                $intParentId,
            );
        }

        Database::getInstance()->prepare($sql)->execute();

        return $arrIds;
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
    public function syncDataString(?array $varValues, $strTable, $intParentId, $strParentField, $strForeignField, ?array $arrAdditionalIdsToKeep = [], ?array $arrAdditionalFieldsValues = [], ?array $arrAdditionalConfigValues = []): array
    {
        $arrIds = [];
        // Found Model class
        $stdModel = Model::getClassFromTable($strTable);

        // step 1 - update existing recipients, add new ones
        foreach ($varValues as $id) {
            $arrConfig = array_merge([
        		$strParentField => $intParentId,
        		$strForeignField => $id,
        	],  $arrAdditionalConfigValues);

        	$objModel = $this->getModel($stdModel, $arrConfig);

            if (!$objModel) {
                $objModel = new $stdModel();
                $objModel->createdAt = time();
                $objModel->created_at = time();
                $objModel->$strParentField = $intParentId;
                $objModel->$strForeignField = $id;
                if ($arrAdditionalFieldsValues) {
                    foreach ($arrAdditionalFieldsValues as $field => $value) {
                        $objModel->$field = $value;
                    }
                }
            }

            $objModel->tstamp = time();
            $objModel->save();
            $arrIds[] = $objModel->id;
        }

        // step 2 - remove all ids not in $varValues
        $arrIdsToKeep = array_merge($varValues, $arrAdditionalIdsToKeep);

        if (!empty($arrIdsToKeep)) {
            $sql = \sprintf(
                'DELETE FROM %s WHERE %s.%s = %s AND %s.%s NOT IN (%s)',
                $strTable,
                $strTable,
                $strParentField,
                $intParentId,
                $strTable,
                $strForeignField,
                implode('","', $arrIdsToKeep)
            );
        } else {
        	$sql = \sprintf(
                'DELETE FROM %s WHERE %s.%s = %s',
                $strTable,
                $strTable,
                $strParentField,
                $intParentId,
            );
        }

        Database::getInstance()->prepare($sql)->execute();

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

    /**
     * Delete rows
     * 
     * @param array
     * @param array
     **/
    protected function deleteRows(): void
    {
    	Database::getInstance()->prepare($sql)->execute();
    }
}