<?php

declare(strict_types=1);

namespace WEM\UtilsBundle\Classes;

use Contao\Controller;

class DcaUtil
{
    public static function getFieldConfig(string $field, string $table): array
    {
        Controller::loadDataContainer($table);

        return $GLOBALS['TL_DCA'][$table]['fields'][$field];
    }

    public static function isFieldMultiple(array $field): bool
    {
        return 
            \array_key_exists('eval', $field)
            && \is_array($field['eval'])
            && \array_key_exists('multiple', $field['eval'])
            && true === (bool) $field['eval']['multiple']
        ;
    }
}
