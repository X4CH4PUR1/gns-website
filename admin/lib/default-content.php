<?php
/**
 * GN SCALES — DEFAULT CONTENT
 *
 * The shipped copy for every page. data/content.php is layered on top of this,
 * so anything edited in the admin wins and anything added here in a later
 * release appears with a sensible default instead of a blank.
 *
 * Fields marked "rich" accept a small inline HTML allowlist (a, br, em, strong,
 * span with an approved class). Everything else is escaped on output.
 */

return array(

    /* =============================================================
       SITE — identity, contact, navigation, footer
       ============================================================= */
    'site' => array(
        'name'        => 'GN Scales',
        'legal_name'  => 'GN Scales',
        'url'         => 'https://gnscales.com',
        'email'       => 'hello@gnscales.com',
        'phone'       => '',
        'tagline'     => 'Design-led performance marketing',
        'blurb'       => 'Design-led performance marketing for automotive aftermarket and jewelry brands.',
        'location'    => 'Serving U.S. brands · Remote-first',
        'postal'      => '',
        'founded'     => '2026',
        'price_range' => '$1,500-$2,500/month',
        'og_image'    => '/assets/og/og-default.jpg',
        'twitter'     => '',
        'theme_color' => '#00081a',

        // One pair of numbers instead of the same sentence hardcoded on five
        // pages. Leave taken at 0 until clients are actually signed: the copy
        // reads "Taking 10 founding partners" and claims nothing untrue.
        'spots_total' => 10,
        'spots_taken' => 0,

        'nav' => array(
            array('label' => 'Home',     'href' => '/'),
            array('label' => 'Services', 'href' => 'services.html'),
            array('label' => 'Pricing',  'href' => 'pricing.html'),
            array('label' => 'Work',     'href' => 'work.html'),
            array('label' => 'Studio',   'href' => 'about.html'),
            array('label' => 'Contact',  'href' => 'contact.html'),
        ),

        'cta' => array(
            'header_label' => 'Book a call',
            'label'        => 'Book a strategy call',
            'href'         => 'contact.html',
        ),

        'social' => array(
            'linkedin'  => '',
            'instagram' => '',
        ),

        'footer' => array(
            'pages_heading' => 'Pages',
            'caps_heading'  => 'Capabilities',
            'touch_heading' => 'Get in touch',
            'caps' => array(
                array('label' => 'Meta & Instagram', 'href' => 'services.html#meta'),
                array('label' => 'Google Search',        'href' => 'services.html#google'),
                array('label' => 'TikTok',               'href' => 'services.html#more'),
                array('label' => 'Landing pages',        'href' => 'services.html#pages'),
            ),
            'cta_label' => 'Book a strategy call',
            'note'      => 'Ad spend is paid by clients directly to ad platforms — never routed or marked up by GN Scales.',
        ),
    ),

    /* =============================================================
       INTEGRATIONS — analytics, form delivery, scheduling
       ============================================================= */
    'integrations' => array(
        // Leave any ID blank and its tag is simply not rendered.
        'ga4_id'         => '',
        'meta_pixel_id'  => '',
        'google_ads_id'  => '',
        'clarity_id'     => '',

        // 'builtin' posts to submit.php, which stores the lead in the admin
        // inbox and emails it. 'external' posts straight to form_endpoint
        // (a Formspree form URL, for instance).
        'form_mode'      => 'builtin',
        'form_endpoint'  => '',
        'lead_emails'    => 'hello@gnscales.com',
        'lead_subject'   => 'New enquiry from gnscales.com',

        // Paste a Cal.com or Calendly link and the contact page grows a
        // scheduler alongside the form.
        'cal_url'        => '',
        'cal_label'      => 'Or pick a time directly',

        // Off by default: the site targets the United States, where a consent
        // wall costs measurement without adding a legal requirement. Turn it on
        // before advertising into the EU or UK.
        'consent_banner' => false,
        'consent_text'   => 'We use cookies to measure how this site is used. Nothing is sold or shared.',
    ),

    /* =============================================================
       FOUNDERS
       ============================================================= */
    'founders' => array(
        array(
            'name'      => 'Nick Lomidze',
            'initials'  => 'NL',
            'role_home' => 'Co-founder — Creative',
            'role_full' => 'Co-founder — Creative & brand',
            'bio_home'  => 'Came out of industrial and automotive design. Sets the creative and brand direction — how a campaign looks and feels before a dollar of media goes out.',
            'bio_full'  => 'Came up through industrial and automotive design. Sets creative and brand direction — how a campaign looks, sounds and feels before a dollar of media goes out the door. If an ad stops working, this is the person who redraws it.',
            'photo'     => '',
            'linkedin'  => '',
            'tone'      => 'warm',
        ),
        array(
            'name'      => 'George Lomidze',
            'initials'  => 'GL',
            'role_home' => 'Co-founder — Media',
            'role_full' => 'Co-founder — Media & performance',
            'bio_home'  => 'Runs media strategy and account performance. Turns design-led creative into campaigns that can actually be measured, argued with and improved.',
            'bio_full'  => 'Runs media strategy and account performance. Turns design-led creative into campaigns that can be measured, argued with and improved — and is the one who will tell you when the number is not moving.',
            'photo'     => '',
            'linkedin'  => '',
            'tone'      => 'cool',
        ),
    ),

    /* =============================================================
       PAGES
       ============================================================= */
    'pages' => array(

        /* ---------------------------------------------------------
           HOME
           --------------------------------------------------------- */
        'home' => array(
            'title'       => 'Design-Led Performance Marketing for Auto Aftermarket & Jewelry | GN Scales',
            'description' => 'GN Scales designs the creative, the landing page and the media engine as one system, then spends against it. Meta, Google and TikTok ads for automotive aftermarket and jewelry brands. Published pricing, no markup on ad spend.',
            'og_title'    => 'GN Scales — Design-Led Performance Marketing',
            'og_image'    => '',

            'hero' => array(
                'flag_dim' => 'Automotive & jewelry',
                'h1'       => 'Craft first.<br><span class="metal">Then the numbers.</span>',
                'sub'      => 'A campaign is only as good as the thing it points at. We design the creative, the landing page and the media engine as one system — then we spend against it.',
                'cta_label' => 'Book a strategy call',
                'cta_href'  => 'contact.html',
                'alt_label' => 'See how we work',
                'alt_href'  => '#process',
                'facts' => array(
                    'No markup on ad spend',
                    'Design and media, one team',
                    'Month to month, no lock-in',
                ),
            ),

            'marquee' => array(
                'Paint protection film', 'Ceramic coating', 'Vehicle wraps',
                'Custom jewelry', 'Engagement rings', 'Automotive aftermarket',
            ),

            'gap' => array(
                'eyebrow' => '01 — The gap',
                'h2'      => 'Most agencies inherit the creative. <span class="metal">We author it.</span>',
                'body'    => array(
                    'A media buyer takes whatever assets a client already has and optimises around them. When performance stalls, the lever they reach for is targeting — because the creative isn’t theirs to change.',
                    'Our founders spent years designing for automotive and industrial B2B, where precision isn’t a style choice. We build the asset and the account together, so the biggest lever in paid media is one we actually control.',
                ),
                'link_label' => 'Read our approach',
                'link_href'  => 'about.html',
                'left_label'  => 'Typical agency',
                'left_steps'  => array(
                    'Client supplies assets',
                    'Build campaign structure',
                    'Launch and optimise',
                    'Creative stalls. Retarget harder.',
                ),
                'right_label' => 'GN Scales',
                'right_steps' => array(
                    'Design the offer and creative',
                    'Build the landing experience',
                    'Launch media against it',
                    'Redesign the lever that stalled',
                ),
            ),

            'capabilities' => array(
                'eyebrow' => '02 — Capabilities',
                'h2'      => 'Six disciplines,<br>one accountable team',
                'lead'    => 'Nothing is subcontracted. The person designing your ad is in the same room as the person spending against it.',
                'items' => array(
                    array(
                        'title'  => 'Meta & Instagram',
                        'body'   => 'Full-funnel campaigns built on creative that earns the stop, not just targeting that finds the eyeball. Cold, warm and retargeting run as one arc.',
                        'visual' => 'funnel', 'size' => 'lg', 'gold' => true,
                    ),
                    array(
                        'title'  => 'Google Search & Shopping',
                        'body'   => 'Demand that already exists, captured at the moment of intent. For a PPF shop that is somebody typing “ceramic coating near me” at 9pm.',
                        'visual' => 'serp', 'size' => 'lg', 'gold' => false,
                    ),
                    array(
                        'title'  => 'TikTok',
                        'body'   => 'Short-form testing at volume, on the one platform where over-polish actively costs you performance.',
                        'visual' => 'tiktok', 'size' => '', 'gold' => false,
                    ),
                    array(
                        'title'  => 'Lifecycle email',
                        'body'   => 'The quote that went cold three weeks ago is cheaper to win back than a new click. We build the sequence that does it.',
                        'visual' => 'sequence', 'size' => '', 'gold' => false,
                    ),
                    array(
                        'title'  => 'Landing pages & websites',
                        'body'   => 'The page has to be as good as the ad that sent them. Usually it is the cheapest performance gain available.',
                        'visual' => 'minipage', 'size' => 'wide', 'gold' => true,
                    ),
                    array(
                        'title'  => 'Full retainer brand management',
                        'body'   => 'Every discipline above, run as one engine, with a single team accountable for the number at the bottom. For brands tired of refereeing between an agency and a freelancer.',
                        'visual' => 'retainer', 'size' => 'full', 'gold' => false,
                    ),
                ),
                'all_label' => 'All services',
                'all_href'  => 'services.html',
            ),

            'process' => array(
                'eyebrow' => '03 — Process',
                'h2'      => 'Ninety days, four movements',
                'steps' => array(
                    array('when' => 'Week 1—2',  'title' => 'Audit & offer',    'body' => 'We pull apart what you sell and how you say it. Half the time the offer is the problem, not the ad account.', 'state' => 'on'),
                    array('when' => 'Week 2—4',  'title' => 'Design the system',    'body' => 'Creative concepts, landing page, tracking. Nothing goes live until the destination is worth sending traffic to.', 'state' => 'on'),
                    array('when' => 'Week 4—8',  'title' => 'Launch & learn',   'body' => 'Structured tests, not scattergun. We are looking for the one angle that outperforms, then we pour into it.', 'state' => ''),
                    array('when' => 'Week 8—12', 'title' => 'Scale the winner',     'body' => 'Budget follows evidence. Creative refreshes on a cadence so the winning angle doesn’t fatigue into the ground.', 'state' => 'off'),
                ),
            ),

            'maths' => array(
                'eyebrow' => '04 — The maths',
                'h2'      => 'Run your own numbers',
                'body'    => 'Most agencies hide the arithmetic until the proposal. Move the sliders and see what a retainer has to beat before it is worth doing — including our fee.',
                'note'    => 'A planning model, not a forecast. It works in gross profit, not revenue, because that is what actually pays for a retainer. Nothing here is a claim about results.',
                'default_spend'  => 6000,
                'default_job'    => 1800,
                'default_close'  => 25,
                'default_margin' => 45,
                'default_tier'   => 'growth',
            ),

            'studio' => array(
                'eyebrow' => '05 — The studio',
                'h2'      => 'Two people. No account managers.',
                'lead'    => 'You will talk to the people doing the work, every time. That is the honest advantage of being small — and the reason we cap the roster at ten.',
            ),

            'craft' => array(
                'eyebrow' => '06 — Craft',
                'h2'      => 'What design actually changes',
                'lead'    => 'Drag the handle. Same offer, same budget, same audience — a different reason to stop.',
                'after_tag'    => 'After — designed',
                'after_kicker' => 'Ceramic coating · 5 year',
                'after_head'   => 'Keep the showroom<br><span class="metal">finish for five years.</span>',
                'after_btn'    => 'Book an inspection',
                'after_meta'   => 'Free · 20 minutes',
                'before_tag'   => 'Before — stock template',
                'before_head'  => 'Best ceramic coating<br>in town!! Call today!',
                'before_sub'   => 'Free quotes. Limited time offer. Financing available. Serving the whole metro area since 2019. Satisfaction guaranteed!',
                'before_btn_a' => 'Call now!!',
                'before_btn_b' => 'Get quote',
                'note'         => 'Both panels are our own illustrative creative, made to show a design contrast. Neither is a client campaign.',
            ),

            'faq' => array(
                'eyebrow' => '07 — Straight answers',
                'h2'      => 'The questions you were going to ask anyway',
                'items' => array(
                    array(
                        'q' => 'Do you have case studies yet?',
                        'a' => 'No. We are building our first client cohort now, and we would rather say that plainly than dress up a planning scenario as a result. What we can show you is the work itself — creative, pages, account structure — on the Work page and on a call.',
                    ),
                    array(
                        'q' => 'Who pays for the ad spend?',
                        'a' => 'You do, directly to Meta, Google or TikTok, from your own card on your own billing. GN Scales never routes, holds or marks up your media budget. Our fees cover our work and nothing else.',
                    ),
                    array(
                        'q' => 'Why only automotive and jewelry?',
                        'a' => 'Because a high-ticket considered purchase behaves nothing like ecommerce, and we would rather be genuinely good at two categories than passable at ten. PPF, coating, wraps and custom jewelry all share the same shape: high job value, long consideration, local intent.',
                    ),
                    array(
                        'q' => 'Am I locked into a contract?',
                        'a' => 'No. Month to month after the setup period. The setup fee covers real work that is delivered to you regardless, and the landing page is yours to keep either way.',
                    ),
                    array(
                        'q' => 'What does a strategy call involve?',
                        'a' => 'Twenty minutes, no deck. You tell us what you sell and what you are spending now; we tell you whether paid media is your next lever, and what we would do first. If the answer is that you shouldn’t be spending yet, we will say so.',
                    ),
                ),
            ),

            'closing' => array(
                'h2'    => 'Let’s find out if this is <span class="metal">a fit.</span>',
                'lead'  => 'Twenty minutes, no deck, no pressure. We will tell you honestly whether paid media is the right lever for you right now — including when the answer is no.',
                'label' => 'Book a strategy call',
                'href'  => 'contact.html',
            ),
        ),

        /* ---------------------------------------------------------
           SERVICES
           --------------------------------------------------------- */
        'services' => array(
            'title'       => 'Meta, Google & TikTok Ads + Landing Pages | GN Scales Services',
            'description' => 'Paid social, paid search, TikTok, lifecycle email and the landing pages they point at — designed and bought by the same two people. Services for automotive aftermarket and jewelry brands.',
            'og_title'    => 'GN Scales Services — Ads, Landing Pages, Lifecycle',

            'intro' => array(
                'eyebrow' => 'Services',
                'h1'      => 'Everything that <span class="metal">touches the sale.</span>',
                'lead'    => 'We do not sell channels à la carte to look busy. These six exist because a paid campaign fails at one of six places, and we wanted to be able to fix all of them.',
                'keyword_h2' => 'Meta, Google and TikTok advertising, plus the landing pages they point at',
            ),

            'sections' => array(
                array(
                    'id'      => 'meta',
                    'index'   => '01',
                    'kicker'  => 'Paid social',
                    'title'   => 'Meta & Instagram',
                    'body'    => array('A cold audience does not care about your business. It cares whether the first two seconds are worth the third. We build the concepts, design or shoot the assets, and structure the account so cold, warm and retargeting behave like one continuous argument rather than three disconnected campaigns.'),
                    'note'    => '',
                    'ticks'   => array(
                        'Concept and creative production in-house',
                        'Structured creative testing, not random variants',
                        'Pixel and conversions API set up properly, once',
                    ),
                    'visual'   => 'funnel',
                    'reversed' => false,
                    'gold'     => true,
                ),
                array(
                    'id'      => 'google',
                    'index'   => '02',
                    'kicker'  => 'Paid search',
                    'title'   => 'Google Search<br>& Shopping',
                    'body'    => array('Paid social creates demand. Search catches it. For a local PPF shop or a jeweller doing custom engagement work, someone typing a high-intent query at 9pm is the single most valuable click available — and usually the one being wasted on a broad-match campaign nobody has read in six months.'),
                    'note'    => '',
                    'ticks'   => array(
                        'Intent-mapped keyword structure',
                        'Negative keyword hygiene, reviewed weekly',
                        'Call tracking and a lead-quality feedback loop',
                    ),
                    'visual'   => 'serp',
                    'reversed' => true,
                    'gold'     => false,
                ),
                array(
                    'id'      => 'pages',
                    'index'   => '03',
                    'kicker'  => 'Conversion',
                    'title'   => 'Landing pages<br>& websites',
                    'body'    => array('The cheapest performance gain most businesses have available is not a better audience — it is a page that does not undo the promise the ad just made. We design and build the destination, not just the click that gets there. This is the part almost no media agency will touch.'),
                    'note'    => 'Built as hand-written static pages, so there is no page-builder to boot, no plugin stack to load and no monthly subscription for the privilege of owning your own page.',
                    'ticks'   => array(),
                    'visual'   => 'browser',
                    'reversed' => false,
                    'gold'     => true,
                ),
            ),

            'more' => array(
                'eyebrow' => 'Also in scope',
                'h2'      => 'TikTok, lifecycle email and the full retainer',
                'lead'    => 'Smaller sections, not smaller commitments. These three are bought and built by the same two people as everything above.',
                'items' => array(
                    array(
                        'title'  => 'TikTok',
                        'body'   => 'Volume testing on the one platform where over-production actively hurts you. A wrap shop filming a real install will beat a polished spot every time — our job is knowing which twelve seconds to keep.',
                        'visual' => 'tiktok',
                    ),
                    array(
                        'title'  => 'Lifecycle email',
                        'body'   => 'Most high-ticket local businesses are sitting on a list of people who asked for a quote and never heard back. Winning one of those costs nothing in media. We build the sequence that does it.',
                        'visual' => 'sequence',
                    ),
                    array(
                        'title'  => 'Full retainer',
                        'body'   => 'All of the above under one team and one number. For brands who are tired of refereeing between a media agency, a designer, and whoever built the site three years ago.',
                        'visual' => 'orbit',
                    ),
                ),
            ),

            'closing' => array(
                'h2'    => 'Not sure which of these you actually need?',
                'lead'  => 'That is the point of the call. We will tell you which one moves your number first — and which ones are not worth your money yet.',
                'label' => 'Book a strategy call',
                'href'  => 'contact.html',
            ),
        ),

        /* ---------------------------------------------------------
           PRICING
           --------------------------------------------------------- */
        'pricing' => array(
            'title'       => 'Agency Pricing: $1,500–$2,500/mo, Published | GN Scales',
            'description' => 'Two retainer tiers, published: Launch at $1,500/month and Growth at $2,500/month, month to month. Your ad spend is paid directly to the platforms and never marked up.',
            'og_title'    => 'GN Scales Pricing — Two tiers. Published.',
            // Its own card: pricing links get pasted into internal threads at
            // prospect companies more often than home pages do.
            'og_image'    => '/assets/og/og-pricing.jpg',

            'intro' => array(
                'eyebrow' => 'Pricing',
                'h1'      => 'Two tiers. <span class="metal">No games.</span>',
                'lead'    => 'Published, because you should not have to sit through a discovery call to find out whether we are in your range. Month to month — if we are not earning it, leave.',
                'keyword_h2' => 'Marketing agency retainer pricing for automotive and jewelry brands',
            ),

            'tiers' => array(
                array(
                    'key'      => 'launch',
                    'name'     => 'Launch',
                    'for'      => 'For one channel, done properly',
                    // Factual, not invented social proof, and it agrees with the
                    // FAQ below rather than contradicting it.
                    'flag'     => 'Best place to start',
                    'price'    => 1500,
                    'period'   => '/ month',
                    'setup'    => 'plus a one-time $1,500 setup',
                    'featured' => false,
                    'features' => array(
                        'One ad platform — Meta, Google, or TikTok',
                        'One designed and built landing page',
                        'Ongoing performance reporting',
                        'Direct access to both founders',
                    ),
                    'cta_label' => 'Book a strategy call',
                    'cta_href'  => 'contact.html',
                ),
                array(
                    'key'      => 'growth',
                    'name'     => 'Growth',
                    'for'      => 'The full engine',
                    'flag'     => 'Most complete',
                    'price'    => 2500,
                    'period'   => '/ month',
                    'setup'    => 'plus a one-time $2,000 setup',
                    'featured' => true,
                    'features' => array(
                        'Two ad platforms of your choice — Meta, Google or TikTok — run as one funnel',
                        'Ongoing creative refresh so ads don’t fatigue',
                        'Continued landing page iteration',
                        'Regular strategy check-ins',
                        'Everything in Launch',
                    ),
                    'cta_label' => 'Book a strategy call',
                    'cta_href'  => 'contact.html',
                ),
            ),

            'parity'     => 'The tiers differ in scope of work, never in how often you hear from us. Every client gets the same access.',
            'spend_h2'   => 'Your ad spend never touches our account',
            'spend_body' => 'You pay Meta, Google or TikTok directly, from your own card, on your own billing. GN Scales never routes, holds, or marks up your media budget — the fees above cover our work and nothing else. If an agency will not put that in writing, ask why.',

            'setup' => array(
                'eyebrow' => 'The setup fee',
                'h2'      => 'What you are actually paying for up front',
                'lead'    => 'Roughly three to four weeks of work before a single ad goes live. Most of the outcome is decided here.',
                'steps' => array(
                    array('title' => 'Offer & audit',  'body' => 'What you sell, who buys it, and why the current version is not converting.'),
                    array('title' => 'Creative build',     'body' => 'Concepts, art direction, and the first production round of assets.'),
                    array('title' => 'Landing page',       'body' => 'Designed and built from scratch. Yours to keep, whatever happens next.'),
                    array('title' => 'Tracking',           'body' => 'Pixel, conversions API, call tracking. Done once, done right, in your accounts.'),
                ),
            ),

            'faq' => array(
                'eyebrow' => 'Questions',
                'h2'      => 'Before you ask',
                'items' => array(
                    array(
                        'q' => 'Am I locked into a contract?',
                        'a' => 'No. Month to month after the setup period. The setup fee covers real work that is delivered to you regardless, and the landing page is yours to keep either way.',
                    ),
                    array(
                        'q' => 'What ad budget do I need on top?',
                        'a' => 'That depends entirely on your job value and margin, which is why we would rather work it out with you than publish a number. Use the break-even model on the home page as a starting point, then bring your real figures to the call.',
                    ),
                    array(
                        'q' => 'Can I start on Launch and move up?',
                        'a' => 'Yes, and most people should — which is why Launch carries the “best place to start” flag. Proving one channel works before adding a second is cheaper than debugging two at once. Moving up costs the difference in monthly fee; there is no second setup charge.',
                    ),
                    array(
                        'q' => 'Which two platforms does Growth cover?',
                        'a' => 'Any two of Meta, Google and TikTok — your choice, decided on the setup call against where your buyers actually are. Adding a third is a scope conversation, not a surprise invoice.',
                    ),
                    array(
                        'q' => 'Do you work outside automotive and jewelry?',
                        'a' => 'Occasionally, if the shape of the business matches: high job value, a considered purchase, and local or regional intent. If you are ecommerce-only, we are honestly not the right studio and will tell you so.',
                    ),
                    array(
                        'q' => 'Do you have case studies yet?',
                        'a' => 'No. We are building our first client cohort now and we will not dress a planning scenario up as a result. What we can show you is the work itself — creative, pages, account structure — on the Work page and on a call.',
                    ),
                ),
            ),

            'closing' => array(
                'h2'    => 'Still cheaper than one <span class="metal">bad quarter.</span>',
                'lead'  => 'Bring your numbers to the call and we will work out together whether this is worth doing. If it is not, we will say so.',
                'label' => 'Book a strategy call',
                'href'  => 'contact.html',
            ),
        ),

        /* ---------------------------------------------------------
           STUDIO / ABOUT
           --------------------------------------------------------- */
        'about' => array(
            'title'       => 'About GN Scales — Two Designers Who Buy Media',
            'description' => 'GN Scales is two founders: one on creative and brand, one on media and performance. No account managers, no subcontractors, and a roster capped at ten so the work stays good.',
            'og_title'    => 'The GN Scales studio — two people, no account managers',

            'intro' => array(
                'eyebrow' => 'The studio',
                'h1'      => 'Two designers who learned to <span class="metal">buy media.</span>',
                'lead'    => 'Not two media buyers who hired a designer. The order matters more than it sounds — it changes what we reach for first when a campaign is not working.',
            ),

            'story' => array(
                'eyebrow' => '01 — Origin',
                'h2'      => 'Why the studio exists',
                'lede'    => 'GN Scales started with an observation neither of us could unsee: most performance agencies are excellent at buying media and mediocre at design — and most design studios will not go near a live ad account.',
                'body' => array(
                    'We spent years designing for automotive and industrial B2B companies. That is work where a millimetre matters, where the client knows more about their product than you ever will, and where “make it pop” is not an accepted brief. It teaches you to sell complexity clearly — which turns out to be exactly the skill a performance campaign needs and rarely has.',
                    'So we built the thing that was missing: one team that designs the asset and spends against it. When the creative underperforms, we do not file a ticket with a freelancer and wait a week. We redraw it.',
                ),
                'note' => 'We are early. We are building toward our first ten clients and choosing them deliberately rather than taking everyone who can pay — partly out of principle, mostly because two people can only do this properly for ten businesses at once.',
            ),

            'founders' => array(
                'eyebrow' => '02 — Founders',
                'h2'      => 'The whole company',
                'lead'    => 'There is no account team, no junior buyer, and nobody between you and the work. This is it.',
            ),

            'principles' => array(
                'eyebrow' => '03 — How we work',
                'h2'      => 'Four things we will not bend on',
                'items' => array(
                    array('title' => 'Your money stays yours',     'body' => 'Ad spend goes from your card to the platform. We never route it, hold it, or take a percentage of it. Our incentive is your result, not your budget size.'),
                    array('title' => 'You own everything we make', 'body' => 'Landing pages, creative, ad accounts, tracking. All built in your accounts and handed over in full. Leaving should cost you nothing but the notice period.'),
                    array('title' => 'We say no when it is a no',   'body' => 'If your margin cannot carry paid media yet, or the problem is operations rather than demand, we will tell you on the first call instead of selling you a retainer.'),
                    array('title' => 'No invented proof',           'body' => 'We have no case studies yet and we will not dress a planning scenario up as one. When we have real results, you will see real numbers with the client’s name on them.'),
                ),
            ),

            'closing' => array(
                'h2'    => 'Come and be one of <span class="metal">the first ten.</span>',
                'lead'  => 'Early clients get disproportionate attention, and we are honest about why: we need the work to be good more than you need us to be busy.',
                'label' => 'Book a strategy call',
                'href'  => 'contact.html',
            ),
        ),

        /* ---------------------------------------------------------
           WORK
           --------------------------------------------------------- */
        'work' => array(
            'title'       => 'Selected Work — Design for Automotive & Jewelry Brands | GN Scales',
            'description' => 'Craft, not case studies. Design work and campaign systems from GN Scales — clearly labelled as our own, with no invented results attached.',
            'og_title'    => 'GN Scales — Selected work',

            'intro' => array(
                'eyebrow' => 'Selected work',
                'h1'      => 'Craft, not <span class="metal">case studies.</span>',
                'lead'    => 'We have no client results to publish yet and we will not invent any. What we can show you is the work itself: the design decisions, the systems, and the standard everything is held to.',
            ),

            'honesty' => 'Everything on this page is our own work, made by us. Where a piece is illustrative rather than a live campaign, it says so on the piece. When we have real client results, they will appear here with real numbers and the client’s name on them.',

            'craft' => array(
                'eyebrow' => '01 — Studio pieces',
                'h2'      => 'The standard, shown rather than described',
                'lead'    => 'Built for this site, in the same way we would build them for you. Illustrative — not client campaigns.',
                'items' => array(
                    array(
                        'kind'    => 'ad',
                        'title'   => 'Ceramic coating — paid social concept',
                        'problem' => 'A five-year coating is a considered purchase sold almost everywhere with exclamation marks and a phone number. The brief was to make the offer feel like the finish it protects.',
                        'did'     => 'One promise, one action, and a price of entry (twenty minutes, free) low enough to be worth clicking. Type does the work; nothing shouts.',
                        'image'   => '',
                        'label'   => 'Illustrative concept',
                    ),
                    array(
                        'kind'    => 'page',
                        'title'   => 'Landing page — installer booking flow',
                        'problem' => 'Most shops send paid traffic to a homepage that asks a visitor to work out, on their own, which of nine services they wanted.',
                        'did'     => 'A single-purpose page that repeats the ad’s promise above the fold, answers the three objections underneath it, and offers one action. Hand-built static — nothing to boot, nothing to subscribe to.',
                        'image'   => '',
                        'label'   => 'Illustrative concept',
                    ),
                    array(
                        'kind'    => 'search',
                        'title'   => 'Paid search — intent-mapped result',
                        'problem' => 'High-intent local queries routed to broad-match campaigns nobody has read in six months, landing on a generic page.',
                        'did'     => 'Query, ad and destination written as one sentence, so what someone typed at 9pm is what they read, and then what they land on.',
                        'image'   => '',
                        'label'   => 'Illustrative concept',
                    ),
                ),
            ),

            'clients' => array(
                'eyebrow' => '02 — Client work',
                'h2'      => 'Brands we have designed for',
                'lead'    => 'Design and brand work from before GN Scales, published with permission.',
                // Empty by default: add entries in the admin once each client has
                // confirmed they are happy to be named. The section hides itself
                // while this list is empty.
                'items' => array(),
            ),

            'closing' => array(
                'h2'    => 'Want to see the rest of it?',
                'lead'  => 'The account structures, the creative that did not make this page, and the reasoning behind both — we will walk you through it live rather than package it into a deck.',
                'label' => 'Book a strategy call',
                'href'  => 'contact.html',
            ),
        ),

        /* ---------------------------------------------------------
           CONTACT
           --------------------------------------------------------- */
        'contact' => array(
            'title'       => 'Book a 20-Minute Strategy Call | GN Scales',
            'description' => 'Tell us what you sell and what you are spending now. Twenty minutes, no deck, and an honest answer about whether paid media is your next lever.',
            'og_title'    => 'Book a 20-minute strategy call with GN Scales',

            'intro' => array(
                'eyebrow' => 'Contact',
                'h1'      => 'Twenty minutes. <span class="metal">No deck.</span>',
                'lead'    => 'Tell us what you sell and what you are spending now. We will tell you whether paid media is your next lever — including when it plainly is not.',
            ),

            'steps' => array(
                array('title' => 'You send this form',                'desc' => 'Both founders read it. Not a shared inbox, not a bot.'),
                array('title' => 'We reply within one business day',  'desc' => 'With a time, or with an honest note about why we are not the right fit.'),
                array('title' => 'We talk through your numbers',      'desc' => 'No slide deck, no pressure, no follow-up sequence afterwards.'),
            ),

            'direct_label' => 'Prefer email',

            'form' => array(
                'heading'     => 'Start here',
                'time'        => '2 minutes',
                'submit'      => 'Send this to both founders',
                'submit_cal'  => 'Send and pick a time',
                'privacy'     => 'We do not sell, share, or add you to a list.',
                'privacy_link'=> 'privacy.html',
                'sector_legend' => 'What do you do?',
                'sector_options' => array(
                    array('value' => 'ppf',     'label' => 'PPF / coating'),
                    array('value' => 'wraps',   'label' => 'Vehicle wraps'),
                    array('value' => 'jewelry', 'label' => 'Jewelry'),
                    array('value' => 'other',   'label' => 'Something else'),
                ),
                'spend_legend' => 'Current monthly ad spend',
                'spend_options' => array(
                    array('value' => 'none',  'label' => 'Not spending yet'),
                    array('value' => '1-5k',  'label' => '$1—5k'),
                    array('value' => '5-15k', 'label' => '$5—15k'),
                    array('value' => '15k+',  'label' => '$15k+'),
                ),
                'message_label'       => 'What are you trying to fix?',
                'message_placeholder' => 'We get plenty of enquiries but they go cold after the quote…',
            ),
        ),

        /* ---------------------------------------------------------
           THANK YOU
           --------------------------------------------------------- */
        'thanks' => array(
            'title'       => 'Thank you — GN Scales',
            'description' => 'Your enquiry reached both founders. We reply within one business day.',
            'h1'          => 'That reached <span class="metal">both of us.</span>',
            'lead'        => 'No autoresponder sequence is about to start. One of us will read it properly and reply within one business day — with a time, or with an honest note about why we are not the right fit.',
            'next_heading'=> 'While you wait',
            'links' => array(
                array('label' => 'See how we price it', 'href' => 'pricing.html'),
                array('label' => 'Look at the work',    'href' => 'work.html'),
                array('label' => 'Read the approach',   'href' => 'about.html'),
            ),
        ),

        /* ---------------------------------------------------------
           404
           --------------------------------------------------------- */
        'notfound' => array(
            'title'       => 'Page not found — GN Scales',
            'description' => 'That page does not exist. Here is the way back.',
            'code'        => '404',
            'h1'          => 'That page <span class="metal">isn’t here.</span>',
            'lead'        => 'The link may be old, or we may have moved something. Neither is your problem — here is the way back.',
            'primary'     => 'Go to the home page',
            'secondary'   => 'Book a strategy call',
        ),

        /* ---------------------------------------------------------
           PRIVACY
           --------------------------------------------------------- */
        'privacy' => array(
            'title'       => 'Privacy Policy — GN Scales',
            'description' => 'What GN Scales collects, why, how long we keep it, and how to have it deleted.',
            'h1'          => 'Privacy',
            'lead'        => 'Short version: we collect what you send us on the contact form, we use it to reply to you, and we do not sell it or add you to a list. The long version is below.',
            'updated'     => '',
            'sections' => array(
                array(
                    'h'  => 'Who we are',
                    'p'  => array('GN Scales is a two-person design and performance marketing studio. We are the data controller for this website. You can reach us at the email address at the bottom of this page.'),
                ),
                array(
                    'h'  => 'What we collect',
                    'p'  => array('<strong>What you type into the contact form.</strong> Your name, business name, email address, the sector and ad spend range you select, and whatever you write in the message box.',
                                  '<strong>Basic technical information.</strong> Our web host records standard server logs: IP address, browser user agent, the page requested and the time. That is a function of the hosting, not something we set up to track you.',
                                  '<strong>Analytics, if enabled.</strong> If a measurement tag is running on this site, it is listed in the “Analytics and advertising” section below. If that section says none are running, none are running.'),
                ),
                array(
                    'h'  => 'Why we collect it',
                    'p'  => array('To reply to your enquiry, to work out whether we are a sensible fit for each other, and to understand at a coarse level which parts of this site are useful. Nothing else.'),
                ),
                array(
                    'h'  => 'What we never do',
                    'p'  => array('We do not sell your information. We do not share it with data brokers. We do not add you to a mailing list because you filled in a contact form — if you have not asked for email from us, you will not get any beyond a reply to what you sent.'),
                ),
                array(
                    'h'  => 'Who else touches it',
                    'p'  => array('Our web host stores the site and its server logs. If a scheduling link is offered on the contact page, the scheduling provider receives the details you enter into it. If analytics or advertising tags are running, the providers named below receive the data those tags collect. Each of these is a processor acting on our instructions; none of them buys the data from us.'),
                ),
                array(
                    'h'  => 'Analytics and advertising',
                    'p'  => array('__TAGS__'),
                ),
                array(
                    'h'  => 'How long we keep it',
                    'p'  => array('Enquiries are kept for two years so we can pick up a conversation that went quiet, then deleted. Server logs are kept for whatever period our host retains them, typically a few weeks. Ask us to delete your enquiry sooner and we will.'),
                ),
                array(
                    'h'  => 'Your choices',
                    'p'  => array('Email us and we will tell you what we hold about you, correct it, or delete it. You do not need to give a reason and there is no form to fill in. If you are in a jurisdiction with statutory data rights — California, the EU, the UK — those rights apply and this is how you exercise them.'),
                ),
                array(
                    'h'  => 'Cookies',
                    'p'  => array('This site sets no cookies of its own for visitors. If analytics or advertising tags are enabled they set their own; the list above tells you which. The admin area sets one session cookie, which only affects us.'),
                ),
                array(
                    'h'  => 'Changes',
                    'p'  => array('If this policy changes materially we will change the date at the top of the page. We are not going to notify you of a typo fix.'),
                ),
            ),
        ),

        /* ---------------------------------------------------------
           TERMS
           --------------------------------------------------------- */
        'terms' => array(
            'title'       => 'Terms — GN Scales',
            'description' => 'The terms that apply to this website, and a plain summary of how our engagements work.',
            'h1'          => 'Terms',
            'lead'        => 'These cover the website. The terms of an actual engagement live in the agreement we sign with you, and that agreement wins wherever the two differ.',
            'updated'     => '',
            'sections' => array(
                array(
                    'h' => 'Using this site',
                    'p' => array('You are welcome to read it, quote it, and link to it. The copy, design, code and marks on this site are ours; please do not republish them as your own.'),
                ),
                array(
                    'h' => 'What the figures mean',
                    'p' => array('The retainer prices on the pricing page are current and real. The break-even model on the home page is a planning tool that works from numbers you type in: it is arithmetic, not a forecast, and it is not a promise of any result.',
                                 'Every visual on this site that resembles an advertisement, a search result or a landing page is our own illustrative work, labelled as such. None of it is a client campaign and none of it carries implied results.'),
                ),
                array(
                    'h' => 'Ad spend',
                    'p' => array('Media budgets are paid by the client directly to the advertising platform, on the client’s own billing. We never route, hold or mark up media spend. Our fees cover our work only.'),
                ),
                array(
                    'h' => 'Engagements',
                    'p' => array('Retainers run month to month after the setup period. Work produced during setup is delivered to the client regardless of what happens afterwards, and landing pages are the client’s to keep. Anything more specific than that belongs in the signed agreement, not on a web page.'),
                ),
                array(
                    'h' => 'Liability',
                    'p' => array('This site is provided as is. We keep it accurate and we correct mistakes when we find them, but we are not liable for decisions taken on the basis of a web page. Decisions about your money should be taken on the basis of a conversation and a written agreement.'),
                ),
            ),
        ),
    ),

    /* =============================================================
       CUSTOM — escape hatches, applied on every page
       ============================================================= */
    'custom' => array(
        'css'       => '',
        'head_html' => '',
        'body_html' => '',
    ),
);
