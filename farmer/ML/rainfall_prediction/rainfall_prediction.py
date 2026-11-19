"""Rainfall prediction command line helper.

Arguments: region (SUBDIVISION) and month column. Prints the average rainfall
for that region and month across all years in the bundled dataset.
"""

import math
import sys
from pathlib import Path

import pandas as pd

BASE_DIR = Path(__file__).resolve().parent
DATASET = BASE_DIR / "rainfall_in_india_1901-2015.csv"


def main():
    if len(sys.argv) < 3:
        print("ERROR: region and month are required.")
        return 1

    region = str(sys.argv[1]).strip().upper()
    month = str(sys.argv[2]).strip()

    dataframe = pd.read_csv(DATASET)
    dataframe["SUBDIVISION"] = dataframe["SUBDIVISION"].astype(str).str.strip().str.upper()

    if month not in dataframe.columns:
        print("ERROR: unknown month.")
        return 1

    region_data = dataframe[dataframe["SUBDIVISION"] == region]
    if region_data.empty:
        print("ERROR: unknown region.")
        return 1

    average = region_data[month].mean()
    if average is None or (isinstance(average, float) and math.isnan(average)):
        print("No rainfall data is available for this region and month.")
        return 0

    print(round(float(average), 2))
    return 0


if __name__ == "__main__":
    sys.exit(main())
