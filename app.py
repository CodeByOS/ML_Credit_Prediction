from flask import Flask, request, jsonify
from flask_cors import CORS
import pandas as pd
import numpy as np
import joblib
import traceback
import sys

app = Flask(__name__)
CORS(app)  # Autoriser toutes les origines

# Variables globales pour le modèle
model = None
scaler = None
label_encoders = {}
imputer_num = None
feature_columns = []
encoder_info = {}

def load_model():
    """Charger le modèle et les préprocesseurs"""
    global model, scaler, label_encoders, imputer_num, feature_columns, encoder_info
    
    try:
        print("🔍 Chargement des modèles...")
        
        model = joblib.load('credit_model.pkl')
        print("✅ Modèle de prédiction chargé")
        
        scaler = joblib.load('scaler.pkl')
        print("✅ Normaliseur chargé")
        
        label_encoders = joblib.load('label_encoders.pkl')
        print(f"✅ Encodeurs chargés ({len(label_encoders)} variables catégorielles)")
        
        imputer_num = joblib.load('imputer_num.pkl')
        print("✅ Imputeureur numérique chargé")
        
        feature_columns = joblib.load('feature_columns.pkl')
        print(f"✅ {len(feature_columns)} colonnes de features chargées")
        
        # Charger les infos des encodeurs si disponible
        try:
            encoder_info = joblib.load('encoder_info.pkl')
            print("✅ Informations des encodeurs chargées")
        except:
            encoder_info = {}
            print("ℹ️  Pas d'informations d'encodeurs supplémentaires")
        
        print(f"🎯 Système prêt avec {len(feature_columns)} variables")
        return True
        
    except Exception as e:
        print(f"❌ Erreur lors du chargement: {e}")
        print(traceback.format_exc())
        return False

# Charger le modèle au démarrage
load_model()

@app.route('/')
def home():
    return jsonify({
        'message': 'API de prédiction de solvabilité - Active ✅',
        'status': 'online',
        'model_loaded': model is not None,
        'features_count': len(feature_columns),
        'endpoints': {
            '/health': 'Statut du système',
            '/predict': 'POST - Prédire la solvabilité',
            '/features': 'Liste des features utilisées'
        }
    })

@app.route('/health')
def health():
    return jsonify({
        'status': 'healthy' if model else 'unhealthy',
        'model_loaded': model is not None,
        'components': {
            'model': model is not None,
            'scaler': scaler is not None,
            'label_encoders': len(label_encoders) > 0,
            'imputer': imputer_num is not None,
            'features': len(feature_columns) > 0
        },
        'features_count': len(feature_columns)
    })

@app.route('/features')
def get_features():
    """Retourner la liste des features attendues"""
    return jsonify({
        'features': feature_columns,
        'count': len(feature_columns),
        'categorical_features': list(label_encoders.keys())
    })

@app.route('/predict', methods=['POST', 'OPTIONS'])
def predict():
    if request.method == 'OPTIONS':
        return '', 200
        
    try:
        # Vérifier que le modèle est chargé
        if model is None:
            return jsonify({'error': 'Modèle non chargé'}), 500
        
        # Récupérer les données JSON
        data = request.get_json()
        
        if not data:
            return jsonify({'error': 'Aucune donnée fournie'}), 400
        
        print(f"📥 Données reçues pour prédiction")
        
        # Préparer les données d'entrée
        input_data = {}
        for col in feature_columns:
            if col in data:
                # Convertir les types si nécessaire
                value = data[col]
                if col in label_encoders:
                    # Variable catégorielle - garder comme string
                    input_data[col] = [str(value)]
                else:
                    # Variable numérique - convertir en float
                    try:
                        input_data[col] = [float(value)]
                    except:
                        input_data[col] = [0.0]
            else:
                # Valeur par défaut si manquante
                if col in label_encoders:
                    input_data[col] = ['inconnu']  # Valeur par défaut pour catégoriel
                else:
                    input_data[col] = [0.0]  # Valeur par défaut pour numérique
        
        # Créer le DataFrame
        df_input = pd.DataFrame(input_data)
        
        # Prétraitement des variables numériques
        numeric_cols = df_input.select_dtypes(include=[np.number]).columns
        if len(numeric_cols) > 0:
            df_input[numeric_cols] = imputer_num.transform(df_input[numeric_cols])
        
        # Encodage des variables catégorielles
        for col, encoder in label_encoders.items():
            if col in df_input.columns:
                try:
                    # Gérer les nouvelles valeurs non vues
                    transformed_values = []
                    for val in df_input[col]:
                        if val in encoder.classes_:
                            transformed_values.append(encoder.transform([val])[0])
                        else:
                            # Utiliser la classe la plus fréquente comme fallback
                            transformed_values.append(0)
                    df_input[col] = transformed_values
                except Exception as e:
                    print(f"⚠️ Erreur encodage {col}: {e}")
                    df_input[col] = [0] * len(df_input)
        
        # S'assurer de l'ordre des colonnes
        df_input = df_input[feature_columns]
        
        # Normalisation
        input_scaled = scaler.transform(df_input)
        
        # Prédiction
        prediction = model.predict(input_scaled)[0]
        probability = model.predict_proba(input_scaled)[0][1]
        
        # Interprétation
        if prediction == 1:
            result_message = "✅ Client solvable - Prêt recommandé"
            risk_level = "Faible"
            alert_type = "success"
        else:
            result_message = "⚠️ Client non solvable - Prêt non recommandé"
            risk_level = "Élevé"
            alert_type = "warning"
        
        # Préparer la réponse
        response = {
            'prediction': int(prediction),
            'probability': float(probability),
            'message': result_message,
            'risk_level': risk_level,
            'confidence': f"{probability:.2%}",
            'alert_type': alert_type
        }
        
        print(f"📤 Prédiction: {prediction} (confiance: {probability:.2%})")
        return jsonify(response)
        
    except Exception as e:
        error_msg = f'Erreur lors de la prédiction: {str(e)}'
        print(f"❌ {error_msg}")
        return jsonify({'error': error_msg}), 500

@app.errorhandler(404)
def not_found(error):
    return jsonify({'error': 'Endpoint non trouvé'}), 404

@app.errorhandler(500)
def internal_error(error):
    return jsonify({'error': 'Erreur interne du serveur'}), 500

if __name__ == '__main__':
    print("\n" + "="*50)
    print("🚀 API de Prédiction de Solvabilité")
    print("="*50)
    print(f"📊 Modèle chargé: {model is not None}")
    print(f"🎯 Features: {len(feature_columns)} variables")
    print(f"🌐 URL: http://localhost:5000")
    print("="*50)
    
    app.run(debug=True, host='0.0.0.0', port=5000)