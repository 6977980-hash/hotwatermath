<?php
/**
 * /water-heater-size-calculator/ (STRATEGY §3.3). Every number is read from data/*.json or
 * data/computed/water-heater-size-calculator.json (built by npm run build:data from formulas.js).
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
$hwm_c   = 'computed/water-heater-size-calculator';
$hwm_ph  = 'peak-hour-demand';
$hwm_hg  = 'household-size-guides';
$hwm_v   = static fn( string $path, ?int $dec = null ): string => esc_html( hwm_value( $hwm_c, $path, $dec ) );
$hwm_def = (array) hwm_raw( $hwm_c, 'defaultInputs' );
$hwm_cl  = (array) hwm_raw( $hwm_c, 'client' );
$hwm_acts = (array) ( $hwm_cl['activities'] ?? array() );
$hwm_presets = (array) ( $hwm_cl['presets'] ?? array() );
$hwm_maxe = hwm_value( 'uef-ranges', 'federalMinimums.electricResistanceMaxGallons' );

/* ---------- calculator inputs ---------- */
ob_start();
?>
<div class="field">
	<label for="ts-people">People in your home</label>
	<select id="ts-people" name="people">
		<?php foreach ( array_keys( $hwm_presets ) as $hwm_n ) : ?>
			<option value="<?php echo esc_attr( (string) $hwm_n ); ?>"<?php selected( (string) $hwm_n, (string) ( $hwm_def['people'] ?? '' ) ); ?>><?php echo esc_html( (string) $hwm_n . ( 1 === (int) $hwm_n ? ' person' : ' people' ) ); ?></option>
		<?php endforeach; ?>
	</select>
</div>
<fieldset>
	<legend>Fuel</legend>
	<label class="check"><input type="radio" name="fuel" value="gas"<?php checked( 'gas', $hwm_def['fuel'] ?? '' ); ?>> Gas or propane</label>
	<label class="check"><input type="radio" name="fuel" value="electric"> Electric</label>
	<label class="check"><input type="radio" name="fuel" value="heatpump"> Heat pump (hybrid)</label>
</fieldset>
<details class="disclosure">
	<summary>Your busiest hour (people fills in a typical one; change any count)</summary>
	<div class="field-grid">
		<?php foreach ( $hwm_acts as $hwm_k => $hwm_a ) : ?>
			<div class="field">
				<label for="ts-<?php echo esc_attr( (string) $hwm_k ); ?>"><?php echo esc_html( (string) $hwm_a['label'] ); ?> <span class="hint"><?php echo esc_html( (string) $hwm_a['gallons'] ); ?> gal each</span></label>
				<input id="ts-<?php echo esc_attr( (string) $hwm_k ); ?>" name="<?php echo esc_attr( (string) $hwm_k ); ?>" type="text" inputmode="numeric" pattern="[0-9]*" maxlength="2" value="<?php echo esc_attr( (string) ( $hwm_def['counts'][ $hwm_k ] ?? 0 ) ); ?>" autocomplete="off">
			</div>
		<?php endforeach; ?>
	</div>
	<label class="check"><input type="checkbox" name="margin" value="1"> We often run several things at once (add <?php echo esc_html( hwm_value( $hwm_c, 'defaultCase.marginPct' ) ); ?>%, our assumption)</label>
</details>
<?php
echo hwm_tool_data_script( 'hwm-tank-data', $hwm_c ); // phpcs:ignore -- JSON encoded with JSON_HEX_TAG.
$calculator_inputs = (string) ob_get_clean();

$next_steps = array(
	array( 'slug' => 'water-heater-cost-calculator', 'text' => 'Water heater cost calculator', 'why' => 'price this size in your state' ),
	array( 'slug' => 'gas-vs-electric-water-heater', 'text' => 'Gas vs electric water heater', 'why' => 'compare running costs' ),
	array( 'slug' => 'tankless-water-heater-size-calculator', 'text' => 'Tankless water heater size calculator', 'why' => 'size a tankless unit instead' ),
	array( 'slug' => 'repair-or-replace-water-heater', 'text' => 'Repair or replace your water heater', 'why' => 'if the old one is still running' ),
);
$related = array( 'tankless-water-heater-size-calculator', 'repair-or-replace-water-heater', 'water-heater-age-calculator', 'water-heater-cost-calculator' );

