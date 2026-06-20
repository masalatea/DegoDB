#!/usr/bin/env python3
"""Audit generated-name migration snapshots.

This tool intentionally does not rewrite project files. It indexes generated
artifact snapshots, compares before/after indexes with an explicit keyword map,
and lists old keyword occurrences for review.
"""

from __future__ import annotations

import argparse
import hashlib
import json
import re
import shutil
from pathlib import Path
from typing import Any


SKIP_DIRS = {".git", ".idea", ".vscode", "node_modules", "vendor", "__pycache__"}
BINARY_EXTENSIONS = {
    ".avif",
    ".bmp",
    ".class",
    ".db",
    ".gif",
    ".ico",
    ".jpeg",
    ".jpg",
    ".pdf",
    ".png",
    ".sqlite",
    ".sqlite3",
    ".ttf",
    ".webp",
    ".woff",
    ".woff2",
    ".zip",
}


def is_identifier_char(value: str) -> bool:
    return re.match(r"[A-Za-z0-9_]", value) is not None


def read_text(path: Path) -> str | None:
    if path.suffix.lower() in BINARY_EXTENSIONS:
        return None
    try:
        data = path.read_bytes()
    except OSError as exc:
        raise RuntimeError(f"failed to read {path}: {exc}") from exc
    if b"\0" in data:
        return None
    try:
        return data.decode("utf-8")
    except UnicodeDecodeError:
        return data.decode("utf-8", errors="replace")


def sha256_file(path: Path) -> str:
    digest = hashlib.sha256()
    with path.open("rb") as handle:
        for chunk in iter(lambda: handle.read(1024 * 1024), b""):
            digest.update(chunk)
    return digest.hexdigest()


def iter_files(root: Path) -> list[Path]:
    files: list[Path] = []
    for path in root.rglob("*"):
        if any(part in SKIP_DIRS for part in path.parts):
            continue
        if path.is_file():
            files.append(path)
    return sorted(files, key=lambda item: item.relative_to(root).as_posix())


def php_namespace(source: str) -> str:
    match = re.search(r"^\s*namespace\s+([A-Za-z_][A-Za-z0-9_\\]*)\s*;", source, re.MULTILINE)
    return match.group(1) if match else ""


def php_symbols(source: str, relative_path: str) -> list[dict[str, Any]]:
    namespace = php_namespace(source)
    symbols: list[dict[str, Any]] = []
    class_matches = list(
        re.finditer(
            r"^\s*(?:(?:final|abstract|readonly)\s+)*"
            r"(class|interface|trait|enum)\s+([A-Za-z_][A-Za-z0-9_]*)\b",
            source,
            re.MULTILINE,
        )
    )

    for index, match in enumerate(class_matches):
        class_kind = match.group(1)
        class_name = match.group(2)
        scope = namespace
        class_scope = f"{namespace}\\{class_name}" if namespace else class_name
        symbols.append(
            {
                "kind": class_kind,
                "name": class_name,
                "scope": scope,
                "path": relative_path,
                "line": source.count("\n", 0, match.start()) + 1,
            }
        )

        block_end = class_matches[index + 1].start() if index + 1 < len(class_matches) else len(source)
        block = source[match.end() : block_end]
        block_line_offset = source.count("\n", 0, match.end())

        for prop_match in re.finditer(
            r"^\s*(public|protected|private)\s+"
            r"(?:(?:static|readonly)\s+)*"
            r"(?:[^\s$=;]+(?:\s*\|\s*[^\s$=;]+)*\s+)?"
            r"\$([A-Za-z_][A-Za-z0-9_]*)\b",
            block,
            re.MULTILINE,
        ):
            symbols.append(
                {
                    "kind": "property",
                    "name": prop_match.group(2),
                    "scope": class_scope,
                    "path": relative_path,
                    "line": block_line_offset + block.count("\n", 0, prop_match.start()) + 1,
                }
            )

        for method_match in re.finditer(
            r"^\s*(public|protected|private)\s+"
            r"(?:(?:static|final|abstract)\s+)*function\s+"
            r"([A-Za-z_][A-Za-z0-9_]*)\s*\(",
            block,
            re.MULTILINE,
        ):
            symbols.append(
                {
                    "kind": "method",
                    "name": method_match.group(2),
                    "scope": class_scope,
                    "path": relative_path,
                    "line": block_line_offset + block.count("\n", 0, method_match.start()) + 1,
                }
            )

        for const_match in re.finditer(
            r"^\s*(?:(?:public|protected|private)\s+)?const\s+([A-Z_][A-Z0-9_]*)\b",
            block,
            re.MULTILINE,
        ):
            symbols.append(
                {
                    "kind": "constant",
                    "name": const_match.group(1),
                    "scope": class_scope,
                    "path": relative_path,
                    "line": block_line_offset + block.count("\n", 0, const_match.start()) + 1,
                }
            )

    return symbols


def json_symbols(source: str, relative_path: str) -> list[dict[str, Any]]:
    try:
        data = json.loads(source)
    except json.JSONDecodeError:
        return []

    symbols: list[dict[str, Any]] = []
    components = data.get("components") if isinstance(data, dict) else None
    schemas = components.get("schemas") if isinstance(components, dict) else None
    if isinstance(schemas, dict):
        for name in sorted(schemas):
            symbols.append({"kind": "openapi_schema", "name": name, "scope": "", "path": relative_path, "line": 0})

    paths = data.get("paths") if isinstance(data, dict) else None
    if isinstance(paths, dict):
        for name in sorted(paths):
            symbols.append({"kind": "route_path", "name": name, "scope": "", "path": relative_path, "line": 0})

    return symbols


def index_root(root: Path) -> dict[str, Any]:
    if not root.is_dir():
        raise RuntimeError(f"root directory not found: {root}")

    files: list[dict[str, Any]] = []
    symbols: list[dict[str, Any]] = []
    for path in iter_files(root):
        relative_path = path.relative_to(root).as_posix()
        text = read_text(path)
        file_record = {
            "path": relative_path,
            "sha256": sha256_file(path),
            "size": path.stat().st_size,
            "text": text is not None,
        }
        files.append(file_record)
        if text is None:
            continue
        if path.suffix.lower() == ".php":
            symbols.extend(php_symbols(text, relative_path))
        elif path.suffix.lower() == ".json":
            symbols.extend(json_symbols(text, relative_path))

    return {
        "schema": "generated-name-migration-index-v1",
        "root": root.as_posix(),
        "file_count": len(files),
        "symbol_count": len(symbols),
        "files": files,
        "symbols": symbols,
    }


