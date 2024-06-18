<?php

include 'header-init.php';
include 'jwt-helper.php';

$user = extractJwtBody();

if ($user->role != "Administrateur") {
    http_response_code(403);
    echo '{"message" : "Vous n\'avez pas les droits nécessaires"}';
    exit();
}

// Prend les données brutes de la requête
$json = file_get_contents('php://input');

// Le convertit en objet PHP
$material = json_decode($json);

// Validation minimale des données (vous pouvez ajouter plus de validation si nécessaire)
if (!isset($material->nom) || !isset($material->date_achat) || !isset($material->numero_de_serie)) {
    http_response_code(400);
    echo '{"message" : "Tous les champs sont obligatoires"}';
    exit();
}

// Vérifier si le numéro de série existe déjà dans la base de données
$checkQuery = $connexion->prepare("SELECT COUNT(*) AS count FROM materiel WHERE numero_de_serie = ?");
$checkQuery->execute([$material->numero_de_serie]);
$result = $checkQuery->fetch(PDO::FETCH_ASSOC);

if ($result['count'] > 0) {
    http_response_code(409); // Conflit si le numéro de série existe déjà
    echo '{"message" : "Ce numéro de série existe déjà"}';
    exit();
}

// Insérer le nouveau matériel dans la base de données
$insertQuery = $connexion->prepare("INSERT INTO materiel (nom, date_achat, numero_de_serie) VALUES (:nom, :date_achat, :numero_de_serie)");

$insertQuery->execute([
    "nom" => $material->nom,
    "date_achat" => $material->date_achat,
    "numero_de_serie" => $material->numero_de_serie,
]);

// Vérifiez si l'insertion a réussi
if ($insertQuery->rowCount() > 0) {
    $newMaterialId = $connexion->lastInsertId(); // Récupère l'ID du nouveau matériel inséré si nécessaire
    echo '{"message" : "Le matériel a bien été ajouté"}';
} else {
    http_response_code(500); // Erreur de serveur si l'insertion a échoué
    echo '{"message" : "Erreur lors de l\'ajout du matériel"}';
}
