import json  
from flask import Flask, request, jsonify
from flask_cors import CORS
import tensorflow as tf
import numpy as np
import cv2
# Indispensable pour la précision !
from tensorflow.keras.applications.mobilenet_v2 import preprocess_input 

app = Flask(__name__)
CORS(app)

# 1. Charger le modèle
model = tf.keras.models.load_model("food_model.h5")


# 2. Charger les classes dynamiquement depuis le JSON
try:
    with open("classes.json", "r") as f:
        classes = json.load(f)
    print(f"✅ {len(classes)} classes chargées avec succès.")
except FileNotFoundError:
    print("❌ Erreur : Le fichier classes.json est introuvable !")
    classes = []

@app.route('/predict', methods=['POST'])
def predict():
    if 'repas_image' not in request.files:
        return jsonify({"error": "Pas d'image"}), 400
    
    file = request.files['repas_image']
    
    # Transformation de l'image en format lisible par OpenCV
    img_array = np.frombuffer(file.read(), np.uint8)
    img = cv2.imdecode(img_array, cv2.IMREAD_COLOR)
    
    # 1. Redimensionnement
    img = cv2.resize(img, (224, 224))
    
    # 2. Ajout de la dimension batch (1, 224, 224, 3)
    img = np.expand_dims(img, axis=0)
    
    # 3. Prétraitement MobileNetV2 (Indispensable pour la précision !)
    img = preprocess_input(img.astype(np.float32))

    # Prédiction avec le GPU
    prediction = model.predict(img)
    index = np.argmax(prediction)
    
    return jsonify({
        "nom_detecte": classes[index],
        "confiance": float(np.max(prediction))
    })

if __name__ == '__main__':
    app.run(port=5000, debug=True)