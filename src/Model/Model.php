<?php

declare(strict_types=1);

/**
 * Contao Utilities for Contao Open Source CMS
 * Copyright (c) 2019-2023 Web ex Machina
 *
 * @category ContaoBundle
 * @package  Web-Ex-Machina/contao-utils
 * @author   Web ex Machina <contact@webexmachina.fr>
 * @link     https://github.com/Web-Ex-Machina/contao-utils/
 */

namespace WEM\UtilsBundle\Model;

use Exception;
use Contao\Database;
use WEM\UtilsBundle\Classes\QueryBuilder;

abstract class Model extends \Contao\Model
{
    /**
     * Default order column
     *
     * @var string
     */
    protected static $strOrderColumn = "createdAt DESC";

    /**
     * Find items, depends on the arguments.
     *
     * @param array $arrConfig  [Request Config]
     * @param int   $intLimit   [Query Limit]
     * @param int   $intOffset  [Query Offset]
     * @param array $arrOptions [Query Options]
     *
     * @return \Contao\Model\Collection
     */
    public static function findItems($arrConfig = [], $intLimit = 0, $intOffset = 0, array $arrOptions = [])
    {
        try {
            $t = static::$strTable;
            $arrColumns = static::formatColumns($arrConfig);

            if ($intLimit > 0) {
                $arrOptions['limit'] = $intLimit;
            }

            if ($intOffset > 0) {
                $arrOptions['offset'] = $intOffset;
            }

            if (!isset($arrOptions['order'])) {
                $arrOptions['order'] = $t . "." . static::$strOrderColumn;
            }

            if (empty($arrColumns)) {
                return static::findAll($arrOptions);
            }

            return static::findBy($arrColumns, null, $arrOptions);
        } catch (Exception $e) {
            throw $e;
        }
    }

    /**
     * Count items, depends on the arguments.
     *
     * @param array [Request Config]
     * @param array [Query Options]
     *
     * @return int
     */
    public static function countItems($arrConfig = [], array $arrOptions = [])
    {
        try {
            $t = static::$strTable;
            $arrColumns = static::formatColumns($arrConfig);

            if (empty($arrColumns)) {
                return static::countAll($arrOptions);
            }

            return static::countBy($arrColumns, null, $arrOptions);
        } catch (Exception $e) {
            throw $e;
        }
    }

    /**
     * Format ItemModel columns.
     *
     * @param array $arrConfig [Configuration to format]
     *
     * @return array
     */
    public static function formatColumns($arrConfig)
    {
        try {
            $t = static::$strTable;
            $arrColumns = [];

            foreach ($arrConfig as $c => $v) {
                $arrColumns = array_merge($arrColumns, static::formatStatement($c, $v));
            }

            if ($arrConfig['not']) {
                $arrColumns[] = $arrConfig['not'];
            }

            return $arrColumns;
        } catch (Exception $e) {
            throw $e;
        }
    }

    /**
     * Format Search statement.
     *
     * @param string $varValue [Value to use]
     *
     * @return string
     */
    public static function formatSearchStatement($strField, $varValue)
    {
        $t = static::$strTable;

        switch ($strField) {
            default:
                return "$t.$strField REGEXP '$varValue'";
        }
    }

    /**
     * Generic statements format.
     *
     * @param string $strField    [Column to format]
     * @param mixed  $varValue    [Value to use]
     * @param string $strOperator [Operator to use, default "="]
     *
     * @return array
     */
    public static function formatStatement($strField, $varValue, $strOperator = '=')
    {
        $arrColumns = [];
        $t = static::$strTable;

        switch ($strField) {
            // Integer fields
            case 'pid':
                $arrColumns[] = sprintf("$t.%s = %s", $strField, $varValue);
                break;

            // Search in table
            case 'search':
                $arrSearchColumns = [];
                if (\is_array($varValue) && \array_key_exists('column', $varValue)) {
                    $arrSearchColumns[] = $varValue['column'];
                    $arrSearchKeywords = $varValue['keywords'];
                } elseif (static::$arrSearchFields) {
                    $arrSearchColumns = static::$arrSearchFields;
                    $arrSearchKeywords = $varValue;
                } else {
                    break;
                }

                if (!empty($arrSearchColumns)) {
                    $k = is_array($arrSearchKeywords) ? implode('|', $arrSearchKeywords) : $arrSearchKeywords;
                    $arrKeywords = [];
                    foreach ($arrSearchColumns as $f) {
                        $arrKeywords[] = static::formatSearchStatement($f, $k);
                    }
                    $arrColumns[] = '('.implode(' OR ', $arrKeywords).')';
                }

                break;

            // Wizard for active items
            case 'active':
                if (1 === $varValue) {
                    $arrColumns[] = "$t.isActive = 1 AND ($t.isActiveAt = 0 OR $t.isActiveAt <= ".time().") AND ($t.isActiveUntil = 0 OR $t.isActiveUntil >= ".time().')';
                } elseif (-1 === $varValue) {
                    $arrColumns[] = "$t.isActive = '' AND ($t.isActiveAt = 0 OR $t.isActiveAt >= ".time().") AND ($t.isActiveUntil = 0 OR $t.isActiveUntil <= ".time().')';
                }
                break;

            case 'invisible':
                if (1 === $varValue) {
                    $arrColumns[] = "$t.invisible = 1";
                } elseif (-1 === $varValue) {
                    $arrColumns[] = "$t.invisible = ''";
                }
                break;

            // Checkboxes
            case 'isActive':
                if (1 === $varValue) {
                    $arrColumns[] = "$t.$strField = 1";
                } elseif (-1 === $varValue) {
                    $arrColumns[] = "$t.$strField = ''";
                }
                break;

            // Dates
            case 'tstamp':
            case 'createdAt':
            case 'isActiveAt':
            case 'isActiveUntil':
                $arrColumns[] = sprintf("$t.%s %s %s", $strField, $strOperator, $varValue);
                break;

            // Inline wheres
            case 'where':
                $arrColumns = array_merge($arrColumns, $varValue);
                break;

            // Default behaviour
            case 'ptable':
            default:
                $arrColumns[] = sprintf("$t.%s %s '%s'", $strField, $strOperator, \addslashes((string) $varValue));
        }

        return $arrColumns;
    }

    /**
     * Find only the items available, for filters.
     *
     * @param string $strField [Column]
     *
     * @return \Contao\Model\Collection
     */
    public static function findItemsGroupByOneField($strField)
    {
        try {
            $t = static::$strTable;
            $objResults = Database::getInstance()->prepare("SELECT $t.$strField FROM $t GROUP BY $t.$strField")->execute();

            return $objResults;
        } catch (Exception $e) {
            throw $e;
        }
    }

    /**
     * Build a query based on the given options.
     *
     * @param array $arrOptions The options array
     *
     * @return string The query string
     */
    protected static function buildFindQuery(array $arrOptions)
    {
        return QueryBuilder::find($arrOptions);
    }

    /**
     * Build a query based on the given options to count the number of records.
     *
     * @param array $arrOptions The options array
     *
     * @return string The query string
     */
    protected static function buildCountQuery(array $arrOptions)
    {
        return QueryBuilder::count($arrOptions);
    }
}
