<?php
/**
 * /repair-or-replace-water-heater/ (STRATEGY §3.4). Accepts ?age=&type= from the age calculator.
 * Every number is read from data/*.json or data/computed/repair-or-replace-water-heater.json
 * (built by npm run build:data from formulas.js).
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
$hwm_c    = 'computed/repair-or-replace-water-heater';
$hwm_dr   = 'decision-rules';
$hwm_v    = static fn( string $path, ?int $dec = null ): string => esc_html( hwm_value( $hwm_c, $path, $dec ) );
$hwm_def  = (array) hwm_raw( $hwm_c, 'defaultInputs' );
$hwm_cl   = (array) hwm_raw( $hwm_c, 'client' );
$hwm_types = (array) ( $hwm_cl['types'] ?? array() );
$hwm_symp  = (array) hwm_raw( $hwm_dr, 'symptoms.items' );
$hwm_pct   = (array) hwm_raw( $hwm_c, 'rulesPct' );

/* ---------- calculator inputs ---------- */
ob_start();
?>
<div class="field-grid">
	<div class="field">
		<label for="rr-age">Age (years)</label>
		<input id="rr-age" name="age" type="text" inputmode="decimal" maxlength="5" value="<?php echo esc_attr( (string) ( $hwm_def['ageYears'] ?? '' ) ); ?>" autocomplete="off">
	</div>
	<div class="field">
		<label for="rr-type">Type</label>
		<select id="rr-type" name="type">
			<?php foreach ( $hwm_types as $hwm_k => $hwm_t ) : ?>
				<option value="<?php echo esc_attr( (string) $hwm_k ); ?>"<?php selected( (string) $hwm_k, (string) ( $hwm_def['type'] ?? '' ) ); ?>><?php echo esc_html( (string) $hwm_t['label'] ); ?></option>
			<?php endforeach; ?>
		</select>
	</div>
	<div class="field">
		<label for="rr-repair">Repair quote ($) <span class="hint">Parts and labor, total.</span></label>
		<input id="rr-repair" name="repair" type="text" inputmode="decimal" maxlength="7" value="<?php echo esc_attr( (string) ( $hwm_def['repairUsd'] ?? '' ) ); ?>" autocomplete="off">
	</div>
	<div class="field">
		<label for="rr-replace">New unit, installed ($) <span class="hint">Assumed: typical installed price, U.S. average, from our cost calculator. Type your quote if you have one.</span></label>
		<input id="rr-replace" name="replace" type="text" inputmode="decimal" maxlength="7" value="<?php echo esc_attr( (string) ( $hwm_def['replace'] ?? '' ) ); ?>" autocomplete="off">
	</div>
</div>
<p class="hint">Not sure of the age? <?php echo hwm_link( 'water-heater-age-calculator', 'Find it from the serial number' ); // phpcs:ignore ?>; the result links back here with the age filled in.</p>
<fieldset>
	<legend>What is happening? (tick any)</legend>
	<?php foreach ( $hwm_symp as $hwm_s ) : ?>
		<label class="check"><input type="checkbox" name="<?php echo esc_attr( (string) $hwm_s['id'] ); ?>" value="1"> <?php echo esc_html( (string) $hwm_s['label'] ); ?></label>
	<?php endforeach; ?>
</fieldset>
<details class="disclosure">
	<summary>Energy prices (assumed; change if you know yours)</summary>
	<div class="field">
		<label for="rr-kwh">Electricity ($ per kWh) <span class="hint">U.S. average, EIA.</span></label>
		<input id="rr-kwh" name="kwh" type="text" inputmode="decimal" value="<?php echo esc_attr( hwm_value( $hwm_c, 'client.prices.usdPerKwh' ) ); ?>" autocomplete="off">
	</div>
	<div class="field">
		<label for="rr-therm">Natural gas ($ per therm) <span class="hint">U.S. average, EIA.</span></label>
		<input id="rr-therm" name="therm" type="text" inputmode="decimal" value="<?php echo esc_attr( hwm_value( $hwm_c, 'client.prices.usdPerTherm' ) ); ?>" autocomplete="off">
	</div>
</details>
<?php
echo hwm_tool_data_script( 'hwm-repair-data', $hwm_c ); // phpcs:ignore -- JSON encoded with JSON_HEX_TAG.
$calculator_inputs = (string) ob_get_clean();