$hwm_ex  = (array) hwm_raw( $hwm_c, 'example' );
$hwm_bp  = (array) hwm_raw( $hwm_c, 'byPeople' );
$hwm_row = static function ( int $n ) use ( $hwm_bp ): array {
	foreach ( $hwm_bp as $hwm_r ) {
		if ( (int) $hwm_r['n'] === $n ) {
			return (array) $hwm_r;
		}
	}
	return array();
};
$hwm_p2 = $hwm_row( 2 );
$hwm_p4 = $hwm_row( 4 );
$hwm_p5 = $hwm_row( 5 );
$hwm_p6 = $hwm_row( 6 );
$hwm_am = (array) hwm_raw( $hwm_hg, 'guides.american' );
$hwm_rex = (array) hwm_raw( $hwm_hg, 'guides.rheem.example' );
?>
<p class="notice"><strong>Where the numbers come from.</strong> Hot water per use is from the AHRI peak-hour worksheet, first hour ratings are from real models (Rheem spec sheets and the ENERGY STAR certified list), and the result is checked against American, Rheem and A. O. Smith sizing guides on <a href="<?php echo esc_url( hwm_url( 'methodology' ) . '#water-heater-size-calculator' ); ?>">our methodology page</a>.</p>

<h2>How do you size a tank water heater?</h2>
<p>Size a tank water heater by its first hour rating, not its gallons. Add up the hot water your home uses in its busiest hour (the peak-hour demand), then pick a heater whose first hour rating is at least that number. The calculator does the adding and shows the smallest tank of each fuel that meets it.</p>
<ol>
	<li><strong>Picture your busiest hour.</strong> For most homes it is the morning: showers back to back, a shave, breakfast.</li>
	<li><strong>Count each use.</strong> Multiply the number of times by the hot water per use in the table below and add them up.</li>
	<li><strong>Match the first hour rating.</strong> AHRI and DOE's Building America program say to choose a first hour rating equal to or greater than that total.</li>
	<li><strong>Read the label.</strong> The first hour rating is printed on the EnergyGuide label and in the spec sheet of every model.</li>
</ol>
<dl>
	<dt>Peak-hour demand</dt>
	<dd>The gallons of hot water your home uses in its busiest hour of a normal day.</dd>
	<dt>First hour rating (FHR)</dt>
	<dd>How many gallons of hot water a heater can deliver in one hour, starting with a full tank. It combines the stored water with how fast the burner or element reheats.</dd>
	<dt>Recovery</dt>
	<dd>How fast a heater reheats the tank. Gas burners recover faster than electric elements, so a gas tank reaches the same FHR with fewer gallons.</dd>
</dl>
<?php echo hwm_table( 'computed/reference-tables', 'peakHour' ); // phpcs:ignore -- escaped in the part. ?>

