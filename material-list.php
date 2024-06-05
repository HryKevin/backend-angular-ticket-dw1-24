<?php

include 'header-init.php';
include 'jwt-helper.php';

$user = extractJwtBody();

if (!$user->role) {
    http_response_code(403);
    echo '{"message" : "Vous n\'êtes pas connecté"}';
    exit();
}

try {
    $request = $connexion->query("SELECT m.id, m.nom, m.date_achat, m.numero_de_serie,
       CASE
           WHEN r.id_materiel IS NOT NULL AND r.accepte != 0 THEN 0
           ELSE 1
       END AS disponible
FROM materiel m
LEFT JOIN (
    SELECT id_materiel, accepte
    FROM reservation
    WHERE date_fin IS NULL OR date_fin > NOW()
    GROUP BY id_materiel 
) r ON m.id = r.id_materiel;

    ");

    $materialList = $request->fetchAll();
    echo json_encode($materialList);
} catch (Exception $e) {
    http_response_code(500);
    echo '{"message" : "Erreur du serveur, contactez votre administrateur"}';
}