$next_steps = array(
	array( 'slug' => 'water-heater-cost-calculator', 'text' => 'Water heater cost calculator', 'why' => 'price the replacement in your state' ),
	array( 'slug' => 'water-heater-size-calculator', 'text' => 'Water heater size calculator', 'why' => 'size the replacement before you shop' ),
	array( 'slug' => 'water-heater-age-calculator', 'text' => 'Water heater age calculator', 'why' => 'find the age from the serial number' ),
	array( 'slug' => 'how-long-does-a-water-heater-last', 'text' => 'How long a water heater lasts', 'why' => 'lifespan by type' ),
);
$related = array( 'water-heater-age-calculator', 'water-heater-size-calculator', 'how-long-does-a-water-heater-last', 'water-heater-cost-calculator' );

$hwm_exs = (array) hwm_raw( $hwm_c, 'examples' );
$hwm_en  = (array) hwm_raw( $hwm_c, 'energy' );
$hwm_vlabel = array( 'replace' => 'replace', 'borderline' => 'borderline', 'repair' => 'repair' );
$hwm_gt  = (array) ( $hwm_types['gas_tank'] ?? array() );
$hwm_tk  = (array) ( $hwm_types['tankless'] ?? array() );
?>
<p class="notice"><strong>Where the numbers come from.</strong> Typical life is DOE's estimate from its 2024 efficiency rules, new-unit prices are installed prices from our cost model (retail listings plus sourced installation costs), and the decision rules are ours, written out below and checked against Rheem and A. O. Smith guidance on <a href="<?php echo esc_url( hwm_url( 'methodology' ) . '#repair-or-replace-water-heater' ); ?>">our methodology page</a>.</p>

<h2>Should you repair or replace your water heater?</h2>
<p>Replace it if the tank itself leaks, if the repair costs more than <?php echo esc_html( (string) ( $hwm_pct['always'] ?? '' ) ); ?>% of a new unit, or if the heater has used <?php echo esc_html( (string) ( $hwm_pct['age'] ?? '' ) ); ?>% of its typical life and the repair costs more than <?php echo esc_html( (string) ( $hwm_pct['old'] ?? '' ) ); ?>%. Otherwise a repair is usually worth it. The flowchart shows the order in which the tool applies these rules.</p>
<?php hwm_part( 'decision-flowchart', array( 'always' => $hwm_pct['always'] ?? '', 'age' => $hwm_pct['age'] ?? '', 'old' => $hwm_pct['old'] ?? '' ) ); ?>
<dl>
	<dt>Typical life</dt>
	<dd>How long a water heater of a given type usually lasts. DOE's figures behind the tool are <?php echo esc_html( (string) ( $hwm_gt['life'] ?? '' ) ); ?> years for tank and heat pump models and <?php echo esc_html( (string) ( $hwm_tk['life'] ?? '' ) ); ?> years for tankless units; see <?php echo hwm_link( 'how-long-does-a-water-heater-last', 'how long a water heater lasts' ); // phpcs:ignore ?> for the range and what shortens it.</dd>
	<dt>The <?php echo esc_html( (string) ( $hwm_pct['always'] ?? '' ) ); ?>% rule</dt>
	<dd>A common rule of thumb, also used by Rheem: when a repair costs more than half the price of a new unit, replacing is usually the better investment.</dd>
</dl>

<h2>What is the most you should pay for a repair?</h2>
<p>For a younger gas tank, the tool's limit is about $<?php echo $hwm_v( 'tables.limits.rows.0.young' ); // phpcs:ignore ?>, half of a typical $<?php echo $hwm_v( 'tables.limits.rows.0.price' ); // phpcs:ignore ?> new unit; once it is old, about $<?php echo $hwm_v( 'tables.limits.rows.0.old' ); // phpcs:ignore ?>. The table works it out for every type, using typical installed replacement prices at U.S. average costs. If your replacement quote is higher, the limits rise with it.</p>
<?php echo hwm_table( $hwm_c, 'limits' ); // phpcs:ignore -- escaped in the part. ?>
<p>Tankless units are counted as old later because DOE estimates they last longer. The installed prices include labor, the permit and haul-away of the old unit; they come from our <?php echo hwm_link( 'water-heater-cost-calculator', 'water heater cost calculator' ); // phpcs:ignore ?>, where you can price your own state and job. Type that figure, or a real quote, into the tool to get your own limit.</p>

