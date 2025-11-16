<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Prédiction de Solvabilité - Banque XYZ</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.8.0/font/bootstrap-icons.css">
    <style>
        :root {
            --primary: #2c3e50;
            --secondary: #3498db;
            --success: #27ae60;
            --warning: #e74c3c;
            --light: #ecf0f1;
        }
        
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        .container {
            max-width: 900px;
        }
        
        .card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            backdrop-filter: blur(10px);
            background: rgba(255,255,255,0.95);
        }
        
        .card-header {
            background: linear-gradient(135deg, var(--primary), #34495e);
            color: white;
            border-radius: 15px 15px 0 0 !important;
            padding: 1.5rem;
        }
        
        .form-section {
            background: var(--light);
            border-radius: 10px;
            padding: 1.5rem;
            margin-bottom: 1.5rem;
        }
        
        .btn-primary {
            background: linear-gradient(135deg, var(--secondary), #2980b9);
            border: none;
            padding: 12px 30px;
            font-weight: 600;
        }
        
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(52, 152, 219, 0.4);
        }
        
        .btn-success {
            background: linear-gradient(135deg, var(--success), #229954);
            border: none;
        }
        
        .btn-danger {
            background: linear-gradient(135deg, var(--warning), #cb4335);
            border: none;
        }
        
        .btn-outline-secondary {
            border: 2px solid var(--secondary);
            color: var(--secondary);
        }
        
        .loading {
            display: none;
        }
        
        .example-card {
            transition: all 0.3s ease;
            cursor: pointer;
        }
        
        .example-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.15);
        }
        
        .result-card {
            transition: all 0.3s ease;
        }
        
        .result-card:hover {
            transform: translateY(-5px);
        }
        
        .badge-example {
            font-size: 0.75rem;
            padding: 4px 8px;
        }
    </style>
</head>
<body>
    <div class="container py-5">
        <!-- En-tête -->
        <div class="text-center text-white mb-5">
            <h1 class="display-4 fw-bold mb-3">
                <i class="bi bi-graph-up-arrow"></i>
                Prédiction de Solvabilité
            </h1>
            <p class="lead">Analyse intelligente du risque crédit</p>
        </div>

        <!-- Carte principale -->
        <div class="card">
            <div class="card-header text-center">
                <h2 class="mb-0">
                    <i class="bi bi-person-badge"></i>
                    Évaluation du Profil Client
                </h2>
            </div>
            
            <div class="card-body p-4">
                <!-- Statut API -->
                <div id="statusAlert" class="alert alert-info d-flex align-items-center">
                    <i class="bi bi-info-circle me-2"></i>
                    <div>
                        <strong>Statut:</strong> 
                        <span id="statusText">Vérification de la connexion...</span>
                    </div>
                </div>

                <!-- Section Exemples Rapides -->
                <div class="mb-4">
                    <h5 class="text-primary mb-3">
                        <i class="bi bi-lightning-charge"></i>
                        Tests Rapides
                    </h5>
                    <div class="row g-3">
                        <!-- Exemple Client Solvable -->
                        <div class="col-md-6">
                            <div class="card example-card h-100 border-success" onclick="loadGoodClientExample()">
                                <div class="card-body text-center">
                                    <i class="bi bi-emoji-smile display-4 text-success mb-3"></i>
                                    <h6 class="card-title text-success">Client Solvable</h6>
                                    <p class="card-text small text-muted">
                                        Profil idéal - Revenus stables, faible endettement
                                    </p>
                                    <div class="mt-2">
                                        <span class="badge bg-success badge-example">CDI</span>
                                        <span class="badge bg-success badge-example">Propriétaire</span>
                                        <span class="badge bg-success badge-example">Salaire élevé</span>
                                    </div>
                                    <button class="btn btn-success btn-sm mt-2">
                                        <i class="bi bi-play-circle"></i> Tester
                                    </button>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Exemple Client Non Solvable -->
                        <div class="col-md-6">
                            <div class="card example-card h-100 border-warning" onclick="loadBadClientExample()">
                                <div class="card-body text-center">
                                    <i class="bi bi-emoji-frown display-4 text-warning mb-3"></i>
                                    <h6 class="card-title text-warning">Client Risqué</h6>
                                    <p class="card-text small text-muted">
                                        Profil à risque - Endettement élevé, revenus faibles
                                    </p>
                                    <div class="mt-2">
                                        <span class="badge bg-warning badge-example">CDD</span>
                                        <span class="badge bg-warning badge-example">Locataire</span>
                                        <span class="badge bg-warning badge-example">Dettes</span>
                                    </div>
                                    <button class="btn btn-warning btn-sm mt-2">
                                        <i class="bi bi-play-circle"></i> Tester
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Formulaire -->
                <div class="form-section">
                    <form id="predictionForm">
                        <div class="row g-4">
                            <!-- Colonne 1: Informations Personnelles -->
                            <div class="col-lg-6">
                                <h5 class="text-primary mb-3">
                                    <i class="bi bi-person-vcard"></i>
                                    Informations Personnelles
                                </h5>
                                
                                <div class="mb-3">
                                    <label class="form-label fw-bold">Type de dossier</label>
                                    <select class="form-select" name="type_dossier" id="type_dossier" required>
                                        <option value="proprietaire">Propriétaire</option>
                                        <option value="locataire">Locataire</option>
                                    </select>
                                </div>
                                
                                <div class="mb-3">
                                    <label class="form-label fw-bold">Situation familiale</label>
                                    <select class="form-select" name="emprunteur.situation_familiale.libelle" id="situation_familiale" required>
                                        <option value="Célibataire">Célibataire</option>
                                        <option value="Marié(e)">Marié(e)</option>
                                        <option value="Divorcée">Divorcée</option>
                                        <option value="Veuf/Veuve">Veuf/Veuve</option>
                                        <option value="Concubins">Concubins</option>
                                        <option value="Pacsé(e)">Pacsé(e)</option>
                                        <option value="Séparé(e)">Séparé(e)</option>
                                    </select>
                                </div>
                                
                                <div class="mb-3">
                                    <label class="form-label fw-bold">
                                        <i class="bi bi-currency-euro"></i>
                                        Salaire emprunteur (€)
                                    </label>
                                    <input type="number" class="form-control" name="emprunteur_salaire" id="emprunteur_salaire" value="2500" min="0" required>
                                </div>
                                
                                <div class="mb-3">
                                    <label class="form-label fw-bold">
                                        <i class="bi bi-people"></i>
                                        Nombre d'enfants
                                    </label>
                                    <input type="number" class="form-control" name="nbr_enfants" id="nbr_enfants" value="0" min="0" required>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label fw-bold">
                                        <i class="bi bi-briefcase"></i>
                                        Ancienneté (années)
                                    </label>
                                    <input type="number" class="form-control" name="emprunteur.anciennete" id="anciennete" value="5" min="0" step="0.5" required>
                                </div>
                            </div>
                            
                            <!-- Colonne 2: Informations Financières -->
                            <div class="col-lg-6">
                                <h5 class="text-primary mb-3">
                                    <i class="bi bi-wallet2"></i>
                                    Informations Financières
                                </h5>
                                
                                <div class="mb-3">
                                    <label class="form-label fw-bold">Crédit immobilier total (€)</label>
                                    <input type="number" class="form-control" name="cumul_crd_immo" id="cumul_crd_immo" value="0" min="0" required>
                                </div>
                                
                                <div class="mb-3">
                                    <label class="form-label fw-bold">Nombre crédits immo</label>
                                    <input type="number" class="form-control" name="nbr_credit_immo" id="nbr_credit_immo" value="0" min="0" required>
                                </div>
                                
                                <div class="mb-3">
                                    <label class="form-label fw-bold">Crédit conso total (€)</label>
                                    <input type="number" class="form-control" name="cumul_crd_conso" id="cumul_crd_conso" value="0" min="0" required>
                                </div>
                                
                                <div class="mb-3">
                                    <label class="form-label fw-bold">Nombre crédits conso</label>
                                    <input type="number" class="form-control" name="nbr_credit_conso" id="nbr_credit_conso" value="0" min="0" required>
                                </div>
                                
                                <div class="mb-3">
                                    <label class="form-label fw-bold">Type de contrat</label>
                                    <select class="form-select" name="type_contrat_menage" id="type_contrat" required>
                                        <option value="CDI">CDI</option>
                                        <option value="CDD">CDD</option>
                                        <option value="TNS">TNS</option>
                                        <option value="RETRAITE">Retraité</option>
                                        <option value="SANS EMPLOI">Sans emploi</option>
                                        <option value="INCONNU">Inconnu</option>
                                    </select>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label fw-bold">
                                        <i class="bi bi-house"></i>
                                        Charge loyer (€)
                                    </label>
                                    <input type="number" class="form-control" name="emprunteur.charge.loyer" id="charge_loyer" value="500" min="0" required>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Boutons d'action -->
                        <div class="text-center mt-4">
                            <button type="submit" class="btn btn-primary btn-lg" id="submitBtn">
                                <i class="bi bi-calculator"></i>
                                Analyser la Solvabilité
                            </button>
                            
                            <button type="button" class="btn btn-outline-secondary btn-lg ms-2" onclick="resetForm()">
                                <i class="bi bi-arrow-repeat"></i>
                                Réinitialiser
                            </button>
                            
                            <!-- Indicateur de chargement -->
                            <div class="loading mt-3" id="loadingSpinner">
                                <div class="spinner-border text-primary" role="status">
                                    <span class="visually-hidden">Chargement...</span>
                                </div>
                                <p class="mt-2 text-muted">Analyse du profil en cours...</p>
                            </div>
                        </div>
                    </form>
                </div>
                
                <!-- Résultats -->
                <div class="result-section" id="resultSection" style="display: none;">
                    <div class="card result-card">
                        <div class="card-body text-center p-4">
                            <h4 id="resultTitle" class="mb-3"></h4>
                            <p id="resultMessage" class="lead"></p>
                            
                            <div class="row mt-4">
                                <div class="col-md-6">
                                    <div class="p-3">
                                        <i class="bi bi-shield-check display-6 text-primary"></i>
                                        <h5>Niveau de risque</h5>
                                        <p class="h6" id="riskLevel"></p>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="p-3">
                                        <i class="bi bi-graph-up display-6 text-primary"></i>
                                        <h5>Niveau de confiance</h5>
                                        <p class="h6" id="confidence"></p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Footer -->
        <div class="text-center text-white mt-4">
            <p class="mb-0">
                <i class="bi bi-cpu"></i>
                Système intelligent de scoring crédit - Oussama Saidi
            </p>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Exemples de clients prédéfinis
        const clientExamples = {
            good: {
                name: "Client Solvable - Profil Ideal",
                data: {
                    type_dossier: "proprietaire",
                    "emprunteur.situation_familiale.libelle": "Marié(e)",
                    emprunteur_salaire: 4500,
                    nbr_enfants: 2,
                    "emprunteur.anciennete": 8,
                    cumul_crd_immo: 150000,
                    nbr_credit_immo: 1,
                    cumul_crd_conso: 8000,
                    nbr_credit_conso: 1,
                    type_contrat_menage: "CDI",
                    "emprunteur.charge.loyer": 0
                }
            },
            bad: {
                name: "Client Risqué - Profil Défavorable",
                data: {
                    type_dossier: "locataire",
                    "emprunteur.situation_familiale.libelle": "Célibataire",
                    emprunteur_salaire: 1800,
                    nbr_enfants: 0,
                    "emprunteur.anciennete": 1,
                    cumul_crd_immo: 0,
                    nbr_credit_immo: 0,
                    cumul_crd_conso: 25000,
                    nbr_credit_conso: 4,
                    type_contrat_menage: "CDD",
                    "emprunteur.charge.loyer": 650
                }
            }
        };

        // Vérifier la connexion API au chargement
        document.addEventListener('DOMContentLoaded', function() {
            checkAPIStatus();
        });

        function checkAPIStatus() {
            fetch('http://localhost:5000/health')
                .then(response => response.json())
                .then(data => {
                    updateStatusDisplay(data.model_loaded, true);
                })
                .catch(error => {
                    updateStatusDisplay(false, false);
                });
        }

        function updateStatusDisplay(modelLoaded, connected) {
            const statusText = document.getElementById('statusText');
            const statusAlert = document.getElementById('statusAlert');
            
            if (connected && modelLoaded) {
                statusText.innerHTML = '<span class="text-success">✅ API connectée et modèle chargé</span>';
                statusAlert.className = 'alert alert-success';
            } else if (connected && !modelLoaded) {
                statusText.innerHTML = '<span class="text-warning">⚠️ API connectée mais modèle non chargé</span>';
                statusAlert.className = 'alert alert-warning';
            } else {
                statusText.innerHTML = '<span class="text-danger">❌ API non accessible - Vérifiez que Flask est démarré sur le port 5000</span>';
                statusAlert.className = 'alert alert-danger';
            }
        }

        // Charger l'exemple client solvable
        function loadGoodClientExample() {
            loadExample(clientExamples.good);
            showToast('✅ Profil client solvable chargé', 'success');
        }

        // Charger l'exemple client risqué
        function loadBadClientExample() {
            loadExample(clientExamples.bad);
            showToast('⚠️ Profil client risqué chargé', 'warning');
        }

        // Charger un exemple dans le formulaire
        function loadExample(example) {
            // Mettre à jour tous les champs du formulaire
            for (const [key, value] of Object.entries(example.data)) {
                const element = document.querySelector(`[name="${key}"]`);
                if (element) {
                    element.value = value;
                }
            }
            
            // Faire défiler jusqu'au formulaire
            document.getElementById('predictionForm').scrollIntoView({ 
                behavior: 'smooth',
                block: 'center'
            });
            
            // Mettre en évidence le formulaire
            highlightForm();
        }

        // Mettre en évidence le formulaire
        function highlightForm() {
            const formSection = document.querySelector('.form-section');
            formSection.style.transition = 'all 0.3s ease';
            formSection.style.boxShadow = '0 0 0 3px rgba(52, 152, 219, 0.3)';
            
            setTimeout(() => {
                formSection.style.boxShadow = 'none';
            }, 2000);
        }

        // Afficher une notification toast
        function showToast(message, type = 'info') {
            // Créer un toast Bootstrap simple
            const toast = document.createElement('div');
            toast.className = `alert alert-${type} alert-dismissible fade show position-fixed`;
            toast.style.cssText = 'top: 20px; right: 20px; z-index: 1050; min-width: 300px;';
            toast.innerHTML = `
                ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            `;
            
            document.body.appendChild(toast);
            
            // Auto-supprimer après 3 secondes
            setTimeout(() => {
                if (toast.parentNode) {
                    toast.parentNode.removeChild(toast);
                }
            }, 3000);
        }

        // Gestion du formulaire
        document.getElementById('predictionForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const submitBtn = document.getElementById('submitBtn');
            const loadingSpinner = document.getElementById('loadingSpinner');
            
            // Afficher le loading
            submitBtn.disabled = true;
            loadingSpinner.style.display = 'block';
            
            // Préparer les données
            const formData = new FormData(this);
            const data = {};
            formData.forEach((value, key) => {
                // Conversion des types
                if (key.includes('salaire') || key.includes('cumul') || key.includes('charge')) {
                    data[key] = parseFloat(value) || 0;
                } else if (key.includes('nbr') || key.includes('anciennete')) {
                    data[key] = parseInt(value) || 0;
                } else {
                    data[key] = value;
                }
            });
            
            console.log('📤 Données envoyées:', data);
            
            // Envoyer la requête
            fetch('http://localhost:5000/predict', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify(data)
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error(`Erreur HTTP ${response.status}`);
                }
                return response.json();
            })
            .then(result => {
                displayResult(result);
            })
            .catch(error => {
                console.error('❌ Erreur:', error);
                displayResult({
                    error: `Erreur de connexion: ${error.message}`
                });
            })
            .finally(() => {
                // Cacher le loading
                submitBtn.disabled = false;
                loadingSpinner.style.display = 'none';
            });
        });

        function displayResult(result) {
            const resultSection = document.getElementById('resultSection');
            const resultTitle = document.getElementById('resultTitle');
            const resultMessage = document.getElementById('resultMessage');
            const riskLevel = document.getElementById('riskLevel');
            const confidence = document.getElementById('confidence');
            
            if (result.error) {
                resultTitle.innerHTML = '<i class="bi bi-exclamation-triangle text-danger"></i> Erreur';
                resultTitle.className = 'text-danger';
                resultMessage.textContent = result.error;
                riskLevel.innerHTML = '<span class="badge bg-secondary">Indéterminé</span>';
                confidence.textContent = 'N/A';
            } else {
                if (result.prediction === 1) {
                    resultTitle.innerHTML = '<i class="bi bi-check-circle-fill text-success"></i> Client Solvable';
                    resultTitle.className = 'text-success';
                    resultSection.style.borderLeft = '5px solid #27ae60';
                } else {
                    resultTitle.innerHTML = '<i class="bi bi-exclamation-triangle-fill text-warning"></i> Client Non Solvable';
                    resultTitle.className = 'text-warning';
                    resultSection.style.borderLeft = '5px solid #e74c3c';
                }
                
                resultMessage.textContent = result.message;
                riskLevel.innerHTML = `<span class="badge bg-${result.alert_type}">${result.risk_level}</span>`;
                confidence.textContent = result.confidence;
            }
            
            resultSection.style.display = 'block';
            resultSection.scrollIntoView({ behavior: 'smooth' });
        }

        function resetForm() {
            document.getElementById('predictionForm').reset();
            document.getElementById('resultSection').style.display = 'none';
            document.getElementById('emprunteur_salaire').value = 2500;
            document.getElementById('anciennete').value = 5;
            document.getElementById('charge_loyer').value = 500;
            showToast('📝 Formulaire réinitialisé', 'info');
        }
    </script>
</body>
</html>