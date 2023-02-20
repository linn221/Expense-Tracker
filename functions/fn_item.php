<?php
/*
    Item:

        id      int
        name    string
        price   int
        cat_id  int
        cat_str string



    Interface:
        getItemsPublic()    => item arrays, with category name for showing
        getItem($id)        => item array, no cat_str
        addNewItem($name, $price, $cat_id)  => new added id
        deleteItem($id)     => boolean on success
*/

// returns items to display
function listItems(mysqli $conn): array
{
    $raw_items = db_SelectItems($conn);
    return array_map(function ($item) use ($conn) {
        return [
            'id' => $item['id'],
            'name' => $item['name'],
            'price' => $item['price'],
            'cat_str' => getCategoryName($item['cat_id'], $conn)
        ];
    }, $raw_items);
}

function getItemNames(mysqli $conn): array
{
    $raw_item_names = db_SelectItemNames($conn);
    return $raw_item_names;
}

function getItemsByCategory(int $cat_id, mysqli $conn): array
{
    $raw_items = db_SelectItemsByCategory($conn, $cat_id);
    return array_map(function ($item) use ($conn) {
        return [
            'id' => $item['id'],
            'name' => $item['name'],
            'price' => $item['price'],
            'cat_str' => getCategoryName($item['cat_id'], $conn)
        ];
    }, $raw_items);
}


// function getItemsByCatId(int $id): array
// {
//     // returns array of id, not item array
// }

function getItem(int $id, mysqli $conn): array
{
    $raw = db_SelectAnItem($conn, $id);
    if ($raw === false) {
        return [];
    }
    $raw['cat_str'] = getCategoryName($raw['cat_id'], $conn);
    return $raw;
}

function addNewItem(string $name, int $price, int $cat_id, mysqli $conn): int
{
    if ($price < 0) {
        return VALIDATE_ERROR;
    }
    if (db_InsertNewItem($conn, $name, $price, $cat_id)) {
        return mysqli_insert_id($conn);
    } else {
        return DB_ERROR;
    }
}

function updateItem(int $id, string $name, int $price, int $cat_id, mysqli $conn) : int
{
    if (!_checkItem($id, $conn)) {
        return VALIDATE_ERROR;
    }
    if (db_UpdateItem($conn, $id, $name, $price, $cat_id)) {
        return $id;
    } else {
        return DB_ERROR;
    }
}

function _checkItem(int $id, mysqli $conn) : bool
{
    // do some vaildations? idk
    return db_SelectExistenceItem($conn, $id);
}

function archiveItem(int $id, mysqli $conn): int
{
    if (db_ArchiveAnItem($conn, $id)) {
        return $id;
    } else {
        return DB_ERROR;
    }
    // do some validation, then db function
}


// function _validateItem(int $id): bool
// {
// }
// function _getArchiveItems(): array
// {
//     return _getArchives(ITEM);
// }
