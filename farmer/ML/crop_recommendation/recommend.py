"""Crop recommendation command line helper.

Reads seven soil/climate values as CLI arguments, trains the random forest
model on the bundled dataset and prints the recommended crop name.
"""

import json
import sys
from pathlib import Path

import numpy as np
import pandas as pd
from sklearn.ensemble import RandomForestClassifier
from sklearn.model_selection import train_test_split

BASE_DIR = Path(__file__).resolve().parent
DATASET = BASE_DIR / "Crop_recommendation.csv"


def parse_arg(value):
    value = str(value).strip()
    if len(value) >= 2 and value[0] == '"' and value[-1] == '"':
        try:
            return json.loads(value)
        except json.JSONDecodeError:
            return value[1:-1]
    return value


def main():
    if len(sys.argv) < 8:
        print("ERROR: N, P, K, temperature, humidity, pH and rainfall are required.")
        return 1

    try:
        features = [float(parse_arg(sys.argv[index])) for index in range(1, 8)]
    except (TypeError, ValueError):
        print("ERROR: all inputs must be numeric.")
        return 1

    dataset = pd.read_csv(DATASET)
    x = dataset.iloc[:, :-1].values
    y = dataset.iloc[:, -1].values

    x_train, _, y_train, _ = train_test_split(x, y, test_size=0.2, random_state=0)
    classifier = RandomForestClassifier(n_estimators=10, criterion="entropy", random_state=0)
    classifier.fit(x_train, y_train)

    prediction = classifier.predict(np.array([features]))
    print(str(prediction[0]))
    return 0


if __name__ == "__main__":
    sys.exit(main())
