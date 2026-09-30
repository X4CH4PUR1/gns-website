"""GN SCALES — SOCIAL SHARE CARDS

Draws the 1200x630 images that Slack, WhatsApp, LinkedIn, iMessage and email
previews show when somebody pastes a link:

    pip install pillow
    python tools/og/make-og.py

A port of the PHP/GD renderer the old admin used, checked against the cards it
made by pixel diff. Change CARDS below, run it, then bump the ?v= on og:image
in every page so the social networks fetch the new picture.

GD sizes are points at 96 dpi, so every size here is pt * 96 / 72 in pixels.
"""
import math
import os
from PIL import Image, ImageDraw, ImageFont

W, H = 1200, 630
PX = 96 / 72
GOLD = (210, 173, 92)
GOLD_HI = (247, 231, 190)
INK = (237, 230, 211)
INK3 = (150, 147, 137)


def ground():
    sw, sh = 150, 79
    small = Image.new("RGB", (sw, sh))
    p = small.load()
    for y in range(sh):
        for x in range(sw):
            px = (x / sw - 0.5) * (W / H)
            py = y / sh - 0.5
            g1 = math.exp(-2.6 * math.hypot(px + 0.52, py + 0.20))
            g2 = math.exp(-3.0 * math.hypot(px - 0.60, py - 0.22))
            g3 = math.exp(-3.4 * math.hypot(px - 0.05, py + 0.62))
            warm = g1 * 0.78 + g3 * 0.34
            cool = g2 * 0.46
            r = 0 + 210 * warm + 103 * cool
            g = 8 + 173 * warm + 133 * cool
            b = 26 + 92 * warm + 196 * cool
            v = 1.0 - 0.42 * math.hypot(px * 0.8, py) ** 1.8
            scrim = 1.0 - 0.46 * max(0.0, 1.0 - max(0.0, (x / sw - 0.10)) / 0.66)
            p[x, y] = tuple(int(max(0, min(255, c * v * scrim))) for c in (r, g, b))
    return small.resize((W, H), Image.BILINEAR)


MARK = [
    [(37.4, 1), (42.6, 1), (42.6, 4.6), (48.6, 4.6), (48.6, 8.8), (42.6, 8.8), (42.6, 13.2), (37.4, 13.2), (37.4, 8.8), (31.4, 8.8), (31.4, 4.6), (37.4, 4.6)],
    [(23, 17), (28, 19), (31, 23.6), (33, 17.5), (36.5, 14.1), (40, 14.1), (43.5, 14.1), (47, 17.5), (49, 23.6), (52, 19), (57, 17), (55.5, 22), (53, 25.6), (52, 30), (28, 30), (27, 25.6), (24.5, 22)],
    [(28, 32), (52, 32), (52, 35.6), (28, 35.6)],
    [(29.6, 37.4), (50.4, 37.4), (50.4, 40.4), (29.6, 40.4)],
    [(31.8, 42.4), (48.2, 42.4), (48.2, 55), (50.6, 63.6), (55, 69), (25, 69), (29.4, 63.6), (31.8, 55)],
    [(26.4, 71), (53.6, 71), (57.2, 82.4), (22.8, 82.4)],
    [(20.6, 85), (59.4, 85), (59.4, 91.4), (20.6, 91.4)],
]


def mark(d, x, y, size):
    s = size / 93
    ox = x - 19 * s
    for poly in MARK:
        d.polygon([(round(ox + a * s), round(y + b * s)) for a, b in poly], fill=GOLD)


def tracked(d, text, pt, x, y, fill, font_path, tracking):
    f = ImageFont.truetype(font_path, pt * PX)
    for ch in text:
        d.text((round(x), y), ch, font=f, fill=fill, anchor="ls")
        box = d.textbbox((0, 0), ch, font=f, anchor="ls")
        x += abs(box[2] - box[0]) + tracking
        if ch == " ":
            x += pt * 0.3


def wrap(d, text, font, max_w):
    lines, line = [], ""
    for word in text.split():
        t = word if not line else line + " " + word
        box = d.textbbox((0, 0), t, font=font)
        if box[2] - box[0] > max_w and line:
            lines.append(line)
            line = word
        else:
            line = t
    if line:
        lines.append(line)
    return lines


def render(path, title, kicker, foot, serif, sans):
    im = ground()
    d = ImageDraw.Draw(im, "RGBA")
    d.rectangle([44, 44, W - 45, H - 45], outline=GOLD + (round(255 * (1 - 96 / 127)),), width=1)
    mark(d, 96, 86, 92)
    d.text((176, 136), "GN", font=ImageFont.truetype(serif, 38 * PX), fill=GOLD, anchor="ls")
    # GD measured each glyph a little narrower than FreeType does here; these
    # spacings reproduce the original cards (checked by pixel diff).
    tracked(d, "SCALES", 13, 179, 164, GOLD, sans, 6.5)
    tf = ImageFont.truetype(serif, 64 * PX)
    # An explicit "\n" sets the break, as the site's <h1> does; otherwise wrap.
    lines = (title.split("\n") if "\n" in title else wrap(d, title, tf, W - 300))[:3]
    y = 400 - (len(lines) - 1) * 40
    # The kicker sits 100px above the first baseline. The PHP original pinned it
    # at y=300, which a two-line headline ran straight into.
    if kicker:
        tracked(d, kicker.upper(), 14, 96, y - 100, INK3, sans, 1.1)
    for i, line in enumerate(lines):
        d.text((96, y), line, font=tf, fill=INK if i == 0 else GOLD_HI, anchor="ls")
        y += 80
    d.rectangle([96, H - 122, 156, H - 121], fill=GOLD)
    if foot:
        d.text((96, H - 78), foot, font=ImageFont.truetype(sans, 15 * PX), fill=INK3, anchor="ls")
    im.save(path, "JPEG", quality=86, optimize=True)


HERE = os.path.dirname(os.path.abspath(__file__))
SERIF = os.path.join(HERE, "InstrumentSerif-Regular.ttf")
SANS = os.path.join(HERE, "SchibstedGrotesk-Variable.ttf")
OUT = os.path.join(HERE, "..", "..", "assets", "og")

# A line break in a title sets where it wraps; later lines are set in pale gold.
CARDS = {
    "og-default.jpg": ("Paid ads that bring\nreal customers.", "Performance marketing agency",
                       "Meta, Google & TikTok ads · creative · landing pages · gnscales.com"),
    "og-pricing.jpg": ("Two plans. No hidden fees.", "Pricing",
                       "$1,500-$2,500/month · month to month, no markup on ad spend"),
}

if __name__ == "__main__":
    for name, (title, kicker, foot) in CARDS.items():
        render(os.path.join(OUT, name), title, kicker, foot, SERIF, SANS)
        print("wrote assets/og/" + name)
