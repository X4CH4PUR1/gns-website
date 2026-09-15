# GN Scales — how this site works

A static website with a private admin behind it. The public pages are plain
`.html` files that Apache serves directly; the admin is PHP that regenerates
those files whenever content changes.

That split is deliberate. The site is the fastest thing we can ship, it matches
what the studio sells, and if PHP or the admin ever falls over the website keeps
serving exactly as before.

---

## 1. First run — do this immediately after deploying

1. Open **`https://gnscales.com/admin/`**.
2. You will get a one-time setup screen. Create your account.
3. That screen is then dead for good. Anyone who reaches `/admin/` afterwards
   needs your password.

**Do this before you tell anyone the site is live.** Until an account exists,
whoever finds `/admin/` first can claim it.

Then:

- Go to **Account** and add a second login for the other founder. Two accounts,
  not one shared password.
- Go to **Site & brand** and fill in the postal address. CAN-SPAM requires one
  in cold outreach, and the privacy page publishes it once it is set.
- Go to **Integrations** and paste in the GA4 and Meta Pixel IDs. The pixel is
  worth installing before you need it: a retargeting audience takes weeks to
  warm and you cannot backfill one.

---

## 2. Where everything lives

```
/                        the public site — generated, do not hand-edit
  index.html services.html pricing.html work.html about.html
  contact.html thanks.html privacy.html terms.html 404.html
  sitemap.xml robots.txt site.webmanifest
  favicon.* icon-*.png apple-touch-icon.png
  style.css + one stylesheet per page       hand-written
  main.js                                   hand-written
  submit.php               the contact form handler
  .htaccess                canonical host, caching, security headers
  .cpanel.yml              git deploy

  assets/fonts/            self-hosted webfonts
  assets/og/               generated social share cards
  assets/uploads/          images added through the admin
  assets/site.css          generated from the admin's Custom CSS box

/admin/                  the private panel
  index.php                every screen, one router
  api.php                  uploads, lead actions, exports
  rebuild.php              regenerates the whole site
  lib/                     the engine (see below)
  views/                   the admin's own screens
  templates/               the PUBLIC site's page templates
  assets/                  admin CSS and JS, plus TTFs for image generation

/data/                   live state — never in git, never served
  content.php              every word on the site
  users.php                password hashes
  leads.php                every enquiry received
  backups/                 the last 40 saves

/tools/                  checks; not deployed
/docs/                   this file; not deployed
```

### The engine, in the order it runs

| File | Does |
|---|---|
| `lib/boot.php` | Paths, session, loads everything else |
| `lib/default-content.php` | The shipped copy for every page |
| `lib/store.php` | Loads and saves content, backups, the break-even model |
| `lib/schema.php` | Describes every editable field — this is what builds the admin forms |
| `lib/render.php` | Turns content + templates into `.html` |
| `lib/ogimage.php` | Draws the favicons and the social share cards |
| `lib/media.php` | Uploads, with validation by file content rather than filename |
| `lib/leads.php` | The enquiry inbox and its notification email |
| `lib/auth.php` | Login, CSRF, throttling |

---

## 3. Editing the site

Everything is in the admin under **Content**. Each save writes
`data/content.php`, snapshots the previous version, and rebuilds every affected
page immediately. There is no separate publish step.

A few things worth knowing:

**Gold text.** Wrap words in `<span class="metal">…</span>` to get the metallic
treatment. `<br>` forces a line break. Those are the only tags that survive in a
content field — anything else is escaped and shows as literal text, which is
deliberate: a stray `<script>` typed into a headline should be visible, not live.

**The founding-spots line.** Two numbers under Site & brand drive it everywhere.
While *Spots taken* is `0` the site says "Taking 10 founding partners". Raise it
only when somebody has actually signed, and the copy changes to "N of 10
remaining" on its own. Do not type that sentence anywhere by hand — that is how
the old version ended up claiming seven clients that did not exist.

**Prices.** The pricing tiers feed three things at once: the pricing page, the
home page calculator's tier buttons, and the `Offer` structured data search
engines read. Change the number in one place.

**Adding a page** needs a developer: one entry in `gns_pages()`
(`lib/render.php`), one template in `admin/templates/`, one screen in
`lib/schema.php`. Everything else — sitemap, canonical, nav, social tags — follows.

---

## 4. Deploying

`.cpanel.yml` runs on every push to the branch cPanel is watching:

1. `rsync -a --delete` the repository into `public_html`,
   excluding `data/`, `assets/uploads/`, `docs/` and `tools/`.
2. `php admin/rebuild.php`, which writes the **live** content back over the
   repository's copies of the `.html` files.

Step 2 matters. A deploy overwrites every generated page with whatever was last
committed; the rebuild puts the admin's current content back. Content itself is
never at risk — it lives in `data/`, which the deploy does not touch.

