"""
GN SCALES — HTML STRUCTURE CHECK

Parses every generated page and reports the things that actually break a page
or a crawl: unbalanced tags, duplicate ids, links to nowhere, images with no
alt text, ARIA that points at an element that does not exist.

    python tools/check-html.py
"""
import os
import re
import sys
import glob
from html.parser import HTMLParser

ROOT = os.path.dirname(os.path.dirname(os.path.abspath(__file__)))
os.chdir(ROOT)

VOID = {"area", "base", "br", "col", "embed", "hr", "img", "input", "link",
        "meta", "param", "source", "track", "wbr"}

problems = []


class Page(HTMLParser):
    def __init__(self, name):
        super().__init__(convert_charrefs=True)
        self.name = name
        self.stack = []
        self.ids = {}
        self.links = []
        self.aria = []       # (attribute, target id)
        self.images = []
        self.headings = []
        self.labels = []     # for= targets
        self.forms = 0
        self.titles = 0
        self.h1 = 0

    def handle_starttag(self, tag, attrs):
        a = dict(attrs)
        if tag not in VOID:
            self.stack.append((tag, self.getpos()[0]))

        if "id" in a:
            if a["id"] in self.ids:
                problems.append(f"{self.name}: duplicate id \"{a['id']}\" "
                                f"(lines {self.ids[a['id']]} and {self.getpos()[0]})")
            else:
                self.ids[a["id"]] = self.getpos()[0]

        if tag == "a" and "href" in a:
            self.links.append((a["href"], self.getpos()[0]))
        if tag == "img":
            self.images.append((a.get("src", ""), a.get("alt"), self.getpos()[0]))
        if tag == "form":
            self.forms += 1
        if tag == "title":
            self.titles += 1
        if tag in ("h1", "h2", "h3", "h4", "h5", "h6"):
            self.headings.append((int(tag[1]), self.getpos()[0]))
            if tag == "h1":
                self.h1 += 1
        for attr in ("aria-controls", "aria-labelledby", "aria-describedby"):
            if attr in a:
                for target in a[attr].split():
                    self.aria.append((attr, target, self.getpos()[0]))
        if tag == "label" and "for" in a:
            self.labels.append((a["for"], self.getpos()[0]))

    def handle_endtag(self, tag):
        if tag in VOID:
            return
        if not self.stack:
            problems.append(f"{self.name}: stray </{tag}> at line {self.getpos()[0]}")
            return
        if self.stack[-1][0] == tag:
            self.stack.pop()
        else:
            open_tag, line = self.stack[-1]
            problems.append(f"{self.name}: </{tag}> at line {self.getpos()[0]} closes "
                            f"while <{open_tag}> from line {line} is still open")
            # Recover so one mistake does not cascade.
            for i in range(len(self.stack) - 1, -1, -1):
                if self.stack[i][0] == tag:
                    del self.stack[i:]
                    break


pages = sorted(glob.glob("*.html"))
print(f"Checking {len(pages)} pages\n")

for name in pages:
    src = open(name, encoding="utf-8").read()
    p = Page(name)
    p.feed(src)
    p.close()

    for tag, line in p.stack:
        problems.append(f"{name}: <{tag}> opened at line {line} is never closed")

    if p.titles != 1:
        problems.append(f"{name}: expected exactly one <title>, found {p.titles}")
    if p.h1 != 1:
        problems.append(f"{name}: expected exactly one <h1>, found {p.h1}")

    # Headings should not skip a level on the way down.
    last = 0
    for level, line in p.headings:
        if last and level > last + 1:
            problems.append(f"{name}: heading jumps from h{last} to h{level} at line {line}")
        last = level

    for src_attr, alt, line in p.images:
        if alt is None:
            problems.append(f"{name}: <img> with no alt attribute at line {line}")

    for attr, target, line in p.aria:
        if target not in p.ids:
            problems.append(f"{name}: {attr}=\"{target}\" at line {line} points at no element")

    for target, line in p.labels:
        if target not in p.ids:
            problems.append(f"{name}: <label for=\"{target}\"> at line {line} points at no field")

    for href, line in p.links:
        if href.startswith(("http://", "https://", "mailto:", "tel:")):
            continue
        if href.startswith("#"):
            if href != "#" and href[1:] not in p.ids:
                problems.append(f"{name}: link to {href} at line {line} has no target on this page")
            continue
        path = href.split("#")[0].split("?")[0].lstrip("/")
        if path == "":
            path = "index.html"
        if not os.path.exists(path):
            problems.append(f"{name}: link to \"{href}\" at line {line} has no file behind it")
        anchor = href.split("#")[1] if "#" in href else None
        if anchor:
            target_src = open(path, encoding="utf-8").read() if os.path.exists(path) else ""
            if f'id="{anchor}"' not in target_src:
                problems.append(f"{name}: link to \"{href}\" at line {line} — no #{anchor} on that page")

    print(f"  {name:<16} {len(p.links):>3} links  {len(p.ids):>3} ids  "
          f"{len(p.headings):>2} headings  {len(p.images)} images")

# Assets referenced from the HTML must exist.
print()
for name in pages:
    src = open(name, encoding="utf-8").read()
    for ref in re.findall(r'(?:href|src)="(/[^"#?]+)(?:\?[^"]*)?"', src):
        if ref.startswith("//"):
            continue
        path = ref.lstrip("/")
        if not os.path.exists(path):
            problems.append(f"{name}: references missing asset {ref}")

if problems:
    print(f"{len(problems)} PROBLEM(S):")
    for line in sorted(set(problems)):
        print("  - " + line)
    sys.exit(1)
print("No structural problems found.")
