#!/bin/bash

# Configuration
MYSQL="/opt/lampp/bin/mysql"
DB="sae23bam"
USER="bam"
PASSW="PassRoot"
HOST="localhost"
FICHTMP="/tmp/mqtt_data.json"
TIMEOUT="0.5"

# Function to display messages with timestamp
echo_status() {
    echo "[$(date '+%H:%M:%S')] $1"
}

# Test connection to the MySQL database
echo_status "Test de connexion à la base de données MySQL"
if $MYSQL -u"$USER" -p"$PASSW" -h"$HOST" -e "USE $DB" 2>/dev/null; then
    echo_status "Connexion à la base de données MySQL réussie"
else
    echo_status "Impossible de se connecter à la base de données MySQL"
    exit 1
fi

# List of authorized rooms with their MQTT topic
ALLOWED_ROOMS=(
    "hall d'entrée"
    "B001" "B105" "B212"
    "C006" "C102"
    "E006" "E105" "E208"
)

# MQTT configuration
MQTT_BROKER="mqtt.iut-blagnac.fr"
MQTT_PORT="1883"

# Build the list of topics for authorized rooms only
MQTT_TOPICS=""
for room in "${ALLOWED_ROOMS[@]}"; do
    mqtt_room=$(echo "$room" | tr '[:lower:]' '[:upper:]' | tr " '" "-")
    MQTT_TOPICS+="AM107/by-room/$mqtt_room/data "
done

echo_status "Configuration:"
echo_status "Broker MQTT: $MQTT_BROKER:$MQTT_PORT"
echo_status "Timeout: ${TIMEOUT}s par salle"
echo_status "Topics utilisés:"
for topic in $MQTT_TOPICS; do
    echo_status "   - $topic"
done

# Main loop
while true; do
    echo_status "Captation des données"
    
    for topic in $MQTT_TOPICS; do
        echo_status "Lecture de $topic"
        if ! timeout $TIMEOUT mosquitto_sub -h "$MQTT_BROKER" -p "$MQTT_PORT" \
            -t "$topic" -C 1 -R > "$FICHTMP" 2>/dev/null; then
            echo_status "   ✗ Timeout ou échec de réception"
            continue
        fi

        if ! grep -q "temperature" "$FICHTMP"; then
            echo_status "   ✗ Format de données invalide"
            continue
        fi

        # Data extraction
        temperature=$(jq -r '.[0].temperature' "$FICHTMP")
        humidity=$(jq -r '.[0].humidity' "$FICHTMP")
        illumination=$(jq -r '.[0].illumination' "$FICHTMP")
        co2=$(jq -r '.[0].co2' "$FICHTMP")
        room=$(jq -r '.[1].room' "$FICHTMP" | tr '[:upper:]' '[:lower:]')

        # Data verification
        if [[ -z "$room" || -z "$temperature" || -z "$humidity" || -z "$co2" ]]; then
            echo_status "   ✗ Données incomplètes"
            continue
        fi

        echo_status "   ✓ Données reçues:"
        echo_status "     • Salle: $room"
        echo_status "     • Température: ${temperature}°C"
        echo_status "     • Humidité: ${humidity}%"
        echo_status "     • CO2: ${co2}ppm"

        # Current date and time
        current_date=$(date "+%Y-%m-%d")
        current_time=$(date "+%H:%M:%S")

        # Sensor configuration in the specified order
        declare -A SENSORS=(
            ["temperature"]="temp$room"
            ["humidity"]="humi$room"
            ["illumination"]="illu$room"
            ["co2"]="co2$room"
        )

        declare -A UNITS=(
            ["temperature"]="degres"
            ["humidity"]="%"
            ["illumination"]="lux"
            ["co2"]="ppm"
        )

        declare -A VALUES=(
            ["temperature"]="$temperature"
            ["humidity"]="$humidity"
            ["illumination"]="$illumination"
            ["co2"]="$co2"
        )

        # Process sensors in the specified order
        for type in "temperature" "humidity" "illumination" "co2"; do
            sensor_name=${SENSORS[$type]}
            value=${VALUES[$type]}
            unit=${UNITS[$type]}

            echo_status "   → Traitement: $sensor_name"

            if [[ -z "$value" || "$value" == "null" ]]; then
                echo_status "     ✗ Valeur manquante"
                continue
            fi

            if $MYSQL -u"$USER" -p"$PASSW" -h"$HOST" "$DB" <<EOF
                INSERT INTO capteur (NOM_capteur, type, unité, NOM_salle)
                VALUES ('$sensor_name', '$type', '$unit', '$room')
                ON DUPLICATE KEY UPDATE
                type='$type', unité='$unit', NOM_salle='$room';

                INSERT INTO mesure (date, horaire, valeur, NOM_capteur)
                VALUES ('$current_date', '$current_time', $value, '$sensor_name');
EOF
            then
                echo_status "      $value $unit"
            else
                echo_status "      Erreur d'insertion"
            fi
        done
    done

    echo_status "Pause 5 secondes avant la reprise"
    sleep 5
done