def capture_root(source_root: Path, output_root: Path) -> dict[str, Any]:
    if not source_root.is_dir():
        raise RuntimeError(f"source root directory not found: {source_root}")
    if output_root.exists() and any(output_root.iterdir()):
        raise RuntimeError(f"output root already exists and is not empty: {output_root}")

    copied_files: list[str] = []
    for source_path in iter_files(source_root):
        relative_path = source_path.relative_to(source_root)
        output_path = output_root / relative_path
        output_path.parent.mkdir(parents=True, exist_ok=True)
        shutil.copy2(source_path, output_path)
        copied_files.append(relative_path.as_posix())

    return {
        "schema": "generated-name-migration-capture-v1",
        "source_root": source_root.as_posix(),
        "output_root": output_root.as_posix(),
        "file_count": len(copied_files),
        "files": copied_files,
    }


def sample_reference_roots(samples_root: Path) -> list[Path]:
    if not samples_root.is_dir():
        raise RuntimeError(f"samples root directory not found: {samples_root}")

    roots = [path for path in samples_root.glob("*/reference") if path.is_dir()]
    return sorted(roots, key=lambda path: path.parent.name)


def capture_samples(samples_root: Path, output_root: Path, phase: str, index: bool) -> dict[str, Any]:
    normalized_phase = trim_phase(phase)
    samples: list[dict[str, Any]] = []
    total_files = 0
    total_symbols = 0

    for reference_root in sample_reference_roots(samples_root):
        sample_name = reference_root.parent.name
        sample_output_root = output_root / sample_name / normalized_phase
        capture = capture_root(reference_root, sample_output_root)
        sample_record: dict[str, Any] = {
            "sample": sample_name,
            "source_root": reference_root.as_posix(),
            "output_root": sample_output_root.as_posix(),
            "file_count": capture["file_count"],
        }
        total_files += int(capture["file_count"])

        if index:
            index_result = index_root(sample_output_root)
            index_output = output_root / sample_name / f"{normalized_phase}-index.json"
            write_json(index_output, index_result, pretty=True)
            sample_record["index_output"] = index_output.as_posix()
            sample_record["symbol_count"] = index_result["symbol_count"]
            total_symbols += int(index_result["symbol_count"])

        samples.append(sample_record)

    return {
        "schema": "generated-name-migration-sample-capture-v1",
        "samples_root": samples_root.as_posix(),
        "output_root": output_root.as_posix(),
        "phase": normalized_phase,
        "sample_count": len(samples),
        "file_count": total_files,
        "symbol_count": total_symbols,
        "samples": samples,
    }


def transform_root(source_root: Path, output_root: Path, keyword_map: list[dict[str, str]]) -> dict[str, Any]:
    if not source_root.is_dir():
        raise RuntimeError(f"source root directory not found: {source_root}")
    if output_root.exists() and any(output_root.iterdir()):
        raise RuntimeError(f"output root already exists and is not empty: {output_root}")
    if keyword_map == []:
        raise RuntimeError("keyword map must not be empty for transform")

    transformed_files: list[dict[str, Any]] = []
    target_paths: dict[str, str] = {}
    for source_path in iter_files(source_root):
        relative_path = source_path.relative_to(source_root).as_posix()
        transformed_relative_path = apply_keyword_map(relative_path, keyword_map)
        if transformed_relative_path in target_paths:
            raise RuntimeError(
                "transform path collision: "
                + target_paths[transformed_relative_path]
                + " and "
                + relative_path
                + " -> "
                + transformed_relative_path
            )
        target_paths[transformed_relative_path] = relative_path

        output_path = output_root / transformed_relative_path
        output_path.parent.mkdir(parents=True, exist_ok=True)
        text = read_text(source_path)
        if text is None:
            shutil.copy2(source_path, output_path)
            text_changed = False
        else:
            transformed_text = apply_keyword_map(text, keyword_map)
            output_path.write_text(transformed_text, encoding="utf-8")
            shutil.copystat(source_path, output_path)
            text_changed = transformed_text != text

        transformed_files.append(
            {
                "old": relative_path,
                "new": transformed_relative_path,
                "path_changed": transformed_relative_path != relative_path,
                "text_changed": text_changed,
            }
        )

    return {
        "schema": "generated-name-migration-transform-v1",
        "source_root": source_root.as_posix(),
        "output_root": output_root.as_posix(),
        "keyword_count": len(keyword_map),
        "file_count": len(transformed_files),
        "path_changed_count": sum(1 for item in transformed_files if item["path_changed"]),
        "text_changed_count": sum(1 for item in transformed_files if item["text_changed"]),
        "files": transformed_files,
    }


