<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . "/../helpers/functions.php";
require_once __DIR__ . "/../config/database.php";

if (!isLoggedIn()) {
    redirect("../public/login.php");
}

$currentPage = basename($_SERVER["SCRIPT_NAME"]);

if (isset($_SESSION["user"]["MAT"])) {
    $stmt = $pdo->prepare("SELECT must_change_password FROM utilisateur WHERE MAT = ? LIMIT 1");
    $stmt->execute([$_SESSION["user"]["MAT"]]);
    $passwordState = $stmt->fetch();

    if ($passwordState) {
        $_SESSION["user"]["must_change_password"] = (int) $passwordState["must_change_password"];
    }
}

if (
    !empty($_SESSION["user"]["must_change_password"]) &&
    $currentPage !== "changer-mot-de-passe.php" &&
    $currentPage !== "logout.php"
) {
    redirect("../public/changer-mot-de-passe.php");
}
