<?php

include 'header-init.php';
include 'jwt-helper.php';

$user = extractJwtBody();

if (!$user->role) {
    http_response_code(403);
    echo '{"message" : "Vous n\'avez pas les droits nécessaires"}';
    exit();
}

// Transformer le JSON en objet PHP contenant les informations du matériel
$json = file_get_contents('php://input');
$materiel = json_decode($json);

// Vérifier l'id du matériel
$request = $connexion->prepare("SELECT * FROM materiel WHERE id = :id");
$request->execute(["id" => $_GET['id']]);
$existingMateriel = $request->fetch();

if ($existingMateriel) {

    // Récupérer les réservations liées à l'id du matériel
    $request = $connexion->prepare("SELECT * FROM reservation WHERE id_materiel = :id");
    $request->execute(["id" => $_GET['id']]);
    $existingReservations = $request->fetchAll();

    // Convertir les dates du POST en objets DateTime
    $debutReservation = new DateTime($materiel->debutReservation);
    $finReservation = new DateTime($materiel->finReservation);

    $conflict = false;

    // Vérifier si les dates saisies rentrent en conflit avec une réservation existante
    foreach ($existingReservations as $reservation) {
        $existingDebut = new DateTime($reservation['date_debut']);
        $existingFin = new DateTime($reservation['date_fin']);

        if (($debutReservation < $existingFin) && ($finReservation > $existingDebut)) {
            $conflict = true;
            break;
        }
    }

    if ($conflict) {
        http_response_code(409);
        echo '{"message" : "Les dates de réservation entrent en conflit avec une réservation existante"}';
    } else {
        // Ajouter la nouvelle réservation
        $request = $connexion->prepare("
            INSERT INTO reservation (id_materiel, date_debut, date_fin, id_loueur)
            VALUES (:id_materiel, :debutReservation, :finReservation, :id_loueur)
        ");
        $request->execute([
            'id_materiel' => $_GET['id'],
            'debutReservation' => $debutReservation->format('Y-m-d H:i:s'),
            'finReservation' => $finReservation->format('Y-m-d H:i:s'),
            'id_loueur' => $user->id,
        ]);

        echo '{"message" : "Votre demande de réservation sera traité dans les plus bref délais"}';
    }

} else {
    http_response_code(404);
    echo '{"message" : "Matériel non trouvé"}';
}
