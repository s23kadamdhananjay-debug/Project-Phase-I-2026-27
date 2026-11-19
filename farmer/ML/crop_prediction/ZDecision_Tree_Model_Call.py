"""Crop prediction command line helper.

Tries the pre-trained decision tree stored in filetest2.pkl. If the pickle was
created with an incompatible Python/scikit-learn version, it falls back to a
frequency lookup over the same preprocessed dataset, so the UI always receives
a useful answer.
"""

import csv
import json
import sys
from collections import Counter
from pathlib import Path

BASE_DIR = Path(__file__).resolve().parent
DATASET = BASE_DIR / "preprocessed2.csv"
MODEL = BASE_DIR / "filetest2.pkl"

HEADER = ["State_Name", "District_Name", "Season", "Crop"]


def clean(value):
    return " ".join(str(value).strip().split())


def parse_arg(value):
    value = str(value).strip()
    if len(value) >= 2 and value[0] == '"' and value[-1] == '"':
        try:
            return json.loads(value)
        except json.JSONDecodeError:
            return value[1:-1]
    return value


class Question:
    def __init__(self, column, value):
        self.column = column
        self.value = value

    def match(self, example):
        return example[self.column] == self.value

    def __repr__(self):
        return "Is %s == %s?" % (HEADER[self.column], str(self.value))


class Leaf:
    def __init__(self, data):
        self.predictions = Counter(row[-1] for row in data)


class Decision_Node:
    def __init__(self, question, true_branch, false_branch):
        self.question = question
        self.true_branch = true_branch
        self.false_branch = false_branch


def load_rows():
    with DATASET.open(newline="", encoding="utf-8") as handle:
        reader = csv.DictReader(handle)
        return [row for row in reader if row.get("Crop")]


def find_matches(rows, state, district, season):
    tiers = [
        (state, district, season),
        (state, district, None),
        (state, None, season),
        (state, None, None),
    ]
    for wanted_state, wanted_district, wanted_season in tiers:
        matched = []
        for row in rows:
            if clean(row["State_Name"]).lower() != state.lower():
                continue
            if wanted_district is not None and clean(row["District_Name"]).lower() != wanted_district.lower():
                continue
            if wanted_season is not None and clean(row["Season"]).lower() != wanted_season.lower():
                continue
            matched.append(row)
        if matched:
            return matched
    return []


def predict_from_dataset(state, district, season):
    rows = find_matches(load_rows(), state, district, season)
    if not rows:
        return []
    counts = Counter(clean(row["Crop"]) for row in rows)
    return [crop for crop, _ in counts.most_common(6)]


def predict_from_model(state, district, season):
    try:
        import joblib
    except Exception:
        return []

    try:
        model = joblib.load(MODEL)
        row = [state, district, season]
        predictions = classify(row, model)
        return [crop for crop, _ in Counter(predictions).most_common(6)]
    except Exception:
        return []


def classify(row, node):
    if isinstance(node, Leaf):
        return list(node.predictions.elements())
    if node.question.match(row):
        return classify(row, node.true_branch)
    return classify(row, node.false_branch)


def main():
    if len(sys.argv) < 4:
        print("ERROR: state, district and season are required.")
        return 1

    state = clean(parse_arg(sys.argv[1]))
    district = clean(parse_arg(sys.argv[2]))
    season = clean(parse_arg(sys.argv[3]))

    crops = predict_from_dataset(state, district, season)
    if not crops:
        crops = predict_from_model(state, district, season)

    if not crops:
        print("No crop data is available for the selected state, district and season.")
        return 0

    print(", ".join(crops))
    return 0


if __name__ == "__main__":
    sys.exit(main())
