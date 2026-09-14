<?php
/**
 * GN SCALES — CONTENT SCHEMA
 *
 * Describes every editable field in the content tree. The admin builds its
 * forms from this, and saves are validated against it, so adding a new field to
 * the site is one entry here plus one line in the template — never a new page
 * of admin code.
 *
 * Field keys are short because there are several hundred of them:
 *   p  path (dotted, relative to the item inside a list)
 *   l  label
 *   t  type: text rich textarea richtext number toggle select url email
 *            image color code list
 *   h  help text
 *   w  layout width: 'half' or 'full' (default)
 *   o  options for select
 *   item  field definitions for each row of a list
 */

function gns_schema()
{
    return array(

        /* ===================================================== SITE */
        'site' => array(
            'label' => 'Site &amp; brand',
            'blurb' => 'Identity, contact details and the numbers that appear in more than one place.',
            'groups' => array(
                array('title' => 'Identity', 'fields' => array(
                    array('p' => 'site.name', 'l' => 'Studio name', 't' => 'text', 'w' => 'half'),
                    array('p' => 'site.legal_name', 'l' => 'Legal name', 't' => 'text', 'w' => 'half', 'h' => 'Used in the copyright line.'),
                    array('p' => 'site.url', 'l' => 'Site address', 't' => 'url', 'w' => 'half', 'h' => 'No trailing slash. Canonical tags, the sitemap and structured data are all built from this.'),
                    array('p' => 'site.tagline', 'l' => 'Tagline', 't' => 'text', 'w' => 'half'),
                    array('p' => 'site.blurb', 'l' => 'One-line description', 't' => 'richtext', 'h' => 'Footer, social cards and structured data fall back to this.'),
                    array('p' => 'site.price_range', 'l' => 'Price range', 't' => 'text', 'w' => 'half', 'h' => 'Shown to search engines in structured data, e.g. $1,500-$2,500/month'),
                    array('p' => 'site.founded', 'l' => 'Founded', 't' => 'text', 'w' => 'half'),
                )),
                array('title' => 'Contact', 'fields' => array(
                    array('p' => 'site.email', 'l' => 'Public email', 't' => 'email', 'w' => 'half', 'h' => 'Appears in the footer of every page and on the contact page.'),
                    array('p' => 'site.phone', 'l' => 'Phone (optional)', 't' => 'text', 'w' => 'half', 'h' => 'Leave blank to hide it everywhere.'),
                    array('p' => 'site.location', 'l' => 'Location line', 't' => 'rich', 'w' => 'half'),
                    array('p' => 'site.postal', 'l' => 'Postal address', 't' => 'textarea', 'w' => 'half', 'h' => 'Required by CAN-SPAM in any cold outreach, and shown on the privacy page when filled in.'),
                    array('p' => 'site.social.linkedin', 'l' => 'LinkedIn URL', 't' => 'url', 'w' => 'half'),
                    array('p' => 'site.social.instagram', 'l' => 'Instagram URL', 't' => 'url', 'w' => 'half'),
                )),
                array('title' => 'Founding cohort', 'blurb' => 'One pair of numbers drives the scarcity line on every page. While "taken" is 0 the site says "Taking 10 founding partners" and claims nothing untrue.', 'fields' => array(
                    array('p' => 'site.spots_total', 'l' => 'Total spots', 't' => 'number', 'w' => 'half'),
                    array('p' => 'site.spots_taken', 'l' => 'Spots taken', 't' => 'number', 'w' => 'half', 'h' => 'Raise this only when a client has actually signed.'),
                )),
                array('title' => 'Appearance &amp; sharing', 'fields' => array(
                    array('p' => 'site.theme_color', 'l' => 'Browser theme colour', 't' => 'color', 'w' => 'half'),
                    array('p' => 'site.twitter', 'l' => 'X / Twitter handle', 't' => 'text', 'w' => 'half', 'h' => 'With the @, or leave blank.'),
                    array('p' => 'site.og_image', 'l' => 'Default social share image', 't' => 'image', 'h' => '1200 x 630. Used whenever a page has no image of its own.'),
                )),
            ),
        ),

        /* =============================================== NAV & FOOTER */
        'nav' => array(
            'label' => 'Navigation &amp; footer',
            'blurb' => 'The header menu, the header button, and the three footer columns.',
            'groups' => array(
                array('title' => 'Header menu', 'fields' => array(
                    array('p' => 'site.nav', 'l' => 'Menu items', 't' => 'list', 'add' => 'Add a menu item', 'item' => array(
                        array('p' => 'label', 'l' => 'Label', 't' => 'rich', 'w' => 'half'),
                        array('p' => 'href', 'l' => 'Link', 't' => 'text', 'w' => 'half', 'h' => 'e.g. services.html, or / for the home page'),
                    )),
                )),
                array('title' => 'Header button', 'fields' => array(
                    array('p' => 'site.cta.header_label', 'l' => 'Header button label', 't' => 'rich', 'w' => 'half'),
                    array('p' => 'site.cta.href', 'l' => 'Button link', 't' => 'text', 'w' => 'half'),
                    array('p' => 'site.cta.label', 'l' => 'Long button label', 't' => 'rich', 'w' => 'half', 'h' => 'Used where there is room for the full wording.'),
                )),
                array('title' => 'Footer', 'fields' => array(
                    array('p' => 'site.footer.pages_heading', 'l' => 'Column 1 heading', 't' => 'text', 'w' => 'half'),
                    array('p' => 'site.footer.caps_heading', 'l' => 'Column 2 heading', 't' => 'text', 'w' => 'half'),
                    array('p' => 'site.footer.touch_heading', 'l' => 'Column 3 heading', 't' => 'text', 'w' => 'half'),
                    array('p' => 'site.footer.cta_label', 'l' => 'Footer call to action', 't' => 'rich', 'w' => 'half'),
                    array('p' => 'site.footer.caps', 'l' => 'Capability links', 't' => 'list', 'add' => 'Add a capability link', 'item' => array(
                        array('p' => 'label', 'l' => 'Label', 't' => 'rich', 'w' => 'half'),
                        array('p' => 'href', 'l' => 'Link', 't' => 'text', 'w' => 'half'),
                    )),
                    array('p' => 'site.footer.note', 'l' => 'Footer note', 't' => 'richtext'),
                )),
            ),
        ),

        /* ===================================================== HOME */
        'home' => array(
            'label' => 'Home page',
            'blurb' => 'Everything on the front page, top to bottom.',
            'page'  => 'index.html',
            'groups' => array(
                array('title' => 'Search &amp; sharing', 'fields' => gns_schema_seo('home')),
                array('title' => 'Hero', 'fields' => array(
                    array('p' => 'pages.home.hero.h1', 'l' => 'Headline', 't' => 'rich', 'h' => 'Wrap words in &lt;span class="metal"&gt;...&lt;/span&gt; for the gold treatment. &lt;br&gt; forces a line break.'),
                    array('p' => 'pages.home.hero.sub', 'l' => 'Sub-headline', 't' => 'richtext'),
                    array('p' => 'pages.home.hero.flag_dim', 'l' => 'Flag suffix', 't' => 'rich', 'w' => 'half', 'h' => 'Follows the founding-spots line at the top of the hero.'),
                    array('p' => 'pages.home.hero.cta_label', 'l' => 'Primary button', 't' => 'rich', 'w' => 'half'),
                    array('p' => 'pages.home.hero.cta_href', 'l' => 'Primary link', 't' => 'text', 'w' => 'half'),
                    array('p' => 'pages.home.hero.alt_label', 'l' => 'Secondary button', 't' => 'rich', 'w' => 'half'),
                    array('p' => 'pages.home.hero.alt_href', 'l' => 'Secondary link', 't' => 'text', 'w' => 'half'),
                    array('p' => 'pages.home.hero.facts', 'l' => 'Three facts under the hero', 't' => 'list', 'simple' => true, 'add' => 'Add a fact'),
                )),
                array('title' => 'Scrolling sector strip', 'fields' => array(
                    array('p' => 'pages.home.marquee', 'l' => 'Words', 't' => 'list', 'simple' => true, 'add' => 'Add a word'),
                )),
                array('title' => '01 — The gap', 'fields' => array(
                    array('p' => 'pages.home.gap.eyebrow', 'l' => 'Eyebrow', 't' => 'rich', 'w' => 'half'),
                    array('p' => 'pages.home.gap.h2', 'l' => 'Heading', 't' => 'rich'),
                    array('p' => 'pages.home.gap.body', 'l' => 'Paragraphs', 't' => 'list', 'simple' => true, 'rich' => true, 'add' => 'Add a paragraph'),
                    array('p' => 'pages.home.gap.link_label', 'l' => 'Link label', 't' => 'rich', 'w' => 'half'),
                    array('p' => 'pages.home.gap.link_href', 'l' => 'Link target', 't' => 'text', 'w' => 'half'),
                    array('p' => 'pages.home.gap.left_label', 'l' => 'Left column heading', 't' => 'rich', 'w' => 'half'),
                    array('p' => 'pages.home.gap.right_label', 'l' => 'Right column heading', 't' => 'rich', 'w' => 'half'),
                    array('p' => 'pages.home.gap.left_steps', 'l' => 'Left column steps', 't' => 'list', 'simple' => true, 'rich' => true, 'add' => 'Add a step', 'h' => 'The last step is styled as the dead end.'),
                    array('p' => 'pages.home.gap.right_steps', 'l' => 'Right column steps', 't' => 'list', 'simple' => true, 'rich' => true, 'add' => 'Add a step', 'h' => 'The last step is styled as the live one.'),
                )),
                array('title' => '02 — Capabilities', 'fields' => array(
                    array('p' => 'pages.home.capabilities.eyebrow', 'l' => 'Eyebrow', 't' => 'rich', 'w' => 'half'),
                    array('p' => 'pages.home.capabilities.h2', 'l' => 'Heading', 't' => 'rich'),
                    array('p' => 'pages.home.capabilities.lead', 'l' => 'Lead paragraph', 't' => 'richtext'),
                    array('p' => 'pages.home.capabilities.items', 'l' => 'Capability cards', 't' => 'list', 'add' => 'Add a capability', 'item' => array(
                        array('p' => 'title', 'l' => 'Title', 't' => 'rich', 'w' => 'half'),
                        array('p' => 'visual', 'l' => 'Icon &amp; illustration', 't' => 'select', 'w' => 'half', 'o' => array(
                            'funnel'   => 'Funnel bars',
                            'serp'     => 'Search result',
                            'minipage' => 'Mini landing page',
                            'retainer' => 'Retainer band',
                            'tiktok'   => 'Vertical video icon only',
                            'sequence' => 'Email icon only',
                            'none'     => 'Generic icon, no illustration',
                        )),
                        array('p' => 'size', 'l' => 'Card size', 't' => 'select', 'w' => 'half', 'o' => array(
                            '' => 'Standard', 'lg' => 'Large', 'wide' => 'Wide', 'full' => 'Full width',
                        )),
                        array('p' => 'gold', 'l' => 'Gold treatment', 't' => 'toggle', 'w' => 'half'),
                        array('p' => 'body', 'l' => 'Body', 't' => 'richtext'),
                    )),
                    array('p' => 'pages.home.capabilities.all_label', 'l' => '"All services" label', 't' => 'rich', 'w' => 'half'),
                    array('p' => 'pages.home.capabilities.all_href', 'l' => '"All services" link', 't' => 'text', 'w' => 'half'),
                )),
                array('title' => '03 — Process', 'fields' => array(
                    array('p' => 'pages.home.process.eyebrow', 'l' => 'Eyebrow', 't' => 'rich', 'w' => 'half'),
                    array('p' => 'pages.home.process.h2', 'l' => 'Heading', 't' => 'rich'),
                    array('p' => 'pages.home.process.steps', 'l' => 'Steps', 't' => 'list', 'add' => 'Add a step', 'item' => array(
                        array('p' => 'when', 'l' => 'Timing', 't' => 'rich', 'w' => 'half'),
                        array('p' => 'title', 'l' => 'Title', 't' => 'rich', 'w' => 'half'),
                        array('p' => 'state', 'l' => 'Marker', 't' => 'select', 'w' => 'half', 'o' => array('' => 'Neutral', 'on' => 'Filled', 'off' => 'Dimmed')),
                        array('p' => 'body', 'l' => 'Body', 't' => 'richtext'),
                    )),
                )),
                array('title' => '04 — The break-even model', 'blurb' => 'Opening positions for the sliders. The model works in gross profit, not revenue.', 'fields' => array(
                    array('p' => 'pages.home.maths.eyebrow', 'l' => 'Eyebrow', 't' => 'rich', 'w' => 'half'),
                    array('p' => 'pages.home.maths.h2', 'l' => 'Heading', 't' => 'rich', 'w' => 'half'),
                    array('p' => 'pages.home.maths.body', 'l' => 'Body', 't' => 'richtext'),
                    array('p' => 'pages.home.maths.note', 'l' => 'Disclaimer', 't' => 'richtext'),
                    array('p' => 'pages.home.maths.default_spend', 'l' => 'Default monthly spend', 't' => 'number', 'w' => 'half'),
                    array('p' => 'pages.home.maths.default_job', 'l' => 'Default job value', 't' => 'number', 'w' => 'half'),
                    array('p' => 'pages.home.maths.default_margin', 'l' => 'Default gross margin %', 't' => 'number', 'w' => 'half', 'h' => 'Your honest estimate for these trades. 45% is the shipped placeholder.'),
                    array('p' => 'pages.home.maths.default_close', 'l' => 'Default close rate %', 't' => 'number', 'w' => 'half'),
                    array('p' => 'pages.home.maths.default_tier', 'l' => 'Default tier', 't' => 'text', 'w' => 'half', 'h' => 'Must match a tier key on the pricing page: launch or growth.'),
                )),
                array('title' => '05 — The studio', 'fields' => array(
                    array('p' => 'pages.home.studio.eyebrow', 'l' => 'Eyebrow', 't' => 'rich', 'w' => 'half'),
                    array('p' => 'pages.home.studio.h2', 'l' => 'Heading', 't' => 'rich', 'w' => 'half'),
                    array('p' => 'pages.home.studio.lead', 'l' => 'Lead paragraph', 't' => 'richtext', 'h' => 'The two founder cards come from the Founders screen.'),
                )),
                array('title' => '06 — Before and after', 'fields' => array(
                    array('p' => 'pages.home.craft.eyebrow', 'l' => 'Eyebrow', 't' => 'rich', 'w' => 'half'),
                    array('p' => 'pages.home.craft.h2', 'l' => 'Heading', 't' => 'rich', 'w' => 'half'),
                    array('p' => 'pages.home.craft.lead', 'l' => 'Lead paragraph', 't' => 'richtext'),
                    array('p' => 'pages.home.craft.after_tag', 'l' => 'After — tag', 't' => 'rich', 'w' => 'half'),
                    array('p' => 'pages.home.craft.after_kicker', 'l' => 'After — kicker', 't' => 'rich', 'w' => 'half'),
                    array('p' => 'pages.home.craft.after_head', 'l' => 'After — headline', 't' => 'rich'),
                    array('p' => 'pages.home.craft.after_btn', 'l' => 'After — button', 't' => 'rich', 'w' => 'half'),
                    array('p' => 'pages.home.craft.after_meta', 'l' => 'After — meta', 't' => 'rich', 'w' => 'half'),
                    array('p' => 'pages.home.craft.before_tag', 'l' => 'Before — tag', 't' => 'rich', 'w' => 'half'),
                    array('p' => 'pages.home.craft.before_head', 'l' => 'Before — headline', 't' => 'rich'),
                    array('p' => 'pages.home.craft.before_sub', 'l' => 'Before — body', 't' => 'richtext'),
                    array('p' => 'pages.home.craft.before_btn_a', 'l' => 'Before — button one', 't' => 'rich', 'w' => 'half'),
                    array('p' => 'pages.home.craft.before_btn_b', 'l' => 'Before — button two', 't' => 'rich', 'w' => 'half'),
                    array('p' => 'pages.home.craft.note', 'l' => 'Honesty note', 't' => 'richtext', 'h' => 'Keep this: both panels are our own work and the page should say so.'),
                )),
                array('title' => '07 — FAQ', 'fields' => array(
                    array('p' => 'pages.home.faq.eyebrow', 'l' => 'Eyebrow', 't' => 'rich', 'w' => 'half'),
                    array('p' => 'pages.home.faq.h2', 'l' => 'Heading', 't' => 'rich', 'w' => 'half'),
                    array('p' => 'pages.home.faq.items', 'l' => 'Questions', 't' => 'list', 'add' => 'Add a question', 'h' => 'Also published as FAQ structured data.', 'item' => array(
                        array('p' => 'q', 'l' => 'Question', 't' => 'rich'),
                        array('p' => 'a', 'l' => 'Answer', 't' => 'richtext'),
                    )),
                )),
                array('title' => 'Closing band', 'fields' => gns_schema_closing('home')),
            ),
        ),

        /* ================================================= SERVICES */
        'services' => array(
            'label' => 'Services page',
            'page'  => 'services.html',
            'groups' => array(
                array('title' => 'Search &amp; sharing', 'fields' => gns_schema_seo('services')),
                array('title' => 'Intro', 'fields' => array(
                    array('p' => 'pages.services.intro.eyebrow', 'l' => 'Eyebrow', 't' => 'rich', 'w' => 'half'),
                    array('p' => 'pages.services.intro.h1', 'l' => 'Headline', 't' => 'rich'),
                    array('p' => 'pages.services.intro.lead', 'l' => 'Lead paragraph', 't' => 'richtext'),
                    array('p' => 'pages.services.intro.keyword_h2', 'l' => 'Sub-heading', 't' => 'rich', 'h' => 'Carries the words people actually search for. Worth keeping plain.'),
                )),
                array('title' => 'Main services', 'fields' => array(
                    array('p' => 'pages.services.sections', 'l' => 'Service sections', 't' => 'list', 'add' => 'Add a service section', 'item' => array(
                        array('p' => 'index', 'l' => 'Number', 't' => 'text', 'w' => 'half'),
                        array('p' => 'id', 'l' => 'Anchor id', 't' => 'text', 'w' => 'half', 'h' => 'Used in links like services.html#meta'),
                        array('p' => 'kicker', 'l' => 'Kicker', 't' => 'rich', 'w' => 'half'),
                        array('p' => 'title', 'l' => 'Title', 't' => 'rich', 'w' => 'half'),
                        array('p' => 'visual', 'l' => 'Illustration', 't' => 'select', 'w' => 'half', 'o' => array(
                            'funnel' => 'Funnel stages', 'serp' => 'Search results page', 'browser' => 'Landing page browser',
                        )),
                        array('p' => 'reversed', 'l' => 'Illustration on the left', 't' => 'toggle', 'w' => 'half'),
                        array('p' => 'gold', 'l' => 'Gold treatment', 't' => 'toggle', 'w' => 'half'),
                        array('p' => 'body', 'l' => 'Paragraphs', 't' => 'list', 'simple' => true, 'rich' => true, 'add' => 'Add a paragraph'),
                        array('p' => 'note', 'l' => 'Pulled-out note', 't' => 'richtext'),
                        array('p' => 'ticks', 'l' => 'Tick list', 't' => 'list', 'simple' => true, 'rich' => true, 'add' => 'Add a point'),
                    )),
                )),
                array('title' => 'Also in scope', 'fields' => array(
                    array('p' => 'pages.services.more.eyebrow', 'l' => 'Eyebrow', 't' => 'rich', 'w' => 'half'),
                    array('p' => 'pages.services.more.h2', 'l' => 'Heading', 't' => 'rich', 'w' => 'half'),
                    array('p' => 'pages.services.more.lead', 'l' => 'Lead paragraph', 't' => 'richtext'),
                    array('p' => 'pages.services.more.items', 'l' => 'Cards', 't' => 'list', 'add' => 'Add a card', 'item' => array(
                        array('p' => 'title', 'l' => 'Title', 't' => 'rich', 'w' => 'half'),
                        array('p' => 'visual', 'l' => 'Illustration', 't' => 'select', 'w' => 'half', 'o' => array(
                            'tiktok' => 'Vertical video frame', 'sequence' => 'Email sequence', 'orbit' => 'Retainer orbit', 'none' => 'None',
                        )),
                        array('p' => 'body', 'l' => 'Body', 't' => 'richtext'),
                    )),
                )),
                array('title' => 'Closing band', 'fields' => gns_schema_closing('services')),
            ),
        ),

        /* ================================================== PRICING */
        'pricing' => array(
            'label' => 'Pricing page',
            'page'  => 'pricing.html',
            'groups' => array(
                array('title' => 'Search &amp; sharing', 'fields' => gns_schema_seo('pricing')),
                array('title' => 'Intro', 'fields' => array(
                    array('p' => 'pages.pricing.intro.eyebrow', 'l' => 'Eyebrow', 't' => 'rich', 'w' => 'half'),
                    array('p' => 'pages.pricing.intro.h1', 'l' => 'Headline', 't' => 'rich'),
                    array('p' => 'pages.pricing.intro.lead', 'l' => 'Lead paragraph', 't' => 'richtext'),
                    array('p' => 'pages.pricing.intro.keyword_h2', 'l' => 'Sub-heading', 't' => 'rich'),
                )),
                array('title' => 'Tiers', 'blurb' => 'The price is also the fee the home-page calculator uses, and it is published as structured data.', 'fields' => array(
                    array('p' => 'pages.pricing.tiers', 'l' => 'Retainer tiers', 't' => 'list', 'add' => 'Add a tier', 'item' => array(
                        array('p' => 'key', 'l' => 'Key', 't' => 'text', 'w' => 'half', 'h' => 'Lowercase, no spaces. The calculator refers to this.'),
                        array('p' => 'name', 'l' => 'Name', 't' => 'rich', 'w' => 'half'),
                        array('p' => 'price', 'l' => 'Monthly price', 't' => 'number', 'w' => 'half'),
                        array('p' => 'period', 'l' => 'Period suffix', 't' => 'rich', 'w' => 'half'),
                        array('p' => 'for', 'l' => 'Who it is for', 't' => 'rich', 'w' => 'half'),
                        array('p' => 'flag', 'l' => 'Badge', 't' => 'rich', 'w' => 'half', 'h' => 'Keep it factual. "Most chosen" is a claim about customers you would have to have.'),
                        array('p' => 'setup', 'l' => 'Setup fee line', 't' => 'rich', 'w' => 'half'),
                        array('p' => 'featured', 'l' => 'Featured styling', 't' => 'toggle', 'w' => 'half'),
                        array('p' => 'features', 'l' => 'Included', 't' => 'list', 'simple' => true, 'rich' => true, 'add' => 'Add a line'),
                        array('p' => 'cta_label', 'l' => 'Button label', 't' => 'rich', 'w' => 'half'),
                        array('p' => 'cta_href', 'l' => 'Button link', 't' => 'text', 'w' => 'half'),
                    )),
                    array('p' => 'pages.pricing.parity', 'l' => 'Parity note', 't' => 'richtext'),
                )),
                array('title' => 'Ad spend disclosure', 'fields' => array(
                    array('p' => 'pages.pricing.spend_h2', 'l' => 'Heading', 't' => 'rich'),
                    array('p' => 'pages.pricing.spend_body', 'l' => 'Body', 't' => 'richtext'),
                )),
                array('title' => 'Setup fee', 'fields' => array(
                    array('p' => 'pages.pricing.setup.eyebrow', 'l' => 'Eyebrow', 't' => 'rich', 'w' => 'half'),
                    array('p' => 'pages.pricing.setup.h2', 'l' => 'Heading', 't' => 'rich', 'w' => 'half'),
                    array('p' => 'pages.pricing.setup.lead', 'l' => 'Lead paragraph', 't' => 'richtext'),
                    array('p' => 'pages.pricing.setup.steps', 'l' => 'Steps', 't' => 'list', 'add' => 'Add a step', 'item' => array(
                        array('p' => 'title', 'l' => 'Title', 't' => 'rich', 'w' => 'half'),
                        array('p' => 'body', 'l' => 'Body', 't' => 'richtext'),
                    )),
                )),
                array('title' => 'FAQ', 'fields' => array(
                    array('p' => 'pages.pricing.faq.eyebrow', 'l' => 'Eyebrow', 't' => 'rich', 'w' => 'half'),
                    array('p' => 'pages.pricing.faq.h2', 'l' => 'Heading', 't' => 'rich', 'w' => 'half'),
                    array('p' => 'pages.pricing.faq.items', 'l' => 'Questions', 't' => 'list', 'add' => 'Add a question', 'item' => array(
                        array('p' => 'q', 'l' => 'Question', 't' => 'rich'),
                        array('p' => 'a', 'l' => 'Answer', 't' => 'richtext'),
                    )),
                )),
                array('title' => 'Closing band', 'fields' => gns_schema_closing('pricing')),
            ),
        ),

        /* ===================================================== WORK */
        'work' => array(
            'label' => 'Work page',
            'page'  => 'work.html',
            'blurb' => 'Craft, not case studies. Client entries stay hidden until you add at least one.',
            'groups' => array(
                array('title' => 'Search &amp; sharing', 'fields' => gns_schema_seo('work')),
                array('title' => 'Intro', 'fields' => array(
                    array('p' => 'pages.work.intro.eyebrow', 'l' => 'Eyebrow', 't' => 'rich', 'w' => 'half'),
                    array('p' => 'pages.work.intro.h1', 'l' => 'Headline', 't' => 'rich'),
                    array('p' => 'pages.work.intro.lead', 'l' => 'Lead paragraph', 't' => 'richtext'),
                    array('p' => 'pages.work.honesty', 'l' => 'Honesty note', 't' => 'richtext'),
                )),
                array('title' => 'Studio pieces', 'fields' => array(
                    array('p' => 'pages.work.craft.eyebrow', 'l' => 'Eyebrow', 't' => 'rich', 'w' => 'half'),
                    array('p' => 'pages.work.craft.h2', 'l' => 'Heading', 't' => 'rich', 'w' => 'half'),
                    array('p' => 'pages.work.craft.lead', 'l' => 'Lead paragraph', 't' => 'richtext'),
                    array('p' => 'pages.work.craft.items', 'l' => 'Pieces', 't' => 'list', 'add' => 'Add a piece', 'item' => array(
                        array('p' => 'title', 'l' => 'Title', 't' => 'rich'),
                        array('p' => 'kind', 'l' => 'Built-in illustration', 't' => 'select', 'w' => 'half', 'o' => array(
                            'ad' => 'Designed ad', 'page' => 'Landing page', 'search' => 'Search result',
                        ), 'h' => 'Ignored once you upload an image.'),
                        array('p' => 'label', 'l' => 'Corner label', 't' => 'rich', 'w' => 'half'),
                        array('p' => 'image', 'l' => 'Image', 't' => 'image', 'h' => 'Upload a real piece and it replaces the built-in illustration.'),
                        array('p' => 'problem', 'l' => 'The problem', 't' => 'richtext'),
                        array('p' => 'did', 'l' => 'What we did', 't' => 'richtext'),
                    )),
                )),
                array('title' => 'Client work', 'blurb' => 'Only publish a client here once they have said yes. The whole section stays hidden while the list is empty.', 'fields' => array(
                    array('p' => 'pages.work.clients.eyebrow', 'l' => 'Eyebrow', 't' => 'rich', 'w' => 'half'),
                    array('p' => 'pages.work.clients.h2', 'l' => 'Heading', 't' => 'rich', 'w' => 'half'),
                    array('p' => 'pages.work.clients.lead', 'l' => 'Lead paragraph', 't' => 'richtext'),
                    array('p' => 'pages.work.clients.items', 'l' => 'Clients', 't' => 'list', 'add' => 'Add a client', 'item' => array(
                        array('p' => 'title', 'l' => 'Client or project', 't' => 'rich', 'w' => 'half'),
                        array('p' => 'sector', 'l' => 'Sector', 't' => 'rich', 'w' => 'half'),
                        array('p' => 'image', 'l' => 'Image', 't' => 'image'),
                        array('p' => 'body', 'l' => 'What the work was', 't' => 'richtext'),
                        array('p' => 'link', 'l' => 'Link', 't' => 'url', 'w' => 'half'),
                        array('p' => 'link_label', 'l' => 'Link label', 't' => 'rich', 'w' => 'half'),
                    )),
                )),
                array('title' => 'Closing band', 'fields' => gns_schema_closing('work')),
            ),
        ),

        /* ================================================== FOUNDERS */
        'founders' => array(
            'label' => 'Founders',
            'blurb' => 'Used on both the home page and the studio page. Upload a photograph and it replaces the monogram everywhere.',
            'groups' => array(
                array('title' => 'The two of you', 'fields' => array(
                    array('p' => 'founders', 'l' => 'Founders', 't' => 'list', 'add' => 'Add a founder', 'item' => array(
                        array('p' => 'name', 'l' => 'Name', 't' => 'rich', 'w' => 'half'),
                        array('p' => 'initials', 'l' => 'Initials', 't' => 'text', 'w' => 'half', 'h' => 'Shown while there is no photograph. Keep the two different.'),
                        array('p' => 'role_home', 'l' => 'Short role', 't' => 'rich', 'w' => 'half'),
                        array('p' => 'role_full', 'l' => 'Full role', 't' => 'rich', 'w' => 'half'),
                        array('p' => 'tone', 'l' => 'Monogram tone', 't' => 'select', 'w' => 'half', 'o' => array('warm' => 'Warm gold', 'cool' => 'Cool steel')),
                        array('p' => 'linkedin', 'l' => 'LinkedIn URL', 't' => 'url', 'w' => 'half', 'h' => 'A findable profile is worth more trust than any amount of copy.'),
                        array('p' => 'photo', 'l' => 'Photograph', 't' => 'image', 'h' => 'Portrait crop, about 600 x 750. This is the highest-trust item on the site.'),
                        array('p' => 'bio_home', 'l' => 'Short bio (home page)', 't' => 'richtext'),
                        array('p' => 'bio_full', 'l' => 'Full bio (studio page)', 't' => 'richtext'),
                    )),
                )),
            ),
        ),

        /* =================================================== STUDIO */
        'about' => array(
            'label' => 'Studio page',
            'page'  => 'about.html',
            'groups' => array(
                array('title' => 'Search &amp; sharing', 'fields' => gns_schema_seo('about')),
                array('title' => 'Intro', 'fields' => array(
                    array('p' => 'pages.about.intro.eyebrow', 'l' => 'Eyebrow', 't' => 'rich', 'w' => 'half'),
                    array('p' => 'pages.about.intro.h1', 'l' => 'Headline', 't' => 'rich'),
                    array('p' => 'pages.about.intro.lead', 'l' => 'Lead paragraph', 't' => 'richtext'),
                )),
                array('title' => 'Origin', 'fields' => array(
                    array('p' => 'pages.about.story.eyebrow', 'l' => 'Eyebrow', 't' => 'rich', 'w' => 'half'),
                    array('p' => 'pages.about.story.h2', 'l' => 'Section heading', 't' => 'rich', 'w' => 'half', 'h' => 'Read by screen readers and search engines; the opening sentence below is what a visitor sees.'),
                    array('p' => 'pages.about.story.lede', 'l' => 'Opening sentence', 't' => 'richtext'),
                    array('p' => 'pages.about.story.body', 'l' => 'Paragraphs', 't' => 'list', 'simple' => true, 'rich' => true, 'add' => 'Add a paragraph'),
                    array('p' => 'pages.about.story.note', 'l' => 'Pulled-out note', 't' => 'richtext'),
                )),
                array('title' => 'Founders section', 'fields' => array(
                    array('p' => 'pages.about.founders.eyebrow', 'l' => 'Eyebrow', 't' => 'rich', 'w' => 'half'),
                    array('p' => 'pages.about.founders.h2', 'l' => 'Heading', 't' => 'rich', 'w' => 'half'),
                    array('p' => 'pages.about.founders.lead', 'l' => 'Lead paragraph', 't' => 'richtext'),
                )),
                array('title' => 'Principles', 'fields' => array(
                    array('p' => 'pages.about.principles.eyebrow', 'l' => 'Eyebrow', 't' => 'rich', 'w' => 'half'),
                    array('p' => 'pages.about.principles.h2', 'l' => 'Heading', 't' => 'rich', 'w' => 'half'),
                    array('p' => 'pages.about.principles.items', 'l' => 'Principles', 't' => 'list', 'add' => 'Add a principle', 'item' => array(
                        array('p' => 'title', 'l' => 'Title', 't' => 'rich', 'w' => 'half'),
                        array('p' => 'body', 'l' => 'Body', 't' => 'richtext'),
                    )),
                )),
                array('title' => 'Closing band', 'fields' => gns_schema_closing('about')),
            ),
        ),

        /* ================================================== CONTACT */
        'contact' => array(
            'label' => 'Contact page',
            'page'  => 'contact.html',
            'groups' => array(
                array('title' => 'Search &amp; sharing', 'fields' => gns_schema_seo('contact')),
                array('title' => 'Intro', 'fields' => array(
                    array('p' => 'pages.contact.intro.eyebrow', 'l' => 'Eyebrow', 't' => 'rich', 'w' => 'half'),
                    array('p' => 'pages.contact.intro.h1', 'l' => 'Headline', 't' => 'rich'),
                    array('p' => 'pages.contact.intro.lead', 'l' => 'Lead paragraph', 't' => 'richtext'),
                    array('p' => 'pages.contact.direct_label', 'l' => 'Email block label', 't' => 'rich', 'w' => 'half'),
                )),
                array('title' => 'What happens next', 'fields' => array(
                    array('p' => 'pages.contact.steps', 'l' => 'Steps', 't' => 'list', 'add' => 'Add a step', 'item' => array(
                        array('p' => 'title', 'l' => 'Title', 't' => 'rich'),
                        array('p' => 'desc', 'l' => 'Description', 't' => 'richtext'),
                    )),
                )),
                array('title' => 'The form', 'fields' => array(
                    array('p' => 'pages.contact.form.heading', 'l' => 'Form heading', 't' => 'rich', 'w' => 'half'),
                    array('p' => 'pages.contact.form.time', 'l' => 'Time badge', 't' => 'rich', 'w' => 'half'),
                    array('p' => 'pages.contact.form.submit', 'l' => 'Submit label', 't' => 'rich', 'w' => 'half', 'h' => 'Used when no calendar link is set.'),
                    array('p' => 'pages.contact.form.submit_cal', 'l' => 'Submit label with calendar', 't' => 'rich', 'w' => 'half'),
                    array('p' => 'pages.contact.form.privacy', 'l' => 'Privacy line', 't' => 'richtext'),
                    array('p' => 'pages.contact.form.privacy_link', 'l' => 'Privacy link', 't' => 'text', 'w' => 'half'),
                    array('p' => 'pages.contact.form.message_label', 'l' => 'Message label', 't' => 'rich', 'w' => 'half'),
                    array('p' => 'pages.contact.form.message_placeholder', 'l' => 'Message placeholder', 't' => 'text'),
                    array('p' => 'pages.contact.form.sector_legend', 'l' => 'Sector question', 't' => 'rich', 'w' => 'half'),
                    array('p' => 'pages.contact.form.sector_options', 'l' => 'Sector options', 't' => 'list', 'add' => 'Add an option', 'item' => array(
                        array('p' => 'value', 'l' => 'Stored value', 't' => 'text', 'w' => 'half'),
                        array('p' => 'label', 'l' => 'Label', 't' => 'rich', 'w' => 'half'),
                    )),
                    array('p' => 'pages.contact.form.spend_legend', 'l' => 'Spend question', 't' => 'rich', 'w' => 'half'),
                    array('p' => 'pages.contact.form.spend_options', 'l' => 'Spend options', 't' => 'list', 'add' => 'Add an option', 'item' => array(
                        array('p' => 'value', 'l' => 'Stored value', 't' => 'text', 'w' => 'half'),
                        array('p' => 'label', 'l' => 'Label', 't' => 'rich', 'w' => 'half'),
                    )),
                )),
            ),
        ),

        /* ============================================ SMALLER PAGES */
        'smallpages' => array(
            'label' => 'Thank you &amp; 404',
            'groups' => array(
                array('title' => 'Thank you page', 'blurb' => 'Where the contact form lands, and where the conversion event fires.', 'fields' => array(
                    array('p' => 'pages.thanks.title', 'l' => 'Browser title', 't' => 'text'),
                    array('p' => 'pages.thanks.description', 'l' => 'Meta description', 't' => 'textarea'),
                    array('p' => 'pages.thanks.h1', 'l' => 'Headline', 't' => 'rich'),
                    array('p' => 'pages.thanks.lead', 'l' => 'Lead paragraph', 't' => 'richtext'),
                    array('p' => 'pages.thanks.next_heading', 'l' => 'Second heading', 't' => 'rich', 'w' => 'half'),
                    array('p' => 'pages.thanks.links', 'l' => 'Onward links', 't' => 'list', 'add' => 'Add a link', 'item' => array(
                        array('p' => 'label', 'l' => 'Label', 't' => 'rich', 'w' => 'half'),
                        array('p' => 'href', 'l' => 'Link', 't' => 'text', 'w' => 'half'),
                    )),
                )),
                array('title' => '404 page', 'fields' => array(
                    array('p' => 'pages.notfound.title', 'l' => 'Browser title', 't' => 'text'),
                    array('p' => 'pages.notfound.description', 'l' => 'Meta description', 't' => 'textarea'),
                    array('p' => 'pages.notfound.code', 'l' => 'Big number', 't' => 'text', 'w' => 'half'),
                    array('p' => 'pages.notfound.h1', 'l' => 'Headline', 't' => 'rich'),
                    array('p' => 'pages.notfound.lead', 'l' => 'Lead paragraph', 't' => 'richtext'),
                    array('p' => 'pages.notfound.primary', 'l' => 'Primary button', 't' => 'rich', 'w' => 'half'),
                    array('p' => 'pages.notfound.secondary', 'l' => 'Secondary button', 't' => 'rich', 'w' => 'half'),
                )),
            ),
        ),

        /* ==================================================== LEGAL */
        'legal' => array(
            'label' => 'Privacy &amp; terms',
            'blurb' => 'Write __TAGS__ anywhere in the privacy text and it is replaced with a live list of whichever analytics tags are switched on.',
            'groups' => array(
                array('title' => 'Privacy policy', 'fields' => array(
                    array('p' => 'pages.privacy.title', 'l' => 'Browser title', 't' => 'text', 'w' => 'half'),
                    array('p' => 'pages.privacy.updated', 'l' => 'Last updated', 't' => 'text', 'w' => 'half', 'h' => 'Leave blank to show the build date.'),
                    array('p' => 'pages.privacy.description', 'l' => 'Meta description', 't' => 'textarea'),
                    array('p' => 'pages.privacy.h1', 'l' => 'Headline', 't' => 'rich', 'w' => 'half'),
                    array('p' => 'pages.privacy.lead', 'l' => 'Lead paragraph', 't' => 'richtext'),
                    array('p' => 'pages.privacy.sections', 'l' => 'Sections', 't' => 'list', 'add' => 'Add a section', 'item' => array(
                        array('p' => 'h', 'l' => 'Heading', 't' => 'rich'),
                        array('p' => 'p', 'l' => 'Paragraphs', 't' => 'list', 'simple' => true, 'rich' => true, 'add' => 'Add a paragraph'),
                    )),
                )),
                array('title' => 'Terms', 'fields' => array(
                    array('p' => 'pages.terms.title', 'l' => 'Browser title', 't' => 'text', 'w' => 'half'),
                    array('p' => 'pages.terms.updated', 'l' => 'Last updated', 't' => 'text', 'w' => 'half'),
                    array('p' => 'pages.terms.description', 'l' => 'Meta description', 't' => 'textarea'),
                    array('p' => 'pages.terms.h1', 'l' => 'Headline', 't' => 'rich', 'w' => 'half'),
                    array('p' => 'pages.terms.lead', 'l' => 'Lead paragraph', 't' => 'richtext'),
                    array('p' => 'pages.terms.sections', 'l' => 'Sections', 't' => 'list', 'add' => 'Add a section', 'item' => array(
                        array('p' => 'h', 'l' => 'Heading', 't' => 'rich'),
                        array('p' => 'p', 'l' => 'Paragraphs', 't' => 'list', 'simple' => true, 'rich' => true, 'add' => 'Add a paragraph'),
                    )),
                )),
            ),
        ),

        /* ============================================= INTEGRATIONS */
        'integrations' => array(
            'label' => 'Integrations',
            'blurb' => 'Measurement, where the contact form delivers, and the optional scheduler.',
            'groups' => array(
                array('title' => 'Measurement', 'blurb' => 'Leave a field blank and its tag is never written into the page.', 'fields' => array(
                    array('p' => 'integrations.ga4_id', 'l' => 'Google Analytics 4 ID', 't' => 'text', 'w' => 'half', 'h' => 'Looks like G-XXXXXXXXXX'),
                    array('p' => 'integrations.google_ads_id', 'l' => 'Google Ads ID', 't' => 'text', 'w' => 'half', 'h' => 'Looks like AW-000000000'),
                    array('p' => 'integrations.meta_pixel_id', 'l' => 'Meta Pixel ID', 't' => 'text', 'w' => 'half', 'h' => 'A long number. Worth installing before you need the audience, because warming one takes time.'),
                    array('p' => 'integrations.clarity_id', 'l' => 'Microsoft Clarity ID', 't' => 'text', 'w' => 'half', 'h' => 'Free session recording. Ten real sessions will teach you more than any dashboard at this traffic level.'),
                    array('p' => 'integrations.consent_banner', 'l' => 'Show a cookie banner', 't' => 'toggle', 'h' => 'Off is right for a United States audience. Turn it on before advertising into the EU or UK.'),
                    array('p' => 'integrations.consent_text', 'l' => 'Banner text', 't' => 'richtext'),
                )),
                array('title' => 'Contact form delivery', 'fields' => array(
                    array('p' => 'integrations.form_mode', 'l' => 'Where the form posts', 't' => 'select', 'w' => 'half', 'o' => array(
                        'builtin' => 'This server (saves to the inbox and emails you)',
                        'external' => 'An external endpoint',
                    )),
                    array('p' => 'integrations.form_endpoint', 'l' => 'External endpoint URL', 't' => 'url', 'w' => 'half', 'h' => 'Only used when the option above is set to external.'),
                    array('p' => 'integrations.lead_emails', 'l' => 'Notify these addresses', 't' => 'text', 'h' => 'Comma separated. Both founders, since the page promises exactly that.'),
                    array('p' => 'integrations.lead_subject', 'l' => 'Notification subject', 't' => 'text'),
                )),
                array('title' => 'Scheduler', 'blurb' => 'Paste a Cal.com or Calendly link and the contact page grows a calendar under the form. The frame only loads when somebody asks for it.', 'fields' => array(
                    array('p' => 'integrations.cal_url', 'l' => 'Booking link', 't' => 'url'),
                    array('p' => 'integrations.cal_label', 'l' => 'Section heading', 't' => 'rich'),
                )),
            ),
        ),

        /* ================================================= ADVANCED */
        'advanced' => array(
            'label' => 'Advanced',
            'blurb' => 'Escape hatches. Everything here is written into every page, so a mistake shows up site-wide.',
            'groups' => array(
                array('title' => 'Custom CSS', 'fields' => array(
                    array('p' => 'custom.css', 'l' => 'Extra CSS', 't' => 'code', 'h' => 'Written to assets/site.css and loaded last, so it overrides the design system. @import is stripped.'),
                )),
                array('title' => 'Custom HTML', 'blurb' => 'Raw markup, inserted as typed. Only paste code from somewhere you trust.', 'fields' => array(
                    array('p' => 'custom.head_html', 'l' => 'Before &lt;/head&gt;', 't' => 'code', 'h' => 'Verification meta tags, extra fonts, a third-party tag we have not built a field for.'),
                    array('p' => 'custom.body_html', 'l' => 'Before &lt;/body&gt;', 't' => 'code', 'h' => 'Chat widgets and anything else that belongs at the end of the document.'),
                )),
            ),
        ),
    );
}

