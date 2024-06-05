<?php

include 'header-init.php';
include 'jwt-helper.php';

$user = extractJwtBody();

if ($user->role != "Administrateur") {
    http_response_code(403);
    echo '{"message" : "Vous n\avez pas les droits nécessaires"}';
    exit();
}

//tansformer le JSON en objet PHP contenant les informations de l'utilisateur
$json = file_get_contents('php://input');

// Le convertit en objet PHP
$user = json_decode($json);

//verifier l'email de l'utilisateur est unique
$request = $connexion->prepare("SELECT * FROM user WHERE email = :email");
$request->execute(["email" => $user->email]);
$existingUser = $request->fetch();

if ($existingUser) {
    http_response_code(409); //note : 409 = CONFLICT
    echo '{"message" : "Cet email est déjà utilisé"}';
    exit();
}

//on récupère le role par rapport au nom indiqué dans le JSON
$request = $connexion->prepare("SELECT id FROM role WHERE name = :name");
$request->execute(["name" => $user->role]);
$role = $request->fetch();

//si le role n'existe pas, on renvoie une erreur 400
if (!$role) {
    http_response_code(400);
    echo '{"message" : "le rôle indiqué n\'existe pas"}';
    exit();
}

//On ajoute l'utilisateur dans la base de données
$request = $connexion->prepare("INSERT INTO user(email, password, firstname, lastname, id_role) 
                                VALUES (:email, :password, :firstname, :lastname, :id_role)");

$request->execute([
    "email" => $user->email,
    "password" => password_hash($user->password, PASSWORD_DEFAULT),
    "firstname" => $user->firstname,
    "lastname" => $user->lastname,
    "id_role" => $role['id']
]);

echo '{"message" : "L\'utilsateur a bien été ajouté"}';
