<?php
/*
    ALL THESE FUNCTIONS DEAL WITH RECORDS, VALIDATE ACCORDING TO THE RECORD RULES AND nothing else!
*/
/* Record:

        item_id     int
        item_name   string(computed)
        qty         int
        cost        int(computed)
        cat_str     string(computed)
        cat_id      int(computed)
        date        str
        note        str
*/

function _makeRecord(int $item_id, int $qty, string $date, string $note): array
{
    return [
        'item_id' => $item_id,
        'qty' => $qty,
        'date' => $date,
        'note' => $note
    ];
}

function listRecords(mysqli $conn, int $month = 0): array
{
    $order = getOrder(RECORD, 'date');
    if ($order === 'qty' || $order === 'note')
        $order .= ' DESC ';
    if (empty($month)) {
        $raw_records = db_SelectAll($conn, RECORD, [], '*', $order);
    } else {
        // yellow, of course
        $raw_records = db_SelectAll($conn, RECORD, ['month(date)' => $month], '*, date_format(date, "%a") as day_name, day(date) as day', $order);
    }
    return is_null($raw_records) ? [] :
        array_map(function ($raw) use ($conn) {
            $record_public = $raw;
            $related_item_id = $raw['item_id'];
            $related_item = getItemById($related_item_id, $conn);
            // computed
            $record_public['item_name'] = $related_item['name'];
            $record_public['cost'] = $related_item['price'] * $raw['qty'];
            $record_public['cat_str'] = $related_item['cat_str'];
            $record_public['cat_id'] = $related_item['cat_id'];
            return $record_public;
        }, $raw_records);
}

function getRecord(mysqli $conn, int $id): array
{
    // yellow, there is no strict rule on what makes of a record, inconsistant shapes of data
    $raw_record = db_SelectOne($conn, RECORD, ['id' => $id]);
    if (is_null($raw_record)) {
        return [];
    }
    $related_item = getItemById($raw_record['item_id'], $conn);
    $raw_record['item_name'] = $related_item['name'];
    $raw_record['item_price'] = $related_item['price'];
    $raw_record['cost'] = $related_item['price'] * $raw_record['qty'];
    $raw_record['cat_str'] = $related_item['cat_str'];
    $raw_record['cat_id'] = $related_item['cat_id'];
    return $raw_record;
}

// add a new record, returns the id
function addNewRecord(int $item_id, int $qty, string $date, string $note, mysqli $conn): int
{
    $record_to_add = _makeRecord($item_id, $qty, $date, $note);
    return db_Insert($conn, RECORD, $record_to_add);
}

function updateRecord(int $id, int $item_id, int $qty, string $date, string $note, mysqli $conn): bool
{
    if (_checkRecord($id, $conn)) {
        $record_to_add = _makeRecord($item_id, $qty, $date, $note);
        return db_Update($conn, RECORD, $record_to_add, ['id' => $id]);
    }
    return false;
}

function _checkRecord(int $id, mysqli $conn): bool
{
    return !is_null(db_SelectOne($conn, RECORD, ['id' => $id], 'id'));
}

function deleteRecord(int $id, mysqli $conn): bool
{
    if (_checkRecord($id, $conn)) {
        return db_Delete($conn, RECORD, $id);
    }
    return false;
}

// yellow?
function getTotalOutcome(mysqli $conn, int $month = 0, bool $filtered=true): int
{
    // excluding None by default for now, im not sure if i should exclude categories at all and should just exclude by tags as they are more flexiable
    $exclude_categories = $filtered ? ['None', 'Medical'] : [];
    if (empty($month)) {
        $raw_records = db_SelectAll($conn, RECORD, []);
    } else {
        $raw_records = db_SelectAll($conn, RECORD, ['month(date)' => $month]);
    }
    return is_null($raw_records) ? 0 :
        array_reduce($raw_records, function ($carry, $raw_record) use ($conn, $exclude_categories) {
            $related_item = getItemById($raw_record['item_id'], $conn);
            if (in_array($related_item['cat_str'], $exclude_categories)) {
                return $carry;
            }
            $cost = $related_item['price'] * $raw_record['qty'];
            return $carry + $cost;
        }, 0);
}

/*
Record for day:
    day         int
    data[]      array of record objects from that day
    total_cost
    length
*/
function make_daily_records(mysqli $conn, int $current_month): array
{
    $active_records = listRecords($conn, $current_month);
    // dd($active_records);
    $daily_records = [];
    $i = 0;
    $current_day = $active_records[0]['day'];
    while (true) {
        $coffee = [];
        $coffee['day'] = $current_day;
        $coffee['data'] = [];
        $coffee['total_cost'] = 0;
        $coffee['length'] = 0;
        while ($active_records[$i]['day'] == $current_day && $i < count($active_records)) {
            $coffee['data'][] = $active_records[$i];
            $coffee['total_cost'] += $active_records[$i]['cost'];
            $coffee['length']++;
            $i++;
        }
        $current_day = $active_records[$i]['day'];
        $daily_records[] = $coffee;
        // print_r($daily_records);
        if ($i >= count($active_records)) {
            break;
        }
    }
    return $daily_records;
}
