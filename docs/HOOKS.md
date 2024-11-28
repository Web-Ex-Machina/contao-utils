# Hooks for package `contao-utils`

This file list all available hooks in this package.
:warning: Those hooks are not in `$GLOBALS['TL_HOOKS']` but in `$GLOBALS['WEM_HOOKS']`.

## List

| name | description |
--- | ---
| `preFindItems` | Called before items retrieving
| `postFindItems` | Called after items have been retrieved
| `preCountItems` |  Called before items count
| `postCountItems` |  Called after items have been counted
| `formatDefaultStatement` | Called when the field to format isn't managed by the Model
| `formatStatement` | Called every time there's a field to format by the Model

## Details

### preFindItems

This hook is called in `Model::findItems`, before items are retrieved. 

**Return value** : `array`

**Arguments**:
Name | Type | Description
--- | --- | ---
$t | `string` | The table name
$arrColumns | `array` | The columns
$arrConfig | `array` | The criterias given to `findItems` method
$intLimit | `int` | The limit
$intOffset | `int` | The offset
$arrOptions | `array` | The options array


**Code**:
```php
public function preFindItems(
	string $t,
	array $arrColumns,
	array $arrConfig,
	int $intLimit,
	int $intOffset,
	array $arrOptions
): array
{
	return $arrColumns;
}
```

### postFindItems

This hook is called in `Model::findItems`, after items are retrieved. 

**Return value** : `array`

**Arguments**:
Name | Type | Description
--- | --- | ---
$objCollection | `\Contao\Model\Collection|\Contao\Model|null` | The items retrieved
$t | `string` | The table name
$arrColumns | `array` | The columns
$arrConfig | `array` | The criterias given to `findItems` method
$intLimit | `int` | The limit
$intOffset | `int` | The offset
$arrOptions | `array` | The options array


**Code**:
```php
public function postFindItems(
	\Contao\Model\Collection|\Contao\Model|null $objCollection,
	string $t,
	array $arrColumns,
	array $arrConfig,
	int $intLimit,
	int $intOffset,
	array $arrOptions
): \Contao\Model\Collection|\Contao\Model|null
{
	return $objCollection;
}
```

### preCountItems

This hook is called in `Model::countItems`, before items are counted. 

**Return value** : `array`

**Arguments**:
Name | Type | Description
--- | --- | ---
$t | `string` | The table name
$arrColumns | `array` | The columns
$arrConfig | `array` | The criterias given to `countItems` method
$arrOptions | `array` | The options array


**Code**:
```php
public function preCountItems(
	string $t,
	array $arrColumns,
	array $arrConfig,
	array $arrOptions
): array
{
	return $arrColumns;
}
```

### postCountItems

This hook is called in `Model::countItems`, after items are counted. 

**Return value** : `int`

**Arguments**:
Name | Type | Description
--- | --- | ---
$intCount | `int` | The count
$t | `string` | The table name
$arrColumns | `array` | The columns
$arrConfig | `array` | The criterias given to `countItems` method
$arrOptions | `array` | The options array


**Code**:
```php
public function postCountItems(
	int $intCount,
	string $t,
	array $arrColumns,
	array $arrConfig,
	array $arrOptions
): int
{
	return $intCount;
}
```

### formatDefaultStatement

This hook is called in `Model::formatStatement`, if field is not managed by Model

**Return value** : `array`

**Arguments**:
Name | Type | Description
--- | --- | ---
$strField | `string` | The field
$varValue | `mixed` | The value
$strOperator | `string` | The operator
$t | `string` | The table name


**Code**:
```php
public function formatDefaultStatement(
	string $strField,
	$varValue,
	string $strOperator,
	string $t
): array
{
	return $arrColumns;
}
```

### formatStatement

This hook is called in `Model::formatStatement`, after field have been managed by Model

**Return value** : `array`

**Arguments**:
Name | Type | Description
--- | --- | ---
$arrColumns | `array` | The columns
$strField | `string` | The field
$varValue | `mixed` | The value
$strOperator | `string` | The operator
$t | `string` | The table name


**Code**:
```php
public function formatDefaultStatement(
	array $arrColumns,
	string $strField,
	$varValue,
	string $strOperator,
	string $t
): array
{
	return $arrColumns;
}
```