def transform_samples(
    samples_snapshot_root: Path,
    output_root: Path,
    source_phase: str,
    output_phase: str,
    keyword_map: list[dict[str, str]],
    index: bool,
) -> dict[str, Any]:
    normalized_source_phase = trim_phase(source_phase)
    normalized_output_phase = trim_phase(output_phase)
    if not samples_snapshot_root.is_dir():
        raise RuntimeError(f"samples snapshot root directory not found: {samples_snapshot_root}")

    samples: list[dict[str, Any]] = []
    total_files = 0
    total_path_changed = 0
    total_text_changed = 0
    total_symbols = 0
    source_roots = sorted(
        [path for path in samples_snapshot_root.glob(f"*/{normalized_source_phase}") if path.is_dir()],
        key=lambda path: path.parent.name,
    )
    if source_roots == []:
        raise RuntimeError(
            f"sample {normalized_source_phase} snapshots not found under: {samples_snapshot_root}"
        )

    for source_root in source_roots:
        sample_name = source_root.parent.name
        sample_output_root = output_root / sample_name / normalized_output_phase
        transform = transform_root(source_root, sample_output_root, keyword_map)
        sample_record: dict[str, Any] = {
            "sample": sample_name,
            "source_root": source_root.as_posix(),
            "output_root": sample_output_root.as_posix(),
            "file_count": transform["file_count"],
            "path_changed_count": transform["path_changed_count"],
            "text_changed_count": transform["text_changed_count"],
        }
        total_files += int(transform["file_count"])
        total_path_changed += int(transform["path_changed_count"])
        total_text_changed += int(transform["text_changed_count"])

        if index:
            index_result = index_root(sample_output_root)
            index_output = output_root / sample_name / f"{normalized_output_phase}-index.json"
            write_json(index_output, index_result, pretty=True)
            sample_record["index_output"] = index_output.as_posix()
            sample_record["symbol_count"] = index_result["symbol_count"]
            total_symbols += int(index_result["symbol_count"])

        samples.append(sample_record)

    return {
        "schema": "generated-name-migration-sample-transform-v1",
        "samples_snapshot_root": samples_snapshot_root.as_posix(),
        "output_root": output_root.as_posix(),
        "source_phase": normalized_source_phase,
        "output_phase": normalized_output_phase,
        "keyword_count": len(keyword_map),
        "sample_count": len(samples),
        "file_count": total_files,
        "path_changed_count": total_path_changed,
        "text_changed_count": total_text_changed,
        "symbol_count": total_symbols,
        "samples": samples,
    }


def trim_phase(value: str) -> str:
    normalized = trim_slug(value)
    if normalized == "":
        raise RuntimeError("phase must not be empty")
    return normalized


def trim_slug(value: str) -> str:
    normalized = re.sub(r"[^A-Za-z0-9_.-]+", "-", value.strip())
    return normalized.strip("-")


def load_json(path: Path) -> Any:
    with path.open("r", encoding="utf-8") as handle:
        return json.load(handle)


def write_json(path: Path, data: Any, pretty: bool) -> None:
    path.parent.mkdir(parents=True, exist_ok=True)
    with path.open("w", encoding="utf-8") as handle:
        json.dump(data, handle, ensure_ascii=False, indent=2 if pretty else None, sort_keys=pretty)
        handle.write("\n")


def load_keyword_map(path: Path | None) -> list[dict[str, str]]:
    if path is None:
        return []
    data = load_json(path)
    pairs: list[dict[str, str]] = []
    if isinstance(data, dict):
        if "keywords" in data and isinstance(data["keywords"], list):
            data = data["keywords"]
        else:
            data = [{"old": key, "new": value} for key, value in data.items()]
    if not isinstance(data, list):
        raise RuntimeError("keyword map must be an object, a list, or {\"keywords\": [...]}")
    for item in data:
        if not isinstance(item, dict):
            raise RuntimeError("keyword map entries must be objects")
        old = str(item.get("old", ""))
        new = str(item.get("new", ""))
        mode = str(item.get("mode", "literal"))
        if old == "":
            raise RuntimeError("keyword map entry has empty old value")
        if mode not in {"literal", "identifier-prefix"}:
            raise RuntimeError("keyword map entry has unsupported mode: " + mode)
        pairs.append({"old": old, "new": new, "mode": mode})
    return sorted(pairs, key=lambda pair: len(pair["old"]), reverse=True)


def apply_keyword_pair(value: str, pair: dict[str, str]) -> str:
    old = pair["old"]
    new = pair["new"]
    if old == "":
        return value
    if pair.get("mode", "literal") == "literal":
        return value.replace(old, new)

    updated: list[str] = []
    start = 0
    while True:
        index = value.find(old, start)
        if index < 0:
            updated.append(value[start:])
            break

        end = index + len(old)
        prev = value[index - 1] if index > 0 else ""
        next_char = value[end] if end < len(value) else ""
        starts_at_boundary = index == 0 or not is_identifier_char(prev)
        ends_at_boundary = end == len(value) or not is_identifier_char(next_char) or next_char.isupper()
        if starts_at_boundary and ends_at_boundary:
            updated.append(value[start:index])
            updated.append(new)
            start = end
        else:
            updated.append(value[start:end])
            start = end

    return "".join(updated)


def apply_keyword_map(value: str, keyword_map: list[dict[str, str]]) -> str:
    updated = value
    for pair in keyword_map:
        updated = apply_keyword_pair(updated, pair)
    return updated


def keyword_pair_match_columns(value: str, pair: dict[str, str]) -> list[int]:
    old = pair["old"]
    if old == "":
        return []

    columns: list[int] = []
    start = 0
    while True:
        index = value.find(old, start)
        if index < 0:
            break

        end = index + len(old)
        if pair.get("mode", "literal") == "literal":
            columns.append(index)
        else:
            prev = value[index - 1] if index > 0 else ""
            next_char = value[end] if end < len(value) else ""
            starts_at_boundary = index == 0 or not is_identifier_char(prev)
            ends_at_boundary = end == len(value) or not is_identifier_char(next_char) or next_char.isupper()
            if starts_at_boundary and ends_at_boundary:
                columns.append(index)

        start = index + max(1, len(old))

    return columns


def replacement_span(old: str, new: str) -> dict[str, Any] | None:
    if old == new or old == "" or new == "":
        return None

    prefix_length = 0
    max_prefix_length = min(len(old), len(new))
    while prefix_length < max_prefix_length and old[prefix_length] == new[prefix_length]:
        prefix_length += 1

    suffix_length = 0
    max_suffix_length = min(len(old), len(new)) - prefix_length
    while (
        suffix_length < max_suffix_length
        and old[len(old) - suffix_length - 1] == new[len(new) - suffix_length - 1]
    ):
        suffix_length += 1

    while suffix_length > 0:
        suffix = old[len(old) - suffix_length :]
        if suffix == "" or not is_identifier_char(suffix[0]):
            break
        suffix_length -= 1

    old_fragment = old[prefix_length : len(old) - suffix_length if suffix_length > 0 else len(old)]
    new_fragment = new[prefix_length : len(new) - suffix_length if suffix_length > 0 else len(new)]
    if old_fragment == "" or new_fragment == "" or old_fragment == new_fragment:
        return None

    return {
        "old": old_fragment,
        "new": new_fragment,
        "prefix": old[:prefix_length],
        "suffix": old[len(old) - suffix_length :] if suffix_length > 0 else "",
        "old_value": old,
        "new_value": new,
    }


