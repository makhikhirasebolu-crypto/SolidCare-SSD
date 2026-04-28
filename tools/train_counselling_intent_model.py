from __future__ import annotations

import argparse
import csv
import json
import re
import xml.etree.ElementTree as ET
from collections import Counter
from dataclasses import dataclass
from datetime import datetime, UTC
from pathlib import Path
from zipfile import ZipFile

import joblib
from sklearn.feature_extraction.text import TfidfVectorizer
from sklearn.metrics import accuracy_score, classification_report
from sklearn.model_selection import train_test_split
from sklearn.pipeline import Pipeline
from sklearn.svm import LinearSVC


XML_NS = {
    "a": "http://schemas.openxmlformats.org/spreadsheetml/2006/main",
    "r": "http://schemas.openxmlformats.org/officeDocument/2006/relationships",
}
CELL_REF_PATTERN = re.compile(r"([A-Z]+)\d+")
REQUIRED_COLUMNS = {"User_Input", "Intent", "Context", "AI_Response", "Next_Action"}


@dataclass(frozen=True)
class TrainingSample:
    user_input: str
    intent: str
    context: str
    ai_response: str
    next_action: str


def column_letters(cell_reference: str) -> str:
    match = CELL_REF_PATTERN.fullmatch(cell_reference)
    if not match:
        raise ValueError(f"Unsupported cell reference: {cell_reference}")
    return match.group(1)


def read_xlsx_rows(path: Path) -> list[dict[str, str]]:
    with ZipFile(path) as workbook_zip:
        workbook_xml = ET.fromstring(workbook_zip.read("xl/workbook.xml"))
        rels_xml = ET.fromstring(workbook_zip.read("xl/_rels/workbook.xml.rels"))
        relationship_targets = {
            relation.attrib["Id"]: relation.attrib["Target"].lstrip("/")
            for relation in rels_xml
        }

        first_sheet = workbook_xml.find("a:sheets", XML_NS)[0]
        relation_id = first_sheet.attrib[
            "{http://schemas.openxmlformats.org/officeDocument/2006/relationships}id"
        ]
        sheet_path = relationship_targets[relation_id]

        shared_strings: list[str] = []
        if "xl/sharedStrings.xml" in workbook_zip.namelist():
            shared_strings_xml = ET.fromstring(workbook_zip.read("xl/sharedStrings.xml"))
            for shared_item in shared_strings_xml.findall("a:si", XML_NS):
                shared_strings.append(
                    "".join(
                        text_node.text or ""
                        for text_node in shared_item.iterfind(".//a:t", XML_NS)
                    )
                )

        def read_cell_value(cell: ET.Element) -> str:
            cell_type = cell.attrib.get("t")
            value_node = cell.find("a:v", XML_NS)
            inline_string = cell.find("a:is", XML_NS)

            if cell_type == "s":
                if value_node is None or value_node.text is None:
                    return ""
                return shared_strings[int(value_node.text)]

            if cell_type == "inlineStr" and inline_string is not None:
                return "".join(
                    text_node.text or ""
                    for text_node in inline_string.iterfind(".//a:t", XML_NS)
                )

            if value_node is None or value_node.text is None:
                return ""

            return value_node.text

        sheet_xml = ET.fromstring(workbook_zip.read(sheet_path))
        rows = []
        for row in sheet_xml.findall(".//a:sheetData/a:row", XML_NS):
            row_values: dict[str, str] = {}
            for cell in row.findall("a:c", XML_NS):
                row_values[column_letters(cell.attrib["r"])] = read_cell_value(cell).strip()
            rows.append(row_values)

        if not rows:
            raise ValueError("The workbook is empty.")

        header_map = rows[0]
        headers_by_column = {column: value for column, value in header_map.items() if value}
        if not headers_by_column:
            raise ValueError("The workbook header row is missing.")

        data_rows = []
        for row in rows[1:]:
            normalized_row = {
                header: row.get(column, "")
                for column, header in headers_by_column.items()
            }
            data_rows.append(normalized_row)

        return data_rows


def read_csv_rows(path: Path) -> list[dict[str, str]]:
    with path.open("r", encoding="utf-8-sig", newline="") as csv_file:
        raw_rows = list(csv.reader(csv_file))

    if not raw_rows:
        raise ValueError("The CSV file is empty.")

    header_index = None
    for index, row in enumerate(raw_rows):
        if REQUIRED_COLUMNS.issubset({cell.strip() for cell in row if cell.strip()}):
            header_index = index
            break

    if header_index is None:
        raise ValueError("The CSV header row is missing required counselling columns.")

    headers = [header.strip() for header in raw_rows[header_index]]
    data_rows = []
    for row in raw_rows[header_index + 1 :]:
        if not any(cell.strip() for cell in row):
            continue
        normalized_row = {
            header: (row[position].strip() if position < len(row) else "")
            for position, header in enumerate(headers)
            if header
        }
        data_rows.append(normalized_row)

    return data_rows


