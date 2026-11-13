<?php
/**
 * /gas-line-size-for-tankless-water-heater/ (STRATEGY §2 item 14, Phase 6d, CLAUDE.md §8b): what the fuel gas code
 * tables say about pipe size for a gas tankless water heater, for discussion with an installer. Not a design.
 * Code rows (IFGC 2021 Tables 402.4(2) and 402.4(3), excerpts) and manufacturer figures live in data/safety.json →
 * gasLine; lookups are data/computed/gas-line-size-for-tankless-water-heater.json (formulas.gasCfh, gasPipeSize,
 * gasMaxLengthFt). No gas work steps: the page says what the code and manuals require and who does the work.
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
$hwm_f = 'safety';
$hwm_c = 'computed/gas-line-size-for-tankless-water-heater';
$hwm_s = static fn( string $path ): string => esc_html( hwm_value( $hwm_f, 'gasLine.' . $path ) );
$hwm_v = static fn( string $path ): string => esc_html( hwm_value( $hwm_c, 'figures.' . $path ) );
$hwm_r = static fn( string $key ): string => (string) hwm_raw( $hwm_f, 'references.' . $key . '.sourceUrl' );
$hwm_n = static fn( string $path ): string => esc_html( number_format( (float) hwm_raw( $hwm_f, 'gasLine.' . $path ) ) );
$hwm_x = static fn( string $path ): string => esc_html( number_format( (float) hwm_raw( 'installation-costs', 'extras.' . $path ) ) );
$hwm_ifgc = (string) hwm_raw( $hwm_f, 'gasLine.edition' );

$related = array( 'tankless-water-heater-size-calculator', 'tankless-water-heater-installation-cost', 'tankless-water-heater-electrical-requirements', 'tankless-vs-tank-water-heater', 'gas-vs-electric-water-heater' );
hwm_part( 'safety-notice', array( 'gas' => true, 'power' => false, 'scald' => false ) );
?>
<aside class="notice" aria-label="Gas piping is licensed work">
	<p><strong>For discussion with your installer, not a design.</strong> This page shows what the <?php echo esc_html( $hwm_ifgc ); ?> sizing tables say, so you can follow a quote and ask good questions. Gas piping is sized, installed, pressure-tested and inspected by a licensed professional, usually under a permit, and your local code official decides which code edition and amendments apply. Rheem's and Rinnai's own gas line guides say the same.</p>
</aside>

<h2>What size gas line does a tankless water heater need?</h2>
<p>Most whole-house gas tankless units need a 3/4-inch line, and 1-inch pipe on longer runs. A <?php echo $hwm_v( 'big' ); // phpcs:ignore ?> BTU/h unit burns about <?php echo $hwm_v( 'cfh199' ); // phpcs:ignore ?> cubic feet of gas an hour; in the code's standard table, 3/4-inch steel pipe carries that only up to about <?php echo $hwm_v( 'q05max' ); // phpcs:ignore ?> feet. Rheem's gas piping guide says a near-200,000 BTU appliance needs at least 3/4-inch pipe "in all cases" on a normal low-pressure system.</p>
<p>That is why a tank-to-tankless switch so often needs new gas pipe. Rheem notes most tankless units are rated from <?php echo $hwm_n( 'tanklessBtuLow' ); // phpcs:ignore ?> to <?php echo $hwm_n( 'tanklessBtuHigh' ); // phpcs:ignore ?> BTU/h or more, "one of the largest single appliances on a typical gas system", while the tank in Rheem's own example is <?php echo $hwm_v( 'tankBtuh' ); // phpcs:ignore ?> BTU/h. The 1/2-inch pipe that fed the tank was sized for a much smaller load.</p>
<dl>
	<dt>BTU/h (input rating)</dt>
	<dd>How much gas energy the burner can use in an hour at full fire. It is on the unit's rating plate, which Rheem says lists the BTU rating and the gas pressures the appliance needs.</dd>
	<dt>CFH</dt>
	<dd>Cubic feet of gas per hour. The code's sizing tables are written in CFH, so the BTU rating has to be converted first.</dd>
	<dt>Inches of water column (in. w.c.)</dt>
	<dd>The unit for low gas pressures. Rheem: a typical home is supplied at about <?php echo $hwm_s( 'typicalSupplyInWc' ); // phpcs:ignore ?> in. w.c., a small fraction of 1 psi.</dd>
	<dt>Pressure drop</dt>
	<dd>How much pressure the pipe is allowed to lose between the meter and the appliance at full flow. A smaller allowed drop needs bigger pipe.</dd>
	<dt>Equivalent length</dt>
	<dd>The measured pipe length plus an allowance for every elbow, tee and valve. Rinnai and Rheem both say fittings must be added before you read the table.</dd>
</dl>

<h2>How do you turn a BTU rating into a gas flow?</h2>
<p>Divide the input rating by the heating value of the gas. Rinnai's sizing table is based on <?php echo $hwm_n( 'btuPerCf' ); // phpcs:ignore ?> BTU per cubic foot of natural gas, so a <?php echo $hwm_v( 'big' ); // phpcs:ignore ?> BTU/h unit needs <?php echo $hwm_v( 'cfh199' ); // phpcs:ignore ?> CFH and a <?php echo $hwm_v( 'low' ); // phpcs:ignore ?> BTU/h unit needs <?php echo $hwm_v( 'cfhLow' ); // phpcs:ignore ?> CFH. Rheem's brochure uses <?php echo $hwm_n( 'rheemBtuPerCf' ); // phpcs:ignore ?>, which gives a slightly smaller flow; your gas utility can tell you the heating value it delivers.</p>
<p>Then add every other gas appliance that shares the pipe. The code is plain about this: the flow to provide "shall be the sum of the maximum input of the appliances served", assuming they "could be operating at full capacity simultaneously" (<?php echo esc_html( $hwm_ifgc ); ?>, Section 402.2). A tankless unit on its own branch from the meter is sized for its own load; the trunk pipe it shares with the furnace and range is sized for all of them.</p>
<p>Not sure what input your unit needs? Size it for your household first. The calculator below gives the BTU/h input for the fixtures you run at once; divide by <?php echo $hwm_n( 'btuPerCf' ); // phpcs:ignore ?> for the CFH.</p>
<?php
ob_start();
hwm_part( 'tankless-inputs', array( 'fuel' => 'gas' ) );
hwm_part( 'calculator', array( 'tool' => 'tankless-water-heater-size-calculator', 'inputs' => (string) ob_get_clean(), 'label' => 'Tankless water heater size calculator' ) );
?>

<h2>Gas pipe size by BTU and length</h2>
<p>For a normal home supply, find your unit's input in the left column and your equivalent length across the top. This lookup uses the code's standard low-pressure table for Schedule 40 steel pipe and natural gas, <?php echo esc_html( (string) hwm_raw( $hwm_f, 'gasLine.tables.drop05.table' ) ); ?>, at a <?php echo esc_html( (string) hwm_raw( $hwm_f, 'gasLine.tables.drop05.dropInWc' ) ); ?> in. w.c. drop, the limit Rheem describes for a typical low-pressure system.</p>
<?php echo hwm_table( $hwm_c, 'size05' ); // phpcs:ignore -- escaped in the part. ?>
<p>Three rules decide how the table is read. Lengths round up: Rinnai's bulletin reads a run that falls between two rows in the next longer row. Fittings count: every elbow adds equivalent length, so a short run with several bends can belong in a longer column. And the length is measured from the meter, not from the nearest tee, under the longest-length method both Rinnai and Rheem walk through; installers may use the branch-length method instead, which Rheem calls the most common.</p>
<p>The result on a typical retrofit: a <?php echo $hwm_v( 'big' ); // phpcs:ignore ?> BTU/h unit 40 feet from the meter needs <?php echo $hwm_v( 's05at40' ); // phpcs:ignore ?>-inch pipe at this pressure drop, and <?php echo $hwm_v( 's05at20' ); // phpcs:ignore ?>-inch is enough only on short runs. This matches Rheem's own worked example, where a unit of nearly the same size on a short branch needs 1-inch pipe at an even stricter pressure drop.</p>

<h2>Can a tankless water heater run on a 1/2-inch gas line?</h2>
<p>Sometimes, and only when the supply pressure is high enough. The code has a second table for a 3.0 in. w.c. drop, <?php echo esc_html( (string) hwm_raw( $hwm_f, 'gasLine.tables.drop30.table' ) ); ?>, whose stated intended use is an "initial supply pressure of 8.0-inch w.c. or greater". With that much pressure to spend, 1/2-inch pipe can carry a <?php echo $hwm_v( 'big' ); // phpcs:ignore ?> BTU/h unit up to about <?php echo $hwm_v( 'h30max' ); // phpcs:ignore ?> feet.</p>
<?php echo hwm_table( $hwm_c, 'size30' ); // phpcs:ignore -- escaped in the part. ?>
<p>Rheem lists the conditions: the static gas pressure must be at least <?php echo $hwm_s( 'drop30MinSupplyInWc' ); // phpcs:ignore ?> in. w.c., and the pressure left after the 3.0 in. w.c. drop must still be above the highest minimum inlet pressure of any appliance on the system. A typical home at about <?php echo $hwm_s( 'typicalSupplyInWc' ); // phpcs:ignore ?> in. w.c. does not qualify. Only a pressure reading with a manometer, taken by your installer or gas utility, tells you which table applies; you cannot see it by looking at the pipe.</p>
<p>Rinnai's view is the same in different words: its units "typically require a 3/4" gas line", but "if there is sufficient pressure and the gas line is short enough, there are some cases where a 1/2" gas line can be used." That can make a switch from a tank "faster, easier and less expensive", which is why it is worth asking about, not assuming.</p>

<h2>How does this compare with the manufacturer's numbers?</h2>
<p>Closely. Rinnai publishes the maximum length of 1/2-inch and 3/4-inch pipe for each of its models, from the same 3.0 in. w.c. table. Running our lookup on the same inputs reproduces <?php echo $hwm_v( 'rinnaiMatches' ); // phpcs:ignore ?> of Rinnai's <?php echo $hwm_v( 'rinnaiChecks' ); // phpcs:ignore ?> figures exactly.</p>
<?php echo hwm_table( $hwm_c, 'rinnai' ); // phpcs:ignore -- escaped in the part. ?>
<p>The two differences: for the RUC80i, Rinnai lists <?php echo $hwm_v( 'ruc80Rq' ); // phpcs:ignore ?> feet of 3/4-inch pipe, while the table still carries its flow at <?php echo $hwm_v( 'ruc80Oq' ); // phpcs:ignore ?> feet (<?php echo $hwm_v( 'ruc80Rq' ); // phpcs:ignore ?> is the table's capacity figure in the <?php echo $hwm_v( 'ruc80Oq' ); // phpcs:ignore ?>-foot row, which suggests a slip in Rinnai's chart). For the RU199i, Rinnai lists <?php echo $hwm_v( 'ru199Rh' ); // phpcs:ignore ?> feet of 1/2-inch pipe for the same <?php echo $hwm_v( 'big' ); // phpcs:ignore ?> BTU/h rating that gets <?php echo $hwm_v( 'h30max' ); // phpcs:ignore ?> feet on its RUC98i; the table gives <?php echo $hwm_v( 'ru199Oh' ); // phpcs:ignore ?>. In both cases we show the shorter, more cautious figure. These checks are also on our <?php echo hwm_link( 'methodology', 'methodology page' ); // phpcs:ignore ?>.</p>

<h2>What the code table says</h2>
<p>These are the rows of the two tables used above, quoted from the <?php echo esc_html( $hwm_ifgc ); ?> for 10 to 100 feet and the four sizes a home uses. Each number is the pipe's capacity in cubic feet of gas per hour. The full tables run to much longer lengths and larger pipe, and other tables cover copper tubing, higher pressures and propane.</p>
<?php echo hwm_table( $hwm_c, 'code05' ); // phpcs:ignore -- escaped in the part. ?>
<?php echo hwm_table( $hwm_c, 'code30' ); // phpcs:ignore -- escaped in the part. ?>
<p>The code also allows two other routes (Section 402.3): the sizing tables in a listed piping system's own installation instructions, which is how flexible stainless steel tubing (CSST) is sized, and engineering methods. Some states and cities adopt NFPA 54, the National Fuel Gas Code, instead of the IFGC. Rinnai's and Rheem's guides are built on NFPA 54, and Rinnai's lengths match the IFGC rows, as the cross-check above shows.</p>

<?php hwm_ad( 'B' ); ?>

<h2>Will your gas meter handle a tankless water heater?</h2>
<p>Maybe not. The meter and regulator have a capacity in CFH too, and Rheem says it "must be greater than the total sum of the maximum BTU rating of all the appliances in the home". Rheem's worked example shows how a tankless unit can push a home past the usual size.</p>
<div class="table-wrap" role="region" aria-label="Meter load example" tabindex="0">
<table>
	<caption>Rheem's example home, before and after a tankless unit</caption>
	<thead><tr><th scope="col">Appliance</th><th scope="col" class="num">With a tank (BTU/h)</th><th scope="col" class="num">With tankless (BTU/h)</th></tr></thead>
	<tbody>
		<tr><th scope="row">Water heater</th><td class="num"><?php echo $hwm_v( 'tankBtuh' ); // phpcs:ignore ?></td><td class="num"><?php echo $hwm_v( 'exTankless' ); // phpcs:ignore ?></td></tr>
		<tr><th scope="row">Furnace</th><td class="num"><?php echo $hwm_v( 'exFurnace' ); // phpcs:ignore ?></td><td class="num"><?php echo $hwm_v( 'exFurnace' ); // phpcs:ignore ?></td></tr>
		<tr><th scope="row">Range</th><td class="num"><?php echo $hwm_v( 'exRange' ); // phpcs:ignore ?></td><td class="num"><?php echo $hwm_v( 'exRange' ); // phpcs:ignore ?></td></tr>
		<tr><th scope="row">Dryer</th><td class="num"><?php echo $hwm_v( 'exDryer' ); // phpcs:ignore ?></td><td class="num"><?php echo $hwm_v( 'exDryer' ); // phpcs:ignore ?></td></tr>
		<tr><th scope="row">Gas logs</th><td class="num"><?php echo $hwm_v( 'exLogs' ); // phpcs:ignore ?></td><td class="num"><?php echo $hwm_v( 'exLogs' ); // phpcs:ignore ?></td></tr>
		<tr><th scope="row">Total</th><td class="num"><?php echo $hwm_v( 'exTank' ); // phpcs:ignore ?></td><td class="num"><?php echo $hwm_v( 'exTotal' ); // phpcs:ignore ?></td></tr>
		<tr><th scope="row">Gas flow at <?php echo $hwm_n( 'btuPerCf' ); // phpcs:ignore ?> BTU per cubic foot</th><td class="num"><?php echo $hwm_v( 'exTankCfh' ); // phpcs:ignore ?> CFH</td><td class="num"><?php echo $hwm_v( 'exCfh' ); // phpcs:ignore ?> CFH</td></tr>
	</tbody>
</table>
</div>
<p class="table-source">Source: Rheem, Gas Piping Information for Tankless Water Heaters (appliance loads); flow computed with formulas.gasCfh. Rheem divides by <?php echo $hwm_n( 'rheemBtuPerCf' ); // phpcs:ignore ?> and gets <?php echo $hwm_v( 'exRheemCfh' ); // phpcs:ignore ?> CFH.</p>
<p>Rheem says "a typical household meter and regulator is commonly rated at <?php echo $hwm_v( 'meter' ); // phpcs:ignore ?> Cubic Feet per Hour". The tank home fits; the tankless home needs well over that. The fix is the gas utility's job: Rheem points owners to the local utility for a meter and regulator upgrade. Ask about it early, before the installation date is set.</p>

<h2>Propane, CSST and copper</h2>
<p>Low-pressure propane systems are sized from their own tables, and the allowed lengths are shorter. Rinnai's propane table gives its <?php echo $hwm_v( 'big' ); // phpcs:ignore ?> BTU/h models <?php echo $hwm_s( 'rinnaiPropane199.half' ); // phpcs:ignore ?> feet of 1/2-inch and <?php echo $hwm_s( 'rinnaiPropane199.threeQuarter' ); // phpcs:ignore ?> feet of 3/4-inch pipe, against <?php echo $hwm_v( 'h30max' ); // phpcs:ignore ?> and <?php echo $hwm_v( 'q30max' ); // phpcs:ignore ?> feet on natural gas. Size propane piping from the propane tables or your propane supplier, not from the natural gas rows on this page.</p>
<p>Flexible corrugated stainless steel tubing (CSST) is sized from its manufacturer's own listed tables, which the code allows under Section 402.3, and its sizes are flow designations, not inch sizes. Copper tubing has its own tables in the code. Either way the method is the same: total load, equivalent length, allowed pressure drop.</p>

<h2>What happens if the gas line is too small?</h2>
<p>The heater cannot get enough gas at full fire. Rheem lists what follows on an undersized system: burners that soot, pilots and burners that go out, and condensation forming in heat exchangers not designed for it, with sooting that can clog flues and produce carbon monoxide. Rinnai says its units "safely cease operation if they detect an inadequate gas flow", which can mean cold water when several gas appliances run at once.</p>
<p>A "negative pressure" gas valve is not a cure. Rheem warns that such a valve can run at very low pressure but "can actually rob the gas from other appliances, such as a furnace". The code's answer to an added appliance is Section 401.4: check the existing piping, and if it is inadequate, enlarge it or run separate piping.</p>

<h2>What a gas line upgrade costs</h2>
<p>In our cost model, upsizing or adding a gas line for a water heater costs about $<?php echo $hwm_x( 'gasLine.typical' ); // phpcs:ignore ?>, with a range of $<?php echo $hwm_x( 'gasLine.low' ); // phpcs:ignore ?> to $<?php echo $hwm_x( 'gasLine.high' ); // phpcs:ignore ?>, more for long runs through finished walls. A meter upgrade is set by your utility. Our <?php echo hwm_link( 'tankless-water-heater-installation-cost', 'tankless installation cost guide' ); // phpcs:ignore ?> puts that next to the vent, labor and permit, and the <?php echo hwm_link( 'tankless-vs-tank-water-heater', 'tankless vs tank calculator' ); // phpcs:ignore ?> shows whether the switch pays back.</p>

<h2>Common mistakes with tankless gas lines</h2>
<ul>
	<li><strong>Reusing the tank's 1/2-inch line without a pressure check.</strong> It works only under the 3.0 in. w.c. conditions above.</li>
	<li><strong>Sizing for the water heater alone.</strong> Shared pipe carries every appliance at once (Section 402.2).</li>
	<li><strong>Measuring to the nearest tee.</strong> Length is counted from the meter or point of delivery.</li>
	<li><strong>Forgetting fittings.</strong> Elbows and tees add equivalent length.</li>
	<li><strong>Ignoring the meter.</strong> A <?php echo $hwm_v( 'meter' ); // phpcs:ignore ?> CFH meter can be too small even when the pipe is right.</li>
	<li><strong>Using natural gas rows for propane.</strong> Propane has its own tables and shorter lengths.</li>
	<li><strong>Using a sea-level table at altitude.</strong> The code says the flow must be adjusted above <?php echo $hwm_n( 'altitudeFt' ); // phpcs:ignore ?> feet (Section 402.2).</li>
</ul>

<h2>When to call a licensed professional</h2>
<p>For any change to the gas piping: sizing, installing, pressure testing and connecting. Rinnai's bulletin says gas systems "should be designed, installed, and inspected by a licensed professional", and Rheem recommends "a licensed gas tradesman size, design, and install the gas system". Ask your local building department whether a permit and inspection are required. Call your gas utility for a meter or regulator upgrade, and right away if you ever smell gas.</p>
<p>What you can do yourself is useful: read the BTU input off the rating plates of all your gas appliances, measure roughly how far the water heater is from the meter, and ask each installer which table and pressure drop they sized to and whether the quote includes a meter check.</p>

<h2>Limitations of this guide</h2>
<p>The lookups cover Schedule 40 steel pipe, natural gas, one appliance per run and the two pressure drops shown; real systems mix branches, materials and loads. Code rows are quoted from the <?php echo esc_html( $hwm_ifgc ); ?>; your area may use another edition, NFPA 54, or local amendments, and the local code official decides. Manufacturer figures are from Rinnai and Rheem as published; your unit's installation manual wins over anything here. We are an independent calculator site, not gas fitters: read <?php echo hwm_link( 'about', 'who we are' ); // phpcs:ignore ?>.</p>
<?php
$faq = array(
	array(
		'q' => 'What size gas line do I need for a tankless water heater?',
		'a' => 'Usually 3/4-inch pipe, and 1-inch on longer runs. A ' . hwm_value( $hwm_c, 'figures.big' ) . ' BTU/h unit burns about ' . hwm_value( $hwm_c, 'figures.cfh199' ) . ' cubic feet of gas an hour; in the code\'s standard low-pressure table, 3/4-inch steel pipe carries that only to about ' . hwm_value( $hwm_c, 'figures.q05max' ) . ' feet. Your installer sizes it for your total load and pipe length.',
	),
	array(
		'q' => 'Can I use a 1/2 inch gas line for a tankless water heater?',
		'a' => 'Only on short runs where the supply pressure is at least ' . hwm_value( 'safety', 'gasLine.drop30MinSupplyInWc' ) . ' inches of water column, so the code\'s 3.0 in. w.c. drop table applies. Then a ' . hwm_value( $hwm_c, 'figures.big' ) . ' BTU/h unit can run about ' . hwm_value( $hwm_c, 'figures.h30max' ) . ' feet of 1/2-inch pipe. A typical home at about ' . hwm_value( 'safety', 'gasLine.typicalSupplyInWc' ) . ' in. w.c. does not qualify.',
	),
	array(
		'q' => 'How many BTU can a 3/4 inch gas line carry?',
		'a' => 'At the code\'s 0.5 in. w.c. drop, 3/4-inch Schedule 40 pipe carries ' . hwm_value( $hwm_c, 'figures.q05cap10' ) . ' cubic feet per hour at 10 feet and ' . hwm_value( $hwm_c, 'figures.q05cap100' ) . ' at 100 feet, about ' . hwm_value( $hwm_c, 'figures.q05cap10Btuh' ) . ' and ' . hwm_value( $hwm_c, 'figures.q05cap100Btuh' ) . ' BTU/h of natural gas. Longer pipe carries less, so length matters as much as size.',
	),
	array(
		'q' => 'Does a tankless water heater need a bigger gas meter?',
		'a' => 'It can. Rheem says a typical home meter and regulator is rated about ' . hwm_value( $hwm_c, 'figures.meter' ) . ' cubic feet per hour. In its example, a tankless unit raises the home\'s total to about ' . hwm_value( $hwm_c, 'figures.exCfh' ) . ' CFH. Your gas utility checks and upgrades the meter and regulator.',
	),
	array(
		'q' => 'What happens if the gas line is too small for a tankless water heater?',
		'a' => 'The unit cannot fire fully when it needs to. Rheem lists sooting burners, pilots and burners going out and condensation in heat exchangers; Rinnai units shut down when gas flow is inadequate, which can mean cold water when other gas appliances run.',
	),
	array(
		'q' => 'Is the gas line size different for propane?',
		'a' => 'Yes. Propane has its own sizing tables, and the allowed lengths are shorter: Rinnai gives its ' . hwm_value( $hwm_c, 'figures.big' ) . ' BTU/h models ' . hwm_value( 'safety', 'gasLine.rinnaiPropane199.half' ) . ' feet of 1/2-inch and ' . hwm_value( 'safety', 'gasLine.rinnaiPropane199.threeQuarter' ) . ' feet of 3/4-inch pipe on low-pressure propane. Ask your propane supplier and installer to size it.',
	),
	array(
		'q' => 'Can I size or install the gas line myself?',
		'a' => 'You can do the reading: rating plates, distances, and the questions to ask. Rinnai and Rheem both say gas piping should be designed, installed and inspected by a licensed professional, and your building department can tell you about permits. Ask your local building department about the permit.',
	),
);
$sources = array(
	array( 'publisher' => 'International Code Council', 'title' => '2021 International Fuel Gas Code, Chapter 4: Sections 401.4, 402.1 to 402.4, Tables 402.4(2) and 402.4(3)', 'url' => $hwm_r( 'ifgc-2021-ch4' ), 'accessed' => '2026-11-13' ),
	array( 'publisher' => 'Rheem Manufacturing', 'title' => 'Gas Piping Information for Tankless Water Heaters (2018)', 'url' => $hwm_r( 'rheem-gas-piping' ), 'accessed' => '2026-11-13' ),
	array( 'publisher' => 'Rinnai America', 'title' => 'Technical Bulletin TB-111: 1/2-inch gas line piping information', 'url' => $hwm_r( 'rinnai-tb111' ), 'accessed' => '2026-11-13' ),
	array( 'publisher' => 'Rinnai America', 'title' => 'Professional FAQ: gas line sizing tables for natural gas and propane', 'url' => $hwm_r( 'rinnai-faq' ), 'accessed' => '2026-11-13' ),
	array( 'publisher' => 'A. O. Smith', 'title' => 'Residential Gas Water Heater Use & Care Guide (100400765): what to do if you smell gas', 'url' => $hwm_r( 'aos-gas-manual' ), 'accessed' => '2026-11-13' ),
);
