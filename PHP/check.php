<?php
session_start();

try {
    $pdo = new PDO("mysql:host=localhost;dbname=ctf_security", "root", "");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    die("Erreur de connexion : " . $e->getMessage());
}

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $level = isset($_POST['level']) ? (int)$_POST['level'] : 0;

    switch($level) {
        case 1: // Source Code
            if(isset($_POST['flag']) && $_POST['flag'] === 'Dmitry Khoroshev') {
                $_SESSION['level'] = 2;
                echo json_encode([
                    'success' => true,
                    'message' => 'Bravo! Vous passez au niveau 2!'
                ]);
            } else {
                echo json_encode([
                    'success' => false,
                    'message' => 'Flag incorrect.'
                ]);
            }
            break;

        case 2: // Cookie
            if(isset($_POST['flag']) && $_POST['flag'] === 'Firesheep') {
                $_SESSION['level'] = 3;
                echo json_encode([
                    'success' => true,
                    'message' => 'Bravo! Vous passez au niveau 3!'
                ]);
            } else {
                echo json_encode([
                    'success' => false,
                    'message' => 'Flag incorrect.'
                ]);
            }
            break;

        case 3: // SQL Injection
            $username = $_POST['username'] ?? '';
            $password = $_POST['password'] ?? '';
            
            // Log de la tentative
            $stmt = $pdo->prepare("INSERT INTO sql_attempts (attempted_username, attempted_password) VALUES (?, ?)");
            $stmt->execute([$username, $password]);

            // Vérifie l'injection dans le mot de passe
            if(strpos($password, "'") !== false && strpos($password, "OR") !== false) {
                $_SESSION['level'] = 4;
                echo json_encode([
                    'success' => true,
                    'message' => 'Injection SQL réussie!',
                    'flag' => 'Little Bobby Tables'
                ]);
            } else {
                echo json_encode([
                    'success' => false,
                    'message' => 'Login incorrect. Indice: Utilisez une injection SQL dans le mot de passe!'
                ]);
            }
            break;

        case 4: // XSS
            if(isset($_POST['message']) && $_POST['message'] === '<script>alert("XSS")</script>') {
                $_SESSION['level'] = 5;
                echo json_encode([
                    'success' => true,
                    'message' => 'XSS réussi!',
                    'flag' => 'Samy Worm'
                ]);
            } else {
                echo json_encode([
                    'success' => false,
                    'message' => 'Essayez encore!'
                ]);
            }
            break;

        case 5: // Upload
            if(isset($_FILES['file'])) {
                $file = $_FILES['file'];
                if(pathinfo($file['name'], PATHINFO_EXTENSION) === 'php') {
                    $_SESSION['level'] = 6;
                    echo json_encode([
                        'success' => true,
                        'message' => 'Upload réussi!',
                        'flag' => 'Joomla Local File Inclusion Exploit'
                    ]);
                } else {
                    echo json_encode([
                        'success' => false,
                        'message' => 'Type de fichier incorrect.'
                    ]);
                }
            }
            break;
    }
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Méthode non autorisée'
    ]);
}
?>