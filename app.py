from flask import Flask, request, jsonify
import joblib
import pandas as pd
import numpy as np
app = Flask(__name__)

# Charger le pipeline
MODEL_PATH = ""
model = joblib.load(MODEL_PATH)

@app.route("/")
def index():
    return "API Flask OK - Service de prédiction crédit"

@app.route("/", methods=["POST"])
def predict():
    """
    Attendu: JSON avec les mêmes features que celles utilisées pour l'entraînement.
    Exemple:
    {
      "age": 40,
      "revenu": 35000,
      "montant_credit": 5000,
      "duree": 24,
      "profession": "employe",
      ...
    }
    """
    data = request.get_json()
    if not data:
        return jsonify({"error": "Aucun JSON envoyé"}), 400

    # Si on reçoit une seule observation sous forme d'objet dict -> construire DataFrame 1 ligne
    if isinstance(data, dict):
        X_input = pd.DataFrame([data])
    elif isinstance(data, list):
        X_input = pd.DataFrame(data)
    else:
        return jsonify({"error": "Format JSON non supporté. Envoyer dict ou list de dicts."}), 400

    # S'assurer mêmes colonnes: on laisse le pipeline gérer les colonnes manquantes / inconnues
    try:
        probs = model.predict_proba(X_input)[:, 1]
        preds = model.predict(X_input)
    except Exception as e:
        return jsonify({"error": f"Erreur lors de la prédiction: {str(e)}"}), 400

    results = []
    for p, pred in zip(probs, preds):
        message = "Client peut payer" if pred == 1 else "Client ne peut pas payer"
        results.append({
            "prediction": int(pred),
            "probability": float(round(p,4)),
            "message": message
        })

    # Si un seul élément, retourner un objet
    if len(results) == 1:
        return jsonify(results[0])
    return jsonify(results)

if __name__ == "__main__":
    app.run(debug=True, host="0.0.0.0", port=5000)
