"""Yield prediction command line helper.

Arguments: state, district, season, crop, area. Trains a random forest on the
bundled Karnataka dataset and prints the predicted production in quintals.
"""

import json
import sys
from pathlib import Path

import numpy as np
import pandas as pd
from sklearn.ensemble import RandomForestRegressor
from sklearn.model_selection import train_test_split
from sklearn.preprocessing import OneHotEncoder

BASE_DIR = Path(__file__).resolve().parent
DATASET = BASE_DIR / "crop_production_karnataka.csv"
CATEGORICAL = ["State_Name", "District_Name", "Season", "Crop"]


def parse_arg(value):
    value = str(value).strip()
    if len(value) >= 2 and value[0] == '"' and value[-1] == '"':
        try:
            return json.loads(value)
        except json.JSONDecodeError:
            return value[1:-1]
    return value


def main():
    if len(sys.argv) < 6:
        print("ERROR: state, district, season, crop and area are required.")
        return 1

    state = str(parse_arg(sys.argv[1])).strip()
    district = str(parse_arg(sys.argv[2])).strip()
    season = str(parse_arg(sys.argv[3])).strip()
    crop = str(parse_arg(sys.argv[4])).strip()

    try:
        area = float(parse_arg(sys.argv[5]))
    except (TypeError, ValueError):
        print("ERROR: area must be numeric.")
        return 1

    dataframe = pd.read_csv(DATASET)
    dataframe = dataframe.drop(["Crop_Year"], axis=1)
    dataframe = dataframe.dropna(subset=["Production"])

    # The CSV contains padded labels such as "Kharif     "; normalise both the
    # dataset and the user input so the encoder sees identical values.
    for column in CATEGORICAL:
        dataframe[column] = dataframe[column].astype(str).str.strip().str.lower()

    features = dataframe.drop(["Production"], axis=1)
    target = dataframe["Production"]

    x_train, _, y_train, _ = train_test_split(features, target, test_size=0.2, random_state=42)

    encoder = OneHotEncoder(handle_unknown="ignore")
    encoder.fit(x_train[CATEGORICAL])

    x_train_encoded = encoder.transform(x_train[CATEGORICAL])
    x_train_final = np.hstack((x_train_encoded.toarray(), x_train.drop(CATEGORICAL, axis=1)))

    model = RandomForestRegressor(n_estimators=100, random_state=42)
    model.fit(x_train_final, y_train)

    user_input = pd.DataFrame(
        [[state.lower(), district.lower(), season.lower(), crop.lower(), area]],
        columns=["State_Name", "District_Name", "Season", "Crop", "Area"],
    )

    encoded = encoder.transform(user_input[CATEGORICAL])
    final_input = np.hstack((encoded.toarray(), user_input[["Area"]].values.astype(float)))

    prediction = model.predict(final_input)
    print(float(prediction[0]))
    return 0


if __name__ == "__main__":
    sys.exit(main())
