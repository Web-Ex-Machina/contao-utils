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
use Contao\System;
use WEM\UtilsBundle\Classes\QueryBuilder;

abstract class Model extends \Contao\Model
{
    /**
     * Default order column
     */
    protected static $strOrderColumn = "createdAt DESC";

    /** @var array<string> $arrSearchFields */
    protected static $arrSearchFields = [];

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
    ): ?Collection
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

        // HOOK: add pre find items logic
        if (isset($GLOBALS['WEM_HOOKS']['preFindItems']) && \is_array($GLOBALS['WEM_HOOKS']['preFindItems']))
        {
            foreach ($GLOBALS['WEM_HOOKS']['preFindItems'] as $callback)
            {
                $arrColumns = System::importStatic($callback[0])->{$callback[1]}($t, $arrColumns, $arrConfig, $intLimit, $intOffset, $arrOptions);
            }
        }

        if ($arrColumns === []) {
            $objCollection = static::findAll($arrOptions);
        } else {
            $objCollection = static::findBy($arrColumns, null, $arrOptions);
        }

        // HOOK: add post find items logic
        if (isset($GLOBALS['WEM_HOOKS']['postFindItems']) && \is_array($GLOBALS['WEM_HOOKS']['postFindItems']))
        {
            foreach ($GLOBALS['WEM_HOOKS']['postFindItems'] as $callback)
            {
                $objCollection = System::importStatic($callback[0])->{$callback[1]}($objCollection, $t, $arrColumns, $arrConfig, $intLimit, $intOffset, $arrOptions);
            }
        }

        return $objCollection;
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

        // HOOK: add pre count items logic
        if (isset($GLOBALS['WEM_HOOKS']['preCountItems']) && \is_array($GLOBALS['WEM_HOOKS']['preCountItems']))
        {
            foreach ($GLOBALS['WEM_HOOKS']['preCountItems'] as $callback)
            {
                $arrColumns = System::importStatic($callback[0])->{$callback[1]}($t, $arrConfig, $arrOptions);
            }
        }

        if ($arrColumns === []) {
            $intCount = static::countAll();
        } else {
            $intCount = static::countBy($arrColumns, null, $arrOptions);
        }

        // HOOK: add post find items logic
        if (isset($GLOBALS['WEM_HOOKS']['postCountItems']) && \is_array($GLOBALS['WEM_HOOKS']['postCountItems']))
        {
            foreach ($GLOBALS['WEM_HOOKS']['postCountItems'] as $callback)
            {
                $intCount = System::importStatic($callback[0])->{$callback[1]}($intCount, $t, $arrColumns, $arrConfig, $arrOptions);
            }
        }

        return $intCount;
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
                $k = is_array($arrSearchKeywords) ? implode('|', $arrSearchKeywords) : $arrSearchKeywords;
                $arrKeywords = [];
                foreach ($arrSearchColumns as $f) {
                    $arrKeywords[] = static::formatSearchStatement($f, $k);
                }
                $arrColumns[] = '('.implode(' OR ', $arrKeywords).')';

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
                // HOOK: add custom format statement logic for switch's default behaviour
                if (isset($GLOBALS['WEM_HOOKS']['formatDefaultStatement']) && \is_array($GLOBALS['WEM_HOOKS']['formatDefaultStatement']))
                {
                    foreach ($GLOBALS['WEM_HOOKS']['formatDefaultStatement'] as $callback)
                    {
                        $arrColumns = System::importStatic($callback[0])->{$callback[1]}($strField, $varValue, $strOperator, $t);
                    }
                }else{
                    $arrColumns[] = sprintf("$t.%s %s '%s'", $strField, $strOperator, \addslashes((string) $varValue));       
                }
        }

        // HOOK: add custom format statement logic
        if (isset($GLOBALS['WEM_HOOKS']['formatStatement']) && \is_array($GLOBALS['WEM_HOOKS']['formatStatement']))
        {
            foreach ($GLOBALS['WEM_HOOKS']['formatStatement'] as $callback)
            {
                $arrColumns = System::importStatic($callback[0])->{$callback[1]}($arrColumns, $strField, $varValue, $strOperator, $t);
            }
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
