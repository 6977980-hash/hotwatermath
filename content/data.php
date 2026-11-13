<?php
/**
 * /data/ (PHASES Phase 8 task 1, STRATEGY "Linkable assets"): every public table as a CSV download with Dataset
 * schema (inc/schema.php) and a "cite this" box. Datasets come from data/datasets.json, built into
 * data/computed/datasets.json by npm run build:data; files are served at /data/<id>.csv (inc/routes.php).
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
$hwm_sets = (array) hwm_raw( 'computed/datasets', 'datasets' );
$hwm_lic  = (array) hwm_raw( 'computed/datasets', 'license' );
$hwm_ext  = static fn( string $url, string $text ): string => '<a href="' . esc_url( $url ) . '" rel="noopener">' . esc_html( $text ) . '</a>';
$hwm_cite = static fn( array $set ): string => hwm_site( 'brand' ) . ' (' . substr( (string) $set['sourceDate'], 0, 4 ) . '). ' . $set['name'] . ' [Data set]. ' . hwm_url( 'data' ) . '#' . $set['id'];

$related = array( 'water-heater-installation-cost-index', 'methodology', 'water-heater-cost-calculator', 'gas-vs-electric-water-heater', 'tankless-water-heater-size-calculator' );
?>
<p>These are the same tables our calculators and guides use, as plain CSV files you can open in Excel, Google Sheets or any data tool. Each one names its primary sources and the date we last built it. The formulas behind the computed columns are on <a href="<?php echo esc_url( hwm_url( 'methodology' ) ); ?>">our methodology page</a>.</p>

<h2>What data can I download?</h2>
<p>There are <?php echo esc_html( (string) count( $hwm_sets ) ); ?> datasets, from state-level costs and energy prices to the reference tables behind tank and tankless sizing. Every file is free, needs no sign-up, and comes from a table you can also see on one of our pages.</p>
<div class="table-wrap" role="region" aria-label="Datasets" tabindex="0">
	<table>
		<caption>Datasets on this page</caption>
		<thead><tr><th scope="col">Dataset</th><th scope="col" class="num">Rows</th><th scope="col">Download</th></tr></thead>
		<tbody>
			<?php foreach ( $hwm_sets as $hwm_set ) : ?>
				<tr>
					<th scope="row"><a href="#<?php echo esc_attr( (string) $hwm_set['id'] ); ?>"><?php echo esc_html( (string) $hwm_set['name'] ); ?></a></th>
					<td class="num"><?php echo esc_html( (string) $hwm_set['rowCount'] ); ?></td>
					<td><a href="<?php echo esc_url( hwm_origin() . '/data/' . $hwm_set['id'] . '.csv' ); ?>" download>CSV</a></td>
				</tr>
			<?php endforeach; ?>
		</tbody>
	</table>
</div>

<?php foreach ( $hwm_sets as $hwm_set ) : ?>
	<section class="dataset" id="<?php echo esc_attr( (string) $hwm_set['id'] ); ?>" aria-labelledby="<?php echo esc_attr( 'h-' . $hwm_set['id'] ); ?>">
		<h3 id="<?php echo esc_attr( 'h-' . $hwm_set['id'] ); ?>"><?php echo esc_html( (string) $hwm_set['name'] ); ?></h3>
		<p><?php echo esc_html( (string) $hwm_set['description'] ); ?></p>
		<dl class="dataset__meta">
			<dt>Columns</dt><dd><?php echo esc_html( implode( ' · ', (array) $hwm_set['columns'] ) ); ?></dd>
			<dt>Rows</dt><dd><?php echo esc_html( (string) $hwm_set['rowCount'] ); ?></dd>
			<dt>How it is built</dt><dd><?php echo esc_html( (string) $hwm_set['tableSource'] ); ?></dd>
			<dt>Primary sources</dt><dd>
				<?php
				$hwm_links = array();
				foreach ( (array) $hwm_set['basedOn'] as $hwm_i => $hwm_url ) {
					$hwm_links[] = '<li>' . $hwm_ext( (string) $hwm_url, (string) ( $hwm_set['basedOnNames'][ $hwm_i ] ?? wp_parse_url( (string) $hwm_url, PHP_URL_HOST ) ) ) . '</li>';
				}
				echo '<ul>' . implode( '', $hwm_links ) . '</ul>'; // phpcs:ignore -- escaped above.
				?>
			</dd>
			<dt>Last built</dt><dd><?php echo esc_html( hwm_date( (string) $hwm_set['sourceDate'] ) ); ?></dd>
			<dt>Shown on</dt><dd><?php echo hwm_link( (string) $hwm_set['page'] ); // phpcs:ignore ?></dd>
		</dl>
		<p><a class="btn btn--ghost" href="<?php echo esc_url( hwm_origin() . '/data/' . $hwm_set['id'] . '.csv' ); ?>" download>Download CSV</a></p>
	</section>
<?php endforeach; ?>

<h2>How do I cite this data?</h2>
<p>Credit <?php echo esc_html( (string) hwm_site( 'brand' ) ); ?> and link to this page or to the dataset's anchor. A plain line such as the one below is enough; journalists may also link the page where the table appears.</p>
<div class="cite-box">
	<p class="cite-box__label">Cite this (example: the cost index)</p>
	<textarea readonly rows="3" aria-label="Citation"><?php echo esc_textarea( $hwm_sets ? $hwm_cite( $hwm_sets[0] ) : '' ); ?></textarea>
</div>
<p>Each dataset section above has its own anchor, so you can link straight to it (for example <code><?php echo esc_html( hwm_url( 'data' ) . '#' . ( $hwm_sets[1]['id'] ?? '' ) ); ?></code>).</p>

<h2>Can I use it in my own article or app?</h2>
<p>Yes. The files are released under the <?php echo $hwm_ext( (string) ( $hwm_lic['url'] ?? '' ), (string) ( $hwm_lic['name'] ?? '' ) ); // phpcs:ignore ?> license: you may copy, change and republish them, including commercially, as long as you credit <?php echo esc_html( (string) hwm_site( 'brand' ) ); ?>. The underlying government data (EIA, BEA, NOAA, DOE) is public domain; our license covers the way we combined and computed it.</p>
<dl>
	<dt>Computed column</dt>
	<dd>A number we calculated with a published formula from the primary sources, such as a yearly energy cost from an EIA price and a DOE efficiency rating. The formula for every computed column is on the methodology page.</dd>
</dl>

<h2>How current is the data?</h2>
<p>Each dataset shows the date we last built it. Energy prices follow EIA's annual releases, price levels follow BEA's yearly Regional Price Parities, and equipment prices are re-checked against retail listings. When a source updates, we rebuild every table from the same files, so the CSVs, the calculators and the articles always agree.</p>

<h2>What are the limits of these numbers?</h2>
<p>State figures are averages. A house in a big city usually pays more than its state average, and a rural one less. Installed costs are planning estimates for a like-for-like replacement, not quotes. If you find a mistake, write to <a href="mailto:<?php echo esc_attr( (string) hwm_site( 'contact.email' ) ); ?>"><?php echo esc_html( (string) hwm_site( 'contact.email' ) ); ?></a> and we will correct it under our <?php echo hwm_link( 'editorial-policy', 'editorial policy' ); // phpcs:ignore ?>.</p>
<?php
$faq     = array(
	array(
		'q' => 'Is the water heater data free to use?',
		'a' => 'Yes. Every CSV is free under the ' . ( $hwm_lic['name'] ?? '' ) . ' license. You can use it in articles, reports and apps, including commercial ones, as long as you credit ' . hwm_site( 'brand' ) . ' with a link.',
	),
	array(
		'q' => 'Where do the numbers come from?',
		'a' => 'From federal sources (EIA energy prices, BEA price levels, NOAA temperatures, DOE efficiency and cost rules), manufacturer documents and dated retail listings. Each dataset names its primary sources and the date we last built it.',
	),
	array(
		'q' => 'Why do the CSV numbers have no dollar signs or commas?',
		'a' => 'So spreadsheets read them as numbers. The column name carries the unit, for example "Gas tank installed ($)", and ranges are split into separate low and high columns.',
	),
	array(
		'q' => 'How often is the data updated?',
		'a' => 'When a primary source publishes new figures, usually once a year for EIA and BEA data. The "Last built" date on each dataset shows when we last rebuilt it.',
	),
);
$sources = array();
$hwm_seen = array();
foreach ( $hwm_sets as $hwm_set ) {
	foreach ( (array) $hwm_set['basedOn'] as $hwm_i => $hwm_url ) {
		if ( isset( $hwm_seen[ $hwm_url ] ) ) {
			continue;
		}
		$hwm_seen[ $hwm_url ] = true;
		$sources[] = array( 'publisher' => '', 'title' => (string) ( $hwm_set['basedOnNames'][ $hwm_i ] ?? $hwm_set['name'] ), 'url' => (string) $hwm_url, 'accessed' => (string) hwm_raw( 'datasets', 'meta.sourceDate' ) );
	}
}
