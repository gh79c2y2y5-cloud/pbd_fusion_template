# Project Blue Dragon: HTML to XML

Converts the existing website HTML pages into **actual XML documents**, with an XML declaration and well-formed XML tags, while preserving relative directory structure. Source HTML files are not changed.

Requires Python 3 and `lxml` (`python3 -m pip install lxml`).

```bash
python3 tools/site-html-to-xml/convert.py /path/to/pbd-html-pages /path/to/pbd-xml-pages
```

For 81 source pages the expected output is 81 XML files. The script prints the actual number converted; it does not fabricate missing pages. This produces generic XML representations of HTML DOM trees, **not** a Joomla-specific import schema. If Joomla XML import is intended, that schema must be applied separately.
