import pandas as pd
import numpy as np
from sklearn.model_selection import train_test_split
from sklearn.preprocessing import StandardScaler, OneHotEncoder
from sklearn.compose import ColumnTransformer
from sklearn.pipeline import Pipeline
from sklearn.linear_model import LogisticRegression
from sklearn.metrics import accuracy_score, confusion_matrix, classification_report
import joblib

# --- 1. Charger le dataset ---
df = pd.read_csv("df_complet.csv")  # adapter le chemin si besoin
df.head()

# --- 2. Identifier la target ---
target_col = "est_encaisse"
if target_col not in df.columns:
    raise ValueError(f"Colonne cible '{target_col}' non trouvée dans le CSV.")

# --- 3. Nettoyage simple ---
# Exemples généraux — adapte selon ton jeu de données :
df = df.copy()
# Supprimer lignes entièrement vides
df.dropna(how="all", inplace=True)

# Séparer X / y
X = df.drop(columns=[target_col])
# Convertir la target en binaire 0/1 (valeur 'encaisse' -> 1, autre -> 0)
y = (df[target_col] == 'encaisse').astype(int)

# Détecter colonnes numériques/catégorielles
num_cols = X.select_dtypes(include=["int64","float64"]).columns.tolist()
cat_cols = X.select_dtypes(include=["object","category","bool"]).columns.tolist()

# Imputer valeurs manquantes simples
from sklearn.impute import SimpleImputer
num_imputer = SimpleImputer(strategy="median")
cat_imputer = SimpleImputer(strategy="constant", fill_value="missing")

# Pipeline de préprocessing
numeric_pipeline = Pipeline(steps=[
    ("imputer", num_imputer),
    ("scaler", StandardScaler())
])

categorical_pipeline = Pipeline(steps=[
    ("imputer", cat_imputer),
    ("onehot", OneHotEncoder(handle_unknown="ignore", sparse_output=False))
])

preprocessor = ColumnTransformer(transformers=[
    ("num", numeric_pipeline, num_cols),
    ("cat", categorical_pipeline, cat_cols)
])

# Pipeline complet avec modèle
clf = Pipeline(steps=[
    ("preprocessor", preprocessor),
    ("model", LogisticRegression(max_iter=1000))
])

# Split train/test
X_train, X_test, y_train, y_test = train_test_split(X, y, test_size=0.2, random_state=42, stratify=y)
# Entraînement
clf.fit(X_train, y_train)
# Évaluation
y_pred = clf.predict(X_test)
y_prob = clf.predict_proba(X_test)[:,1]
print("Accuracy:", accuracy_score(y_test, y_pred))
print("Confusion matrix:\n", confusion_matrix(y_test, y_pred))
print("Classification report:\n", classification_report(y_test, y_pred))
# Interpréter coefficients (approximation : pour variables encodées, noms générés)
# Récupérer noms des features après preprocessor
def get_feature_names(column_transformer):
    # pour sklearn >= 1.0
    feature_names = []
    for name, trans, cols in column_transformer.transformers_:
        if name == "remainder":
            continue
        # cols peut être une liste ou un ndarray ; normaliser en list
        in_cols = list(cols)
        if hasattr(trans, 'named_steps') and 'onehot' in trans.named_steps:
            ohe = trans.named_steps['onehot']
            ohe_names = ohe.get_feature_names_out(in_cols)
            feature_names.extend(list(ohe_names))
        elif hasattr(trans, 'named_steps') and 'scaler' in trans.named_steps:
            feature_names.extend(in_cols)
        else:
            feature_names.extend(in_cols)
    return feature_names
feat_names = get_feature_names(clf.named_steps['preprocessor'])
coefs = clf.named_steps['model'].coef_[0]
coef_df = pd.DataFrame({"feature": feat_names, "coef": coefs})
coef_df = coef_df.reindex(coef_df.coef.abs().sort_values(ascending=False).index)
print("Top features influence (by absolute coefficient):")
print(coef_df.head(20))
# Sauvegarder le pipeline
joblib.dump(clf, "credit_model.pkl")
print("Modèle sauvegardé dans credit_model.pkl")