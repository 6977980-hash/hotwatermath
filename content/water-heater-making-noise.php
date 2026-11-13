<?php
/**
 * /water-heater-making-noise/ (STRATEGY §2 item 20, Phase 6b batch 2): symptom article, sound by sound, with the
 * repair-or-replace tool embedded (noise pre-ticked). Causes restate Rheem's noise guide (June 2026), A. O. Smith's
 * two noise pages and its gas Use & Care Guide; heat pump sound level from data/heat-pump.json (ENERGY STAR).
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
$hwm_f   = 'troubleshooting';
$hwm_t   = static fn( string $path ): string => esc_html( hwm_value( $hwm_f, $path ) );
$hwm_r   = static fn( string $key ): string => (string) hwm_raw( $hwm_f, 'references.' . $key . '.sourceUrl' );
$hwm_ext = static fn( string $url, string $text ): string => '<a href="' . esc_url( $url ) . '" rel="noopener">' . esc_html( $text ) . '</a>';
$hwm_pct = (array) hwm_raw( 'computed/repair-or-replace-water-heater', 'rulesPct' );

$related = array( 'how-to-flush-a-water-heater', 'repair-or-replace-water-heater', 'water-heater-age-calculator', 'water-heater-leaking', 'water-heater-anode-rod' );
hwm_part( 'safety-notice', array( 'gas' => true, 'power' => true, 'scald' => true ) );
?>
<h2>Is it normal for a water heater to make noise?</h2>
<p>Some noise, yes. Rheem says a water heater heats, cools and cycles through the day, and quiet, brief sounds at regular intervals are generally nothing to worry about: ticking, a low hum on an electric model, gurgling after heavy use, and a soft sound when a gas burner lights. Loud, new or constant sounds are the ones that mean something.</p>
<p>Find your sound in the table, then read its section below.</p>
<?php echo hwm_table( $hwm_f, 'noiseSounds' ); // phpcs:ignore -- escaped in the part. ?>
<figure class="figure">
	<svg viewBox="0 0 640 380" width="640" height="380" role="img" aria-labelledby="noise-fig-t noise-fig-d">
		<title id="noise-fig-t">Where water heater noises come from</title>
		<desc id="noise-fig-d">Cutaway of a tank water heater. Ticking comes from the heat trap fittings on top. Whistling can come from a partly closed valve or the relief valve. Humming comes from the heating elements of an electric tank. Popping and rumbling come from a layer of sediment on the tank bottom, where steam bubbles form under it above the gas burner or lower element. Sizzling comes from water dripping on a hot gas burner. Banging usually comes from the house pipes, not the tank.</desc>
		<g font-family="-apple-system, Segoe UI, Roboto, Arial, sans-serif" font-size="13" fill="#17212e">
			<rect x="220" y="40" width="190" height="290" rx="22" fill="#e6f0f8" stroke="#4a5562" stroke-width="2"/>
			<rect x="262" y="18" width="16" height="24" fill="#4a5562"/>
			<rect x="352" y="18" width="16" height="24" fill="#4a5562"/>
			<path d="M410 110 h22 v14 h-22" fill="#ffffff" stroke="#4a5562"/>
			<rect x="236" y="268" width="158" height="28" fill="#c9b48a" stroke="#7a4f00"/>
			<circle cx="270" cy="262" r="4" fill="#ffffff" stroke="#0b4a7d"/>
			<circle cx="300" cy="258" r="5" fill="#ffffff" stroke="#0b4a7d"/>
			<circle cx="340" cy="262" r="4" fill="#ffffff" stroke="#0b4a7d"/>
			<rect x="240" y="312" width="150" height="12" rx="4" fill="#f4a261" stroke="#a8231a"/>
			<rect x="410" y="200" width="36" height="8" rx="3" fill="#7a4f00"/>
			<path d="M270 18 V8 H60" fill="none" stroke="#17212e"/>
			<text x="20" y="28">Ticking: heat trap fittings</text>
			<path d="M432 117 H470" fill="none" stroke="#17212e"/>
			<text x="475" y="113">Whistling: relief valve,</text>
			<text x="475" y="129">partly closed valve</text>
			<path d="M446 204 H470" fill="none" stroke="#17212e"/>
			<text x="475" y="200">Humming: electric</text>
			<text x="475" y="216">element (loose or working)</text>
			<path d="M236 282 H120 V250" fill="none" stroke="#17212e"/>
			<text x="20" y="226">Popping, rumbling:</text>
			<text x="20" y="242">sediment traps steam</text>
			<path d="M390 318 H470" fill="none" stroke="#17212e"/>
			<text x="475" y="314">Sizzling: water on</text>
			<text x="475" y="330">a hot gas burner</text>
			<text x="20" y="366" fill="#4a5562">Banging when a tap shuts usually comes from the house pipes (water hammer), not the tank.</text>
		</g>
	</svg>
	<figcaption>Where each sound starts, drawn by Hot Water Math from Rheem's and A. O. Smith's explanations. Not to scale.</figcaption>
</figure>

<h2>Why is my water heater popping or rumbling?</h2>
<p>Sediment. Minerals in your water settle on the tank bottom, and A. O. Smith explains that steam bubbles form under that layer as the burner or element heats it; when they force their way through, you hear a pop. A thick layer that blocks the water's flow makes a rolling rumble. Rheem calls sediment the most common cause of a noisy tank.</p>
<dl>
	<dt>Sediment (scale)</dt>
	<dd>Calcium and magnesium minerals that drop out of hard water when it is heated and collect at the bottom of a tank water heater. It insulates the water from the heat source, so the heater works harder, and it takes up space where hot water should be.</dd>
</dl>
<p>The fix is to drain and flush the tank: see <?php echo hwm_link( 'how-to-flush-a-water-heater', 'how to flush a water heater' ); // phpcs:ignore ?>. A. O. Smith says to flush once a year, or <?php echo $hwm_t( 'noise.hardWaterFlush' ); // phpcs:ignore ?> where the water is hard, and that a flush also tells you something: visible sediment coming out is a definite red flag. A. O. Smith puts hard water in more than <?php echo $hwm_t( 'noise.hardWaterHomesPct' ); // phpcs:ignore ?>% of American homes.</p>
<p>Crackling sounds come from the same sediment. A. O. Smith names a second cause: if you see green, blue or gray jelly-like beads in the drain water, an aluminum anode rod is reacting with chlorine, and it suggests asking about an aluminum-zinc rod. Our <?php echo hwm_link( 'water-heater-anode-rod', 'anode rod guide' ); // phpcs:ignore ?> explains the types.</p>
<p>If the popping continues after a good flush, A. O. Smith says something inside has likely failed and needs repair. Popping that comes with water at the base of the tank is worse news: the sediment speeds up the rust that causes tank leaks. Check <?php echo hwm_link( 'water-heater-leaking', 'water heater leaking' ); // phpcs:ignore ?>.</p>

<h2>Ticking, clicking and humming</h2>
<p>Usually harmless. Rheem lists ticking and clicking as normal, caused by heat traps or pipes expanding as they warm and cool; A. O. Smith says the heat trap nipples on top of the tank can tick as the water inside them expands and contracts.</p>
<p>A low hum is normal on an electric model while the elements are heating, Rheem says. A loud hum or buzz is more likely a loose part vibrating. A. O. Smith's advice is to turn the heater off, let it cool, and work from top to bottom tightening screws and checking joints; anything beyond basic tightening, such as a rusted joint, is for a professional. On an ultra-low NOx gas model, A. O. Smith says humming may mean a damaged burner instead.</p>

<h2>Hissing or sizzling: when should you worry?</h2>
<p>It depends on when you hear it. A. O. Smith's gas manual says that when a gas heater is first lit on a tank of cold water, condensation can drip onto the burner and sizzle, and may even leave a small puddle. That is normal and stops once the tank is up to temperature.</p>
<p>Hissing or sizzling that keeps going is different. Rheem says it can mean an active leak, such as water dripping onto a hot burner on a gas model or a leaking element gasket on an electric one, and its advice is to turn the unit off and have it inspected right away. If you also smell gas, leave the house and call the gas utility from outside.</p>

<h2>Screeching or whistling</h2>
<p>Water is being squeezed through a gap that is too small. Rheem and A. O. Smith list a partly closed inlet or outlet valve, sediment blocking the flow, and a faulty or clogged relief valve. First make sure the water valves at the heater are fully open. A. O. Smith adds two mechanical causes: worn blower motor bearings on gas models with a blower, and a damaged heating element on an electric tank.</p>
<p>A screech that continues after the valves are open will not go away on its own, Rheem says; have the unit inspected. Do not try to quiet a relief valve by blocking it: A. O. Smith warns never to cap or plug the relief valve or its pipe.</p>

<h2>Banging or knocking (water hammer)</h2>
<p>Usually a plumbing problem, not the heater. Rheem says banging or knocking is typically water hammer: flowing water stops suddenly when a valve or faucet shuts, and the shock makes pipes shake. Its other triggers are high water pressure and loose pipes.</p>
<ul>
	<li><strong>A water hammer arrestor</strong> near the heater absorbs the shock; A. O. Smith says you should hear an immediate difference.</li>
	<li><strong>A pressure-reducing valve</strong> on the main line if your pressure is high. A. O. Smith's manuals say to set it between <?php echo $hwm_t( 'noise.prvLowPsi' ); // phpcs:ignore ?> and <?php echo $hwm_t( 'noise.prvHighPsi' ); // phpcs:ignore ?> psi.</li>
	<li><strong>Pipe straps</strong> for pipes that move when water stops.</li>
</ul>

<h2>Tankless and heat pump water heater noises</h2>
<p>A tankless unit has no sediment layer to pop, but it has scale. A. O. Smith calls hard water scale a leading cause of tankless failure, and Rinnai says to flush a tankless unit at least once a year. If a tankless unit starts making a new sound, check its display for an error code and look it up in your manual before you call for service.</p>
<p>A heat pump water heater has a fan and a compressor, so it is louder than a tank by design. ENERGY STAR heat pump models run below <?php echo esc_html( (string) hwm_value( 'heat-pump', 'sound.maxDba' ) ); ?> dBA (decibels). Our <?php echo hwm_link( 'heat-pump-water-heater-cost', 'heat pump water heater cost guide' ); // phpcs:ignore ?> covers where to put one so the sound does not bother you.</p>
<p>One rare sound is a warning for every type of tank. A. O. Smith's gas manual says that when hot water has not been used for <?php echo $hwm_t( 'noise.hydrogenWeeks' ); // phpcs:ignore ?> weeks or more, flammable hydrogen gas can build up, and you may hear air escaping from the tap. Run the kitchen hot tap for several minutes before using electrical appliances on the hot water system, with no flame or spark nearby.</p>

<h2>Does a noisy water heater need replacing?</h2>
<p>Not usually: most noises are fixed by a flush, an open valve or a plumbing part. Rheem lists loud repeated banging or rumbling, persistent screeching, hissing, visible leaks and a unit <?php echo $hwm_t( 'noise.rheemAgeLow' ); // phpcs:ignore ?> to <?php echo $hwm_t( 'noise.rheemAgeHigh' ); // phpcs:ignore ?> or more years old as the signs to call a professional, and says noise that persists after maintenance can mean it is time to consider replacement.</p>
<p>Weigh the repair against the heater's age. Our rule, from Rheem: replace if the repair costs more than <?php echo esc_html( (string) ( $hwm_pct['always'] ?? '' ) ); ?>% of a new unit, or more than <?php echo esc_html( (string) ( $hwm_pct['old'] ?? '' ) ); ?>% once it is past <?php echo esc_html( (string) ( $hwm_pct['age'] ?? '' ) ); ?>% of its expected life. The noise box is ticked for you.</p>
<?php
ob_start();
hwm_part( 'repair-inputs', array( 'checked' => array( 'noise' ) ) );
hwm_part( 'calculator', array( 'tool' => 'repair-or-replace-water-heater', 'inputs' => (string) ob_get_clean(), 'label' => 'Repair or replace calculator' ) );
?>
<p>The heater's age is printed in code in its serial number; the <?php echo hwm_link( 'water-heater-age-calculator', 'water heater age calculator' ); // phpcs:ignore ?> decodes it.</p>

<h2>When a noisy water heater is a job for a licensed professional</h2>
<p>Flushing, opening valves and tightening a loose panel screw with the heater off are owner tasks in the manufacturers' guidance. Call a professional for hissing or sizzling that does not stop, a screech after the valves are open, noise that continues after a flush, any noise together with a leak, a gas smell or lukewarm water, and any work on elements, thermostats, burners or relief valves. A. O. Smith points out that those parts connect to electricity or gas lines.</p>

<h2>Limitations of this guide</h2>
<p>Causes come from Rheem's noise guide and A. O. Smith's noise pages and gas manual, matched to what each sound usually means. A sound can have more than one cause, and only an inspection can confirm which. We cannot hear your heater. Read <?php echo hwm_link( 'about', 'who we are' ); // phpcs:ignore ?>: an independent calculator site, not plumbers.</p>
<?php
$faq = array(
	array(
		'q' => 'Why is my water heater making a popping noise?',
		'a' => 'Sediment on the tank bottom. A. O. Smith explains that steam bubbles form under the mineral layer and pop as they break through. Draining and flushing the tank usually stops it. If it continues after a flush, have the heater inspected.',
	),
	array(
		'q' => 'Is it normal for a water heater to make noise?',
		'a' => 'Quiet, brief sounds are normal, Rheem says: ticking from heat traps and pipes, a hum from electric elements, gurgling after heavy use and a soft sound when a gas burner lights. Loud, new or constant popping, hissing, screeching or banging is not.',
	),
	array(
		'q' => 'Why does my water heater make a hissing sound?',
		'a' => 'On a gas heater that has just been lit on a cold tank, it is usually condensation dripping on the burner, which stops once the tank is hot. Hissing that continues can mean a leak onto the burner or a leaking element gasket; Rheem says to turn the unit off and have it inspected.',
	),
	array(
		'q' => 'Why is my water heater banging?',
		'a' => 'Usually water hammer in the house pipes: water stops suddenly when a valve shuts. Rheem also lists high water pressure and loose pipes. A water hammer arrestor, a pressure-reducing valve set to ' . hwm_value( $hwm_f, 'noise.prvLowPsi' ) . ' to ' . hwm_value( $hwm_f, 'noise.prvHighPsi' ) . ' psi, and pipe straps are the usual fixes.',
	),
	array(
		'q' => 'Will flushing a water heater stop the noise?',
		'a' => 'For popping, rumbling and crackling, usually yes, because those come from sediment. Flushing does not fix ticking, humming, whistling from a closed valve, or water hammer. If the noise continues after a flush, A. O. Smith says something inside has likely failed.',
	),
	array(
		'q' => 'Can a noisy water heater explode?',
		'a' => 'Sediment noise alone is not a sign of that. The safety device that prevents a tank failure is the temperature and pressure relief valve, and A. O. Smith warns never to cap or plug it. If that valve releases a lot of very hot water, A. O. Smith says to turn the gas supply off and call a qualified person.',
	),
	array(
		'q' => 'Why does my heat pump water heater make so much noise?',
		'a' => 'It has a fan and a compressor, so some sound is normal. ENERGY STAR models run below ' . hwm_value( 'heat-pump', 'sound.maxDba' ) . ' dBA. Rattles or grinding are not normal; check the manual and have it serviced.',
	),
);
$sources = array(
	array( 'publisher' => 'Rheem Manufacturing', 'title' => 'The ultimate guide to water heater noises: what\'s normal (and what isn\'t)', 'url' => $hwm_r( 'rheem-noise' ), 'accessed' => '2026-11-13' ),
	array( 'publisher' => 'A. O. Smith', 'title' => 'Why your water heater might be making noise', 'url' => $hwm_r( 'aos-noise' ), 'accessed' => '2026-11-13' ),
	array( 'publisher' => 'A. O. Smith', 'title' => 'What to do when your hot water heater is making noises', 'url' => $hwm_r( 'aos-noise-guide' ), 'accessed' => '2026-11-13' ),
	array( 'publisher' => 'A. O. Smith', 'title' => 'Residential Gas Water Heater Use & Care Guide (100400765): condensation, hydrogen gas, relief valve', 'url' => $hwm_r( 'aos-manual' ), 'accessed' => '2026-11-13' ),
	array( 'publisher' => 'A. O. Smith', 'title' => 'Residential guide to monitoring your water quality', 'url' => $hwm_r( 'aos-water-quality' ), 'accessed' => '2026-11-13' ),
	array( 'publisher' => 'Rinnai', 'title' => '13-step tankless water heater flushing procedure', 'url' => $hwm_r( 'rinnai-flush' ), 'accessed' => '2026-11-13' ),
	array( 'publisher' => 'ENERGY STAR (U.S. EPA)', 'title' => 'Heat Pump Water Heater Technical Guide (June 2024): sound levels', 'url' => (string) hwm_raw( 'heat-pump', 'sound.sourceUrl' ), 'accessed' => '2026-11-13' ),
);
