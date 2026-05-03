<?

define('DB_SERVER', 'localhost');
define('DB_USERNAME', 'csci488_spring26');
define('DB_PASSWORD', 'writeMoreCode26');
define('DB_DATABASE', 'csci488_spring26');

$pdo = new PDO('mysql:host=' . DB_SERVER . ';dbname=' . DB_DATABASE . ';charset=utf8mb4', DB_USERNAME, DB_PASSWORD);

$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
$pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

$animal_name = trim($_GET["name"]);

if (!isset($_GET["name"]) || empty($_GET["name"])) {
  echo "No animal specified.";
  exit;
}

$stmt = $pdo->prepare("SELECT * FROM miyamoto_animals WHERE animal_name = :animal_name");
$stmt->execute([':animal_name' => $animal_name]);

$name_rows = $stmt->fetchAll();
$name_counts = count($name_rows);

if ($name_counts > 0) {
  echo "<br>$animal_name was rejected since it already is in the database.";
} else {
  $timestamp = date("Y-m-d H:i:s");
  $datetime = date("Y-m-d H:i:s");
  $int = time();

  $stmt = $pdo->prepare("INSERT INTO miyamoto_animals (animal_id, animal_name, animal_timestamp, animal_date_time, animal_int) VALUES (NULL, ?, ?, ?, ?)");
  $stmt->execute([$animal_name, $timestamp, $datetime, $int]);

  echo "<br>$animal_name was successfully added to the database.";
}

$stmt = $pdo->query("SELECT * FROM miyamoto_animals ORDER BY animal_id ASC");
$rows = $stmt->fetchAll();
$num_rows = count($rows);

?>
<!DOCTYPE html>
<html>

<head>
  <title>Basic Database Operations</title>
  <style>
    table,
    td,
    th {
      border: 1px solid #000;
      border-collapse: collapse;
      padding: 3px;
    }
  </style>
</head>

<body>
  <br>

  <br><br>
  <b>Database table listing.</b>
  <br><br>
  <?= $num_rows ?> rows in database.
  <br><br>

  <table>
    <? if ($num_rows > 0) { ?>
      <? foreach ($rows as $row) { ?>
        <tr valign="top">
          <td>
            <?= $row['animal_id'] ?>
          </td>
          <td>
            <?= $row['animal_name'] ?>
          </td>
          <td>
            <?= $row['animal_timestamp'] ?>
          </td>
          <td>
            <?= date('ga \- M j, Y', strtotime($row['animal_date_time'])) ?>
          </td>
          <td>
            <?= $row['animal_int'] ?>
          </td>
        </tr>
      <? } ?>
    <? } ?>
  </table>

</body>

</html>