def replacement_similarity(old: str, new: str) -> int:
    span = replacement_span(old, new)
    if span is None:
        return -1
    return len(str(span["prefix"])) + len(str(span["suffix"]))


def comparable_identifier(value: str) -> str:
    return re.sub(r"[^A-Za-z0-9]+", "", value).lower()


def best_unmatched_pair(
    old_value: str,
    new_values: list[str],
    used_new_indexes: set[int],
    *,
    allow_normalized_match: bool = False,
) -> tuple[int, int] | None:
    best: tuple[int, int] | None = None
    old_comparable = comparable_identifier(old_value)
    for index, new_value in enumerate(new_values):
        if index in used_new_indexes:
            continue
        score = replacement_similarity(old_value, new_value)
        if score < 1 and not (
            allow_normalized_match
            and old_comparable != ""
            and old_comparable == comparable_identifier(new_value)
        ):
            continue
        if best is None or score > best[1]:
            best = (index, score)
    return best


def append_keyword_candidate(
    candidates: dict[tuple[str, str], dict[str, Any]],
    old: str,
    new: str,
    source: str,
    evidence: dict[str, Any],
) -> None:
    if old == "" or new == "" or old == new:
        return
    key = (old, new)
    candidate = candidates.setdefault(
        key,
        {
            "old": old,
            "new": new,
            "source_count": 0,
            "sources": {},
            "evidence": [],
        },
    )
    candidate["source_count"] += 1
    candidate["sources"][source] = int(candidate["sources"].get(source, 0)) + 1
    if len(candidate["evidence"]) < 10:
        candidate["evidence"].append(evidence)


def derive_keyword_map(before: dict[str, Any], after: dict[str, Any]) -> dict[str, Any]:
    candidates: dict[tuple[str, str], dict[str, Any]] = {}

    before_files = {item["path"]: item for item in before.get("files", [])}
    after_files = {item["path"]: item for item in after.get("files", [])}
    removed_files = sorted(set(before_files) - set(after_files))
    added_files = sorted(set(after_files) - set(before_files))
    used_added_file_indexes: set[int] = set()

    for old_path in removed_files:
        pair = best_unmatched_pair(old_path, added_files, used_added_file_indexes)
        if pair is None:
            continue
        added_index, score = pair
        new_path = added_files[added_index]
        span = replacement_span(old_path, new_path)
        if span is None:
            continue
        used_added_file_indexes.add(added_index)
        append_keyword_candidate(
            candidates,
            str(span["old"]),
            str(span["new"]),
            "file_path",
            {
                "old_path": old_path,
                "new_path": new_path,
                "score": score,
            },
        )

    before_symbols = before.get("symbols", [])
    after_symbols = after.get("symbols", [])
    before_symbol_keys = {symbol_key(item) for item in before_symbols}
    after_symbol_keys = {symbol_key(item) for item in after_symbols}
    removed_symbols = [item for item in before_symbols if symbol_key(item) not in after_symbol_keys]
    added_symbols = [item for item in after_symbols if symbol_key(item) not in before_symbol_keys]
    added_symbol_names_by_kind: dict[str, list[str]] = {}
    for symbol in added_symbols:
        added_symbol_names_by_kind.setdefault(str(symbol.get("kind", "")), []).append(str(symbol.get("name", "")))

    used_symbol_indexes_by_kind: dict[str, set[int]] = {}
    for old_symbol in removed_symbols:
        kind = str(old_symbol.get("kind", ""))
        old_name = str(old_symbol.get("name", ""))
        new_names = added_symbol_names_by_kind.get(kind, [])
        used_indexes = used_symbol_indexes_by_kind.setdefault(kind, set())
        pair = best_unmatched_pair(old_name, new_names, used_indexes, allow_normalized_match=True)
        if pair is None:
            continue
        added_index, score = pair
        new_name = new_names[added_index]
        span = replacement_span(old_name, new_name)
        if span is None:
            continue
        used_indexes.add(added_index)
        append_keyword_candidate(
            candidates,
            str(span["old"]),
            str(span["new"]),
            "symbol_name",
            {
                "kind": kind,
                "old": old_name,
                "new": new_name,
                "old_path": old_symbol.get("path", ""),
                "score": score,
            },
        )

    keyword_candidates = sorted(
        candidates.values(),
        key=lambda item: (-int(item["source_count"]), -len(str(item["old"])), str(item["old"]), str(item["new"])),
    )
    return {
        "schema": "generated-name-migration-derived-keyword-map-v1",
        "before_root": before.get("root", ""),
        "after_root": after.get("root", ""),
        "candidate_count": len(keyword_candidates),
        "candidates": keyword_candidates,
        "keywords": [
            {
                "old": candidate["old"],
                "new": candidate["new"],
            }
            for candidate in keyword_candidates
        ],
    }


def append_derived_candidate(
    candidates: dict[tuple[str, str], dict[str, Any]],
    source_candidate: dict[str, Any],
    sample_name: str,
) -> None:
    old = str(source_candidate.get("old", ""))
    new = str(source_candidate.get("new", ""))
    if old == "" or new == "" or old == new:
        return
    key = (old, new)
    target = candidates.setdefault(
        key,
        {
            "old": old,
            "new": new,
            "source_count": 0,
            "sample_count": 0,
            "samples": {},
            "sources": {},
            "evidence": [],
        },
    )
    source_count = int(source_candidate.get("source_count", 0))
    target["source_count"] += source_count
    target["samples"][sample_name] = int(target["samples"].get(sample_name, 0)) + source_count
    target["sample_count"] = len(target["samples"])
    for source, count in dict(source_candidate.get("sources", {})).items():
        target["sources"][source] = int(target["sources"].get(source, 0)) + int(count)
    for evidence in source_candidate.get("evidence", []):
        if len(target["evidence"]) >= 20:
            break
        if isinstance(evidence, dict):
            target["evidence"].append({"sample": sample_name, **evidence})


