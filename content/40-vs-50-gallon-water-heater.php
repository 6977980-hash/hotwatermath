<?php
/**
 * /40-vs-50-gallon-water-heater/ (STRATEGY §2 item 17): which tank size a household needs, compared by first hour
 * rating, price and fit, with the tank size calculator embedded. Numbers: data/computed/40-vs-50-gallon-water-heater.json
 * (spec sheet medians, retail prices, the cost model) and data/household-size-guides.json.
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
$hwm_c   = 'computed/40-vs-50-gallon-water-heater';
$hwm_g   = static fn( string $path ): string => esc_html( hwm_value( $hwm_c, 'figures.gas.' . $path ) );
$hwm_e   = static fn( string $path ): string => esc_html( hwm_value( $hwm_c, 'figures.el.' . $path ) );
$hwm_v   = static fn( string $path ): string => esc_html( hwm_value( $hwm_c, 'figures.' . $path ) );
$hwm_am  = (array) hwm_raw( $hwm_c, 'figures.americanRows' );
$hwm_bp  = (array) hwm_raw( 'computed/water-heater-size-calculator', 'byPeople' );
$hwm_hg  = (array) hwm_raw( 'household-size-guides', 'guides.american' );
$hwm_ph  = 'peak-hour-demand';

$related = array( 'water-heater-size-calculator', 'water-heater-installation-cost', 'gas-water-heater-installation-cost', 'electric-water-heater-installation-cost', 'tankless-vs-tank-water-heater' );
?>
<h2>Is a 40 or 50 gallon water heater better?</h2>
<p>A 50-gallon tank is better for four or more people, and a 40-gallon tank is enough for most households of three or fewer on gas. The difference that matters is hot water in your busiest hour: a typical 50-gallon gas tank delivers about <?php echo $hwm_g( 'fhr50' ); // phpcs:ignore ?> gallons, a 40-gallon one about <?php echo $hwm_g( 'fhr40' ); // phpcs:ignore ?>, a gap of about <?php echo $hwm_g( 'fhrDiff' ); // phpcs:ignore ?> gallons.</p>
<?php echo hwm_table( $hwm_c, 'compare' ); // phpcs:ignore -- escaped in the part. ?>
<p>On electric the gap is smaller, about <?php echo $hwm_e( 'fhrDiff' ); // phpcs:ignore ?> gallons, because both sizes use the same <?php echo $hwm_e( 'in40' ); // phpcs:ignore ?> kW element and recover equally slowly. The extra price is about $<?php echo $hwm_g( 'instDiff' ); // phpcs:ignore ?> installed for gas and $<?php echo $hwm_e( 'instDiff' ); // phpcs:ignore ?> for electric; labor is the same, so the whole difference is the unit.</p>
<dl>
	<dt>First hour rating (FHR)</dt>
	<dd>The gallons of hot water a tank delivers in one hour, starting full: what is stored plus what the burner or element reheats in that hour. It is printed on the yellow EnergyGuide label and is the number to size by.</dd>
	<dt>Peak hour demand</dt>
	<dd>The hot water your home uses in its busiest hour, for example two showers, a sink and the dishwasher. Choose a heater whose FHR is at least this number.</dd>
</dl>

<h2>Why tank size alone is the wrong number</h2>
<p>Two 50-gallon tanks can deliver very different amounts of hot water. The spec sheets we use show 50-gallon gas tanks from <?php echo $hwm_g( 'fhr50Range' ); // phpcs:ignore ?> gallons in the first hour, and some 40-gallon gas models (<?php echo $hwm_g( 'fhr40Range' ); // phpcs:ignore ?>) beat weaker 50s. A bigger burner recovers faster; a gas tank's burner is about <?php echo $hwm_g( 'in40' ); // phpcs:ignore ?> to <?php echo $hwm_g( 'in50' ); // phpcs:ignore ?> thousand BTU per hour on these sizes.</p>
<p>So compare first hour ratings on the labels, not gallons on the box. A high-recovery 40-gallon gas tank can be the right pick for a tight closet where a 50 does not fit.</p>

<h2>How much hot water does your busiest hour use?</h2>
<p>Add up what happens in your home's busiest hour, usually the morning. The industry worksheet from AHRI, which runs the water heater rating program, gives these gallons per use:</p>
<?php echo hwm_table( $hwm_c, 'peakHour' ); // phpcs:ignore ?>
<p>AHRI's own example household has <?php echo esc_html( hwm_value( $hwm_ph, 'example.showers' ) ); ?> showers, a shave, a shampoo and hand dishwashing in its busiest hour, for a peak hour demand of <?php echo esc_html( hwm_value( $hwm_ph, 'example.peakHourDemand' ) ); ?> gallons. That home needs an FHR of at least <?php echo esc_html( hwm_value( $hwm_ph, 'example.peakHourDemand' ) ); ?>: above a typical 40-gallon gas tank (<?php echo $hwm_g( 'fhr40' ); // phpcs:ignore ?>), within reach of a 50 (<?php echo $hwm_g( 'fhr50' ); // phpcs:ignore ?>).</p>

<h2>Check your own household</h2>
<p>The calculator fills in a typical busiest hour for your household size. Change the counts to match your own, pick your fuel, and it gives the first hour rating and tank size to buy.</p>
<?php
ob_start();
hwm_part( 'tank-size-inputs' );
hwm_part( 'calculator', array( 'tool' => 'water-heater-size-calculator', 'inputs' => (string) ob_get_clean(), 'label' => '40 or 50 gallon water heater size check' ) );
?>

<h2>40 vs 50 gallon by household size</h2>
<p>With our typical busiest hour for each household size, a 40-gallon gas tank covers <?php echo $hwm_g( 'fits40' ); // phpcs:ignore ?> people and a 50-gallon one <?php echo $hwm_g( 'fits50' ); // phpcs:ignore ?>. On electric, a 40 covers <?php echo $hwm_e( 'fits40' ); // phpcs:ignore ?> and a 50 covers <?php echo $hwm_e( 'fits50' ); // phpcs:ignore ?>. American Water Heater's selection guide gives a first hour rating range for each family size, shown here next to ours.</p>
<div class="table-wrap" role="region" aria-label="Tank size by household" tabindex="0">
	<table>
		<caption>First hour rating needed by household size, and the tank that covers it</caption>
		<thead><tr><th scope="col">People</th><th scope="col" class="num">Our typical busiest hour (gal)</th><th scope="col" class="num">American Water Heater FHR range (gal)</th><th scope="col" class="num">Gas tank</th><th scope="col" class="num">Electric tank</th></tr></thead>
		<tbody>
			<?php foreach ( $hwm_bp as $hwm_row ) : ?>
				<?php
				$hwm_ar = array();
				foreach ( $hwm_am as $hwm_a ) {
					if ( (int) $hwm_a['people'] === (int) $hwm_row['n'] ) {
						$hwm_ar = $hwm_a;
					}
				}
				?>
				<tr>
					<td><?php echo esc_html( (string) $hwm_row['n'] ); ?></td>
					<td class="num"><?php echo esc_html( (string) $hwm_row['phd'] ); ?></td>
					<td class="num"><?php echo esc_html( $hwm_ar ? $hwm_ar['low'] . '–' . $hwm_ar['high'] : '–' ); ?></td>
					<td class="num"><?php echo esc_html( '' !== (string) $hwm_row['gas'] ? $hwm_row['gas'] . ' gal' : 'Two tanks or tankless' ); ?></td>
					<td class="num"><?php echo esc_html( '' !== (string) $hwm_row['electric'] ? $hwm_row['electric'] . ' gal' : 'Heat pump' ); ?></td>
				</tr>
			<?php endforeach; ?>
		</tbody>
	</table>
	<p class="table-source">Sources: our size calculator's household presets with AHRI per-use gallons; <a href="<?php echo esc_url( (string) ( $hwm_hg['sourceUrl'] ?? '' ) ); ?>" rel="noopener">American Water Heater residential selection guide</a>.</p>
</div>
<p>A four-person home sits right at the edge for a 40-gallon gas tank: its typical busiest hour is just above what a typical 40 delivers. If your mornings are lighter than our preset, a high-recovery 40 can still work; if teenagers take long showers, buy the 50.</p>

<h2>Does a 50 gallon water heater cost more to run?</h2>
<p>A little, mainly for gas, because a bigger tank loses more heat while it waits. The federal efficiency rules account for that, and typical 40- and 50-gallon gas tanks rate about UEF <?php echo $hwm_g( 'uef40' ); // phpcs:ignore ?> and <?php echo $hwm_g( 'uef50' ); // phpcs:ignore ?> in the spec sheets we use. Electric tanks of both sizes rate about UEF <?php echo $hwm_e( 'uef50' ); // phpcs:ignore ?>. Your bill depends far more on how much hot water you use than on the tank size.</p>
<p>Running out of hot water costs more in practice: people buy a second heater or replace the tank early. For running costs by fuel and state, see the <?php echo hwm_link( 'gas-vs-electric-water-heater', 'gas vs electric water heater calculator' ); // phpcs:ignore ?>.</p>

<h2>When to go bigger than 50 gallons</h2>
<p>Five or more people, a large soaking tub, or two showers running at once often need more than a 50. A typical 75-gallon gas tank delivers about <?php echo $hwm_v( 'g75' ); // phpcs:ignore ?> gallons in the first hour and costs about $<?php echo $hwm_v( 'g75Inst' ); // phpcs:ignore ?> installed. On electric, standard tanks stop at <?php echo esc_html( hwm_value( 'uef-ranges', 'federalMinimums.electricResistanceMaxGallons' ) ); ?> gallons; above that a heat pump is the option, with an 80-gallon model delivering about <?php echo $hwm_v( 'hp80' ); // phpcs:ignore ?> gallons for about $<?php echo $hwm_v( 'hp80Inst' ); // phpcs:ignore ?> installed.</p>
<p>A tankless unit avoids the size question but has its own: flow in gallons per minute. The <?php echo hwm_link( 'tankless-vs-tank-water-heater', 'tankless vs tank comparison' ); // phpcs:ignore ?> covers when that pays.</p>

<h2>Common sizing mistakes</h2>
<ul>
	<li><strong>Replacing like for like without checking.</strong> Your household may have grown since the last heater went in.</li>
	<li><strong>Going by gallons, not FHR.</strong> Two tanks of the same size can differ by more than the 40 to 50 gap.</li>
	<li><strong>Forgetting the space.</strong> A 50-gallon tank is wider or taller than a 40; measure the closet and door first.</li>
	<li><strong>Swapping gas for a same-size heat pump.</strong> Heat pumps recover slowly, and ENERGY STAR advises sizing up when replacing gas.</li>
</ul>

<h2>Limitations</h2>
<p>Household fits use a typical busiest hour for each household size, an assumption you can change in the calculator. FHR values are medians of the spec sheets in our data, not every model on sale. Prices are U.S. averages from our cost model.</p>
<?php
$faq = array(
	array(
		'q' => 'Is a 40-gallon water heater enough for a family of 4?',
		'a' => 'Borderline on gas. A typical four-person busiest hour uses about ' . (string) ( $hwm_bp[3]['phd'] ?? '' ) . ' gallons, and a typical 40-gallon gas tank delivers about ' . hwm_value( $hwm_c, 'figures.gas.fhr40' ) . ' in the first hour. A high-recovery 40 can work with light mornings; otherwise buy a 50. On electric, our calculator suggests a ' . (string) ( $hwm_bp[3]['electric'] ?? '' ) . '-gallon tank for four.',
	),
	array(
		'q' => 'How much more does a 50-gallon water heater cost than a 40?',
		'a' => 'About $' . hwm_value( $hwm_c, 'figures.gas.instDiff' ) . ' more installed for gas and $' . hwm_value( $hwm_c, 'figures.el.instDiff' ) . ' for electric at U.S. average prices. Labor is the same for both sizes, so the difference is the unit price.',
	),
	array(
		'q' => 'How many showers can a 50-gallon water heater handle?',
		'a' => 'AHRI counts about ' . hwm_value( $hwm_ph, 'activities.shower.gallons' ) . ' gallons of hot water per shower. A typical 50-gallon gas tank delivers about ' . hwm_value( $hwm_c, 'figures.gas.fhr50' ) . ' gallons in the first hour, enough for about ' . hwm_value( $hwm_c, 'figures.gas.showers50' ) . ' showers in a row. A typical 50-gallon electric tank, about ' . hwm_value( $hwm_c, 'figures.el.fhr50' ) . ' gallons, covers about ' . hwm_value( $hwm_c, 'figures.el.showers50' ) . '.',
	),
	array(
		'q' => 'Can I replace a 40-gallon water heater with a 50?',
		'a' => 'Usually yes, if it fits the space and the existing gas line, vent or circuit suits the new unit. A 50-gallon tank is larger, so measure height, width and door clearance. The installer checks the gas input or the electrical rating against the existing supply.',
	),
	array(
		'q' => 'Does a 50-gallon water heater use more energy than a 40?',
		'a' => 'Slightly, because it keeps more water hot. Typical gas models rate UEF ' . hwm_value( $hwm_c, 'figures.gas.uef40' ) . ' at 40 gallons and ' . hwm_value( $hwm_c, 'figures.gas.uef50' ) . ' at 50 in our spec sheets; electric tanks rate about ' . hwm_value( $hwm_c, 'figures.el.uef50' ) . ' at both sizes. How much hot water you use matters far more.',
	),
	array(
		'q' => 'What size water heater do I need for 2 people?',
		'a' => 'A 40-gallon tank is plenty for most two-person homes: a typical two-person busiest hour uses about ' . (string) ( $hwm_bp[1]['phd'] ?? '' ) . ' gallons, which our size calculator covers with a ' . (string) ( $hwm_bp[1]['gas'] ?? '' ) . '-gallon gas or ' . (string) ( $hwm_bp[1]['electric'] ?? '' ) . '-gallon electric tank. Check your own busiest hour in the calculator above.',
	),
);
$sources = array(
	array( 'publisher' => 'AHRI', 'title' => 'Water Heater Calculator: Worksheet for Estimating Peak Hour Demand/First Hour Rating', 'url' => (string) hwm_raw( $hwm_ph, 'meta.sourceUrl' ), 'accessed' => (string) hwm_raw( $hwm_ph, 'meta.sourceDate' ) ),
	array( 'publisher' => 'PNNL Building America Solution Center (U.S. DOE)', 'title' => 'DHW First Hour Rating tool: choose FHR equal to or greater than the peak hour demand', 'url' => (string) hwm_raw( $hwm_ph, 'fhrRule.sourceUrl' ), 'accessed' => (string) hwm_raw( $hwm_ph, 'meta.sourceDate' ) ),
	array( 'publisher' => 'American Water Heater', 'title' => 'Residential Water Heater Selection Guide: first hour rating by family size', 'url' => (string) ( $hwm_hg['sourceUrl'] ?? '' ), 'accessed' => (string) hwm_raw( 'household-size-guides', 'meta.sourceDate' ) ),
	array( 'publisher' => 'Rheem', 'title' => 'Performance gas and electric water heater spec sheets (first hour rating, input, UEF)', 'url' => (string) hwm_raw( 'fhr-to-tank-size', 'meta.sourceUrl' ), 'accessed' => (string) hwm_raw( 'fhr-to-tank-size', 'meta.sourceDate' ) ),
	array( 'publisher' => 'ENERGY STAR (U.S. EPA)', 'title' => 'Best Practices: Installing Heat Pump Water Heaters (2024): size up when replacing gas', 'url' => (string) hwm_raw( 'heat-pump', 'references.es-install.sourceUrl' ), 'accessed' => (string) hwm_raw( 'heat-pump', 'references.es-install.checked' ) ),
);
