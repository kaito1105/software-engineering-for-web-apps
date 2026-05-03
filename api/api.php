<?
require "init.php";

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

$assoc = [];
$work = $get_post["work"] ?? null;
$act = $get_post["act"] ?? null;
$scene = $get_post["scene"] ?? null;

$found = false;

if ($work && $act && $scene) {
  $sql = "SELECT * FROM " . SHAKESPEARE_PARAGRAPHS_TABLE . " 
    JOIN " . SHAKESPEARE_CHAPTERS_TABLE . "
    ON par_work_id = chap_work_id 
    AND par_act = chap_act
    AND par_scene = chap_scene
    WHERE par_work_id = :work 
    AND par_act = :act 
    AND par_scene = :scene 
    ORDER BY par_number ASC";
  $placeholders = [":work" => $work, ":act" => $act, ":scene" => $scene];
  $result = lib::db_query($sql, $placeholders);
  $rows = $result->fetchAll();
  $num_rows = count($rows);

  if ($num_rows > 0) {
    $found = true;
    $assoc = [
      "scene_location" => $rows[0]["chap_description"] ?? "",
      "paragraphs" => []
    ];

    foreach ($rows as $row) {
      $assoc["paragraphs"][] = [$row["par_number"], $row["par_char_id"], $row["par_text"]];
    }
  }
} elseif ($work) {
  $sql = "SELECT * FROM " . SHAKESPEARE_CHAPTERS_TABLE . "
    WHERE chap_work_id = :work 
    ORDER BY chap_id ASC";
  $placeholders = [":work" => $work];
  $result = lib::db_query($sql, $placeholders);
  $rows = $result->fetchAll();
  $num_rows = count($rows);

  if ($num_rows > 0) {
    $found = true;
    foreach ($rows as $row) {
      $assoc[] = [
        "scene_id" => $row["chap_id"],
        "scene_work_id" => $row["chap_work_id"],
        "scene_act" => $row["chap_act"],
        "scene_scene" => $row["chap_scene"],
        "scene_location" => $row["chap_description"]
      ];
    }
  }
}

if (!$found) {
  $sql = "SELECT * FROM " . SHAKESPEARE_WORKS_TABLE . "
    ORDER BY work_title ASC";
  $result = lib::db_query($sql);
  $rows = $result->fetchAll();

  foreach ($rows as $row) {
    $assoc[] = [
      "work_id" => $row["work_id"],
      "work_title" => $row["work_title"],
      "work_long_title" => $row["work_long_title"],
      "work_year" => $row["work_year"],
      "work_genre" => $row["work_genre"]
    ];
  }
}

echo json_encode($assoc);

?>