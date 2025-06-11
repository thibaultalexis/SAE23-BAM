#!/bin/bash

# --- Configuration ---
MYSQL="/opt/lampp/bin/mysql"
DB="sae23bam"
USER="bam"
PASSWORD="PassRoot"
HOST="localhost"

# Fonction pour afficher les messages avec timestamp
echo_status() {
    echo "[$(date '+%H:%M:%S')] $1"
}

# Test de connexion à la base de données
echo_status "Test de connexion à MySQL :"
if $MYSQL -u"$USER" -p"$PASSWORD" -h"$HOST" -e "USE $DB" 2>/dev/null; then
    echo_status " Connexion MySQL réussie"
else
    echo_status " Impossible de se connecter à MySQL"
    exit 1
fi

# Liste des salles autorisées
ALLOWED_ROOMS=(
    "hall-entrée-principale"
    "B001" "B105" "B212"
    "C006" "C102"
    "E006" "E105" "E208"
)

# Configuration MQTT
MQTT_BROKER="mqtt.iut-blagnac.fr"
MQTT_PORT="1883"
MQTT_TOPIC="AM107/by-room/#"
TMPFILE="/tmp/mqtt_data.json"

echo_status "Démarrage du script de collecte des données"
echo_status "Broker MQTT: $MQTT_BROKER:$MQTT_PORT"
echo_status "Topic MQTT: $MQTT_TOPIC"

# Boucle principale
while true; do
    echo_status "Récupération des dernières données MQTT :"
    
    # Récupération MQTT avec timeout de 5 secondes
    if ! timeout 5s mosquitto_sub -h "$MQTT_BROKER" -p "$MQTT_PORT" \
        -t "$MQTT_TOPIC" -C 1 -R > "$TMPFILE" 2>/dev/null; then
        echo_status " Timeout ou échec réception MQTT"
        sleep 1
        continue
    fi
    echo_status " Données MQTT reçues"


    # Vérification contenu
    if ! grep -q "temperature" "$TMPFILE"; then
        echo_status " Données invalides"
        continue
    fi
    echo_status " Format données OK"

    # Extraction données
    temperature=$(jq -r '.[0].temperature' "$TMPFILE")
    humidity=$(jq -r '.[0].humidity' "$TMPFILE")
    co2=$(jq -r '.[0].co2' "$TMPFILE")
    room=$(jq -r '.[1].room' "$TMPFILE" | tr '[:upper:]' '[:lower:]')

    echo_status "Données extraites:"
    echo_status "- Salle: $room"
    echo_status "- Température: ${temperature}°C"
    echo_status "- Humidité: ${humidity}%"
    echo_status "- CO2: ${co2}ppm"

    # Vérification données
    if [[ -z "$room" || -z "$temperature" || -z "$humidity" || -z "$co2" ]]; then
        echo_status " Données manquantes"
        continue
    fi

    # Vérification salle
    if [[ ! " ${ALLOWED_ROOMS[@]} " =~ " ${room} " ]]; then
        echo_status " Salle '$room' non autorisée"
        continue
    fi
    echo_status " Salle autorisée"

    # Date et heure
    current_date=$(date "+%Y-%m-%d")
    current_time=$(date "+%H:%M:%S")

    # Configuration capteurs
    declare -A SENSORS=(
        ["temperature"]="temp$room"
        ["humidity"]="humi$room"
        ["co2"]="co2$room"
    )

    declare -A UNITS=(
        ["temperature"]="degres"
        ["humidity"]="%"
        ["co2"]="ppm"
    )

    declare -A VALUES=(
        ["temperature"]="$temperature"
        ["humidity"]="$humidity"
        ["co2"]="$co2"
    )

    # Traitement capteurs
    for type in "${!SENSORS[@]}"; do
        sensor_name=${SENSORS[$type]}
        value=${VALUES[$type]}
        unit=${UNITS[$type]}

        echo_status "Traitement capteur: $sensor_name"

        if [[ -z "$value" || "$value" == "null" ]]; then
            echo_status " Pas de valeur pour $type"
            continue
        fi

        echo_status "Insertion en base..."
        if $MYSQL -u"$USER" -p"$PASSWORD" -h"$HOST" "$DB" <<EOF
            INSERT INTO capteur (NOM_capteur, type, unité, NOM_salle)
            VALUES ('$sensor_name', '$type', '$unit', '$room')
            ON DUPLICATE KEY UPDATE
            type='$type', unité='$unit', NOM_salle='$room';

            INSERT INTO mesure (date, horaire, valeur, NOM_capteur)
            VALUES ('$current_date', '$current_time', $value, '$sensor_name');
EOF
        then
            echo_status " OK: $sensor_name = $value $unit"
        else
            echo_status " Insertion échouée pour $sensor_name"
        fi
    done
done