def derive_keyword_map_samples(before_root: Path, after_root: Path) -> dict[str, Any]:
    if not before_root.is_dir():
        raise RuntimeError(f"before root directory not found: {before_root}")
    if not after_root.is_dir():
        raise RuntimeError(f"after root directory not found: {after_root}")

    candidates: dict[tuple[str, str], dict[str, Any]] = {}
    samples: list[dict[str, Any]] = []
    before_index_paths = sorted(before_root.glob("*/before-index.json"), key=lambda path: path.parent.name)
    if before_index_paths == []:
        raise RuntimeError(f"before indexes not found under: {before_root}")

    for before_index_path in before_index_paths:
        sample_name = before_index_path.parent.name
        after_index_path = after_root / sample_name / "after-index.json"
        if not after_index_path.is_file():
            samples.append(
                {
                    "sample": sample_name,
                    "ok": False,
                    "error": "after index not found: " + after_index_path.as_posix(),
                    "candidate_count": 0,
                }
            )
            continue

        sample_result = derive_keyword_map(load_json(before_index_path), load_json(after_index_path))
        for candidate in sample_result["candidates"]:
            append_derived_candidate(candidates, candidate, sample_name)
        samples.append(
            {
                "sample": sample_name,
                "ok": True,
                "candidate_count": sample_result["candidate_count"],
                "candidates": sample_result["candidates"],
            }
        )

    keyword_candidates = sorted(
        candidates.values(),
        key=lambda item: (
            -int(item["sample_count"]),
            -int(item["source_count"]),
            -len(str(item["old"])),
            str(item["old"]),
            str(item["new"]),
        ),
    )
    return {
        "schema": "generated-name-migration-derived-sample-keyword-map-v1",
        "before_root": before_root.as_posix(),
        "after_root": after_root.as_posix(),
        "sample_count": len(samples),
        "candidate_count": len(keyword_candidates),
        "candidates": keyword_candidates,
        "keywords": [
            {
                "old": candidate["old"],
                "new": candidate["new"],
            }
            for candidate in keyword_candidates
        ],
        "samples": samples,
    }


def compare_indexes(before: dict[str, Any], after: dict[str, Any], keyword_map: list[dict[str, str]]) -> dict[str, Any]:
    before_files = {item["path"]: item for item in before.get("files", [])}
    after_files = {item["path"]: item for item in after.get("files", [])}
    after_paths = set(after_files)
    matched_after_paths: set[str] = set()
    file_renames: list[dict[str, Any]] = []
    unchanged_files: list[str] = []
    removed_files: list[str] = []
    conflicts: list[dict[str, Any]] = []
    predicted_targets: dict[str, list[str]] = {}

    for old_path, old_file in before_files.items():
        predicted = apply_keyword_map(old_path, keyword_map)
        predicted_targets.setdefault(predicted, []).append(old_path)
        if old_path in after_files:
            unchanged_files.append(old_path)
            matched_after_paths.add(old_path)
            continue
        if predicted in after_files:
            new_file = after_files[predicted]
            file_renames.append(
                {
                    "old": old_path,
                    "new": predicted,
                    "content_equal": old_file.get("sha256") == new_file.get("sha256"),
                }
            )
            matched_after_paths.add(predicted)
        else:
            removed_files.append(old_path)

    for target, sources in sorted(predicted_targets.items()):
        if len(sources) > 1 and target in after_paths:
            conflicts.append({"kind": "file_many_to_one", "new": target, "old": sorted(sources)})

    added_files = sorted(after_paths - matched_after_paths)

    before_symbols = before.get("symbols", [])
    after_symbols = after.get("symbols", [])
    after_symbol_keys = {symbol_key(item): item for item in after_symbols}
    symbol_renames: list[dict[str, Any]] = []
    unchanged_symbols = 0
    removed_symbols: list[dict[str, Any]] = []
    matched_after_symbols: set[str] = set()

    for old_symbol in before_symbols:
        old_key = symbol_key(old_symbol)
        if old_key in after_symbol_keys:
            unchanged_symbols += 1
            matched_after_symbols.add(old_key)
            continue
        predicted_symbol = {
            **old_symbol,
            "name": apply_keyword_map(str(old_symbol.get("name", "")), keyword_map),
            "scope": apply_keyword_map(str(old_symbol.get("scope", "")), keyword_map),
            "path": apply_keyword_map(str(old_symbol.get("path", "")), keyword_map),
        }
        predicted_key = symbol_key(predicted_symbol)
        if predicted_key in after_symbol_keys:
            symbol_renames.append(
                {
                    "kind": old_symbol.get("kind", ""),
                    "scope": old_symbol.get("scope", ""),
                    "old": old_symbol.get("name", ""),
                    "new_scope": predicted_symbol.get("scope", ""),
                    "new": predicted_symbol.get("name", ""),
                    "old_path": old_symbol.get("path", ""),
                    "new_path": predicted_symbol.get("path", ""),
                }
            )
            matched_after_symbols.add(predicted_key)
        else:
            removed_symbols.append(old_symbol)

    added_symbols = [item for item in after_symbols if symbol_key(item) not in matched_after_symbols]

    return {
        "schema": "generated-name-migration-compare-v1",
        "ok": len(conflicts) == 0,
        "keyword_count": len(keyword_map),
        "files": {
            "unchanged_count": len(unchanged_files),
            "renames": file_renames,
            "added": added_files,
            "removed": removed_files,
        },
        "symbols": {
            "unchanged_count": unchanged_symbols,
            "renames": symbol_renames,
            "added": added_symbols,
            "removed": removed_symbols,
        },
        "conflicts": conflicts,
    }