**Before the first deploy**, open cPanel's Terminal and run `which rsync`. If it
is missing, use the fallback block commented at the bottom of `.cpanel.yml`.

If you want the repository to hold the current content as well, use
**Versions → Download content.json** in the admin and commit that file.

---

## 5. Where enquiries go

The contact form posts to `submit.php`, which:

1. Drops silent spam — a honeypot field, a minimum time-to-complete, and six
   submissions per IP per hour.
2. Validates, including checking that the two qualifying answers are real
   options rather than whatever was posted.
3. **Writes the lead to `data/leads.php` first**, then emails it.

That order is on purpose. Shared-host `mail()` fails often enough that a lead
which exists only in an email is a lead you can lose without ever knowing it
arrived. The admin's **Enquiries** screen flags any lead that did not email.

The notification is sent *from* `website@gnscales.com` with the visitor's
address in `Reply-To`. Sending as the visitor would fail SPF and land in spam.

To use an external service instead — Formspree, say — switch **Integrations →
Where the form posts** to *external* and paste the endpoint.

---

## 6. Checks

```bash
php tools/check-calc.php      # the break-even model agrees between PHP and JS
python tools/check-audit.py   # every finding in the audit is still fixed
python tools/check-html.py    # no broken links, duplicate ids or stray tags
bash tools/check-live.sh      # the live server: TLS, redirects, headers
```

The first three run against the generated site and take a second. Worth wiring
into the deploy once you are comfortable.

---

## 7. Still to do — things no amount of code can fix

**Two founder photographs.** The highest-trust hour available. They do not need
a studio: consistent background, consistent crop, natural light, graded toward
the navy and gold. Upload them under Founders and they replace the monograms
everywhere. A prospect in Indianapolis being asked to wire $3,000 to two people
they have never seen is being asked for a lot.

**LinkedIn links** for both founders, same screen. A named person with a
findable profile is worth more than any amount of copy about honesty.

**Google Search Console.** Verify the domain, submit `/sitemap.xml`, then
request indexing on all nine public URLs — home last, so the fresh crawl
overwrites whatever is cached. Check the coverage report for leftover Shopify
URLs (`/collections/*`, `/products/*`, `/cart`) and let them 404 cleanly; the
custom 404 page is already wired up. Bing Webmaster Tools imports from Search
Console in about five minutes.

**Client work.** The Work page currently shows studio pieces, each labelled as
illustrative. Add real client work under Work → Client work once each client has
agreed to be named. That section stays hidden until you do.

**UTM tags on outreach.** Link to
`gnscales.com/?utm_source=…&utm_medium=email&utm_campaign=…&utm_content=day6`.
`submit.php` records those against the lead, so you can tell which email did the
work rather than guessing.

---

## 8. Decisions made on your behalf

These were open questions in the audit. Each was answered with the most
defensible option and each is one field in the admin if you disagree.

| Question | What the site now says | Where to change it |
|---|---|---|
| Is "3 of 10 spots" real? | Treated as not real. Spots taken = 0, so the site says "Taking 10 founding partners". | Site & brand → Founding cohort |
| Founder's published name | **Nick Lomidze**, monogram `NL`. "Gn Lomidze" read like a truncated database field next to "George Lomidze". | Founders |
| Does Growth include TikTok? | Yes — "Two ad platforms of your choice — Meta, Google or TikTok". The ambiguity in "both of three" is gone. | Pricing → Tiers |
| Can you name Logimotors? | Not assumed. No client is named anywhere until you add one. | Work → Client work |
| Real gross margin | 45% is the shipped default for the calculator. Replace it with your own estimate. | Home page → The break-even model |
| Calendar or form? | Form, with a calendar slot ready. Paste a Cal.com or Calendly link and the contact page grows a scheduler that only loads when somebody opens it. | Integrations → Scheduler |
| Does `rsync` exist on the box? | Assumed yes; a fallback is commented in `.cpanel.yml`. | Run `which rsync` |
| Cookie banner? | Off. The audience is United States, where a consent wall costs measurement without adding a requirement. Turn it on before advertising into the EU or UK. | Integrations → Measurement |

---

## 9. Keeping the admin quiet

Nothing on the public site links to it, `robots.txt` disallows it, and every
admin page sends `noindex`. That is obscurity, not security — the password is
what protects it.

Two things worth doing:

- **Rename the folder.** `admin` → anything you like, in cPanel's File Manager.
  Every path inside is relative, so it keeps working, and
  `php <newname>/rebuild.php` still rebuilds. Update the `Disallow` line in
  `robots.txt` to match, or drop it.
- **Add a second lock.** cPanel's *Directory Privacy* puts an HTTP password on
  the folder. Two prompts is mildly annoying and genuinely harder to get past.

Sessions expire after two hours idle. Six failed logins from one address locks
it out for fifteen minutes. Every change is written to `data/audit.log`.
