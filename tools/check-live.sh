#!/usr/bin/env bash
#
# GN SCALES — LIVE SERVER CHECK
#
# Everything about the deployed site that cannot be verified from the source:
# TLS, redirects, compression, cache headers, and whether the files that should
# not be reachable are in fact unreachable.
#
#   bash tools/check-live.sh                    # checks gnscales.com
#   bash tools/check-live.sh staging.example.com
#
# Run it from anywhere with curl. Nothing here changes the server.

set -u
HOST="${1:-gnscales.com}"
BASE="https://${HOST}"

pass=0; fail=0; warn=0
ok()   { printf '  \033[32mOK\033[0m    %s\n' "$1"; pass=$((pass+1)); }
bad()  { printf '  \033[31mFAIL\033[0m  %s\n' "$1"; fail=$((fail+1)); }
note() { printf '  \033[33mCHECK\033[0m %s\n' "$1"; warn=$((warn+1)); }

head_of() { curl -sS -I -m 20 -L --max-redirs 0 "$1" 2>/dev/null; }
code_of() { curl -sS -o /dev/null -w '%{http_code}' -m 20 "$1" 2>/dev/null; }

echo
echo "GN Scales — checking ${BASE}"
echo

# ---------------------------------------------------------------- TLS
echo "TLS and canonical origin"
if curl -sS -I -m 20 "$BASE/" >/dev/null 2>&1; then
  ok "https responds"
else
  bad "https did not respond at all — nothing below will be meaningful"
fi

days=$(echo | openssl s_client -servername "$HOST" -connect "$HOST:443" 2>/dev/null \
  | openssl x509 -noout -enddate 2>/dev/null | cut -d= -f2)
if [ -n "${days:-}" ]; then
  ok "certificate valid until ${days}"
else
  note "could not read the certificate (openssl missing, or the host is down)"
fi

if curl -sS -I -m 20 "https://www.${HOST}/" >/dev/null 2>&1; then
  loc=$(head_of "https://www.${HOST}/" | tr -d '\r' | awk '/^[Ll]ocation:/{print $2}')
  case "$loc" in
    "$BASE"/*|"$BASE") ok "www redirects to the canonical host" ;;
    "") bad "www serves content instead of redirecting — two URLs, one site, split signals" ;;
    *)  note "www redirects to ${loc}" ;;
  esac
else
  bad "https://www.${HOST} failed — the certificate probably does not cover www, and a browser hits that error before it ever sees the redirect"
fi

loc=$(head_of "http://${HOST}/" | tr -d '\r' | awk '/^[Ll]ocation:/{print $2}')
case "$loc" in
  https://*) ok "http redirects to https" ;;
  *) bad "http did not redirect to https (got '${loc:-nothing}')" ;;
esac

# ------------------------------------------------------------- headers
echo
echo "Headers"
h=$(head_of "$BASE/style.css")
echo "$h" | grep -qi 'content-encoding:.*\(gzip\|br\)' \
  && ok "CSS is compressed" || bad "CSS is served uncompressed — check mod_deflate"
echo "$h" | grep -qi 'cache-control:.*max-age=3' \
  && ok "CSS has a long cache life" || note "CSS cache-control is not long-lived: $(echo "$h" | tr -d '\r' | grep -i cache-control)"

h=$(head_of "$BASE/")
echo "$h" | grep -qi 'x-content-type-options: *nosniff' \
  && ok "X-Content-Type-Options set" || bad "X-Content-Type-Options missing"
echo "$h" | grep -qi 'referrer-policy' \
  && ok "Referrer-Policy set" || bad "Referrer-Policy missing"
echo "$h" | grep -qi 'cache-control:.*no-cache' \
  && ok "HTML is not cached, so edits go live immediately" \
  || note "HTML cache-control: $(echo "$h" | tr -d '\r' | grep -i cache-control)"
echo "$h" | grep -qi 'strict-transport-security' \
  && ok "HSTS set" || note "HSTS not set — it is commented out in .htaccess until you are ready to commit for a year"

# --------------------------------------------------------------- pages
echo
echo "Pages and files"
for path in / /services.html /pricing.html /work.html /about.html /contact.html \
            /privacy.html /terms.html /thanks.html /robots.txt /sitemap.xml \
            /favicon.ico /site.webmanifest /assets/og/og-default.jpg; do
  c=$(code_of "${BASE}${path}")
  [ "$c" = "200" ] && ok "200  ${path}" || bad "${c}  ${path}"
done

c=$(code_of "${BASE}/this-page-does-not-exist")
[ "$c" = "404" ] && ok "404  unknown URLs return 404" || bad "${c}  unknown URLs should return 404"

body=$(curl -sS -m 20 "${BASE}/this-page-does-not-exist" 2>/dev/null)
echo "$body" | grep -qi 'gn scales' \
  && ok "the 404 is our page, not Apache's" \
  || bad "the 404 is not our page — check ErrorDocument in .htaccess"

# ------------------------------------------------------- must not leak
echo
echo "Things that must not be reachable"
for path in /data/content.php /data/users.php /data/leads.php /data/.htaccess \
            /.git/config /.cpanel.yml /admin/lib/auth.php /admin/lib/default-content.php; do
  c=$(code_of "${BASE}${path}")
  case "$c" in
    403|404) ok "${c}  ${path}" ;;
    200)     bad "200  ${path} IS READABLE — fix this before anything else" ;;
    *)       note "${c}  ${path}" ;;
  esac
done

c=$(code_of "${BASE}/admin/")
case "$c" in
  200|302) ok "${c}  /admin/ responds (it should ask for a password)" ;;
  *)       note "${c}  /admin/ — PHP may not be enabled on this host" ;;
esac

# -------------------------------------------------------------- legacy
echo
echo "Leftovers from the Shopify site"
for path in /collections/all /products/test /cart /pages/about; do
  c=$(code_of "${BASE}${path}")
  [ "$c" = "404" ] && ok "404  ${path}" || note "${c}  ${path} — still resolving; check it in Search Console"
done

echo
printf 'passed %d, failed %d, to check by hand %d\n\n' "$pass" "$fail" "$warn"
[ "$fail" -eq 0 ] || exit 1
