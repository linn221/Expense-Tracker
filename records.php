<?php require_once "./header.php" ?>
<?php require_once "./nav-bar.php" ?>

<?php
$conn = connectMysql();
$active_records = getRecordsPublic($conn);
?>

<table class=" table table-success table-bordered border-2 mt-3 container">
    <thead>
        <th>#</th>
        <th>Item</th>
        <th>Qty</th>
        <th>Cost</th>
        <th>Category</th>
        <th>Note</th>
        <th>Del/Edit</th>
        <th>Date</th>
    </thead>
    <tbody>
        <?php
        foreach ($active_records as $record) :
            $id = $record['id'];
            $del_url = "api/del-api.php?selected=record&del&id=$id";
            $update_url = "insert.php?update&selected=record&id=$id";
        ?>
            <tr>
                <td>
                    <?= $record['id'] ?>
                </td>
                <td>
                    <?= $record['item_name'] ?>
                </td>
                <td>
                    <?= $record['qty'] ?>
                </td>
                <td>
                    <div>
                        <?= displayMoney($record['cost']) ?>
                    </div>
                </td>
                <td>
                    <?= $record['cat_str'] ?>
                </td>
                <td>
                    <?= $record['note'] ?>
                </td>
                <td>
                    <a href="<?= $update_url ?>" class=" btn btn-sm btn-primary">
                        Update
                    </a>
                    <a href="<?= $del_url; ?>" class=" btn btn-sm btn-danger">
                        Del
                    </a>
                </td>
                <td>
                    <?= $record['date'] ?>
                </td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>

<?php require_once "./footer.php"; ?>