<?php
require_once $_SERVER['DOCUMENT_ROOT'] . "/functions/functions.php";
// html_print_r($_GET);
// die();
_delete_api_main();

function _validateRequestParams(): void
{
    if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
        error("only accepting GET method");
    }
    // TODO: combine all 3 into one condition
    if (!isset($_GET['id'])) {
        error("Not setting 'id' param. Don't fuck with delete api!");
    }
    if (!isset($_GET['del'])) {
        error("Not setting del parameter. Did you call me mistakenly? Don't fuck with delete api!");
    }
    if (!isset($_GET['selected'])) {
        error("Not setting 'selected' param. Don't fuck with delete api!");
    }
    if (!is_numeric($_GET['id'])) {
        error("id parameter has to be a number. don't fuck with delete api!");
    }
    $selected_to_del = $_GET['selected'];
    if (!in_array($selected_to_del, VALID_SELECTORS)) {
        error("Invalid 'selected' param.");
    }
}
function _delete_api_record(mysqli $conn): void
{
    $id = (int)$_GET['id'];
    // do some validations
    if (($e_code = deleteRecord($id, $conn)) >= 0) {
        redirect('./records.php');
    } else {
        switch ($e_code) {
            case DB_ERROR:
                error("DB error!");
                break;
            case '-4':
                error("record you are trying to delete do not exist.");
                break;
            case '-5':
                error("record you are deleting has already been deleted");
                break;
            case '-8':
                error("fuck off, hecker!");
                break;
        }
    }
}


function _delete_api_main(): void
{
    $conn = connectMysql();
    /*
            DELETEING CATEGORY
        */
    _validateRequestParams();
    $selected_to_del = $_GET['selected'];

    // if ($selected_to_del === CATEGORY) {
    //     _category();
    // }

    // /*
    //         DELETING ITEM
    //     */
    // if ($selected_to_del === ITEM) {
    //     _item();
    // }

    /*
            DELETING RECORD
        */
    switch($selected_to_del) {
        case RECORD:
            _delete_api_record($conn);
            break;
        case INCOME:
            _delete_api_income($conn);
            break;
        default:
            error("Invalid api call. Don't fuck with del api, pls.");
    }
}

function _delete_api_income(mysqli $conn) : void
{
    $id = (int)$_GET['id'];
    // do some validations
    if (($e_code = deleteIncome($id, $conn)) >= 0) {
        redirect('./income.php');
    } else {
        switch ($e_code) {
            case DB_ERROR:
                error("DB error!");
                break;
            case VALIDATE_ERROR:
                error("record you are trying to delete do not exist.");
                break;
        }
    }
}