def compare_samples(before_root: Path, after_root: Path, keyword_map: list[dict[str, str]]) -> dict[str, Any]:
    if not before_root.is_dir():
        raise RuntimeError(f"before root directory not found: {before_root}")
    if not after_root.is_dir():
        raise RuntimeError(f"after root directory not found: {after_root}")

    samples: list[dict[str, Any]] = []
    ok = True
    totals = {
        "file_rename_count": 0,
        "file_added_count": 0,
        "file_removed_count": 0,
        "symbol_rename_count": 0,
        "symbol_added_count": 0,
        "symbol_removed_count": 0,
        "conflict_count": 0,
    }

    before_index_paths = sorted(before_root.glob("*/before-index.json"), key=lambda path: path.parent.name)
    if before_index_paths == []:
        raise RuntimeError(f"before indexes not found under: {before_root}")

    for before_index_path in before_index_paths:
        sample_name = before_index_path.parent.name
        after_index_path = after_root / sample_name / "after-index.json"
        if not after_index_path.is_file():
            ok = False
            samples.append(
                {
                    "sample": sample_name,
                    "ok": False,
                    "error": "after index not found: " + after_index_path.as_posix(),
                }
            )
            totals["conflict_count"] += 1
            continue

        sample_compare = compare_indexes(load_json(before_index_path), load_json(after_index_path), keyword_map)
        sample_ok = bool(sample_compare["ok"])
        ok = ok and sample_ok

        file_section = sample_compare["files"]
        symbol_section = sample_compare["symbols"]
        conflict_count = len(sample_compare["conflicts"])
        totals["file_rename_count"] += len(file_section["renames"])
        totals["file_added_count"] += len(file_section["added"])
        totals["file_removed_count"] += len(file_section["removed"])
        totals["symbol_rename_count"] += len(symbol_section["renames"])
        totals["symbol_added_count"] += len(symbol_section["added"])
        totals["symbol_removed_count"] += len(symbol_section["removed"])
        totals["conflict_count"] += conflict_count

        samples.append(
            {
                "sample": sample_name,
                "ok": sample_ok,
                "file_rename_count": len(file_section["renames"]),
                "file_added_count": len(file_section["added"]),
                "file_removed_count": len(file_section["removed"]),
                "symbol_rename_count": len(symbol_section["renames"]),
                "symbol_added_count": len(symbol_section["added"]),
                "symbol_removed_count": len(symbol_section["removed"]),
                "conflict_count": conflict_count,
                "compare": sample_compare,
            }
        )

    return {
        "schema": "generated-name-migration-sample-compare-v1",
        "ok": ok,
        "before_root": before_root.as_posix(),
        "after_root": after_root.as_posix(),
        "keyword_count": len(keyword_map),
        "sample_count": len(samples),
        "totals": totals,
        "samples": samples,
    }


def symbol_key(symbol: dict[str, Any]) -> str:
    return "|".join(
        [
            str(symbol.get("kind", "")),
            str(symbol.get("scope", "")),
            str(symbol.get("name", "")),
            str(symbol.get("path", "")),
        ]
    )


def scan_keywords(root: Path, keyword_map: list[dict[str, str]]) -> dict[str, Any]:
    if not root.is_dir():
        raise RuntimeError(f"root directory not found: {root}")
    occurrences: list[dict[str, Any]] = []
    by_keyword: dict[str, dict[str, Any]] = {}
    by_file: dict[str, dict[str, Any]] = {}
    for path in iter_files(root):
        text = read_text(path)
        if text is None:
            continue
        relative_path = path.relative_to(root).as_posix()
        for line_number, line in enumerate(text.splitlines(), start=1):
            for pair in keyword_map:
                for column in keyword_pair_match_columns(line, pair):
                    occurrences.append(
                        {
                            "file": relative_path,
                            "line": line_number,
                            "column": column + 1,
                            "keyword": pair["old"],
                            "replacement": pair["new"],
                            "mode": pair.get("mode", "literal"),
                            "context": line.strip(),
                        }
                    )
                    keyword_summary = by_keyword.setdefault(
                        pair["old"],
                        {
                            "keyword": pair["old"],
                            "replacement": pair["new"],
                            "occurrence_count": 0,
                            "file_count": 0,
                            "files": {},
                        },
                    )
                    keyword_summary["occurrence_count"] += 1
                    keyword_summary["files"][relative_path] = keyword_summary["files"].get(relative_path, 0) + 1

                    file_summary = by_file.setdefault(
                        relative_path,
                        {
                            "file": relative_path,
                            "occurrence_count": 0,
                            "keyword_count": 0,
                            "keywords": {},
                        },
                    )
                    file_summary["occurrence_count"] += 1
                    file_summary["keywords"][pair["old"]] = file_summary["keywords"].get(pair["old"], 0) + 1

    keyword_summaries = []
    for item in by_keyword.values():
        item["file_count"] = len(item["files"])
        item["files"] = dict(sorted(item["files"].items(), key=lambda entry: (-entry[1], entry[0])))
        keyword_summaries.append(item)

    file_summaries = []
    for item in by_file.values():
        item["keyword_count"] = len(item["keywords"])
        item["keywords"] = dict(sorted(item["keywords"].items(), key=lambda entry: (-entry[1], entry[0])))
        file_summaries.append(item)

    return {
        "schema": "generated-name-migration-keyword-scan-v1",
        "root": root.as_posix(),
        "keyword_count": len(keyword_map),
        "occurrence_count": len(occurrences),
        "summary": {
            "by_keyword": sorted(
                keyword_summaries,
                key=lambda item: (-int(item["occurrence_count"]), str(item["keyword"])),
            ),
            "by_file": sorted(
                file_summaries,
                key=lambda item: (-int(item["occurrence_count"]), str(item["file"])),
            ),
        },
        "occurrences": occurrences,
    }


