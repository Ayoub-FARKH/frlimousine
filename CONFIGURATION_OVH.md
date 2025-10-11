# 🚗 Configuration FRLimousine pour OVH Cloud

## 📋 Guide de déploiement sur OVH Cloud

### 1. Configuration du script PHP

**Fichier : `receive-pdf.php`**

Modifiez les variables suivantes selon votre hébergement OVH :

```php
// Configuration OVH Cloud
$uploadDir = 'pdfs/';
$emailNotification = 'contact@votre-domaine.ovh'; // ← Remplacez par votre email OVH
$logFile = 'pdfs/reception.log';
$domainName = 'votre-domaine.ovh'; // ← Remplacez par votre nom de domaine OVH
```

### 2. Préparation des fichiers

#### Structure requise :
```
projet-limousine/
├── receive-pdf.php
├── pdfs/
│   └── .htaccess
└── [autres fichiers du site]
```

#### Permissions sur OVH :
- **Dossiers** : 755
- **Fichiers PHP** : 644
- **Fichiers PDF** : 644

### 3. Configuration email OVH

#### A. Via l'interface OVH :
1. Connectez-vous à votre panel OVH
2. Allez dans "Emails" → "Comptes email"
3. Créez un compte email (ex: `contact@votre-domaine.ovh`)

#### B. Configuration SMTP (optionnel) :
Si l'envoi d'email ne fonctionne pas, ajoutez ces lignes dans `receive-pdf.php` :

```php
ini_set('SMTP', 'ssl0.ovh.net');
ini_set('smtp_port', '465');
ini_set('sendmail_from', 'contact@votre-domaine.ovh');
```

### 4. Upload via FTP

#### Logiciel recommandé : FileZilla

**Paramètres de connexion :**
- Hôte : ftp.votre-domaine.ovh
- Utilisateur : votre-identifiant-ovh
- Mot de passe : votre-mot-de-passe-ovh
- Port : 21

#### Étapes d'upload :
1. Connectez-vous à votre espace FTP OVH
2. Téléversez tous les fichiers du projet
3. Vérifiez les permissions des dossiers

### 5. Vérifications post-déploiement

#### Test du script :
```bash
curl -X POST https://votre-domaine.ovh/receive-pdf.php \
  -H "Content-Type: application/json" \
  -d '{"test": "connexion"}'
```

#### Vérification des logs :
- Fichier : `pdfs/reception.log`
- Accessible via FTP ou panel OVH

#### Test d'envoi d'email :
Créez un devis de test sur votre site pour vérifier la réception d'email.

### 6. Résolution des problèmes courants

#### Problème : "Erreur création répertoire"
**Solution :** Vérifiez les permissions du dossier parent (755)

#### Problème : "Email non reçu"
**Solutions :**
1. Vérifiez la configuration email dans OVH
2. Testez l'envoi d'email via le panel OVH
3. Vérifiez les logs du serveur OVH

#### Problème : "Erreur sauvegarde PDF"
**Solution :** Vérifiez l'espace disque disponible sur votre hébergement OVH

### 7. Maintenance

#### Nettoyage automatique (à ajouter si nécessaire) :
```php
// Supprimer les PDFs de plus de 30 jours
$days = 30;
$files = glob($uploadDir . "*.pdf");
foreach($files as $file) {
    if (time() - filemtime($file) > $days * 24 * 3600) {
        unlink($file);
    }
}
```

### 8. Support OVH

- **Panel client** : https://www.ovh.com/manager/
- **Documentation** : https://docs.ovh.com/
- **Support** : https://www.ovh.com/fr/support/

---

## ⚡ Déploiement rapide

1. ✅ Modifiez les configurations dans `receive-pdf.php`
2. ✅ Créez un compte email dans OVH
3. ✅ Téléversez les fichiers via FTP
4. ✅ Testez le fonctionnement
5. ✅ Vérifiez la réception d'emails

**Votre site FRLimousine est prêt pour OVH Cloud ! 🚗**