"""Fertilizer recommendation command line helper.

Arguments: temperature, humidity, soil moisture, soil type, crop type,
nitrogen, potassium, phosphorous. Prints the recommended fertilizer name.
"""

import json
import sys
import warnings
from pathlib import Path

import pandas as pd
from sklearn.preprocessing import LabelEncoder
from sklearn.tree import DecisionTreeClassifier

warnings.filterwarnings("ignore")

BASE_DIR = Path(__file__).resolve().parent
DATASET = BASE_DIR / "fertilizer_recommendation.csv"


def parse_arg(value):
    value = str(value).strip()
    if len(value) >= 2 and value[0] == '"' and value[-1] == '"':
        try:
            return json.loads(value)
        except json.JSONDecodeError:
            return value[1:-1]
    return value


def match_label(encoder, value):
    wanted = str(value).strip().lower()
    for label in encoder.classes_:
        if str(label).strip().lower() == wanted:
            return label
    return None


def main():
    if len(sys.argv) < 9:
        print("ERROR: eight input values are required.")
        return 1

    try:
        temperature = float(parse_arg(sys.argv[1]))
        humidity = float(parse_arg(sys.argv[2]))
        soil_moisture = float(parse_arg(sys.argv[3]))
        nitrogen = float(parse_arg(sys.argv[6]))
        potassium = float(parse_arg(sys.argv[7]))
        phosphorous = float(parse_arg(sys.argv[8]))
    except (TypeError, ValueError):
        print("ERROR: numeric inputs are invalid.")
        return 1

    data = pd.read_csv(DATASET)

    le_soil = LabelEncoder()
    data["Soil Type"] = le_soil.fit_transform(data["Soil Type"].astype(str).str.strip())
    le_crop = LabelEncoder()
    data["Crop Type"] = le_crop.fit_transform(data["Crop Type"].astype(str).str.strip())

    soil_label = match_label(le_soil, parse_arg(sys.argv[4]))
    crop_label = match_label(le_crop, parse_arg(sys.argv[5]))
    if soil_label is None or crop_label is None:
        print("ERROR: unknown soil type or crop type.")
        return 1

    x = data.iloc[:, :8]
    y = data.iloc[:, -1]
    model = DecisionTreeClassifier(random_state=0)
    model.fit(x, y)

    user_input = [[
        temperature,
        humidity,
        soil_moisture,
        le_soil.transform([soil_label])[0],
        le_crop.transform([crop_label])[0],
        nitrogen,
        potassium,
        phosphorous,
    ]]

    prediction = model.predict(user_input)
    print(str(prediction[0]))
    return 0


if __name__ == "__main__":
    sys.exit(main())
