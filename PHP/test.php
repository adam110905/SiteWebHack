<?php
session_start();

// Initialisation du niveau si pas déjà fait
if(!isset($_SESSION['level'])) {
    $_SESSION['level'] = 1;
}

function getCurrentLevel() {
    return $_SESSION['level'];
}

function isLevelUnlocked($level) {
    return $level <= getCurrentLevel();
}

try {
    $pdo = new PDO("mysql:host=localhost;dbname=ctf_security", "root", "");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    die("Erreur de connexion : " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>CTF Challenge</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100">
    <nav class="bg-blue-600 text-white p-4">
        <div class="container mx-auto">
            <h1 class="text-2xl font-bold">CTF Challenge</h1>
            <p>Niveau actuel: <?= getCurrentLevel() ?>/5</p>
        </div>
    </nav>

    <div class="container mx-auto p-4">
        <!-- Niveau 1: Source Code -->
        <div class="mb-8 p-6 bg-white rounded-lg shadow-lg">
            <h2 class="text-xl font-bold mb-4">Niveau 1: Code Source Secret</h2>
            <p class="mb-4">Trouvez le flag caché dans le code source de cette page!</p>
            <!-- FLAG : Dmitry Khoroshev -->
            <form class="flag-form" data-level="1">
                <input type="hidden" name="level" value="1">
                <input type="text" name="flag" class="border p-2 rounded" placeholder="Entrez le flag">
                <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded ml-2">Vérifier</button>
            </form>
        </div>

        <!-- Niveau 2: Cookie -->
        <div class="mb-8 p-6 bg-white rounded-lg shadow-lg <?= isLevelUnlocked(2) ? '' : 'opacity-50' ?>">
            <h2 class="text-xl font-bold mb-4">Niveau 2: Cookie Monster</h2>
            <?php if(isLevelUnlocked(2)): 
                setcookie("secret_cookie", base64_encode("Firesheep"), time() + 3600);
            ?>
                <p class="mb-4">Le flag est caché dans un cookie (en base64)</p>
                <form class="flag-form" data-level="2">
                    <input type="hidden" name="level" value="2">
                    <input type="text" name="flag" class="border p-2 rounded" placeholder="Entrez le flag">
                    <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded ml-2">Vérifier</button>
                </form>
            <?php else: ?>
                <p>Terminez le niveau 1 d'abord!</p>
            <?php endif; ?>
        </div>

        <!-- Niveau 3: SQL -->
        <div class="mb-8 p-6 bg-white rounded-lg shadow-lg <?= isLevelUnlocked(3) ? '' : 'opacity-50' ?>">
            <h2 class="text-xl font-bold mb-4">Niveau 3: SQL Injection</h2>
            <?php if(isLevelUnlocked(3)): ?>
                <p class="mb-4">Connectez-vous en tant qu'administrateur</p>
                <form class="sql-form" data-level="3">
                    <input type="hidden" name="level" value="3">
                    <input type="text" name="username" class="border p-2 rounded mb-2 block" placeholder="Username">
                    <input type="password" name="password" class="border p-2 rounded mb-2 block" placeholder="Password">
                    <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded">Login</button>
                </form>
            <?php else: ?>
                <p>Terminez le niveau 2 d'abord!</p>
            <?php endif; ?>
        </div>

        <!-- Niveau 4: XSS -->
        <div class="mb-8 p-6 bg-white rounded-lg shadow-lg <?= isLevelUnlocked(4) ? '' : 'opacity-50' ?>">
            <h2 class="text-xl font-bold mb-4">Niveau 4: XSS Challenge</h2>
            <?php if(isLevelUnlocked(4)): ?>
                <p class="mb-4">Injectez un script qui affiche une alerte "XSS"</p>
                <form class="xss-form" data-level="4">
                    <input type="hidden" name="level" value="4">
                    <input type="text" name="message" class="border p-2 rounded mb-2 block" placeholder="Votre message">
                    <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded">Tester</button>
                </form>
                <div id="xss-output"></div>
            <?php else: ?>
                <p>Terminez le niveau 3 d'abord!</p>
            <?php endif; ?>
        </div>

        <!-- Niveau 5: Upload -->
        <div class="mb-8 p-6 bg-white rounded-lg shadow-lg <?= isLevelUnlocked(5) ? '' : 'opacity-50' ?>">
            <h2 class="text-xl font-bold mb-4">Niveau 5: Upload Challenge</h2>
            <?php if(isLevelUnlocked(5)): ?>
                <p class="mb-4">Uploadez un fichier PHP pour obtenir le flag</p>
                <form class="upload-form" data-level="5" enctype="multipart/form-data">
                    <input type="hidden" name="level" value="5">
                    <input type="file" name="file" class="border p-2 rounded mb-2 block">
                    <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded">Upload</button>
                </form>
            <?php else: ?>
                <p>Terminez le niveau 4 d'abord!</p>
            <?php endif; ?>
        </div>
    </div>

    <script>
    // Gestion des formulaires
    document.querySelectorAll('form').forEach(form => {
        form.addEventListener('submit', async (e) => {
            e.preventDefault();
            const formData = new FormData(form);
            
            try {
                const response = await fetch('check.php', {
                    method: 'POST',
                    body: formData
                });
                
                const data = await response.json();
                
                if(data.success) {
                    alert(data.message);
                    if(data.flag) {
                        alert(`Le flag est : ${data.flag}`);
                    }
                    location.reload();
                } else {
                    alert(data.message);
                }
            } catch(error) {
                console.error('Erreur:', error);
                alert('Une erreur est survenue');
            }
        });
    });

    // Pour le XSS Challenge
    const xssForm = document.querySelector('.xss-form');
    if(xssForm) {
        xssForm.addEventListener('submit', (e) => {
            const message = e.target.elements.message.value;
            if(message.includes('<script>') && message.includes('alert("XSS")')) {
                eval(message.replace(/<\/?script>/g, ''));
            }
        });
    }
    </script>
</body>
<script>
    document.querySelector('.upload-form button').addEventListener('click', (e) => {
    e.preventDefault();
    window.location.href = 'https://www.youtube.com/watch?v=dQw4w9WgXcQ';
});
</script>
</html>