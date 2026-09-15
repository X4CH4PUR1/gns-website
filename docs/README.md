# GN Scales — how this site works

A static website. Every page is a plain `.html` file, hand-edited and committed;
GitHub Pages serves it. There is no PHP, no database and no build step — what is
in the repository is exactly what visitors get.

It used to be different. A PHP admin on cPanel generated these pages from a
content store, and the site was deployed by an `rsync` hook. That whole layer is
gone: no `admin/`, no `submit.php`, no `.htaccess`, no `.cpanel.yml`. If you are
reading an older copy of this file that describes them, this one supersedes it.

---

## 1. Where everything lives

```
/                        the public site
  index.html services.html pricing.html work.html about.html
  contact.html thanks.html privacy.html terms.html 404.html
  sitemap.xml robots.txt site.webmanifest
  favicon.* icon-*.png apple-touch-icon.png
  .nojekyll                stops GitHub Pages running the pages through Jekyll

  style.css                the design system: tokens, header, footer, buttons
  index.css services.css pricing.css work.css about.css contact.css legal.css
                           one stylesheet per page, loaded on top of style.css
  main.js                  everything interactive, one file

  assets/fonts/            self-hosted webfonts
  assets/og/               social share cards

/tools/                  checks; not part of the site
/docs/                   this file; not part of the site
```

---

## 2. Editing

Open the `.html` file and edit it. That is the whole workflow.

Two things are worth knowing before you do:

**Gold text.** `<span class="metal">…</span>` gives the metallic treatment. Use
it on a word or two per heading, not a sentence.

**Repeated copy.** The header and footer are duplicated in all ten pages,
because nothing assembles them any more. Change one and you have changed one —
grep for the old wording and fix every copy, or the nav will disagree with
itself from page to page.

**Assets are cache-busted by hand.** Stylesheet and font links carry `?v=…`.
GitHub Pages sets long cache lifetimes, so if you edit a `.css` file and do not
change its `?v=` in every page that links it, returning visitors keep the old
one. Any new value works; it only has to differ from the last.

### Links

Every internal link is document-relative — `services.html`, `./` for home —
never `/services.html`. That matters: the site is served from
`x4ch4pur1.github.io/gns-website/`, a subdirectory, and a root-relative link
would resolve to `x4ch4pur1.github.io/services.html`, which does not exist. This
is what left an earlier deploy unstyled with dead navigation.

`404.html` is the one deliberate exception. GitHub Pages serves it at whatever
URL the visitor mistyped, so document-relative links in it would resolve against
that made-up path. It carries a `<base href="/">` plus three lines of script
that correct the base to `/gns-website/` on a `github.io` host.

`tools/check-audit.py` asserts both of these, so a regression fails the check
rather than the site.

---

## 3. Deploying

Push to `main`. GitHub Pages publishes the branch root; there is no workflow and
nothing to build. A change is live in a minute or so.

`.nojekyll` must stay. Without it GitHub runs the tree through Jekyll, which
silently drops files and folders whose names begin with an underscore.

### Moving to gnscales.com

The canonical tags, `sitemap.xml`, `robots.txt` and the Open Graph URLs all
already name `https://gnscales.com/`, which is correct for the custom domain and
wrong for the `github.io` URL. Nothing breaks in the meantime — canonical tags
affect search engines, not browsers — but do not submit the `github.io` URL to
Search Console while they say that.

To switch: add a `CNAME` file containing `gnscales.com` at the repository root,
point the DNS at GitHub Pages, and enable HTTPS in the repository's Pages
settings. The links inside the site need no change at all, because they are
relative.

---

## 4. Where enquiries go

The contact form has no server behind it. `main.js` handles it in one of two
modes, chosen by the attributes on the `<form>` in `contact.html`:

**Compose an email** (what it does today). `data-mailto="hello@gnscales.com"`
assembles the answers into a labelled message and opens the visitor's own mail
client with it filled in. Nothing is stored anywhere, and it works with no
account and no third party — but it costs the visitor one more click, and it
fails quietly for anyone browsing without a mail client configured. Without
JavaScript the form's plain `action` is that same `mailto:`, so the enquiry
still reaches you.

**Post to a form service** (better, when you want it). Sign up with Formspree,
Basin or Getform, then add their endpoint to the form tag:

```html
<form class="form" id="form" data-endpoint="https://formspree.io/f/xxxxxxxx" …>
```

`main.js` picks that up on its own — it POSTs the fields, reads the JSON reply
and redirects to `thanks.html`. No other change is needed. Keep `data-mailto`
alongside it as the no-JavaScript fallback.

The honeypot field (`name="website"`) and the `_started` timestamp are still in
the markup either way; the form services use both, and the mail path drops a
submission that fills the honeypot in.

---

## 5. Checks

```bash
python tools/check-audit.py   # every finding in the audit is still fixed
python tools/check-html.py    # no broken links, duplicate ids or stray tags
```

Both run against the shipped files and take about a second. Run them before you
push — `check-audit.py` is the thing that catches a root-relative link or a
missing cache-bust before GitHub Pages does.

---

## 6. Still to do — things no amount of code can fix

**Two founder photographs.** The highest-trust hour available. They do not need
a studio: consistent background, consistent crop, natural light, graded toward
the navy and gold. Drop them in `assets/` and replace the monogram markup in
`about.html`. A prospect in Indianapolis being asked to wire $3,000 to two
people they have never seen is being asked for a lot.

**LinkedIn links** for both founders. A named person with a findable profile is
worth more than any amount of copy about honesty.

**A real form endpoint.** See section 4. The mailto path works, but it loses the
enquiries where the visitor opens their mail client and then thinks better of
it — and you never learn that it happened.

**Google Search Console.** After the custom domain is live, not before: verify
the domain, submit `/sitemap.xml`, then request indexing on all nine public
URLs — home last, so the fresh crawl overwrites whatever is cached. Check the
coverage report for leftover Shopify URLs (`/collections/*`, `/products/*`,
`/cart`) and let them 404 cleanly; the custom 404 page is already wired up.

**Client work.** The Work page shows studio pieces, each labelled as
illustrative. Add real client work once each client has agreed to be named.

---

## 7. Decisions made on your behalf

These were open questions in the audit. Each was answered with the most
defensible option, and each is a straightforward edit if you disagree.

| Question | What the site says | Where to change it |
|---|---|---|
| Is "3 of 10 spots" real? | Treated as not real. The site says "Taking 10 founding partners" and names no count of clients. | Search the pages for "founding" |
| Founder's published name | **Nick Lomidze**, monogram `NL`. "Gn Lomidze" read like a truncated database field next to "George Lomidze". | `about.html`, `index.html` |
| Does Growth include TikTok? | Yes — "Two ad platforms of your choice — Meta, Google or TikTok". The ambiguity in "both of three" is gone. | `pricing.html` |
| Can you name Logimotors? | Not assumed. No client is named anywhere. | `work.html` |
| Real gross margin | 45% is the calculator's default. Replace it with your own estimate. | `index.html`, the margin slider |
| Calendar or form? | Form. A scheduler slot is ready: put a Cal.com or Calendly link in the `data-scheduler` attribute on the contact page and it loads only when somebody opens it. | `contact.html` |
| Cookie banner? | Off. The audience is United States, where a consent wall costs measurement without adding a requirement. Turn it on before advertising into the EU or UK. | `style.css`, `#consent` markup |
| Analytics? | None installed. `main.js` has the consent plumbing ready for GA4 or a Meta pixel when you want one. | `initConsent` in `main.js` |
