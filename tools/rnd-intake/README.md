# PBD R&D batch intake

Put this folder **outside public_html** on the Hostinger server. Place exported source images in `incoming/`. Cron executes `bin/process.php` using PHP CLI 8+. Output JSON records go to `records/` and are keyed by SHA-256 to prevent duplicate processing. Each invocation processes up to 10 new files by default; a lock prevents overlapping runs. Originals are not modified.

Hostinger hPanel → Advanced → Cron Jobs. Example command (replace USER and DOMAIN, and verify PHP binary path):

`/usr/bin/php /home/USER/domains/DOMAIN/private/pbd-rnd/bin/process.php >> /home/USER/domains/DOMAIN/private/pbd-rnd/cron.log 2>&1`

Tesseract must be installed on the server for OCR. If unavailable, records are created with `needs_manual_transcription`. Even when available, handwriting, formulas, and diagrams need visual review. No output is marked scientifically verified automatically. The cron script **does not fetch images directly from ChatGPT Library**; export/copy images to the server first. Do not place source research or records in public web directories.
