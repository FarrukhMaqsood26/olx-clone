import os
import re

# Specifically mapping exact casing to preserve standard
exact_replacements = [
    ("olx_clone", "bazaar"),
    ("OLX Clone", "Bazaar"),
    ("OLX CLONE", "BAZAAR"),
    ("olx-clone", "bazaar"),
    ("OLX", "Bazaar"),
    ("olx", "bazaar")
]

def replace_in_file(filepath):
    try:
        with open(filepath, 'r', encoding='utf-8') as f:
            content = f.read()
    except Exception:
        return

    new_content = content
    for old, new in exact_replacements:
        new_content = new_content.replace(old, new)

    if new_content != content:
        with open(filepath, 'w', encoding='utf-8') as f:
            f.write(new_content)
        print(f"Updated {filepath}")

for root, dirs, files in os.walk('.'):
    # Exclude vendor, .git, node_modules, etc if any exist
    if '.git' in root or 'vendor' in root or 'node_modules' in root or 'rename.py' in root:
        continue
    for file in files:
        if file.endswith('.php') or file.endswith('.sql') or file.endswith('.js') or file.endswith('.md') or file.endswith('.css') or file.endswith('.html') or file.endswith('.example'):
            filepath = os.path.join(root, file)
            replace_in_file(filepath)
