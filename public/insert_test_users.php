<?php
require_once "../app/config/database.php";

try {
    
    $pdo->exec("CREATE DATABASE IF NOT EXISTS scolar_sys");
    $pdo->exec("USE scolar_sys");

    // Create tables if not exist (simplified, you may need the full schema)
    // For now, assume tables exist, or add basic ones

    // Insert test users
    $admin_password = password_hash("admin123", PASSWORD_DEFAULT);
    $other_password = password_hash("123", PASSWORD_DEFAULT);

    // Admin (identifiants pour tout le monde)
    $stmt = $pdo->prepare("INSERT IGNORE INTO utilisateur (MAT, nom, prenom, email, motdepasse, role_id, statut) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->execute(["AD-0001", "Admin", "Principal", "admin@scolarsys.test", $admin_password, 2, 1]); 

    // Gestionnaire
    $stmt->execute(["GE-0001", "Gestionnaire", "Test", "gestionnaire@scolarsys.test", $other_password, 3, 1]); 

    // Enseignant
    $stmt->execute(["ES-0001", "Enseignant", "Test", "enseignant@scolarsys.test", $other_password, 4, 1]); 

    // Etudiant
    $stmt->execute(["ET-0001", "Etudiant", "Test", "etudiant@scolarsys.test", $other_password, 5, 1]);

    echo "Test users inserted. Identifiants:\n- Admin: admin@scolarsys.test / admin123\n- Autres roles: utiliser le mail avec / 123";

} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>