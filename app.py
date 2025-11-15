from flask import Flask, request, jsonify
import joblib
import pandas as pd
import numpy as np

app = Flask(__name__)

# Load the pipeline
MODEL_PATH = "credit_model.pkl"
model = joblib.load(MODEL_PATH)

@app.route("/")
def index():
    return "API Flask OK - Service de prédiction crédit"

@app.route("/predict", methods=["POST"])
def predict():
    """
    Expected: JSON with features used for training.
    Example:
    {
      "age": 40,
      "revenu": 35000,
      "montant_credit": 5000,
      "duree": 24,
      "profession": "employe"
    }
    """

    data = request.get_json()
    if not data:
        return jsonify({"error": "Aucun JSON envoyé"}), 400

    # Convert single dict or list of dicts to DataFrame
    if isinstance(data, dict):
        X_input = pd.DataFrame([data])
    elif isinstance(data, list):
        X_input = pd.DataFrame(data)
    else:
        return jsonify({"error": "Format JSON non supporté. Envoyer dict ou list de dicts."}), 400

    # Ensure all expected columns are present
    try:
        expected_columns = model.feature_names_in_
    except AttributeError:
        return jsonify({"error": "Votre modèle ne contient pas 'feature_names_in_'. Re-train avec scikit-learn >=1.0"}), 500

    for col in expected_columns:
        if col not in X_input.columns:
            # Fill numeric columns with 0, object/categorical with 'unknown'
            if pd.api.types.is_numeric_dtype(model.named_steps['preprocessor'].transformers_[0][1].named_steps['imputer'].statistics_[0]):
                X_input[col] = 0
            else:
                X_input[col] = 'unknown'

    X_input = X_input[expected_columns]

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
            "probability": float(round(p, 4)),
            "message": message
        })

    # Return single object if only one prediction
    if len(results) == 1:
        return jsonify(results[0])
    return jsonify(results)

if __name__ == "__main__":
    app.run(debug=True, host="0.0.0.0", port=5000)