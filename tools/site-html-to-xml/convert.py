#!/usr/bin/env python3
"""Convert every HTML file in a site tree to well-formed XML, preserving paths.
Usage: python convert.py SOURCE_HTML_FOLDER OUTPUT_XML_FOLDER
Requires: pip install lxml
"""
from pathlib import Path
import argparse
from lxml import etree, html

def main():
    ap=argparse.ArgumentParser()
    ap.add_argument("source",type=Path)
    ap.add_argument("destination",type=Path)
    args=ap.parse_args()
    src=args.source.resolve()
    dst=args.destination.resolve()
    if not src.is_dir():
        ap.error(f"Source folder not found: {src}")
    if dst == src or src in dst.parents and dst.name == "":
        ap.error("Use a separate destination folder.")
    files=sorted(p for p in src.rglob("*") if p.is_file() and p.suffix.lower() in {".html",".htm"} and dst not in p.parents)
    if not files:
        ap.error("No HTML files found in source folder.")
    done=0
    for p in files:
        rel=p.relative_to(src).with_suffix(".xml")
        target=dst/rel
        target.parent.mkdir(parents=True,exist_ok=True)
        raw=p.read_bytes()
        root=html.document_fromstring(raw)
        xml=etree.tostring(root,encoding="UTF-8",xml_declaration=True,method="xml",pretty_print=True)
        # Verify XML syntax before writing
        etree.fromstring(xml)
        target.write_bytes(xml)
        done+=1
        print(f"OK {p.relative_to(src)} -> {rel}")
    print(f"Converted {done} of {len(files)} HTML files.")

if __name__=="__main__":
    main()
