"""
GN SCALES — AUDIT REGRESSION CHECK

Asserts, against the generated site rather than against the source, that every
finding in gnscales-site-audit.md is actually fixed and stays fixed.

    python tools/check-audit.py

Exits non-zero on the first regression, so it can go in a deploy hook.
"""
import re
import os
import sys
import glob

ROOT = os.path.dirname(os.path.dirname(os.path.abspath(__file__)))
os.chdir(ROOT)

PAGES = ["index.html", "services.html", "pricing.html", "work.html", "about.html",
         "contact.html", "thanks.html", "privacy.html", "terms.html", "404.html"]

docs = {p: open(p, encoding="utf-8").read() for p in PAGES}
allsrc = "\n".join(docs.values())
css = "\n".join(open(f, encoding="utf-8").read() for f in sorted(glob.glob("*.css")))
js = open("main.js", encoding="utf-8").read()
htaccess = open(".htaccess", encoding="utf-8").read()
cpanel = open(".cpanel.yml", encoding="utf-8").read()
robots = open("robots.txt", encoding="utf-8").read()
defaults = open("admin/lib/default-content.php", encoding="utf-8").read()

fails = []


def c(label, ok, detail=""):
    print("  %s  %s" % ("PASS" if ok else "FAIL", label))
    if not ok:
        fails.append(label + ((" — " + detail) if detail else ""))


def every(fn):
    return all(fn(d) for d in docs.values())


print("P0 — actively broken")
c("1.1 no dead Formspree placeholder", "YOUR_FORM_ID" not in allsrc)
c("1.1 form posts to a real handler",
  'action="submit.php"' in docs["contact.html"] and os.path.isfile("submit.php"))
c("1.1 thank-you page exists and is the form target",
  os.path.isfile("thanks.html") and 'name="_next" value="thanks.html"' in docs["contact.html"])
c("1.1 honeypot present", 'name="website"' in docs["contact.html"])
c("1.1 submit button no longer promises a booking it cannot make",
  "Send and book a time" not in docs["contact.html"])
c("1.2 no placeholder email anywhere", "yourdomain.com" not in allsrc)
c("1.2 a real address is published", allsrc.count("hello@gnscales.com") >= 10)
c("1.3 no internal notes in shipped source",
  not re.search(r"(?i)<!--(?:(?!-->).)*?(todo|outstanding items|portrait pending)", allsrc, re.S))
c("1.4 .js guard on every page", every(lambda d: "documentElement.className += ' js'" in d))
c("1.4 reveal states gated behind .js",
  ".js .reveal {" in css and not re.search(r"(?m)^\.reveal \{", css))
c("1.4 init calls individually guarded",
  "].forEach(safe);" in js and "function safe(fn)" in js)
c("5.6 no pre-checked radios", not re.search(r'type="radio"[^>]*checked', docs["contact.html"]))
c("5.6 both qualifying groups required",
  len(re.findall(r'name="sector"[^>]*required', docs["contact.html"])) >= 4
  and len(re.findall(r'name="spend"[^>]*required', docs["contact.html"])) >= 4)

print("\nP1 — SEO and sharing")
c("2.2 sitemap.xml exists", os.path.isfile("sitemap.xml"))
c("2.3 robots.txt names the sitemap", "Sitemap:" in robots)
c("2.3 robots.txt keeps crawlers out of the admin", "Disallow: /admin/" in robots)
c("2.4 canonical on every indexable page",
  all('rel="canonical"' in docs[p] for p in PAGES if p not in ("thanks.html", "404.html")))
c("2.4 no internal links to index.html", 'href="index.html"' not in allsrc)
c("2.4 the home page is linked as /", allsrc.count('href="/"') >= 20)
c("2.5 Open Graph on every page", every(lambda d: 'property="og:image"' in d))
c("2.5 Twitter card on every page", every(lambda d: 'name="twitter:card"' in d))
c("2.5 share images exist",
  os.path.isfile("assets/og/og-default.jpg") and os.path.isfile("assets/og/og-pricing.jpg"))
c("2.6 favicon set exists", all(os.path.isfile(f) for f in
  ["favicon.ico", "favicon.svg", "apple-touch-icon.png", "icon-192.png", "icon-512.png"]))
c("2.6 icons linked from every page", every(lambda d: 'rel="icon"' in d))
c("2.7 ProfessionalService structured data", '"@type": "ProfessionalService"' in docs["index.html"])
c("2.7 FAQPage on home and pricing",
  '"FAQPage"' in docs["index.html"] and '"FAQPage"' in docs["pricing.html"])
c("2.7 Offer structured data on pricing", '"@type": "Offer"' in docs["pricing.html"])
c("2.8 titles carry searchable terms",
  "Auto Aftermarket" in docs["index.html"] and "Landing Pages" in docs["services.html"]
  and "$1,500" in docs["pricing.html"])
c("2.8 the 40-word h2 is a paragraph again",
  'class="story-lede"' in docs["about.html"] and "<h2 id=\"origin-heading\" class=\"sr-only\"" in docs["about.html"])
c("2.9 author and format-detection everywhere",
  every(lambda d: 'name="author"' in d and 'name="format-detection"' in d))

print("\nP2 — copy that contradicted itself")
c("3.1 no invented social proof", "Most chosen" not in allsrc)
c("3.1 both tiers carry an honest badge",
  "Best place to start" in docs["pricing.html"] and "Most complete" in docs["pricing.html"])
c("3.2 no fabricated scarcity count", "3 of 10" not in allsrc and "3 / 10" not in allsrc)
c("3.2 scarcity derived from one pair of numbers",
  "'spots_total' => 10" in defaults and "'spots_taken' => 0" in defaults)