/** The four fields every page shares for search and social. */
function gns_schema_seo($page)
{
    return array(
        array('p' => 'pages.' . $page . '.title', 'l' => 'Browser title', 't' => 'text', 'h' => 'This does most of the ranking work. Aim for under about 60 characters before the studio name.'),
        array('p' => 'pages.' . $page . '.description', 'l' => 'Meta description', 't' => 'textarea', 'h' => 'Roughly 150 to 160 characters. It is the sales line under the search result.'),
        array('p' => 'pages.' . $page . '.og_title', 'l' => 'Social card title', 't' => 'text', 'w' => 'half'),
        array('p' => 'pages.' . $page . '.og_image', 'l' => 'Social card image', 't' => 'image', 'w' => 'half', 'h' => 'Leave blank to use the site default.'),
    );
}

/** The four fields in every closing band. */
function gns_schema_closing($page)
{
    return array(
        array('p' => 'pages.' . $page . '.closing.h2', 'l' => 'Heading', 't' => 'rich'),
        array('p' => 'pages.' . $page . '.closing.lead', 'l' => 'Lead paragraph', 't' => 'richtext'),
        array('p' => 'pages.' . $page . '.closing.label', 'l' => 'Button label', 't' => 'rich', 'w' => 'half'),
        array('p' => 'pages.' . $page . '.closing.href', 'l' => 'Button link', 't' => 'text', 'w' => 'half'),
    );
}

