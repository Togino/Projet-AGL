<?php
require_once "../app/config/database.php";

try {
    
    $pdo->exec("CREATE DATABASE IF NOT EXISTS scolar_sys");
    $pdo->exec("USE scolar_sys");

    // Create tables if not exist (simplified, you may need the full schema)
    // For now, assume tables exist, or add basic ones

    // Insert test users
    $password = password_hash("password123", PASSWORD_DEFAULT);

    // Admin
    $stmt = $pdo->prepare("INSERT IGNORE INTO utilisateur (MAT, nom, prenom, email, motdepasse, role_id, statut) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->execute(["ADM001", "Admin", "Test", "admin@test.com", $password, 1, 1]); // Assume role_id 1 is admin

    // Gestionnaire
    $stmt->execute(["GEST001", "Gestionnaire", "Test", "gestionnaire@test.com", $password, 2, 1]); // Assume 2 is gestionnaire

    // Etudiant
    $stmt->execute(["ETU001", "Etudiant", "Test", "etudiant@test.com", $password, 3, 1]); // Assume 3 is etudiant

    echo "Test users inserted. Login with email: admin@test.com, gestionnaire@test.com, etudiant@test.com, password: password123";

} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>