<?php
require_once "../app/config/database.php";

$id = $_GET["id"];

$stmt = $pdo->prepare("SELECT * FROM classe WHERE ID=?");
$stmt->execute([$id]);
$c = $stmt->fetch();

if($_POST){
    $stmt = $pdo->prepare("UPDATE classe SET nom=?, niveau=? WHERE ID=?");
    $stmt->execute([$_POST["nom"], $_POST["niveau"], $id]);
    header("Location: classes.php");
}
?>

<form method="POST">
    <input name="nom" value="<?= $c["nom"] ?>">
    <input name="niveau" value="<?= $c["niveau"] ?>">
    <button>Modifier</button>
</form>