def read_dataset_rows(path: Path) -> list[dict[str, str]]:
    suffix = path.suffix.lower()
    if suffix == ".csv":
        return read_csv_rows(path)
    if suffix == ".xlsx":
        return read_xlsx_rows(path)
    raise ValueError(f"Unsupported dataset format: {path.suffix}")


def build_samples(path: Path) -> list[TrainingSample]:
    rows = read_dataset_rows(path)
    if not rows:
        raise ValueError("The dataset has no training rows.")

    missing_columns = REQUIRED_COLUMNS.difference(rows[0].keys())
    if missing_columns:
        raise ValueError(
            "The dataset is missing required columns: "
            + ", ".join(sorted(missing_columns))
        )

    samples = []
    for row in rows:
        sample = TrainingSample(
            user_input=row.get("User_Input", ""),
            intent=row.get("Intent", ""),
            context=row.get("Context", ""),
            ai_response=row.get("AI_Response", ""),
            next_action=row.get("Next_Action", ""),
        )
        if not sample.user_input or not sample.intent:
            continue
        samples.append(sample)

    if not samples:
        raise ValueError("No usable training rows were found.")

    return samples


def train_intent_model(samples: list[TrainingSample]) -> tuple[Pipeline, dict[str, object]]:
    texts = [
        f"user_input: {sample.user_input}\ncontext: {sample.context or 'None'}"
        for sample in samples
    ]
    labels = [sample.intent for sample in samples]

    x_train, x_test, y_train, y_test = train_test_split(
        texts,
        labels,
        test_size=0.2,
        random_state=42,
        stratify=labels,
    )

    pipeline = Pipeline(
        steps=[
            (
                "tfidf",
                TfidfVectorizer(
                    ngram_range=(1, 2),
                    min_df=2,
                    strip_accents="unicode",
                    sublinear_tf=True,
                ),
            ),
            ("classifier", LinearSVC()),
        ]
    )
    pipeline.fit(x_train, y_train)

    predictions = pipeline.predict(x_test)
    metrics = {
        "target": "Intent",
        "model_type": "LinearSVC",
        "accuracy": accuracy_score(y_test, predictions),
        "train_size": len(x_train),
        "test_size": len(x_test),
        "label_distribution": dict(sorted(Counter(labels).items())),
        "classification_report": classification_report(
            y_test,
            predictions,
            output_dict=True,
            zero_division=0,
        ),
    }

    return pipeline, metrics


def save_outputs(
    model: Pipeline,
    metrics: dict[str, object],
    dataset_path: Path,
    output_dir: Path,
) -> tuple[Path, Path]:
    output_dir.mkdir(parents=True, exist_ok=True)

    timestamp = datetime.now(UTC).strftime("%Y%m%dT%H%M%SZ")
    model_path = output_dir / f"counselling-intent-linear-svc-{timestamp}.joblib"
    metrics_path = output_dir / f"counselling-intent-linear-svc-{timestamp}.metrics.json"

    artifact = {
        "model": model,
        "target": "Intent",
        "model_type": "LinearSVC",
        "dataset_path": str(dataset_path),
        "trained_at_utc": timestamp,
    }
    joblib.dump(artifact, model_path)

    with metrics_path.open("w", encoding="utf-8") as metrics_file:
        json.dump(
            {
                **metrics,
                "dataset_path": str(dataset_path),
                "trained_at_utc": timestamp,
            },
            metrics_file,
            indent=2,
        )

    return model_path, metrics_path


def parse_args() -> argparse.Namespace:
    parser = argparse.ArgumentParser(
        description="Train a counselling intent classifier from a CSV or Excel dataset."
    )
    parser.add_argument(
        "--dataset",
        default="storage/app/training-datasets/counselling.csv",
        help="Path to the CSV or Excel dataset.",
    )
    parser.add_argument(
        "--output-dir",
        default="storage/app/trained-models",
        help="Directory where the trained model and metrics will be stored.",
    )
    return parser.parse_args()


def main() -> int:
    args = parse_args()
    dataset_path = Path(args.dataset).resolve()
    output_dir = Path(args.output_dir).resolve()

    if not dataset_path.exists():
        raise FileNotFoundError(f"Dataset not found: {dataset_path}")

    samples = build_samples(dataset_path)
    model, metrics = train_intent_model(samples)
    model_path, metrics_path = save_outputs(model, metrics, dataset_path, output_dir)

    print(f"Dataset: {dataset_path}")
    print(f"Samples used: {len(samples)}")
    print(f"Model: {metrics['model_type']} target={metrics['target']}")
    print(f"Accuracy: {metrics['accuracy']:.4f}")
    print(f"Saved model: {model_path}")
    print(f"Saved metrics: {metrics_path}")

    return 0


if __name__ == "__main__":
    raise SystemExit(main())