<h2>Which water heater problems can be repaired?</h2>
<p>Most parts can be replaced: heating elements, thermostats, anode rods and valves are designed to be swapped out. The tank cannot. A leak from the tank body, or a heater that sat in a flood, means a new heater. The table lists what each problem usually means, with the manufacturer's words.</p>
<div class="table-wrap" role="region" aria-label="Water heater problems and what they mean" tabindex="0">
	<table>
		<caption>Common problems: repair, replace or check first</caption>
		<thead><tr><th scope="col">Problem</th><th scope="col">Usually</th><th scope="col">What the manufacturer says</th></tr></thead>
		<tbody>
			<?php
			$hwm_eff = array( 'replace' => 'Replace', 'repair' => 'Repair', 'warn' => 'Sign of the end; check first', 'safety' => 'Safety check now' );
			foreach ( $hwm_symp as $hwm_s ) :
				?>
				<tr><th scope="row"><?php echo esc_html( (string) $hwm_s['label'] ); ?></th><td><?php echo esc_html( $hwm_eff[ $hwm_s['effect'] ] ?? '' ); ?></td><td><?php echo esc_html( (string) $hwm_s['note'] ); ?> <a href="<?php echo esc_url( (string) $hwm_s['sourceUrl'] ); ?>" rel="noopener">Source</a></td></tr>
			<?php endforeach; ?>
		</tbody>
	</table>
	<p class="table-source">Source: <?php echo esc_html( (string) hwm_raw( $hwm_dr, 'symptoms.meta.source' ) ); ?>.</p>
</div>
<p class="notice notice--warn">Never cap or plug the temperature and pressure relief valve or its discharge pipe, even if it drips. A. O. Smith's manual warns that an explosion could occur if the valve or pipe is blocked.</p>

<h2>Worked examples: three common cases</h2>
<p>Each case uses the calculator's rules and typical installed prices. Change any number in the tool to see how the answer moves.</p>
<ol>
	<?php foreach ( $hwm_exs as $hwm_e ) : ?>
		<li><strong><?php echo esc_html( (string) $hwm_e['age'] ); ?>-year-old <?php echo esc_html( strtolower( (string) $hwm_e['label'] ) ); ?>, $<?php echo esc_html( (string) $hwm_e['repairFmt'] ); ?> repair.</strong> It has used <?php echo esc_html( (string) $hwm_e['used'] ); ?>% of a <?php echo esc_html( (string) $hwm_e['life'] ); ?>-year life (old from <?php echo esc_html( (string) $hwm_e['oldFrom'] ); ?> years), and the repair is <?php echo esc_html( (string) $hwm_e['share'] ); ?>% of a $<?php echo esc_html( (string) $hwm_e['replace'] ); ?> new unit. Verdict: <strong><?php echo esc_html( $hwm_vlabel[ $hwm_e['verdict'] ] ?? '' ); ?></strong>.</li>
	<?php endforeach; ?>
</ol>
<p>The heat pump case is borderline because the repair is a large share of the price even though the unit is younger. That is where a second quote, or asking what else is worn, pays off.</p>

<h2>How much can a new, efficient water heater save?</h2>
<p>Replacing an electric tank with a heat pump water heater saves the most: about $<?php echo esc_html( (string) ( $hwm_en[1]['save'] ?? '' ) ); ?> a year at U.S. average prices for typical use. A high-efficiency gas tank saves about $<?php echo esc_html( (string) ( $hwm_en[0]['save'] ?? '' ) ); ?> a year over a standard one. These savings make replacement more attractive when a repair is borderline.</p>
<?php echo hwm_table( $hwm_c, 'energy' ); // phpcs:ignore ?>
<p>The comparison assumes a standard new unit, because the efficiency of an old heater is not on its label in today's units. An older heater can do worse than a new standard one, so the real saving can be larger. Use your own energy prices in the calculator, and see whether rebates apply in your area.</p>

<h2>Questions to ask before you accept any quote</h2>
<ul>
	<li>Is the leak from the tank, or from a fitting, valve or pipe? Can you show me?</li>
	<li>Which part failed, and what does the part cost on its own?</li>
	<li>Is the heater or the part still under the manufacturer's warranty? What serial number did you check?</li>
	<li>What else is worn, such as the anode rod or the relief valve, and will it need work soon?</li>
	<li>If I replace it, what does the quote include: permit, haul-away, expansion tank, venting or wiring changes?</li>
	<li>Will the new heater have the first hour rating my household needs?</li>
</ul>
<p>Ask for prices in writing and compare like for like. Getting a second quote is normal.</p>

<h2>When is this a job for a licensed professional?</h2>
<p>Gas parts, venting, wiring and relief valves are safety systems, and replacing a heater usually needs a permit. Let a licensed plumber or electrician diagnose these and do the work; the manufacturer's manual says which owner maintenance is safe, such as testing the relief valve once a year. Use this tool to judge a quote, not to diagnose a fault.</p>