def validate_keyword_map(root: Path, keyword_map: list[dict[str, str]]) -> dict[str, Any]:
    if not root.is_dir():
        raise RuntimeError(f"root directory not found: {root}")
    errors: list[dict[str, Any]] = []
    warnings: list[dict[str, Any]] = []

    old_values: dict[str, list[str]] = {}
    for pair in keyword_map:
        old_values.setdefault(pair["old"], []).append(pair["new"])
    for old, new_values in sorted(old_values.items()):
        unique_new_values = sorted(set(new_values))
        if len(unique_new_values) > 1:
            errors.append(
                {
                    "kind": "duplicate_old_conflict",
                    "old": old,
                    "new_values": unique_new_values,
                }
            )
        elif len(new_values) > 1:
            warnings.append(
                {
                    "kind": "duplicate_old_same_target",
                    "old": old,
                    "new": unique_new_values[0],
                    "count": len(new_values),
                }
            )

    for index, pair in enumerate(keyword_map):
        for other_index, other_pair in enumerate(keyword_map):
            if index == other_index:
                continue
            if apply_keyword_pair(pair["new"], other_pair) != pair["new"]:
                errors.append(
                    {
                        "kind": "chained_replacement",
                        "old": pair["old"],
                        "new": pair["new"],
                        "next_old": other_pair["old"],
                        "next_new": other_pair["new"],
                    }
                )
            if other_pair["old"] != "" and other_pair["old"] in pair["old"]:
                warnings.append(
                    {
                        "kind": "overlapping_old_keyword",
                        "old": pair["old"],
                        "overlaps_old": other_pair["old"],
                    }
                )

    predicted_paths: dict[str, list[str]] = {}
    path_change_count = 0
    for path in iter_files(root):
        relative_path = path.relative_to(root).as_posix()
        predicted_path = apply_keyword_map(relative_path, keyword_map)
        if predicted_path != relative_path:
            path_change_count += 1
        predicted_paths.setdefault(predicted_path, []).append(relative_path)

    path_collisions = [
        {
            "new": target,
            "old": sorted(sources),
        }
        for target, sources in sorted(predicted_paths.items())
        if len(sources) > 1
    ]
    for collision in path_collisions:
        errors.append({"kind": "path_collision", **collision})

    occurrence_result = scan_keywords(root, keyword_map)
    return {
        "schema": "generated-name-migration-keyword-map-validation-v1",
        "ok": errors == [],
        "root": root.as_posix(),
        "keyword_count": len(keyword_map),
        "path_change_count": path_change_count,
        "text_occurrence_count": occurrence_result["occurrence_count"],
        "error_count": len(errors),
        "warning_count": len(warnings),
        "errors": errors,
        "warnings": warnings,
        "summary": occurrence_result["summary"],
    }


def validate_keyword_map_samples(
    samples_snapshot_root: Path,
    phase: str,
    keyword_map: list[dict[str, str]],
) -> dict[str, Any]:
    normalized_phase = trim_phase(phase)
    if not samples_snapshot_root.is_dir():
        raise RuntimeError(f"samples snapshot root directory not found: {samples_snapshot_root}")

    source_roots = sorted(
        [path for path in samples_snapshot_root.glob(f"*/{normalized_phase}") if path.is_dir()],
        key=lambda path: path.parent.name,
    )
    if source_roots == []:
        raise RuntimeError(f"sample {normalized_phase} snapshots not found under: {samples_snapshot_root}")

    samples: list[dict[str, Any]] = []
    totals = {
        "path_change_count": 0,
        "text_occurrence_count": 0,
        "error_count": 0,
        "warning_count": 0,
    }
    ok = True
    for source_root in source_roots:
        sample_name = source_root.parent.name
        validation = validate_keyword_map(source_root, keyword_map)
        sample_ok = bool(validation["ok"])
        ok = ok and sample_ok
        totals["path_change_count"] += int(validation["path_change_count"])
        totals["text_occurrence_count"] += int(validation["text_occurrence_count"])
        totals["error_count"] += int(validation["error_count"])
        totals["warning_count"] += int(validation["warning_count"])
        samples.append(
            {
                "sample": sample_name,
                "ok": sample_ok,
                "path_change_count": validation["path_change_count"],
                "text_occurrence_count": validation["text_occurrence_count"],
                "error_count": validation["error_count"],
                "warning_count": validation["warning_count"],
                "errors": validation["errors"],
                "warnings": validation["warnings"],
                "summary": validation["summary"],
            }
        )

    return {
        "schema": "generated-name-migration-sample-keyword-map-validation-v1",
        "ok": ok,
        "samples_snapshot_root": samples_snapshot_root.as_posix(),
        "phase": normalized_phase,
        "keyword_count": len(keyword_map),
        "sample_count": len(samples),
        "totals": totals,
        "samples": samples,
    }


