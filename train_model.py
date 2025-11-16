import pandas as pd
import numpy as np
from sklearn.model_selection import train_test_split
from sklearn.linear_model import LogisticRegression
from sklearn.preprocessing import StandardScaler, LabelEncoder
from sklearn.metrics import accuracy_score, confusion_matrix, classification_report
from sklearn.impute import SimpleImputer
import joblib
import warnings
warnings.filterwarnings('ignore')

# Charger les données
print("📊 Chargement des données...")
df = pd.read_csv('df_complet.csv')

# Afficher les informations de base
print(f"Dimensions du dataset: {df.shape}")
print(f"Colonnes disponibles: {len(df.columns)}")
print(f"Variable cible 'est_encaisse': {df['est_encaisse'].value_counts()}")

# Identifier la variable cible
target = 'est_encaisse'

def prepare_data(df):
    """Préparer les données pour l'entraînement"""
    data = df.copy()
    
    # Encoder la variable cible
    data[target] = data[target].map({'non_encaisse': 0, 'encaisse': 1})
    print(f"✅ Variable cible encodée: {data[target].value_counts()}")
    
    # Sélectionner les features importantes (éviter les colonnes problématiques)
    features_to_use = [
        'emprunteur_salaire', 'nbr_enfants', 'cumul_crd_immo', 'nbr_credit_immo',
        'cumul_crd_conso', 'nbr_credit_conso', 'type_dossier',
        'emprunteur.situation_familiale.libelle', 'type_contrat_menage',
        'emprunteur.anciennete', 'emprunteur.charge.loyer'
    ]
    
    # Garder seulement les colonnes existantes
    available_features = [f for f in features_to_use if f in data.columns]
    available_features.append(target)
    
    data = data[available_features]
    print(f"📋 Features sélectionnées: {available_features}")
    
    # Séparer features et target
    X = data.drop(columns=[target])
    y = data[target]
    
    # Identifier les types de colonnes
    numeric_cols = X.select_dtypes(include=[np.number]).columns.tolist()
    categorical_cols = X.select_dtypes(include=['object']).columns.tolist()
    
    print(f"🔢 Colonnes numériques: {numeric_cols}")
    print(f"🔤 Colonnes catégorielles: {categorical_cols}")
    
    # Traitement des valeurs manquantes pour les numériques
    imputer_num = SimpleImputer(strategy='median')
    X[numeric_cols] = imputer_num.fit_transform(X[numeric_cols])
    print("✅ Valeurs manquantes numériques traitées")
    
    # Encoder les variables catégorielles
    label_encoders = {}
    for col in categorical_cols:
        le = LabelEncoder()
        X[col] = le.fit_transform(X[col].astype(str))
        label_encoders[col] = le
        print(f"✅ Colonne '{col}' encodée - {len(le.classes_)} classes")
    
    return X, y, numeric_cols, categorical_cols, label_encoders, imputer_num

# Préparer les données
print("\n🔧 Préparation des données...")
X, y, numeric_cols, categorical_cols, label_encoders, imputer_num = prepare_data(df)

# Séparation train/test
X_train, X_test, y_train, y_test = train_test_split(
    X, y, test_size=0.2, random_state=42, stratify=y
)

print(f"\n📁 Split des données:")
print(f"Train set: {X_train.shape}")
print(f"Test set: {X_test.shape}")
print(f"Proportion cible - Train: {y_train.value_counts(normalize=True)}")
print(f"Proportion cible - Test: {y_test.value_counts(normalize=True)}")

# Normalisation des données
scaler = StandardScaler()
X_train_scaled = scaler.fit_transform(X_train)
X_test_scaled = scaler.transform(X_test)
print("✅ Données normalisées")

# Entraînement du modèle
print("\n🤖 Entraînement du modèle...")
model = LogisticRegression(
    random_state=42, 
    max_iter=1000,
    class_weight='balanced'  # Gérer le déséquilibre des classes
)
model.fit(X_train_scaled, y_train)

# Évaluation
y_pred = model.predict(X_test_scaled)
y_pred_proba = model.predict_proba(X_test_scaled)[:, 1]

print("\n" + "="*50)
print("📊 PERFORMANCES DU MODÈLE")
print("="*50)
print(f"✅ Accuracy: {accuracy_score(y_test, y_pred):.4f}")
print(f"✅ Score train: {model.score(X_train_scaled, y_train):.4f}")
print(f"✅ Score test: {model.score(X_test_scaled, y_test):.4f}")

print("\n📈 Matrice de confusion:")
print(confusion_matrix(y_test, y_pred))

print("\n📋 Rapport de classification:")
print(classification_report(y_test, y_pred))

# Analyse des coefficients
feature_importance = pd.DataFrame({
    'feature': X.columns,
    'coefficient': model.coef_[0],
    'abs_coefficient': np.abs(model.coef_[0])
}).sort_values('abs_coefficient', ascending=False)

print("\n🎯 TOP 10 VARIABLES LES PLUS IMPORTANTES")
print("="*40)
print(feature_importance.head(10))

# Sauvegarde du modèle et des préprocesseurs
print("\n💾 Sauvegarde des modèles...")
joblib.dump(model, 'credit_model.pkl')
joblib.dump(scaler, 'scaler.pkl')
joblib.dump(label_encoders, 'label_encoders.pkl')
joblib.dump(imputer_num, 'imputer_num.pkl')
joblib.dump(list(X.columns), 'feature_columns.pkl')

# Sauvegarder aussi les informations sur les encodeurs
encoder_info = {}
for col, encoder in label_encoders.items():
    encoder_info[col] = {
        'classes': list(encoder.classes_),
        'n_classes': len(encoder.classes_)
    }
joblib.dump(encoder_info, 'encoder_info.pkl')

print("✅ Modèle et préprocesseurs sauvegardés:")
print(f"   - credit_model.pkl")
print(f"   - scaler.pkl") 
print(f"   - label_encoders.pkl")
print(f"   - imputer_num.pkl")
print(f"   - feature_columns.pkl")
print(f"   - encoder_info.pkl")

print(f"\n🎉 Entraînement terminé avec succès!")
print(f"📊 Modèle prêt avec {len(X.columns)} features")