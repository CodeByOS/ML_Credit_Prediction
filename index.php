<?php
$prediction = null;
$error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Collect form data
    $data = [
        "age" => (int)$_POST['age'],
        "revenu" => (float)$_POST['revenu'],
        "montant_credit" => (float)$_POST['montant_credit'],
        "duree" => (int)$_POST['duree'],
        "profession" => $_POST['profession']
    ];

    // Prepare cURL to call Flask API
    $ch = curl_init("http://127.0.0.1:5000/predict"); // Adjust host if needed
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ["Content-Type: application/json"]);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));

    $response = curl_exec($ch);

    if(curl_errno($ch)) {
        $error = "Erreur lors de la requête: " . curl_error($ch);
    } else {
        $decoded = json_decode($response, true);
        if(isset($decoded['error'])) {
            $error = $decoded['error'];
        } else {
            $prediction = $decoded;
        }
    }

    curl_close($ch);
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Prédiction Crédit</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <h1>Prédiction Crédit</h1>
        <form method="POST">
            <label>Âge:</label>
            <input type="number" name="age" required>

            <label>Revenu (€):</label>
            <input type="number" name="revenu" required>

            <label>Montant Crédit (€):</label>
            <input type="number" name="montant_credit" required>

            <label>Durée (mois):</label>
            <input type="number" name="duree" required>

            <label>Profession:</label>
            <select name="profession" required>
                <option value="employe">Employé</option>
                <option value="retraite">Retraité</option>
                <option value="independant">Indépendant</option>
                <option value="autre">Autre</option>
            </select>

            <button type="submit">Prédire</button>
        </form>

        <?php if($prediction): ?>
            <div class="result">
                Résultat: <?= htmlspecialchars($prediction['message']) ?><br>
                Probabilité: <?= htmlspecialchars($prediction['probability']) ?><br>
                Code: <?= htmlspecialchars($prediction['prediction']) ?>
            </div>
        <?php elseif($error): ?>
            <div class="error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
    </div>
</body>
</html>
