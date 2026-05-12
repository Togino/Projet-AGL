<?php
// Générer les hashs pour les mots de passe standards
$hash_admin = password_hash("admin123", PASSWORD_DEFAULT);
$hash_123 = password_hash("123", PASSWORD_DEFAULT);

echo "Hash admin123: " . $hash_admin . PHP_EOL;
echo "Hash 123: " . $hash_123 . PHP_EOL;

// Vérifier que les hashs fonctionnent
echo "Verify admin123: " . (password_verify("admin123", $hash_admin) ? "OK" : "FAIL") . PHP_EOL;
echo "Verify 123: " . (password_verify("123", $hash_123) ? "OK" : "FAIL") . PHP_EOL;
?>