/** Flatten one screen's fields, so a save can be validated path by path. */
function gns_schema_fields($screenKey)
{
    $schema = gns_schema();
    if (!isset($schema[$screenKey])) {
        return array();
    }
    $out = array();
    foreach ($schema[$screenKey]['groups'] as $group) {
        foreach ($group['fields'] as $field) {
            $out[$field['p']] = $field;
        }
    }
    return $out;
}

/** Coerce one submitted value into the shape its field declares. */
function gns_coerce($field, $value)
{
    $type = $field['t'];
    if ($type === 'number') {
        return is_numeric($value) ? (int)$value : 0;
    }
    if ($type === 'toggle') {
        return !empty($value) && $value !== '0' && $value !== 'false';
    }
    if ($type === 'select') {
        $options = isset($field['o']) ? $field['o'] : array();
        return array_key_exists((string)$value, $options) ? (string)$value : (string)key($options);
    }
    if ($type === 'url') {
        $value = trim((string)$value);
        if ($value === '') {
            return '';
        }
        return gns_safe_url($value) ? $value : '';
    }
    if ($type === 'email') {
        $value = trim((string)$value);
        return ($value === '' || filter_var($value, FILTER_VALIDATE_EMAIL)) ? $value : '';
    }
    if ($type === 'color') {
        $value = trim((string)$value);
        return preg_match('/^#[0-9a-fA-F]{3,8}$/', $value) ? $value : '#00081a';
    }
    if ($type === 'code') {
        // Kept verbatim: this is the deliberate escape hatch, and it is only
        // reachable by someone already logged into the admin.
        return (string)$value;
    }
    // text, textarea, rich, richtext, image: stored as typed, escaped or
    // allowlisted at render time rather than mangled on the way in.
    return trim((string)$value);
}
