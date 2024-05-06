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
use Contao\Database\Result;
use Contao\Database\Statement;
use Contao\Model\Collection;
use WEM\UtilsBundle\Classes\QueryBuilder;

abstract class Model extends \Contao\Model
{
    /**
     * Default order column
     */
    protected static string $strOrderColumn = "createdAt DESC";

    private static array $arrSearchFields = [];

    /**
     * Find items, depends on the arguments.
     *
     * @param array $arrConfig [Request Config]
     * @param int $intLimit [Query Limit]
     * @param int $intOffset [Query Offset]
     * @param array $arrOptions [Query Options]
     *
     * @throws Exception
     */
    public static function findItems(
        array $arrConfig = [], int $intLimit = 0,
        int $intOffset = 0, array $arrOptions = []
    ): Collection
    {
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
        if ($arrColumns === []) {
            return static::findAll($arrOptions);
        }
        return static::findBy($arrColumns, null, $arrOptions);
    }

    /**
     * Count items, depends on the arguments.
     *
     * @param array $arrConfig [Request Config]
     * @param array $arrOptions [Query Options]
     *
     * @throws Exception
     */
    public static function countItems(array $arrConfig = [], array $arrOptions = []): int
    {
        $arrColumns = static::formatColumns($arrConfig);
        if ($arrColumns === []) {
            return static::countAll();
        }
        return static::countBy($arrColumns, null, $arrOptions);
    }

    /**
     * Format ItemModel columns.
     *
     * @param array $arrConfig [Configuration to format]
     *
     * @throws Exception
     */
    public static function formatColumns(array $arrConfig): array
    {
        $arrColumns = [];
        foreach ($arrConfig as $c => $v) {
            $arrColumns = array_merge($arrColumns, static::formatStatement($c, $v));
        }
        if (array_key_exists('not',$arrConfig)) {
            $arrColumns[] = $arrConfig['not'];
        }
        return $arrColumns;
    }

    /**
     * Format Search statement.
     *
     * @param string $strField
     * @param string $varValue [Value to use]
     */
    public static function formatSearchStatement(string $strField, string $varValue): string
    {
        $t = static::$strTable;

        return "$t.$strField REGEXP '$varValue'";
    }

    /**
     * Generic statements format.
     *
     * @param string $strField    [Column to format]
     * @param mixed  $varValue    [Value to use]
     * @param string $strOperator [Operator to use, default "="]
     */
    public static function formatStatement(string $strField, $varValue, string $strOperator = '='): array
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
                    $arrSearchColumns = static::$arrSearchFields; //TODO : Elle existe cette var ?
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
     * @return Result|Statement
     * @throws Exception
     */
    public static function findItemsGroupByOneField(string $strField) //for php 8 only : Result|Statement
    {
        $t = static::$strTable;
        return Database::getInstance()->prepare(sprintf('SELECT %s.%s FROM %s GROUP BY %s.%s', $t, $strField, $t, $t, $strField))->execute();
    }

    /**
     * Build a query based on the given options.
     *
     * @param array $arrOptions The options array
     *
     * @return string The query string
     */
    protected static function buildFindQuery(array $arrOptions): string
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
    protected static function buildCountQuery(array $arrOptions): string
    {
        return QueryBuilder::count($arrOptions);
    }
}