def main() -> int:
    parser = argparse.ArgumentParser(description="Audit generated-name migration snapshots.")
    subparsers = parser.add_subparsers(dest="command", required=True)

    capture_parser = subparsers.add_parser("capture", help="copy a generated artifact snapshot")
    capture_parser.add_argument("--root", required=True)
    capture_parser.add_argument("--output-root", required=True)
    capture_parser.add_argument("--manifest-output")
    capture_parser.add_argument("--pretty", action="store_true")

    capture_samples_parser = subparsers.add_parser("capture-samples", help="copy all sample reference snapshots")
    capture_samples_parser.add_argument("--samples-root", default="sample/tutorials")
    capture_samples_parser.add_argument("--output-root", required=True)
    capture_samples_parser.add_argument("--phase", default="before")
    capture_samples_parser.add_argument("--manifest-output", required=True)
    capture_samples_parser.add_argument("--index", action="store_true")
    capture_samples_parser.add_argument("--pretty", action="store_true")

    transform_parser = subparsers.add_parser("transform", help="copy a snapshot while applying a keyword map")
    transform_parser.add_argument("--root", required=True)
    transform_parser.add_argument("--output-root", required=True)
    transform_parser.add_argument("--keyword-map", required=True)
    transform_parser.add_argument("--manifest-output")
    transform_parser.add_argument("--pretty", action="store_true")

    transform_samples_parser = subparsers.add_parser(
        "transform-samples",
        help="copy sample snapshots while applying a keyword map",
    )
    transform_samples_parser.add_argument("--samples-snapshot-root", required=True)
    transform_samples_parser.add_argument("--output-root", required=True)
    transform_samples_parser.add_argument("--source-phase", default="before")
    transform_samples_parser.add_argument("--output-phase", default="after")
    transform_samples_parser.add_argument("--keyword-map", required=True)
    transform_samples_parser.add_argument("--manifest-output", required=True)
    transform_samples_parser.add_argument("--index", action="store_true")
    transform_samples_parser.add_argument("--pretty", action="store_true")

    index_parser = subparsers.add_parser("index", help="index a generated artifact snapshot")
    index_parser.add_argument("--root", required=True)
    index_parser.add_argument("--output", required=True)
    index_parser.add_argument("--pretty", action="store_true")

    compare_parser = subparsers.add_parser("compare", help="compare before/after indexes")
    compare_parser.add_argument("--before", required=True)
    compare_parser.add_argument("--after", required=True)
    compare_parser.add_argument("--keyword-map")
    compare_parser.add_argument("--output", required=True)
    compare_parser.add_argument("--pretty", action="store_true")

    compare_samples_parser = subparsers.add_parser("compare-samples", help="compare all sample before/after indexes")
    compare_samples_parser.add_argument("--before-root", required=True)
    compare_samples_parser.add_argument("--after-root", required=True)
    compare_samples_parser.add_argument("--keyword-map")
    compare_samples_parser.add_argument("--output", required=True)
    compare_samples_parser.add_argument("--pretty", action="store_true")

    derive_parser = subparsers.add_parser("derive-keyword-map", help="derive keyword map candidates from before/after indexes")
    derive_parser.add_argument("--before", required=True)
    derive_parser.add_argument("--after", required=True)
    derive_parser.add_argument("--output", required=True)
    derive_parser.add_argument("--pretty", action="store_true")

    derive_samples_parser = subparsers.add_parser(
        "derive-keyword-map-samples",
        help="derive keyword map candidates from all sample before/after indexes",
    )
    derive_samples_parser.add_argument("--before-root", required=True)
    derive_samples_parser.add_argument("--after-root", required=True)
    derive_samples_parser.add_argument("--output", required=True)
    derive_samples_parser.add_argument("--pretty", action="store_true")

    scan_parser = subparsers.add_parser("scan-keywords", help="list old keyword occurrences")
    scan_parser.add_argument("--root", required=True)
    scan_parser.add_argument("--keyword-map", required=True)
    scan_parser.add_argument("--output", required=True)
    scan_parser.add_argument("--pretty", action="store_true")

    validate_parser = subparsers.add_parser("validate-keyword-map", help="validate keyword map safety against a root")
    validate_parser.add_argument("--root", required=True)
    validate_parser.add_argument("--keyword-map", required=True)
    validate_parser.add_argument("--output", required=True)
    validate_parser.add_argument("--pretty", action="store_true")

    validate_samples_parser = subparsers.add_parser(
        "validate-keyword-map-samples",
        help="validate keyword map safety against sample snapshots",
    )
    validate_samples_parser.add_argument("--samples-snapshot-root", required=True)
    validate_samples_parser.add_argument("--phase", default="before")
    validate_samples_parser.add_argument("--keyword-map", required=True)
    validate_samples_parser.add_argument("--output", required=True)
    validate_samples_parser.add_argument("--pretty", action="store_true")

    args = parser.parse_args()

    if args.command == "capture":
        result = capture_root(Path(args.root), Path(args.output_root))
        if args.manifest_output:
            write_json(Path(args.manifest_output), result, args.pretty)
        else:
            print(json.dumps(result, ensure_ascii=False, indent=2 if args.pretty else None, sort_keys=args.pretty))
        return 0

    if args.command == "capture-samples":
        result = capture_samples(Path(args.samples_root), Path(args.output_root), args.phase, args.index)
        write_json(Path(args.manifest_output), result, args.pretty)
        return 0

    if args.command == "transform":
        result = transform_root(
            Path(args.root),
            Path(args.output_root),
            load_keyword_map(Path(args.keyword_map)),
        )
        if args.manifest_output:
            write_json(Path(args.manifest_output), result, args.pretty)
        else:
            print(json.dumps(result, ensure_ascii=False, indent=2 if args.pretty else None, sort_keys=args.pretty))
        return 0

    if args.command == "transform-samples":
        result = transform_samples(
            Path(args.samples_snapshot_root),
            Path(args.output_root),
            args.source_phase,
            args.output_phase,
            load_keyword_map(Path(args.keyword_map)),
            args.index,
        )
        write_json(Path(args.manifest_output), result, args.pretty)
        return 0

    if args.command == "index":
        write_json(Path(args.output), index_root(Path(args.root)), args.pretty)
        return 0

    if args.command == "compare":
        result = compare_indexes(
            load_json(Path(args.before)),
            load_json(Path(args.after)),
            load_keyword_map(Path(args.keyword_map) if args.keyword_map else None),
        )
        write_json(Path(args.output), result, args.pretty)
        print("generated name migration compare OK" if result["ok"] else "generated name migration compare has conflicts")
        return 0 if result["ok"] else 1

    if args.command == "compare-samples":
        result = compare_samples(
            Path(args.before_root),
            Path(args.after_root),
            load_keyword_map(Path(args.keyword_map) if args.keyword_map else None),
        )
        write_json(Path(args.output), result, args.pretty)
        print("generated name migration sample compare OK" if result["ok"] else "generated name migration sample compare has conflicts")
        return 0 if result["ok"] else 1

    if args.command == "derive-keyword-map":
        result = derive_keyword_map(load_json(Path(args.before)), load_json(Path(args.after)))
        write_json(Path(args.output), result, args.pretty)
        print(f"generated name migration keyword candidates: {result['candidate_count']}")
        return 0

    if args.command == "derive-keyword-map-samples":
        result = derive_keyword_map_samples(Path(args.before_root), Path(args.after_root))
        write_json(Path(args.output), result, args.pretty)
        print(f"generated name migration sample keyword candidates: {result['candidate_count']}")
        return 0

    if args.command == "scan-keywords":
        write_json(
            Path(args.output),
            scan_keywords(Path(args.root), load_keyword_map(Path(args.keyword_map))),
            args.pretty,
        )
        return 0

    if args.command == "validate-keyword-map":
        result = validate_keyword_map(Path(args.root), load_keyword_map(Path(args.keyword_map)))
        write_json(Path(args.output), result, args.pretty)
        print("generated name migration keyword map validation OK" if result["ok"] else "generated name migration keyword map validation has errors")
        return 0 if result["ok"] else 1

    if args.command == "validate-keyword-map-samples":
        result = validate_keyword_map_samples(
            Path(args.samples_snapshot_root),
            args.phase,
            load_keyword_map(Path(args.keyword_map)),
        )
        write_json(Path(args.output), result, args.pretty)
        print("generated name migration sample keyword map validation OK" if result["ok"] else "generated name migration sample keyword map validation has errors")
        return 0 if result["ok"] else 1

    raise RuntimeError(f"unsupported command: {args.command}")


if __name__ == "__main__":
    raise SystemExit(main())
