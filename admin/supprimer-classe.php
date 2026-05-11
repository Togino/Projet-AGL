<?php
require_once "../app/config/database.php";

$id = $_GET["id"];

$pdo->prepare("DELETE FROM classe WHERE ID=?")->execute([$id]);

header("Location: classes.php");