c("3.3 'Both ad platforms' ambiguity gone", "Both ad platforms" not in allsrc)
c("3.3 platform scope stated explicitly", "Two ad platforms of your choice" in docs["pricing.html"])
c("3.4 calculator has a gross-margin slider", 'id="calc-margin"' in docs["index.html"])
c("3.4 break-even framed in profit, not revenue",
  "profit per job" in docs["index.html"] and "Jobs to cover cost" in docs["index.html"])
c("3.4 verdict bands scale with job value", "benchmark" in js and "job * 0.045" in js)
c("3.5 the unmet sub-second claim is gone", "0.8s" not in allsrc)
c("3.6 nav and footer agree on Studio", ">About<" not in allsrc)
c("3.7 Contact appears in the footer", docs["index.html"].count(">Contact<") >= 2)
c("3.8 founder name resolved", "Gn Lomidze" not in allsrc and "Nick Lomidze" in allsrc)
c("3.8 monograms are distinguishable",
  ">NL<" in docs["about.html"] and ">GL<" in docs["about.html"])

print("\nP3 — unfinished design")
c("4.1 'Portrait pending' never reaches a visitor",
  "Portrait pending" not in allsrc and "— pending" not in allsrc)
c("4.2 'Remaining services' placeholder gone", "Remaining services" not in allsrc)
c("4.2 the 04-06 group has a real heading",
  "TikTok, lifecycle email and the full retainer" in docs["services.html"])
c("4.3 a work page exists and is in the nav",
  os.path.isfile("work.html") and 'href="/work.html"' in docs["index.html"])
c("4.4 breakpoints reduced to the agreed ladder",
  sorted(set(re.findall(r"max-width: (\d+)px", css)), key=int) == ["380", "560", "700", "900", "1080"])
c("4.5 the two orphaned classes are styled", ".faq-list {" in css and ".order-col {" in css)

print("\nP4 — code bugs and fragility")
c("5.1 view-transition interception removed", "startViewTransition" not in js)
c("5.1 native cross-document transitions declared", "@view-transition" in css)
c("5.2 cursor loop pauses on a hidden tab", "if (document.hidden) return;" in js)
c("5.3 pointerover writes only on a state change", "if (next === cursorState) return;" in js)
c("5.4 media queries re-evaluated live", "onMediaChange(" in js)
c("5.5 WebGL context loss handled",
  "webglcontextlost" in js and "webglcontextrestored" in js)
c("5.7 calculator results are announced", 'aria-live="polite"' in docs["index.html"])
c("5.7 sliders expose aria-valuetext", "aria-valuetext" in docs["index.html"])
c("5.8 scripts deferred", every(lambda d: 'src="/main.js' in d and "defer" in d))
c("5.9 drag starts only near the handle", "function isGrab(e)" in js)

print("\nP5 — performance")
c("6.1 no third-party font origins",
  "fonts.googleapis.com" not in allsrc and "fonts.gstatic.com" not in allsrc)
c("6.1 fonts self-hosted and preloaded",
  every(lambda d: 'rel="preload"' in d and "assets/fonts/" in d))
c("6.1 font payload trimmed to four files",
  len(glob.glob("assets/fonts/*.woff2")) == 4)
c("6.2 shader deferred until idle", "requestIdleCallback" in js)
c("6.2 fbm reduced to three octaves", "i < 3; i++" in js)
c("6.2 shader skipped on small screens", "mqWide.matches" in js)
c("6.4 compression and caching configured",
  "mod_deflate" in htaccess and "mod_expires" in htaccess)
c("6.4 cache busting on every hashed asset", every(lambda d: "?v=" in d))

print("\nP6 — accessibility")
c("7.1 the quietest ink tone clears AA", "--ink-4: rgba(237, 230, 211, 0.54)" in css)
c("7.2 the mobile nav traps focus", "inert" in js and "e.key !== 'Tab'" in js)
c("7.5 collapsed FAQ panels are hidden from AT",
  docs["index.html"].count('role="region" hidden') >= 4 and "panel.hidden = true" in js)
c("skip link on every page", every(lambda d: 'class="skip-link"' in d))
c("aria-current marks the right nav item",
  'href="/services.html" aria-current="page"' in docs["services.html"])

print("\nP7 — server, deploy and legal")
c("8.1 the deploy can ship dotfiles", "rsync" in cpanel)
c("8.2 the deploy removes deleted files", "--delete" in cpanel)
c("8.2 the deploy protects live content", "--exclude 'data/'" in cpanel)
c("8.3 .htaccess present with security headers",
  "X-Content-Type-Options" in htaccess and "Referrer-Policy" in htaccess)
c("8.3 source control is not served", "/\\.git" in htaccess)
c("8.4 404 page exists and is wired up",
  os.path.isfile("404.html") and "ErrorDocument 404" in htaccess)
c("8.5 measurement hooks exist, unset until filled in",
  "'ga4_id'" in defaults and "'meta_pixel_id'" in defaults)
c("9.1 privacy policy exists and is linked from every page",
  os.path.isfile("privacy.html") and every(lambda d: 'href="/privacy.html"' in d))
c("9.1 terms exist", os.path.isfile("terms.html"))
c("9.4 thank-you page carries the conversion marker",
  'data-conversion="lead"' in docs["thanks.html"])

print()
if fails:
    print("%d CHECK(S) FAILED:" % len(fails))
    for f in fails:
        print("  - " + f)
    sys.exit(1)
print("ALL CHECKS PASSED")
