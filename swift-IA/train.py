"""
Script standalone d'entraînement.
Usage : python train.py [data/swift_messages.csv]

Entraîne les modèles et les sauvegarde dans model/.
"""

import sys
import os

# Ajouter le répertoire racine au path pour les imports app.*
sys.path.insert(0, os.path.dirname(os.path.abspath(__file__)))

from app.services.detector import detector


def main():
    csv_path = sys.argv[1] if len(sys.argv) > 1 else "data/swift_messages.csv"

    if not os.path.exists(csv_path):
        print(f"ERREUR : fichier {csv_path} introuvable.")
        print("Lance d'abord : python scripts/generate_synthetic_data.py")
        sys.exit(1)

    print(f"Entraînement avec {csv_path}...")
    n_samples, version = detector.train()
    print(f"Modèle entraîné avec succès !")
    print(f"  → Échantillons : {n_samples}")
    print(f"  → Version      : {version}")
    print(f"  → Fichiers dans model/")


if __name__ == "__main__":
    main()