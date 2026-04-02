<?php
declare(strict_types=1);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Administration</title>
    <link rel="stylesheet" href="admin.css">
</head>
<body>
    <main>
        <div id="d1">
            <img src="../images/image.png" alt="logo"><br>
            <p>UIE</p>
            <nav>
                <button id="bo-nav">ACCUEIL</button>
                <button id="bo-nav" onclick="location.href='../public/admin-demo.html'">API ADMIN</button>
                <button id="bo-nav">ACTIONS</button>
            </nav>
        </div>
        <div id="d2">
            <br>
            <button>Votre espace</button><br><br>
            <button onclick="location.href='../public/admin-demo.html'">Tester backend</button><br><br>
            <hr>
            <p>Le backend PHP securise est disponible dans les dossiers <strong>app</strong> et <strong>public</strong>.</p>
        </div>
        <div id="d3">
            <h2>Administration scolaire</h2>
            <p>Cette interface historique est conservee. Les nouvelles routes backend et la gestion des permissions sont disponibles via la console de test.</p>
            <p><a href="../public/admin-demo.html">Ouvrir la console admin de test</a></p>
        </div>
    </main>
</body>
</html>