<h2>What size water heater do I need for my household?</h2>
<p>For a typical busy hour, a family of four needs a first hour rating of about <?php echo esc_html( (string) ( $hwm_p4['fhr'] ?? '' ) ); ?> gallons: a <?php echo esc_html( (string) ( $hwm_p4['gas'] ?? '' ) ); ?>-gallon gas tank, a <?php echo esc_html( (string) ( $hwm_p4['electric'] ?? '' ) ); ?>-gallon electric tank or a <?php echo esc_html( (string) ( $hwm_p4['heatpump'] ?? '' ) ); ?>-gallon heat pump. The table shows every household size next to two manufacturers' own guides.</p>
<?php echo hwm_table( $hwm_c, 'households' ); // phpcs:ignore ?>
<p>Each row uses a busy hour we picked for that household size (for four people: <?php echo $hwm_v( 'defaultCase.preset' ); // phpcs:ignore ?>). Manufacturer guides disagree with each other because each assumes different habits. <?php echo esc_html( (string) ( $hwm_am['label'] ?? '' ) ); ?> also advises moving up one level if <?php echo esc_html( lcfirst( rtrim( (string) ( $hwm_am['note'] ?? '' ), '.' ) ) ); ?>. Your own count beats any table, so change the uses in the calculator.</p>

<h2>Why does the first hour rating matter more than tank size?</h2>
<p>Because two tanks of the same size can deliver very different amounts of hot water. A 50-gallon gas tank in our sample delivers about <?php echo $hwm_v( 'compare.gas50' ); // phpcs:ignore ?> gallons in the first hour, while a 50-gallon electric tank delivers about <?php echo $hwm_v( 'compare.elec50' ); // phpcs:ignore ?>, a gap of <?php echo $hwm_v( 'compare.gasVsElec50' ); // phpcs:ignore ?> gallons. Burner size, tank design and fuel all change the FHR.</p>
<?php echo hwm_table( 'computed/fhr-table', 'fhr' ); // phpcs:ignore ?>
<p>Even within one size, the FHR spreads: 40-gallon gas models in our sample range from <?php echo $hwm_v( 'compare.gas40Range' ); // phpcs:ignore ?> gallons. Federal efficiency rules require electric storage tanks larger than <?php echo esc_html( $hwm_maxe ); ?> gallons to reach heat pump efficiency, so electric rows stop at <?php echo esc_html( $hwm_maxe ); ?> gallons and bigger electric heaters are heat pumps.</p>

<h2>Worked example: a family of <?php echo $hwm_v( 'example.people' ); // phpcs:ignore ?> on a weekday morning</h2>
<p>This is the calculator's default busy hour. Every number below comes from the same formula the tool uses.</p>
<ol>
	<?php foreach ( (array) ( $hwm_ex['lines'] ?? array() ) as $hwm_l ) : ?>
		<li><?php echo esc_html( (string) $hwm_l['label'] ); ?>: <?php echo esc_html( (string) $hwm_l['count'] ); ?> × <?php echo esc_html( (string) $hwm_l['gallons'] ); ?> gal = <?php echo esc_html( (string) $hwm_l['total'] ); ?> gal</li>
	<?php endforeach; ?>
	<li><strong>Peak-hour demand: <?php echo $hwm_v( 'example.phd' ); // phpcs:ignore ?> gallons</strong>, so look for a first hour rating of <?php echo $hwm_v( 'example.fhr' ); // phpcs:ignore ?> gallons or more.</li>
	<li>Gas: the <?php echo $hwm_v( 'example.gas' ); // phpcs:ignore ?>-gallon size rates about <?php echo $hwm_v( 'example.gasFhr' ); // phpcs:ignore ?> gallons. Electric: <?php echo $hwm_v( 'example.electric' ); // phpcs:ignore ?> gallons (about <?php echo $hwm_v( 'example.electricFhr' ); // phpcs:ignore ?>). Heat pump: <?php echo $hwm_v( 'example.heatpump' ); // phpcs:ignore ?> gallons (about <?php echo $hwm_v( 'example.heatpumpFhr' ); // phpcs:ignore ?>).</li>
</ol>
<p>If this family often runs the dishwasher or a load of laundry during the morning rush, add them in the calculator. One washer load adds <?php echo esc_html( hwm_value( $hwm_ph, 'activities.clothesWasher.gallons' ) ); ?> gallons and usually moves the answer up one size.</p>

<h2>40 or 50 gallons: which one do you need?</h2>
<p>Pick the smaller tank only if its first hour rating still covers your busiest hour. In our sample a typical 40-gallon gas tank rates about <?php echo $hwm_v( 'compare.gas40' ); // phpcs:ignore ?> gallons and a 50-gallon about <?php echo $hwm_v( 'compare.gas50' ); // phpcs:ignore ?>; for electric the step is <?php echo $hwm_v( 'compare.elec40' ); // phpcs:ignore ?> to <?php echo $hwm_v( 'compare.elec50' ); // phpcs:ignore ?> gallons.</p>
<p>So moving from 40 to 50 gallons adds about <?php echo $hwm_v( 'compare.step40to50Gas' ); // phpcs:ignore ?> gallons of first-hour hot water for gas and <?php echo $hwm_v( 'compare.step40to50Elec' ); // phpcs:ignore ?> for electric. A high-input 40-gallon gas model can beat a basic 50-gallon one, so compare labels, not sizes. Check that the larger tank fits the space and that the floor can carry it.</p>

<h2>Gas vs electric vs heat pump: how does the size change?</h2>
<p>Electric tanks need more gallons than gas for the same household, because an electric element reheats water more slowly than a gas burner. For the family above that means <?php echo $hwm_v( 'example.gas' ); // phpcs:ignore ?> gallons of gas, <?php echo $hwm_v( 'example.electric' ); // phpcs:ignore ?> of electric or <?php echo $hwm_v( 'example.heatpump' ); // phpcs:ignore ?> of heat pump.</p>
<p>Heat pump water heaters are the most efficient tanks but rate lower for their size: a typical 50-gallon heat pump model on the ENERGY STAR list rates about <?php echo $hwm_v( 'compare.hp50' ); // phpcs:ignore ?> gallons in the first hour, against <?php echo $hwm_v( 'compare.gas50' ); // phpcs:ignore ?> for gas. Households of <?php echo esc_html( (string) ( $hwm_p5['n'] ?? '' ) ); ?> or more often outgrow a single electric tank and land on a large heat pump, a gas tank, or a <?php echo hwm_link( 'tankless-water-heater-size-calculator', 'tankless water heater sized by flow rate' ); // phpcs:ignore ?>.</p>

<h2>Common water heater sizing mistakes</h2>
<ul>
	<li><strong>Buying by gallons alone.</strong> 50-gallon gas models in our sample rate anywhere from <?php echo $hwm_v( 'compare.gas50Range' ); // phpcs:ignore ?> gallons in the first hour. Compare the FHR on the label.</li>
	<li><strong>Sizing for the whole day.</strong> A tank refills all day; what runs it out is one busy hour.</li>
	<li><strong>Copying the old tank.</strong> Your household may have changed. Teenagers, a big soaking tub or a new washer can push you up a size.</li>
	<li><strong>Oversizing "to be safe".</strong> A bigger tank keeps more water hot around the clock, which costs standby energy, and it may not fit.</li>
	<li><strong>Ignoring the tub.</strong> <?php echo esc_html( (string) ( $hwm_am['label'] ?? '' ) ); ?>'s guide says the tank should be about the size of a large tub you fill.</li>
</ul>

<h2>When is this a job for a licensed professional?</h2>
<p>Replacing a water heater involves gas piping and venting or a 240-volt circuit, and most places require a permit. A larger tank may need a bigger gas supply, a different vent or new wiring. A licensed plumber can confirm the size against your household and local code; use this result to check their recommendation, not to replace it.</p>

<h2>Limitations of this estimate</h2>
<p>Your busiest hour is a guess about your own habits, and the presets are our assumptions. The per-use gallons follow the AHRI worksheet, which assumes no water-saving fixtures, so homes with low-flow showerheads may need less. First hour ratings come from a sample of current models, mostly one manufacturer's tanks; the model you buy may rate higher or lower, so read its label. Very cold incoming water lowers real output in winter.</p>
<?php
$faq = array(
	array(
		'q' => 'What size water heater do I need for a family of 4?',
		'a' => 'With three showers, hand washing and breakfast in the busiest hour, a family of four uses about ' . ( $hwm_p4['phd'] ?? '' ) . ' gallons. That calls for a first hour rating of ' . ( $hwm_p4['fhr'] ?? '' ) . '+ gallons: a ' . ( $hwm_p4['gas'] ?? '' ) . '-gallon gas tank, ' . ( $hwm_p4['electric'] ?? '' ) . '-gallon electric or ' . ( $hwm_p4['heatpump'] ?? '' ) . '-gallon heat pump.',
	),
	array(
		'q' => 'What size water heater do I need for a family of 5?',
		'a' => 'Our typical five-person busy hour (four showers plus a shave, hand washing and breakfast) uses about ' . ( $hwm_p5['phd'] ?? '' ) . ' gallons. A ' . ( $hwm_p5['gas'] ?? '' ) . '-gallon gas tank covers it; no standard electric tank does, so electric homes look at an ' . ( $hwm_p5['heatpump'] ?? '' ) . '-gallon heat pump or a tankless unit.',
	),
	array(
		'q' => 'What size water heater do I need for 2 people?',
		'a' => 'Two people with back-to-back showers use about ' . ( $hwm_p2['phd'] ?? '' ) . ' gallons in their busiest hour. A ' . ( $hwm_p2['gas'] ?? '' ) . '-gallon gas tank or ' . ( $hwm_p2['electric'] ?? '' ) . '-gallon electric tank covers it. Many couples still choose 40 or 50 gallons for guests or a soaking tub.',
	),
	array(
		'q' => 'What size water heater do I need for 6 people?',
		'a' => 'Six people need about ' . ( $hwm_p6['fhr'] ?? '' ) . ' gallons of first-hour hot water in our typical busy hour. Only the largest gas tank in our sample, ' . ( $hwm_p6['gas'] ?? '' ) . ' gallons, reaches that. Many large households choose a tankless unit, two heaters, or spread showers out.',
	),
	array(
		'q' => 'What size water heater do I need for a 3 or 4 bedroom house?',
		'a' => 'Bedrooms do not set the size; the people and their habits do. Count the showers and other uses in your busiest hour. ' . ( $hwm_am['label'] ?? '' ) . ' suggests moving up one size if the home has more than two full baths, teenagers, or an oversize tub.',
	),
	array(
		'q' => 'How many gallons of hot water does a person use?',
		'a' => 'In a busy hour, one shower uses about ' . hwm_value( $hwm_ph, 'activities.shower.gallons' ) . ' gallons of hot water on the AHRI worksheet, and hand and face washing about ' . hwm_value( $hwm_ph, 'activities.handsFace.gallons' ) . '. Over a whole day, DOE uses ' . hwm_value( 'hot-water-use', 'typicalHouseholdGallonsPerDay.value' ) . ' gallons as typical for a household when rating water heaters.',
	),
	array(
		'q' => 'Do electric water heaters need to be bigger than gas?',
		'a' => 'Yes, usually one size up. A 50-gallon gas tank in our sample delivers about ' . hwm_value( $hwm_c, 'compare.gas50' ) . ' gallons in the first hour and a 50-gallon electric tank about ' . hwm_value( $hwm_c, 'compare.elec50' ) . ', because electric elements reheat water more slowly than a gas burner.',
	),
	array(
		'q' => 'What is a first hour rating?',
		'a' => 'The first hour rating is the gallons of hot water a heater can supply in one hour, starting with a full tank. DOE measures it in the federal test, and it is printed on the EnergyGuide label. Choose a rating at least equal to your peak-hour demand.',
	),
);
$sources = array(
	array( 'publisher' => 'AHRI', 'title' => 'Water heater calculator: worksheet for estimating peak hour demand / first hour rating', 'url' => (string) hwm_raw( $hwm_ph, 'meta.sourceUrl' ), 'accessed' => '2026-11-13' ),
	array( 'publisher' => 'Pacific Northwest National Laboratory, Building America Solution Center', 'title' => 'DHW first hour rating tool', 'url' => (string) hwm_raw( $hwm_ph, 'fhrRule.sourceUrl' ), 'accessed' => '2026-11-13' ),
	array( 'publisher' => 'U.S. Code of Federal Regulations', 'title' => '10 CFR Part 430, Subpart B, Appendix E: test procedure for water heaters (first hour rating, draw patterns)', 'url' => (string) hwm_raw( 'hot-water-use', 'meta.sourceUrl' ), 'accessed' => '2026-11-13' ),
	array( 'publisher' => 'U.S. Code of Federal Regulations', 'title' => '10 CFR 430.32(d): energy conservation standards for water heaters', 'url' => (string) hwm_raw( 'uef-ranges', 'meta.sourceUrl' ), 'accessed' => '2026-11-13' ),
	array( 'publisher' => 'Rheem', 'title' => 'Performance gas and electric water heater spec sheets (first hour ratings)', 'url' => (string) hwm_raw( 'fhr-to-tank-size', 'meta.sourceUrl' ), 'accessed' => '2026-11-13' ),
	array( 'publisher' => 'ENERGY STAR', 'title' => 'Certified water heaters dataset (heat pump first hour ratings)', 'url' => (string) hwm_raw( 'fhr-to-tank-size', 'heatPump.sourceUrl' ), 'accessed' => '2026-11-13' ),
	array( 'publisher' => 'American Water Heater', 'title' => 'Residential product catalog: residential water heater selection guide', 'url' => (string) hwm_raw( $hwm_hg, 'guides.american.sourceUrl' ), 'accessed' => '2026-11-13' ),
	array( 'publisher' => 'Rheem', 'title' => 'What size gas water heater do I need for my home?', 'url' => (string) hwm_raw( $hwm_hg, 'guides.rheem.sourceUrl' ), 'accessed' => '2026-11-13' ),
	array( 'publisher' => 'A. O. Smith', 'title' => 'What size water heater is right for you?', 'url' => (string) hwm_raw( $hwm_hg, 'guides.aosmith.sourceUrl' ), 'accessed' => '2026-11-13' ),
);
