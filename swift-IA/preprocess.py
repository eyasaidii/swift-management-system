"""
Script standalone de preprocessing.
Usage : python preprocess.py [input.csv] [output.csv]

Lit un CSV brut de messages SWIFT, nettoie et sauvegarde.
"""

import sys
import pandas as pd


def preprocess(input_path: str, output_path: str):
    print(f"Chargement de {input_path}...")
    df = pd.read_csv(input_path)
    print(f"  → {len(df)} lignes chargées")

    # Supprimer les doublons exacts
    before = len(df)
    df = df.drop_duplicates()
    print(f"  → {before - len(df)} doublons supprimés")

    # Nettoyer les colonnes texte
    str_cols = ["message_type", "direction", "currency", "sender_bank", "receiver_bank"]
    for col in str_cols:
        if col in df.columns:
            df[col] = df[col].astype(str).str.strip().str.upper()

    # Remplir les montants manquants par 0
    if "amount" in df.columns:
        df["amount"] = pd.to_numeric(df["amount"], errors="coerce").fillna(0)

    # Supprimer les lignes sans type de message ni montant
    if "message_type" in df.columns:
        df = df[df["message_type"].notna() & (df["message_type"] != "")]

    df.to_csv(output_path, index=False)
    print(f"Données nettoyées sauvegardées dans {output_path} ({len(df)} lignes)")


if __name__ == "__main__":
    inp = sys.argv[1] if len(sys.argv) > 1 else "data/swift_messages_raw.csv"
    out = sys.argv[2] if len(sys.argv) > 2 else "data/swift_messages.csv"
    preprocess(inp, out)