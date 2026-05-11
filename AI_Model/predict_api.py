import json  
from flask import Flask, request, jsonify
from flask_cors import CORS
import tensorflow as tf
import numpy as np
import cv2
from tensorflow.keras.applications.mobilenet_v2 import preprocess_input 

app = Flask(__name__)
CORS(app)

# 1. Charger le modèle
print("⏳ Chargement du modèle...")
model = tf.keras.models.load_model("food_model.h5")

# 2. Charger les classes
try:
    with open("classes.json", "r") as f:
        classes = json.load(f)
    print(f"✅ {len(classes)} classes chargées.")
except FileNotFoundError:
    print("❌ Erreur : classes.json introuvable !")
    classes = []

# 🥦 3. CHARGER LA BASE DE DONNÉES NUTRITIONNELLE (L'étape manquante !)
try:
    with open("nutrition.json", "r") as f:
        nutrition_db = json.load(f)
    print(f"✅ Base de données nutritionnelle chargée.")
except FileNotFoundError:
    print("⚠️ Attention : nutrition.json introuvable !")
    nutrition_db = {}

@app.route('/predict', methods=['POST'])
def predict():
    if 'repas_image' not in request.files:
        return jsonify({"error": "Pas d'image"}), 400
    
    file = request.files['repas_image']
    
    img_array = np.frombuffer(file.read(), np.uint8)
    img = cv2.imdecode(img_array, cv2.IMREAD_COLOR)
    
    img = cv2.resize(img, (224, 224))
    img = np.expand_dims(img, axis=0)
    img = preprocess_input(img.astype(np.float32))

    prediction = model.predict(img)
    index = np.argmax(prediction)
    nom_plat = classes[index]
    confiance = float(np.max(prediction))
    
    # 🥦 RÉCUPÉRATION DES MACROS DEPUIS LE JSON
    infos_nutritionnelles = nutrition_db.get(nom_plat, {
        "erreur": "Valeurs non disponibles pour ce plat"
    })
    
    # On renvoie TOUT au JavaScript !
    return jsonify({
        "nom_detecte": nom_plat,
        "confiance": confiance,
        "nutrition_pour_100g": infos_nutritionnelles
    })

if __name__ == '__main__':
    app.run(port=5000, debug=True)