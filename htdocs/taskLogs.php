<?php
/**
 * Created by PhpStorm.
 * User: fizda
 * Date: 16/01/2018
 * Time: 18:19
 */


$page = isset($_GET['page']) ? $_GET['page'] : 1;
$itemsPerPage = isset($_GET['itemsPerPage']) ? $_GET['itemsPerPage'] : 50;
//we are going to use this to add to the monitoring db
$dsn = 'mysql:host=localhost;dbname=cncappsdev';
$DB_USER = "webuser";
$DB_PASSWORD = "CnC1988";
$options = [
    PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8'
];
$db = new PDO($dsn, $DB_USER, $DB_PASSWORD, $options);

$query = "SELECT COUNT(id) AS totalCount FROM taskLog";

$result = $db->query($query, PDO::FETCH_COLUMN, 0);

$totalCount = $result->fetchColumn();

$offset = $itemsPerPage * ($page - 1);

$query = "SELECT * FROM taskLog ORDER BY id DESC LIMIT ? OFFSET ?";

$statement = $db->prepare($query);
$statement->bindValue(1, $itemsPerPage, PDO::PARAM_INT);
$statement->bindValue(2, $offset, PDO::PARAM_INT);
$execution = $statement->execute();

$result = $statement->fetchAll(PDO::FETCH_ASSOC);

?>

    <table>
        <thead>
        <tr>
            <td>Description</td>
            <td>Started At</td>
            <td>Finished At</td>
            <td>Max CPU Usage %</td>
            <td>Max Memory Usage Bytes</td>
        </tr>
        </thead>
        <tbody>
        <?php

        foreach ($result as $datum) {
            ?>
            <tr>
                <td><?= $datum['description'] ?></td>
                <td><?= $datum['startedAt'] ?></td>
                <td><?= $datum['finishedAt'] ?></td>
                <td><?= $datum['maxCpuUsage'] ?></td>
                <td><?= $datum['maxMemoryUsage'] ?></td>
            </tr>
            <?php
        }
        ?>
        </tbody>
    </table>
    <div>
        <?php
        $totalPages = ceil($totalCount / $itemsPerPage);

        if ($page > 1) {
            ?>
            <a href="?page=<?= $page - 1 ?>">Previous Page</a>
            <?php
        }
        if ($page < $totalPages) {
            ?>
            <a href="?page=<?= $page + 1 ?>">Next Page</a>
            <?php
        }

        ?>
    </div>
<?php
