<?php
// Include database connection and setup check
require_once 'php/db.php';
require_once 'php/setup_check.php';

// Check and setup database if needed
$setupRan = checkAndSetupDatabase($pdo);
?>
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Deine Gesundheitsplattform für Patienten und Pflegekräfte!">
    <title>PflegePro</title>
    <link rel="stylesheet" href="style.css">
    <script src="script.js"></script>
</head>
<body>
  <div class="navbar">
    PflegePro
  </div>
  
  <!-- Normal login form -->
  <div class="container">
    <div class="left">
      <img src="img/home.svg" alt="Pflege Symbolbild">
    </div>
    <div class="right">
      <div class="login-box">
        <h2>Login</h2>
        
        <?php 
        // Show error messages if any
        if (isset($_GET['error'])) {
            echo "<div style='background: #ffe6e6; color: #d00; padding: 10px; margin: 10px 0; border-radius: 5px;'>";
            switch ($_GET['error']) {
                case 'invalid':
                    echo "❌ Ungültiger Benutzername oder Passwort";
                    break;
                case 'empty':
                    echo "❌ Bitte füllen Sie alle Felder aus";
                    break;
                case 'db':
                    echo "❌ Datenbankfehler, bitte versuchen Sie es später erneut";
                    break;
                case 'role':
                    echo "❌ Unbekannte Benutzerrolle";
                    break;
                default:
                    echo "❌ Anmeldefehler";
            }
            echo "</div>";
        }
        ?>

        <form action="php/login.php" method="POST">
          <label for="username">Benutzername</label>
          <input type="text" id="username" name="username" required>
          <label for="password">Passwort</label>
          <input type="password" id="password" name="password" required>
          <button type="submit">Anmelden</button>
        </form>
      </div>
    </div>
  </div>
  <?php endif; ?>
</body>
</html>
