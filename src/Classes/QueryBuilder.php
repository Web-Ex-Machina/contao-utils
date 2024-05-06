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

namespace WEM\UtilsBundle\Classes;

use Contao\Database;
use Contao\DcaExtractor;

class QueryBuilder
{
    /**
     * Build a query based on the given options.
     *
     * @param array $arrOptions The options array
     *
     * @return string The query string
     */
    public static function find(array $arrOptions): string
    {
        $objBase = DcaExtractor::getInstance($arrOptions['table']);

        $strSelect = '*';
        if (array_key_exists('select',$arrOptions)) {
            $strSelect = \is_array($arrOptions['select']) ? implode(',', $arrOptions['select']) : $arrOptions['select'];
        }

        $strJoin = '';
        if (array_key_exists('join',$arrOptions)) {
            $strJoin = implode('', $arrOptions['join']);
        }

        if (!$objBase->hasRelations()) {
            $strQuery = 'SELECT '.$strSelect.' FROM '.$arrOptions['table'].$strJoin;
        } else {
            $arrJoins = [];
            $arrFields = '*' === $strSelect ? [$arrOptions['table'].'.*'] : [$strSelect];

            if ('' !== $strJoin) {
                $arrJoins[] = $strJoin;
            }

            $intCount = 0;

            foreach ($objBase->getRelations() as $strKey => $arrConfig) {
                // Automatically join the single-relation records
                if ('eager' === $arrConfig['load'] || array_key_exists('eager',$arrOptions)) {
                    if ('hasOne' === $arrConfig['type'] || 'belongsTo' === $arrConfig['type']) {
                        ++$intCount;
                        $objRelated = DcaExtractor::getInstance($arrConfig['table']);

                        foreach (array_keys($objRelated->getFields()) as $strField) {
                            $arrFields[] = 'j'.$intCount.'.'.Database::quoteIdentifier($strField).' AS '.$strKey.'__'.$strField;
                        }

                        $arrJoins[] = ' LEFT JOIN '.$arrConfig['table']." j$intCount ON ".$arrOptions['table'].'.'.Database::quoteIdentifier($strKey)."=j$intCount.".$arrConfig['field'];
                    }
                }
            }

            // Generate the query
            $strQuery = 'SELECT '.implode(', ', $arrFields).' FROM '.$arrOptions['table'].' '.implode(' ', $arrJoins);
        }

        // Where condition
        if (isset($arrOptions['column'])) {
            $strQuery .= ' WHERE '.(\is_array($arrOptions['column']) ? implode(' AND ', $arrOptions['column']) : $arrOptions['table'].'.'.Database::quoteIdentifier($arrOptions['column']).'=?');
        }

        // Group by
        if (isset($arrOptions['group'])) {
            if (\is_array($arrOptions['group'])) {
                $strQuery .= ' GROUP BY '.implode(',', $arrOptions['group']);
            } else {
                $strQuery .= ' GROUP BY '.$arrOptions['group'];
            }
        }

        // Having (see #6446)
        if (isset($arrOptions['having'])) {
            $strQuery .= ' HAVING '.$arrOptions['having'];
        }

        // Order by
        if (isset($arrOptions['order'])) {
            if (\is_array($arrOptions['order'])) {
                $strQuery .= ' ORDER BY '.implode(',', $arrOptions['order']);
            } else {
                $strQuery .= ' ORDER BY '.$arrOptions['order'];
            }
        }

        if (array_key_exists('debug',$arrOptions) && true === $arrOptions['debug']) {
            print_r($strQuery);
            die;
        }

        return $strQuery;
    }

    /**
     * Build a query based on the given options to count the number of records.
     *
     * @param array $arrOptions The options array
     *
     * @return string The query string
     */
    public static function count(array $arrOptions): string
    {
        $strQuery = 'SELECT COUNT(*) AS count FROM '.$arrOptions['table'];

        if (null !== $arrOptions['column']) {
            $strQuery .= ' WHERE '.(\is_array($arrOptions['column']) ? implode(' AND ', $arrOptions['column']) : $arrOptions['table'].'.'.Database::quoteIdentifier($arrOptions['column']).'=?');
        }

        if (array_key_exists('debug',$arrOptions) && true === $arrOptions['debug']) {
            print_r($strQuery);
            die;
        }

        return $strQuery;
    }
}

class_alias(QueryBuilder::class, 'Model\QueryBuilder');