<h2>Limitations of this estimate</h2>
<p>The rules are our own judgment, not an industry standard; no government agency publishes one. Typical life is a national average, and your heater may fail sooner or last longer. New-unit prices are U.S. average installed prices for a like-for-like swap; a fuel switch, new venting or a tight space costs more, so enter your own quote when you have one. The tool cannot see corrosion inside the tank.</p>
<?php
$hwm_e0 = (array) ( $hwm_exs[0] ?? array() );
$faq    = array(
	array(
		'q' => 'Is it worth repairing a 10-year-old water heater?',
		'a' => 'Sometimes. At 10 years a tank has used most of its ' . ( $hwm_gt['life'] ?? '' ) . '-year typical life, so our rules call it old from ' . hwm_value( $hwm_c, 'defaultCase.oldFrom' ) . ' years. A small repair, under ' . ( $hwm_pct['old'] ?? '' ) . '% of a new unit, is reasonable while you plan a replacement; anything more usually is not.',
	),
	array(
		'q' => 'Can a leaking water heater tank be repaired?',
		'a' => 'No. A leak from the tank body cannot be fixed, so the heater has to be replaced. First check where the water comes from: A. O. Smith notes that leaks are often in the plumbing, not the heater, and a drip at a valve or fitting can usually be repaired.',
	),
	array(
		'q' => 'What is the 50 percent rule for water heaters?',
		'a' => 'If a repair costs more than half the price of a new water heater, replacing it is usually the better investment. Rheem uses the same rule. Our tool also replaces an old heater at a lower share, ' . ( $hwm_pct['old'] ?? '' ) . '%, because it is likely to need another repair soon.',
	),
	array(
		'q' => 'Should I replace a water heater that still works?',
		'a' => 'If it is past its typical life, planning a replacement before it fails avoids an emergency and water damage. You do not have to replace a working heater, but check its age, look for rust or moisture at the base, and keep the relief valve tested as the manual says.',
	),
	array(
		'q' => 'How long should a water heater last?',
		'a' => 'DOE estimates about ' . ( $hwm_gt['life'] ?? '' ) . ' years as typical for tank water heaters and about ' . ( $hwm_tk['life'] ?? '' ) . ' years for tankless units. Hard water and no maintenance shorten the life; our lifespan guide covers the details.',
	),
	array(
		'q' => 'Does a new water heater lower my energy bill?',
		'a' => 'It can. Replacing an electric tank with a heat pump water heater saves about $' . ( $hwm_en[1]['save'] ?? '' ) . ' a year at U.S. average prices for typical use, and a high-efficiency gas tank about $' . ( $hwm_en[0]['save'] ?? '' ) . ' over a standard gas tank. A like-for-like replacement saves little.',
	),
);
$sources = array(
	array( 'publisher' => 'Rheem', 'title' => 'Water heater lifespan: when to repair vs. replace (March 25, 2026)', 'url' => 'https://www.rheem.com/water-heating/articles/water-heater-lifespan-when-to-repair-vs-replace/', 'accessed' => '2026-11-13' ),
	array( 'publisher' => 'A. O. Smith', 'title' => 'Residential electronic thermostat electric water heater: installation instructions and use and care guide (2021)', 'url' => 'https://www.aosmithatlowes.com/media/1710/smart-electric-2000598248.pdf', 'accessed' => '2026-11-13' ),
	array( 'publisher' => 'U.S. Department of Energy, Federal Register', 'title' => 'Energy conservation standards for consumer water heaters, final rule (May 6, 2024): product lifetime', 'url' => (string) hwm_raw( 'lifespans', 'meta.sourceUrl' ), 'accessed' => '2026-11-13' ),
	array( 'publisher' => 'The Home Depot', 'title' => 'Water heater listings (equipment prices)', 'url' => (string) hwm_raw( 'cost-ranges', 'meta.sourceUrl' ), 'accessed' => '2026-11-12' ),
	array( 'publisher' => 'U.S. Department of Energy, Federal Register', 'title' => 'Consumer gas-fired instantaneous water heaters final rule (December 26, 2024): installation, permit and disposal costs', 'url' => (string) hwm_raw( 'installation-costs', 'references.doe-gas-tankless-2024.sourceUrl' ), 'accessed' => '2026-11-13' ),
	array( 'publisher' => 'ENERGY STAR', 'title' => 'Certified water heaters dataset (heat pump and high-efficiency gas UEF)', 'url' => (string) hwm_raw( 'energystar-water-heaters', 'meta.sourceUrl' ), 'accessed' => '2026-11-13' ),
	array( 'publisher' => 'U.S. Energy Information Administration', 'title' => 'Average residential electricity and natural gas prices', 'url' => (string) hwm_raw( 'energy-prices', 'meta.sourceUrl' ), 'accessed' => '2026-11-13' ),
	array( 'publisher' => 'U.S. DOE Federal Energy Management Program', 'title' => 'Purchasing energy-efficient residential water heaters (typical daily use)', 'url' => (string) hwm_raw( 'hot-water-use', 'typicalHouseholdGallonsPerDay.sourceUrl' ), 'accessed' => '2026-11-13' ),
);
